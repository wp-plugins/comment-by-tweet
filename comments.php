<?php
$hash = get_post_meta( $post->ID, 'commentByTweetHash', true );
?>

<script type="text/javascript">
/***
 * Include the Twitter SDK
 * Support tarteaucitron.js
 */
if (commentByTweetSDK === undefined) {
    function commentByTweetSDK() {
        if (tarteaucitron !== undefined) {
            (tarteaucitron.job = tarteaucitron.job || []).push('twitter');
        } else {
            !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
        }
    }
}

if (window.addEventListener) {
    window.addEventListener("load", commentByTweetSDK, false);
} else {
    window.attachEvent("onload", commentByTweetSDK);
}
</script>

<div id="comments" class="comments-area">
    <!-- commment-by-tweet -->
    <?php echo commentByTweetGet($hash); ?>
</div>
