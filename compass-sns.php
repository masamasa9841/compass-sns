<?php
/**
 * Main plugin's file.
 *
 * @package compass-sns
 */

/**
 * Plugin Name: compass-sns
 * Plugin URI: https://okawa.routecompass.net
 * Description: コンパス専用のsnsプラグイン
 * Version: 1.0
 * Author: Masaya Okawa
 * Author URI: https://okawa.routecompass.net
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

require __DIR__ . '/class-twittersettingspage.php';
require __DIR__ . '/class-twitterapi.php';
require __DIR__ . '/editor-option.php';

if ( is_admin() ) {
	$sns_settings_page = new TwitterSettingsPage();

	// wp-cron.
	if ( ! wp_next_scheduled( 'twitter_date' ) ) {
		// If time zone is tokyo, now time - 9hour.
		wp_schedule_event( strtotime( '2017-11-1 23:00:00' ), 'daily', 'twitter_date' );
	}
}

/**
 * Transition post status tweet..
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
	$hashtag = get_hashtag_singular_page();
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
