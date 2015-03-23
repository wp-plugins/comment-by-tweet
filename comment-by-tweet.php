<?php
/*
Plugin Name: Comment by Tweet
Plugin URI: http://amauri.champeaux.fr/comment-by-tweet/
Description: Système de commentaires basé sur les hashtags Twitter
Version: 0.1
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
            __( 'Hashtag pour le suivi des tweets', 'commentByTweet_textdomain' ),
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
    $hash = get_post_meta( $post->ID, 'commentByTweetHash', true );
    $link = get_post_meta( $post->ID, 'commentByTweetLink', true );
    if($link == 'on') {$isChecked = 'checked';}

    echo '<label for="commentByTweet_hash">';
    _e( 'Hashtag (sans #) qui servira pour le suivi des "tweetmentaires"', 'commentByTweet_textdomain' );
    echo '</label> ';
    echo '<input type="text" id="commentByTweet_hash" name="commentByTweet_hash" value="' . esc_attr( $hash ) . '" size="25" />';
    echo '<br/><br/>';
    echo '<label for="commentByTweet_link">';
    _e( 'Sélectionner des phrases à Tweeter ?', 'commentByTweet_textdomain' );
    echo '</label> ';
    echo '<input type="checkbox" id="commentByTweet_link" name="commentByTweet_link" ' . $isChecked . ' />';
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
    $link_data = sanitize_text_field( $_POST['commentByTweet_link'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'commentByTweetHash', $hash_data );
    update_post_meta( $post_id, 'commentByTweetLink', $link_data );
}
add_action( 'save_post', 'commentByTweet_save_meta_box_data' );

/**
 * Display the new comment template if hashtag is set and comments allowed.
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
function commentByTweetGet($hash) {
	global $post;
	
    if ($hash == '' ||
        get_option('commentByTweet_CONSUMER_KEY') == FALSE ||
        get_option('commentByTweet_CONSUMER_SECRET') == FALSE ||
        get_option('commentByTweet_ACCESS_TOKEN') == FALSE ||
        get_option('commentByTweet_ACCESS_TOKEN_SECRET') == FALSE) {
        return;
    }
    
    // is cached ?
    $cache = get_transient( 'tweets-'.$hash );
    if ($cache != false) {
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
    
    // get json
    $response = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/search/tweets.json'), array('q' => '%23'.$hash.'+exclude:retweets', 'count' => 100));
    
    // 200 = ok
    if ($response == 200) {
        
        // store result
        $data = json_decode($tmhOAuth->response['response'], true);
        
        // count number of tweets
        $nb = count($data['statuses']);
        
        $return = '<h2 class="comments-title">' . $nb . ' tweets à propos de #'.$hash.'</h2>
        <span class="tacTwitter"></span>
        <a href="https://twitter.com/intent/tweet?button_hashtag='.$hash.'&text=%20'.get_permalink().'" class="twitter-hashtag-button" data-size="large" data-dnt="true">Tweet #'.$hash.'</a>';
        
        // construct the blockquote for embed tweet
        foreach ($data['statuses'] as $tweet) {
            $return .= '<blockquote class="twitter-tweet" lang="'.$tweet['lang'].'">
                <p>'.$tweet['text'].'</p>&mdash; '.$tweet['user']['name'].' (@'.$tweet['user']['screen_name'].') <a href="https://twitter.com/ressourceinfo/status/'.$tweet['id'].'">'.date('l j M @ G:i', strtotime($tweet['created_at'])).'</a>
            </blockquote>';
        }
        
        // store to cache
        $delay = time() - get_the_time('U');
        if ($delay < 86400) {$cacheFor = 600;} // cache 10mn the first day
        elseif ($delay >= 86400 && $delay < (86400*3)) {$cacheFor = 3600;} // cache for 1
        elseif ($delay >= (86400*3) && $delay < (86400*7)) {$cacheFor = 7200;} // cache for 2 hours the first week
        else {$cacheFor = 86400;} // cache for 1 day
        set_transient('tweets-'.$hash, $return, $cacheFor);
        
        echo $return;
    }
}

/**
 * Add link for tweeting
 *
 * param @string $content The content
 */
function commentByTweetLink($content) {
    global $post;
    
    if(get_post_meta( $post->ID, 'commentByTweetLink', true ) != 'on') {
        return $content;
    }

    // append the hashtag and the url
    $append = '';
    if(get_post_meta( $post->ID, 'commentByTweetHash', true ) != '') {
        $append .= ' #'.get_post_meta( $post->ID, 'commentByTweetHash', true );
    }
    $append .= ' '.get_permalink($post->ID);
    
    // calculate the max chars (minus 3 for 'RT ')
    $min = 30;
    $max = 157 - strlen($append);
    
    // 1 link per <hx> tag
    $explodeHx = preg_split('/<h[1-6]>/m', $content);
    foreach($explodeHx as $pTags) {
        $found = false;
        
        // remove all tags except <p>
        $pTags = preg_replace('/^[^<]+<\/h[1-6]>/', '', $pTags);
        $pTags = preg_replace('/<\/?p>/m', '', $pTags);
        $pTags = preg_replace('/<[^>]+>[^<]+<\/[^>]+>/mU', '', $pTags);
        
        // search sentences
        preg_match_all('/[A-Z][^.!?<>\n\r]+[.!?]{1,3}[\s\r\n]+/m', $pTags, $phrase);
        foreach($phrase[0] as $p) {
            $p = trim($p);
            
            // if the size is correct, transform in link and stop
            if(strlen(html_entity_decode($p)) > $min && 
               strlen(html_entity_decode($p)) < $max && 
               $found === false && 
               preg_match('/'.preg_quote($p, '/').'/', $content)) {
                $found = true;
                $content = preg_replace('/'.preg_quote($p, '/').'/', '<a href="https://twitter.com/intent/tweet?text='.urlencode(html_entity_decode($p).$append).'" target="_blank" rel="nofollow" style="color:#4099ff"><span class="icon-twitter"></span> '.$p.'</a>', $content);
            }
        }
    }
    
    return $content;
}
add_filter('the_content', 'commentByTweetLink');

/**
 * CSS et Javascript
 */
function commentByTweetCSS() {
	wp_register_style('commentByTweet', plugins_url('comment-by-tweet/fontello/css/fontello.css'));
    wp_enqueue_style('commentByTweet');
}
add_action('wp_enqueue_scripts', 'commentByTweetCSS');
