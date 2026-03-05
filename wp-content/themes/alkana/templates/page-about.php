<?php
/**
 * Template Name: About Us
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<?php // ── Page Hero ─────────────────────────────────────────────────── ?>
	<section class="page-hero bg-[--color-secondary] text-white py-16">
		<div class="container mx-auto px-4">
			<h1 class="text-3xl font-heading font-bold"><?php the_title(); ?></h1>
		</div>
	</section>

	<div class="container mx-auto px-4 py-12">
		<div class="prose prose-lg max-w-none">
			<?php the_content(); ?>
		</div>
	</div>

<?php endwhile; endif; ?>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
