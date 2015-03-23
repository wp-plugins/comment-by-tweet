jQuery(document).ready(function ($) {
    tinymce.create('tinymce.plugins.commentByTweet_plugin', {
        init : function (ed, url) {
            var content,
                selected,
                msgLength;
            
            ed.addCommand('commentByTweet_insert_shortcode', function () {
                selected = tinyMCE.activeEditor.selection.getContent();

                if (selected) {
					msgLength = (jQuery('#commentByTweet_hash').val() + ' ' + tinyMCE.activeEditor.selection.getContent({format : 'text'}) + ' ' + jQuery('#shortlink').val()).length;
					if (msgLength > 160) {
						alert('Le message est trop long de ' + (msgLength - 160) + ' caractères');
					} else {
						content = '<a href="[twitter_linkhash text=\'' + tinyMCE.activeEditor.selection.getContent({format : 'text'}).replace(/'/g, " ") + '\']" target="_blank" rel="nofollow" style="color:#4099ff">[twitter_icon] ' + selected + '</a>';
                        tinymce.execCommand('mceInsertContent', false, content);
					}
                } else {
                    alert('Veuillez sélectionner du texte');
                }
            });

            ed.addButton('commentByTweet_button', {title : 'Ajouter un lien vers Twitter', cmd : 'commentByTweet_insert_shortcode', icon: 'icon dashicons-twitter' });
            
            ed.onNodeChange.add(function (ed, cm, node) {
                cm.setDisabled('commentByTweet_button', tinyMCE.activeEditor.selection.getContent() === '');
            });
        }
    });

    tinymce.PluginManager.add('commentByTweet_button', tinymce.plugins.commentByTweet_plugin);
});
