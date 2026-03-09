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

<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-[--color-primary] text-white px-4 py-2 z-50 rounded">
	<?php esc_html_e( 'Skip to content', 'alkana' ); ?>
</a>

<header class="site-header sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm transition-all" id="site-header">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-20">

			<?php // ── Logo ──────────────────────────────────────────────────────── ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo flex items-center gap-2" rel="home">
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, [ 160, 48 ], false, [ 'class' => 'site-logo__img h-10 w-auto' ] );
				} else {
					echo '<span class="text-2xl font-extrabold text-[#1A3A5C] tracking-tight">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
				}
				?>
			</a>

			<?php // ── Desktop Nav ───────────────────────────────────────────────── ?>
			<nav class="site-nav hidden md:flex items-center gap-6" aria-label="<?php esc_attr_e( 'Primary', 'alkana' ); ?>">
				<?php
				wp_nav_menu( [
					'theme_location' => 'primary',
					'menu_class'     => 'nav-menu flex items-center space-x-8',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				] );
				?>
			</nav>

			<?php // ── Mobile Hamburger ──────────────────────────────────────────── ?>
			<button
				class="nav-toggle lg:hidden flex flex-col gap-1 p-2"
				id="nav-toggle"
				aria-label="<?php esc_attr_e( 'Open menu', 'alkana' ); ?>"
				aria-expanded="false"
				aria-controls="nav-drawer">
				<span class="block w-5 h-0.5 bg-[--color-secondary]"></span>
				<span class="block w-5 h-0.5 bg-[--color-secondary]"></span>
				<span class="block w-5 h-0.5 bg-[--color-secondary]"></span>
			</button>

	</div>

	<?php // ── Mobile Nav Drawer ─────────────────────────────────────────────── ?>
	<div class="nav-drawer hidden fixed inset-0 bg-white z-[--z-drawer] flex-col"
		 id="nav-drawer"
		 aria-hidden="true">
		<div class="nav-drawer__header flex items-center justify-between px-4 h-16 border-b">
			<span class="font-heading font-bold text-[--color-secondary]"><?php esc_html_e( 'Menu', 'alkana' ); ?></span>
			<button class="nav-close" id="nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'alkana' ); ?>">×</button>
		</div>
		<div class="nav-drawer__body overflow-y-auto p-4">
			<?php
			wp_nav_menu( [
				'theme_location' => 'mobile',
				'menu_class'     => 'nav-drawer-menu flex flex-col gap-2',
				'container'      => false,
				'depth'          => 2,
				'fallback_cb'    => false,
			] );
			?>
		</div>
	</div>

</header>
