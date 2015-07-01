<?php

if(!class_exists('ToolsCommentByTweet'))
{
	class ToolsCommentByTweet
	{
		public function __construct() {
			add_action('wp_enqueue_scripts', array($this, 'css'));
			add_action( 'admin_enqueue_scripts', array($this, 'css_admin') );
			add_action('wp_footer', array($this, 'footer'));
			add_shortcode( 'twitter_icon', array($this, 'shortcode_icon') );
			add_shortcode( 'twitter_linkhash', array($this, 'shortcode_hash') );
			add_filter( 'cron_schedules', array($this, 'cron_add_job')); 
			add_action( 'commentbytweetpurge', array($this, 'cron_job') );
		}
		
		public function css() {
			wp_enqueue_style('commentByTweet', plugins_url('comment-by-tweet/fontello/css/fontello.css'));
			wp_enqueue_script('jquery');
			wp_enqueue_script('commentByTweetSDK', plugins_url('comment-by-tweet/js/twitter-sdk.js'));
			wp_enqueue_script('commentByTweetHighlight', plugins_url('comment-by-tweet/js/tweet-on-highlight.js'), array( 'jquery' ));
		}
		
		public function css_admin() {
			wp_enqueue_style( 'commentByTweet', plugins_url( 'comment-by-tweet/css/custom-icon.css' ) );
		}
		
		/**
		* Display div container at bottom.
		*/
		public function footer() {
			global $post;
	
			$hash = get_post_meta( $post->ID, 'commentByTweetHash', true );
			if ($hash != '' AND get_option('commentByTweet_TWEETBUTTON') == 'on' AND is_singular()) {
				echo '<input type="hidden" id="commentByTweetHash" value="'.$hash.'" /><div id="commentByTweetMe" style="display:none;position:absolute;z-index:2147483647"></div>';
			}
		}
		
		/**
		* Icon shortcodes (by fontello).
		*/
		public function shortcode_icon() {
			return '<span class="icon_54047278-twitter"></span>';
		}

		/**
		* Tweet link shortcode.
		*
		* param @array $atts Text
		*/
		function shortcode_hash($atts) {
			global $post;
	
			if(get_post_meta( $post->ID, 'commentByTweetHash', true ) != '') {
				$hash = ' #'.get_post_meta( $post->ID, 'commentByTweetHash', true );
			}
	
			return 'https://twitter.com/intent/tweet?text=' . urlencode(preg_replace("/_apos_/", "'", $atts['text']) . $hash . ' ' . get_permalink($post->ID));
		}
		
		/**
		* Create cron interval.
		*/
		public function cron_add_job( $schedules ) {
			$schedules['quart'] = array(
				'interval' => (60*15),
				'display' => __( 'Every 15mns' )
			);
			return $schedules;
		}
		
		/**
		* Purge stats every 15mns
		*/
		public function cron_job() {
			global $wpdb;
			$wpdb->query("UPDATE {$wpdb->prefix}cbt_api SET quart = ''");
		}
	}
}

if(class_exists('ToolsCommentByTweet'))
{
	$ToolsCommentByTweet = new ToolsCommentByTweet();
}