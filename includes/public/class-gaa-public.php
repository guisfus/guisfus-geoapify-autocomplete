<?php
/**
 * Frontend asset loading.
 *
 * @package GeoapifyAutocomplete
 */

defined( 'ABSPATH' ) || exit;

/**
 * Frontend integration.
 */
final class GAA_Public {

	/**
	 * Settings service.
	 *
	 * @var GAA_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param GAA_Settings $settings Settings service.
	 */
	public function __construct( GAA_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Enqueue frontend assets when an API key is configured.
	 */
	public function enqueue_assets() {
		$settings = $this->settings->get();
		if ( empty( $settings['api_key'] ) ) {
			return;
		}

		wp_enqueue_style(
			'geoapify-autocomplete-frontend',
			GEOAPIFY_AUTOCOMPLETE_URL . 'assets/css/frontend.css',
			array(),
			GEOAPIFY_AUTOCOMPLETE_VERSION
		);

		wp_enqueue_script(
			'geoapify-autocomplete-frontend',
			GEOAPIFY_AUTOCOMPLETE_URL . 'assets/js/frontend.js',
			array(),
			GEOAPIFY_AUTOCOMPLETE_VERSION,
			true
		);

		/**
		 * Filters the public frontend configuration.
		 *
		 * The Geoapify API key is intentionally sent to the browser because the
		 * autocomplete endpoint is called client-side. Restrict the key by HTTP
		 * referrer in Geoapify before using it in production.
		 *
		 * @param array<string,mixed> $config Public configuration.
		 * @param array<string,mixed> $settings Normalized plugin settings.
		 */
		$config = apply_filters(
			'geoapify_autocomplete_public_config',
			array(
				'apiKey'      => $settings['api_key'],
				'countryCode' => $settings['country_code'],
				'lang'        => $settings['lang'],
				'minChars'    => (int) $settings['min_chars'],
				'limit'       => (int) $settings['limit'],
				'forms'       => $settings['forms'],
				'i18n'        => array(
					'loading'   => __( 'Searching addresses...', 'geoapify-autocomplete' ),
					'noResults' => __( 'No addresses found.', 'geoapify-autocomplete' ),
					'available' => __( 'results available.', 'geoapify-autocomplete' ),
				),
			),
			$settings
		);

		wp_localize_script( 'geoapify-autocomplete-frontend', 'GeoapifyAutocompleteConfig', $config );
	}
}
