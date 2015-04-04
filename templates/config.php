<div class="wrap" style="float:left">
    <h1>Comment by Tweet</h1>
    <form method="post" action="options.php" autocomplete="off">
    <?php
    settings_fields( 'commentByTweet' );
    ?>
    <h2 style="margin-bottom:20px">Options</h2>
    <div class="commentByTweetDiv">
    <table class="form-table">
    <tr valign="top">
    <th scope="row"><?php _e('Affichage d\'un lien pour tweeter le texte sélectionné ?');?><br/><br/><img src="<?php echo plugins_url( 'comment-by-tweet/img/select.png' );?>" width="250" /></th>
    <td><input type="checkbox" name="commentByTweet_TWEETBUTTON" <?php if(get_option('commentByTweet_TWEETBUTTON') == 'on'){echo 'checked';}?> /></td>
    </tr>
    </table>
    </div>
        
    <h2 style="margin-bottom:20px;margin-top:20px;">OAuth</h2>
    <div class="commentByTweetDiv">
    <table class="form-table">
    <tr valign="top">
    <th scope="row">CONSUMER_KEY</th>
    <td><input type="password" name="commentByTweet_CONSUMER_KEY" value="<?php echo get_option('commentByTweet_CONSUMER_KEY');?>" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">CONSUMER_SECRET</th>
    <td><input type="password" name="commentByTweet_CONSUMER_SECRET" value="<?php echo get_option('commentByTweet_CONSUMER_SECRET');?>" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">ACCESS_TOKEN</th>
    <td><input type="password" name="commentByTweet_ACCESS_TOKEN" value="<?php echo get_option('commentByTweet_ACCESS_TOKEN');?>" /></td>
    </tr>
    <tr valign="top">
    <th scope="row">ACCESS_TOKEN_SECRET</th>
    <td><input type="password" name="commentByTweet_ACCESS_TOKEN_SECRET" value="<?php echo get_option('commentByTweet_ACCESS_TOKEN_SECRET');?>" /></td>
    </tr>
    </table>
    </div>
    <?php submit_button(); ?>
    </form>
</div>
<div class="commentByTweetDivRight">
    <h4><?php _e('Procédure OAuth', 'commentByTweet' );?></h4>
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