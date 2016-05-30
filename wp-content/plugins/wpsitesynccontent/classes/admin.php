<?php

/*
 * Controls post edit page and activation.
 * @package Sync
 * @author Dave Jesch
 */
class SyncAdmin
{
	private static $_instance = NULL;

	private function __construct()
	{
		// Hook here, admin_notices won't work on plugin activation since there's a redirect.
		add_action('admin_notices', array(&$this, 'configure_notice'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		add_action('add_meta_boxes', array(&$this, 'add_sync_metabox'));
		add_filter('plugin_action_links_wpsitesynccontent/wpsitesynccontent.php', array(&$this, 'plugin_action_links'));

		SyncSettings::get_instance();		
	}

	/*
	 * retrieve singleton class instance
	 * @return instance reference to plugin
	 */
	public static function get_instance()
	{
		if (NULL === self::$_instance)
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Displays the configuration prompt after activating the plugin.
	 */
	public function configure_notice()
	{
		if (1 != get_option('spectrom_sync_activated')) {
			// Make sure this runs only once.
			add_option('spectrom_sync_activated', 1);
			$notice = __('You just installed WPSiteSync for Content and it needs to be configured. Please go to the <a href="%s">WPSiteSync for Content Settings page</a>.', 'wpsitesynccontent');
			echo '<div class="update-nag fade">';
			printf($notice, admin_url('options-general.php?page=sync'));
			echo '</div>';
		}
	}

	/**
	 * Registers js and css to be used.
	 */
	public function admin_enqueue_scripts($hook_suffix)
	{
		wp_register_script('sync', WPSiteSyncContent::get_asset('js/sync.js'), array('jquery'), WPSiteSyncContent::PLUGIN_VERSION, TRUE);
		wp_register_script('sync-settings', WPSiteSyncContent::get_asset('js/settings.js'), array('jquery'), WPSiteSyncContent::PLUGIN_VERSION, TRUE);

		wp_register_style('sync-admin', WPSiteSyncContent::get_asset('css/sync-admin.css'), array(), WPSiteSyncContent::PLUGIN_VERSION, 'all');
		wp_register_style('font-awesome', WPSiteSyncContent::get_asset('css/font-awesome.min.css'), array(), WPSiteSyncContent::PLUGIN_VERSION, 'all');

		$screen = get_current_screen();
		// load resources only on Sync settings page or page/post editor
		if ('post' === $screen->id || 'page' === $screen->id ||
			'settings_page_sync' === $screen->id) {
			if ('post.php' === $hook_suffix && 'add' !== $screen->action)
				wp_enqueue_script('sync');

			$option_data = array('site_key' => WPSiteSyncContent::get_option('site_key'));
			wp_localize_script('sync-settings', 'syncdata', $option_data);

			wp_enqueue_style('sync-admin');
		}
	}

	/**
	 * Adds the metabox with the Sync button on post and page edit screens.
	 */
	public function add_sync_metabox($post_type)
	{
		$target = SyncOptions::get('host', NULL);

		if (!empty($target)) {
			$screen = get_current_screen();
			$post_types = apply_filters('spectrom_sync_allowed_post_types', array('post', 'page'));     //limit meta box to certain post types
			if (in_array($post_type, $post_types) &&
				'add' !== $screen->action) {		// don't display metabox while adding content
				add_meta_box(
					'spectrom_sync',				// TODO: update name
					__('WPSiteSync for Content', 'wpsitesynccontent'),
					array(&$this, 'render_sync_metabox'),
					$post_type,
					'side',
					'high');
			}
		}
	}

	/**
	 * Display the Sync button on edit pages.
	 */
	public function render_sync_metabox()
	{
		$api = new SyncApiRequest();
		$e = $api->api('auth'); // $api->auth();

		$error = FALSE;
		if (is_wp_error($e)) {
			$notice = __('WPSiteSync for Content has invalid or missing settings. Please go the the <a href="%s">settings page</a> to update the configuration.', 'wpsitesynccontent');
			echo '<p>', sprintf($notice, admin_url('options-general.php?page=sync')), '</p>';
			$error = TRUE;
		}

		if (!$error)
			echo '<p>', sprintf(__('Push content to Target site: <span title="%2$s"><b>%1$s</b></span>', 'wpsitesynccontent'),
				WPSiteSyncContent::get_option('host'),
				esc_attr(__('The "Target" is the WP install that the Content will be pushed to.', 'wpsitesynccontent'))),
				'</p>';

		global $post;
		do_action('spectrom_sync_metabox_before_button', $error);

		echo '<button id="sync-content" type="button" class="button button-primary btn-sync" onclick="wpsitesynccontent.push(', $post->ID, ')" ';
		if ($error)
			echo ' disabled';
		echo ' title="', __('Push this Content to the Target site', 'wpsitesynccontent'), '" ';
		echo '>';
		echo '<span>', __('WPSiteSync for Content', 'wpsitesynccontent'), '</span>';
		echo '<span id="sync-content-anim" style="display:none"> <img src="', WPSiteSyncContent::get_asset('imgs/ajax-loader.gif'), '" /></span>';
		echo '</button>';

		do_action('spectrom_sync_metabox_after_button', $error);

		echo '<p id="sync-message"></p>';
		echo '<p id="disabled-notice-sync" style="display:none;"><b>', __('Please UPDATE your changes in order to SYNC.', 'wpsitesynccontent'), '</b></p>';

		wp_nonce_field('sync', '_sync_nonce');

		echo '<div style="display:none">';
		echo '<div id="sync-working-msg"><img src="', WPSiteSyncContent::get_asset('imgs/ajax-loader.gif'), '" />', '</div>';
		echo '<div id="sync-success-msg">', __('Content successfully sent to Target system.', 'wpsitesynccontent'), '</div>';
		echo '</div>';
	}

	/**
	 * Filter for adding a 'settings' link in the list of plugins
	 * @param array $actions The list of available actions
	 * @return array The modified actions list
	 */
	public function plugin_action_links($actions)
	{
		$actions[] = sprintf('<a href="%1$s">%2$s</a>', admin_url('options-general.php?page=sync' ), __('Settings', 'wpsitesynccontent'));
		return $actions;
	}
}

// EOF
