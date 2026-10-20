<?php
/**
 * Settings page shell.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base = admin_url( 'edit.php?post_type=oli_store&page=oli-sl-settings' );
?>
<div class="wrap oli-sl-settings">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors( 'oli_sl_options' ); ?>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', $slug, $base ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</h2>

	<?php if ( in_array( $tab, array( 'import', 'sync' ), true ) ) : ?>
		<?php include OLI_SL_PLUGIN_DIR . 'admin/views/tab-' . $tab . '.php'; ?>
	<?php else : ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'oli_sl_settings_group' ); ?>
			<?php include OLI_SL_PLUGIN_DIR . 'admin/views/tab-' . $tab . '.php'; ?>
			<?php submit_button(); ?>
		</form>
	<?php endif; ?>
</div>
