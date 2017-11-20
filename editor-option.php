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
function add_my_box_init() {
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'post', 'side' );
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'page', 'side' );
	add_meta_box( 'hashtag', 'Twitterハッシュタグ', 'add_my_box_hashtag', 'topic', 'side' );
}
add_action( 'add_meta_boxes', 'add_my_box_init' );

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
