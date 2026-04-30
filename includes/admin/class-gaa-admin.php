<?php
/**
 * Admin settings screen.
 *
 * @package GeoapifyAutocomplete
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin integration.
 */
final class GAA_Admin {

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
	 * Register the plugin option.
	 */
	public function register_settings() {
		register_setting(
			'geoapify_autocomplete_group',
			GAA_Settings::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
			)
		);
	}

	/**
	 * Add the settings page.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Geoapify Autocomplete', 'geoapify-autocomplete' ),
			__( 'Geoapify', 'geoapify-autocomplete' ),
			'manage_options',
			'geoapify-autocomplete',
			array( $this, 'render_settings_page' )
		);

		add_action(
			'load-settings_page_geoapify-autocomplete',
			function () {
				$screen = get_current_screen();
				if ( ! $screen ) {
					return;
				}

				$screen->add_help_tab(
					array(
						'id'      => 'geoapify_autocomplete_help',
						'title'   => __( 'Geoapify help', 'geoapify-autocomplete' ),
						'content' => '<p><strong>' . esc_html__( 'Admin mode:', 'geoapify-autocomplete' ) . '</strong> ' . esc_html__( 'Configure field IDs in Settings.', 'geoapify-autocomplete' ) . '</p>' .
							'<p><strong>' . esc_html__( 'HTML mode:', 'geoapify-autocomplete' ) . '</strong> ' . esc_html__( 'Use data attributes on the address input.', 'geoapify-autocomplete' ) . '</p>' .
							'<pre>&lt;input data-geoapify="address" data-geoapify-city="#city" data-geoapify-state="#state" data-geoapify-zip="#postcode"&gt;</pre>',
					)
				);
			}
		);
	}

	/**
	 * Enqueue admin assets only on the plugin settings screen.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_geoapify-autocomplete' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'geoapify-autocomplete-admin',
			GEOAPIFY_AUTOCOMPLETE_URL . 'assets/css/admin.css',
			array(),
			GEOAPIFY_AUTOCOMPLETE_VERSION
		);

		wp_enqueue_script(
			'geoapify-autocomplete-admin',
			GEOAPIFY_AUTOCOMPLETE_URL . 'assets/js/admin.js',
			array(),
			GEOAPIFY_AUTOCOMPLETE_VERSION,
			true
		);

		wp_localize_script(
			'geoapify-autocomplete-admin',
			'GeoapifyAutocompleteAdminConfig',
			array(
				'optionKey' => GAA_Settings::OPTION_KEY,
				'i18n'      => array(
					'formTitle' => __( 'Form #', 'geoapify-autocomplete' ),
					'address'   => __( 'Address field ID', 'geoapify-autocomplete' ),
					'city'      => __( 'City field ID', 'geoapify-autocomplete' ),
					'state'     => __( 'State field ID', 'geoapify-autocomplete' ),
					'zip'       => __( 'Postal code field ID', 'geoapify-autocomplete' ),
					'remove'    => __( 'Remove', 'geoapify-autocomplete' ),
				),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab          = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
		$tab          = in_array( $tab, array( 'settings', 'docs' ), true ) ? $tab : 'settings';
		$settings     = $this->settings->get();
		$forms        = $settings['forms'];
		$settings_url = add_query_arg( array( 'page' => 'geoapify-autocomplete', 'tab' => 'settings' ), admin_url( 'options-general.php' ) );
		$docs_url     = add_query_arg( array( 'page' => 'geoapify-autocomplete', 'tab' => 'docs' ), admin_url( 'options-general.php' ) );
		?>
		<div class="wrap">
			<h1 class="gaa-admin-title"><?php esc_html_e( 'Geoapify Autocomplete', 'geoapify-autocomplete' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo esc_url( $settings_url ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Settings', 'geoapify-autocomplete' ); ?>
				</a>
				<a href="<?php echo esc_url( $docs_url ); ?>" class="nav-tab <?php echo 'docs' === $tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Documentation', 'geoapify-autocomplete' ); ?>
				</a>
			</h2>

			<?php if ( 'settings' === $tab ) : ?>
				<form method="post" action="options.php">
					<?php settings_fields( 'geoapify_autocomplete_group' ); ?>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Geoapify API key', 'geoapify-autocomplete' ); ?></th>
							<td>
								<input class="regular-text" type="text" autocomplete="off" spellcheck="false" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[api_key]" value="<?php echo esc_attr( $settings['api_key'] ); ?>">
								<p class="description"><?php esc_html_e( 'Restrict this key by HTTP referrer in Geoapify before using it on production websites.', 'geoapify-autocomplete' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Country code', 'geoapify-autocomplete' ); ?></th>
							<td>
								<input class="small-text" type="text" maxlength="2" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[country_code]" value="<?php echo esc_attr( $settings['country_code'] ); ?>">
								<p class="description"><?php esc_html_e( 'Optional ISO 3166-1 alpha-2 code, for example es, mx or us. Leave empty for worldwide search.', 'geoapify-autocomplete' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Language', 'geoapify-autocomplete' ); ?></th>
							<td>
								<input class="small-text" type="text" maxlength="5" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[lang]" value="<?php echo esc_attr( $settings['lang'] ); ?>">
								<p class="description"><?php esc_html_e( 'Optional language code, for example es or en.', 'geoapify-autocomplete' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Minimum characters', 'geoapify-autocomplete' ); ?></th>
							<td><input class="small-text" type="number" min="2" max="10" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[min_chars]" value="<?php echo esc_attr( $settings['min_chars'] ); ?>"></td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Suggestion limit', 'geoapify-autocomplete' ); ?></th>
							<td><input class="small-text" type="number" min="1" max="20" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[limit]" value="<?php echo esc_attr( $settings['limit'] ); ?>"></td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Field mappings by input ID', 'geoapify-autocomplete' ); ?></th>
							<td>
								<div id="gaa-forms">
									<?php foreach ( $forms as $i => $row ) : ?>
										<div class="gaa-form-row">
											<p><strong><?php echo esc_html__( 'Form #', 'geoapify-autocomplete' ) . esc_html( (string) ( $i + 1 ) ); ?></strong></p>
											<?php $this->render_mapping_input( $i, 'address', __( 'Address field ID', 'geoapify-autocomplete' ), $row['address'] ); ?>
											<?php $this->render_mapping_input( $i, 'city', __( 'City field ID', 'geoapify-autocomplete' ), $row['city'] ); ?>
											<?php $this->render_mapping_input( $i, 'state', __( 'State field ID', 'geoapify-autocomplete' ), $row['state'] ); ?>
											<?php $this->render_mapping_input( $i, 'zip', __( 'Postal code field ID', 'geoapify-autocomplete' ), $row['zip'] ); ?>
											<button type="button" class="button gaa-remove"><?php esc_html_e( 'Remove', 'geoapify-autocomplete' ); ?></button>
										</div>
									<?php endforeach; ?>
								</div>

								<button type="button" class="button button-secondary" id="gaa-add">
									<?php esc_html_e( 'Add another form', 'geoapify-autocomplete' ); ?>
								</button>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			<?php endif; ?>

			<?php if ( 'docs' === $tab ) : ?>
				<div class="gaa-docs">
					<h2><?php esc_html_e( 'How to use it', 'geoapify-autocomplete' ); ?></h2>
					<h3><?php esc_html_e( 'Admin mode', 'geoapify-autocomplete' ); ?></h3>
					<p><?php esc_html_e( 'Enter the ID of the address input and, optionally, the IDs of city, state and postal code inputs. The address input receives the suggestions and the other fields are filled after selection.', 'geoapify-autocomplete' ); ?></p>

					<h3><?php esc_html_e( 'HTML data attributes', 'geoapify-autocomplete' ); ?></h3>
					<p><?php esc_html_e( 'Add data attributes directly to the address input when you prefer not to store mappings in the WordPress admin.', 'geoapify-autocomplete' ); ?></p>
					<pre>&lt;input
  data-geoapify="address"
  data-geoapify-city="#city"
  data-geoapify-state="#state"
  data-geoapify-zip="#postcode"&gt;</pre>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render one field mapping input.
	 *
	 * @param int    $index Mapping index.
	 * @param string $key Field key.
	 * @param string $label Field label.
	 * @param string $value Current value.
	 */
	private function render_mapping_input( $index, $key, $label, $value ) {
		?>
		<p>
			<label><strong><?php echo esc_html( $label ); ?></strong></label><br>
			<input class="regular-text" type="text" name="<?php echo esc_attr( GAA_Settings::OPTION_KEY ); ?>[forms][<?php echo esc_attr( (string) $index ); ?>][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>">
		</p>
		<?php
	}
}
