<?php
/**
 * single.php — Single post template for standard blog posts.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header' );
?>

<main id="main-content" class="site-main">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

	<div class="container mx-auto px-4 pt-4">
		<?php get_template_part( 'template-parts/breadcrumb' ); ?>
	</div>

	<?php // ── Hero banner ─────────────────────────────────────────────── ?>
	<section class="page-hero bg-[--color-secondary] text-white py-14">
		<div class="container mx-auto px-4">
			<div class="flex items-center gap-2 text-sm text-white/60 mb-3">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
				<?php
				$cats = get_the_category();
				if ( $cats ) :
					$cat = $cats[0];
				?>
					<span>·</span>
					<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
					   class="text-white/80 hover:text-white hover:underline">
						<?php echo esc_html( $cat->name ); ?>
					</a>
				<?php endif; ?>
			</div>
			<h1 class="text-3xl md:text-4xl font-heading font-bold leading-tight">
				<?php the_title(); ?>
			</h1>
		</div>
	</section>

	<?php // ── Featured image ──────────────────────────────────────────── ?>
	<?php if ( has_post_thumbnail() ) : ?>
	<div class="container mx-auto px-4 -mt-6">
		<div class="rounded-xl overflow-hidden shadow-lg">
			<?php the_post_thumbnail( 'large', [
				'class'   => 'w-full h-auto object-cover max-h-[480px]',
				'loading' => 'eager',
			] ); ?>
		</div>
	</div>
	<?php endif; ?>

	<?php // ── Article content ─────────────────────────────────────────── ?>
	<article class="container mx-auto px-4 py-12">
		<div class="prose prose-lg max-w-3xl mx-auto">
			<?php the_content(); ?>
		</div>

		<?php // ── Tags ────────────────────────────────────────────────── ?>
		<?php
		$tags = get_the_tags();
		if ( $tags ) :
		?>
		<div class="max-w-3xl mx-auto mt-8 flex flex-wrap gap-2">
			<?php foreach ( $tags as $tag ) : ?>
				<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
				   class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-600 hover:bg-[--color-primary] hover:text-white transition-colors">
					#<?php echo esc_html( $tag->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php // ── Share buttons ────────────────────────────────────────── ?>
		<div class="max-w-3xl mx-auto mt-8">
			<?php get_template_part( 'template-parts/share-buttons' ); ?>
		</div>

		<?php // ── Post navigation ─────────────────────────────────────── ?>
		<nav class="max-w-3xl mx-auto mt-12 pt-8 border-t border-gray-200 flex justify-between gap-4" aria-label="<?php esc_attr_e( 'Post navigation', 'alkana' ); ?>">
			<?php
			$prev = get_previous_post();
			$next = get_next_post();
			?>
			<?php if ( $prev ) : ?>
			<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="flex-1 group">
				<span class="text-xs text-gray-400 uppercase"><?php esc_html_e( 'Previous', 'alkana' ); ?></span>
				<p class="text-sm font-medium text-[--color-secondary] group-hover:text-[--color-primary] mt-1 line-clamp-2">
					<?php echo esc_html( $prev->post_title ); ?>
				</p>
			</a>
			<?php else : ?>
			<div class="flex-1"></div>
			<?php endif; ?>

			<?php if ( $next ) : ?>
			<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="flex-1 text-right group">
				<span class="text-xs text-gray-400 uppercase"><?php esc_html_e( 'Next', 'alkana' ); ?></span>
				<p class="text-sm font-medium text-[--color-secondary] group-hover:text-[--color-primary] mt-1 line-clamp-2">
					<?php echo esc_html( $next->post_title ); ?>
				</p>
			</a>
			<?php endif; ?>
		</nav>
	</article>

<?php endwhile; endif; ?>
</main>

<?php get_template_part( 'template-parts/footer' ); ?>
