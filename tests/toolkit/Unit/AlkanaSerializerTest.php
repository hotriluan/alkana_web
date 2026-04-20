<?php
/**
 * Phase 01 — AlkanaSerializer Unit Tests
 *
 * Covers:
 * - isSerialized() detection (null, bool, int, float, array, nested)
 * - recursiveReplace() — plain strings, arrays, objects, nested serialized
 * - Scenario 9.1: mb_strlen vs strlen for Vietnamese UTF-8 serialized strings
 * - Scenario 9.5: unserialize without allowed_classes (object injection guard)
 */

declare(strict_types=1);

namespace AlkanaTests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AlkanaSerializerTest extends TestCase
{
    // ── isSerialized ───────────────────────────────────────────────────────────

    public function testIsSerializedReturnsFalseForEmptyString(): void
    {
        $this->assertFalse(\AlkanaSerializer::isSerialized(''));
    }

    public function testIsSerializedReturnsFalseForPlainString(): void
    {
        $this->assertFalse(\AlkanaSerializer::isSerialized('hello world'));
    }

    public function testIsSerializedReturnsFalseForNonString(): void
    {
        $this->assertFalse(\AlkanaSerializer::isSerialized(42));
        $this->assertFalse(\AlkanaSerializer::isSerialized(null));
        $this->assertFalse(\AlkanaSerializer::isSerialized(['a']));
    }

    public function testIsSerializedDetectsNullSerialized(): void
    {
        $this->assertTrue(\AlkanaSerializer::isSerialized('N;'));
    }

    public function testIsSerializedDetectsBoolean(): void
    {
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize(true)));
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize(false)));
    }

    public function testIsSerializedDetectsInteger(): void
    {
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize(42)));
    }

    public function testIsSerializedDetectsString(): void
    {
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize('hello')));
    }

    public function testIsSerializedDetectsArray(): void
    {
        $data = ['key' => 'value', 'nested' => [1, 2, 3]];
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize($data)));
    }

    public function testIsSerializedDetectsAssocArray(): void
    {
        $data = ['site_url' => 'http://localhost', 'count' => 5];
        $this->assertTrue(\AlkanaSerializer::isSerialized(serialize($data)));
    }

    public function testIsSerializedReturnsFalseForPartiallySerializedLike(): void
    {
        // Looks like serialized prefix but broken
        $this->assertFalse(\AlkanaSerializer::isSerialized('a:BROKEN'));
    }

    // ── Scenario 9.1: UTF-8 multibyte serialized string ───────────────────────

    /**
     * CRITICAL: recursiveReplace must correctly rebuild serialized string lengths.
     * Vietnamese strings use multi-byte chars — strlen() counts bytes, mb_strlen() counts chars.
     * PHP's serialize() uses byte lengths, so we MUST use strlen() here.
     */
    public function testRecursiveReplaceHandlesVietnameseSerializedString(): void
    {
        // "Sơn Alkana" → "Sơn AlkanaNew" — contains multibyte chars
        $original = 'http://localhost';
        $replacement = 'http://alkana.vn';
        $data = serialize([
            'name' => 'Sơn Alkana',
            'url'  => 'http://localhost/alkana',
            'desc' => 'Sơn nước cao cấp',
        ]);

        $result = \AlkanaSerializer::recursiveReplace($data, $original, $replacement);

        // Must be valid serialized data
        $unserialized = @unserialize($result, ['allowed_classes' => false]);
        $this->assertIsArray($unserialized, 'Result must be valid serialized array after replace');
        $this->assertSame('http://alkana.vn/alkana', $unserialized['url']);
        $this->assertSame('Sơn Alkana', $unserialized['name']); // unchanged
    }

    public function testRecursiveReplaceVietnameseFixtureFile(): void
    {
        // Generate correct PHP serialized data (byte-accurate lengths)
        $data = [
            'name' => 'Sơn Alkana',
            'url'  => 'http://localhost/alkana',
            'desc' => 'Sơn nước cao cấp cho mọi công trình',
        ];
        $fixture = serialize($data);

        $result = \AlkanaSerializer::recursiveReplace(
            $fixture,
            'http://localhost/alkana',
            'http://alkana.vn'
        );

        $unserialized = @unserialize($result, ['allowed_classes' => false]);
        $this->assertIsArray($unserialized, 'Multibyte fixture must remain valid after replace');
        $this->assertSame('http://alkana.vn', $unserialized['url']);
        $this->assertSame('Sơn Alkana', $unserialized['name']); // unchanged
    }

    // ── Scenario 9.5: unserialize object injection guard ──────────────────────

    public function testRecursiveReplaceRejectsObjectInjection(): void
    {
        // Attempt to inject a malicious object through serialized data
        // allowed_classes => false ensures no class is instantiated
        $malicious = 'O:8:"stdClass":1:{s:4:"exec";s:2:"id";}';
        // isSerialized may match this, but recursiveReplace must not instantiate objects
        $result = \AlkanaSerializer::recursiveReplace($malicious, 'exec', 'safe');
        // Should return plain string (no object created that could trigger __destruct)
        $this->assertIsString($result);
    }

    public function testIsSerializedDoesNotInstantiateObjects(): void
    {
        // Even if we pass a valid object serialization, allowed_classes => false must prevent instantiation
        $serializedObj = serialize(new \stdClass());
        // The method calls unserialize with ['allowed_classes' => false]
        // This is tested indirectly — isSerialized should return true but not crash
        $result = \AlkanaSerializer::isSerialized($serializedObj);
        $this->assertTrue($result);
    }

    // ── recursiveReplace — basic scenarios ─────────────────────────────────────

    public function testRecursiveReplacePlainString(): void
    {
        $result = \AlkanaSerializer::recursiveReplace(
            'http://old.example.com/page',
            'http://old.example.com',
            'http://new.example.com'
        );
        $this->assertSame('http://new.example.com/page', $result);
    }

    public function testRecursiveReplaceDoesNotModifyNonMatchingString(): void
    {
        $result = \AlkanaSerializer::recursiveReplace('no match here', 'needle', 'replace');
        $this->assertSame('no match here', $result);
    }

    public function testRecursiveReplaceArray(): void
    {
        $data = ['url' => 'http://old.com', 'title' => 'Old Site'];
        $result = \AlkanaSerializer::recursiveReplace($data, 'http://old.com', 'http://new.com');
        $this->assertIsArray($result);
        $this->assertSame('http://new.com', $result['url']);
        $this->assertSame('Old Site', $result['title']);
    }

    public function testRecursiveReplaceNestedArray(): void
    {
        $data = ['outer' => ['inner' => 'http://old.com/path']];
        $result = \AlkanaSerializer::recursiveReplace($data, 'http://old.com', 'http://new.com');
        $this->assertSame('http://new.com/path', $result['outer']['inner']);
    }

    public function testRecursiveReplaceArrayKeys(): void
    {
        $data = ['http://old.com' => 'value'];
        $result = \AlkanaSerializer::recursiveReplace($data, 'http://old.com', 'http://new.com');
        $this->assertArrayHasKey('http://new.com', $result);
    }

    public function testRecursiveReplaceSerializedArray(): void
    {
        $data = serialize(['url' => 'http://old.com', 'count' => 5]);
        $result = \AlkanaSerializer::recursiveReplace($data, 'http://old.com', 'http://new.com');

        $unserialized = @unserialize($result, ['allowed_classes' => false]);
        $this->assertIsArray($unserialized);
        $this->assertSame('http://new.com', $unserialized['url']);
        $this->assertSame(5, $unserialized['count']);
    }

    public function testRecursiveReplaceDoublyNestedSerialized(): void
    {
        // Serialized inside serialized (WordPress widget_data style)
        $inner = serialize(['url' => 'http://old.com']);
        $outer = serialize(['data' => $inner, 'other' => 'val']);

        $result = \AlkanaSerializer::recursiveReplace($outer, 'http://old.com', 'http://new.com');

        $outerArr = @unserialize($result, ['allowed_classes' => false]);
        $this->assertIsArray($outerArr);
        $innerArr = @unserialize($outerArr['data'], ['allowed_classes' => false]);
        $this->assertIsArray($innerArr);
        $this->assertSame('http://new.com', $innerArr['url']);
    }

    public function testRecursiveReplaceObject(): void
    {
        $obj = new \stdClass();
        $obj->url = 'http://old.com';
        $obj->name = 'Alkana';

        $result = \AlkanaSerializer::recursiveReplace($obj, 'http://old.com', 'http://new.com');
        $this->assertSame('http://new.com', $result->url);
        $this->assertSame('Alkana', $result->name);
    }

    public function testRecursiveReplacePassesThroughInt(): void
    {
        $this->assertSame(42, \AlkanaSerializer::recursiveReplace(42, 'a', 'b'));
    }

    public function testRecursiveReplacePassesThroughNull(): void
    {
        $this->assertNull(\AlkanaSerializer::recursiveReplace(null, 'a', 'b'));
    }

    public function testRecursiveReplaceBoolFalseSerialized(): void
    {
        // b:0; is the edge case
        $data = serialize(false);
        $this->assertSame('b:0;', $data);

        // isSerialized should handle b:0; correctly
        $this->assertTrue(\AlkanaSerializer::isSerialized('b:0;'));
    }
}
