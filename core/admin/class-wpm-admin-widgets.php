<?php
/**
 * Translate widgets in admin
 *
 * @package  WPM\Core\Admin
 * @class    WPM_Admin_Widgets
 * @category Admin
 * @author   VaLeXaR
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Admin_Widgets
 */
class WPM_Admin_Widgets {

	/**
	 * WPM_Admin_Widgets constructor.
	 */
	public function __construct() {
		add_filter( 'pre_update_option', array( $this, 'save_widgets' ), 99, 2 );
		add_filter( 'widget_form_callback', 'wpm_translate_value', 0 );
	}

	/**
	 * Save widgets with translation. Title and text field translate for all widgets.
	 *
	 * @param $value
	 * @param $option
	 *
	 * @return mixed
	 */
	public function save_widgets( $value, $option ) {

		if ( substr( $option, 0, 6 ) != 'widget' ) {
			return $value;
		}

		$old_value = get_option( $option );

		if ( ! $old_value ) {
			return $value;
		}

		$widget_name    = substr( $option, 6 );
		$config         = wpm_get_config();
		$default_fields = array(
			'title' => array(),
			'text'  => array()
		);

		$widget_config = $default_fields;

		if ( isset( $config['widgets'][ $widget_name ] ) ) {
			$widget_config = wpm_array_merge_recursive( $widget_config, $config['widgets'][ $widget_name ] );
		}

		$widget_config = apply_filters( "wpm_widget_{$widget_name}_config", $widget_config, $value );

		foreach ( $value as $key => &$widget ) {

			if ( ( '_multiwidget' == $key ) || ! isset( $old_value[ $key ] ) ) {
				continue;
			}

			foreach ( $widget as $_key => $_value ) {
				if ( isset( $widget_config[ $_key ] ) ) {
					$strings                = wpm_value_to_ml_array( $old_value[ $key ][ $_key ] );
					$new_value              = wpm_set_language_value( $strings, $_value, $widget_config[ $_key ] );
					$value[ $key ][ $_key ] = wpm_ml_value_to_string( $new_value );
				}
			}
		}

		return $value;
	}
}