<?php

if(!class_exists('commentByTweet_Admin'))
{
    class commentByTweet_Admin {
        var $hook = 'comment-by-tweet';
        var $longname = 'Comment by Tweet';
        var $shortname = 'Comment by Tweet';
        var $filename = 'comment-by-tweet/comment-by-tweet.php';
        var $homepage = 'http://amauri.champeaux.fr/comment-by-tweet/';
        
        function commentByTweet_Admin() {
            add_action('admin_menu', array(&$this, 'register_settings_page'));
            add_filter('plugin_action_links', array(&$this,'add_action_link'), 10, 2);
            add_action('admin_init', array(&$this,'register'));
        }
        
        /**
         * Add link to the admin panel.
         */
        function register_settings_page() {
            $hook_suffix = add_options_page($this->longname, $this->shortname, 'manage_options', $this->hook, array(&$this,'config_page'));
        }
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
         * Define options.
         */
        function register() {
            register_setting( 'commentByTweet', 'commentByTweet_CONSUMER_KEY' );
            register_setting( 'commentByTweet', 'commentByTweet_CONSUMER_SECRET' );
            register_setting( 'commentByTweet', 'commentByTweet_ACCESS_TOKEN' );
            register_setting( 'commentByTweet', 'commentByTweet_ACCESS_TOKEN_SECRET' );
        }
        
        /**
         * Settings page.
         */
        function config_page() {
            ?>
            <div class="wrap" style="float:left">
                <h1>Comment by Tweet</h1>
                <form method="post" action="options.php" autocomplete="off">
                <?php
                settings_fields( 'commentByTweet' );
                ?>
                <h2 style="margin-bottom:20px">OAuth</h2>
                <?php
                if (get_option('commentByTweet_CONSUMER_KEY') != FALSE &&
                    get_option('commentByTweet_CONSUMER_SECRET') != FALSE &&
                    get_option('commentByTweet_ACCESS_TOKEN') != FALSE &&
                    get_option('commentByTweet_ACCESS_TOKEN_SECRET') != FALSE) {
                    echo '<div class="commentByTweetDiv"><b style="color:darkgreen;font-size:16px">';
					_e('Pour des raisons de sécurité le formulaire reste vide mais les 4 champs sont bien renseignés.', 'commentByTweet' );
					echo '</b></div>';
                }
                ?>
                <div class="commentByTweetDiv">
                <table class="form-table">
                <tr valign="top">
                <th scope="row">CONSUMER_KEY</th>
                <td><input type="text" name="commentByTweet_CONSUMER_KEY" value="" /></td>
                </tr>
                <tr valign="top">
                <th scope="row">CONSUMER_SECRET</th>
                <td><input type="text" name="commentByTweet_CONSUMER_SECRET" value="" /></td>
                </tr>
                <tr valign="top">
                <th scope="row">ACCESS_TOKEN</th>
                <td><input type="text" name="commentByTweet_ACCESS_TOKEN" value="" /></td>
                </tr>
                <tr valign="top">
                <th scope="row">ACCESS_TOKEN_SECRET</th>
                <td><input type="text" name="commentByTweet_ACCESS_TOKEN_SECRET" value="" /></td>
                </tr>
                </table>
                </div>
                <?php submit_button(); ?>
                </form>
            </div>
            <div class="commentByTweetDivRight">
                <h4><?php _e('Procédure', 'commentByTweet' );?></h4>
                <ol>
                    <li><?php _e('Rendez vous sur <a href="https://apps.twitter.com" target="_blank">apps.twitter.com</a> et connectez vous avec votre compte Twitter', 'commentByTweet' );?></li>
                    <li><?php _e('Cliquez sur “Create New App” et remplissez les champs : Name, Description, Website', 'commentByTweet' );?></li>
                    <li><?php _e('Cliquez sur l’onglet “Keys and Access Token” et tout en bas “Create my access token”', 'commentByTweet' );?></li>
                    <li><?php _e('Cliquez en haut à droite sur “Test OAuth”', 'commentByTweet' );?></li>
                </ol>
                
                <em><?php _e('Si vous obtenez une erreur Invalid auth/bad request (got a 403, expected HTTP/1.1 20X or a redirect) c’est que vous n’êtes pas connecté avec le même utilisateur sur <a href="https://dev.twitter.com" target="_blank">dev.twitter.com</a> et <a href="https://apps.twitter.com" target="_blank">apps.twitter.com</a>.<br/>Déconnectez vous depuis les 2 sous domaines et reconnectez vous pour régler le problème.', 'commentByTweet' );?></em>
            </div>
            <div style="clear:both"></div>
            <style type="text/css">.commentByTweetDiv{background:#FFF;padding: 10px;border: 1px solid #eee;border-bottom: 2px solid #ddd;max-width: 500px;}.commentByTweetDivRight{float:left;padding:10px;margin-top:63px;width:250px;margin-left:30px;background:#fff;border:1px solid #eee;border-bottom:2px solid #ddd;}</style>
            <?php        
        }
    }
    
    $commentByTweet_admin = new commentByTweet_Admin();
}
