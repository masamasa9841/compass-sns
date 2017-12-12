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
 *
 * @param string $post_id post id.
 */
function save_hashtag_custom_data( $post_id ) {
	$hashtag_key = 'hashtag';
	if ( isset( $_POST['hashtag'] ) ) {
		update_post_meta( $post_id, $hashtag_key, $_POST['hashtag'] );
	}
	if ( isset( $_REQUEST['hashtag'] ) ) {
		update_post_meta( $post_id, $hashtag_key, $_REQUEST['hashtag'] );
	}
}
add_action( 'save_post', 'save_hashtag_custom_data' );

/**
 * Get hash tags.
 *
 * @param string $post_id post id.
 * @return string $hashtag Hashtags.
 */
function get_hashtag_singular_page( $post_id ) {
	$hashtag = get_post_meta( $post_id, 'hashtag', true );
	$hashtag = str_replace( '＃', '#', $hashtag );
	return $hashtag;
}

/**
 * Add post columns.
 *
 * @param array $defaults colum data.
 * @return array $defaults Add Hashtags.
 */
function my_posts_columns( $defaults ) {
	$defaults['hashtag'] = 'hashtag';
	return $defaults;
}
add_filter( 'manage_posts_columns', 'my_posts_columns' );

/**
 * Add post columns.
 *
 * @param array  $column colum data.
 * @param string $post_id post id.
 */
function my_posts_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'hashtag':
			$post_meta = get_post_meta( $post_id, 'hashtag', true );
			echo esc_html( $post_meta );
			break;
	}
}
add_action( 'manage_posts_custom_column', 'my_posts_custom_column', 10, 2 );

/**
 * Setting html.
 *
 * @param array $column_name column name.
 */
function display_my_custom_quickedit( $column_name ) {
	?>
	<fieldset class="inline-edit-col-right inline-custom-meta">
		<div class="inline-edit-col column-<?php echo esc_html( $column_name ); ?>">
			<label class="inline-edit-group">
				<?php
				switch ( $column_name ) {
					case 'hashtag':
						?>
						<span class='title'>hashtag</span><input name="hashtag"/>
						<?php
						break;
				}
				?>
			</label>
		</div>
	</fieldset>
<?php
}
add_action( 'quick_edit_custom_box', 'display_my_custom_quickedit', 10, 2 );

/**
 * Load Javascript
 */
function my_admin_edit_foot() {
	wp_enqueue_script( 'test', plugins_url( 'js/admin_edit.js', __FILE__ ), false, null, true );
}
add_action( 'admin_enqueue_scripts', 'my_admin_edit_foot' );

