<?php
/**
*
* @package language	[English] Mass Moderation in Search
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
	'ACP_MMS_CONFIG'			=> 'Mass Moderation in Search',
	'ACP_MMS_SETTINGS'			=> 'Mass Moderation in Search settings',
//UMIL
	'ACP_MMS_CONFIG_UMIL'		=> 'Mass Moderation in Search',
	'MMS_UMIL_LOG'				=> 'Managing’s logs of Mass Moderation in Search',
	'MMS_UMIL_LOGS'				=> 'Deleting’s logs of Mass Moderation in Search',
	'MMS_CONFIG_UMIL_PHP'		=> 'Information de version PHP',
	'MMS_CONFIG_UMIL_PHP530_OK'	=> 'Great!! You have PHP <strong>5.3.0</strong> or highter, you can proceed to the mod install',
	'MMS_CONFIG_UMIL_PHP530_NO'	=> 'Sorry!! You don’t have PHP <strong>5.3.0</strong> or highter, you should not proceed to the mod install',
//Logs
	'ACP_MMS_LOG_ALTERED'		=> '<strong>Altered Mass Moderation in Search’s settings</strong>',
	'ACP_MMS_LOG_OFF'			=> '<strong>Mass Moderation in Search has been disabled because the installation is not complete.</strong><br />
									» Error returned :<br /> %s',
//Mod Settings
	'ACP_MMS_ADDONS'			=> 'Automatic addons activation',
	'ACP_MMS_ADDONS_EXP'		=> 'This feature will allow Mass Moderation in Search to detect and enable addons automatically.
									<br />Current available addons are the next:',
	'ACP_MMS_ADDONS_LIST'		=> '<a href="https://www.phpbb.com/customise/db/mod/qte">Quick Title Edition</a>, <a href="https://www.phpbb.com/customise/db/mod/hide_profile_in_viewtopic/">Hide profile in Viewtopic</a>, <a href="http://forums.phpbb-fr.com/mods-en-dev-phpbb3/sujet188266.html">Moderator Message</a>, <a href="https://www.phpbb.com/customise/db/mod/prime_post_revisions/">Prime Post Revision</a>',
	'ACP_MMS_MOD'				=> 'Enable mass moderation',
	'ACP_MMS_UPDATED_CFG'		=> 'The parameters were updated.',
	'ACP_MMS_MAX_ATTEMPTS'		=> 'Maximum attempts',
	'ACP_MMS_MAX_ATTEMPTS_EXP'	=> 'If the client has reached the maximum attempt limit of topic/post moderation, the script will consider that the topic/post is ignored.',
	'ACP_MMS_MULTI_USERS'		=> 'Allow multi-user use (not recommended)',
	'ACP_MMS_MULTI_USERS_EXP'	=> 'If you allow the use multi-user use, this can result in a significant increase of the server load if multiple users are using the mass moderation at the same time.',
	'ACP_MMS_OFFLINE_TIME'		=> 'Inactivity period',
	'ACP_MMS_OFFLINE_TIME_EXP'	=> 'Inactivity period (in seconds) starting an user will no longer be considered logged to the mass moderation.<br />Default value: 20',
	'ACP_MMS_PAGINATION'		=> 'Result per page',
	'ACP_MMS_PAGINATION_EXP'	=> 'The maximum pagination available in the mass moderation tool.<br />Maximum limit: 5000',
	'ACP_MMS_PASSWORD'			=> 'Password confirmation',
	'ACP_MMS_PASSWORD_EXP'		=> 'Ask a password confirmation when starting mass moderation procedure (recommended).',
	'ACP_MMS_PREVIEW'			=> 'Preview chars count limit',
	'ACP_MMS_TIMEOUT'			=> 'Ajax timeout',
	'ACP_MMS_TIMEOUT_EXP'		=> 'Max timeout (in seconds) waiting Ajax requests before the client aborts the connection.<br />Default value: 5',

//Mod install error
	'ACP_MMS_ERR_INSTALL'		=> 'The Mod is now disabled for security reasons until the installation is complete.',

	'MMS_INSTALL_NO_COLLUMN'	=> 'The SQL column <strong>“ %1$s ”</strong> from the <strong>“ %2$s ”</strong> table is missing.',
	'MMS_INSTALL_NO_FILE'		=> 'The file<strong>“ %s ”</strong> is missing.',
	'MMS_INSTALL_NO_TABLE'		=> 'The SQL table <strong>“ %1$s ”</strong> is missing.',

//Version Check
	'ACP_ERRORS'					=> 'Errors',

	'MMS_CURRENT_VERSION'			=> 'Current version',

	'MMS_ERRORS_CONFIG_ALT'			=> 'Configuration of the Mass Moderation in Search MOD',
	'MMS_ERRORS_CONFIG_EXPLAIN'		=> 'On this page you can check if your version of this mod is up to date, otherwise, it is actions to take for the update.<br />You can also set related simple configuration points.',

	'MMS_ERRORS_INSTRUCTIONS'		=> '<br /><h1>To use the Mass Moderation in Search Mod v%1$s</h1><br />
											<p>The team Geolim4.com thank you for your trust and hope you enjoy the features of this Mod.<br />
											Feel free to donate to make durable development and support... Go <strong><a href="%2$s" title="Mass Moderation in Search">on this page</a></strong>.</p>
											<p>For any support request, go to the <strong><a href="%3$s" title="Support Forum">Support Forum</a></strong>.</p>
											<p>Also visit the Tracker <strong><a href="%4$s" title="Tracker of Mod Mass Moderation in Search">on this page</a></strong>. Keep you informed of any bugs, feature requests or additions, security...</p>',

	'MMS_ERRORS_MESSAGE' 				=> '%s word',
	'MMS_ERRORS_MESSAGES' 				=> '%s words',
	'MMS_ERRORS_NO_VERSION'				=> '<span style="color: red">The version of the server could not be found...</span>',
	'MMS_ERRORS_VERSION_CHECK'			=> 'Version checker of Mass Moderation in Search',
	'MMS_ERRORS_VERSION_CHECK_EXPLAIN'	=> 'Checks if the version of Mass Moderation in Search that you are currently using is the latest.',
	'MMS_ERRORS_VERSION_COPY'			=> '<a href="%1$s" title="Mod Mass Moderation in Search">Mod Mass Moderation in Search v%2$s</a> &copy; 2013 <a href="http://geolim4.com" title="geolim4.com"><em>Geolim4.com</em></a>',
	'MMS_ERRORS_VERSION_NOT_UP_TO_DATE'	=> 'Your version of the Mass Moderation in Search Mod is outdated.<br />Below you will find a link to the release announcement of the latest version and instructions on how to perform the update.',
	'MMS_ERRORS_VERSION_UP_TO_DATE'		=> 'Your installation is up to date.',

	'MMS_ERRORS_UPDATE_INSTRUCTIONS'	=> '
				<h1>Release announcement</h1>
				<p>Please read <a href="%1$s" title="%1$s"><strong>the release announcement of the latest version</strong></a> before beginning the process of updating, it may contain useful information. It also contains download links and a complete change log.</p>
				<br />
				<h1>How to update your installation of Mod Mass Moderation in Search</h1>
				<p>► Download the latest version.</p>
				<p>► Unzip the archive and open the install.xml file, it contains all the information update.</p>
				<p>► Official announcement of the latest version : (%2$s).</p>',

	'MMS_LATEST_VERSION'				=> 'Latest version',
	'MMS_NEW_VERSION'					=> 'Your version of Mass Moderation in Search Mod is not up to date. Your version is %1$s, the latest version is %2$s. Please read on for more information.',
	'MMS_UNABLE_CONNECT'				=> 'Can not connect to server version checking, error message : %s',
));

?>