<?php
/**
*
* @package language	[Standard french] Mass Moderation in Search
^>@version $Id: info_acp_mms.php v1.1.1 07h79 03/16/2014 Geolim4 Exp $
* @copyright (c) 2012 Geolim4.com  http://Geolim4.com
* @bug/function request: http://geolim4.com/tracker.php
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
//Important note to validators:
//We've not created an UCP file because, here only 3 lang key are used for UCP, so it's a bit useless!
$lang = array_merge($lang, array(

//Mod Name
	'ACP_MMS_CONFIG'		=> 'Modération de masse dans la recherche',
	'ACP_MMS_SETTINGS'		=> 'Configuration de la modération de masse',
//UMIL
	'ACP_MMS_CONFIG_UMIL'		=> 'Mots de passe interdits',
	'MMS_UMIL_LOG'				=> 'Gestion des journaux de Mass Moderation in Search',
	'MMS_UMIL_LOGS'				=> 'Suppression des journaux de Mass Moderation in Search',
	'MMS_CONFIG_UMIL_PHP'		=> 'Information de version PHP',
	'MMS_CONFIG_UMIL_PHP530_OK'	=> '<strong style="color:green;">Super!! Vous possédez PHP <strong>5.3.0</strong> ou supérieur, vous pouvez continuer l’installation en toute sécurité.</strong>',
	'MMS_CONFIG_UMIL_PHP530_NO'	=> '<strong style="color:red;">Dommage!! Vous ne possédez pas PHP <strong>5.3.0</strong> ou supérieur, vous ne devriez pas continuer l’installation sans risquer des erreurs critiques</strong>',

//Logs
	'ACP_MMS_LOG_ALTERED' 		=> '<strong>Modification des paramètres du Mod Mass Moderation in Search</strong>',
	'ACP_MMS_LOG_OFF'			=> '<strong>Mass Moderation in Search est désactivé car l’installation n’est pas terminée.</strong><br />
										» Erreur retournée :<br /> %s',
//Mod Settings
	'ACP_MMS_ADDONS'			=> 'Activation automatique des plugs-in',
	'ACP_MMS_ADDONS_EXP'		=> 'Cette option permettras à Mass Moderation in Search de détecter et d’activer les plugs-in automatiquement.
									<br />Les plugs-in disponibles actuellement sont les suivant:',
	'ACP_MMS_ADDONS_LIST'		=> '<a href="https://www.phpbb.com/customise/db/mod/qte">Quick Title Edition</a>, <a href="https://www.phpbb.com/customise/db/mod/hide_profile_in_viewtopic/">Hide profile in Viewtopic</a>, <a href="http://forums.phpbb-fr.com/mods-en-dev-phpbb3/sujet188266.html">Moderator Message</a>, <a href="https://www.phpbb.com/customise/db/mod/prime_post_revisions/">Prime Post Revision</a>',
	'ACP_MMS_MAX_ATTEMPTS'		=> 'Tentatives maximum',
	'ACP_MMS_MAX_ATTEMPTS_EXP'	=> 'Si le client atteint la limite de tentatives maximum de modération de sujet/message, le script considérera que le sujet/message est ignoré.',
	'ACP_MMS_MOD'				=> 'Activer la modération de masse',
	'ACP_MMS_UPDATED_CFG'		=> 'Les paramètres ont été mis à jour.',
	'ACP_MMS_MULTI_USERS'		=> 'Autoriser l’utilisation multi-utilisateur (déconseillé)',
	'ACP_MMS_MULTI_USERS_EXP'	=> 'Si vous autorisez l’utilisation multi-utilisateur cela peux résulter d’une hausse de charge significative du serveur si plusieurs utilisateurs utilisent la modération de masse en même temps.',
	'ACP_MMS_OFFLINE_TIME'		=> 'Période d’inactivité',
	'ACP_MMS_OFFLINE_TIME_EXP'	=> 'Période d’inactivité (en secondes) a partir duquel un utilisateur ne seras plus considéré comme connecté à la modération de masse.<br />Valeur par défaut: 20',
	'ACP_MMS_PAGINATION'		=> 'Résultat par page',
	'ACP_MMS_PAGINATION_EXP'	=> 'Nombre de résultat maximum proposé par l’outil de modération de masse.<br />Limite maximum: 5000',
	'ACP_MMS_PASSWORD'			=> 'Confirmation par mot de passe',
	'ACP_MMS_PASSWORD_EXP'		=> 'Demander un mot de passe de confirmation lors du démarrage de la procédure de modération de masse (recommandé).',
	'ACP_MMS_PREVIEW'			=> 'Nombre limite de caractères de prévisualisation',
	'ACP_MMS_TIMEOUT'			=> 'Timeout Ajax',
	'ACP_MMS_TIMEOUT_EXP'		=> 'Temps limite (en secondes) d’attente des requêtes en Ajax avant que le client avorte la connexion.<br />Valeur par défaut: 5',

//Mod install error
	'ACP_MMS_ERR_INSTALL'				=> 'Le mod est maintenant désactivé par mesure de sécurité jusqu’à ce que l’installation soit terminée.',

	'MMS_INSTALL_NO_COLLUMN'			=> 'La colonne SQL <strong>« %1$s »</strong> de la table <strong>« %2$s »</strong> est absente.',
	'MMS_INSTALL_NO_FILE'				=> 'Le fichier <strong>« %s »</strong> est absent.',
	'MMS_INSTALL_NO_TABLE'				=> 'La table SQL <strong>« %1$s »</strong> est absente.',

//Version Check
	'ACP_ERRORS'						=> 'Erreurs',

	'MMS_CURRENT_VERSION'				=> 'Version actuelle',

	'MMS_ERRORS_CONFIG_ALT'				=> 'Vérification de version et configuration du Mod Mass Moderation in Search',
	'MMS_ERRORS_CONFIG_EXPLAIN'			=> 'Sur cette page, vous pouvez vérifier si votre version de ce Mod est bien à jour et, dans le cas contraire, les actions à effectuer pour le mettre à jour.<br />Vous pouvez également régler des points simples de configuration qui s’y rapportent.',

	'MMS_ERRORS_INSTRUCTIONS'			=> '<br /><h1>Utilisation du Mod Mass Moderation in Search v%1$s</h1><br />
										<p>L’équipe de Geolim4.com vous remercie de votre confiance et espère que vous apprécierez les fonctionalités de ce Mod.<br />
										N’hésitez pas à faire un don pour faire du développement durable et de soutien... Rendez-vous <strong><a href="%2$s" title="Mass Moderation in Search">sur cette page.</a></strong>.</p>
										<p>Pour toute demande de support, rendez vous dans le <strong><a href="%3$s" title="forum de support">forum de support</a></strong>.</p>
										<p>Visitez également le Traqueur <strong><a href="%4$s" title="Traqueur du Mod Mass Moderation in Search">sur cette page</a></strong>. Tenez vous informés des éventuels bogues, ajouts ou demandes de fonctionnalités, la sécurité...</p>',

	'MMS_ERRORS_MESSAGE' 				=> '%s mot',
	'MMS_ERRORS_MESSAGES' 				=> '%s mots',
	'MMS_ERRORS_NO_VERSION'				=> '<span style="color: red">La version du serveur n’a pas pu être contactée...</span>',
	'MMS_ERRORS_VERSION_CHECK'			=> 'Vérificateur de Version du Mod Mass Moderation in Search',
	'MMS_ERRORS_VERSION_CHECK_EXPLAIN'	=> 'Vérifie si la version du Mod « mots de passe interdits » que vous utilisez en ce moment est à jour.',
	'MMS_ERRORS_VERSION_COPY'			=> '<a href="%1$s" title="Mod Mass Moderation in Search">Mod Mass Moderation in Search v%2$s</a> &copy; 2013 <a href="http://geolim4.com" title="geolim4.com"><em>Geolim4.com</em></a>',
	'MMS_ERRORS_VERSION_NOT_UP_TO_DATE'	=> 'Votre version du Mod « mots de passe interdits » n’est pas à jour.<br />Ci-dessous vous trouverez un lien vers l’annonce de sortie de la version la plus récente ainsi que des instructions sur la façon d’effectuer la mise à jour.',
	'MMS_ERRORS_VERSION_UP_TO_DATE'		=> 'Votre installation est à jour.',

	'MMS_ERRORS_UPDATE_INSTRUCTIONS'	=> '
		<h1>Annonce de sortie</h1>
		<p>Veuillez lire <a href="%1$s" title="%1$s"><strong>le sujet de la version la plus récente</strong></a> pour accéder au processus de mise à jour, il peut contenir des informations utiles. Il incluera également le lien de téléchargement ainsi que le journal des modifications.</p>
		<br />
		<h1>Comment mettre à jour votre installation du Mod « mots de passe interdits » ?</h1>
		<p>► Téléchargez la dernière version.</p>
		<p>► Décompressez l’archive et ouvrez le fichier install.xml, il contient toutes les informations de mise à jour.</p>
		<p>► Annonce officielle de la dernière version : (%2$s).</p>',

	'MMS_LATEST_VERSION'				=> 'Dernière version',
	'MMS_NEW_VERSION'					=> 'Votre version du Mod « mots de passe interdits » n’est pas à jour. Votre version est la %1$s, la version la plus récente est la %2$s. Veuillez lire la suite pour plus d’informations.',
	'MMS_UNABLE_CONNECT'				=> 'Impossible de récupérer la version du Mod depuis le serveur, message d’erreur : %s',

));

?>