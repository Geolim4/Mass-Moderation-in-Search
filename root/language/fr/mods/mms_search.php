<?php
/**
*
* mms_search.php [Français]
* @package search Mass Moderation In Search
^>@version $Id: class_mms.php v1.1.0 22h14 06/07/2013 Geolim4 Exp $
* @copyright (c) 2012 Geolim4.com  http://Geolim4.com
* @package language
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
// Some characters you may want to copy&paste:
// ’ « » “ ” …
// Use: <strong style="color:green">Texte</strong>',
// For add Color
//

$lang = array_merge($lang, array(
	'MMS_ABORT'				=> 'Avorter le processus ?',
	'MMS_ABORTED'			=> 'Le processus a été avorté, vous allez être redirigé sur l’index.',
	'MMS_ACCESS_DENIED'		=> 'Accès refusé',
	'MMS_ACTION'			=> 'Action de modération',
	'MMS_ACTION_EXPLAIN'	=> 'Choisir l’option cible de modération.
								<br />Merci de noter que ce paramètre dépends de vos permissions dans les forums sélectionnés ci-dessus.',
	'MMS_ADDON_DISABLED'	=> 'Addon indisponible actuellement!',
	'MMS_BAD_POST_MODE'		=> 'Mode de message incorrect!',
	'MMS_BAD_TOPIC_MODE'	=> 'Mode de sujet incorrect!',
	'MMS_BAD_DATA_FORMAT'	=> 'Mauvais format de données reçu!',
	'MMS_CHAR_BOTTOM'		=> '▼',
	'MMS_CHAR_TOP'			=> '▲',
									//Aux traducteurs: Ne pas remplacer/traduire *m*,*s*,*q*,*p*,*t*,*f*,*d* !!!!Considérez ces chaînes de caractères comme la chaîne %s .
	'MMS_CHRONO_POSTS'		=> 'Opération terminée en *m* minute(s) et *s* seconds, *q* requêtes SQL pour *p* messages impactés: *f* message(s) échoué(s) and *d* message(s) traîté(s)',
	'MMS_CHRONO_TOPICS'		=> 'Opération terminée en *m* minute(s) et *s* seconds, *q* requêtes SQL pour *t* sujets impactés: *f* sujet(s) échoué(s) and *d* sujet(s) traîté(s)',
	'MMS_CONNECTION_FAIL'	=> 'Quelque chose s’est mal passé lors de l’envoi de données vers le serveur! Ré-essayer?',
	'MMS_DATA_ABORTED'		=> 'Avorté...',
	'MMS_DATA_SENDING'		=> 'Envois des données...',
	'MMS_DATA_INTERRUPTED'	=> 'Interrompu...',
	'MMS_DATA_TERMINATED'	=> 'Terminé',
	'MMS_EXIT_ALERT'		=> 'Vous êtes sur le point d’interrompre ce processus, êtes-vous vraiment sûr de continuer?',
	'MMS_FAIL'				=> array(
			'post'					=> 'Messages échoués',
			'topic'					=> 'Sujets échoués',
	),
	'MMS_FAILED'			=> array(
			'post'					=> 'Aucun message échoué actuellement',
			'topic'					=> 'Aucun sujet échoué actuellement',
	),
	'MMS_FINAL_RESYNC'		=> array(
			'F'				=> 'Forums resynchronisés',
			'T'				=> 'Sujets resynchronisés',
			'S'					=> 'Statistiques resynchronisés',
			'U'					=> 'Utilisateurs resynchronisés',
	),
	'MMS_FINAL_RESYNC_NEXT'		=> array(
			'F'				=> 'Resynchronisation des forums',
			'T'				=> 'Resynchronisation des sujets',
			'S'					=> 'Resynchronisation des statistiques',
			'U'					=> 'Resynchronisation des utilisateurs',
	),
	'MMS_FIND_TOPIC'				=> 'Rechercher un sujet',
	'MMS_GLOBAL_ERROR'				=> 'Impossible de gérer les annonces générales avec l’outil de modération de masse.',
	'MMS_GRAB_EXP'					=> 'Double-cliquez pour tout sélectionner',
	'MMS_HTML_DUMP'					=> '<strong>Dump HTML</strong> (incluant les en-têtes HTTP)',
	'MMS_IPS_GRABBED'				=> 'IP(s) récupérée(s)',
	'MMS_FORUM_ID'					=> 'Forum de destination',
	'MMS_IGNORE'					=> 'Ignorer',
	'MMS_IGNORED'					=> 'Ignoré par l’utilisateur',
	'MMS_ITEM_MOVED'				=> 'Le sujet de destination ne peux pas être un sujet traçeur!',
	'MMS_LEFT'				=> array(
			'post'					=> 'Messages restants',
			'topic'					=> 'Sujets restants',
	),
	'MMS_LEFTNO'			=> array(
			'post'					=> 'Aucun message en attente actuellement',
			'topic'					=> 'Aucun sujet en attente actuellement'
	),
	'MMS_LOAD'						=> 'Charger l’outil de modération de masse',
	'MMS_LOADAVG'					=> 'Charge système',
	'MMS_LOADAVG_EXP'				=> 'Comme un serveur à plus de 10% de charge moyenne ne répondra jamais à temps, la barre de progression réelle est donc multipliée par 10.',

	//Topics Logs
	'MMS_LOG_TOPIC_LOCK'			=> '<strong>Verrouillage d’un sujet</strong><em>(avec l’outil de modération de masse)</em><br />» %s',
	'MMS_LOG_TOPIC_UNLOCK'			=> '<strong>Déverrouillage d’un sujet</strong><em>(avec l’outil de modération de masse)</em><br />» %s',
	'MMS_LOG_TOPIC_FORK'			=> '<strong>Copie d’un sujet</strong><em>(avec l’outil de modération de masse)</em><br />» depuis %s',
	'MMS_LOG_TOPIC_DELETE'			=> '<strong>Suppression du sujet « %1$s » écrit par</strong><em>(avec l’outil de modération de masse)</em><br />» %2$s',
	'MMS_LOG_TOPIC_MOVE'			=> '<strong>Déplacement d’un sujet</strong><br />» depuis %1$s vers %2$s <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_TOPIC_RESYNC'			=> '<strong>Resynchronisation des compteurs de sujets</strong><br />» %s <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_TOPIC_MERGE'			=> '<strong>Fusion du sujet « %1$s » écrit par</strong> %2$s <br />» <strong>vers le sujet</strong> « %3$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_TOPIC_CGHICON'			=> '<strong>Modification de l’icône de sujet</strong> <em>(avec l’outil de modération de masse)</em><br />» %s',

	//Posts Logs
	'MMS_LOG_POST_LOCK'				=> '<strong>Verrouillage d’un message</strong><em>(avec l’outil de modération de masse)</em><br />» %s',
	'MMS_LOG_POST_UNLOCK'			=> '<strong>Déverrouillage d’un message</strong><em>(avec l’outil de modération de masse)</em><br />» %s',
	'MMS_LOG_POST_DELETE'			=> '<strong>Suppression du message « %1$s » écrit par</strong><br />» %2$s <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_MOVE'				=> '<strong>Déplacement d’un message écrit par</strong> %1$s <br />» depuis %2$s vers %3$s <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_CHGPOSTER'		=> '<strong>Modification de l’auteur du sujet « %1$s »</strong><em>(avec l’outil de modération de masse)</em><br />» de %2$s en %3$s',

	//Posts Options Logs
	'MMS_LOG_POST_OPTIONS_ENABLE_SIG'	=> '<strong>Profil du posteur caché dans le message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_SIG'	=> '<strong>Profil du posteur affiché dans le message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_ENABLE_BBCODES'	=> '<strong>Activation des BBCODES dans le message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_BBCODES'	=> '<strong>Désactivation des BBCODES dans le message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_ATTACHMENT'	=> '<strong>Suppression de fichiers-joints du message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	//Posts Options Logs (Addons)
	'MMS_LOG_POST_OPTIONS_ENABLE_HPIV'	=> '<strong>Profil du posteur caché écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_HPIV'	=> '<strong>Profil du posteur affiché écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_PPR'	=> '<strong>Suppression d’éventuel historique d’édition de message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_MM'	=> '<strong>Suppression du message de modération du message écrit par</strong> %1$s <br />» <strong>dans le sujet</strong> « %2$s » <em>(avec l’outil de modération de masse)</em>',

	'MMS_MAIN'						=> 'Menu principal',
	'MMS_MASS_POST_TOOL'			=> array(
			'lock'						=> 'Verrouillage de message en masse',
			'unlock'					=> 'Déverrouillage de message en masse',
			'delete'					=> 'Suppression de message en masse',
			'chgposter'					=> 'Changement de posteur en masse',
			'move'						=> 'Déplacement de message en masse',
			'options'					=> 'Changement des options de messages en masse',
			'grabip'					=> 'Récupération des IPs',
	),
	'MMS_MASS_TOPIC_TOOL'			=> array(
			'lock'						=> 'Verrouillage de sujet en masse',
			'merge'						=> 'Fusion de sujet en masse',
			'unlock'					=> 'Déverrouillage de sujet en masse',
			'delete'					=> 'Suppression de sujet en masse',
			'fork'						=> 'Copie de sujet en masse',
			'move'						=> 'Déplacement de sujet en masse',
			'resync'					=> 'Resynchronisation de sujet en masse',
			'chgicon'					=> 'Changement d’icône en masse',
			'attr'						=> 'Changement d’attribut en masse',
	),
	'MMS_MOD_DISABLED'				=> 'L’outil de modération de masse est désactivé.',
	'MMS_MORE_INFORMATIONS'			=> 'Plus d’informations',
	'MMS_NO_DIRECT_ACCESS'			=> 'L’outil de modération de masse ne peut pas être utilisé en accès direct, vous devez passer par la %1$srecherche avancée%2$s pour utiliser cet outil!',
	'MMS_NO_FPERMISSION'			=> 'Permission de lecture refusée',
	'MMS_NO_MPERMISSION'			=> 'Permission de modération refusée',
	'MMS_NO_POST'					=> 'Aucun message sélectionné!',
	'MMS_NO_TOPIC'					=> 'Aucun sujet sélectionné!',
	'MMS_NOT_AVAILABLE'				=> 'Non disponible',
	'MMS_OK'						=> 'Ok',
	'MMS_PACKET_SIZE'				=> 'Le paquet à dépassé la taille autorisé !
										<br />Par mesure de sécurité vous allez être redirigé sur l’index.',
	'MMS_PAGINATION'				=> 'Pagination',
	'MMS_PAGINATION_EXP'			=> 'Ce paramètre écrase la configuration de pagination par défault',
	'MMS_PAGINATION_POSTS_TOPICS'	=> 'messages/sujets par page',
	'MMS_PASSWORD'					=> 'Confirmation de mot de passe',
	'MMS_PASSWORD_BAD'				=> 'Mauvais mot de passe!',
	'MMS_PASSWORD_CONFIRM'			=> 'Merci de confirmer votre mot de passe',
	'MMS_POST'						=> 'Message',
	'MMS_POST_ALREADY_LOCKED'		=> 'Le message a déjà été verrouillé.',
	'MMS_POST_ALREADY_UNLOCKED'		=> 'Le message a déjà été déverrouillé.',
	'MMS_POST_DELETED'				=> 'Le message a probablement déjà été supprimé.',
	'MMS_POSTS_OPTIONS'			=> array(
		'DISABLE_SIG'				=> 'Détacher la signature',
		'DISABLE_SMILIES'			=> 'Désactiver les smileys',
		'DISABLE_LINKS'				=> 'Désactiver les liens automatiques',
		'DISABLE_BBCODES'			=> 'Désactiver les BBcodes',
		'ENABLE_SIG'				=> 'Attacher la signature',
		'ENABLE_SMILIES'			=> 'Activer les smileys',
		'ENABLE_LINKS'				=> 'Activer les liens automatiques',
		'ENABLE_BBCODES'			=> 'Activer les BBCodes',
		'REMOVE_ATTACHMENT'			=> '╚► Supprimer le(s) fichier(s)-joint(s)',
		//Addons
		'DISABLE_HPIV'				=> 'Afficher le profil du posteur',
		'ENABLE_HPIV'				=> 'Cacher le profil du posteur',
		'REMOVE_PPR'				=> '╚► Supprimer l’historique éventuel d’édition des messages',
		'REMOVE_MM'					=> '╚► Supprimer les messages de modération',
	),
	'MMS_POSTS_OPTIONS_ERROR'		=> array(
		'REMOVE_ATTACHMENT'			=> 'Aucun fichier-joint supprimé',
	),
	'MMS_POSTS_OPTIONS_SUCCESS'		=> array(
		'DISABLE_SIG'				=> 'Signature détachée',
		'DISABLE_SMILIES'			=> 'Smileys désactivés',
		'DISABLE_LINKS'				=> 'Liens désactivés',
		'DISABLE_BBCODES'			=> 'BBcodes désactivés',
		'ENABLE_SIG'				=> 'Signature attachée',
		'ENABLE_SMILIES'			=> 'Smileys activés',
		'ENABLE_LINKS'				=> 'Liens activés',
		'ENABLE_BBCODES'			=> 'BBcodes activés',
		'REMOVE_ATTACHMENT'			=> 'Fichier(s)-joint(s) supprimé(s)',
		//Addons
		'DISABLE_HPIV'				=> 'Profil du posteur affiché',
		'ENABLE_HPIV'				=> 'Profil du posteur caché',
		'REMOVE_PPR'				=> 'Historique d’édition éventuel supprimé',
		'REMOVE_MM'					=> 'Message de modération supprimé',
	),
	//For key marked "!" try to no reach more than 255 chars per key. Otherwise the text will be hard-broken in the post-edit-log reason
	'MMS_POSTS_ICON_FAIL'			=> 'L’icône choisie n’existe pas!',
	'MMS_POSTS_ICON_REASON'			=> 'Changement d’icône de sujet (avec l’outil de modération de masse)',//!
	'MMS_POSTS_OPTIONS_REASON'		=> array(
		'DISABLE_SIG'				=> 'Détachement de signature (avec l’outil de modération de masse)',//!
		'DISABLE_SMILIES'			=> 'Désactivation des smileys (avec l’outil de modération de masse)',//!
		'DISABLE_LINKS'				=> 'Désactivation des liens (avec l’outil de modération de masse)',//!
		'DISABLE_BBCODES'			=> 'Désactivation des BBcodes (avec l’outil de modération de masse)',//!
		'ENABLE_SIG'				=> 'Activation de la signature (avec l’outil de modération de masse)',//!
		'ENABLE_SMILIES'			=> 'Activation des smileys (avec l’outil de modération de masse)',//!
		'ENABLE_LINKS'				=> 'Activation des liens (avec l’outil de modération de masse)',//!
		'ENABLE_BBCODES'			=> 'Activation des bbcodes (avec l’outil de modération de masse)',//!
		'REMOVE_ATTACHMENT'			=> 'Supression des fichiers-joints (avec l’outil de modération de masse)',//!
		//Addons
		'DISABLE_HPIV'				=> 'Affichage du profil du posteur (avec l’outil de modération de masse)',//!
		'ENABLE_HPIV'				=> 'Dissimulation du profil du posteur (avec l’outil de modération de masse)',//!
		'REMOVE_PPR'				=> 'Suppression de l’éventuel historique d’édition (avec l’outil de modération de masse)',//!
		'REMOVE_MM'					=> 'Suppression du message de modération (avec l’outil de modération de masse)',//!
	),
	'MMS_POSTS_OPTIONS_EXP'			=> 'Options de message',
	'MMS_PRELOADING'				=> 'Chargement du document...
										<br />Merci de patienter.',
	'MMS_PRELOADING_EXP'			=> 'Si votre navigateur semble ne pas répondre c’est un comportement normal si vous avez sélectionné beaucoup de critères de recherche!',
	'MMS_PREV'						=> 'Aperçu',
	'MMS_PREVIEW'					=> 'Aperçu rapide du message',
	'MMS_PRIV'						=> '(Privé)',
	'MMS_PRIVATE'					=> 'Vous n’êtes pas autorisé à lire ce message',
	'MMS_REASON'					=> 'Raison',
	'MMS_REDIRECT'					=> 'Vous allez être redirigé sur l’index.',
	'MMS_SAME_FORUM'				=> 'Le forum d’origine est le même que le forum de destination.',
	'MMS_SAME_TOPIC'				=> 'Le sujet d’origine est le même que le sujet de destination.',
	'MMS_SAME_USERNAME'				=> 'Le nom d’utilisateur actuel est le même que le nouveau nom d’utilisateur.',
	'MMS_SEARCH_WARN'				=> 'Vous êtes sur le point d’afficher plus de 1000 résultats simultanément, celà peux augmenter considérablement le temps de chargement de votre serveur et de votre navigateur. Êtes-vous sûr de vouloir continuer ?',
	'MMS_SELECTED'					=> 'sélectionné',
	'MMS_SELECTEDS'					=> 'sélectionnés',
	'MMS_SELECT_FORUM'				=> 'Sélectionner par forum',
	'MMS_SELECT_MODE'				=> 'Vous êtes en mode de sélection de sujet, l’accès à certaines fonctionalités comme la modération, l’administration et votre panneau de l’utilisateur seront donc indisponible actuellement.',
	'MMS_SELECT_TOPIC'				=> 'Sélectionner un sujet',
	'MMS_SELECT_THIS_TOPIC'			=> '<strong style="color: green;">Sélectionner ce sujet</strong>',
	'MMS_SELECT_TYPE'				=> 'Type de sélecteur',
	'MMS_SELECT_CHECKBOX'			=> 'Case à cocher',
	'MMS_SELECT_CHECKTOPIC'			=> 'Message/Sujet à cocher',
	'MMS_SELECT_RECTANGLE'			=> 'Rectangle de sélection',
	'MMS_SELECT_USER'				=> 'Sélectionner par utilisateur',
	'MMS_SHOW_POST_REASON'			=> 'Afficher la raison d’édition du message?',
	'MMS_SQL_QUERIES'				=> '(*s* requêtes SQL)',
																//To translators: Please keep this line as a single line!!
	'MMS_SQL_WARN'					=> 'Attention, la copie de sujets populaires peux entraîner une quantité de requête SQL considérable souhaitez-vous activer la temporisation des requêtes?<br />Ce procédé augmentera le temps de traîtement nécéssaire mais réduira considérablement le <a href="http://dev.mysql.com/doc/refman/5.0/fr/gone-away.html" onclick="window.open(this.href); return false;">risque de timeout</a> de la base de données.',
	'MMS_STATUT'					=> 'Statut',
	'MMS_STATUT_ATTR_CHGED'			=> 'Attribut de sujet modifié en %s',
	'MMS_STATUT_DELETED'			=> 'Supprimé',
	'MMS_STATUT_FORKED'				=> 'Copié dans %1$s avec l’ID %2$s',
	'MMS_STATUT_ICONCHD'			=> 'Icône de sujet modifié',
	'MMS_STATUT_IPGRABBED'			=> 'IP récupérée',
	'MMS_STATUT_LOCKED'				=> 'Verouillé',
	'MMS_STATUT_MERGED'				=> 'Fusionné dans %s',
	'MMS_STATUT_MOVED'				=> 'Déplacé dans %s',
	'MMS_STATUT_POSTER_CHGED'		=> 'Posteur changé en %s',
	'MMS_STATUT_RECYNC'				=> 'Resynchronisé',
	'MMS_STATUT_UNLOCKED'			=> 'Déverouillé',
	'MMS_SUB_ARROW'					=> '╚═►',
	'MMS_SUCCESS'					=> 'Le processus s’est terminé avec succès!',
	'MMS_TIMEOUT'					=> 'Connection timed out',//To translators: Do not translate this line !This an HTTP code
	'MMS_TIMEOUT_EXP'				=> 'Délai imparti à l’opération dépassé',
	'MMS_TITLE'						=> 'Outil de modération de masse',
									//To translators: Please Keep the first <br /> too !!
	'MMS_TOO_MANY_USERS'			=> 'Pour des raisons de sécurité l’outil de modération de masse ne peux pas être utilisé par plusieurs utilisateurs simultanément!
										<br />Merci d’attendre 20 secondes avant de ré-éssayer.
										<br />Utilisateur connecté actuellement: %s',
	'MMS_TOOLS_POSTS'		=> array(
			'lock'					=> 'Verrouiller les messages  [ Empêche l’édition du message ]',
			'unlock'				=> 'Déverrouiller les messages',
			'delete'				=> 'Supprimer les messages',
			'chgposter'				=> 'Changer le nom du posteur',
			//'fork'					=> 'Copier les sujets',
			'move'					=> 'Déplacer les messages',
			'options'				=> 'Editer les options de message',
			'grabip'				=> 'Récupérer les IPs',
	),
	'MMS_TOOLS_TOPICS'		=> array(
			'lock'					=> 'Verrouiller les sujet',
			'unlock'				=> 'Déverrouiller les sujets',
			'delete'				=> 'Supprimer les sujets',
			'fork'					=> 'Copier les sujets',
			'move'					=> 'Déplacer les sujets',
			'merge'					=> 'Fusionner les sujets',
			'resync'				=> 'Resynchroniser les sujets',
			'chgicon'				=> 'Changement d’icône',
			'attr'					=> 'Changement d’attribut',
	),
	'MMS_TOPIC'						=> 'Sujet',
	'MMS_TOPIC_ALREADY_LOCKED'		=> 'Le sujet a déjà été verrouillé.',
	'MMS_TOPIC_ALREADY_UNLOCKED'	=> 'Le sujet a déjà été déverrouillé.',
	'MMS_TOPIC_DELETED'				=> 'Le sujet a probablement déjà été supprimé.',
	'MMS_TOPIC_ICON'				=> 'Icône de sujet',
	'MMS_TOPIC_ICON_NO'				=> 'Aucune icône',
	'MMS_TOPIC_ID'					=> 'Id du sujet de destination',
	'MMS_TOPIC_ID_EXP'				=> 'Entrez l’Id du sujet de destination des éléments sélectionnés',
	'MMS_TOPIC_ID_INVALID'			=> 'Id de sujet invalide!',
	'MMS_TREAT'				=> array(
			'post'					=> 'Messages traîtés',
			'topic'					=> 'Sujets traîtés'
	),
	'MMS_TREATED'			=> array(
			'post'					=> 'Aucun messages traîté actuellement',
			'topic'					=> 'Aucun sujet traîté actuellement'
	),
	'MMS_UP_ARROW'				=> '╔►',
	'MMS_USERNAME'				=> 'Nouveau nom d’utilisateur',
	'MMS_USERNAME_CASE'			=> 'Le nom d’utilisateur est sensible à la casse',
	'MMS_USERNAME_INVALID'		=> 'Nom d’utilisateur invalide',
	'MMS_VIA_MMS'				=> ' <em>(avec l’outil de modération de masse)</em>',
	'MMS_VIEW_ALL'				=> 'Voir tout',
	'MMS_VIEW_LESS'				=> 'Voir moins',
	'MMS_VIEW_MORE'				=> 'Voir plus',
	'MMS_WARNING_ACTION'		=> 'La modération de masse est un outil puissant comme dangereux, vous devez être sûr de l’action que vous êtes sur le point de commettre, particulièrement avec les outils de suppression et de fusion.
									Soyez bien conscient qu’en cas d’erreur seule une restauration de la base de données pourra résoudre cette erreur sous réserve que celle-ci sois récente.
									La modération de masse ne devrait d’ailleurs pas être utilisée sur des serveurs dont les délais de sauvegarde excèdent 24 heures.
									<br />En cliquant sur «<em>' . $lang ['SUBMIT'] . '</em>» vous prenez donc conscience du présent avertissement et procéderez à l’action choisie, en cas de doute cliquez sur «<em>' . $lang ['CANCEL'] . '</em>» et contactez votre responsable technique afin d’éviter toute mauvaise manipulation.',
));
?>