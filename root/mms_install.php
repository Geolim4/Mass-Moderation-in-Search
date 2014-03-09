<?php
/**
*
* @package UMIL Mass Moderation in Search
^>@version $Id: mms_install.php v1.1.0 22h14 06/07/2013 Geolim4 Exp $
* @copyright (c) 2013 Geolim4.com  http://Geolim4.com
* @bug/function request: http://geolim4.com/tracker.php
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
 * @ignore
 */
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();
if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'ACP_MMS_CONFIG_UMIL';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'mms_mod_version';


// The language file which will be included when installing
$language_file = 'mods/info_acp_mms';


/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
$logo_img = 'images/mms_umil.png';

// Options to display to the user
$php_v_required = phpbb_version_compare(PHP_VERSION, '5.3.0', '>=');
$options = array(
	'legend2'	=> 'WARNING',
	'welcome'	=> array('lang' => 'MMS_CONFIG_UMIL_PHP', 'type' => 'custom', 'function' => 'display_message', 'params' => array('MMS_CONFIG_UMIL_PHP530_' . ($php_v_required ? 'OK' : 'NO'), ($php_v_required ? 'success' : 'error')), 'explain' => false),
	'legend3'	=> 'ACP_SUBMIT_CHANGES',
);

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/

/*
* Generate a fully unique and unfindable API ID....
*
*	[quote="Bertie"]Huuuuh Geo, are you a psychopath??[/quote]
*	[quote="Geolim4"]Yes, why? There is a problem with that?[/quote]
*	[quote="Bertie"]Nope! :shock: [/quote]
*	[quote="Geolim4"]Great! 8)[/quote]
*/
$api = strrev(gen_rand_string(6)) . str_shuffle(gen_rand_string(6)) . strrev(gen_rand_string(6)) . str_shuffle(gen_rand_string(6)) . strrev(gen_rand_string(6));

$versions = array(
	'1.1.0' => array(
		'config_add' => array(
			//Not use for now, but later, beggining to generate unique keys now....
			array('mms_mod_api', $api),
		),
	),
	'1.0.0' => array(
		'config_add' => array(
			array('mms_mod_addons', 1),
		),
		'cache_purge' => array(''),
	),
	'0.1.0' => array(
	//Some config we've need...
		'permission_add' => array(
			//UCP acl
			array('m_mms', true),
		),
		'permission_set' => array(
			//UCP ROLES
			array('ROLE_MOD_FULL', array('m_mms') ),
		),
		'config_add' => array(
			array('mms_mod_enable', 1),
			array('mms_mod_multi_users', 0),
			array('mms_mod_offline_time', 20),
			array('mms_mod_pagination', 500),
			array('mms_mod_password', 1),
			array('mms_mod_preview', 200),
			array('mms_mod_timeout', 5),
			array('mms_timecheck', serialize(array(
					'last_sid'	=> $user->session_id,
					'last_uid'	=> $user->data['user_id'],
					'last_time'	=> time() - 3600,
					'last_pwd'	=> time() - 3600,
				))
			)
		),
	//ACP Module
		'module_add' => array(
			array('acp', 'ACP_CAT_DOT_MODS', array(
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'module_langname'	=> 'ACP_MMS_CONFIG',
				'module_auth'		=> 'acl_a_board',
				),
			),
			array('acp', 'ACP_MMS_CONFIG', array(
				'module_basename' => 'mms',
				'module_langname' => 'ACP_MMS_SETTINGS',
				'module_mode'	=> 'configuration',
				'module_auth' => 'acl_a_board',
			)),
		),
		'custom' => 'mms_remove_logs',
		'cache_purge' => array(''),
	),
);
/**
* mms_remove_logs()
* Here is our custom function that will be called in UMIL install file.
* @param string $action The action (install|update|uninstall) will be sent through this.
* @param string $version The version this is being run for will be sent through this.
*/
function mms_remove_logs($action, $version)
{
	global $db, $user;

	if ($action == 'uninstall')
	{
	   $mms_ops = 'ACP_MMS_LOG_ALTERED,ACP_MMS_LOG_OFF,MMS_LOG_TOPIC_LOCK,MMS_LOG_TOPIC_UNLOCK,MMS_LOG_TOPIC_FORK,MMS_LOG_TOPIC_DELETE,MMS_LOG_TOPIC_MOVE,MMS_LOG_TOPIC_RESYNC,MMS_LOG_POST_LOCK,MMS_LOG_POST_UNLOCK,MMS_LOG_POST_DELETE,MMS_LOG_POST_MOVE,MMS_LOG_POST_CHGPOSTER';

		// Delete all Mod's Logs !!!
		$db->sql_query('DELETE FROM ' . LOG_TABLE . ' WHERE ' . $db->sql_in_set('log_operation', explode(',', $mms_ops)));
		return $user->lang['MMS_UMIL_LOGS'];
	}
	else
	{
		return $user->lang['MMS_UMIL_LOG'];
	}
}

/**
* Display a message with a specified css class
*
* @param string		$lang_string	The language string to display
* @param string		$class			The css class to apply
* @return string					Formated html code
**/
function display_message($lang_string, $class)
{
	global $user;

	$lang_string = isset($user->lang[$lang_string]) ? $user->lang[$lang_string] : $lang_string;
	return '<span class="' . $class . '">' . $lang_string . '</span>';
}
// Include the UMIL Auto file, it handles the rest
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);