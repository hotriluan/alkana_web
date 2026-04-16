<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-alkana-purple-600 text-white px-4 py-2 z-50 rounded">
	<?php esc_html_e( 'Skip to content', 'alkana' ); ?>
</a>

<?php get_template_part( 'template-parts/topbar' ); ?>

<header class="site-header sticky top-0 z-[--z-nav] bg-white/95 backdrop-blur-xl border-b border-gray-100 shadow-sm transition-all duration-300" id="site-header" style="--header-height:80px">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">

			<?php // ── Logo ──────────────────────────────────────────────────────── ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo flex items-center gap-2 shrink-0" rel="home">
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, [ 160, 48 ], false, [ 'class' => 'site-logo__img h-10 w-auto transition-[filter] duration-300' ] );
				} else {
					echo '<span class="text-2xl font-extrabold text-alkana-purple-900 tracking-tight transition-colors duration-300">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
				}
				?>
			</a>

			<?php // ── Desktop Nav ───────────────────────────────────────────────── ?>
			<nav class="site-nav hidden md:flex items-center gap-1" aria-label="<?php esc_attr_e( 'Primary', 'alkana' ); ?>">
				<?php
				wp_nav_menu( [
					'theme_location' => 'primary',
					'menu_class'     => 'nav-menu flex items-center space-x-1',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
					'walker'         => new Alkana_Mega_Menu_Walker(),
				] );
				?>
			</nav>

			<?php // ── Right actions: Search + CTA + Mobile Menu ────────────────── ?>
			<div class="flex items-center gap-2">
				<button class="search-toggle p-2 text-alkana-purple-700 hover:text-alkana-purple-500 transition-colors" id="search-toggle" aria-label="<?php esc_attr_e( 'Search', 'alkana' ); ?>">
					<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
					</svg>
				</button>

				<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"
				   class="header-cta btn btn--gradient hidden sm:inline-flex text-sm py-2 px-4">
					<?php esc_html_e( 'Nhận báo giá', 'alkana' ); ?>
				</a>

				<button
					class="nav-toggle md:hidden flex flex-col gap-1.5 p-2 ml-1"
					id="nav-toggle"
					aria-label="<?php esc_attr_e( 'Open menu', 'alkana' ); ?>"
					aria-expanded="false"
					aria-controls="nav-drawer">
					<span class="block w-5 h-0.5 bg-alkana-purple-800 transition-all duration-300"></span>
					<span class="block w-5 h-0.5 bg-alkana-purple-800 transition-all duration-300"></span>
					<span class="block w-5 h-0.5 bg-alkana-purple-800 transition-all duration-300"></span>
				</button>
			</div>

	</div>

	<?php // ── Mobile Nav Drawer ─────────────────────────────────────────────── ?>
	<div class="nav-drawer fixed inset-y-0 right-0 w-80 max-w-full bg-white z-[--z-drawer] flex-col shadow-2xl transform translate-x-full transition-transform duration-300 ease-out"
		 id="nav-drawer"
		 aria-hidden="true">
		<div class="nav-drawer__header flex items-center justify-between px-5 h-16 border-b border-gray-100">
			<span class="font-heading font-bold text-alkana-purple-900"><?php esc_html_e( 'Menu', 'alkana' ); ?></span>
			<button class="nav-close w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-600 text-xl leading-none" id="nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'alkana' ); ?>">
				<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>
		<div class="nav-drawer__body overflow-y-auto py-4 px-5 flex-1">
			<?php
			wp_nav_menu( [
				'theme_location' => 'mobile',
				'menu_class'     => 'nav-drawer-menu flex flex-col gap-1',
				'container'      => false,
				'depth'          => 2,
				'fallback_cb'    => false,
			] );
			?>
		</div>
		<div class="nav-drawer__footer p-5 border-t border-gray-100">
			<a href="<?php echo esc_url( home_url( '/lien-he/' ) ); ?>"
			   class="btn btn--gradient w-full text-center">
				<?php esc_html_e( 'Nhận báo giá', 'alkana' ); ?>
			</a>
		</div>
	</div>
	<div class="nav-overlay fixed inset-0 bg-black/40 z-[calc(var(--z-drawer)-1)] hidden" id="nav-overlay" aria-hidden="true"></div>

</header>
