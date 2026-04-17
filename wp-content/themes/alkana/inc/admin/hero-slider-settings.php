<?php
/**
 * Admin Settings Page: Hero Slider
 * Manages homepage hero banner slides.
 * Option key: alkana_hero_slides (array).
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Data helpers
// ---------------------------------------------------------------------------

function alkana_get_hero_slides(): array {
	$saved = get_option( 'alkana_hero_slides', [] );
	return is_array( $saved ) ? $saved : [];
}

// ---------------------------------------------------------------------------
// Front-end: enqueue Swiper CDN + inline CSS/JS (front page only)
// ---------------------------------------------------------------------------

add_action( 'wp_enqueue_scripts', 'alkana_hero_slider_enqueue_frontend' );

function alkana_hero_slider_enqueue_frontend(): void {
	if ( ! is_front_page() || empty( alkana_get_hero_slides() ) ) return;

	wp_enqueue_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11' );

	wp_add_inline_style( 'swiper', '
		.hero-slider-section { position:relative; }
		.hero-swiper .swiper-slide { position:relative; overflow:hidden; }
		.hero-slide__bg { position:absolute; inset:0; width:100%; height:100%;
			background-size:cover; background-position:center; }
		@keyframes alkana-kb { 0%{transform:scale(1)} 100%{transform:scale(1.08)} }
		.swiper-slide-active .hero-slide__bg { animation:alkana-kb 9s ease forwards; }
		@keyframes alkana-up { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
		.hero-slide__title,.hero-slide__subtitle,.hero-slide__cta-wrap { opacity:0; }
		.swiper-slide-active .hero-slide__title   { animation:alkana-up .8s .2s ease both; }
		.swiper-slide-active .hero-slide__subtitle{ animation:alkana-up .8s .45s ease both; }
		.swiper-slide-active .hero-slide__cta-wrap{ animation:alkana-up .8s .65s ease both; }
		.hero-swiper__prev,.hero-swiper__next {
			width:52px !important; height:52px !important;
			background:rgba(255,255,255,.15) !important;
			border-radius:50% !important; border:1px solid rgba(255,255,255,.3) !important;
			color:#fff !important; backdrop-filter:blur(6px);
			transition:background .3s !important; }
		.hero-swiper__prev:hover,.hero-swiper__next:hover { background:rgba(123,31,162,.85) !important; }
		.hero-swiper__prev::after,.hero-swiper__next::after { font-size:18px !important; font-weight:700 !important; }
		.hero-swiper__pagination { bottom:24px !important; }
		.hero-swiper__pagination .swiper-pagination-bullet { background:#fff; opacity:.6; width:10px; height:10px; }
		.hero-swiper__pagination .swiper-pagination-bullet-active {
			background:#7B1FA2; opacity:1; width:30px; border-radius:5px; transition:all .35s; }
	' );

	wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true );

	$loop = count( alkana_get_hero_slides() ) > 1 ? 'true' : 'false';
	wp_add_inline_script( 'swiper-js', "
		function alkanaInitHeroSwiper(){
			var el=document.querySelector('.hero-swiper');
			if(!el||el.classList.contains('swiper-initialized')) return;
			new Swiper('.hero-swiper',{
				loop:{$loop},effect:'fade',fadeEffect:{crossFade:true},speed:1100,
				autoplay:{delay:6000,disableOnInteraction:false,pauseOnMouseEnter:true},
				pagination:{el:'.hero-swiper__pagination',clickable:true},
				navigation:{nextEl:'.hero-swiper__next',prevEl:'.hero-swiper__prev'},
				a11y:{prevSlideMessage:'Slide trước',nextSlideMessage:'Slide tiếp theo'},
				on:{
					slideChangeTransitionStart:function(){
						this.slides.forEach(function(s){
							s.querySelectorAll('.hero-slide__title,.hero-slide__subtitle,.hero-slide__cta-wrap')
							.forEach(function(el){el.style.animation='none';el.offsetHeight;el.style.animation='';});
						});
					}
				}
			});
		}
		document.addEventListener('DOMContentLoaded',alkanaInitHeroSwiper);
		document.addEventListener('alkana:pageChanged',alkanaInitHeroSwiper);
	" );
}

// ---------------------------------------------------------------------------
// Admin: enqueue media library + sortable scripts on our settings page
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', 'alkana_hero_slider_admin_scripts' );

function alkana_hero_slider_admin_scripts(): void {
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'alkana-hero-slider' ) === false ) return;

	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_add_inline_script(
		'jquery-ui-sortable',
		<<<'JS'
jQuery(function($){
	$(document).on('click','.alkana-slide-img-btn',function(e){
		e.preventDefault();
		var $r=$(this).closest('.alkana-slide-row');
		var frame=wp.media({title:'Chọn ảnh banner',button:{text:'Dùng ảnh này'},multiple:false});
		frame.on('select',function(){
			var att=frame.state().get('selection').first().toJSON();
			var url=(att.sizes&&att.sizes.full)?att.sizes.full.url:att.url;
			$r.find('.slide-img-id').val(att.id);
			$r.find('.slide-img-url').val(url);
			$r.find('.slide-preview').attr('src',url).show();
			$r.find('.alkana-slide-img-btn').text('Đổi ảnh');
		});
		frame.open();
	});
	$(document).on('click','.alkana-slide-del',function(){
		if(confirm('Xóa slide này?')){$(this).closest('.alkana-slide-row').remove();renum();}
	});
	$('#alkana-add-slide').on('click',function(){
		var tpl=$('#alkana-slide-tpl').html().replace(/__N__/g,$('.alkana-slide-row').length);
		$('#alkana-slides').append(tpl);
	});
	$('#alkana-slides').sortable({handle:'.alkana-drag',update:renum,axis:'y'});
	function renum(){
		$('.alkana-slide-row').each(function(i){
			$(this).find('.slide-num').text(i+1);
			$(this).find('[name]').each(function(){
				$(this).attr('name',$(this).attr('name').replace(/slides\[\d+\]/,'slides['+i+']'));
			});
		});
	}
});
JS
	);
}

// ---------------------------------------------------------------------------
// Admin page — render
// ---------------------------------------------------------------------------

function alkana_render_hero_slider_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'alkana' ) );
	}

	$updated = false;
	$error   = '';

	if ( isset( $_POST['alkana_hs_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['alkana_hs_nonce'] ) ), 'alkana_save_hero_slides' ) ) {
			$error = __( 'Security check failed. Please try again.', 'alkana' );
		} else {
			$raw    = is_array( $_POST['slides'] ?? null ) ? (array) $_POST['slides'] : [];
			$slides = [];
			foreach ( $raw as $row ) {
				if ( ! is_array( $row ) ) continue;
				$slides[] = [
					'image_id'   => absint( $row['image_id'] ?? 0 ),
					'image_url'  => esc_url_raw( wp_unslash( $row['image_url'] ?? '' ) ),
					'title'      => sanitize_text_field( wp_unslash( $row['title'] ?? '' ) ),
					'subtitle'   => sanitize_textarea_field( wp_unslash( $row['subtitle'] ?? '' ) ),
					'cta_label'  => sanitize_text_field( wp_unslash( $row['cta_label'] ?? '' ) ),
					'cta_url'    => esc_url_raw( wp_unslash( $row['cta_url'] ?? '' ) ),
					'overlay'    => max( 0, min( 90, absint( $row['overlay'] ?? 50 ) ) ),
					'text_align' => in_array( $row['text_align'] ?? '', [ 'left', 'center', 'right' ], true ) ? $row['text_align'] : 'center',
				];
			}
			update_option( 'alkana_hero_slides', $slides );
			$updated = true;
		}
	}

	$slides = alkana_get_hero_slides();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Hero Slider — Banner Trang chủ', 'alkana' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Quản lý các slide banner hiển thị trên trang chủ. Kéo thả để sắp xếp thứ tự.', 'alkana' ); ?></p>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Đã lưu thành công.', 'alkana' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<p><button type="button" id="alkana-add-slide" class="button button-primary">+ Thêm Slide</button></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'alkana_save_hero_slides', 'alkana_hs_nonce' ); ?>
			<div id="alkana-slides">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<?php alkana_hero_slider_row( $i, $slide ); ?>
				<?php endforeach; ?>
			</div>
			<?php submit_button( __( 'Lưu thay đổi', 'alkana' ) ); ?>
		</form>
	</div>

	<script type="text/template" id="alkana-slide-tpl">
		<?php alkana_hero_slider_row( '__N__', [] ); ?>
	</script>
	<?php
}

/**
 * Output HTML for a single slide row.
 *
 * @param int|string $idx   Numeric index or '__N__' for JS template.
 * @param array      $slide Slide data.
 */
function alkana_hero_slider_row( $idx, array $slide ): void {
	$n        = esc_attr( (string) $idx );
	$img_id   = absint( $slide['image_id'] ?? 0 );
	$img_url  = esc_url( $slide['image_url'] ?? '' );
	$title    = esc_attr( $slide['title'] ?? '' );
	$sub      = esc_textarea( $slide['subtitle'] ?? '' );
	$cta_lbl  = esc_attr( $slide['cta_label'] ?? '' );
	$cta_url  = esc_attr( $slide['cta_url'] ?? '' );
	$overlay  = absint( $slide['overlay'] ?? 50 );
	$align    = in_array( $slide['text_align'] ?? '', [ 'left', 'center', 'right' ], true ) ? $slide['text_align'] : 'center';
	$disp_num = is_numeric( $idx ) ? (int) $idx + 1 : 1;
	?>
	<div class="alkana-slide-row postbox" style="margin-bottom:16px">
		<div class="postbox-header" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #ddd">
			<span class="alkana-drag dashicons dashicons-move" style="cursor:grab;color:#999" title="Kéo để sắp xếp"></span>
			<strong>Slide <span class="slide-num"><?php echo esc_html( (string) $disp_num ); ?></span></strong>
			<button type="button" class="alkana-slide-del button-link" style="margin-left:auto;color:#c00">
				<?php esc_html_e( 'Xóa slide', 'alkana' ); ?>
			</button>
		</div>
		<div class="inside" style="padding:16px;display:flex;gap:24px;align-items:flex-start">
			<div style="min-width:170px;text-align:center">
				<img class="slide-preview" src="<?php echo $img_url; ?>"
					style="width:170px;height:110px;object-fit:cover;border-radius:6px;display:<?php echo $img_url ? 'block' : 'none'; ?>;margin-bottom:8px;border:1px solid #ddd">
				<input type="hidden" class="slide-img-id" name="slides[<?php echo $n; ?>][image_id]" value="<?php echo $img_id; ?>">
				<input type="hidden" class="slide-img-url" name="slides[<?php echo $n; ?>][image_url]" value="<?php echo $img_url; ?>">
				<button type="button" class="alkana-slide-img-btn button">
					<?php echo $img_url ? esc_html__( 'Đổi ảnh', 'alkana' ) : esc_html__( 'Chọn ảnh', 'alkana' ); ?>
				</button>
			</div>
			<table class="form-table" style="margin:0;flex:1">
				<tr>
					<th style="width:120px"><label><?php esc_html_e( 'Tiêu đề', 'alkana' ); ?></label></th>
					<td><input type="text" name="slides[<?php echo $n; ?>][title]" value="<?php echo $title; ?>" class="large-text" maxlength="120"></td>
				</tr>
				<tr>
					<th><label><?php esc_html_e( 'Mô tả', 'alkana' ); ?></label></th>
					<td><textarea name="slides[<?php echo $n; ?>][subtitle]" rows="2" class="large-text" maxlength="250"><?php echo $sub; ?></textarea></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Nút CTA', 'alkana' ); ?></th>
					<td>
						<input type="text" name="slides[<?php echo $n; ?>][cta_label]" value="<?php echo $cta_lbl; ?>" placeholder="<?php esc_attr_e( 'Tên nút (vd: Khám phá)', 'alkana' ); ?>" style="width:180px">
						<input type="url"  name="slides[<?php echo $n; ?>][cta_url]"   value="<?php echo $cta_url; ?>" placeholder="https://..." class="regular-text">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Căn chỉnh', 'alkana' ); ?></th>
					<td>
						<?php foreach ( [ 'left' => 'Trái', 'center' => 'Giữa', 'right' => 'Phải' ] as $val => $label ) : ?>
							<label style="margin-right:12px">
								<input type="radio" name="slides[<?php echo $n; ?>][text_align]" value="<?php echo esc_attr( $val ); ?>"
									<?php checked( $align, $val ); ?>>
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<th><label><?php esc_html_e( 'Độ tối overlay', 'alkana' ); ?></label></th>
					<td>
						<input type="number" name="slides[<?php echo $n; ?>][overlay]" value="<?php echo $overlay; ?>"
							min="0" max="90" style="width:70px"> %
						<span class="description"><?php esc_html_e( '0 = trong suốt, 90 = tối nhất', 'alkana' ); ?></span>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php
}
