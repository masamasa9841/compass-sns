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

if ( is_admin() ) {
	$sns_settings_page = new TwitterSettingsPage();

	// wp-cron.
	if ( ! wp_next_scheduled( 'twitter_date' ) ) {
		// If time zone is tokyo, now time - 9hour.
		wp_schedule_event( strtotime( '2017-11-1 23:00:00' ), 'daily', 'twitter_date' );
	}
}
