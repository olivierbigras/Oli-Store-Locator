<?php
/**
 * Single store template.
 *
 * Copy to yourtheme/oli-store-locator/single-store.php
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$store_id = get_the_ID();
	Oli_SL_Public::enqueue_locator_assets();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'oli-sl-single' ); ?>>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="oli-sl-single-image"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>
		<div class="oli-sl-single-grid">
			<div class="oli-sl-single-details">
				<?php echo do_shortcode( '[oli_store_address id="' . (int) $store_id . '"]' ); ?>
				<?php
				$phone = oli_sl_get_store_meta( $store_id, 'phone' );
				$email = oli_sl_get_store_meta( $store_id, 'email' );
				$url   = oli_sl_get_store_meta( $store_id, 'url' );
				$fax   = oli_sl_get_store_meta( $store_id, 'fax' );
				if ( $phone ) {
					echo '<p><a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a></p>';
				}
				if ( $fax ) {
					echo '<p>' . esc_html__( 'Fax:', 'oli-store-locator' ) . ' ' . esc_html( $fax ) . '</p>';
				}
				if ( $email ) {
					echo '<p><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
				}
				if ( $url ) {
					echo '<p><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Website', 'oli-store-locator' ) . '</a></p>';
				}
				echo do_shortcode( '[oli_store_hours id="' . (int) $store_id . '"]' );
				?>
				<div class="oli-sl-single-content"><?php the_content(); ?></div>
			</div>
			<div class="oli-sl-single-map-wrap">
				<?php echo do_shortcode( '[oli_store_map id="' . (int) $store_id . '" height="400px"]' ); ?>
			</div>
		</div>
	</article>
	<?php
endwhile;

get_footer();
