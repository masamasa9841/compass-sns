<?php
/**
 * Plugin Name: compass-sns
 * Plugin URI: https://okawa.routecompass.net
 * Description: コンパス専用のsnsプラグイン
 * Version: 1.0
 * Author: Masaya Okawa
 * Author URI: https://okawa.routecompass.net
 * License: GPLv2 or later
 */

// 管理画面を表示している場合のみ実行します.
if ( is_admin() ) {
	$test_settings_page = new TestSettingsPage();
}

/**
 * Setting.
 */
class TestSettingsPage {

	/**
	 * うんこ.
	 *
	 * @var hogehgoe $options 設定値.
	 */
	private $options;

	/**
	 * 初期化処理です。
	 */
	public function __construct() {
		// メニューを追加します.
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		// ページの初期化を行います.
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * メニューを追加します。
	 */
	public function add_plugin_page() {
			// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
			//   $page_title: 設定ページの<title>部分
			//   $menu_title: メニュー名
			//   $capability: 権限 ( 'manage_options' や 'administrator' など)
			//   $menu_slug : メニューのslug
			//   $function  : 設定ページの出力を行う関数
			//   $icon_url  : メニューに表示するアイコン
			//   $position  : メニューの位置 ( 1 や 99 など )
			add_menu_page( 'テスト設定', 'テスト設定', 'manage_options', 'test_setting', array( $this, 'create_admin_page' ) );

			// 設定のサブメニューとしてメニューを追加する場合は下記のような形にします。
			// add_options_page( 'テスト設定', 'テスト設定', 'manage_options', 'test_setting', array( $this, 'create_admin_page' ) );
	}

	/**
	 * 設定ページの初期化を行います。
	 */
	public function page_init() {
			// 設定を登録します(入力値チェック用)。
			// register_setting( $option_group, $option_name, $sanitize_callback )
			//   $option_group      : 設定のグループ名
			//   $option_name       : 設定項目名(DBに保存する名前)
			//   $sanitize_callback : 入力値調整をする際に呼ばれる関数
			register_setting( 'test_setting', 'test_setting', array( $this, 'sanitize' ) );

			// 入力項目のセクションを追加します。
			// add_settings_section( $id, $title, $callback, $page )
			//   $id       : セクションのID
			//   $title    : セクション名
			//   $callback : セクションの説明などを出力するための関数
			//   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
			add_settings_section( 'test_setting_section_id', '', '', 'test_setting' );

			// 入力項目のセクションに項目を1つ追加します(今回は「メッセージ」というテキスト項目)。
			// add_settings_field( $id, $title, $callback, $page, $section, $args )
			//   $id       : 入力項目のID
			//   $title    : 入力項目名
			//   $callback : 入力項目のHTMLを出力する関数
			//   $page     : 設定ページのslug (add_menu_page()の$menu_slugと同じものにする)
			//   $section  : セクションのID (add_settings_section()の$idと同じものにする)
			//   $args     : $callbackの追加引数 (必要な場合のみ指定)
			add_settings_field( 'message', 'メッセージ', array( $this, 'message_callback' ), 'test_setting', 'test_setting_section_id' );
	}

	/**
	 * 設定ページのHTMLを出力します。
	 */
	public function create_admin_page() {
			// 設定値を取得します。
			$this->options = get_option( 'test_setting' );
			?>
			<div class="wrap">
					<h2>テスト設定</h2>
					<?php
					// add_options_page()で設定のサブメニューとして追加している場合は
					// 問題ありませんが、add_menu_page()で追加している場合
					// options-head.phpが読み込まれずメッセージが出ない(※)ため
					// メッセージが出るようにします。
					// ※ add_menu_page()の場合親ファイルがoptions-general.phpではない
					global $parent_file;
					if ( $parent_file != 'options-general.php' ) {
							require(ABSPATH . 'wp-admin/options-head.php');
					}
					?>
					<form method="post" action="options.php">
					<?php
							// 隠しフィールドなどを出力します(register_setting()の$option_groupと同じものを指定)。
							settings_fields( 'test_setting' );
							// 入力項目を出力します(設定ページのslugを指定)。
							do_settings_sections( 'test_setting' );
							// 送信ボタンを出力します。
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
			// 値を取得
			$message = isset( $this->options['message'] ) ? $this->options['message'] : '';
			// nameの[]より前の部分はregister_setting()の$option_nameと同じ名前にします。
			?>
			<input type="text" id="message" name="test_setting[message]" value="<?php esc_attr_e( $message ); ?>" />
			<?php
	}

	/**
	 * 送信された入力値の調整を行います.
	 *
	 * @param array $input 設定値.
	 */
	public function sanitize( $input ) {
			// DBの設定値を取得します。
			$this->options = get_option( 'test_setting' );

			$new_input = array();

			// メッセージがある場合値を調整
			if( isset( $input['message'] ) && trim( $input['message'] ) !== '' ) {
					$new_input['message'] = sanitize_text_field( $input['message'] );
			}
			// メッセージがない場合エラーを出力
			else {
					// add_settings_error( $setting, $code, $message, $type )
					//   $setting : 設定のslug
					//   $code    : エラーコードのslug (HTMLで'setting-error-{$code}'のような形でidが設定されます)
					//   $message : エラーメッセージの内容
					//   $type    : メッセージのタイプ。'updated' (成功) か 'error' (エラー) のどちらか
					add_settings_error( 'test_setting', 'message', 'メッセージを入力して下さい。' );

					// 値をDBの設定値に戻します。
					$new_input['message'] = isset( $this->options['message'] ) ? $this->options['message'] : '';
			}

			return $new_input;
	}

}
