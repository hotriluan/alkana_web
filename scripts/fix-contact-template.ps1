# Fix page-contact.php: replace hardcoded contact info with get_theme_mod() calls

$file = "c:\dev\alkana_web\wp-content\themes\alkana\templates\page-contact.php"
$enc  = [System.Text.Encoding]::UTF8

$content  = [System.IO.File]::ReadAllText($file, $enc)

# Anchors (tabs are literal in the file)
$startMarker = "`t`t`t`t<div class=`"space-y-4`">"
$endMarker   = "`t`t`t<div class=`"contact-form`">"

$startIdx = $content.IndexOf($startMarker)
$endIdx   = $content.IndexOf($endMarker)

if ($startIdx -lt 0 -or $endIdx -lt 0) {
    Write-Error "Markers not found. startIdx=$startIdx  endIdx=$endIdx"
    exit 1
}

$before = $content.Substring(0, $startIdx)
$after  = $content.Substring($endIdx)

# New dynamic block (single-quoted here-string — no PS variable expansion)
$nl = [System.Environment]::NewLine
$t4 = "`t`t`t`t"
$t5 = "`t`t`t`t`t"
$t6 = "`t`t`t`t`t`t"
$t7 = "`t`t`t`t`t`t`t"
$t8 = "`t`t`t`t`t`t`t`t"

$newBlock = @'
			<?php
			$contact_address    = get_theme_mod( 'alkana_address', 'Lô C1-2, Đường N1, KCN Hiệp Phước, Nhà Bè, TP. Hồ Chí Minh' );
			$contact_phone      = get_theme_mod( 'alkana_phone', '+84 28 3873 8888' );
			$contact_email      = get_theme_mod( 'alkana_email', 'info@alkana.vn' );
			$contact_hours      = get_theme_mod( 'alkana_hours', 'Thứ Hai - Thứ Bảy: 8:00 - 17:00' );
			$contact_map        = get_theme_mod( 'alkana_map_embed', '' );
			$contact_phone_e164 = preg_replace( '/[^+\d]/', '', $contact_phone );
			?>
				<div class="space-y-4">

					<?php if ( $contact_address ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary] leading-relaxed">
								<?php echo esc_html( $contact_address ); ?>
							</p>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_phone ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
						</svg>
						<div>
							<a href="tel:<?php echo esc_attr( $contact_phone_e164 ); ?>" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								<?php echo esc_html( $contact_phone ); ?>
							</a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_email ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
						</svg>
						<div>
							<a href="mailto:<?php echo esc_attr( $contact_email ); ?>" class="text-[--color-secondary] hover:text-[--color-primary] transition-colors">
								<?php echo esc_html( $contact_email ); ?>
							</a>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $contact_hours ) : ?>
					<div class="flex items-start gap-3">
						<svg class="w-5 h-5 text-[--color-primary] flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
						</svg>
						<div>
							<p class="text-[--color-secondary]">
								<?php echo esc_html( $contact_hours ); ?>
							</p>
						</div>
					</div>
					<?php endif; ?>

				</div>

				<?php if ( $contact_map ) : ?>
				<div class="mt-8">
					<div class="aspect-video rounded-xl overflow-hidden shadow-lg">
						<?php echo alkana_sanitize_map_embed( $contact_map ); ?>
					</div>
				</div>
				<?php endif; ?>

'@

$newContent = $before + $newBlock + $after
[System.IO.File]::WriteAllText($file, $newContent, $enc)

Write-Host "SUCCESS: page-contact.php updated (startIdx=$startIdx, endIdx=$endIdx)"
