<?php
/*
Plugin Name: Comment by Tweet
Plugin URI: http://amauri.champeaux.fr/comment-by-tweet/
Description: Système de commentaires basé sur les hashtags Twitter
Version: 0.4.1
Author: Amauri CHAMPEAUX
Author URI: http://amauri.champeaux.fr/a-propos/
*/

if(!class_exists('CommentByTweet'))
{
	class CommentByTweet
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// Initialize Settings
			require_once(sprintf("%s/API.php", dirname(__FILE__)));
			require_once(sprintf("%s/Admin.php", dirname(__FILE__)));
			require_once(sprintf("%s/Editor.php", dirname(__FILE__)));
			require_once(sprintf("%s/Tools.php", dirname(__FILE__)));
		}
		
		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			global $wpdb;

			// create db
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE ".$wpdb->prefix."cbt_api (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`url` varchar(55) NOT NULL DEFAULT '',
				`total` mediumint(9) unsigned NOT NULL,
				`quart` int(3) unsigned NOT NULL,
				`limitation` int(3) unsigned NOT NULL,
				UNIQUE KEY `id` (`id`),
				UNIQUE KEY `url` (`url`)
			) $charset_collate;";
	
			$sql .= "CREATE TABLE ".$wpdb->prefix."cbt_hash (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`hash` varchar(55) NOT NULL DEFAULT '',
				`last_id` varchar(30) NOT NULL DEFAULT '-1',
				UNIQUE KEY `id` (`id`),
				UNIQUE KEY `hash` (`hash`)
			) $charset_collate;";
	
			$sql .= "CREATE TABLE ".$wpdb->prefix."cbt_tweets (
				`id` mediumint(9) NOT NULL AUTO_INCREMENT,
				`hash_id` mediumint(9) NOT NULL,
				`tweet_id` varchar(30) NOT NULL DEFAULT '',
				`lang` varchar(2) NOT NULL DEFAULT '',
				`text` varchar(200) NOT NULL DEFAULT '',
				`user_id` varchar(30) NOT NULL DEFAULT '',
				`user_name` varchar(50) NOT NULL DEFAULT '',
				`user_screen_name` varchar(30) NOT NULL DEFAULT '',
				`created_at` varchar(30) NOT NULL DEFAULT '',
				UNIQUE KEY `id` (`id`),
				UNIQUE KEY `tweet_id` (`tweet_id`, `hash_id`)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
	
			// add data	
			$table_name = $wpdb->prefix . 'cbt_api';
			$urls = array(
				array('/account/verify_credentials', 15),
				array('/application/rate_limit_status', 180),
				array('/friends/ids', 15),
				array('/search/tweets', 180)
			);

			foreach($urls as $url) {
				$wpdb->insert( 
					$table_name, 
					array( 
						'url' => $url[0],
						'limitation' => $url[1]
					) 
				);
			}
	
			// add cron
			wp_schedule_event( time(), 'quart', 'commentbytweetpurge' );
		}
		
		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{
			global $wpdb;
	
			// remove db
			$wpdb->query("DROP TABLE {$wpdb->prefix}cbt_api");
			$wpdb->query("DROP TABLE {$wpdb->prefix}cbt_hash");
			$wpdb->query("DROP TABLE {$wpdb->prefix}cbt_tweets");
	
			// remove cron
			wp_clear_scheduled_hook( 'commentbytweetpurge' );
		}
		
		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=wp_plugin_template">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
	}
}

if(class_exists('CommentByTweet'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('CommentByTweet', 'activate'));
	register_deactivation_hook(__FILE__, array('CommentByTweet', 'deactivate'));
	
	// instantiate the plugin class
	$CommentByTweet = new CommentByTweet();
}