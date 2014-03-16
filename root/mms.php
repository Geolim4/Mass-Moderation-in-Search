<?php
/**
*
* @package phpBB3 Mass Moderation in Search
^>@version $Id: mms.php v1.1.1 07h79 03/16/2014 Geolim4 Exp $
* @copyright (c) 2013 Geolim4.com  http://Geolim4.com
* @bug/function request: http://geolim4.com/tracker.php
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_MMS', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
require($phpbb_root_path . 'includes/class_mms.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mcp');

$redirect = request_var('r', 0);
$token = request_var('tkn', '');
$is_ajax = request_var('ajax', false);
$resync = request_var('resync', '');
$unlock = request_var('unlock', false);
if ($redirect)
{	if ($redirect == 1)
	{
		meta_refresh(5, $phpbb_root_path . 'index.' . $phpEx);
		$user->add_lang('mods/mms_search');
		trigger_error($user->lang['MMS_REDIRECT']);
	}
	else if ($redirect == 2)
	{
		redirect($phpbb_root_path . 'index.' . $phpEx);
	}

}
if (!sizeof($_POST))
{
	if ($auth->acl_get('m_mms'))
	{
		$user->add_lang('mods/mms_search');
		meta_refresh(5, $phpbb_root_path . 'search.' . $phpEx);
		trigger_error($user->lang('MMS_NO_DIRECT_ACCESS', '<a href="' . append_sid($phpbb_root_path . 'search.' . $phpEx) . '">', '</a>'));
	}
	else
	{
		trigger_error($user->lang['NOT_AUTHORISED']);
	}
}
if (!$auth->acl_get('m_mms') && !$is_ajax)
{
	$user->add_lang('mods/mms_search');
	meta_refresh(5, $phpbb_root_path . 'index.' . $phpEx);
	trigger_error($user->lang['NOT_AUTHORISED']);
}
$mms = new mms_search($token);

if (empty($config['mms_mod_enable']))
{
	$mms->trigger_error($user->lang['MMS_MOD_DISABLED'] . '<br />' . $user->lang['MMS_REDIRECT'], E_USER_WARNING, append_sid("{$phpbb_root_path}mms.{$phpEx}", "r=2"), false);
}
if ($mms->is_ajax)
{
	// Report only fatal errors as in Ajax mode
	error_reporting(E_ERROR | E_PARSE);

	if (empty($resync) && empty($unlock))
	{
		if ($mms->row_mode == 'post')
		{
			if (!sizeof($mms->{MOD_MODE}))
			{
				$mms->ajax_error($user->lang['INFORMATION'], $user->lang['NO_POST_SELECTED'] . '<br />' . $user->lang['MMS_REDIRECT'], append_sid("{$phpbb_root_path}mms.{$phpEx}", "r=2"));
			}
			if (!$auth->acl_get('m_mms'))
			{
				$mms->ajax_error($user->lang['INFORMATION'], $user->lang['NOT_AUTHORISED'] . '<br />' . $user->lang['MMS_REDIRECT'], append_sid("{$phpbb_root_path}mms.{$phpEx}", "r=2"));
			}
			switch ($mms->{'mms_' . $mms->row_mode . '_action'})
			{
				case 'delete':
				case 'lock':
				case 'unlock':
				case 'move':
				case 'chgposter':
				case 'options':
				case 'grabip':
					$mms->ajax_check_pwd();
					$mms->{$mms->row_mode . '_' . $mms->mms_post_action}();
				break;

				default:
						trigger_error('NO_MODE');
				break;
			}
		}
		else if ($mms->row_mode == 'topic')
		{
			if (!sizeof($mms->{MOD_MODE}))
			{
				$mms->ajax_error($user->lang['INFORMATION'], $user->lang['NO_TOPIC_SELECTED'] . '<br />' . $user->lang['MMS_REDIRECT'], append_sid("{$phpbb_root_path}mms.{$phpEx}", "r=2"));
			}
			switch ($mms->{'mms_' . $mms->row_mode . '_action'})
			{
				case 'delete':
				case 'lock':
				case 'fork':
				case 'move':
				case 'unlock':
				case 'resync':
				case 'merge':
				case 'chgicon':
				case 'attr':
					$mms->ajax_check_pwd();
					$mms->{$mms->row_mode . '_' . $mms->mms_topic_action}();
				break;

				default:
						trigger_error('NO_MODE');
				break;
			}
		}
		else
		{
			trigger_error('MMS_UNALLOWED_MODE');
		}
	}
	else if($resync)
	{
		switch ($resync)
		{
			case 'f':
			case 't':
			case 's':
			case 'u':
				$mms->ajax_check_pwd();
				$mms->final_resync($resync);
			break;

			default:
					trigger_error('NO_MODE');
			break;
		}
	}
	else if($unlock)
	{
		$mms->ajax_check_pwd();
		$mms->unlock();
	}
}
$template->assign_vars(array(
	'L_MMS_LEFT'		=> $user->lang['MMS_LEFT'][$mms->row_mode],
	'L_MMS_TREATED'		=> $user->lang['MMS_TREATED'][$mms->row_mode],
	'L_MMS_FAIL'		=> $user->lang['MMS_FAIL'][$mms->row_mode],
	'L_MMS_IGNORED_JS'	=> $user->lang['MMS_IGNORED_JS'][$mms->row_mode],
));

// Output the page
page_header($user->lang['MMS_TITLE']);
$template->set_filenames(array('body' => 'mms_body.html'));
page_footer();
?>