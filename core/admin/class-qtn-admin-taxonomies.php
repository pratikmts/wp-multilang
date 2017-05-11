<?php
/**
 * Taxonomies Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QtN_Admin_Taxonomies' ) ) :

	/**
	 * QtN_Admin_Taxonomies Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class QtN_Admin_Taxonomies {

		private $description = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
			add_filter( 'wp_update_term_data', array( $this, 'save_term' ), 0, 4 );
			add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );
		}


		public function init() {
			global $qtn_config;

			foreach ( $qtn_config->settings['taxonomies'] as $taxonomy ) {
				add_action( "{$taxonomy}_term_edit_form_top", array( $this, 'translate_taxonomies' ), 0 );
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'language_columns' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'render_language_column' ), 0, 3 );
			}
		}


		public function pre_insert_term( $term, $taxonomy ) {
			global $wpdb, $qtn_config;

			$to_locale = '';
			$languages = array_flip( $qtn_config->languages );
			if ( isset( $_POST['lang'] ) && isset( $languages[ qtn_clean( $_POST['lang'] ) ] ) ) {
				$to_locale = $languages[ qtn_clean( $_POST['lang'] ) ];
			}

			$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

			foreach ( $results as $result ) {
				$ml_term = qtn_translate_string( $result->name, $to_locale );
				if ( $ml_term == $term ) {
					return '';
				}
			}

			return $term;
		}


		public function save_term( $data, $term_id, $taxonomy, $args ) {
			global $qtn_config;

			if ( ! in_array( $taxonomy, $qtn_config->settings['taxonomies'] ) ) {
				return $data;
			}

			if ( qtn_is_ml_value( $data['name'] ) ) {
				return $data;
			}

			remove_filter( 'get_term', 'qtn_translate_object', 0 );
			$old_name        = get_term_field( 'name', $term_id );
			$old_description = get_term_field( 'description', $term_id );
			add_filter( 'get_term', 'qtn_translate_object', 0 );
			$strings      = qtn_value_to_ml_array( $old_name );
			$value        = qtn_set_language_value( $strings, $data['name'] );
			$data['name'] = qtn_ml_value_to_string( $value );

			$this->description = array(
				'old' => $old_description,
				'new' => $args['description']
			);

			return $data;
		}


		public function update_description( $tt_id, $taxonomy ) {
			global $wpdb, $qtn_config;
			if ( ! in_array( $taxonomy, $qtn_config->settings['taxonomies'] ) ) {
				return;
			}

			if ( ! $this->description ) {
				return;
			}

			$value = $this->description['new'];

			if ( qtn_is_ml_value( $value ) ) {
				return;
			}

			$old_value   = $this->description['old'];
			$strings     = qtn_value_to_ml_array( $old_value );
			$value       = qtn_set_language_value( $strings, $value );
			$description = qtn_ml_value_to_string( $value );

			$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
		}


		public function translate_taxonomies( $tag ) {
			global $qtn_config;

			$languages = $qtn_config->languages;
			$lang      = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : $qtn_config->languages[ get_locale() ];
			$tag       = qtn_translate_object( $tag );
			?>
			<input type="hidden" name="lang" value="<?php echo $lang; ?>">
			<?php

			if ( count( $languages ) <= 1 ) {
				return;
			}

			$url = remove_query_arg( 'edit_lang', get_edit_term_link( $tag->term_id ) );
			?>
			<h3 class="nav-tab-wrapper language-switcher">
				<?php foreach ( $languages as $key => $language ) { ?>
					<a class="nav-tab<?php if ( $lang == $language ) { ?> nav-tab-active<?php } ?>"
					   href="<?php echo add_query_arg( 'edit_lang', $language, $url ); ?>">
						<img src="<?php echo QN()->flag_dir() . $qtn_config->options[ $key ]['flag'] . '.png'; ?>"
						     alt="<?php echo $qtn_config->options[ $key ]['name']; ?>">
						<span><?php echo $qtn_config->options[ $key ]['name']; ?></span>
					</a>
				<?php } ?>
			</h3>
			<?php
		}

		/**
		 * Define custom columns for post_types.
		 *
		 * @param  array $existing_columns
		 *
		 * @return array
		 */
		public function language_columns( $columns ) {
			if ( empty( $columns ) && ! is_array( $columns ) ) {
				$columns = array();
			}

			$insert_after = 'name';

			$i = 0;
			foreach ( $columns as $key => $value ) {
				if ( $key == $insert_after ) {
					break;
				}
				$i ++;
			}

			$columns =
				array_slice( $columns, 0, $i + 1 ) + array( 'languages' => __( 'Languages', 'qtranslate-next' ) ) + array_slice( $columns, $i + 1 );

			return $columns;
		}

		/**
		 * Ouput custom columns for products.
		 *
		 * @param string $column
		 */
		public function render_language_column( $columns, $column, $term_id ) {
			global $qtn_config;

			if ( 'languages' == $column ) {
				remove_filter( 'get_term', 'qtn_translate_object', 0 );
				$term = get_term( $term_id );
				add_filter( 'get_term', 'qtn_translate_object', 0 );
				$output  = array();
				$text    = $term->name . $term->description;
				$strings = qtn_value_to_ml_array( $text );
				$options = $qtn_config->options;

				foreach ( $qtn_config->languages as $locale => $language ) {
					if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
						$output[] = '<img src="' . QN()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
					}
				}

				if ( ! empty( $output ) ) {
					$columns .= implode( '<br />', $output );
				}
			}

			return $columns;
		}
	}

endif;