<?php
/**
 * Template Name: Paint System Builder
 * 3-step wizard: Surface → Environment → Recommendation.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );

// Surface type icons keyed by slug
$surface_icons = [
	'wood'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 7l4-4 4 4M8 17l4 4 4-4"/></svg>',
	'metal-steel'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>',
	'concrete'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18" stroke-linecap="round"/></svg>',
	'plaster-wall'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="2"/><path d="M2 8h20M2 14h20M8 2v20M14 2v20" stroke-linecap="round"/></svg>',
	'roof-tile'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-7 9 7v11H3V10z"/></svg>',
	'concrete-floor'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 20h18M3 16h18M6 16V8m4 8V8m4 8V8m4 8V8"/></svg>',
	'water-tank'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C8 2 4 8 4 14a8 8 0 0016 0c0-6-4-12-8-12z"/></svg>',
	'plastic-pvc'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>',
];

$surfaces = get_terms( [
	'taxonomy'   => 'surface_type',
	'hide_empty' => false,
	'orderby'    => 'term_order',
] );
?>

<main id="main-content" class="site-main">

	<!-- ── Hero ───────────────────────────────────────────────────────────── -->
	<section class="bg-gradient-to-br from-alkana-purple-800 to-alkana-purple-900 text-white py-14">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
			<span class="inline-block bg-white/10 border border-white/20 text-xs font-semibold uppercase tracking-widest px-3 py-1 rounded-full mb-4">
				Công cụ tư vấn
			</span>
			<h1 class="text-3xl md:text-4xl font-bold font-heading mb-3">
				Trợ lý Tư vấn Giải pháp Sơn Alkana
			</h1>
			<p class="text-white/70 max-w-xl mx-auto text-base">
				Chọn bề mặt bạn cần bảo vệ để chúng tôi đưa ra hệ thống sơn tối ưu nhất cho công trình của bạn.
			</p>
		</div>
	</section>

	<!-- ── Wizard ─────────────────────────────────────────────────────────── -->
	<section class="py-12 bg-gray-50 min-h-[60vh]">
		<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

			<!-- Progress -->
			<div class="flex items-center justify-center gap-2 mb-10" aria-label="Wizard steps">
				<?php foreach ( [ '1. Bề mặt', '2. Điều kiện', '3. Kết quả' ] as $i => $label ) : ?>
					<div class="flex items-center gap-2">
						<div class="builder-step-dot flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
									<?php echo $i === 0 ? 'bg-alkana-purple-600 text-white' : 'bg-gray-200 text-gray-400'; ?>"
							 id="step-dot-<?php echo $i + 1; ?>">
							<?php echo $i + 1; ?>
						</div>
						<span class="text-sm font-medium <?php echo $i === 0 ? 'text-alkana-purple-700' : 'text-gray-400'; ?>"
							  id="step-label-<?php echo $i + 1; ?>">
							<?php echo esc_html( $label ); ?>
						</span>
					</div>
					<?php if ( $i < 2 ) : ?>
						<div class="w-10 h-px bg-gray-300 mx-1" id="step-connector-<?php echo $i + 1; ?>"></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<!-- Step 1: Surface Type -->
			<div id="step-1" class="builder-step">
				<h2 class="text-xl font-bold text-alkana-purple-900 mb-6 text-center">Chọn loại bề mặt cần sơn</h2>
				<div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="surface-grid" role="group" aria-label="Loại bề mặt">
					<?php
					foreach ( (array) $surfaces as $term ) :
						if ( is_wp_error( $term ) ) continue;
						$icon = $surface_icons[ $term->slug ] ?? $surface_icons['concrete'];
					?>
					<button type="button"
							class="surface-card group flex flex-col items-center gap-3 p-5 bg-white rounded-xl border-2 border-gray-100
								   hover:border-alkana-purple-400 hover:shadow-md transition-all duration-200 cursor-pointer text-center"
							data-surface="<?php echo esc_attr( $term->slug ); ?>"
							aria-pressed="false"
							aria-label="<?php echo esc_attr( $term->name ); ?>">
						<div class="w-12 h-12 rounded-full bg-alkana-purple-50 flex items-center justify-center text-alkana-purple-600
									group-hover:bg-alkana-purple-100 transition-colors surface-card__icon">
							<?php echo $icon; // already safe SVG literal ?>
						</div>
						<span class="text-sm font-semibold text-gray-700 group-hover:text-alkana-purple-700 transition-colors">
							<?php echo esc_html( $term->name ); ?>
						</span>
					</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Step 2: Environment & Condition (locked) -->
			<div id="step-2" class="builder-step mt-10 hidden">
				<h2 class="text-xl font-bold text-alkana-purple-900 mb-6 text-center">Điều kiện thi công</h2>
				<div class="flex flex-col gap-6 max-w-lg mx-auto">

					<fieldset>
						<legend class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Vị trí</legend>
						<div class="grid grid-cols-2 gap-3">
							<button type="button" class="env-btn option-btn" data-group="environment" data-value="indoor">
								<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10l9-7 9 7v11H3V10z"/></svg>
								Trong nhà
							</button>
							<button type="button" class="env-btn option-btn" data-group="environment" data-value="outdoor">
								<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 100 20A10 10 0 0012 2zm0 0v4m0 12v4M2 12H6m12 0h4"/><circle cx="12" cy="12" r="4"/></svg>
								Ngoài trời
							</button>
						</div>
					</fieldset>

					<fieldset>
						<legend class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Điều kiện môi trường</legend>
						<div class="grid grid-cols-2 gap-3">
							<button type="button" class="env-btn option-btn" data-group="condition" data-value="normal">
								<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
								Điều kiện thường
							</button>
							<button type="button" class="env-btn option-btn" data-group="condition" data-value="harsh">
								<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
								Hóa chất / Khắc nghiệt
							</button>
						</div>
					</fieldset>
				</div>
			</div>

			<!-- Step 3: Results (locked) -->
			<div id="step-3" class="builder-step mt-10 hidden">
				<h2 class="text-xl font-bold text-alkana-purple-900 mb-6 text-center">Hệ thống sơn phù hợp</h2>
				<div id="builder-results" class="builder-results">
					<!-- Skeleton (visible during loading) -->
					<div id="builder-skeleton" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
						<?php for ( $i = 0; $i < 3; $i++ ) : ?>
						<div class="animate-pulse bg-white rounded-xl border border-gray-100 overflow-hidden">
							<div class="aspect-[4/3] bg-gray-200 w-full"></div>
							<div class="p-5 space-y-3">
								<div class="h-3 bg-gray-200 rounded w-1/3"></div>
								<div class="h-4 bg-gray-300 rounded w-3/4"></div>
								<div class="h-3 bg-gray-200 rounded w-1/2"></div>
							</div>
						</div>
						<?php endfor; ?>
					</div>
				</div>
			</div>

			<!-- Reset button -->
			<div class="text-center mt-8 hidden" id="builder-reset-wrap">
				<button type="button" id="builder-reset" class="text-sm text-alkana-purple-600 hover:underline font-medium">
					← Chọn lại
				</button>
			</div>

		</div>
	</section>

</main>

<?php get_template_part( 'template-parts/footer' ); ?>
