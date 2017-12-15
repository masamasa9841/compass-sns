<?php
/**
 * Transition post status tweet.
 *
 * @param string $new_status New status.
 * @param string $old_status Old status.
 * @param object $post post.
 */
function hook_transition_post_status( $new_status, $old_status, $post ) {
	if ( ( 'auto-draft' === $old_status
		|| 'draft' === $old_status
		|| 'pending' === $old_status
		|| 'future' === $old_status )
		&& 'publish' === $new_status && 'post' === $post->post_type ) {
			$text = '{{AUTHOR}}さんの記事が公開されました!!{{BR}}{{TITLE}}{{BR}}{{URL}}{{BR}}';
			post_twitter( $text, $post );
	}
}
add_action( 'transition_post_status', 'hook_transition_post_status', 10, 3 );

/**
 * Post tweet everyday.
 */
function cron_twitter() {
	$posts = get_posts(array(
		'posts_per_page' => 1,
		'orderby'        => 'rand',
		'post_type'      => 'post',
	));
	$text  = '今までの記事です♪読んだ?{{BR}}{{TITLE}}{{BR}}{{URL}}{{BR}}';
	foreach ( $posts as $post ) {
		post_twitter( $text, $post );
	}
}
add_action( 'twitter_date', 'cron_twitter', 10, 3 );

/**
 * Post twitter.
 *
 * @param string $text Twitter text.
 * @param object $post post.
 */
function post_twitter( $text, $post ) {
	$options = get_option( 'cp_sns_setting' );
	$ck      = esc_attr( $options['consumer_key'] );
	$cs      = esc_attr( $options['consumer_secret'] );
	$at      = esc_attr( $options['access_token'] );
	$atc     = esc_attr( $options['access_token_secret'] );
	$twitter = new TwitterApi( $ck, $cs, $at, $atc );
	if ( has_post_thumbnail( $post->ID ) ) {
		$image_url = _get_post_thumbnail_url( $post->ID, 'large' );
		$json      = $twitter->post_media( $image_url );
		$media_id  = $twitter->get_media_id( $json );
	} else {
		$media_id = null;
	}
	$status  = $text;
	$hashtag = get_hashtag_singular_page( $post->ID );
	if ( ! empty( $hashtag ) ) {
		$status .= $hashtag;
	}
	$status = str_replace( '{{TITLE}}', $post->post_title, $status );
	$status = str_replace( '{{AUTHOR}}', get_the_author_meta( 'display_name', $post->post_author ), $status );
	$status = str_replace( '{{URL}}', get_permalink( $post->ID ), $status );
	$status = str_replace( '{{BR}}', "\n", $status );
	$twitter->tweet( $status, $media_id );
}

/**
 * Get image url.
 *
 * @param string $post_id Post id.
 * @param string $size size.
 */
function _get_post_thumbnail_url( $post_id, $size ) {
	$image_id  = get_post_thumbnail_id( $post_id );
	$images    = wp_get_attachment_image_src( $image_id, $size );
	$image_url = $images[0];
	return $image_url;
}

function get_account() {
	$options = get_option( 'cp_sns_setting' );
	$ck      = esc_attr( $options['consumer_key'] );
	$cs      = esc_attr( $options['consumer_secret'] );
	$at      = esc_attr( $options['access_token'] );
	$atc     = esc_attr( $options['access_token_secret'] );
	$twitter = new TwitterApi( $ck, $cs, $at, $atc );
	return $twitter->get_account();
}
