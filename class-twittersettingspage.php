<?php
/**
 * View for Integration Setting Meta Box.
 *
 * @package compass-sns
 * @author Masaya Okawa
 * @license GPL-2.0+
 */

require __DIR__ . '/class-twitterapi.php';
require __DIR__ . '/editor-option.php';
require __DIR__ . '/functions.php';

/**
 * Setting page.
 */
class TwitterSettingsPage {

	/**
	 * Holds the values to be used in the fields callbacks.
	 *
	 * @var object
	 */
	private $options_general;

	/**
	 * Holds the values to be used in the fields callbacks.
	 *
	 * @var object
	 */
	private $options_social;

	/**
	 * Holds the values to be used in the fields callbacks.
	 *
	 * @var object
	 */
	private $options_footer;


	/**
	 * Start up.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page.
	 */
	public function add_plugin_page() {
		add_menu_page( 'コンパスsns設定', 'コンパスsns設定', 'manage_options', 'cp_sns_setting', array( $this, 'create_admin_page' ) );
	}

	/**
	 * Register and add settings.
	 */
	public function page_init() {
		register_setting(
			'cp_sns_setting',
			'cp_sns_setting',
			array( $this, 'sanitize' )
		);
		add_settings_section(
			'cp_sns_twitter',
			'Twitter Connection',
			'',
			'cp_sns_setting'
		);
		add_settings_field( 'consumer_key', 'Consumer Key', array( $this, 'consumer_key' ), 'cp_sns_setting', 'cp_sns_twitter' );
		add_settings_field( 'consumer_secret', 'Consumer Secret', array( $this, 'consumer_secret' ), 'cp_sns_setting', 'cp_sns_twitter' );
		add_settings_field( 'access_token', 'Access Token', array( $this, 'access_token' ), 'cp_sns_setting', 'cp_sns_twitter' );
		add_settings_field( 'access_token_secret', 'Access Token Secret', array( $this, 'access_token_secret' ), 'cp_sns_setting', 'cp_sns_twitter' );

		register_setting(
			'vaajo_social', // Option group.
			'vaajo_social', // Option name.
			array( $this, 'sanitize' ) // Sanitize.
		);
		add_settings_section(
			'setting_section_id', // ID.
			'Social Settings', // Title.
			'',
			'vaajo-setting-social' // Page.
		);

		add_settings_field( 'fb_url', 'Facebook URL', array( $this, 'fb_url_callback' ), 'vaajo-setting-social', 'setting_section_id' );
	}

	/**
	 * Options page callback.
	 */
	public function create_admin_page() {
		$this->options_general = get_option( 'cp_sns_setting' );
		$this->options_social  = get_option( 'vaajo_social' );
		$this->options_footer  = get_option( 'vaajo_footer' );
		$social_screen         = ( filter_input( INPUT_GET, 'action' ) === 'social' ) ? true : false;
		$footer_screen         = ( filter_input( INPUT_GET, 'action' ) === 'footer' ) ? true : false;
		echo '<div class="wrap">';
			echo '<h2>Compass Twitter</h2>';
			?>
			<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cp_sns_setting' ) ); ?>" class="nav-tab
			<?php
			if ( filter_input( INPUT_GET, 'action' ) ) {
				echo ' nav-tab-active';
			}
			?>
			"> <?php esc_html_e( 'General' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'social' ), admin_url( 'admin.php?page=cp_sns_setting' ) ) ); ?>" class="nav-tab
			<?php
			if ( $social_screen ) {
				echo ' nav-tab-active';
			}
			?>
			"><?php esc_html_e( 'Social' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'footer' ), admin_url( 'admin.php?page=cp_sns_setting' ) ) ); ?>" class="nav-tab
			<?php
			if ( $footer_screen ) {
				echo ' nav-tab-active';
			}
			?>
			"><?php esc_html_e( 'Footer' ); ?></a>
		</h2>
		<?php
		if ( $social_screen ) {
			settings_fields( 'vaajo_social' );
			do_settings_sections( 'vaajo-setting-social' );
			submit_button();
		} elseif ( $footer_screen ) {
			settings_fields( 'vaajo_footer' );
			do_settings_sections( 'vaajo-setting-footer' );
			submit_button();
		} else {
			echo '<form method="post" action="options.php">';
				settings_fields( 'cp_sns_setting' );
				do_settings_sections( 'cp_sns_setting' );
				submit_button( 'connection' );
				echo esc_attr( getaccount() );
				echo '</form>';
			echo '</div>';
		}
	}

	/**
	 * Get the settings option array and print one of its values.
	 */
	public function consumer_key() {
		printf(
			'<input type="text" id="consumer_key" name="cp_sns_setting[consumer_key]" value="%s" />',
			isset( $this->options_general['consumer_key'] ) ? esc_attr( $this->options_general['consumer_key'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values.
	 */
	public function consumer_secret() {
		printf(
			'<input type="text" id="consumer_secret" name="cp_sns_setting[consumer_secret]" value="%s" />',
			isset( $this->options_general['consumer_secret'] ) ? esc_attr( $this->options_general['consumer_secret'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values.
	 */
	public function access_token() {
		printf(
			'<input type="text" id="access_token" name="cp_sns_setting[access_token]" value="%s" />',
			isset( $this->options_general['access_token'] ) ? esc_attr( $this->options_general['access_token'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values.
	 */
	public function access_token_secret() {
		printf(
			'<input type="text" id="access_token_secret" name="cp_sns_setting[access_token_secret]" value="%s" />',
			isset( $this->options_general['access_token_secret'] ) ? esc_attr( $this->options_general['access_token_secret'] ) : ''
		);
	}

	/**
	 * Sanitize each setting field as needed.
	 *
	 * @param array $input Contains all settings fields as array keys.
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['consumer_key'] ) && trim( $input['consumer_key'] ) !== '' ) {
			$new_input['consumer_key'] = sanitize_text_field( $input['consumer_key'] );
		}
		if ( isset( $input['consumer_secret'] ) && trim( $input['consumer_secret'] ) !== '' ) {
			$new_input['consumer_secret'] = sanitize_text_field( $input['consumer_secret'] );
		}
		if ( isset( $input['access_token'] ) && trim( $input['access_token'] ) !== '' ) {
			$new_input['access_token'] = sanitize_text_field( $input['access_token'] );
		}
		if ( isset( $input['access_token_secret'] ) && trim( $input['access_token_secret'] ) !== '' ) {
			$new_input['access_token_secret'] = sanitize_text_field( $input['access_token_secret'] );
		}
		return $new_input;
	}
}
