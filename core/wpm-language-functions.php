<?php
/**
 * WPM Language functions
 *
 * Functions for getting params from WPM_Setup.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPM\Core\WPM_Setup;

/**
 * Get enabled languages
 *
 * @see WPM_Setup::get_languages()
 *
 * @return array
 */
function wpm_get_languages() {
	return WPM_Setup::instance()->get_languages();
}

/**
 * Get user language
 *
 * @see WPM_Setup::get_user_language()
 *
 * @return string
 */
function wpm_get_user_language() {
	return WPM_Setup::instance()->get_user_language();
}

/**
 * Get default locale
 *
 * @see WPM_Setup::get_default_locale()
 *
 * @return string
 */
function wpm_get_default_locale() {
	return WPM_Setup::instance()->get_default_locale();
}

/**
 * Get config
 *
 * @see WPM_Setup::get_config()
 *
 * @return array
 */
function wpm_get_config() {
	return WPM_Setup::instance()->get_config();
}

/**
 * Get options
 *
 * @see WPM_Setup::get_options()
 *
 * @return array
 */
function wpm_get_options() {
	return WPM_Setup::instance()->get_options();
}

/**
 * Get installed languages
 *
 * @see WPM_Setup::get_installed_languages()
 *
 * @return array
 */
function wpm_get_installed_languages() {
	return WPM_Setup::instance()->get_installed_languages();
}

/**
 * Get available translation
 *
 * @see WPM_Setup::get_translations()
 *
 * @return array
 */
function wpm_get_translations() {
	return WPM_Setup::instance()->get_translations();
}

/**
 * Get language for translation
 *
 * @return string
 */
function wpm_get_language() {
	if ( is_admin() ) {
		$lang = isset( $_GET['edit_lang'] ) ? wpm_clean( $_GET['edit_lang'] ) : ( isset( $_COOKIE['edit_language'] ) ? wpm_clean( $_COOKIE['edit_language'] ) : wpm_get_user_language() );
	} else {
		$lang = wpm_get_user_language();
	}

	return $lang;
}

/**
 * Get current url from $_SERVER
 *
 * @return string
 */
function wpm_get_current_url() {
	$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	return $url;
}