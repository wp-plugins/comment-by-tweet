<?php
/*
Plugin Name: Comment by Tweet
Plugin URI: http://amauri.champeaux.fr/comment-by-tweet/
Description: Système de commentaires basé sur les hashtags Twitter
Version: 0.3
Author: Amauri CHAMPEAUX
Author URI: http://amauri.champeaux.fr/a-propos/
*/

require_once('Admin.php');

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function commentByTweet_add_meta_box() {
    $screens = array( 'post', 'page' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'commentByTweet_hashtag',
            __( 'Hashtag pour le suivi des tweets', 'commentByTweet' ),
            'commentByTweet_meta_box_callback',
            $screen
        );
    }
}
add_action( 'add_meta_boxes', 'commentByTweet_add_meta_box' );

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function commentByTweet_meta_box_callback( $post ) {

    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'commentByTweet_meta_box', 'commentByTweet_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $hash           = get_post_meta( $post->ID, 'commentByTweetHash', true );
    $lang           = get_post_meta( $post->ID, 'commentByTweetLang', true );
    $from           = get_post_meta( $post->ID, 'commentByTweetFrom', true );
    $fromTo         = get_post_meta( $post->ID, 'commentByTweetFromTo', true );
    $fromFrom       = get_post_meta( $post->ID, 'commentByTweetFromFrom', true );
    $fromMention    = get_post_meta( $post->ID, 'commentByTweetFromMention', true );

    echo '<label for="commentByTweet_hash">';
    _e( '<b>Hashtag</b> (sans la #) :', 'commentByTweet' );
    echo '</label> ';
    echo '<input type="text" id="commentByTweet_hash" name="commentByTweet_hash" value="' . esc_attr( $hash ) . '" size="25" />';
    echo '<br/><br/>';

    echo '<label for="commentByTweet_from">';
    _e( '<b>Filtrer par compte</b> (séparés par des ,) :', 'commentByTweet' );
    echo '</label> ';
    echo '<input type="text" id="commentByTweet_from" name="commentByTweet_from" value="' . esc_attr( $from ) . '" size="25" />';
    echo '<br/>';
	
    echo '<input type="checkbox" id="commentByTweet_fromMention" name="commentByTweet_fromMention" ';if($fromMention == 'on'){echo 'checked';}echo ' />';
    echo '<label for="commentByTweet_fromMention" style="font-size:11px">';
    _e( 'Compte mentionné', 'commentByTweet' );
    echo '</label><br/>';
    
    echo '<input type="checkbox" id="commentByTweet_fromFrom" name="commentByTweet_fromFrom" ';if($fromFrom == 'on'){echo 'checked';}echo ' />';
    echo '<label for="commentByTweet_fromFrom" style="font-size:11px">';
    _e( 'De ce compte [selfish mode]', 'commentByTweet' );
    echo '</label><br/>';
    
    echo '<input type="checkbox" id="commentByTweet_fromTo" name="commentByTweet_fromTo" ';if($fromTo == 'on'){echo 'checked';}echo ' />';
    echo '<label for="commentByTweet_fromTo" style="font-size:11px">';
    _e( 'A ce compte [discussion]', 'commentByTweet' );
    echo '</label>';
    echo '<br/><br/>';
    
    echo '<label for="commentByTweet_lang">';
    _e( '<b>Langue</b> <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank">[2 lettres]</a> (optionnel) :', 'commentByTweet' );
    echo '</label><br/>';
    echo '<input type="text" maxlength="2" size="2" id="commentByTweet_lang" name="commentByTweet_lang" value="'.esc_attr($lang).'" />';
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function commentByTweet_save_meta_box_data( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['commentByTweet_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['commentByTweet_meta_box_nonce'], 'commentByTweet_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    
    // Make sure that it is set.
    if ( ! isset( $_POST['commentByTweet_hash'] ) ) {
        return;
    }

    // Sanitize user input.
    $hash_data = sanitize_text_field( $_POST['commentByTweet_hash'] );
    $lang_data = sanitize_text_field( $_POST['commentByTweet_lang'] );
    $from_data = sanitize_text_field( $_POST['commentByTweet_from'] );
    $fromTo_data = sanitize_text_field( $_POST['commentByTweet_fromTo'] );
    $fromFrom_data = sanitize_text_field( $_POST['commentByTweet_fromFrom'] );
    $fromMention_data = sanitize_text_field( $_POST['commentByTweet_fromMention'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'commentByTweetHash', $hash_data );
    update_post_meta( $post_id, 'commentByTweetLang', $lang_data );
    update_post_meta( $post_id, 'commentByTweetFrom', $from_data );
    update_post_meta( $post_id, 'commentByTweetFromTo', $fromTo_data );
    update_post_meta( $post_id, 'commentByTweetFromFrom', $fromFrom_data );
    update_post_meta( $post_id, 'commentByTweetFromMention', $fromMention_data );
}
add_action( 'save_post', 'commentByTweet_save_meta_box_data' );

/**
 * Display the new comment template if hashtag is set.
 */
function commentByTweet($template) {
    global $post;

    if ( !is_singular() || get_post_meta( $post->ID, 'commentByTweetHash', true ) == '') {
        return;
    }

    return dirname(__FILE__) . '/comments.php';
}
add_filter('comments_template', 'commentByTweet');

/**
 * Ask the Twitter API.
 *
 * param @string $hash The hash without #.
 */
function commentByTweetGet() {
	global $post;
	
	$hash = get_post_meta( $post->ID, 'commentByTweetHash', true );
	$lang = get_post_meta( $post->ID, 'commentByTweetLang', true );
	$from = get_post_meta( $post->ID, 'commentByTweetFrom', true );
	$fromTo = get_post_meta( $post->ID, 'commentByTweetFromTo', true );
	$fromFrom = get_post_meta( $post->ID, 'commentByTweetFromFrom', true );
	$fromMention = get_post_meta( $post->ID, 'commentByTweetFromMention', true );
	
    if ($hash == '' ||
        get_option('commentByTweet_CONSUMER_KEY') == FALSE ||
        get_option('commentByTweet_CONSUMER_SECRET') == FALSE ||
        get_option('commentByTweet_ACCESS_TOKEN') == FALSE ||
        get_option('commentByTweet_ACCESS_TOKEN_SECRET') == FALSE) {
        return;
    }
    
    // is cached ?
    $cache = get_transient( 'tweets_h-'.$hash );
    if ($cache != false && !current_user_can('edit_post', $post->ID) ) {
        echo $cache;
        return;
    }
    
    // oauth
    include(dirname(__FILE__) . '/tmhOAuth/tmhOAuth.php');
    $tmhOAuth = new tmhOAuth(array(
        'consumer_key'     => get_option('commentByTweet_CONSUMER_KEY'),
        'consumer_secret'  => get_option('commentByTweet_CONSUMER_SECRET'),
        'user_token'       => get_option('commentByTweet_ACCESS_TOKEN'),
        'user_secret'      => get_option('commentByTweet_ACCESS_TOKEN_SECRET'),
    ));
	
    
    // account filter
	if ($from !== '') {
		$mentionMe = ' cc';
		$filterFrom = '(';
		
        // multiple account ?
		$account = explode(',', $from);		
		foreach($account as $f) {
			if ($fromTo != '') {
				$filterFrom .= 'to:'.$f.' OR ';
			}

			if ($fromFrom != '') {
				$filterFrom .= 'from:'.$f.' OR ';
			}

			if ($fromMention != '') {
				$filterFrom .= '@'.$f.' OR ';
			}
			
			$mentionMe .= ' @'.$f;
		}
		
		$filterFrom = trim(trim($filterFrom, ' OR ').')', '()');
	}
	
    // get json
    $response = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/search/tweets.json'), array('q' => '%23'.$hash.' +exclude:retweets '.$filterFrom, 'lang' => strtolower($lang), 'count' => 100));
    
    // 200 = ok
    if ($response == 200) {
        
        // store result
        $data = json_decode($tmhOAuth->response['response'], true);

        // count number of tweets
        $nb = count($data['statuses']);
        
        // output html
        $return = '<h2 class="comments-title">' . $nb . ' tweets '.__('à propos de', 'commentByTweet').' #'.$hash.'</h2>
        <span class="tacTwitter"></span>
        <a href="https://twitter.com/intent/tweet?button_hashtag='.$hash.'&text=%20'.get_permalink().$mentionMe.'" class="twitter-hashtag-button" data-size="large" data-dnt="true">Tweet #'.$hash.'</a>';
        
        // construct the blockquote for embed tweet
        foreach ($data['statuses'] as $tweet) {
            $return .= '<blockquote class="twitter-tweet" lang="'.$tweet['lang'].'">
                <p>'.$tweet['text'].'</p>&mdash; '.$tweet['user']['name'].' (@'.$tweet['user']['screen_name'].') <a rel="nofollow" href="https://twitter.com/ressourceinfo/status/'.$tweet['id'].'">'.date('l j M @ G:i', strtotime($tweet['created_at'])).'</a>
            </blockquote>';
        }
        
		$return .= '<input type="hidden" id="commentByTweetHash" value="'.$hash.'" />
		<div id="commentByTweetMe" style="display:none;position:absolute;z-index:2147483647"></div>';
		
        // store to cache
        $delay = time() - get_the_time('U');
        if ($delay < 86400) {$cacheFor = 600;} // cache 10mn the first day
        elseif ($delay >= 86400 && $delay < (86400*3)) {$cacheFor = 3600;} // cache for 1
        elseif ($delay >= (86400*3) && $delay < (86400*7)) {$cacheFor = 7200;} // cache for 2 hours the first week
        else {$cacheFor = 86400;} // cache for 1 day
        set_transient('tweets_h-'.$hash, $return, $cacheFor);
        
        echo $return;
    }
}

/**
 * Icon shortcodes (by fontello).
 */
function commentByTweetIcon() {
    return '<span class="icon_54047278-twitter"></span>';
}
add_shortcode( 'twitter_icon', 'commentByTweetIcon' );

/**
 * Tweet link shortcode.
 *
 * param @array $atts Text
 */
function commentByTweetHash($atts) {
	global $post;
	
    if(get_post_meta( $post->ID, 'commentByTweetHash', true ) != '') {
        $hash = ' #'.get_post_meta( $post->ID, 'commentByTweetHash', true );
    }
	
    return 'https://twitter.com/intent/tweet?text=' . urlencode(preg_replace("/_apos_/", "'", $atts['text']) . $hash . ' ' . get_permalink($post->ID));
}
add_shortcode( 'twitter_linkhash', 'commentByTweetHash' );

/**
 * Add a button to the tinymce editor.
 */
function commentByTweet_shortcode_button_init() {
    
    //Abort early if the user will never see TinyMCE
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
        return;
    }

    // callback to regiser our tinymce plugin   
    add_filter("mce_external_plugins", "commentByTweet_register_tinymce_plugin"); 

    // callback to add our button to the TinyMCE toolbar
    add_filter('mce_buttons', 'commentByTweet_add_tinymce_button');
}
function commentByTweet_register_tinymce_plugin($plugin_array) {
    $plugin_array['commentByTweet_button'] = plugins_url('comment-by-tweet/js/tinymce-plugin.js');
    return $plugin_array;
}
function commentByTweet_add_tinymce_button($buttons) {
    $buttons[] = "commentByTweet_button";
    return $buttons;
}
add_action('init', 'commentByTweet_shortcode_button_init');

/**
 * CSS et Javascript
 */
function commentByTweetCSS() {
	wp_enqueue_style('commentByTweet', plugins_url('comment-by-tweet/fontello/css/fontello.css'));
    wp_enqueue_script('commentByTweetSDK', plugins_url('comment-by-tweet/js/twitter-sdk.js'));
    wp_enqueue_script('commentByTweetHighlight', plugins_url('comment-by-tweet/js/tweet-on-highlight.js'), array( 'jquery' ));
}
add_action('wp_enqueue_scripts', 'commentByTweetCSS');

function commentByTweetAdminCSS() {
	wp_enqueue_style( 'commentByTweet', plugins_url( 'comment-by-tweet/css/custom-icon.css' ) );
}
add_action( 'admin_enqueue_scripts', 'commentByTweetAdminCSS' );
