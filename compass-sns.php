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
require __DIR__ . '/twitter_api.php';
require __DIR__ . '/editor-option.php';

if ( is_admin() ) {
	$sns_settings_page = new TwitterSettingsPage();
}

/**
 * Post twitter.
 *
 * @param string $new_status New status.
 * @param string $old_status Old status.
 * @param object $post post.
 */
function hook_transition_post_status( $new_status, $old_status, $post ) {
	$options = get_option( 'cp_sns_setting' );
	$ck      = esc_attr( $options['consumer_key'] );
	$cs      = esc_attr( $options['consumer_secret'] );
	$at      = esc_attr( $options['access_token'] );
	$atc     = esc_attr( $options['access_token_secret'] );
	if ( ( 'auto-draft' === $old_status
		|| 'draft' === $old_status
		|| 'pending' === $old_status
		|| 'future' === $old_status )
		&& 'publish' === $new_status && 'post' === $post->post_type ) {
		$twitter = new TwitterApi( $ck, $cs, $at, $atc );
		if ( has_post_thumbnail( $post->ID ) ) {
			$image_url = get_the_post_thumbnail_url( $post->ID, 'large' );
			$json      = $twitter->post_media( $image_url );
			$media_id  = $twitter->get_media_id( $json );
		} else {
			$media_id = null;
		}
		$status  = get_the_author_meta( 'display_name', $post->post_author ) . 'さんの記事が公開されました!!{{BR}}{{TITLE}}{{BR}}{{URL}}{{BR}}';
		$hashtag = get_hashtag_singular_page();
		if ( ! empty( $hashtag ) ) {
			$status .= $hashtag;
		}
		$status = str_replace( '{{TITLE}}', $post->post_title, $status );
		$status = str_replace( '{{URL}}', get_permalink( $post->ID ), $status );
		$status = str_replace( '{{BR}}', "\n", $status );
		$twitter->tweet( $status, $media_id );
	}
}
add_action( 'transition_post_status', 'hook_transition_post_status', 10, 3 );
