<?php
/**
*
* @package acp Mass Moederation in Search
* @version $Id: acp_mms.php v1.1.1 07h79 03/16/2014 Geolim4 Exp $
* @copyright (c) 2012 Geolim4.com  http://Geolim4.com
* @bug/function request: http://geolim4.com/tracker.php
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
define('MMS_HOST', 'gl4.fr');
define('MMS_PATH', '');
define('MMS_FILE', 'mms.txt');
class acp_mms
{
	var $u_action;

	function main($id, $mode)
	{
		global $config;//Only Call $config Starting now if $error has returned as TRUE :/

		//Check install before all !!
		$this->mms_check_install();

		//No $error? We can continue.
		global $db, $user, $template, $phpbb_root_path, $phpbb_admin_path, $phpEx, $auth, $cache;

		//tpl settings..
		$this->tpl_name = 'acp_mms';

		//Grab basic vars
		$action				= request_var('action', '');

		//grab submit buttons/param set
		$update					= (isset($_POST['update'])) 	? true : false;
		$submit					= (isset($_POST['submit'])) 	? true : false;
		//secure your mom
		$form_key = 'acp_mms';
		add_form_key($form_key);

		$latest_version = $announcement_url = $trigger_info = '';//Empty string...

		switch ($mode)
		{
			case 'configuration':

				$this->page_title = 'ACP_MMS_CONFIG';
				// Get current and latest version
				$errstr = '';
				$errno = 0;
				$info = get_remote_file(MMS_HOST, MMS_PATH, MMS_FILE, $errstr, $errno);
				if ( $update )
				{
					if ( !check_form_key($form_key) )
					{
						trigger_error($user->lang['FORM_INVALID'], E_USER_WARNING);
					}
					$settings = array (
						'mms_mod_enable'			=> request_var('mms_mod_enable', 1),
						'mms_mod_multi_users'		=> request_var('mms_mod_multi_users', 0),
						'mms_mod_offline_time'		=> request_var('mms_mod_offline_time', 20),
						'mms_mod_pagination'		=> request_var('mms_mod_pagination', 500),
						'mms_mod_password'			=> request_var('mms_mod_password', 1),
						'mms_mod_preview'			=> request_var('mms_mod_preview', 200),
						'mms_mod_timeout'			=> request_var('mms_mod_timeout', 5),
						'mms_mod_addons'			=> request_var('mms_mod_addons', 1),
						'mms_max_attempts'			=> request_var('mms_max_attempts', 5),
					);
					//Define some hard limits
					if($settings['mms_mod_pagination'] > 5000 || $settings['mms_mod_pagination'] < 50 )
					{
						$settings['mms_mod_pagination'] = 500;
					}
					foreach ($settings as $config_name => $config_value)
					{
						//just update what we need...
						if ($config_value != $config[$config_name])
						{
							set_config($config_name, $config_value, false);
						}
					}
					add_log('admin', 'ACP_MMS_LOG_ALTERED');
					trigger_error($user->lang['ACP_MMS_UPDATED_CFG'] . $trigger_info . adm_back_link($this->u_action) );
				}
				if ( $info === false )
				{
					$template->assign_vars(array(
						'S_ERROR'   => true,
						'ERROR_MSG' => sprintf($user->lang['MMS_UNABLE_CONNECT'], $errstr),
					));
				}
				else
				{
					$info 				= explode("\n", $info);
					$latest_version 	= trim($info[0]);
					$announcement_url 	= trim($info[1]);
					$up_to_date			= phpbb_version_compare($config['mms_mod_version'], $latest_version, '<') ? false : true;

					if ( !$up_to_date )
					{
						$template->assign_vars(array(
							'S_ERROR'   			=> true,
							'S_UP_TO_DATE'			=> false,
							'ERROR_MSG' 			=> sprintf($user->lang['MMS_NEW_VERSION'], $config['mms_mod_version'], $latest_version),
							'UPDATE_INSTRUCTIONS'	=> sprintf($user->lang['MMS_ERRORS_UPDATE_INSTRUCTIONS'], $announcement_url, $latest_version),
						));
					}
					else
					{
						$template->assign_vars(array(
							'S_ERROR'   			=> false,
							'S_UP_TO_DATE'			=> true,
							'UP_TO_DATE_MSG'		=> sprintf($user->lang['MMS_ERRORS_VERSION_UP_TO_DATE'], $config['mms_mod_version']),
							'UPDATE_INSTRUCTIONS'	=> sprintf($user->lang['MMS_ERRORS_INSTRUCTIONS'], $config['mms_mod_version'], $announcement_url, trim($info[2]), trim($info[3])),
						));
					}
				}

				$template->assign_vars(array(
					//pagination
					'S_VERSION'				=> isset($config['mms_mod_version']) ? $config['mms_mod_version'] : '',
					'S_CONFIG'				=> true,

					//Basics vars
					'U_ACTION'				=> $this->u_action,
					'U_ACTION_API'			=> $this->u_action . '&amp;new_api=1',
					'TITLE'					=> $user->lang['ACP_MMS_CONFIG'],
					'TITLE_EXPLAIN'			=> $user->lang['MMS_ERRORS_CONFIG_EXPLAIN'],
					'TITLE_IMG'				=> $phpbb_root_path . 'images/mms.png',

					//Mod vars
					'ERRORS_VERSION'		=> sprintf($user->lang['MMS_ERRORS_VERSION_COPY'], $announcement_url, $config['mms_mod_version']),
					'S_NO_VERSION'			=> $latest_version ? false : true,
					'LATEST_VERSION'		=> $latest_version ? $latest_version : $user->lang['MMS_ERRORS_NO_VERSION'],
					'USERS_COUNT'			=> ($config['num_users'] > 50000) ? true : false,//50000 start to be a big board...
					'CURRENT_VERSION'		=> $config['mms_mod_version'],
					//Mod settings
					'MMS_MOD_API'			=> isset($config['mms_mod_api'])				? (string) 	$config['mms_mod_api'] : '',
					'MMS_MOD_ENABLE'		=> isset($config['mms_mod_enable'])				? (((bool)	$config['mms_mod_enable']	== 1)		? true : false) : '',
					'MMS_MOD_MULTI_USERS'	=> isset($config['mms_mod_multi_users'])		? (((bool)	$config['mms_mod_multi_users']	== 1)	? true : false) : '',
					'MMS_MOD_PASSWORD'		=> isset($config['mms_mod_password'])			? (((bool)	$config['mms_mod_password']	== 1)		? true : false) : '',
					'MMS_MOD_ADDONS'		=> isset($config['mms_mod_addons'])				? (((bool)	$config['mms_mod_addons']	== 1)		? true : false) : '',
					'MMS_MOD_OFFLINE_TIME'	=> isset($config['mms_mod_offline_time'])		? (int)		$config['mms_mod_offline_time']			: '',
					'MMS_MOD_PAGINATION'	=> isset($config['mms_mod_pagination'])			? (int)		$config['mms_mod_pagination']			: '',
					'MMS_MOD_PREVIEW'		=> isset($config['mms_mod_preview'])			? (int)		$config['mms_mod_preview']				: '',
					'MMS_MOD_TIMEOUT'		=> isset($config['mms_mod_timeout'])			? (int)		$config['mms_mod_timeout']				: '',
					'MMS_MAX_ATTEMPTS'		=> isset($config['mms_max_attempts'])			? (int)		$config['mms_max_attempts']				: 5,

				));
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

//Fast Install checking
/**
* Check all the steps of the install of the mod
* @noparam
* @return trigger_error if install is corrupted/uncompleted
*/
	function mms_check_install()
	{
		global $cache, $config, $user, $phpbb_root_path, $phpbb_admin_path, $phpEx, $db;
		if ( !empty($config['mms_check_install']) && $config['mms_check_install'] < time() )
		{
/* 			if ( !class_exists('phpbb_db_tools') || !class_exists('dbal') )
			{
				include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
			}
			$mms_db	= new phpbb_db_tools($db); */

			$error = '';
			$sql = 'SELECT style_name
				FROM ' . STYLES_TABLE . "
				WHERE style_id= '" . $config['default_style'] . " ' ";
			$result = $db->sql_query($sql);
			$row_style = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$filelist[] = 'style/acp_mms.html';
			$filelist[] = 'includes/class_mms.' . $phpEx;
			$filelist[] = 'includes/acp/info/acp_mms.' . $phpEx;
			$filelist[] = 'language/' .$config['default_lang']. '/mods/info_acp_mms.' . $phpEx;
			$filelist[] = ($user->theme['template_path'] != $row_style['style_name']) ? 'styles/' .$row_style['style_name']. '/template/mms_body.html' : 'styles/' .$user->theme['template_path']. '/template/mms_body.html';
			$filelist[] = ($user->theme['template_path'] != $row_style['style_name']) ? 'styles/' .$row_style['style_name']. '/template/search_body_mms.html' : 'styles/' .$user->theme['template_path']. '/template/search_body_mms.html';
			$filelist[] = ($user->theme['template_path'] != $row_style['style_name']) ? 'styles/' .$row_style['style_name']. '/template/search_results_mms.html' : 'styles/' .$user->theme['template_path']. '/template/search_results_mms.html';
			$filelist[] = ($user->theme['template_path'] != $row_style['style_name']) ? 'styles/' .$row_style['style_name']. '/theme/mms.css' : 'styles/' .$user->theme['template_path']. '/theme/mms.css';
			/*if ( !$mms_db->sql_table_exists(MMS_TABLE) )
			{
				$error .= sprintf($user->lang['MMS_INSTALL_NO_TABLE'] . '<br />', MMS_TABLE);
				$error .= $user->lang['ACP_MMS_ERR_INSTALL'];
				//Disable Mod: install not complete !!!
				if ( $config['mms_mod_enable'] )
				{
					add_log('critical', 'ACP_MMS_LOG_OFF', $error);
				}
				set_config('mms_mod_enable', 0, false);
				//Die, die, die, die, die, die, die, die, die, die, die, die, die, die, die!!!
				trigger_error($error . adm_back_link($this->u_action), E_USER_ERROR);
			}*/
			$columnlist = array ();

			foreach ( $filelist as $key => $file )
			{
				if (!file_exists($phpbb_root_path . $file) && $key > 0)
				{
					$error .= sprintf($user->lang['MMS_INSTALL_NO_FILE'] . '<br />', $file);
				}
				if (!file_exists($phpbb_admin_path . $file) && $key === 0)
				{
					$error .= sprintf($user->lang['MMS_INSTALL_NO_FILE'] . '<br />', $file);
				}
			}
/* 			foreach ( $columnlist as $key => $column )
			{
				if (!$mms_db->sql_column_exists(MMS_TABLE, $column))
				{
					$error .= sprintf($user->lang['MMS_INSTALL_NO_COLLUMN'] . '<br />', $column, MMS_TABLE);
				}
			} */
			if ( $error )
			{
				$error .= $user->lang['ACP_MMS_ERR_INSTALL'];
				//Disable Mod: install not complete !!!
				if ( $config['mms_mod_enable'] )
				{
					add_log('critical', 'ACP_MMS_LOG_OFF', $error);
				}
				set_config('mms_mod_enable', 0, false);
				trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
			}
			//Install look fine (we've passed the trigger_error() ), cache it 1 hour...
			set_config('mms_check_install', time() + 3600, false);
		}
	}
}
?>