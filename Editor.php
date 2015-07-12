<?php

if(!class_exists('EditorCommentByTweet'))
{
	class EditorCommentByTweet
	{
		public function __construct() {
			add_action( 'add_meta_boxes', array($this, 'add_meta_box'));
			add_action( 'save_post', array($this, 'save_meta_box_data') );
			add_action('init', array($this, 'shortcode_button_init'));
		}
		
		/**
		* Adds a box to the main column on the Post and Page edit screens.
		*/
		public function add_meta_box() {
			$screens = array( 'post', 'page' );
			foreach ( $screens as $screen ) {
				add_meta_box(
					'commentByTweet_hashtag',
					__( 'Hashtag pour le suivi des tweets', 'commentByTweet' ),
					array($this, 'meta_box_callback'),
					$screen
				);
			}
		}
		
		/**
		* Prints the box content.
		* 
		* @param WP_Post $post The object for the current post/page.
		*/
		public function meta_box_callback( $post ) {

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
			$spam           = get_post_meta( $post->ID, 'commentByTweetSpam', true );
			$sendpost       = get_post_meta( $post->ID, 'commentByTweetPost', true );

			_e( '<b>Promouvoir l\'article</b> :', 'commentByTweet' );
			echo '<br/><input type="checkbox" id="commentByTweet_post" name="commentByTweet_post" ';if($sendpost == 'on'){echo 'checked';}echo ' />';
			echo '<label for="commentByTweet_post">';
			_e( 'Poster un tweet à la publication ?', 'commentByTweet' );
			echo '</label>';
			echo '<br/><br/>';
			
			echo '<label for="commentByTweet_hash">';
			_e( '<b>Hashtag</b> (sans la #) :', 'commentByTweet' );
			echo '</label> ';
			echo '<input type="text" id="commentByTweet_hash" name="commentByTweet_hash" value="' . esc_attr( $hash ) . '" size="25" />';
			echo '<br/><br/>';
    
			_e( '<b>Filtre antispam</b> :', 'commentByTweet' );
			echo '<br/><input type="checkbox" id="commentByTweet_spam" name="commentByTweet_spam" ';if($spam == 'on'){echo 'checked';}echo ' />';
			echo '<label for="commentByTweet_spam">';
			_e( 'Uniquement de mes abonnements', 'commentByTweet' );
			echo '</label><br/><br/>';

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
		public function save_meta_box_data( $post_id ) {
	
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
			$hash_data = '';
			if (isset($_POST['commentByTweet_hash'])) {
				$hash_data = sanitize_text_field( $_POST['commentByTweet_hash'] );
			}
			$lang_data = '';
			if (isset($_POST['commentByTweet_lang'])) {
				$lang_data = sanitize_text_field( $_POST['commentByTweet_lang'] );
			}
			$from_data = '';
			if (isset($_POST['commentByTweet_from'])) {
				$from_data = sanitize_text_field( $_POST['commentByTweet_from'] );
			}
			$fromTo_data = '';
			if (isset($_POST['commentByTweet_fromTo'])) {
				$fromTo_data = sanitize_text_field( $_POST['commentByTweet_fromTo'] );
			}
			$fromFrom_data = '';
			if (isset($_POST['commentByTweet_fromFrom'])) {
				$fromFrom_data = sanitize_text_field( $_POST['commentByTweet_fromFrom'] );
			}
			$fromMention_data = '';
			if (isset($_POST['commentByTweet_fromMention'])) {
				$fromMention_data = sanitize_text_field( $_POST['commentByTweet_fromMention'] );
			}
			$spam_data = '';
			if (isset($_POST['commentByTweet_spam'])) {
				$spam_data = sanitize_text_field( $_POST['commentByTweet_spam'] );
			}
			$post_data = '';
			if (isset($_POST['commentByTweet_post'])) {
				$post_data = sanitize_text_field( $_POST['commentByTweet_post'] );
			}

			// Update the meta field in the database.
			update_post_meta( $post_id, 'commentByTweetHash', $hash_data );
			update_post_meta( $post_id, 'commentByTweetLang', $lang_data );
			update_post_meta( $post_id, 'commentByTweetFrom', $from_data );
			update_post_meta( $post_id, 'commentByTweetFromTo', $fromTo_data );
			update_post_meta( $post_id, 'commentByTweetFromFrom', $fromFrom_data );
			update_post_meta( $post_id, 'commentByTweetFromMention', $fromMention_data );
			update_post_meta( $post_id, 'commentByTweetSpam', $spam_data );
			update_post_meta( $post_id, 'commentByTweetPost', $post_data );
		}
		
		/**
		* Add a button to the tinymce editor.
		*/
		public function shortcode_button_init() {
    
			//Abort early if the user will never see TinyMCE
			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
				return;
			}

			// callback to regiser our tinymce plugin   
			add_filter("mce_external_plugins", array($this, 'register_tinymce_plugin')); 

			// callback to add our button to the TinyMCE toolbar
			add_filter('mce_buttons', array($this, 'add_tinymce_button'));
		}

		public function register_tinymce_plugin($plugin_array) {
			$plugin_array['commentByTweet_button'] = plugins_url('comment-by-tweet/js/tinymce-plugin.js');
			return $plugin_array;
		}

		public function add_tinymce_button($buttons) {
			$buttons[] = "commentByTweet_button";
			return $buttons;
		}
	}
}

if(class_exists('EditorCommentByTweet'))
{
	$EditorCommentByTweet = new EditorCommentByTweet();
}