<div class="wrap">
    <h1>Comment by Tweet : API Twitter</h1>
    <h2 style="margin-bottom:20px">OAuth infos</h2>
    <div class="commentByTweetDiv">
    	<span style="font-size:15px"><?php echo '<b>'.$userData['name'].'</b> @'.$userData['screen_name'].'</span><br/>
    	'.$userData['description'].'<br/><br/>
    	Friends : <b>'.$userData['friends_count'].' + 1 (you)</b> - In cache (for antispam) : ';
		$Ids_cached = get_transient( 'twitter_abonnements' );
		if($Ids_cached != false) {
      		echo '<b>Yes ('.count($Ids_cached).')</b>';
		} else {echo '<b>No</b>';}
		?>
    </div>
    <h2 style="margin-bottom:20px;margin-top:20px;">Stats</h2>
    <div class="commentByTweetDiv">
		<table class="form-table">
			<tr valign="top">
				<td><b>#hash</b></td>
				<td><b>Number</b></td>
				<td><b>Last id</b></td>
			</tr>
			<?php
			$query = $wpdb->get_results("SELECT {$wpdb->prefix}cbt_hash.hash, COUNT({$wpdb->prefix}cbt_tweets.id) as number, {$wpdb->prefix}cbt_hash.last_id FROM {$wpdb->prefix}cbt_hash JOIN {$wpdb->prefix}cbt_tweets ON {$wpdb->prefix}cbt_hash.id = {$wpdb->prefix}cbt_tweets.hash_id GROUP BY {$wpdb->prefix}cbt_hash.hash ORDER BY {$wpdb->prefix}cbt_hash.hash ASC");
			foreach($query as $obj) {
			    echo '<tr valign="top">
					<td>'.$obj->hash.'</td>
					<td>'.$obj->number.'</td>
					<td>'.$obj->last_id.'</td>
				</tr>';
			}
			?>
		</table>
	</div>
    <h2 style="margin-bottom:20px;margin-top:20px;">Logs</h2>
    <div class="commentByTweetDiv">
		<table class="form-table">
			<tr valign="top">
				<td><b>Type</b></td>
				<td><b>Limit</b></td>
				<td><b>Remaining</b></td>
				<td><b>Reset</b></td>
				<td><b>Last 15mns</b></td>
				<td><b>Total</b></td>
			</tr>
			<tr valign="top">
				<td>/account/verify_credentials</td>
				<td><?php echo $apiLimitData['resources']['account']['/account/verify_credentials']['limit']; ?></td>
				<td><?php echo $apiLimitData['resources']['account']['/account/verify_credentials']['remaining']; ?></td>
				<td><?php echo '~'.round(($apiLimitData['resources']['account']['/account/verify_credentials']['reset'] - time()) / 60).'mns'; ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/account/verify_credentials', true); ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/account/verify_credentials'); ?></td>
			</tr>
			<tr valign="top">
				<td>/application/rate_limit_status</td>
				<td><?php echo $apiLimitData['resources']['application']['/application/rate_limit_status']['limit']; ?></td>
				<td><?php echo $apiLimitData['resources']['application']['/application/rate_limit_status']['remaining']; ?></td>
				<td><?php echo '~'.round(($apiLimitData['resources']['application']['/application/rate_limit_status']['reset'] - time()) / 60).'mns'; ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/application/rate_limit_status', true); ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/application/rate_limit_status'); ?></td>
			</tr>
			<tr valign="top">
				<td>/friends/ids</td>
				<td><?php echo $apiLimitData['resources']['friends']['/friends/ids']['limit']; ?></td>
				<td><?php echo $apiLimitData['resources']['friends']['/friends/ids']['remaining']; ?></td>
				<td><?php echo '~'.round(($apiLimitData['resources']['friends']['/friends/ids']['reset'] - time()) / 60).'mns'; ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/friends/ids', true); ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/friends/ids'); ?></td>
			</tr>
			<tr valign="top">
				<td>/search/tweets</td>
				<td><?php echo $apiLimitData['resources']['search']['/search/tweets']['limit']; ?></td>
				<td><?php echo $apiLimitData['resources']['search']['/search/tweets']['remaining']; ?></td>
				<td><?php echo '~'.round(($apiLimitData['resources']['search']['/search/tweets']['reset'] - time()) / 60).'mns'; ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/search/tweets', true); ?></td>
				<td><?php echo $APICommentByTweet->get_stats('/search/tweets'); ?></td>
			</tr>
		</table>
    </div>
</div>
<div style="clear:both"></div>
<style type="text/css">.commentByTweetDiv{background:#FFF;padding: 10px;border: 1px solid #eee;border-bottom: 2px solid #ddd;max-width: 650px;}.commentByTweetDivRight{float:left;padding:10px;margin-top:63px;width:250px;margin-left:30px;background:#fff;border:1px solid #eee;border-bottom:2px solid #ddd;}</style>
