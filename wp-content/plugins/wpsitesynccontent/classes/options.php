<?php

// TODO: change all references to 'host' setting to 'target' for naming consistency

class SyncOptions
{
	const OPTION_NAME = 'spectrom_sync_settings';

	private static $_options = NULL;
	private static $_dirty = FALSE;

	/*
	 * Options are:
	 * 'host' = Target site URL
	 * 'username' = Target site login username
	 * 'password' = Target site login password
	 * 'site_key' = Current site's site_key - a unique identifier for the site
	 * 'auth' = 1 for username/password authenticated; otherwise 0
	 * 'strict' = 1 for strict mode; otherwise 0
	 * 'salt' = salt value used for authentication
	 * 'min_role' = minimum role allowed to perform SYNC operations
	 */

	/**
	 * Loads the options from the database and decodes the password
	 */
	private static function _load_options()
	{
		if (NULL === self::$_options)
			self::$_options = get_option(self::OPTION_NAME, array());
		if (!empty(self::$_options['host'])) {
			$auth = new SyncAuth();
//			self::$_options['password'] = $auth->decode_password(self::$_options['password'], self::$_options['host']);
		}
	}

	/**
	 * Retrieves a named setting option
	 * @param string $name The name of the setting option to retrieve
	 * @param mixed $default The default value to return if it's not found
	 * @return mixed The value of the named setting option if found; otherwise the default value
	 */
	public static function get($name, $default = '')
	{
		self::_load_options();
		if (isset(self::$_options[$name]))
			return self::$_options[$name];
		return $default;
	}

	/**
	 * Return the integer value of a settings option
	 * @param name $name The name of the setting option to retrieve
	 * @param int $default A default value for the option if it's not found
	 * @return int The integer value of the setting option
	 */
	public static function get_int($name, $default = 0)
	{
		return intval(self::get($name, $default));
	}

	/*
	 * Retrieve the array of all options
	 */
	public static function get_all()
	{
		self::_load_options();
		return self::$_options;
	}

	/**
	 * Updates the local copy of the option data
	 * @param string $name The name of the Sync option to update
	 * @param mixed $value The value to store with the name
	 */
	public static function set($name, $value)
	{
		self::$_options[$name] = $value;
		self::$_dirty = TRUE;
	}

	/**
	 * Saves the options data if it's been updated
	 */
	public static function save_options()
	{
		if (self::$_dirty) {
			$opts = self::$_options;
			if (!empty($opts['host'])) {
				// make a copy and write it -- so the self::$_options still has unencrypted password
				$auth = new SyncAuth();
//				$opts['password'] = $auth->encode_password($opts['password'], $opts['host']);
			}
			// assume options already exist - they are created at install time
			update_option(self::OPTION_NAME, self::$_options);
		}
	}
}

// EOF
