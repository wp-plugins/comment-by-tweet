=== Comment by Tweet ===
Contributors: Amauri CHAMPEAUX
Tags: comment, twitter
Requires at least: 2.8
Tested up to: 4.1
Stable tag: trunk

Système de commentaire basé sur Twitter et les #hashtags

== Description ==

Remplace le système de commentaire Wordpress de base par un écosystème basé sur Twitter et les #hashtags.

* Récuperation via l'api des tweets sur n'importe quel hashtag avec possibilité de filtrer par langue et compte :
  * En réponse à ...
  * De ...
  * Ayant mentionné ...
* Affichage des tweets-commentaires via les `embed tweet` (responsive et adapté à tous les sites)
* Ne nécessite pas de se connecter ou de donner son email pour participer à la discussion, être connecté sur twitter.com suffit
* Affichage des médias (images, vine, [twitter cards](http://amauri.champeaux.fr/meta-open-graph-twitter-card/), rich media, ...)
* Affichage des discussions
* Possibilité de retweeter, mettre en favori et suivre les différents intervenants
* Gestion des notifications natives de Twitter (réponse au @compte)

== Installation ==

1. Uploadez le dossier `comment-by-tweet` dans `/wp-content/plugins/`
2. Activez le plugin depuis le menu `Extensions` de Wordpress
3. Suivez les instructions pour l'identification oAuth sur la page des réglages

== Screenshots ==

1. Bloc commentaire
2. Configuration

== Changelog ==

= 0.2 =
* Filtrage par @compte
* Ajout des liens depuis l'éditeur tinymce
* Pas de cache pour l'auteur de l'article

= 0.1 =
* Version initiale
