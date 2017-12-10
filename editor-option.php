<?php
/**
 * The template for editor_option.
 *
 * @package compass-sns
 * @author Masaya Okawa
 * @license GPL-2.0+
 */

/**
 * Box init.
 */
function add_compass_box_init() {
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'post', 'side' );
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'page', 'side' );
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'topic', 'side' );
}
add_action( 'add_meta_boxes', 'add_compass_box_init' );

/**
 * Box html.
 */
function add_my_box_hashtag() {
	global $post;
	$hashtag = get_post_meta( get_the_ID(), 'hashtag', true );
	$hashtag = htmlspecialchars( $hashtag );
	echo '<input type="text" style="width: 100%;" placeholder="ハッシュタグの追加" name="hashtag" value="' . esc_html( $hashtag ) . '" />';
	echo '<p class="howto" style="margin-top:0;">example: #うんこ #はなまるうどん</p>';
	echo '<p class="howto" style="margin-top:0;">何もない場合は無視されます。</p>';
}

/**
 * Save custom data of hashtag.
 */
function save_hashtag_custom_data() {
	$id      = get_the_ID();
	$hashtag = null;
	if ( isset( $_POST['hashtag'] ) ) {
		$hashtag = $_POST['hashtag'];
	}
	$hashtag_key = 'hashtag';
	add_post_meta( $id, $hashtag_key, $hashtag, true );
	update_post_meta( $id, $hashtag_key, $hashtag );
}
add_action( 'save_post', 'save_hashtag_custom_data' );

/**
 * Get hash tags.
 *
 * @return string $hashtag Hashtags.
 */
function get_hashtag_singular_page() {
	$hashtag = get_post_meta( get_the_ID(), 'hashtag', true );
	$hashtag = str_replace( '＃', '#', $hashtag );
	return $hashtag;
}

function my_posts_columns( $defaults ) {
	$defaults['hashtag'] = 'hashtag';

	return $defaults;
}
add_filter( 'manage_posts_columns', 'my_posts_columns' );

function my_posts_custom_column( $column, $post_id ) {
	switch ( $column ) {
			case 'hashtag':
					$post_meta = get_post_meta( $post_id, 'hashtag', true );
							echo $post_meta;
					break;
	}
}
add_action( 'manage_posts_custom_column' , 'my_posts_custom_column', 10, 2 );

function display_my_custom_quickedit( $column_name, $post_type ) {
	static $print_nonce = TRUE;
	if ( $print_nonce ) {
			$print_nonce = FALSE;
			wp_nonce_field( 'quick_edit_action', $post_type . '_edit_nonce' ); //CSRF対策
	}

	?>
	<fieldset class="inline-edit-col-right inline-custom-meta">
			<div class="inline-edit-col column-<?php echo $column_name ?>">
					<label class="inline-edit-group">
							<?php
							switch ( $column_name ) {
									case 'hashtag':
											?><span class="title unko">hashtag</span><input name="hashtag"/><?php
											break;
							}
							?>
					</label>
			</div>
	</fieldset>
<?php
}
add_action( 'quick_edit_custom_box', 'display_my_custom_quickedit', 10, 2 );

function my_admin_edit_foot() {
	global $post_type;
	$slug = 'post'; //他の一覧ページで動作しないように投稿タイプの指定をする

	if ( $post_type == $slug ) {
			echo '<script type="text/javascript">';
			?>
(function($) {
  var $wp_inline_edit = inlineEditPost.edit;
  inlineEditPost.edit = function( id ) {
      $wp_inline_edit.apply( this, arguments );

      var $post_id = 0;
      if ( typeof( id ) == 'object' )
          $post_id = parseInt( this.getId( id ) );

      if ( $post_id > 0 ) {
          var $edit_row = $( '#edit-' + $post_id );
          var $post_row = $( '#post-' + $post_id );

          var $hashtag = $( '.column-hashtag', $post_row ).html();
          $( ':input[name="hashtag"]', $edit_row ).val( $hashtag );

      }
  };

})(jQuery);
<?php
			echo '</script>';
	}
}
add_action('admin_footer-edit.php', 'my_admin_edit_foot');

function save_custom_meta( $post_id ) {
	$slug = 'post'; //カスタムフィールドの保存処理をしたい投稿タイプを指定

	if ( $slug !== get_post_type( $post_id ) ) {
			return;
	}
	if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
	}

	$_POST += array("{$slug}_edit_nonce" => '');
	if ( !wp_verify_nonce( $_POST["{$slug}_edit_nonce"], 'quick_edit_action' ) ) {
			return;
	}

	if ( isset( $_REQUEST['hashtag'] ) ) {
			update_post_meta( $post_id, 'hashtag', $_REQUEST['hashtag'] );
	}

	//チェックボックスの場合
	if ( isset( $_REQUEST['display'] ) ) {
			update_post_meta($post_id, 'display', TRUE);
	} else {
			update_post_meta($post_id, 'display', FALSE);
	}
}
add_action( 'save_post', 'save_custom_meta' );
