<?php

/*
 * Controls settings pages and retrieval.
 * @package Sync
 * @author Dave Jesch
 */
 
//require_once(dirname(__FILE__) . '/contextual-help.php');
 
class SyncSettings
{
	private static $_instance = NULL;
	private $_options = array();

	const SETTINGS_PAGE = 'sync';		// TODO: update name

	private function __construct()
	{
		add_action('admin_menu', array(&$this, 'add_configuration_page'));
		add_action('admin_init', array(&$this, 'settings_api_init'));
		add_action('load-settings_page_sync', array(&$this, 'contextual_help'));

		$this->_options = SyncOptions::get_all();
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

	/*
	 * Returns an option from the `spectrom_sync_settings` option
	 *
	 * @param string $option The key for the option under OPTION_KEY
	 * @param string $default (optional) The default value to be returned
	 *
	 * @return mixed The value if it exists, else $default
	 */
	public function get_option($option, $default = NULL)
	{
		return isset($this->_options[$option]) ? $this->_options[$option] : $default;
	}

	/**
	 * Adds the Sync settings menu to the Setting section.
	 */
	public function add_configuration_page()
	{
		$slug = add_submenu_page(
			'options-general.php',
			__('WPSiteSync for Content Settings', 'wpsitesynccontent'),
			__('WPSiteSync for Content', 'wpsitesynccontent'),		// displayed in menu
			'manage_options',							// capability
			self::SETTINGS_PAGE,						// menu slug
			array(&$this, 'settings_page')				// callback
		);
		return $slug;
	}

	/**
	 * Callback to display contents of settings page
	 */
	public function settings_page()
	{
		add_filter('admin_footer_text', array(&$this, 'footer_content'));
		add_action('spectrom_page', array(&$this, 'show_settings_page'));
		do_action('spectrom_page');
	}

	/**
	 * Echo the Sync settings page and enqueues needed scripts/styles.
	 */
	public function show_settings_page()
	{
		wp_enqueue_script('sync-settings');
		wp_enqueue_style('font-awesome');

		do_action('spectrom_sync_before_render_settings');

		echo '<div class="wrap spectrom-sync-settings">';
		echo '<h1 class="nav-tab-wrapper">';
		echo '<a class="nav-tab nav-tab-active" title="', __('General', 'wpsitesynccontent'), '" href="/wp-admin/edit.php?post_type=sync&page=sync-settings&tab=general">',
			__('General', 'wpsitesynccontent'), '</a>';
		echo '</h1>';
		echo '</div>';
		echo '<div id="tab_container" class="spectrom-sync-settings">';
		
		echo '<form id="form-spectrom-sync" action="options.php" method="POST">';
			settings_errors();
			settings_fields('sync_options_group');
			do_settings_sections('sync'); 
			submit_button(); 
		echo '</form>';
		echo '<p>', __('WPSiteSync for Content Site key: ', 'wpsitesynccontent'), '<b>', $this->get_option('site_key'), '</b></p>';
		echo '</div></div><!-- .wrap -->';

	}

	/**
	 * Registers the setting sections and fields to be used by Sync.
	 */
	public function settings_api_init()
	{
		$option_values = $this->_options;

		$default_values = apply_filters('spectrom_sync_default_settings', 
			array(
				'host' => '',
				'username' => '',
				'password' => '',
				'auth' => 0,
				'strict' => '1',
				'salt' => '',
				'min_role' => '',
			)
		);

		// Parse option values into predefined keys, throw the rest away.
		$data = shortcode_atts($default_values, $option_values);

		$section_id = 'sync_section';

		register_setting(
			'sync_options_group',						// option group, used for settings_fields()
			SyncOptions::OPTION_NAME,					// option name, used as key in database
			array(&$this, 'validate_settings')			// validation callback
		);

		add_settings_section(
			$section_id,									// id
			__('WPSiteSync for Content - Configuration:', 'wpsitesynccontent'),	// title
			'__return_true',								// callback
			self::SETTINGS_PAGE								// option page
		);

/*		if ('' === $data['host']) {
			add_settings_field(
				'showtarget',								// field id
				__('Do you need to add a Target?', 'wpsitesynccontent'),
				array(&$this, 'render_button_field'),		// callback
				self::SETTINGS_PAGE,						// page
				$section_id,								// section id
				array(										// args
					'name' => 'showtarget',
					'title' => __('Create Target', 'wpsitesynccontent'),
					'message' => __('Click to add settings for Target site.', 'wpsitesynccontent'),
				)
			);
		} */

		add_settings_field(
			'host',											// field id
			__('Host Name of Target:', 'wpsitesynccontent'),// title
			array(&$this, 'render_input_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section id
			array(											// args
				'name' => 'host',
				'value' => $data['host'],
				'size' => '50',
			)
		);

		add_settings_field(
			'username',										// field id
			__('Username on Target:', 'wpsitesynccontent'),	// title
			array(&$this, 'render_input_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section id
			array(											// args
				'name' => 'username',
				'size' => '50',
				'value' => $data['username']
			)
		);

		add_settings_field(
			'password',										// field id
			__('Password on Target:', 'wpsitesynccontent'),	// title
			array(&$this, 'render_password_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section
			array(											// args
				'name' => 'password',
				'value' => '', // Always empty
				'size' => '50',
				'auth' => $data['auth']
			)
		);

		$section_id = 'sync_behaviors';
		add_settings_section(
			$section_id,									// id
			__('WPSiteSync for Content - Behaviors:', 'wpsitesynccontent'),		// title
			'__return_true',								// callback
			self::SETTINGS_PAGE								// option page
		);

		add_settings_field(
			'strict',										// field id
			__('Strict Mode:', 'wpsitesynccontent'),		// title
			array(&$this, 'render_radio_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section id
			array(											// args
				'name' => 'strict',
				'value' => $data['strict'],
				'options' => array(
							'1' => __('WordPress and WPSiteSync for Content versions must match on Source and Target in order to perform SYNCs.', 'wpsitesynccontent'),
							'0' => __('WordPress and WPSiteSync for Content versions do not need to match.', 'wpsitesynccontent'),
				),
			)
		);

/*
		add_settings_field(
			'salt',											// field id
			__('Authentication Salt:', 'wpsitesynccontent'),// title
			array(&$this, 'render_input_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section id
			array(
				'name' => 'salt',
				'value' => $data['salt'],
				'size' => '50',
				'description' => __('If blank, will use default value. If filled in, same value needs to be configured on Target System.', 'wpsitesynccontent'),
			)
		); */

/*
		add_settings_field(
			'min_role',										// field id
			__('Minimum Role allowed to Sync content:', 'wpsitesynccontent'),	// title
			array(&$this, 'render_select_field'),			// callback
			self::SETTINGS_PAGE,							// page
			$section_id,									// section id
			array(
				'name' => 'min_role',
				'value' => $data['min_role'],
				'options' => array('admin' => __('Administrator', 'wpsitesynccontent'),
					'editor' => __('Editor', 'wpsitesynccontent'),
					'author' => __('Author', 'wpsitesynccontent')
					)
			)
		); */

		do_action('spectrom_sync_register_settings', $data);
	}

	/**
	 * Renders an input field control
	 * @param array $args Array of arguments, contains name and value.
	 */
	public function render_input_field($args)
	{
		$attrib = '';
		if (isset($args['size']))
			$attrib = ' size="' . esc_attr($args['size']) . '" ';
		if (!empty($args['class']))
			$attrib .= ' class="' . esc_attr($args['class']) . '" ';

		printf('<input type="text" id="spectrom-form-%s" name="spectrom_sync_settings[%s]" value="%s" %s />',
			$args['name'], $args['name'], esc_attr($args['value']), $attrib);

		if (!empty($args['description']))
			echo '<p><em>', esc_html($args['description']), '</em></p>';
	}

	/**
	 * Renders a <select> field and it's <option> elements
	 * @param array $args Array of arguments, contains name, value and options data
	 */
	public function render_select_field($args)
	{
		printf('<select id="spectrom-form-%s" name="spectrom_sync_settings[%s]" value="%s">',
			$args['name'], $args['name'], esc_attr($args['value']));
		foreach ($args['options'] as $key => $value) {
			echo '<option value="', esc_attr($key), '">', esc_html($value), '</option>';
		}
		echo '</select>';
	}

	/**
	 * Renders the radio buttons used for the control
	 * @param array $args Arguments used to render the radio buttons
	 */
	public function render_radio_field($args)
	{
		$options = $args['options'];
		$name = $args['name'];

		foreach ($options as $value => $label) {
			printf('<input type="radio" name="spectrom_sync_settings[%s]" value="%s" %s /> %s',
				$name, $value, checked($value, $args['value'], FALSE), $label);
			echo '<br>';
		}
		if (isset($args['description']))
			echo '<br/><em>', $args['description'], '</em>';
	}

	/**
	 * Renders the <button> field
	 * @param array $args Arguments used to render the button
	 */
	public function render_button_field($args)
	{
		echo '<button type="button" id="spectrom-button-', $args['name'], '" class="button-primary spectrom-ui-button">', $args['title'], '</button>';
		if (!empty($args['message']))
			echo '<p>', $args['message'], '</p>';
	}

	/**
	 * Echoes the password field with the connection success indicator.
	 * @param array $args Array of arguments, contains name and value.
	 */
	public function render_password_field($args)
	{
		$attrib = '';
		if (isset($args['size']))
			$attrib = ' size="' . esc_attr($args['size']) . '" ';
			
		printf('<input type="password" id="spectrom-form-%s" name="spectrom_sync_settings[%s]" value="%s" %s />',
			$args['name'], $args['name'], esc_attr($args['value']), $attrib);
		echo '<i id="connect-success-indicator" class="fa ', ($args['auth'] ? 'fa-check' : 'fa-close'), '"';
		echo ' title="';
		if ($args['auth'])
			echo esc_attr(__('Settings authenticated on Target server', 'wpsitesynccontent'));
		else
			echo esc_attr(__('Settings do not authenticate on Target server', 'wpsitesynccontent'));
		echo '"></i>';
	}

	/**
	 * Validates the values and forms the spectrom_sync_settings array
	 * @param  array $values The submitted form values.
	 * @return array
	 */
	public function validate_settings($values)
	{
		$settings = $this->_options;

SyncDebug::log(__METHOD__.'() settings: ' . var_export($settings, TRUE));

		// Merge so that site_key value is preserved on update. const OPTION_NAME = 'spectrom_sync_settings';
		$out = array_merge($settings, array());

		foreach ($values as $key => $value) {
SyncDebug::log("  key={$key}  value=[{$value}]");
			if (empty($values[$key]) && 'password' === $key) {
				$out[$key] = $settings[$key];
			} else {
				if ('host' === $key && FALSE === filter_var($value, FILTER_VALIDATE_URL)) {
					add_settings_error('sync_options_group', 'invalid-url', __('Invalid URL.', 'wpsitesynccontent'));
					$out[$key] = $settings[$key];
				} else if (0 === strlen(trim($value))) {
					add_settings_error('sync_options_group', 'missing-field', __('All fields are required.', 'wpsitesynccontent'));
					$out[$key] = $settings[$key];
				} else {
					$out[$key] = $value;
				}
			}
		}
SyncDebug::log(__METHOD__.'() output array: ' . var_export($out, TRUE));

//		$auth = new SyncAuth();
//		$out['password'] = $auth->encode_password($out['password'], $out['host']);

		// authenticate
		if (!empty($out['password'])) {
			$out['auth'] = 0;
//			SyncOptions::set('host', $out['host']);
//			SyncOptions::set('username', $out['username']);
//			SyncOptions::set('password', $out['password']);

			$api = new SyncApiRequest();
			$res = $api->api('auth', $out);
			if (!is_wp_error($res)) {
SyncDebug::log(__METHOD__.'() response from auth request: ' . var_export($res, TRUE));
				if (isset($res->response->success) && $res->response->success) {
					$out['auth'] = 1;
SyncDebug::log(__METHOD__.'() got token: ' . $res->response->data->token);
				} else {
SyncDebug::log(__METHOD__.'() bad password response from Target');
				}
			}
			// remove ['password'] element from $values since we now have a token
			unset($out['password']);
//			if (0 === $res->error_code)
//				$out['auth'] = 1;
		}

		return apply_filters('spectrom_sync_validate_settings', $out, $values);
	}

	/**
	 * Callback for adding contextual help to Sync Settings page
	 */
	public function contextual_help()
	{
		$screen = get_current_screen();
		if ('settings_page_sync' !== $screen->id)
			return;

		$screen->set_help_sidebar(
			'<p><strong>' . __('For more information:', 'wpsitesynccontent') . '</strong></p>' .
			'<p>' . sprintf(__('Visit the <a href="%s" target="_blank">documentation</a> on the WPSiteSync for Content website.', 'wpsitesynccontent'),
						esc_url('https://wpsitesync.com/documentation/')) . '</p>' .
			'<p>' . sprintf(
						__('<a href="%s" target="_blank">Post an issue</a> on <a href="%s" target="_blank">GitHub</a>.', 'wpsitesynccontent'),
						esc_url('https://github.com/ServerPress/sync/issues'),
						esc_url('https://github.com/ServerPress/sync/')) .
			'</p>'
		);

		$screen->add_help_tab(array(
			'id'	    => 'sync-settings-general',
			'title'	    => __('General', 'wpsitesynccontent'),
			'content'	=>
				'<p>' . __('This page allows you to configure how WPSiteSync for Content behaves.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<strong>Host Name of Target</strong>: Enter the URL of the Target website you wish to Sync with.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<strong>Username on Target</strong>: Enter the Administrator username for the Target website.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<strong>Password on Target</strong>: Enter the Administrator password for the Target website.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<strong>Strict Mode:</strong>: Select if WordPress and WPSiteSync for Content should be the same versions on the Source and the Target.', 'wpsitesynccontent') . '</p>'
//				'<p>' . __('<strong>Authentication Salt:</strong>: Enter a salt to use when Content is sent to current site or leave blank.', 'wpsitesynccontent') . '</p>' .
//				'<p>' . __('<strong>Minimum Role allowed to SYNC Content</strong>: Select minimum role of user who can Sync Content to current site.', 'wpsitesynccontent') . '</p>'
		));
		$screen->add_help_tab(array(
			'id'		=> 'sync-settings-terms',
			'title'		=> __('Terms/Definitions', 'wpsitesynccontent'),
			'content'	=>
				'<p>' . __('<b>Source</b> - The website that Content is being Syncd from. This is the non-authority or development/staging site.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<b>Target</b> - The website that you will be Pushing/Syncing Content to. This is the authoritative or live site.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<b>Push</b> - Moving Content from the Source to the Target website.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<b>Pull</b> - Moving Content from the Target to the Source website.', 'wpsitesynccontent') . '</p>' .
				'<p>' . __('<b>Content</b> - The data that is being Syncd between websites. This can be Posts, Pages or Custom Post Types, User Information, Comments, and more, depending on the Sync Add-ons that you have installed.', 'wpsitesynccontent') . '</p>'
		));

		do_action('spectrom_sync_contextual_help', $screen);
	}

	/**
	 * Callback for modifying the footer text on the Sync settings page
	 * @param string $footer_text Original footer text
	 * @return string Modified text, with links to Sync pages
	 */
	public function footer_content($footer_text)
	{
		$rate_text = sprintf(__('Thank you for using <a href="%1$s" target="_blank">WPSiteSync for Content</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'wpsitesynccontent'),
			esc_url('https://wpsitesync.com'),
			esc_url('https://wordpress.org/support/view/plugin-reviews/wpsitesynccontent?filter=5#postform')
		);

		return str_replace('</span>', '', $footer_text) . ' | ' . $rate_text . '</span>';
	}
}

// EOF
