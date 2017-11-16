<?php
/**
 *
 * Class file.
 *
 * @package compass-sns
 * @author Masaya Okawa
 * @license GPL-2.0+
 */

/**
 * うんこ
 */
class TwitterSettingsPage {

	/**
	 * うん.
	 *
	 * @var object Option.
	 */
	private $options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * メニューの追加
	 */
	public function add_plugin_page() {
		add_menu_page( 'コンパスsns設定', 'コンパスsns設定', 'manage_options', 'cp_sns_setting', array( $this, 'create_admin_page' ) );
	}

	/**
	 * 設定ページの初期化を行います。
	 */
	public function page_init() {
		// 設定を登録します(入力値チェック用).
		register_setting( 'cp_sns_setting', 'cp_sns_twitter_ck', array( $this, 'sanitize' ) );

		// 入力項目のセクションを追加します.
		add_settings_section( 'cp_sns_twitter_ck', '', '', 'cp_sns_setting' );
		add_settings_field( 'message', 'CONSUMER_KEY', array( $this, 'message_callback' ), 'cp_sns_setting', 'cp_sns_twitter_ck' );
	}

	/**
	 * 設定ページのHTML.
	 */
	public function create_admin_page() {
		$this->options = get_option( 'cp_sns_setting' );
		?>
		<div class="wrap">
			<h2>Twitter設定</h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'cp_sns_setting' );
				do_settings_sections( 'cp_sns_setting' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * 入力項目(「メッセージ」)のHTMLを出力します。
	 */
	public function message_callback() {
			$message = isset( $this->options['message'] ) ? $this->options['message'] : '';
			?>
			<input type="text" id="message" name="cp_sns_twitter_ck[message]" value="<?php esc_attr_e( $message ); ?>" />
			<?php
	}

	/**
	 * 送信された入力値の調整を行います.
	 *
	 * @param array $input 設定値.
	 */
	public function sanitize( $input ) {
		$this->options = get_option( 'cp_sns_setting' );
		$new_input     = array();
		if ( isset( $input['message'] ) && trim( $input['message'] ) !== '' ) {
			$new_input['message'] = sanitize_text_field( $input['message'] );
		} else {
			add_settings_error( 'cp_sns_setting', 'message', 'メッセージを入力して下さい。' );
			$new_input['message'] = isset( $this->options['message'] ) ? $this->options['message'] : '';
		}
		return $new_input;
	}

}
