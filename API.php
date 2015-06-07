<?php

if(!class_exists('APICommentByTweet'))
{
	class APICommentByTweet
	{
		public function __construct() {
			add_filter('comments_template', array($this, 'load_template'));
			add_shortcode('comment_by_tweet', array($this, 'shortcode'));
		}
		
		/**
		* Display the new comment template if hashtag is set.
		*/
		public function load_template($template) {
			global $post;
			if ( !is_singular() || get_post_meta( $post->ID, 'commentByTweetHash', true ) == '') {
				return;
			}
			return dirname(__FILE__) . '/templates/comments.php';
		}
		
		/**
		* Show the comment template via shortcode.
		*/
		public function shortcode() {
			global $post;
			if ( !is_singular() || get_post_meta( $post->ID, 'commentByTweetHash', true ) == '') {
				return;
			}
			return '<div id="comments" class="comments-area">
				<!-- commment-by-tweet -->
				'.$this->show_tweets(false).'
			</div>';
		}
		
		/**
		* Retrieve the friend list ids.
		*/
		public function retrieveFriends(&$tmhOAuth, $userId, $arr = array(), $cursor = -1) {
			if($this->check_ping('/friends/ids')) {
				$friend = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/friends/ids.json'), array('cursor' => $cursor, 'user_id' => $userId, 'count' => 5000));
			}
			if ($friend == 200) {
				$friendList = json_decode($tmhOAuth->response['response'], true);
        
				$return = array_merge($friendList['ids'], $arr);
        
				if ($friendList['next_cursor'] != 0) {
					$this->retrieveFriends($tmhOAuth, $userId, $return, $friendList['next_cursor']);
				} else {
					return $return;
				}
			}
		}
		
		/**
		* Retrieve all tweets.
		*/
		public function show_tweets($echo = true) {
			global $wpdb, $post;
	
			$hash = get_post_meta( $post->ID, 'commentByTweetHash', true );

			if ($hash == '' ||
				get_option('commentByTweet_CONSUMER_KEY') == FALSE ||
				get_option('commentByTweet_CONSUMER_SECRET') == FALSE ||
				get_option('commentByTweet_ACCESS_TOKEN') == FALSE ||
				get_option('commentByTweet_ACCESS_TOKEN_SECRET') == FALSE) {
				return;
			}
    
			// is cached ?
			$cache = get_transient( 'tweets_h-'.$hash );
			if ($cache == '1' && !current_user_can('edit_post', $post->ID) ) {
				if($echo == true) {
					echo $this->render($hash);
					return;
				} else {
					return $this->render($hash);
				}
			}
    
			// oauth
			include(dirname(__FILE__) . '/tmhOAuth/tmhOAuth.php');
			$tmhOAuth = new tmhOAuth(array(
				'consumer_key'     => get_option('commentByTweet_CONSUMER_KEY'),
				'consumer_secret'  => get_option('commentByTweet_CONSUMER_SECRET'),
				'user_token'       => get_option('commentByTweet_ACCESS_TOKEN'),
				'user_secret'      => get_option('commentByTweet_ACCESS_TOKEN_SECRET'),
			));
	
			// store hash info
			$hash_info = $wpdb->get_row($wpdb->prepare("SELECT `id`, `last_id` FROM {$wpdb->prefix}cbt_hash WHERE `hash` = %s", $hash));
			if($hash_info->id == '') {
				$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}cbt_hash (`last_id`, `hash`) VALUES(%d, %s)", 0, $hash));
				$hash_info = $wpdb->get_row($wpdb->prepare("SELECT `id`, `last_id` FROM {$wpdb->prefix}cbt_hash WHERE `hash` = %s", $hash));
			}

			// get json
			if($this->check_ping('/search/tweets')) {
				$response = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/search/tweets.json'), array('q' => '%23'.$hash.'%20-filter:retweets', 'since_id' => $hash_info->last_id, 'count' => 100, 'result_type' => 'mixed'));
			}
     
			// 200 = ok
			if ($response == 200) {
        
				// store result
				$data = json_decode($tmhOAuth->response['response'], true);
		
				// construct the blockquote for embed tweet
				foreach ($data['statuses'] as $tweet) {
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}cbt_tweets
						( `hash_id`, `tweet_id`, `lang`, `text`, `user_id`, `user_name`, `user_screen_name`, `created_at` )
						VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )", 
						$hash_info->id,
						$tweet['id'], 
						$tweet['lang'],
						$tweet['text'],
						$tweet['user']['id_str'],
						$tweet['user']['name'],
						$tweet['user']['screen_name'],
						$tweet['created_at']
					) );
				}
		
				$last_id = $data['search_metadata']['max_id_str'];
				$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}cbt_hash SET `last_id` = %s WHERE `hash` = %s", $last_id, $hash));
 
				// store to cache
				$delay = time() - get_the_time('U');
				if ($delay < 86400) {$cacheFor = 600;} // cache 10mn the first day
				elseif ($delay >= 86400 && $delay < (86400*3)) {$cacheFor = 3600;} // cache for 1
				elseif ($delay >= (86400*3) && $delay < (86400*7)) {$cacheFor = 7200;} // cache for 2 hours the first week
				else {$cacheFor = 86400;} // cache for 1 day
				set_transient('tweets_h-'.$hash, '1', $cacheFor);
        
				if($echo == true) {
					echo $this->render($hash);
				} else {
					return $this->render($hash);
				}
			}
		}
		
		/**
		* Filter and Render tweets in HTML.
		*/
		public function render($hash) {
			global $wpdb, $post;
	
			$return = '';
			$nbTweets = 0;

			// filtres
			$lang = get_post_meta( $post->ID, 'commentByTweetLang', true );
			$from = get_post_meta( $post->ID, 'commentByTweetFrom', true );
			$fromTo = get_post_meta( $post->ID, 'commentByTweetFromTo', true );
			$fromFrom = get_post_meta( $post->ID, 'commentByTweetFromFrom', true );
			$fromMention = get_post_meta( $post->ID, 'commentByTweetFromMention', true );
    
			// get all the friend ids if antispam is on
			if(get_post_meta( $post->ID, 'commentByTweetSpam', true ) == 'on') {
        
				// check cache (24 hours)
				$Ids_cached = get_transient( 'twitter_abonnements' );
				if($Ids_cached != false) {
					$Ids = $Ids_cached;
				} else {
			
					// oauth
					include(dirname(__FILE__) . '/tmhOAuth/tmhOAuth.php');
					$tmhOAuth = new tmhOAuth(array(
						'consumer_key'     => get_option('commentByTweet_CONSUMER_KEY'),
						'consumer_secret'  => get_option('commentByTweet_CONSUMER_SECRET'),
						'user_token'       => get_option('commentByTweet_ACCESS_TOKEN'),
						'user_secret'      => get_option('commentByTweet_ACCESS_TOKEN_SECRET'),
					));
	
					// get the current id associated to the app
					if($this->check_ping('/account/verify_credentials')) {
						$userId = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials.json'));
					}
					if ($userId == 200) {
						$userData = json_decode($tmhOAuth->response['response'], true);
					}
        
					// get friend list
					$Ids = $this->retrieveFriends($tmhOAuth, $userData['id'], array($userData['id']));
					set_transient('twitter_abonnements', $Ids, 21600);
				}
			}
		
			$query = $wpdb->get_results($wpdb->prepare("SELECT `tweet_id`, `lang`, `text`, `user_id`, `user_name`, `user_screen_name`, `created_at` FROM {$wpdb->prefix}cbt_tweets WHERE `hash_id` IN (SELECT `id` FROM {$wpdb->prefix}cbt_hash WHERE `hash` = %s) ORDER BY `id` DESC", $hash));
			foreach($query as $obj) {
				$displayThisTweet = true;
            
				// antispam : uniquement mes abonnements
				if(is_array($Ids)) {
					if(!in_array($obj->user_id, $Ids)) {
						$displayThisTweet = false;
					}
				}
		
				// filtre par langue
				if ($lang != '' AND $lang != $obj->lang) {
					$displayThisTweet = false;
				}
		
				// account filter
				if ($from !== '') {
					$filterOK = 0;
					$displayThisTweet = false;
			
					// multiple account ?
					$account = explode(',', $from);		
					foreach($account as $f) {
						if ($fromTo != '' AND preg_match('/^@'.$f.'/', $obj->text)) {
							$filterOK++;
						}
	
						if ($fromFrom != '' AND $obj->user_screen_name == $f) {
							$filterOK++;
						}

						if ($fromMention != '' AND preg_match('/@'.$f.'/', $obj->text)) {
							$filterOK++;
						}
					}
			
					if($filterOK > 0) {
						$displayThisTweet = true;
					}
				}
            
				if($displayThisTweet == true) {
					$nbTweets++;
					$return .= '<blockquote class="twitter-tweet" lang="'.$obj->lang.'">
						<p>'.$obj->text.'</p>&mdash; '.$obj->user_name.' (@'.$obj->user_screen_name.') <a rel="nofollow" href="https://twitter.com/ressourceinfo/status/'.$obj->tweet_id.'">'.date('l j M @ G:i', strtotime($obj->created_at)).'</a>
					</blockquote>';
				}
			}
	
			$return = '<h2 class="comments-title">' . $nbTweets . ' tweets '.__('à propos de', 'commentByTweet').' #'.$hash.'</h2>
			<span class="tacTwitter"></span>
			<a href="https://twitter.com/intent/tweet?button_hashtag='.$hash.'&text=%20'.get_permalink().$mentionMe.'" class="twitter-hashtag-button" data-size="large" data-dnt="true">Tweet #'.$hash.'</a>'.$return;
	
			return $return;
		}

		/**
		* Get stats about API call.
		*/
		public function get_stats($url, $quart = false) {
			global $wpdb;
			$urls = array('/account/verify_credentials', '/application/rate_limit_status', '/friends/ids', '/search/tweets');
			if(!in_array($url, $urls))	{
				return;
			}
	
			$nb = $wpdb->get_row($wpdb->prepare("SELECT total, quart FROM {$wpdb->prefix}cbt_api WHERE url = %s", $url));
	
			if($quart == false) {
				return $nb->total;
			} else {
				return $nb->quart;
			}
		}

		/**
		* Update stats about API call.
		*/
		public function check_ping($url) {
			global $wpdb;
			$urls = array('/account/verify_credentials', '/application/rate_limit_status', '/friends/ids', '/search/tweets');
			if(!in_array($url, $urls))	{
				return false;
			}
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}cbt_api SET total = total+1, quart = quart+1 WHERE url = %s", $url));
	
			$nb = $wpdb->get_row($wpdb->prepare("SELECT limitation, quart FROM {$wpdb->prefix}cbt_api WHERE url = %s", $url));

			if($nb->limitation > $nb->quart) {
				return true;
			}
	
			return false;
		}
	}
}

if(class_exists('APICommentByTweet'))
{
	$APICommentByTweet = new APICommentByTweet();
}