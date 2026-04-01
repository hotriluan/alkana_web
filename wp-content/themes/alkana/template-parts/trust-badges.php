<?php
/**
 * Trust Badges / Certifications Bar
 * Horizontal strip showing key trust indicators.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="section section--trust py-8 bg-gray-50 border-y border-gray-100">
	<div class="max-w-7xl mx-auto px-4">
		<div class="flex items-center justify-center gap-8 md:gap-16 flex-wrap">
			<!-- Badge 1: ISO -->
			<div class="flex items-center gap-3">
				<svg class="w-6 h-6 text-[#E8611A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
				</svg>
				<span class="text-sm text-gray-700 font-medium">ISO 9001:2015</span>
			</div>

			<!-- Badge 2: Warranty -->
			<div class="flex items-center gap-3">
				<svg class="w-6 h-6 text-[#E8611A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
				</svg>
				<span class="text-sm text-gray-700 font-medium">Bảo Hành 10 Năm</span>
			</div>

			<!-- Badge 3: Free Consultation -->
			<div class="flex items-center gap-3">
				<svg class="w-6 h-6 text-[#E8611A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
				</svg>
				<span class="text-sm text-gray-700 font-medium">Tư Vấn Miễn Phí</span>
			</div>

			<!-- Badge 4: Nationwide Delivery -->
			<div class="flex items-center gap-3">
				<svg class="w-6 h-6 text-[#E8611A]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
				</svg>
				<span class="text-sm text-gray-700 font-medium">Giao Hàng Toàn Quốc</span>
			</div>
		</div>
	</div>
</section>
