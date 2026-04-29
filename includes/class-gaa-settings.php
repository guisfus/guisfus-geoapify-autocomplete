<?php
/**
 * Plugin settings storage and sanitization.
 *
 * @package GuisfusGeoapifyAutocomplete
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gestión de ajustes del plugin.
 *
 * @since 1.0.0
 */
final class GAA_Settings {

	/**
	 * Option key used in wp_options.
	 */
	public const OPTION_KEY = 'guisfus_geoapify_autocomplete_settings';

	/**
	 * Get default settings.
	 *
	 * @return array<string,mixed>
	 */
	public function defaults() {
		return array(
			'api_key'      => '',
			'country_code' => 'es',
			'lang'         => 'es',
			'min_chars'    => 3,
			'limit'        => 6,
			'forms'        => array(),
		);
	}

	/**
	 * Get normalized settings.
	 *
	 * @return array<string,mixed>
	 */
	public function get() {
		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$defaults = $this->defaults();
		$out      = wp_parse_args( $saved, $defaults );

		if ( ! isset( $out['forms'] ) || ! is_array( $out['forms'] ) ) {
			$out['forms'] = array();
		}

		foreach ( $out['forms'] as $i => $row ) {
			if ( ! is_array( $row ) ) {
				$row = array();
			}

			$out['forms'][ $i ] = array(
				'address' => isset( $row['address'] ) ? (string) $row['address'] : '',
				'city'    => isset( $row['city'] ) ? (string) $row['city'] : '',
				'zip'     => isset( $row['zip'] ) ? (string) $row['zip'] : '',
				'state'   => isset( $row['state'] ) ? (string) $row['state'] : '',
			);
		}

		return $out;
	}

	/**
	 * Sanitize settings saved from the admin screen.
	 *
	 * @param array<string,mixed> $input Raw input from WordPress.
	 * @return array<string,mixed>
	 */
	public function sanitize( $input ) {
		$d   = $this->defaults();
		$out = array();

		$input = is_array( $input ) ? $input : array();

		$out['api_key']      = isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : '';
		$out['country_code'] = isset( $input['country_code'] ) ? $this->sanitize_country_code( $input['country_code'] ) : $d['country_code'];
		$out['lang']         = isset( $input['lang'] ) ? $this->sanitize_language_code( $input['lang'] ) : $d['lang'];

		$out['min_chars'] = isset( $input['min_chars'] ) ? max( 2, min( 10, intval( $input['min_chars'] ) ) ) : $d['min_chars'];
		$out['limit']     = isset( $input['limit'] ) ? max( 1, min( 20, intval( $input['limit'] ) ) ) : $d['limit'];

		$out['forms'] = array();
		if ( isset( $input['forms'] ) && is_array( $input['forms'] ) ) {
			foreach ( $input['forms'] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$m = array(
					'address' => isset( $row['address'] ) ? $this->sanitize_field_id( $row['address'] ) : '',
					'city'    => isset( $row['city'] ) ? $this->sanitize_field_id( $row['city'] ) : '',
					'zip'     => isset( $row['zip'] ) ? $this->sanitize_field_id( $row['zip'] ) : '',
					'state'   => isset( $row['state'] ) ? $this->sanitize_field_id( $row['state'] ) : '',
				);

				if ( ! empty( $m['address'] ) ) {
					$out['forms'][] = $m;
				}
			}
		}

		return $out;
	}

	/**
	 * Sanitize a two-letter country code.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private function sanitize_country_code( $value ) {
		$value = strtolower( sanitize_text_field( (string) $value ) );
		return preg_match( '/^[a-z]{2}$/', $value ) ? $value : '';
	}

	/**
	 * Sanitize a language code accepted by Geoapify.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private function sanitize_language_code( $value ) {
		$value = strtolower( sanitize_text_field( (string) $value ) );
		return preg_match( '/^[a-z]{2}(?:-[a-z]{2})?$/', $value ) ? $value : '';
	}

	/**
	 * Sanitize an HTML id configured from the admin screen.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	private function sanitize_field_id( $value ) {
		$value = sanitize_text_field( (string) $value );
		$value = ltrim( $value, '#' );

		return preg_match( '/^[A-Za-z][A-Za-z0-9_-]*$/', $value ) ? $value : '';
	}
}
