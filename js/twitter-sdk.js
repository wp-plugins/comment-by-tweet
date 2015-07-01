/***
 * Include the Twitter SDK
 * Support tarteaucitron.js
 */
if (typeof commentByTweetSDK !== 'function') {
    commentByTweetSDK = function () {
        if (typeof tarteaucitron !== 'undefined') {
            (tarteaucitron.job = tarteaucitron.job || []).push('twitter');
        } else {
            !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
        }
    };
}

if (window.addEventListener) {
    window.addEventListener("load", commentByTweetSDK, false);
} else {
    window.attachEvent("onload", commentByTweetSDK);
}

function commentByTweetMore(hash, start, id, url) {
	jQuery.ajax({
	    url : url + 'templates/load_more.php',
        type : 'GET',
        data: { hash: hash, start: start, id: id, time: Math.random()},
        dataType : 'html',
        success : function(code_html, statut){
			jQuery('#comment-by-tweet-more-' + start).html(code_html);
			if (typeof twttr !== 'undefined') {
				twttr.widgets.load();
			}
		}
    });
}