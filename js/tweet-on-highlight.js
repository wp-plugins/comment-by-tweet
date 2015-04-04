/**
 * Show a tweet button if highlighted text is <140 car.
 */

jQuery(document).mouseup(function(event){
    var text;
	
	if (document.getElementById('commentByTweetMe') === null || document.getElementById('commentByTweetHash') === null) {
        return;
	}
	
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    if (text.length < 120 && text.length > 1 && event.target.id !== 'commentByTweetAutoA') {
        if (document.getElementById('commentByTweetHash') !== null) {
		    text += ' #' + document.getElementById('commentByTweetHash').value;
		}
		
		text += ' ' + document.URL;
		
		document.getElementById('commentByTweetMe').innerHTML = '<a target="_blank" style="color:#4099FF;" onclick="document.getElementById(\'commentByTweetMe\').style.display = \'none\';" href="https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '"><span id="commentByTweetAutoA" class="icon_54047278-twitter" style="padding:6px 7px 6px 5px;background:#fff;border-radius: 45px;border: 2px solid;"></span></a>';
		document.getElementById('commentByTweetMe').style.display = 'block';
		document.getElementById('commentByTweetMe').style.top = (event.pageY) + 'px';
		document.getElementById('commentByTweetMe').style.left = (event.pageX + 10) + 'px';
	} else if (event.target.id !== 'commentByTweetAutoA') {
		document.getElementById('commentByTweetMe').style.display = 'none';
	}
});