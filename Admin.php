<?php

if(!class_exists('AdminCommentByTweet'))
{
    class AdminCommentByTweet {

        var $hook = 'comment-by-tweet',
            $longname = 'Comment by Tweet',
            $shortname = 'Comment by Tweet',
            $filename = 'comment-by-tweet/comment-by-tweet.php',
            $homepage = 'http://amauri.champeaux.fr/comment-by-tweet/';
        
        function __construct() {
            add_action('admin_menu', array($this, 'register_settings_page'));
            add_filter('plugin_action_links', array($this,'add_action_link'), 10, 2);
            add_action('admin_init', array($this,'register'));
        }
        
        /**
         * Add link to the admin panel.
         */
        function register_settings_page() {
            add_submenu_page('tools.php', $this->longname, $this->shortname.' API', 'manage_options', $this->hook.'-api', array($this, 'api_page'));
            add_options_page($this->longname, $this->shortname, 'manage_options', $this->hook, array($this, 'config_page'));
        }
        
        /**
         * Add setting link to plugin list.
         */
        function add_action_link( $links, $file ) {
            static $this_plugin;
            if( empty($this_plugin) ) $this_plugin = $this->filename;
            if ( $file == $this_plugin ) {
                $settings_link = '<a href="' . admin_url('options-general.php?page='.$this->hook) . '">' . __('Réglages') . '</a>';
                array_unshift( $links, $settings_link );
            }
            return $links;
        }
        
        /**
         * Register options.
         */
        function register() {
            register_setting( 'commentByTweet', 'commentByTweet_CONSUMER_KEY' );
            register_setting( 'commentByTweet', 'commentByTweet_CONSUMER_SECRET' );
            register_setting( 'commentByTweet', 'commentByTweet_ACCESS_TOKEN' );
            register_setting( 'commentByTweet', 'commentByTweet_ACCESS_TOKEN_SECRET' );
            register_setting( 'commentByTweet', 'commentByTweet_TWEETBUTTON' );
        }
        
        /**
         * Settings page.
         */
        function config_page() {
			include(sprintf("%s/templates/config.php", dirname(__FILE__)));    
        }
        
        /**
         * Info about API.
         */
		function api_page() {
         	
			global $wpdb;
			
			$APICommentByTweet = new APICommentByTweet();
			
			if (get_option('commentByTweet_CONSUMER_KEY') == FALSE ||
		      	get_option('commentByTweet_CONSUMER_SECRET') == FALSE ||
		      	get_option('commentByTweet_ACCESS_TOKEN') == FALSE ||
		      	get_option('commentByTweet_ACCESS_TOKEN_SECRET') == FALSE) {
		   			_e('Veuillez commencer par configurer l\'API OAuth via la page des réglages.');
			} else {
    
	   			// oauth
	   			include(dirname(__FILE__) . '/tmhOAuth/tmhOAuth.php');
	   			$tmhOAuth = new tmhOAuth(array(
					'consumer_key'     => get_option('commentByTweet_CONSUMER_KEY'),
					'consumer_secret'  => get_option('commentByTweet_CONSUMER_SECRET'),
					'user_token'       => get_option('commentByTweet_ACCESS_TOKEN'),
					'user_secret'      => get_option('commentByTweet_ACCESS_TOKEN_SECRET'),
				));
				   
				if($APICommentByTweet->check_ping('/account/verify_credentials')) {
		            $userId = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/account/verify_credentials.json'));
	            }
	            if ($userId == 200) {
	                $userData = json_decode($tmhOAuth->response['response'], true);
	            }
	            
	            if($APICommentByTweet->check_ping('/application/rate_limit_status')) {
		            $apiLimit = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/application/rate_limit_status.json'), array('resources' => 'account,application,friends,search'));
	            }
	            if ($apiLimit == 200) {
	                $apiLimitData = json_decode($tmhOAuth->response['response'], true);
	            }

				include(sprintf("%s/templates/info.php", dirname(__FILE__))); 
			}
		}
    }
}

if(class_exists('AdminCommentByTweet'))
{
    $AdminCommentByTweet = new AdminCommentByTweet();
}
