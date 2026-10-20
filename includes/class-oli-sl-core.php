<?php
/**
 * The core plugin class.
 *
 * @package Oli_Store_Locator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 */
class Oli_SL_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var Oli_SL_Loader
	 */
	protected $loader;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies.
	 */
	private function load_dependencies() {
		require_once OLI_SL_PLUGIN_DIR . 'includes/oli-sl-functions.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-loader.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-i18n.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-csv-parser.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-sync-diff.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-map-styles.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-post-type.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-taxonomies.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-geocoding.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-geocoding-google.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-geocoding-nominatim.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-search.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-ajax.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-rest-api.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-shortcodes.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-csv-handler.php';
		require_once OLI_SL_PLUGIN_DIR . 'includes/class-oli-sl-google-sheets-sync.php';
		require_once OLI_SL_PLUGIN_DIR . 'admin/class-oli-sl-admin.php';
		require_once OLI_SL_PLUGIN_DIR . 'admin/class-oli-sl-settings.php';
		require_once OLI_SL_PLUGIN_DIR . 'admin/class-oli-sl-metaboxes.php';
		require_once OLI_SL_PLUGIN_DIR . 'public/class-oli-sl-public.php';

		$this->loader = new Oli_SL_Loader();
	}

	/**
	 * Define the locale for internationalization.
	 */
	private function set_locale() {
		$plugin_i18n = new Oli_SL_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all admin-related hooks.
	 */
	private function define_admin_hooks() {
		$post_type  = new Oli_SL_Post_Type();
		$taxonomies = new Oli_SL_Taxonomies();
		$admin      = new Oli_SL_Admin();
		$settings   = new Oli_SL_Settings();
		$metaboxes  = new Oli_SL_Metaboxes();

		$this->loader->add_action( 'init', $post_type, 'register_post_type' );
		$this->loader->add_action( 'init', $taxonomies, 'register_taxonomies' );
		$this->loader->add_action( 'admin_menu', $settings, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $settings, 'register_settings' );
		$this->loader->add_action( 'admin_init', $admin, 'handle_admin_actions' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'add_meta_boxes', $metaboxes, 'add_meta_boxes' );
		$this->loader->add_action( 'save_post_oli_store', $metaboxes, 'save_meta_boxes', 10, 2 );
		$this->loader->add_filter( 'manage_oli_store_posts_columns', $admin, 'store_columns' );
		$this->loader->add_action( 'manage_oli_store_posts_custom_column', $admin, 'store_column_content', 10, 2 );
		$this->loader->add_action( 'oli_store_category_add_form_fields', $taxonomies, 'add_category_marker_field' );
		$this->loader->add_action( 'oli_store_category_edit_form_fields', $taxonomies, 'edit_category_marker_field', 10, 2 );
		$this->loader->add_action( 'created_oli_store_category', $taxonomies, 'save_category_marker_field' );
		$this->loader->add_action( 'edited_oli_store_category', $taxonomies, 'save_category_marker_field' );
		$this->loader->add_action( 'wp_ajax_oli_sl_geocode_address', $admin, 'geocode_address' );
		$this->loader->add_action( 'wp_ajax_oli_sl_detect_sheet_columns', $admin, 'detect_sheet_columns' );
		$this->loader->add_action( 'wp_ajax_oli_sl_sync_now', $admin, 'sync_now' );
	}

	/**
	 * Register all public-facing hooks.
	 */
	private function define_public_hooks() {
		$plugin_public = new Oli_SL_Public();
		$ajax          = new Oli_SL_Ajax();
		$rest_api      = new Oli_SL_Rest_API();
		$shortcodes    = new Oli_SL_Shortcodes();
		$sheets        = new Oli_SL_Google_Sheets_Sync();

		$this->loader->add_filter( 'cron_schedules', $this, 'cron_schedules' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'register_assets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'maybe_enqueue' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'output_custom_css' );
		$this->loader->add_action( 'wp_ajax_oli_sl_search_stores', $ajax, 'search_stores' );
		$this->loader->add_action( 'wp_ajax_nopriv_oli_sl_search_stores', $ajax, 'search_stores' );
		$this->loader->add_action( 'wp_ajax_oli_sl_autocomplete', $ajax, 'autocomplete' );
		$this->loader->add_action( 'wp_ajax_nopriv_oli_sl_autocomplete', $ajax, 'autocomplete' );
		$this->loader->add_action( 'rest_api_init', $rest_api, 'register_routes' );
		$this->loader->add_action( 'init', $shortcodes, 'register_shortcodes' );
		$this->loader->add_action( 'init', $shortcodes, 'register_blocks' );
		$this->loader->add_filter( 'template_include', $plugin_public, 'single_template' );
		$this->loader->add_action( 'oli_sl_sync_google_sheets_5min', $sheets, 'run_scheduled_sync' );
		$this->loader->add_action( 'oli_sl_sync_google_sheets_15min', $sheets, 'run_scheduled_sync' );
		$this->loader->add_action( 'oli_sl_sync_google_sheets_hourly', $sheets, 'run_scheduled_sync' );
		$this->loader->add_action( 'oli_sl_sync_google_sheets_daily', $sheets, 'run_scheduled_sync' );
		$this->loader->add_action( 'oli_sl_sync_google_sheets_batch', $sheets, 'process_batch' );
		$this->loader->add_action( 'save_post_oli_store', $this, 'clear_search_cache' );
		$this->loader->add_action( 'deleted_post', $this, 'clear_search_cache' );
	}

	/**
	 * Add custom cron schedules.
	 *
	 * @param array $schedules Existing schedules.
	 * @return array
	 */
	public function cron_schedules( $schedules ) {
		return oli_sl_cron_schedules( $schedules );
	}

	/**
	 * Clear cached search results when stores change.
	 */
	public function clear_search_cache() {
		Oli_SL_Search::clear_cache();
	}

	/**
	 * Run the loader to execute all hooks.
	 */
	public function run() {
		$this->loader->run();
	}
}
