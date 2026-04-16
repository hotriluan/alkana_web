# Alkana Code Standards & Development Guidelines

**Last Updated:** April 15, 2026  
**Revamp Status:** Complete — All 10 Phases Delivered  
**Version:** 2.0 (Post-Master Revamp)

---

## Overview

This document defines the coding conventions, file structure, and development best practices for the Alkana project. All code in this repository follows these standards to ensure consistency, maintainability, and quality.

**Core Principles:**
- **YAGNI** — You Aren't Gonna Need It (no over-engineering)
- **KISS** — Keep It Simple, Stupid (prefer simple solutions)
- **DRY** — Don't Repeat Yourself (eliminate code duplication)

---

## File Size & Modularization

### Maximum File Size: 200 Lines of Code

**Rule:** Individual code files should not exceed 200 lines of code (excluding comments, blank lines, and imports).

**Why?**
- Improves readability and code comprehension
- Makes testing and debugging easier
- Encourages logical separation of concerns
- Reduces merge conflicts in version control

**Exception:** Configuration files (tailwind.config.js, vite.config.js) may exceed 200 lines.

### Modularization Strategy

When a file approaches 200 LOC:
1. **Identify logical separation boundaries** — Functions performing distinct tasks
2. **Create separate module files** — One concern per file
3. **Use consistent naming** — Kebab-case filenames with descriptive names
4. **Export and import cleanly** — Clear module interfaces

**Example:**
```php
// Before: single-file with 250+ LOC
// form-handler.php (too large)

// After: modularized
forms/
├── form-validator.php    (validation logic)
├── form-sanitizer.php    (input sanitization)
├── form-handler.php      (form processing)
└── form-mailer.php       (email sending)
```

---

## File Naming Conventions

### General Rules
- **Format:** kebab-case (lowercase with hyphens)
- **Descriptive:** Include purpose in filename (not generic names like `utils.php` or `main.js`)
- **Long names are okay:** Self-documenting filenames are more important than brevity

### Examples

**Good:**
- `paint-system-builder.js` — Clear purpose
- `inline-edit-handler.php` — Specific functionality
- `admin-dashboard-widgets.php` — Obvious responsibility

**Bad:**
- `main.js` — Too generic
- `util.php` — Unclear purpose
- `x.js` — Meaningless

### File Types

| Type | Format | Location | Example |
|------|--------|----------|---------|
| PHP Classes | kebab-case.php | `inc/` | `paint-recommendation-engine.php` |
| PHP Functions | kebab-case.php | `inc/` | `form-validators.php` |
| JavaScript Modules | kebab-case.js | `dist/assets/src/scripts/` | `paint-builder.js` |
| CSS Modules | kebab-case.css | `dist/assets/src/styles/` | `product-cards.css` |
| Templates (WordPress) | name.php | `templates/` | `paint-builder-wizard.php` |
| Images/Assets | kebab-case.ext | `dist/assets/src/images/` | `hero-background.jpg` |

---

## PHP Code Standards

### File Header & DocBlocks

```php
<?php
/**
 * Paint System Builder Recommendation Engine
 *
 * Handles the recommendation logic for the 3-step paint system wizard.
 * Queries the product index table and returns ranked results.
 *
 * @package Alkana
 * @subpackage Paint_Builder
 * @since 2.0
 */

namespace Alkana\PaintBuilder;

// ... code follows
```

### Function & Variable Naming

**Functions:** lowercase with underscores (snake_case)
```php
function alkana_get_paint_recommendations( $surface, $conditions ) {
    // ...
}

function sanitize_paint_system_input( $input ) {
    // ...
}
```

**Variables:** lowercase with underscores (snake_case)
```php
$surface_type = 'steel';
$environment_conditions = [];
$recommended_products = [];
```

**Constants:** UPPERCASE with underscores
```php
const PAINT_SYSTEM_PRIMER = 1;
const PAINT_SYSTEM_INTERMEDIATE = 2;
const PAINT_SYSTEM_TOPCOAT = 3;
```

### Class & Method Naming

**Classes:** PascalCase
```php
class PaintRecommendationEngine {
    public function getSurfaceTypes() { }
    public function getRecommendation( $surface, $conditions ) { }
    private function rankProducts( $products ) { }
}
```

### Code Style (PSR-12)

**Indentation:** 4 spaces (NOT tabs)
```php
if ( condition_is_true() ) {
    do_something();
    if ( nested_condition() ) {
        do_nested_thing();
    }
}
```

**Spaces Around Operators:**
```php
$a = 1;
$b = $a + 5;
if ( $a === $b ) {}
forEach( $items as $item ) {}
```

**WordPress Escaping:**
```php
// Output: always escape
echo esc_html( $product_name );
echo esc_attr( $color_hex );
echo wp_kses_post( $description );

// Input: always sanitize
$value = sanitize_text_field( $_POST['product_name'] );
$email = sanitize_email( $_POST['email'] );
```

**WordPress Nonces (Security):**
```php
// In template (form)
wp_nonce_field( 'alkana_paint_builder', 'alkana_nonce' );

// In AJAX handler
if ( ! isset( $_POST['alkana_nonce'] ) || 
     ! wp_verify_nonce( $_POST['alkana_nonce'], 'alkana_paint_builder' ) ) {
    wp_die( 'Security check failed' );
}
```

### SQL & Database Queries

**Always use prepared statements:**
```php
// Good ✅
$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM $wpdb->posts WHERE post_type = %s AND ID = %d",
    'product',
    $product_id
) );

// Bad ❌
$results = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE ID = " . $product_id );
```

### Comments & Documentation

**Code comments:** Explain WHY, not WHAT
```php
// Good ✅ (explains logic)
// Sort products by relevance score (higher = more relevant to user's conditions)
usort( $products, fn( $a, $b ) => $b['score'] <=> $a['score'] );

// Bad ❌ (obvious from code)
// Increment counter
$count++;
```

**Inline comments:** Use sparingly for non-obvious logic
```php
// Cache the product index query for 24 hours to reduce DB load
set_transient( 'alkana_product_index', $products, DAY_IN_SECONDS );
```

---

## JavaScript Code Standards

### File Header

```javascript
/**
 * Paint System Builder — Alpine.js Wizard
 *
 * Handles the 3-step paint system recommendation wizard using Alpine.js
 * for reactive state management and AJAX for recommendation engine.
 *
 * @module paint-builder
 * @since 2.0
 */
```

### Naming Conventions

**Variables & Functions:** camelCase
```javascript
const productCount = 50;
function getPaintRecommendation(surface, conditions) { }
const isLoading = true;
```

**Constants:** UPPER_SNAKE_CASE
```javascript
const PAINT_LAYER_PRIMER = 'primer';
const PAINT_LAYER_INTERMEDIATE = 'intermediate';
const PAINT_LAYER_TOPCOAT = 'topcoat';
const RECOMMENDATION_TIMEOUT_MS = 5000;
```

**Classes:** PascalCase
```javascript
class PaintWizard {
    constructor(element) { }
    initialize() { }
    handleStepChange(stepNumber) { }
}
```

### Code Style (ESNext)

**Indentation:** 2 spaces (JavaScript convention)
```javascript
if (condition) {
  doSomething();
  if (nestedCondition) {
    doNested();
  }
}
```

**Arrow Functions (modern syntax):**
```javascript
// Good ✅
const filtered = products.filter(p => p.inStock);
const mapped = items.map(item => ({...item, processed: true}));

// Also acceptable
products.forEach((product) => {
  updatePrice(product);
});
```

**Template Literals (for strings):**
```javascript
const message = `Recommended product: ${product.name} (${product.sku})`;
const html = `
  <div class="product-card">
    <h3>${product.name}</h3>
    <p>SKU: ${product.sku}</p>
  </div>
`;
```

**Destructuring (reduce boilerplate):**
```javascript
const { surface, conditions } = wizardState;
const { name, sku, specs } = product;
const [step1, step2, step3] = wizardSteps;
```

### Alpine.js Best Practices

**Component Definition:**
```javascript
// Good ✅
Alpine.data('paintWizard', () => ({
  currentStep: 1,
  selectedSurface: null,
  conditions: {},
  
  init() {
    // Setup code
  },
  
  async nextStep() {
    if (this.validateStep()) {
      this.currentStep++;
    }
  },
  
  getRecommendations() {
    return this.fetchRecommendations(this.selectedSurface, this.conditions);
  }
}));
```

**Template (HTML with Alpine directives):**
```html
<div x-data="paintWizard()" x-init="init()">
  <div x-show="currentStep === 1" class="step-1">
    <h2>Step 1: Select Surface</h2>
    <button @click="selectedSurface = 'steel'; nextStep()">Steel</button>
  </div>
  
  <div x-show="currentStep === 2" class="step-2">
    <h2>Step 2: Environment</h2>
    <input x-model="conditions.humidity" type="text" />
  </div>
  
  <button @click="getRecommendations()" :disabled="!selectedSurface">Get Results</button>
</div>
```

### Async/Await (Modern Promise Handling)

```javascript
// Good ✅
async function fetchRecommendations(surface, conditions) {
  try {
    const response = await fetch('/wp-admin/admin-ajax.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'alkana_get_paint_recommendations',
        surface: surface,
        conditions: JSON.stringify(conditions),
      })
    });
    
    if (!response.ok) throw new Error('Recommendation failed');
    
    const data = await response.json();
    return data.success ? data.data : null;
  } catch (error) {
    console.error('Paint recommendation error:', error);
    return null;
  }
}
```

### Error Handling

```javascript
// Good ✅
try {
  const result = await getRecommendations();
  if (!result) {
    throw new Error('No recommendations found');
  }
  displayResults(result);
} catch (error) {
  console.error('[Paint Builder]', error.message);
  showErrorMessage('Failed to get recommendations. Please try again.');
}
```

---

## CSS & Tailwind Standards

### CSS Custom Properties (Design Tokens)

Define all colors, spacing, and typography in `:root`:
```css
:root {
  /* Color Palette */
  --color-primary: #67219D;
  --color-primary-hover: #8236BC;
  --color-primary-light: #B87EDD;
  --color-primary-dark: #4C0682;
  
  /* Typography */
  --font-heading: 'Montserrat', sans-serif;
  --font-body: 'Inter', sans-serif;
  --font-size-body: 1rem;
  --font-size-small: 0.875rem;
  
  /* Spacing */
  --spacing-xs: 0.25rem;  /* 4px */
  --spacing-sm: 0.5rem;   /* 8px */
  --spacing-md: 1rem;     /* 16px */
  --spacing-lg: 1.5rem;   /* 24px */
  --spacing-xl: 2rem;     /* 32px */
}
```

### Tailwind CSS Usage

**Prefer Tailwind utilities over custom CSS:**
```html
<!-- Good ✅ -->
<button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg text-white">
  Get Recommendation
</button>

<!-- Avoid ❌ -->
<button style="padding: 8px 16px; background-color: #67219D; border-radius: 8px;">
  Get Recommendation
</button>
```

**Organize utilities logically:**
```html
<!-- Layout | Sizing | Color | Spacing | Typography | Other -->
<div class="flex flex-col gap-4 bg-purple-50 p-6 rounded-lg shadow-md">
  <h2 class="text-2xl font-bold text-purple-900">Product Details</h2>
  <p class="text-gray-600 leading-relaxed">Description here...</p>
</div>
```

### Component Classes (When Needed)

Only create custom CSS classes for components used multiple times:
```css
/* Good ✅ */
.product-card {
  @apply flex flex-col gap-3 p-4 bg-white rounded-lg shadow-md 
         border border-gray-200 hover:shadow-lg transition-shadow;
}

.product-card__title {
  @apply text-lg font-bold text-gray-900;
}

/* Avoid ❌ */
.p { /* Too generic */
}

.header-element-123 { /* Too specific */
}
```

---

## WordPress Best Practices

### Action & Filter Hooks

**Naming:** `alkana_{feature}_{action}`
```php
// Action hooks
do_action( 'alkana_paint_recommendation_complete', $result );
do_action( 'alkana_admin_dashboard_init' );

// Filter hooks
$products = apply_filters( 'alkana_paint_recommendations', $products, $surface );
$title = apply_filters( 'alkana_product_title', $title, $product_id );
```

### AJAX Endpoints

All AJAX actions prefixed with `alkana_`:
```php
// In inc/ajax-handlers.php
add_action( 'wp_ajax_alkana_get_paint_recommendations', function() {
    // Public AJAX (unauthenticated)
    if ( ! isset( $_POST['alkana_nonce'] ) ||
         ! wp_verify_nonce( $_POST['alkana_nonce'], 'alkana_paint_builder' ) ) {
        wp_send_json_error( 'Security check failed' );
    }
    
    $surface = sanitize_text_field( $_POST['surface'] ?? '' );
    $conditions = json_decode( sanitize_text_field( $_POST['conditions'] ?? '{}' ), true );
    
    $recommendations = get_paint_recommendations( $surface, $conditions );
    wp_send_json_success( $recommendations );
} );

add_action( 'wp_ajax_nopriv_alkana_get_paint_recommendations', 'alkana_public_ajax_handler' );
```

### ACF Fields

Use ACF Pro for custom fields instead of raw meta boxes:
```php
// In inc/acf-fields.php
if ( function_exists( 'acf_add_local_field_group' ) ) {
    acf_add_local_field_group( [
        'key' => 'group_alkana_product_meta',
        'title' => 'Product Metadata',
        'fields' => [
            [
                'key' => 'field_sku',
                'name' => 'sku',
                'label' => 'SKU',
                'type' => 'text',
                'required' => 1,
            ],
            // ... more fields
        ],
        'location' => [ [ [ 'post_type', '==', 'product' ] ] ],
    ] );
}
```

---

## Testing Standards

### Unit Testing (PHP)

Use PHPUnit for server-side logic:
```php
// tests/PaintRecommendationEngineTest.php
class PaintRecommendationEngineTest extends \PHPUnit\Framework\TestCase {
    public function testGetRecommendationForSteelSurface() {
        $engine = new PaintRecommendationEngine();
        $result = $engine->getRecommendation( 'steel', [] );
        
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'primer', $result );
    }
}
```

### Browser Testing (Playwright)

Test critical user flows:
```javascript
// tests/paint-builder.spec.js
test('Paint Builder Wizard Flow', async ({ page }) => {
  await page.goto( '/paint-builder' );
  
  // Step 1: Select surface
  await page.click( 'button:has-text("Steel")' );
  await page.click( 'button:has-text("Next")' );
  
  // Step 2: Set conditions
  await page.fill( 'input[name="humidity"]', 'high' );
  await page.click( 'button:has-text("Get Recommendations")' );
  
  // Step 3: Verify results
  await expect( page.locator( '.recommendation-result' ) ).toBeVisible();
});
```

---

## Version Control Standards

### Commit Messages (Conventional Commits)

**Format:** `type(scope): description`

```
feat(paint-builder): add shareable URL state preservation
fix(admin): correct ARIA live region announcement timing
docs(roadmap): update phase 10 completion status
perf(images): optimize product card thumbnails
refactor(styles): consolidate color token definitions
test(paint-builder): add wind conditions test case
```

### Commit Best Practices
- ✅ Atomic commits (one feature/fix per commit)
- ✅ Descriptive messages (explain WHY, not just WHAT)
- ✅ No secrets (no API keys, passwords, tokens)
- ✅ Test before commit (no broken builds)

---

## Performance Guidelines

### Images
- Lazy load images below fold: `loading="lazy"`
- Use `.webp` format with fallback to `.jpg` in `<picture>` tags
- Optimize with ImageOptim or similar (< 100kb per image)

### JavaScript
- Split large features into separate modules (imported only when needed)
- No blocking scripts in `<head>` (use `defer` or `async`)
- Minify & bundle with Vite for production

### CSS
- Use Tailwind utilities (no unused CSS)
- Extract critical CSS for above-the-fold content
- Defer non-critical CSS via `media="print"` trick or `<link rel="preload">`

### Fonts
- Preload fonts in `<head>`: `<link rel="preload" href="..." as="font" type="font/woff2" crossorigin>`
- Limit font weights to essentials (Normal, Bold, etc.)
- Use `font-display: swap` for fast initial render

---

## Accessibility Standards (WCAG 2.1 AA)

### Semantic HTML
```html
<!-- Good ✅ -->
<button>Add to Cart</button>
<input type="email" required>
<h1>Product Title</h1>
<label for="email">Email Address</label>
<input id="email" type="email">

<!-- Bad ❌ -->
<div onclick="addToCart()">Add to Cart</div>
<input placeholder="Email">
<div class="heading">Product Title</div>
```

### ARIA Labels
```html
<!-- Form input -->
<label for="surface">Select Surface Type</label>
<select id="surface" aria-describedby="surface-help">
  <option>Steel</option>
</select>
<small id="surface-help">Required for recommendation</small>

<!-- Icon button -->
<button aria-label="Close dialog">
  <svg><!-- X icon --></svg>
</button>

<!-- Live region -->
<div aria-live="polite" aria-atomic="true" id="status">
  <!-- Status messages inserted here -->
</div>
```

### Keyboard Navigation
- All interactive elements focusable (tab order makes sense)
- Escape key closes modals
- Enter/Space activates buttons
- Arrow keys navigate lists

### Color Contrast
- Minimum 4.5:1 for normal text
- Minimum 3:1 for large text (18pt+)
- Use contrast checkers: WebAIM, Webaim.org/resources/contrastchecker

---

## Code Review Checklist

Before requesting code review:
- [ ] Code follows standards in this document
- [ ] Files are < 200 LOC (or properly modularized)
- [ ] Variables/functions have descriptive names
- [ ] No hardcoded values (use constants)
- [ ] No security issues (sanitize input, verify nonces)
- [ ] No console.log() or var_dump() left in code
- [ ] Tests pass locally
- [ ] Commit messages are descriptive
- [ ] No conflicts with main branch

---

## Tools & Linting

### PHP Linting
```bash
# Check syntax
php -l wp-content/themes/alkana/inc/paint-builder.php

# Run PHPCS (if configured)
./vendor/bin/phpcs wp-content/themes/alkana --standard=PSR12
```

### JavaScript Linting
```bash
# ESLint (if configured)
npm run lint

# Check syntax
node -c dist/assets/src/scripts/paint-builder.js
```

### Vite Build
```bash
# Development watch
npm run dev

# Production build
npm run build
```

---

## Summary

**Key Requirements:**
1. ✅ Max 200 LOC per file
2. ✅ kebab-case filenames (descriptive)
3. ✅ camelCase (JS), snake_case (PHP)
4. ✅ Sanitize input, escape output
5. ✅ No hardcoded values
6. ✅ Comments explain WHY, not WHAT
7. ✅ Tailwind first, minimal custom CSS
8. ✅ WCAG 2.1 AA compliance
9. ✅ Atomic commits with descriptive messages
10. ✅ Test before pushing

**Resources:**
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PSR-12 Style Guide](https://www.php-fig.org/psr/psr-12/)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Alpine.js Docs](https://alpinejs.dev/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

**Last Updated:** April 15, 2026  
**Status:** Active (All phases follow these standards)
