<?php

/**
 * Sends requests to the API on the Target
 */
class SyncApiRequest implements SyncApiHeaders
{
	const ERROR_CANNOT_CONNECT = 1;
	const ERROR_UNRECOGNIZED_REQUEST = 2;
	const ERROR_NOT_INSTALLED = 3;
	const ERROR_BAD_CREDENTIALS = 4;
	const ERROR_SESSION_EXPIRED = 5;
	const ERROR_CONTENT_EDITING = 6;			// TODO: add checks in SyncApiController
	const ERROR_CONTENT_LOCKED = 7;				// TODO: add checks in SyncApiController
	const ERROR_POST_DATA_INCOMPLETE = 8;
	const ERROR_USER_NOT_FOUND = 9;
	const ERROR_FILE_UPLOAD = 10;
	const ERROR_PERMALINK_MISMATCH = 11;
	const ERROR_WP_VERSION_MISMATCH = 12;
	const ERROR_SYNC_VERSION_MISMATCH = 13;
	const ERROR_EXTENSION_MISSING = 14;
	const ERROR_INVALID_POST_TYPE = 15;
	const ERROR_REMOTE_REQUEST_FAILED = 16;
	const ERROR_BAD_POST_RESPONSE = 17;
	const ERROR_MISSING_SITE_KEY = 18;
	const ERROR_POST_CONTENT_NOT_FOUND = 19;
	const ERROR_BAD_NONCE = 20;
	const ERROR_UNRESOLVED_PARENT = 21;
	const ERROR_NO_AUTH_TOKEN = 22;

	const NOTICE_FILE_EXISTS = 1;
	const NOTICE_CONTENT_SYNCD = 2;
	const NOTICE_INTERNAL_ERROR = 3;

	public $host = NULL;

	private $_response = NULL;

	private $_user_id = 0;
	private $_target_data = array();
	private $_auth_cookie = '';
	private $_queue = array();
	private $_processing = FALSE;				// set to TRUE when processing the $_queue

	/**
	 * Initializes the cookies and nonce, throws an exception if it fails any of the validations.
	 * @param array $target_data A set of options data with credentials for Target system.
	 */
	public function __construct($target_data = array())
	{
		$this->_user_id = get_current_user_id();

		if (empty($target_data))
			$this->_target_data = SyncOptions::get_all();
		else
			$this->_target_data = $target_data;

		if (isset($this->_target_data['host']))
			$this->host = $this->_target_data['host'];
	}

	/**
	 * Sends an API call to the target site.
	 * @param string $action The action to be performed, 'auth', 'push', etc. Extendable by add-ons.
	 * @param array $data The data to be sent. Contents of array depend on the api request type being made.
	 * @param array $remote_args Arguments to override wp_remote_post
	 * @return SyncApiResponse object; the $success property indicates success/failure of request
	 */
	public function api($action, $data = array(), $remote_args = array())
	{
SyncDebug::log(__METHOD__.'() action="' . $action . '"');
		// TODO: check if there's a configured Target site and Source site has a key

//		if (!is_array($data))
//			$data = array();
		$this->_response = $response = new SyncApiResponse();

		// always add the authentication data to the request
		if (is_array($data))
			$this->_auth($data);
		// TODO: check $res for WP_Error

		// TODO: do some sanity checking on $data contents
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' checking action: ' . $action);
		switch ($action) {
		case 'auth':
			// authentication handled by _auth() above. This is here to avoid falling into 'default' case
			break;
		case 'push':
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' calling _push()');
			$data = $this->_push($data);
			break;
		case 'upload_media':
			$data = apply_filters('spectrom_sync_api_request_media', $data, $action, $remote_args);
			$data = $this->_media($data, $remote_args);		// converts $data to a string
			break;
		default:
			// allow add-ons to create the $data object for non-standard api actions
SyncDebug::log(__METHOD__.'() sending action "' . $action . '" to filter \'spectrom_sync_api_request_action\'');
			$data = apply_filters('spectrom_sync_api_request_action', $data, $action, $remote_args);
			break;
		}
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' data=' . var_export($data, TRUE));

		// check value returned from API call
		if (is_wp_error($data)) {
			// an error occured somewhere along the way. report it and return
			$response->error_code(intval($res->get_message()));
			return $response;
		}

		$data = apply_filters('spectrom_sync_api_request', $data, $action, $remote_args);

		// merge the body of the post with any other wp_remote_() arguments passed in
		$remote_args = array_merge($remote_args, array('body' => $data));	// new $data content should override anything in $remote_args
		// setup the SYNC arguments
		global $wp_version;
		$model = new SyncModel();
		if (!isset($remote_args['headers']))
			$remote_args['headers'] = array();
		$remote_args['headers'][self::HEADER_SYNC_VERSION] = WPSiteSyncContent::PLUGIN_VERSION;
		$remote_args['headers'][self::HEADER_WP_VERSION] = $wp_version;
		$remote_args['headers'][self::HEADER_SOURCE] = site_url();
		$remote_args['headers'][self::HEADER_SITE_KEY] = WPSiteSyncContent::get_option('site_key'); // $model->generate_site_key();

		// send data where it's going
		$url = $this->host . '/' . WPSiteSyncContent::API_ENDPOINT . '?action=' . $action;
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' sending API request to ' . $url, TRUE);
SyncDebug::log('  sending data array: ' . SyncDebug::arr_dump($remote_args));

		$request = wp_remote_post($url, $remote_args);
		if (is_wp_error($request)) {
			// TODO: handle error
			$response->error_code(self::ERROR_REMOTE_REQUEST_FAILED, $request->get_error_message());
		} else {
			$response->result = $request;
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' api result: ' . var_export($request, TRUE));

			// validate the host and credentials
			if (!($request['response']['code'] >= 200 && $request['response']['code'] < 300)) {
				$response->error_code(self::ERROR_BAD_POST_RESPONSE, intval($request['response']['code']));
			} else if (!isset($request['headers'][self::HEADER_SYNC_VERSION])) {
				$response->error_code(self::ERROR_NOT_INSTALLED);
			} else if (WPSiteSyncContent::PLUGIN_VERSION !== $request['headers'][self::HEADER_SYNC_VERSION]) {
				if (!WPSiteSyncContent::ALLOW_SYNC_VERSION_DIFF)
					$response->error_code(self::ERROR_SYNC_VERSION_MISMATCH);
			} else if (!version_compare($wp_version, $request['headers'][self::HEADER_WP_VERSION], '==')) {
				if (!WPSiteSyncContent::ALLOW_WP_VERSION_DIFF)
					$response->error_code(self::ERROR_WP_VERSION_MISMATCH);
			}

			// API request went through, check for error_code returned in JSON results

			$response->response = json_decode($request['body']);
			// TODO: convert error/notice codes into strings at this point.
SyncDebug::log(__METHOD__.'() received response from Target:');
SyncDebug::log(var_export($response->response, TRUE));

			// examine the Target response's error codes and assign them to the local system's response object
			// TODO: Use SyncResponse::copy() method
			if (isset($response->response->error_code))
				$response->error_code($response->response->error_code);
			else if (isset($response->response->has_errors) && $response->response->has_errors)
				$response->error_code($response->response->error_code);

			if (isset($response->response)) {
SyncDebug::log('- error code: ' . $response->response->error_code);
SyncDebug::log('- timeout: ' . $response->response->session_timeout);
SyncDebug::log('- has errors: ' . $response->response->has_errors);
SyncDebug::log('- success: ' . $response->response->success);
SyncDebug::log(__METHOD__.'() response: ' . var_export($response, TRUE));
				do_action('spectrom_sync_api_request_response', $action, $remote_args, $response);

				// only report success if no other error codes have been added to response object
				if (0 === $response->get_error_code()) {
					$response->success(TRUE);

					// if it was an authentication request, store the auth cookies in user meta
					// TOOD: need to do this differently to support auth cookies from multiple Targets
					if ('auth' === $action && isset($response->response->data)) {
						update_user_meta($this->_user_id, 'spectrom_site_cookies', $response->response->data->auth_cookie);
						update_user_meta($this->_user_id, 'spectrom_site_nonce', $response->response->data->access_nonce);
						update_user_meta($this->_user_id, 'spectrom_site_target_uid', $response->response->data->user_id);

SyncDebug::log(__METHOD__.'() saving auth token ' . var_export($response, TRUE));
						// store the returned token for later authentication uses
						$sources_model = new SyncSourcesModel();
						$source = array(
							'domain' => $data['host'],
							'site_key' => '',						// indicates that it's a Target's entry on the Source
							'auth_name' => $data['username'],
							'token' => $response->response->data->token,
						);
						$sources_model->add_source($source);
					}
				}
			}
		}

		$this->_process_queue($remote_args, $response);

		// API request successful. Return results to caller.
		return $response;
	}

	/**
	 * Sends any additional API requests that were queued up during the first API call. These are mostly from images associated with the post
	 * @param array $remote_args Arguments being passed to wp_remote_post()
	 * @param SyncApiResponse $response The response instance that will be returned from api()
	 */
	private function _process_queue($remote_args, $response)
	{
SyncDebug::log(__METHOD__.'()');
		if ($this->_processing || $response->has_errors())
			return;
		$this->_processing = TRUE;

		foreach ($this->_queue as $queue) {
			$action = $queue['action'];
SyncDebug::log(__METHOD__.'() found action ' . $action);
			$res = $this->api($action, $queue['data'], $remote_args);
			// exit processing if one of the queued API calls has an error
			if ($res->has_errors()) {
				$response->error_code($res->get_error_code());
				break;
			}
		}
		$this->_processing = FALSE;
	}

	/**
	 * Adds items to the post processing queue
	 * @param string $action The API action. This is something handled in the api() method, or via the 'spectrom_sync_api_request_action' filter
	 * @param array $data Data used in processing the request; passed to api()
	 */
	private function _add_queue($action, $data)
	{
		$this->_queue[] = array('action' => $action, 'data' => $data);
	}

	/**
	 * Returns the current user's auth cookie provided by auth()
	 * @return string The auth cookie to be used on api requests
	 */
	private function _get_auth_cookie()
	{
SyncDebug::log(__METHOD__.'() user id=' . $this->_user_id);
		$this->_auth_cookie = get_user_meta($this->_user_id, 'spectrom_site_cookies', TRUE);

		// TODO: check for error and return WP_Error instance

//		if (empty($this->_auth_cookie))
//			$this->_auth_cookie = $this->auth();

		return $this->_auth_cookie;
	}

	/**
	 * Validates the target settings and sets the proper nonces
	 * @return mixed WP_Error on failure | The auth cookie on success
	 */
	public function auth()
	{
SyncDebug::log(__METHOD__.'()', TRUE);
		$current_user_id = get_current_user_id();
		// Spoof the referer header.
		$args = array('headers' => 'Referer: ' . $this->host);
SyncDebug::log(__METHOD__.'() target data=' . var_export($this->_target_data, TRUE));

//		$auth = new SyncAuth();
		$auth_args = $this->_target_data;
//		$auth_args['password'] = $auth->encode_password($auth_args['password'], $auth_args['host']);
		$request = $this->api('auth', $this->_target_data /*$auth_args */, $args);
SyncDebug::log(__METHOD__.'() target data: ' . var_export($auth_args, TRUE));

		if (!is_wp_error($request)) {
			$reqdata = json_decode($request->__toString());
			// TODO: check response- getting "trying to get property of non-object"
			update_user_meta($current_user_id, 'spectrom_site_cookies', $reqdata->data->auth_cookie /*$request->data->auth_cookie*/);
			update_user_meta($current_user_id, 'spectrom_site_nonce', $reqdata->data->access_nonce /*$request->data->access_nonce*/);
			update_user_meta($current_user_id, 'spectrom_site_target_uid', $reqdata->data->user_id /*$request->data->user_id*/);

			return $request->data->auth_cookie;
		}

		return $request;
	}

	/**
	 * Perform push operation
	 * @param int $post_id The post ID to be pushed
	 * @return SyncApiResponse result data
	 */
	// TODO: remove this method- all functionality needs to be in _push()
	private /*public*/ function push($post_id)
	{
		// TODO: refactor to call $this->api('push')
		$model = new SyncModel();
		$response = new SyncApiResponse();

		// TODO: $this->_target_data created in constructor
		$settings = SyncOptions::get_all();

		// TODO: site_key needs to be present before calling push()
		if (!isset($this->_target_data['site_key'])) {
			$response->error_code(self::ERROR_MISSING_SITE_KEY);
			return $response;
		}
//		$sync = WPSiteSyncContent::get_instance();
//		// Check the stored value of the site key against the current host name and install directory. If the two don’t match, reset the site key.
//		if ($settings['site_key'] !== $model->generate_site_key()) {
//			SyncOptions::set('site_key', $model->generate_site_key());
//			SyncOptions::save_options(); // update_option(SyncSettings::OPTION_NAME, $settings);
//		}

		// build array of data that will be sent to Target via the API
		$push_data = $model->build_sync_data($post_id);

		// Check if this is an update
		// TODO: change get_current_blog_id() to site_key
		// TODO: use a better variable name than $sync_data
		$sync_data = $model->get_sync_data($post_id); // , get_current_blog_id());
		if (NULL !== $sync_data)
			$push_data['target_post_id'] = $sync_data->target_content_id;		

// TODO: move into build_sync_data() and add filtering

		// serialize the data into a JSON string.
//		$push_data = json_encode($push_data);
		// use the wp_remote_post() API to perform a connection/authenticate operation on the Target site using the Target site’s configured credentials and send the JSON data.
		$target = new SyncApiRequest();

		$result = $this->api('push', $push_data); // $target->api('push', $push_data); // send data
/////;here;
		// the response from the Target site will indicate success or failure of the operation via an error code.
		// this error code will be used to look up a translatable string to display a useful error message to the user.

		// the success or error message will be returned as part of the response for the AJAX request and displayed just
		// underneath the ( Sync ) button within the MetaBox.
		$response = new SyncApiResponse();
		if (!is_wp_error($result)) {
			// PARSE IMAGES FROM SOURCE ONLY
			$this->_parse_media($result->data->post_id, $push_data['post_data']['post_content'], $target, $response);
			$response->success(TRUE);
			$response->notice_code(SyncApiRequest::NOTICE_CONTENT_SYNCD);
//			$response->notice(__('Content SYNCd.', 'wpsitesynccontent'));
			$response->set('post_id', $result->data->post_id);

			global $wp_version;

			$sync_data = array(
				'site_key' => $result->data->site_key,
				'source_content_id' => $post_id,
				'target_content_id' => $result->data->post_id,
				'wp_version' => $wp_version,
				'sync_version' => WPSiteSyncContent::PLUGIN_VERSION,
			);

			$model = new SyncModel();
			$model->save_sync_data($sync_data);
		} else {
			$response->success(FALSE);
			$response->error($result->get_error_message());
		}

		$response = apply_filters('spectrom_sync_push_result', $response);

		return $response;
	}

	/**
	 * Adds authentication information to the data array
	 * @param array $data The data array being built for the current API request
	 * @return NULL | WP_Error NULL on success or WP_Error on error
	 */
	private function _auth(&$data)
	{
SyncDebug::log(__METHOD__.'() data: ' . var_export($data, TRUE));
		// TODO: indicate error if target system not set up

		// if no Target credentials provided, get them from the config
		if (!isset($data['username']) /*|| !isset($data['password'])*/ || !isset($data['host'])) {
SyncDebug::log(__METHOD__.'() using credentials from config');
			$source_model = new SyncSourcesModel();
			$opts = new SyncOptions();
			$row = $source_model->find_target($opts->get('host'));
			if (NULL === $row) {
				$this->_response->error_code(self::ERROR_NO_AUTH_TOKEN);
				return new WP_Error($this->error_code_to_string(self::ERROR_NO_AUTH_TOKEN));
			}
			if (!isset($this->_target_data['token']))
				$this->_target_data['token'] = $row->token;

			if (!isset($data['username']))
				$data['username'] = $opts->get('username');
//			if (!isset($data['password']))
//				$data['password'] = $opts->get('password');
			if (!isset($data['token']))
				$data['token'] = $row->token;
			// TODO: change name to ['target'] to be more consistent
			if (!isset($data['host']))
				$data['host'] = $opts->get('host');
		}

		$auth_cookie = $this->_get_auth_cookie();
		if (is_wp_error($auth_cookie)) {
SyncDebug::log(__METHOD__.'() no authentication cookie data found');
			return $auth_cookie;
		}

SyncDebug::log(__METHOD__.'():' . __LINE__ . ' target data: ' . var_export($this->_target_data, TRUE));
		// check for site key and credentials
		if (!isset($this->_target_data['site_key']))
			return new WP_Error(self::ERROR_MISSING_SITE_KEY);
SyncDebug::log(__METHOD__.'() target username: ' . $this->_target_data['username']);
SyncDebug::log(__METHOD__.'() target token: ' . (isset($this->_target_data['token']) ? $this->_target_data['token'] : ''));
SyncDebug::log(__METHOD__.'() data token: ' . (isset($data['token']) ? $data['token'] : ''));
//SyncDebug::log(__METHOD__.'() data password: ' . $data['password']);
		if (empty($this->_target_data['username']) ||
			(empty($this->_target_data['token']) && empty($data['token']) && empty($data['password']))) {
SyncDebug::log(__METHOD__.'() return ERROR_BAD_CREDENTIALS');
			return new WP_Error(self::ERROR_BAD_CREDENTIALS);
		}

SyncDebug::log(' ' . __LINE__ . ' - adding authentication data to array');
		// add authentication to the data array
		$data['auth'] = array(
			'cookie' => $auth_cookie,
			'nonce' => get_user_meta(get_current_user_id(), 'spectrom_site_nonce', TRUE),
			'site_key' => $this->_target_data['site_key']
		);
		// if password provided (first time authentication) then encrypt it
		if (!empty($data['password'])) {
SyncDebug::log(__METHOD__.'() encrypting password');
			$auth = new SyncAuth();
			$data['password'] = $auth->encode_password($data['password'], $data['host']);
		}
SyncDebug::log(__METHOD__.'() data: ' . var_export($data, TRUE));

		return NULL;
	}

	/**
	 * Perform data manipulation for 'push' operations
	 * @param array $data The data array to be sent via the API call
	 * @return NULL|WP_Error return WP_Error if there was a problem; modified $data array otherwise
	 */
	private function _push($data)
	{
		$post_id = intval($data['post_id']);

		// build array of data that will be sent to Target via the API
		$model = new SyncModel();
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' post id=' . $post_id);
		$post_data = $model->build_sync_data($post_id);
SyncDebug::log(__METHOD__.'():' . __LINE__ . ' post data: ' . var_export($post_data, TRUE));

		// Check if this is an update of a previously sync'd post
		// TODO: change get_current_blog_id() to site_key
		// TODO: use a better variable name than $sync_data
		$sync_data = $model->get_sync_data($post_id, get_current_blog_id());
SyncDebug::log(__METHOD__.'() sync data: ' . var_export($sync_data, TRUE));

		if (NULL !== $sync_data)
			$data['target_post_id'] = $sync_data->target_content_id;		

		// add generated post data to the content being sent via the API
		// TODO: swap this around. move the data from $post_data[] into $data[] instead of copy- then set $data['post_data']. This should reduce memory usage
		$data['post_data'] = $post_data['post_data'];
		if (isset($post_data['post_meta']))
			$data['post_meta'] = $post_data['post_meta'];
		if (isset($post_data['taxonomies']))
			$data['taxonomies'] = $post_data['taxonomies'];
		if (isset($post_data['sticky']))
			$data['sticky'] = $post_data['sticky'];

		// parse images from source only
		$res = $this->_parse_media($post_id, $post_data['post_data']['post_content']);
		if (is_wp_error($res))
			return $res;

		$data['media_data'] = $res;

		return $data;
	}

	/**
	 * Formats input data into multipart form for file/'media_upload' operations
	 * @param array $data The data array to be sent via the API call
	 * @param array $args The arguments array to be sent to wp_remote_post();
	 * @return string|WP_Error return WP_Error if there was a problem; formatted Multipart content otherwise
	 */
	private function _media($data, &$args)
	{
SyncDebug::log(__METHOD__.'() called with ' . var_export($data, TRUE));
		// grab a few required items out of the data array
		$boundary = $data['boundary'];
		unset($data['boundary']);
		$img_name = $data['img_name'];
		unset($data['img_name']);
		$content = $data['contents'];
		unset($data['contents']);
/**
array (
  'username' =>
  'password' =>
  'host' =>
  'auth' => 
  array (
    'cookie' =>
    'nonce' =>
    'site_key' =>
  )
 */
		$headers = array(
			'content-type' => 'multipart/form-data; boundary=' . $boundary
		);
		$boundary = '--' . $boundary;

		$payload = '';
		// first, add the standard POST fields:
		foreach ($data as $name => $value) {
			if (!is_array($value)) {
				$payload .= $boundary . "\r\n";
				$payload .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
				$payload .= $value;
				$payload .= "\r\n";
			}
		}

		// Upload the file
		if (!empty($content)) {
			$payload .= $boundary . "\r\n";
			$payload .= "Content-Disposition: form-data; name=\"sync_file_upload\"; filename=\"{$img_name}\"\r\n\r\n";
			$payload .= $content;
			$payload .= "\r\n";
		}
		$payload .= $boundary . '--';

		$args['headers'] = $headers;
		// TODO: remove $args['post_data'] element

		return $payload;
	}

	/**
	 * Parses content, looking for image references that need to be synchronized
	 * @param int $post_id The post id
	 * @param string $content The post content to be parsed
	 * @param SyncApiRequest $target instances indicating the target
	 * @param SyncApiResponse $response The response object being built
	 * @return boolean TRUE on successful addition of media to response; otherwise FALSE
	 */
	private function _parse_media($post_id, $content) // , $target, SyncApiResponse $response)
	{
		// TODO: add try..catch
		// TODO: can we use get_media_embedded_in_content()?
		$xml = new DOMDocument();
		$xml->loadHTML($content);

		$tags = $xml->getElementsByTagName('img');

		$url = parse_url(get_bloginfo('url'));
		$host = $url['host'];

		$post_thumbnail_id = get_post_thumbnail_id($post_id);

		// loop through each <a> tag and replace them by their text content
		for ($i = $tags->length - 1; $i >= 0; $i--) {
//break; // TODO: remove this
			$media_node = $tags->item($i);
			$src = $media_node->getAttribute('src');

			if (isset($src)) {
				$src = parse_url($src);
				$path = substr($src['path'], 1); // remove first "/"

				// return data array
				if ($src['host'] === $host && is_wp_error($this->_upload_media($post_id, ABSPATH . $path, $this->host, $post_thumbnail_id == $post_id)))
					return FALSE;
			}
		}

		// handle the featured image
		if ('' !== $post_thumbnail_id) {
SyncDebug::log(__METHOD__.'() featured image:');
			$img = wp_get_attachment_image_src($post_thumbnail_id, 'large');
SyncDebug::log('  src=' . var_export($img, TRUE));
			// convert site url to relative path
			if (FALSE !== $img) {
				$src = $img[0];
SyncDebug::log('  src=' . var_export($src, TRUE));
SyncDebug::log('  siteurl=' . site_url());
SyncDebug::log('  ABSPATH=' . ABSPATH);
SyncDebug::log('  DOCROOT=' . $_SERVER['DOCUMENT_ROOT']);
				$path = str_replace(trailingslashit(site_url()), ABSPATH, $src);
				$this->_upload_media($post_id, $path, $this->host, TRUE);
			}
		}

		return TRUE;
	}

	/**
	 * Uploads a found image to the target site.
	 * @param int $post_id The post ID returned from the target site.
	 * @param string $file_path Path to the file.
	 * @param SyncApiRequest $target Request object.
	 * @param boolean $featured Flag if the image/media is the featured image
	 * @return mixed WP_Error on failure.
	 */
	private function _upload_media($post_id, $file_path, $target, $featured = false)
	// TODO: remove $target parameter
	{
SyncDebug::log(__METHOD__.'() post_id=' . $post_id . ' path=' . $file_path . ' featured=' . ($featured ? 'TRUE' : 'FALSE'), TRUE);
		$post_fields = array (
			'name' => 'value',
			'post_id' => $post_id,
			'featured' => intval($featured),
			'boundary' => wp_generate_password(24),
			'img_name' => basename($file_path),
			'contents' => file_get_contents($file_path),
		);
		// add file upload operation to the API queue
		$this->_add_queue('upload_media', $post_fields);
		return;
#####
		return $this->api('upload_media', $post_fields);
#####
		$boundary = wp_generate_password(24);
		$headers  = array(
			'content-type' => 'multipart/form-data; boundary=' . $boundary
		);
		$payload = '';
		// First, add the standard POST fields:
		foreach ($post_fields as $name => $value) {
			$payload .= '--' . $boundary;
			$payload .= "\r\n";
			$payload .= "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n";
			$payload .= $value;
			$payload .= "\r\n";
		}
		// Upload the file
		if (file_exists($file_path)) {
			$payload .= '--' . $boundary;
			$payload .= "\r\n";
			$payload .= 'Content-Disposition: form-data; name="' . 'sync_file_upload' .
						'"; filename="' . basename($file_path) . '"' . "\r\n";
			$payload .= "\r\n";
			// TODO: use WP_Filesystem
			$payload .= file_get_contents($file_path);
			$payload .= "\r\n";
		}

		$payload .= '--' . $boundary . '--';

		$args = array('headers' => $headers);

//		return $this->api('upload_media', $payload, $args);
		return $payload;
	}

	/**
	 * Converts an error code to a language translated string
	 * @param int $code The integer error code. One of the `ERROR_*` values.
	 * @return strint The text value of the error code, translated to the current locale
	 */
	// TODO: move to SyncApiResponse
	public static function error_code_to_string($code)
	{
		$error = '';
		switch ($code) {
		case self::ERROR_CANNOT_CONNECT:		$error = __('Unable to connect to Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_UNRECOGNIZED_REQUEST:	$error = __('The requested action is not recognized', 'wpsitesynccontent'); break;
		case self::ERROR_NOT_INSTALLED:			$error = __('WPSiteSync for Content is not installed and activated on Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_BAD_CREDENTIALS:		$error = __('Unable to authenticate on Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_SESSION_EXPIRED:		$error = __('User session has expired.', 'wpsitesynccontent'); break;
		case self::ERROR_CONTENT_EDITING:		$error = __('A user is currently editing this Content on the Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_CONTENT_LOCKED:		$error = __('This Content is currently Locked on Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_POST_DATA_INCOMPLETE:	$error = __('Some or all of the data for the request is missing.', 'wpsitesynccontent'); break;
		case self::ERROR_USER_NOT_FOUND:		$error = __('The username does not exist on the Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_FILE_UPLOAD:			$error = __('Error while handling file upload.', 'wpsitesynccontent'); break;
		case self::ERROR_PERMALINK_MISMATCH:	$error = __('The Permalink settings are different on the Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_WP_VERSION_MISMATCH:	$error = __('The WordPress versions are different on the Source and Target sites.', 'wpsitesynccontent'); break;
		case self::ERROR_SYNC_VERSION_MISMATCH:	$error = __('The SYNC versions are different on the Source and Target sites.', 'wpsitesynccontent'); break;
		case self::ERROR_EXTENSION_MISSING:		$error = __('The required SYNC extension is not active on the Target site.', 'wpsitesynccontent'); break;
		case self::ERROR_INVALID_POST_TYPE:		$error = __('The post type is not allowed.', 'wpsitesynccontent'); break;
		case self::ERROR_REMOTE_REQUEST_FAILED:	$error = __('Unable to make API request to Target system.', 'wpsitesynccontent'); break;
		case self::ERROR_BAD_POST_RESPONSE:		$error = __('Target system did not respond with success code.', 'wpsitesynccontent'); break;
		case self::ERROR_MISSING_SITE_KEY:		$error = __('Site Key for Target system has not been obtained.', 'wpsitesynccontent'); break;
		case self::ERROR_POST_CONTENT_NOT_FOUND:$error = __('Unable to determine post content.', 'wpsitesynccontent'); break;
		case self::ERROR_BAD_NONCE:				$error = __('Unable to validate AJAX request.', 'wpsitesynccontent'); break;
		case self::ERROR_UNRESOLVED_PARENT:		$error = __('Content has a Parent Page that has not been Sync\'d.', 'wpsitesynccontent'); break;
		case self::ERROR_NO_AUTH_TOKEN:			$error = __('No authentication Token found for this Target.', 'wpsitesynccontent'); break;

		default:
			$error = apply_filters('spectrom_sync_error_code_to_text', __('unknown error', 'wpsitesynccontent'), $code);
			break;
		}

		return $error;
	}

	/**
	 * Converts a notice code to a language translated string
	 * @param int $code The integer error code. One of the `NOTICE_*` values.
	 * @return strint The text value of the notice code, translated to the current locale
	 */
	public static function notice_code_to_string($code, $notice_data = NULL)
	{
		$notice = '';
		switch ($code) {
		case self::NOTICE_FILE_EXISTS:			$notice = __('The file name already exists.', 'wpsitesynccontent'); break;
		case self::NOTICE_CONTENT_SYNCD:		$notice = __('Content SYNChronized.', 'wpsitesynccontent'); break;
		case self::NOTICE_INTERNAL_ERROR:		$notice = __('Internal error:', 'wpsitesynccontent'); break;
		default:
			$notice = apply_filters('spectrom_sync_notice_code_to_text', __('unknown action', 'wpsitesynccontent'), $notice, $code);
			break;
		}

		return $notice;
	}

	/**
	 * Return a translated error message.
	 * @param int $error_code The error code.
	 * @deprecated Use error_code_to_string() instead
	 * @return WP_Error Instance describing the error
	 */
	// TODO: replace with error_code_to_string()
	public static function get_error($error_code, $error_data = NULL)
	{
		$error_code = intval($error_code);
		$msg = self::error_code_to_string($error_code);
		if (NULL !== $error_data)
			$msg = sprintf($msg, $error_data);

		return new WP_Error($error_code, $msg);
	}
}

// EOF
