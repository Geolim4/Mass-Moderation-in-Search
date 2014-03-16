<?php
/**
*
* @package phpBB3 Mass Moderation in Search Class
^>@version $Id: class_mms.php v1.1.1 07h79 03/16/2014 Geolim4 Exp $
* @copyright (c) 2013 Geolim4.com  http://Geolim4.com
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

//prevents child classes from overriding our own...
final class mms_search
{
	/**
	* Class Summary(56):
	*
	*Basic MOD methods:
	*	@ __construct()					=> MMS constructor										@ Access public
	*	@ load_vars()					=> Initialize basic vars								@ Access private
	*	@ load_ext_vars()				=> Initialize externals vars							@ Access private
	*	@ load_values()					=> Initialize dedicated value for each action			@ Access private
	*	@ load_addons()					=> Load default Addons if they are installed...			@ Access private
	*	@ is_unix()						=> Check if we are on UNIX platform: sys_getloadavg()	@ Access private
	*	@ load_timecheck()				=> Check if have only one user at the same time [...]	@ Access private
	*	@ load_sizecheck()				=> Check the packet size sent by the client				@ Access private
	*	@ check_post_options_acl()		=> Extra forums ACL checking for posts options			@ Access private
	*	@ row_mode()					=> Define the Master Script row mode (post/topic)		@ Access private
	*	@ extra_pagination()			=> Hack the url for add MMS params						@ Access public
	*	@ load_datas()					=> Initialize basic tpl vars							@ Access private
	*	@ inject_tpl()					=> Initialize an user selector for the search result.	@ Access public
	* 	@ build_tools()					=> Build available tools depending forums permissions.	@ Access public
	*	@ build_tools_options()			=> Build available posts options depending forum [...]	@ Access private
	*	@ request_vars()				=> Construct the Master row Array [...]					@ Access private
	*	@ unescape_gpc()				=> Remove Magic Quotes for JSON communication			@ Access private
	*	@ load_sanitized_mms_action()	=> Check if the called action is not malicious [...]	@ Access private
	*	@ load_sanitized_mms_action_acl()=> Extended sanitized action for phpBB auth [...]		@ Access private
	*	@ get_tp_row()					=> Construct array for MMS as in non-Ajax mode,			@ Access private
	*	@ text_parse()					=> Post text simple pre-parsor							@ Access private
	*	@ strip_bbcode()				=> Advanced post BBCODE remover, written by RMCgirr83	@ Access private

	*Ajax Topics manipulation Sub-Part:
	*	@ topic_delete()				=> Delete topic as in Ajax-mode							@ Access public
	*	@ topic_move()					=> Move topic as in Ajax-mode							@ Access public
	*	@ topic_lock()					=> Lock topic as in Ajax-mode							@ Access public
	*	@ topic_unlock()				=> Unlock topic as in Ajax-mode							@ Access public
	*	@ topic_resync()				=> Resync topic as in Ajax-mode							@ Access public
	*	@ topic_merge()					=> Merge topic as in Ajax-mode							@ Access public
	*	@ topic_chgicon()				=> Change topic icon as in Ajax-mode					@ Access public
	*	@ topic_attr()					=> Change topic attribute as in Ajax-mode (QTE ADDON)	@ Access public

	*Ajax Posts manipulation Sub-Part:
	*	@ post_delete()					=> Delete post as in Ajax-mode							@ Access public
	*	@ post_move()					=> Move post as in Ajax-mode							@ Access public
	*	@ post_lock()					=> Lock post as in Ajax-mode (Prevent Editing)			@ Access public
	*	@ post_unlock()					=> Unlock post as in Ajax-mode							@ Access public
	*	@ post_chgposter()				=> Poster changer as in Ajax-mode						@ Access public
	*	@ post_options()				=> Change some post options (Remove attachment [...])	@ Access public
	*	@ post_grabip()					=> Grab IPs from specified post IDs as in Ajax-mode		@ Access public

	*Json client interaction function:
	*	@ final_resync()				=> Do a final resync once the Ajax Job is finished!!	@ Access public
	*	@ ajax_check_pwd()				=> Check password and update last auth time if needed	@ Access public
	*	@ ajax_error()					=> Show up an error to the user as of Ajax-mode			@ Access public
	*	@ ajax_echo()					=> Show up some datas to the user as of Ajax-mode		@ Access private
	*	@ youmadbro()					=> Terminate correctly the script... Awesome name heh ? @ Access private
	*	@ trigger_error()				=> Our own trigger_error() [...]						@ Access public

	*Ajax root MCP & utilities functions:
	*	@ check_ids()					=> A very simplified function to check row ID [...]		@ Access private
	*	@ adv_check_ids()				=> Original phpBB function bit modified as MMS need.	@ Access private
	*	@ mcp_move_topic()				=> Move topics											@ Access private
	*	@ mcp_fork_topic()				=> Fork topics											@ Access private
	*	@ mcp_merge_topic()				=> Merge topics											@ Access private
	*	@ mcp_post_options()			=> Do some changes in specified posts					@ Access private
	*	@ get_forum_data()				=> Get simple forum data								@ Access private
	*	@ get_full_topic_data()			=> Get full topic data									@ Access private
	*	@ post_resync_username()		=> Do a final username resync							@ Access private
	*	@ change_poster()				=> Change post poster									@ Access private
	*	@ search_destroy_cache()		=> Destruct search's cache for target UID				@ Access private
	*	@ get_post_data()				=> Get simple post data									@ Access private
	*	@ get_full_topic_data()			=> Get full topic data									@ Access private
	*	@ posting_gen_topic_icons()		=> Generate Topic Icons for display (2th)				@ Access private
	*/


	// SYS const, do not modify them!!!!
	const MMS_HARD_PAGINATION	= 5000;		//Hard pagination limit
	const MMS_HARD_RESYNC_LIMIT	= 250;		//Hard resync limit
	const MMS_IGNORED			= 'ignored';//Rows ignored such deleted posts/topics, unallowed permissions, from->to same topic/forum destination etc.
	const MMS_PASSED			= 'passed';	//Rows correctly treated
	const MMS_DB_FALSE			= 0;		//False value for DB
	const MMS_DB_TRUE			= 1;		//True value for DB

	// SYS fake const, do not modify her !!
	private $MMS_AJAX_PACKETS = 6;//Define the maximum topics/posts treated by the server in the same time. (default 6)

	//root phpBB vars
	private static $template;
	private static $auth;
	private static $user;
	private static $config;
	private static $phpbb_root_path;
	private static $phpEx;
	private static $cache;
	private static $table_prefix;
	private static $db_tools;

	//Global Var for Addons
	private static $qte;

	private $mms_acl = array('lock' => false, 'unlock' => false, 'delete' => false, 'move' => false, 'fork' => false, 'chgposter' => false, 'merge' => false, 'edit' => false, 'info' => false);
	private $mms_f_acl = array('attach' => false, 'bbcode' => false, 'sigs' => false, 'smilies' => false);
	private $mms_f_acl_addon = array('hpiv' => false, 'qte' => false, 'mm' => false, 'ppr' => false);
	private $mms_no_sync_needed = array('grabip', 'lock', 'unlock', 'resync', 'chgicon', 'attr', 'options');
	private $mms_post_mode =  array('lock', 'unlock', 'delete', 'move', 'chgposter', 'options', 'grabip');
	private $mms_topic_mode = array('lock', 'unlock', 'delete', 'move', 'fork', 'resync', 'merge', 'chgicon', 'attr');
	private $mms_resync =  array('f', 't', 's', 'u');
	private $topic_selector = '';
	private $post_selector = '';
	private $post_options_selector = '';
	private $user_selector = '';
	private $forum_selector = '';
	private $mms_action_acl = '';//Sanitized value for acl. Possible value: lock/delete/move/chgposter/merge/ (unlock depend of "lock" permission", fork depend of f_read/f_post source/destination forum, resync depend of m_edit permission)
	public $mms_load = 0;

	public $fids = array();//Can be sticked from search.php
	public $uids = array();//...

	private $time = 0;
	private $tids = array();
	private $pids = array();
	private $rids = array();
	private $unms = array();
	private $final_eval = '';
	private $to_fid = 0;
	private $to_tid = 0;
	private $load = false;
	private $to_uid = array();
	private $row_msg_ary = array();
	private $row_statut_ary = array();
	private $row_full = array();
	private $rids_title = array();
	private $data_to_resync = array();
	private $fdata = array(
		'f' => array(),//Forums IDs to resync
		't' => array(),//Topics IDs to resync
		'u' => array(),//Users IDs to resync
	);

	//Available Addons (4)
	private $addons = array(
		'hpiv'	=> false,	//Hide profile in viewtopic: https://www.phpbb.com/customise/db/mod/hide_profile_in_viewtopic/
		'qte'	=> false,	//Quick Title Edition: https://www.phpbb.com/customise/db/mod/qte
		'mm'	=> false,	//Moderator Message: http://forums.phpbb-fr.com/mods-en-dev-phpbb3/sujet188266.html
		'ppr'	=> false,	//Prime Post Revision: https://www.phpbb.com/customise/db/mod/prime_post_revisions/
	);

	//External vars
	public	$row_mode = '';//post/topic
	public	$resync = '';//forums/topics/stats/users
	public	$mms_action = '';//Possible value: lock/unlock/delete/move/resync/fork/chgposter/options/attr/grabip
	public	$mms_topic_action = '';
	public	$mms_post_action = '';
	public	$mms_topic_ary = array();
	public	$mms_post_ary = array();
	public	$mms_pagination = 25;
	public	$is_ajax = false;
	public	$post_reason = false;
	public	$ajax_data = '';
	public	$mms_from_sr = '';
	public	$post_option = '';

	/****
	* __construct()
	* MMS constructor
	* @noparam
	****/
	public function __construct()
	{
		$this->load_vars();
		$this->load_addons();
		$this->load_ext_vars();
		$this->load_values();
		$this->load_timecheck();
		$this->load_datas();
	}

	/****
	* load_vars()
	* Initialize basic vars
	* @noparam
	****/
	private function load_vars()
	{
		global $template, $db, $user, $auth, $config;
		global $phpbb_root_path, $phpEx, $cache, $table_prefix;

		include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);

		//Do the globals vars fork
		$this->template			= &$template;
		$this->db				= &$db;
		$this->user				= &$user;
		$this->auth				= &$auth;
		$this->config			= &$config;
		$this->phpbb_root_path	= &$phpbb_root_path;
		$this->phpEx			= &$phpEx;
		$this->cache			= &$cache;
		$this->table_prefix		= &$table_prefix;
		$this->db_tools			= new phpbb_db_tools($db);
		$this->time				= time();
		if ($this->is_unix() && ((function_exists('sys_getloadavg') && $load = sys_getloadavg()) || ($load = explode(' ', @file_get_contents('/proc/loadavg')))))
		{
			$this->load = array_slice($load, 0, 1);
			$this->load = floatval($this->load[0]);
		}
		if (!phpbb_version_compare(PHP_VERSION, '5.4.0', '>=') && !defined('ENT_XHTML'))
		 {
			define('ENT_XHTML', 32);
		 }
	}

	/****
	* load_ext_vars()
	* Initialize externals vars
	* @noparam
	****/
	private function load_ext_vars()
	{
		$this->user->add_lang('mods/mms_search');
		$this->mms_topic_action =  request_var('mms_topic_action', '');
		$this->mms_post_action =  request_var('mms_post_action', '');
		$this->resync = request_var('resync', '');
		$this->mms_from_sr =  request_var('mms_from_sr', '');
		$this->config['posts_per_page'] = &$this->mms_pagination;
		$this->config['topics_per_page'] = &$this->mms_pagination;
		$this->config['search_block_size'] = &$this->mms_pagination;
		if ($this->config['mms_mod_pagination'] > $this::MMS_HARD_PAGINATION || $this->config['mms_mod_pagination'] < 50)
		{
			$this->config['mms_mod_pagination'] = $this::MMS_HARD_PAGINATION;
		}
		$this->mms_load = request_var('mms_load', 0);
		$this->is_ajax = request_var('ajax', 0);
		$this->ajax_data = request_var('ajax_data', '');

		if ($this->mms_load || defined('IN_MMS'))
		{
			if (empty($this->resync))
			{
				$this->row_mode($mode);
				$this->request_vars();
				$this->load_sanitized_mms_action();
				if (sizeof($this->{MOD_MODE}) && empty($this->is_ajax))
				{
					$this->get_tp_row();
				}
			}
		}
	}

	/****
	* load_values()
	* Initialize dedicated value for each action
	* @noparam
	****/
	private function load_values()
	{
		switch ($this->mms_action)
		{
			case 'fork':
				$this->MMS_AJAX_PACKETS = 4;//Topic forking is really an huge load for the server!
			break;

			case 'move':
			case 'resync':
			case 'merge':
			case 'delete':
			case 'chgposter':
				$this->MMS_AJAX_PACKETS = 6;
			break;

			case 'options':
				$this->MMS_AJAX_PACKETS = 8;
			break;

			case 'lock':
			case 'unlock':
			case 'chgicon':
			case 'attr':
			case 'grabip':
				$this->MMS_AJAX_PACKETS = 10;
			break;
		}
	}

	/****
	* load_addons()
	* Load default Addons if they are installed...
	* @noparam
	****/
	private function load_addons()
	{
		if (!empty($this->config['mms_mod_addons']))
		{
			global $qte;
			if (!empty($qte) && is_object($qte) && file_exists($this->phpbb_root_path . 'includes/functions_attributes.' . $this->phpEx))
			{
				$this->qte = &$qte;
				$this->addons['qte'] = true;
				$this->user->add_lang('mods/attributes');
				$this->template->assign_var('QTE_ADDON', true);
			}
			if (isset($this->config['hpiv_mod_version']))
			{
				$this->addons['hpiv'] = true;
			}
			if (isset($this->config['mm_version']))
			{
				$this->addons['mm'] = true;
			}
			if (file_exists($this->phpbb_root_path . 'includes/prime_post_revisions.' . $this->phpEx) && defined('POST_REVISIONS_TABLE'))
			{
				if ($this->db_tools->sql_table_exists($this->table_prefix . 'post_revisions'))
				{
					$this->addons['ppr'] = true;
				}
			}
		}
	}

	/****
	* is_unix()
	* Check if we are on UNIX platform (sys_getloadavg() function)
	* @noparam
	****/
	private function is_unix()
	{
		return (bool) (substr($_SERVER['DOCUMENT_ROOT'], 0, 1) === '/');
	}

	/****
	* load_timecheck()
	* Check if have only one user at the same time on the MMS, and kick them if needed
	* @noparam
	****/
	private function load_timecheck()
	{
		if (!defined('IN_MMS') || $this->config['mms_mod_multi_users'])
		{
			return;
		}
		$timecheck = unserialize($this->config['mms_timecheck']);
		$now = $this->time;
		if ($timecheck['last_sid'] != $this->user->session_id || $timecheck['last_uid'] != $this->user->data['user_id'])
		{
			if ($timecheck['last_time'] > ($now - $this->config['mms_mod_offline_time']))
			{
				$sql = "SELECT user_id, username, user_colour
					FROM " .  USERS_TABLE  . '
					WHERE user_id = ' . (int) $timecheck['last_uid'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);
				$user_string = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
				$this->trigger_error($this->user->lang('MMS_TOO_MANY_USERS', $user_string), E_USER_WARNING, false, true);
			}
		}
		$timecheck = array(
			'last_sid'	=> $this->user->session_id,
			'last_uid'	=> $this->user->data['user_id'],
			'last_time'	=> $now,
			'last_pwd'	=> $timecheck['last_pwd'],//We are not re-authed yet, keep the last auth time...
		);
		set_config('mms_timecheck', serialize($timecheck));
	}

	/****
	* load_sizecheck()
	* Check the packet size sent by the client
	* @noparam
	****/
	private function load_sizecheck()
	{
		//Topic forking is really an huge load for the server!
		if ($this->mms_action == 'fork' && sizeof($this->{MOD_MODE}) > 4)
		{
			send_status_line(412, 'Request Entity Too Large');
			//There is maybe a potential exploit attempt...Broke the script now!
			$this->trigger_error($this->user->lang['MMS_PACKET_SIZE'], E_USER_ERROR, append_sid("{$this->phpbb_root_path}index.{$this->phpEx}", ""), false);
		}
		if (($this->mms_action == 'move' || $this->mms_action == 'resync' || $this->mms_action == 'merge' || $this->mms_action == 'delete' || $this->mms_action == 'chgposter')&& sizeof($this->{MOD_MODE}) > 6)
		{
			send_status_line(412, 'Request Entity Too Large');
			//There is maybe a potential exploit attempt...Broke the script now!
			$this->trigger_error($this->user->lang['MMS_PACKET_SIZE'], E_USER_ERROR, append_sid("{$this->phpbb_root_path}index.{$this->phpEx}", ""), false);
		}
		if ($this->mms_action == 'options' && sizeof($this->{MOD_MODE}) > 8)
		{
			send_status_line(412, 'Request Entity Too Large');
			//There is maybe a potential exploit attempt...Broke the script now!
			$this->trigger_error($this->user->lang['MMS_PACKET_SIZE'], E_USER_ERROR, append_sid("{$this->phpbb_root_path}index.{$this->phpEx}", ""), false);
		}
		if (($this->mms_action == 'lock' || $this->mms_action == 'unlock' || $this->mms_action == 'chgicon' || $this->mms_action == 'attr' || $this->mms_action == 'grabip')&& sizeof($this->{MOD_MODE}) > 10)
		{
			send_status_line(412, 'Request Entity Too Large');
			//There is maybe a potential exploit attempt...Broke the script now!
			$this->trigger_error($this->user->lang['MMS_PACKET_SIZE'], E_USER_ERROR, append_sid("{$this->phpbb_root_path}index.{$this->phpEx}", ""), false);
		}
	}

	/****
	* check_post_options_acl()
	* Extra forums ACL checking for posts options
	* @param int $fid forum id permission to check
	* @param bool $is_me check if the check permission is for current MMS user or not
	****/
	private function check_post_options_acl($fid, $is_me = false)
	{
		if (!$is_me)
		{
			switch ($this->post_option)
			{
				case 'disable_sig':
				case 'enable_sig':
					if (!$this->auth->acl_get('f_sigs', $fid) || !$this->auth->acl_get('m_edit', $fid))
					{
						return false;
					}
				break;

				case 'disable_smilies':
				case 'enable_smilies':
					if (!$this->auth->acl_get('f_smilies', $fid) || !$this->auth->acl_get('m_edit', $fid))
					{
						return false;
					}
				break;

				case 'disable_links':
				case 'enable_links':

				break;

				case 'disable_bbcodes':
				case 'enable_bbcodes':
					if (!$this->auth->acl_get('f_bbcode', $fid) || !$this->auth->acl_get('m_edit', $fid))
					{
						return false;
					}
				break;

				case 'remove_attachment':
					if (!$this->auth->acl_get('f_attach', $fid) || !$this->auth->acl_get('m_edit', $fid))
					{
						return false;
					}
				break;

				//Addons
				case 'disable_hpiv':
				case 'enable_hpiv':
					if (!$this->addons['hpiv'] || !$this->auth->acl_get('f_post_profile', $fid) || !$this->auth->acl_get('m_edit', $fid))
					{
						return false;
					}
				break;

				case 'remove_ppr':
					if (!$this->addons['ppr'] || !$this->auth->acl_get('f_read', $fid) || !$this->auth->acl_get('m_delete', $fid))
					{
						return false;
					}
				break;

				case 'remove_mm':
					if (!$this->addons['mm'] || !$this->auth->acl_get('f_read', $fid) || !$this->auth->acl_get('m_mm_delete', $fid))
					{
						return false;
					}
				break;
			}
		}
		else
		{
			switch ($this->post_option)
			{
				case 'disable_sig':
				case 'enable_sig':
					if (!$this->auth->acl_get('f_sigs', $fid) || (!$this->auth->acl_get('f_edit', $fid) || !$this->auth->acl_get('m_edit', $fid)))
					{
						return false;
					}
				break;

				case 'disable_smilies':
				case 'enable_smilies':
					if (!$this->auth->acl_get('f_smilies', $fid) || (!$this->auth->acl_get('f_edit', $fid) || !$this->auth->acl_get('m_edit', $fid)))
					{
						return false;
					}
				break;

				case 'disable_links':
				case 'enable_links':

				break;
				case 'disable_bbcodes':
				case 'enable_bbcodes':
					if (!$this->auth->acl_get('f_bbcode', $fid) || (!$this->auth->acl_get('f_edit', $fid) || !$this->auth->acl_get('m_edit', $fid)))
					{
						return false;
					}
				break;

				case 'remove_attachment':
					if (!$this->auth->acl_get('f_attach', $fid) || (!$this->auth->acl_get('f_edit', $fid) || !$this->auth->acl_get('m_edit', $fid)))
					{
						return false;
					}
				break;

				//Addons
				case 'disable_hpiv':
				case 'enable_hpiv':
					if (!$this->addons['hpiv'] || !$this->auth->acl_get('f_post_profile', $fid) || (!$this->auth->acl_get('f_edit', $fid) || !$this->auth->acl_get('m_edit', $fid)))
					{
						return false;
					}
				break;

				case 'remove_ppr':
					if (!$this->addons['ppr'] || !$this->auth->acl_get('f_read', $fid) || (!$this->auth->acl_get('f_delete', $fid) || !$this->auth->acl_get('m_delete', $fid)))
					{
						return false;
					}
				break;

				case 'remove_mm':
					if (!$this->addons['mm'] || !$this->auth->acl_get('f_read', $fid) || (!$this->auth->acl_get('f_delete', $fid) || !$this->auth->acl_get('m_mm_delete', $fid)))
					{
						return false;
					}
				break;
			}
		}
		return true;
	}
	/****
	* row_mode()
	* Define the Master Script row mode (post/topic)
	* @ref string param $mode mode passed through
	****/
	private function row_mode(&$mode)
	{
		if (($mode = request_var('mms_type', '')))
		{
			switch ($mode)
			{
				//Extra security measure for variables variable.
				case 'topic':
				case 'post':
					$this->row_mode = $mode;
				break;

				default:
					$this->trigger_error($this->user->lang['NO_MODE']);
				break;
			}
		}
		else if (($mode = request_var('sr', '')))
		{
			switch ($mode)
			{
				//Extra security measure for variables variable.
				case 'topics':
				case 'posts':
					$this->row_mode = substr($mode, 0, -1);//remove the "s"
				break;

				default:
					$this->trigger_error($this->user->lang['NO_MODE']);
				break;
			}
		}
		else
		{
			$this->trigger_error($this->user->lang['NO_MODE']);
		}
	}

	/****
	* extra_pagination()
	* Hack the url for add MMS params.
	* @ref param string $u_search url to hack :)
	****/
	public function extra_pagination(&$u_search)
	{
		$u_search	//Enable MMS
					.= "&amp;mms_load=" . $this->mms_load
					//Topic Action Mode
					. "&amp;mms_topic_action=" . $this->{'mms_' . $this->row_mode . '_action'}
					//Post Action Mode
					. "&amp;mms_post_action=" . $this->{'mms_' . $this->row_mode . '_action'}
					//MMS Pagination
					. "&amp;mms_pagination={$this->mms_pagination}"
		;
	}

	/****
	* load_datas()
	* Initialize basic tpl vars
	* @noparam
	****/
	private function load_datas()
	{
		if (!$this->config['mms_mod_enable'])
		{
			return;
		}
		// Number of chars returned
		$pagination = '<option value="25">25</option>';
		$pagination .= '<option value="50" selected="selected">50</option>';
		$pagination .= '<option value="75">50</option>';

		for ($i = 100; $i <= $this->config['mms_mod_pagination'] ; (($i >= 1000) ? $i += 200 :$i += 50))
		{
			$pagination .= '<option value="' . $i . '">' . $i . '</option>';
		}
		if ($this->mms_load)
		{
			$this->mms_pagination = request_var('mms_pagination', $this->mms_pagination);
			if ($this->mms_pagination > $this->config['mms_mod_pagination'])
			{

				$this->mms_pagination = $this->config['mms_mod_pagination'];//Set the hardlimit
			}
		}
		$this->template->assign_vars(array(
			'S_MMS_SEARCH'			=> $this->auth->acl_get('m_mms') ? true : false,
			'S_MMS_LOAD'			=> request_var('mms_load', 0),
			'S_MMS_MAGINATION'		=> $pagination,
			'S_MMS_ACTION'			=> $this->phpbb_root_path . 'mms.' . $this->phpEx,
			'S_MMS_ACTION_REDIRECT'	=> append_sid("{$this->phpbb_root_path}mms.{$this->phpEx}", "r=1"),
			'S_MMS_INSTANT_REDIRECT'=> append_sid("{$this->phpbb_root_path}mms.{$this->phpEx}", "r=2"),
			'S_MMS_EXTRA_URL'		=> append_sid("{$this->phpbb_root_path}index.{$this->phpEx}", "imms=1"),
			'S_MMS_TYPE'			=> $this->row_mode,
			'S_MMS_AJAX_PACKETS'	=> $this->MMS_AJAX_PACKETS,
			'S_MMS_MOD_TIMEOUT'		=> ($this->config['mms_mod_timeout'] * 1000),
			'S_MMS_MAX_ATTEMPTS'	=> (int) $this->config['mms_max_attempts'],
			'S_MMS_TOPIC_ACTION'	=> in_array($this->mms_topic_action, $this->mms_topic_mode) ? $this->mms_topic_action : '',
			'S_MMS_POST_ACTION'		=> in_array($this->mms_post_action, $this->mms_post_mode) ? $this->mms_post_action : '',
			'S_MMS_MASS_TOOL'		=> (!empty($this->row_mode) ? $this->user->lang['MMS_MASS_' . strtoupper($this->row_mode) . '_TOOL'][$this->{'mms_' . $this->row_mode . '_action'}] : ''),
		));
	}

	/****
	* inject_tpl()
	* Initialize an user selector for the search result.
	* @noparam
	****/
	public function inject_tpl()
	{
		if ($this->mms_topic_action == 'attr' && !$this->addons['qte'])
		{
			//Add-on missing, stop the script definitely!
			$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR);
		}
		if (!function_exists('make_forum_select'))
		{
			include($this->phpbb_root_path . 'includes/functions_admin.' . $this->phpEx);
		}
		//If there is only one user in selector, then the user selector tool is useless..
		if (sizeof($this->uids) > 1)
		{
			foreach ($this->uids AS $uids_)
			{
				$style = ($uids_['user_colour'] ? 'style="color: #' . $uids_['user_colour'] . ';font-weight: bold;" ' : '');
				$this->user_selector .= '<option ' . $style . 'value="' . $uids_['username'] . '">' . $uids_['username'] . '</option>';
			}
			unset($uids_);
			$this->template->assign_var('S_MMS_USER_SELECTOR', $this->user_selector);
		}
		//If there is only one forum in selector, then the forum selector tool is useless..
		if (sizeof($this->fids) > 1)
		{
			$forum_ary = make_forum_select(0, false, false, true, true, true, true);
			$forum_ary_ = array();
			foreach ($this->fids AS $fid_)
			{
				$forum_ary_[$fid_] = $forum_ary[$fid_];
			}
			$forum_ary = $forum_ary_;
			unset($forum_ary_, $fid_);

 			foreach ($forum_ary AS $fids_ => $row)
			{
				//Forum Title Colour Addon integration, uncomment below to see an example...
				//$row['forum_name_colour'] = "AAAAAA";
				$style = (!empty($row['forum_name_colour']) ? ' style="color: #' . $row['forum_name_colour'] . ';" ' : '');

				$this->forum_selector .= '<option value="' . $row['forum_id'] . '"' . $style .'>' . $row['forum_name'] . '</option>';
			}
			unset($uids_);
			$this->template->assign_var('S_MMS_FORUM_SELECTOR', $this->forum_selector);
		}
	}

	/****
	* build_tools()
	* Build available tools depending forums permissions. Final permissions check in the MMS, here is only a pre-check
	* @noparam
	****/
	public function build_tools()
	{
		foreach ($this->fids AS $fids_)
		{
			if ($this->auth->acl_getf('m_lock', $fids_) && !$this->mms_acl['lock'])
			{
				$this->mms_acl['lock'] = true;
				$this->mms_acl['unlock'] = true;
				$this->topic_selector .= '<option value="lock">' . $this->user->lang['MMS_TOOLS_TOPICS']['lock'] . '</option>';
				$this->topic_selector .= '<option value="unlock">' . $this->user->lang['MMS_TOOLS_TOPICS']['unlock'] . '</option>';
				$this->post_selector .= '<option value="lock">' . $this->user->lang['MMS_TOOLS_POSTS']['lock'] . '</option>';
				$this->post_selector .= '<option value="unlock">' . $this->user->lang['MMS_TOOLS_POSTS']['unlock'] . '</option>';
			}
			if ($this->auth->acl_getf('m_move', $fids_) && !$this->mms_acl['move'])
			{
				$this->mms_acl['move'] = true;
				$this->mms_acl['fork'] = true;
				$this->topic_selector .= '<option value="move">' . $this->user->lang['MMS_TOOLS_TOPICS']['move'] . '</option>';
				$this->topic_selector .= '<option value="fork">' . $this->user->lang['MMS_TOOLS_TOPICS']['fork'] . '</option>';
				$this->post_selector .= '<option value="move">' . $this->user->lang['MMS_TOOLS_POSTS']['move'] . '</option>';
				//$this->post_selector .= '<option value="fork">' . $this->user->lang['MMS_TOOLS_POSTS']['fork'] . '</option>';
			}
			if ($this->auth->acl_getf('m_delete', $fids_) && !$this->mms_acl['delete'])
			{
				$this->mms_acl['delete'] = true;
				$this->topic_selector .= '<option value="delete">' . $this->user->lang['MMS_TOOLS_TOPICS']['delete'] . '</option>';
				$this->post_selector .= '<option value="delete">' . $this->user->lang['MMS_TOOLS_POSTS']['delete'] . '</option>';
			}
			if ($this->auth->acl_getf('m_chgposter', $fids_) && !$this->mms_acl['chgposter'])
			{
				$this->mms_acl['chgposter'] = true;
				$this->post_selector .= '<option value="chgposter">' . $this->user->lang['MMS_TOOLS_POSTS']['chgposter'] . '</option>';
			}
			if ($this->auth->acl_getf('m_edit', $fids_) && !$this->mms_acl['edit'])
			{
				$this->mms_acl['edit'] = true;
				$this->topic_selector .= '<option value="chgicon">' . $this->user->lang['MMS_TOOLS_TOPICS']['chgicon'] . '</option>';
				$this->post_selector .= '<option value="options">' . $this->user->lang['MMS_TOOLS_POSTS']['options'] . '</option>';
				if ($this->addons['qte'])
				{
					$this->topic_selector .= '<option value="attr">' . $this->user->lang['MMS_TOOLS_TOPICS']['attr'] . '</option>';
				}
			}
			if ($this->auth->acl_getf('m_info', $fids_) && !$this->mms_acl['info'])
			{
				$this->mms_acl['info'] = true;
				$this->post_selector .= '<option value="grabip">' . $this->user->lang['MMS_TOOLS_POSTS']['grabip'] . '</option>';
			}
			if ($this->auth->acl_getf('m_merge', $fids_) && !$this->mms_acl['merge'])
			{
				$this->mms_acl['merge'] = true;
				$this->topic_selector .= '<option value="merge">' . $this->user->lang['MMS_TOOLS_TOPICS']['merge'] . '</option>';
			}
		}
		unset($fids_);
		if ($this->topic_selector)
		{
			$this->topic_selector .= '<option value="resync">' . $this->user->lang['MMS_TOOLS_TOPICS']['resync'] . '</option>';
		}
		$this->template->assign_vars(array(
			'S_MMS_TOPIC_ACTION'			=> $this->topic_selector,
			'S_MMS_POST_ACTION'				=> $this->post_selector
		));
	}

	/****
	* build_tools_options()
	* Build available posts options depending forums permissions. Final permissions check in the MMS, here is only a pre-check
	* @noparam
	****/
	private function build_tools_options()
	{
		$this->post_options_selector .= '<option value="disable_links">' . $this->user->lang['MMS_UP_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['DISABLE_LINKS'] . '</option>';
		$this->post_options_selector .= '<option value="enable_links">' . $this->user->lang['MMS_SUB_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['ENABLE_LINKS'] . '</option>';
		foreach ($this->fids AS $fids_)
		{
			if ($this->auth->acl_getf('f_sigs', $fids_) && !$this->mms_f_acl['sigs'])
			{
				$this->mms_f_acl['sigs'] = true;
				$this->post_options_selector .= '<option value="disable_sig">' . $this->user->lang['MMS_UP_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['DISABLE_SIG'] . '</option>';
				$this->post_options_selector .= '<option value="enable_sig">' . $this->user->lang['MMS_SUB_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['ENABLE_SIG'] . '</option>';
			}
			if ($this->auth->acl_getf('f_smilies', $fids_) && !$this->mms_f_acl['smilies'])
			{
				$this->mms_f_acl['smilies'] = true;
				$this->post_options_selector .= '<option value="disable_smilies">' . $this->user->lang['MMS_UP_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['DISABLE_SMILIES'] . '</option>';
				$this->post_options_selector .= '<option value="enable_smilies">' . $this->user->lang['MMS_SUB_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['ENABLE_SMILIES'] . '</option>';
			}
			if ($this->auth->acl_getf('f_bbcode', $fids_) && !$this->mms_f_acl['bbcode'])
			{
				$this->mms_f_acl['bbcode'] = true;
				$this->post_options_selector .= '<option value="disable_bbcodes">' . $this->user->lang['MMS_UP_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['DISABLE_BBCODES'] . '</option>';
				$this->post_options_selector .= '<option value="enable_bbcodes">' . $this->user->lang['MMS_SUB_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['ENABLE_BBCODES'] . '</option>';
			}
			//Addons
			if ($this->addons['hpiv'] && $this->auth->acl_getf('f_post_profile', $fids_) && !$this->mms_f_acl_addon['hpiv'])
			{
				$this->mms_f_acl_addon['hpiv'] = true;
				$this->post_options_selector .= '<option value="enable_hpiv">' . $this->user->lang['MMS_UP_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['ENABLE_HPIV'] . '</option>';
				$this->post_options_selector .= '<option value="disable_hpiv">' . $this->user->lang['MMS_SUB_ARROW'] . $this->user->lang['MMS_POSTS_OPTIONS']['DISABLE_HPIV'] . '</option>';
			}
			if ($this->addons['ppr'] && $this->auth->acl_getf('f_delete', $fids_) && !$this->mms_f_acl_addon['ppr'])
			{
				$this->mms_f_acl_addon['ppr'] = true;
				$this->post_options_selector .= '<option value="remove_ppr">' . $this->user->lang['MMS_POSTS_OPTIONS']['REMOVE_PPR'] . '</option>';
			}
			if ($this->addons['mm'] && $this->auth->acl_getf('m_mm_delete', $fids_) && !$this->mms_f_acl_addon['mm'])
			{
				$this->mms_f_acl_addon['mm'] = true;
				$this->post_options_selector .= '<option value="remove_mm">' . $this->user->lang['MMS_POSTS_OPTIONS']['REMOVE_MM'] . '</option>';
			}
			//Alone option only...
			if ($this->auth->acl_getf('f_attach', $fids_) && !$this->mms_f_acl['attach'])
			{
				$this->mms_f_acl['attach'] = true;
				$this->post_options_selector .= '<option value="remove_attachment">' . $this->user->lang['MMS_POSTS_OPTIONS']['REMOVE_ATTACHMENT'] . '</option>';
			}
		}
		unset($fids_);
		$this->template->assign_vars(array(
			'S_MMS_POST_OPTIONS_ACTION' => $this->post_options_selector,
			//'S_MMS_POST_OPTIONS_SIZE' => substr_count($this->post_options_selector, '<option'), not usefull for now ;)
		));
	}

	private function build_mms_ipreview_url($url, $title)
	{
		$str = "javascript:mmsIpreview('" . htmlentities($url) ."',%20'" . addslashes($title) . "')";
		return $str;
	}
	/****
	* request_vars()
	* Construct the Master row Array which contains all row ID
	* @noparam
	****/
	private function request_vars()
	{
		define('MOD_MODE', "mms_{$this->row_mode}_ary");
		$this->{MOD_MODE} = array();
		//This IF statement will never be used, but will keep it if someone want do an addon for non-JSON server.
		//As in Jquery all input like "mms_(topic|post)_(int)" are automatically transformed into a nice JSON object recovered in the next "ELSE alternative statement"
		if (sizeof($_POST) && !$this->is_ajax && empty($this->mms_from_sr))
		{
			foreach ($_POST AS $_POST_KEY_ => $_POST_VALUE_)
			{
				if (preg_match('/^mms_' . $this->row_mode . '_([0-9]{1,10})$/', $_POST_KEY_, $matches))
				{
					//echo($_POST_KEY_);
					$this->{MOD_MODE}[] = (int) $matches[1];
				}
			}
		}
		else if (!$this->is_ajax && !empty($this->mms_from_sr) && defined('IN_MMS'))
		{
			$post = json_decode($this->unescape_gpc($this->mms_from_sr), true);
			foreach ($post AS $post_key_ => $post_value_)
			{
				if (preg_match('/^mms_' . $this->row_mode . '_([0-9]{1,10})$/', $post_key_, $matches))
				{
					$this->{MOD_MODE}[] = (int) $matches[1];
				}
			}
		}
		else if ($this->is_ajax && empty($this->mms_from_sr))
		{
			//Extra safety: Force Array to be casted as INT
			$this->{MOD_MODE} = array_map('intval', json_decode($this->unescape_gpc(request_var('rids', '', true)), true));
		}
		//Security measure: check here if the client doesn't try to send more than $this->MMS_AJAX_PACKETS allowed packets
		if (sizeof($this->{MOD_MODE}) > $this->MMS_AJAX_PACKETS)
		{
			$ary = array();
			$i = 0;
			foreach ($this->{MOD_MODE} AS $key_ => $val_)
			{
				$ary[$key_] = $val_;
				$i++;
				if ($i = $this->MMS_AJAX_PACKETS)
				{
					//Chunck extra-rows
					break;
				}
			}
			unset($ary, $i, $key_, $val_);
		}
	}

	/****
	* unescape_gpc()
	* Remove Magic Quotes for JSON communication since we cannot do that using ini_set() => http://php.net/manual/en/security.magicquotes.disabling.php
	* @param string $str String we're working
	****/
	private function unescape_gpc($str)
	{
		return str_replace('&quot;', '"', $str);
	}

	/****
	* load_sanitized_mms_action()
	* Check if the called action is not a malicious action passed through URL (GET method)
	* @noparam
	****/
	private function load_sanitized_mms_action()
	{
		$mode = request_var('mms_' . $this->row_mode . '_action', '');//$this->row_mode is already sanitized above: function: row_mode()

		switch ($this->row_mode)
		{
			case 'post':
					switch ($mode)
					{
						case 'delete':
						case 'lock':
						case 'unlock':
						case 'move':
						case 'chgposter':
						case 'options':
						case 'grabip':
							$this->mms_action = $mode;
							$this->load_sanitized_mms_action_acl();
						break 2;

						default:
							$this->trigger_error($this->user->lang['MMS_BAD_POST_MODE'], E_USER_WARNING);
						break;
					}
			break;

			case 'topic':
					switch ($mode)
					{
						case 'delete':
						case 'lock':
						case 'unlock':
						case 'move':
						case 'fork':
						case 'resync':
						case 'merge':
						case 'chgicon':
						case 'attr':
								$this->mms_action = $mode;
								$this->load_sanitized_mms_action_acl();
						break 2;

						default:
							$this->trigger_error($this->user->lang['MMS_BAD_TOPIC_MODE'], E_USER_WARNING);
						break;
					}
			break;

		}
	}

	/****
	* load_sanitized_mms_action_acl()
	* Extented sanitized action for phpBB auth system (ACLs)
	* @noparam
	****/
	private function load_sanitized_mms_action_acl()
	{
		switch($this->mms_action)
		{
			case 'fork':
				$this->mms_action_acl = 'move';
			break;

			case 'unlock':
				$this->mms_action_acl = 'lock';
			break;

			case 'resync':
				$this->mms_action_acl = '';
			break;

			case 'options':
			case 'chgicon':
			case 'attr':
				$this->mms_action_acl = 'edit';
			break;

			case 'grabip':
				$this->mms_action_acl = 'info';
			break;

			default:
				$this->mms_action_acl = $this->mms_action;//It is now safe °-°
			break;
		}
	}

	/****
	* get_tp_row()
	* Construct array for MMS as in non-Ajax mode, directly grab from search_result.html and do a second auth pass check
	* @noparam
	****/
	private function get_tp_row()
	{
		if ($this->row_mode)
		{
			switch ($this->row_mode)
			{
				case 'topic':
						$sql = 'SELECT t.topic_id, t.forum_id, t.topic_title AS row_title, t.topic_poster AS user_id, t.topic_first_poster_name AS username, t.topic_first_poster_colour AS user_colour, t.topic_type AS row_type, t.topic_time AS row_time, p.post_text AS text_content
							FROM ' . TOPICS_TABLE . ' t
							LEFT JOIN ' . POSTS_TABLE . ' p
									ON (p.post_id = t.topic_first_post_id)
							WHERE ' . $this->db->sql_in_set('t.topic_id', $this->{MOD_MODE});
				break;
				case 'post':
						$sql = 'SELECT p.post_id, p.topic_id, p.forum_id, p.post_subject AS row_title, p.poster_id, p.post_time AS row_time, p.post_text AS text_content, t.topic_type AS row_type, u.user_id, u.username, u.user_colour
							FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . USERS_TABLE . ' u
									ON (u.user_id = p.poster_id)
							LEFT JOIN ' . TOPICS_TABLE . ' t
									ON (t.topic_id = p.topic_id)
							WHERE ' . $this->db->sql_in_set('p.post_id', $this->{MOD_MODE});
				break;
			}
			$result = $this->db->sql_query($sql);
			$i = $f = 0;
			$json_row = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($this->auth->acl_get('m_' . $this->mms_action_acl, $row['forum_id']) && $this->auth->acl_gets('f_list', 'f_read', $row['forum_id']) && $row['row_type'] != POST_GLOBAL)
				{
					$this->template->assign_block_vars('mms_row',  array(
						'POST_ID'		=> isset($row['post_id']) ? $row['post_id'] : '',
						'TEXT_CONTENT'	=> $this->text_parse($row),
						'TOPIC_ID'		=> $row['topic_id'],
						'USER_ID'		=> $row['user_id'],
						'FULL_USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'FULL_URL'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->phpEx}", "f=" . $row['forum_id'] . (isset($row['post_id']) ? '&amp;p=' . $row['post_id'] . '#p' . $row['post_id'] :  "&amp;t=" . $row['topic_id']) ),
						'USERNAME'		=> $row['username'],
						'USER_COLOUR'	=> $row['user_colour'],
						'ROW_TITLE'		=> $row['row_title'],
						'ROW_TIME'		=> $this->user->format_date($row['row_time']),
						'FORUM_ID'		=> $row['forum_id'],
						'S_MMS_ROW_COUNT'=> $i + 1,
						'IS_VIEWABLE'	=> true
					));
					$i++;
					$this->fids[] = $row['forum_id'];//Do only a pre-auth checking for build_tools_options()
					$json_row[(isset($row['post_id']) ? $row['post_id'] : $row['topic_id'])] = $this->row_mode;
				}
				else if ($row['row_type'] == POST_GLOBAL)//exclude global announcement
				{
					$this->template->assign_block_vars('mms_fail_row',  array(
						'POST_ID'		=> isset($row['post_id']) ? $row['post_id'] : '',
						'TEXT_CONTENT'	=> $this->text_parse($row),
						'TOPIC_ID'		=> $row['topic_id'],
						'USER_ID'		=> $row['user_id'],
						'FULL_USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'FULL_URL'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->phpEx}", "f=" . $row['forum_id'] . "&amp;t=" . $row['topic_id'] . (isset($row['rids']) ? '&amp;p=' . $row['post_id'] : '') ),
						'USERNAME'		=> $row['username'],
						'USER_COLOUR'	=> $row['user_colour'],
						'ROW_TITLE'		=> $row['row_title'],
						'ROW_TIME'		=> $this->user->format_date($row['row_time']),
						'FORUM_ID'		=> $row['forum_id'],
						'FAIL_REASON'	=> $this->user->lang['MMS_GLOBAL_ERROR'],
						'S_MMS_ROW_COUNT'=> $f + 1,
						'IS_VIEWABLE'	=> true
					));
					$f++;
				}
				else if (!$this->auth->acl_get('m_' . $this->mms_action_acl, $row['forum_id']) && $this->auth->acl_gets('f_list', 'f_read', $row['forum_id']))
				{
					$this->template->assign_block_vars('mms_fail_row',  array(
						'POST_ID'		=> isset($row['post_id']) ? $row['post_id'] : '',
						'TEXT_CONTENT'	=> $this->text_parse($row),
						'TOPIC_ID'		=> $row['topic_id'],
						'USER_ID'		=> $row['user_id'],
						'FULL_USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'FULL_URL'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->phpEx}", "f=" . $row['forum_id'] . "&amp;t=" . $row['topic_id'] . (isset($row['rids']) ? '&amp;p=' . $row['post_id'] : '') ),
						'USERNAME'		=> $row['username'],
						'USER_COLOUR'	=> $row['user_colour'],
						'ROW_TITLE'		=> $row['row_title'],
						'ROW_TIME'		=> $this->user->format_date($row['row_time']),
						'FORUM_ID'		=> $row['forum_id'],
						'FAIL_REASON'	=> $this->user->lang['MMS_NO_MPERMISSION'],
						'S_MMS_ROW_COUNT'=> $f + 1,
						'IS_VIEWABLE'	=> true
					));
					$f++;
				}
				else
				{
					$this->template->assign_block_vars('mms_fail_row',  array(
						'POST_ID'		=> isset($row['post_id']) ? $row['post_id'] : '',
						'TEXT_CONTENT'	=> '',
						'TOPIC_ID'		=> $row['topic_id'],
						'USER_ID'		=> 0,
						'FULL_USER'		=> $this->user->lang['GUEST'],
						'FULL_URL'		=> '',
						'USERNAME'		=> $this->user->lang['GUEST'],
						'USER_COLOUR'	=> '',
						'ROW_TITLE'		=> $this->user->lang['MMS_PRIVATE'],
						'ROW_TIME'		=> $this->user->format_date($this->time),
						'FORUM_ID'		=> $row['forum_id'],
						'FAIL_REASON'	=> $this->user->lang['MMS_NO_FPERMISSION'],
						'S_MMS_ROW_COUNT'=> $f + 1,
						'IS_VIEWABLE'	=> false
					));
					$f++;
				}
			}
			$this->db->sql_freeresult($result);
			if (!$i && !$f)
			{
				$this->trigger_error($this->user->lang['MMS_NO_' . strtoupper($this->row_mode)], E_USER_WARNING);
			}
			if ($this->row_mode == 'post' && $this->mms_action == 'options')
			{
				$this->build_tools_options();
			}
			else if ($this->row_mode == 'topic' && $this->mms_action == 'chgicon')
			{
				$this->posting_gen_topic_icons();
			}
			else if ($this->row_mode == 'topic' && $this->mms_action == 'attr')
			{
				if ($this->addons['qte'])
				{
					$this->qte->attr_search();
				}
				else
				{
					$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR);
				}
			}
			$this->template->assign_vars(array(
				'S_ROW_TYPE'			=> $this->user->lang['MMS_' . strtoupper($this->row_mode)],
				'L_MMS_FAILED'			=> $this->user->lang['MMS_FAILED'][$this->row_mode],
				'L_MMS_TREATED'			=> $this->user->lang['MMS_TREATED'][$this->row_mode],
				'L_MMS_FAIL'			=> $this->user->lang['MMS_FAIL'][$this->row_mode],
				'L_MMS_TREAT'			=> $this->user->lang['MMS_TREAT'][$this->row_mode],
				'L_MMS_LEFTNO'			=> $this->user->lang['MMS_LEFTNO'][$this->row_mode],
				'S_RESYNC_NEXT_FORUMS' 	=> addslashes($this->user->lang['MMS_FINAL_RESYNC_NEXT']['F']),//Simulate LA_ keys....
				'MMS_ROWS_COUNT'		=> $i,
				'MMS_ROWS_FAIL_COUNT'	=> $f,
				'MMS_SYNC_NEEDED'		=> in_array($this->mms_action, $this->mms_no_sync_needed) ? false : true,
				'MMS_JSON_ROW'			=> json_encode($json_row, JSON_HEX_QUOT),
				'MMS_ACTION'			=> $this->{'mms_' . $this->row_mode . '_action'},
				'S_MMS_FORUM_OPTIONS'	=> make_forum_select(0, false, false, true, true, true),
				'U_FIND_USERNAME'		=> append_sid("{$this->phpbb_root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=mms_form_username&amp;field=mms_username&amp;select_single=true'),
			));
		}
	}

	/****
	* text_parse()
	* Post text simple pre-parsor
	* @param string $text text to pre-parse
	****/
	private function text_parse($text)
	{
		$text['text_content'] = utf8_substr(str_replace('"', '&quot;', censor_text($this->strip_bbcode(nl2br($text['text_content'])))), 0, $this->config['mms_mod_preview']);
		return $text['text_content'] . ((strlen($text['text_content']) >= ($this->config['mms_mod_preview'] - 1)) ? ' [...]' : '');
	}

	/****
	* strip_bbcode()
	* Advanced post bbcode remover, picked up from Last Topic Hover Mod by RMCgirr83
	* @param text $row text to parse
	****/
	private function strip_bbcode($text)
	{
		static $RegEx = array();
		static $bbcode_strip = 'flash';
		// remove basic html chars...
		$text_html = array('&quot;','&amp;','&#039;','&lt;','&gt;');
		$text = str_replace($text_html,'',$text);
		$RegEx = array(//'`<[^>]*>(.*<[^>]*>)?`Usi',
			'`\[(' . $bbcode_strip . ')[^\[\]]+\].*\[/(' . $bbcode_strip . ')[^\[\]]+\]`Usi',
			'`\[/?[^\[\]]+\]`mi',
			'`[\s]+`'
		);
		return strip_tags(preg_replace($RegEx, ' ', $text), '<br>');
	}


/***************************************
*
*		Ajax Function Part
*
****************************************/
	public $ajax_ary = array(
		'error' 		=> false,
		'title'			=> '',
		'message'		=> '',
		'rids_treated'	=> '',
		'continue'		=> false,
		'redirect'		=> '',
		'num_queries'	=> 0,
		'final_eval'	=> '',
		'pwd_confirm'	=> false
	);

	/****************************
	*****************************
	*
	*		Topics manipulation Sub-Part
	*
	*****************************
	****************************/

	/****
	* topic_delete()
	* Delete topic as in Ajax-mode
	* @noparam
	****/
	public function topic_delete()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_delete');

		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->final_eval .= "$('#span_row_id{$val_}').remove();";
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_DELETED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_], $this->unms[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		delete_topics('topic_id', $rows[$this::MMS_PASSED]);

		//Special treatment for this mode:
		$this->fdata['t'] = array();

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> $this->final_eval,
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);

	}

	/****
	* topic_move()
	* Move topic as in Ajax-mode
	* @noparam
	****/
	public function topic_move()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$this->to_fid = $this->ajax_data['forum_id'];
		$shadow = $this->ajax_data['shadow'];
		$forum_data = $this->get_forum_data($this->to_fid);
		if (!sizeof($forum_data))
		{
			$this->trigger_error($this->user->lang['FORUM_NOT_EXIST'], E_USER_NOTICE, false);
		}
		else
		{
			$forum_data = $forum_data[$this->to_fid];

			if ($forum_data['forum_type'] != FORUM_POST)
			{
				$this->trigger_error($this->user->lang['FORUM_NOT_POSTABLE'], E_USER_NOTICE, false);
			}
			else if (!$this->auth->acl_get('f_post', $this->to_fid) || (!$this->auth->acl_get('m_approve', $this->to_fid) && !$this->auth->acl_get('f_noapprove', $this->to_fid)))
			{
				$this->trigger_error($this->user->lang['USER_CANNOT_POST'], E_USER_NOTICE, false);
			}
		}
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_move');

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $rows_)
		{
			$this->mcp_move_topic(array($key_ => $rows_), $shadow, $forum_data);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		//Special treatment for this mode:
		$this->fdata['f'][] = (int)  $this->to_fid;

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);

	}

	/****
	* topic_fork()
	* Fork topic as in Ajax-mode
	* @noparam
	****/
	public function topic_fork()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_');
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);

		$to_forum_id = $this->ajax_data['forum_id'];

		if(!empty($rows[$this::MMS_PASSED]))
		{
			$this->mcp_fork_topic($rows[$this::MMS_PASSED], $to_forum_id);
		}

		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}

		//Special treatment for this mode:
		$this->fdata['f'][] = (int)  $to_forum_id;

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_lock()
	* Lock topic as in Ajax-mode
	* @noparam
	****/
	public function topic_lock()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_lock');
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_status = " .  ITEM_LOCKED  . '
				WHERE ' . $this->db->sql_in_set('topic_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_LOCKED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_unlock()
	* Unlock topic as in Ajax-mode
	* @noparam
	****/
	public function topic_unlock()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_lock');
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_status = " .  ITEM_UNLOCKED  . '
				WHERE ' . $this->db->sql_in_set('topic_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_UNLOCKED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_resync()
	* Resync topic as in Ajax-mode
	* @noparam
	****/
	public function topic_resync()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_');

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_RECYNC'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			// Sync everything and perform extra checks separately
			sync('topic_reported', 'topic_id', $rows[$this::MMS_PASSED], false, true);
			sync('topic_attachment', 'topic_id', $rows[$this::MMS_PASSED], false, true);
			sync('topic', 'topic_id', $rows[$this::MMS_PASSED], true, false);
		}

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_merge()
	* Merge topic as in Ajax-mode
	* @noparam
	****/
	public function topic_merge()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$this->to_tid = $this->ajax_data['topic_id'];

		$sql = "SELECT *
			FROM " .  TOPICS_TABLE  . '
			WHERE topic_id = ' . (int) $this->to_tid;
		$result = $this->db->sql_query($sql);
		$topicdata = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($topicdata['topic_id']))
		{
			$this->trigger_error($this->user->lang['NO_TOPIC'], E_USER_NOTICE, false, true);
		}
		if (!empty($topicdata['topic_moved_id']) && $topicdata['topic_status'] == ITEM_MOVED)
		{
			$this->trigger_error($this->user->lang['MMS_ITEM_MOVED'], E_USER_NOTICE, false, true);
		}
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_merge');

		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$this->mcp_merge_topic($rows[$this::MMS_PASSED], $topicdata['topic_id']);
		}

		$viewtopic_url = append_sid("{$this->phpbb_root_path}viewtopic.$this->phpEx", 't=' . $topicdata['topic_id']);
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang('MMS_STATUS_MERGED', '<a class="mms_wo" href="' . $this->build_mms_ipreview_url($viewtopic_url, $topicdata['topic_title']) . '" title="' . $topicdata['topic_title'] . '">' . $topicdata['topic_title'] . '</a>');
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $topicdata['topic_id'], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_], $this->unms[$val_], $topicdata['topic_title']);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		//Special treatment for this mode:
		$this->fdata['t'] = array();//Reset array because once merged, topic IDs are no longer valids.
		$this->fdata['t'][] = (int)  $this->to_tid;

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_chgicon()
	* Change topic icon as in Ajax-mode
	* @noparam
	****/
	public function topic_chgicon()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_edit', true);
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$icon_id = $this->ajax_data['icon_id'];
		$this->post_reason = $this->ajax_data['reason'];
		$row_full = array();

		if (sizeof($rows[$this::MMS_PASSED]))
		{
			//echo($icon_id);
			$icon = $this->cache->obtain_icons();
			if (!isset($icon[$icon_id]))
			{
				$this->trigger_error($this->user->lang['MMS_POSTS_ICON_FAIL'], E_USER_NOTICE, false, true);
			}
			$sql = "UPDATE " . TOPICS_TABLE . "
				SET icon_id = " . (int) $icon_id  . '
				WHERE ' . $this->db->sql_in_set('topic_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
			foreach ($this->row_full AS $row_full_)
			{
				$row_full = $row_full_['topic_first_post_id'];
			}
			$sql = "UPDATE " . POSTS_TABLE . "
				SET icon_id = " . (int) $icon_id  . '
				WHERE ' . $this->db->sql_in_set('post_id', $row_full);
			$this->db->sql_query($sql);
			$sql_ary = array(
				'icon_id'	=> (int) $icon_id,
			);
			if ($this->post_reason)
			{
				$sql_ary += array(
					'post_edit_time'	=> $this->time,
					'post_edit_user'	=> $this->user->data['user_id'],
					//'post_edit_count'	=> 'post_edit_count + 1', //*
					'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_ICON_REASON'], 0, 254),
				);
			}
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE ' . $this->db->sql_in_set('post_id', $this->pids);
			if ($this->post_reason)
			{
				//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
				$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
			}
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_ICONCHD'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* topic_attr()
	* If you read this, ABDev, you should know i love you terribly :mrgreen:
	* Change topic attribute as in Ajax-mode (QTE ADDON)
	* @noparam
	****/
	public function topic_attr()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, TOPICS_TABLE, 'topic_id', 'topic_title', 'm_edit');
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		if (!$this->addons['qte'])
		{
			//Add-on missing, stop the script definitely!
			$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR, false, false);
		}
		$attr_id = (int) $this->ajax_data['attr_id'];
		if ($attr_id < 0)
		{
			$attr_id = 0;
		}
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$sql_ary = array(
				'topic_attr_id'		=> $attr_id,
				'topic_attr_user'	=> $this->user->data['user_id'],
				'topic_attr_time'	=> $this->time,
			);
			$sql = "UPDATE " . TOPICS_TABLE . "
				SET " . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE ' . $this->db->sql_in_set('topic_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['QTE_TOPIC_ATTRIBUTE_' . ($attr_id < 1 ? 'REMOVED' : 'UPDATED')];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MCP_ATTRIBUTE_' . ($attr_id < 1 ? 'REMOVED' : 'UPDATED'), $this->rids_title[$val_] . $this->user->lang['MMS_VIA_MMS']);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}
	/****************************
	*****************************
	*
	*		Posts manipulation Sub-Part
	*
	*****************************
	****************************/

	/****
	* post_delete()
	* Delete post as in Ajax-mode
	* @noparam
	****/
	public function post_delete()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_delete');

		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_DELETED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_], $this->unms[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			delete_posts('post_id', $rows[$this::MMS_PASSED]);
		}
		//Special treatment for this mode:
		$this->fdata['t'][] = (int)  array();

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_move()
	* Move post as in Ajax-mode
	* @noparam
	****/
	public function post_move()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$this->to_tid = $this->ajax_data['topic_id'];
		$sql = "SELECT *
			FROM " .  TOPICS_TABLE  . '
			WHERE topic_id = ' . (int) $this->to_tid;
		$result = $this->db->sql_query($sql);
		$topicdata = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (empty($topicdata['topic_id']))
		{
			$this->trigger_error($this->user->lang['NO_TOPIC'], E_USER_NOTICE, false, true);
		}
		if (!empty($topicdata['topic_moved_id']) && $topicdata['topic_status'] == ITEM_MOVED)
		{
			$this->trigger_error($this->user->lang['MMS_ITEM_MOVED'], E_USER_NOTICE, false, true);
		}
		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_move');
		$viewtopic_url = append_sid("{$this->phpbb_root_path}viewtopic.$this->phpEx", 't=' . $topicdata['topic_id']);
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang('MMS_STATUS_MOVED', '<a class="mms_wo" href="' . $this->build_mms_ipreview_url($viewtopic_url, $topicdata['topic_title']) . '" title="' . $topicdata['topic_title'] . '">' . $topicdata['topic_title'] . '</a>');
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->unms[$val_], $this->rids_title[$val_], $topicdata['topic_title']);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			move_posts($rows[$this::MMS_PASSED], $this->to_tid);
		}
		//Special treatment for this mode:
		$this->fdata['t'][] = (int)  $this->to_tid;

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_lock()
	* Lock post as in Ajax-mode (Prevent Editing)
	* @noparam
	****/
	public function post_lock()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_lock');
		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_edit_locked = ' .  ITEM_LOCKED  . '
				WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_LOCKED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_], $this->unms[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_unlock()
	* Unlock post as in Ajax-mode
	* @noparam
	****/
	public function post_unlock()
	{
		$this->load_sizecheck();
		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_lock');

		if (sizeof($rows[$this::MMS_PASSED]))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_edit_locked = ' .  ITEM_UNLOCKED  . '
				WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
			$this->db->sql_query($sql);
		}
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_UNLOCKED'];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_chgposter()
	* Poster changer as in Ajax-mode
	* @noparam
	****/
	public function post_chgposter()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$username = trim($this->ajax_data['username']);
		$sql = 'SELECT *
			FROM ' .  USERS_TABLE  . '
			WHERE username = "' . $this->db->sql_escape(utf8_normalize_nfc($username)) . '"';
		$result = $this->db->sql_query($sql);
		$this->to_uid = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		if (empty($this->to_uid['username']))
		{
			$this->trigger_error($this->user->lang['NO_USER'], E_USER_NOTICE, false, true);
		}
		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_chgposter', true);
		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang('MMS_STATUS_POSTER_CHGED', get_username_string('full', $this->to_uid['user_id'], $this->to_uid['username'], $this->to_uid['user_colour']));
			$this->row_statut_ary[$val_] = true;
			$this->change_poster($this->row_full[$val_], $this->to_uid);
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $this->rids_title[$val_], $this->row_full[$val_]['post_username'], $this->to_uid['username']);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->search_destroy_cache($this->uids);
		$this->post_resync_username();
		//Special treatment for this mode:
		$this->fdata['u'][] = (int)  $this->to_uid['user_id'];

		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> $this->fdata,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_options()
	* Change some post options (Remove attachment, enable/disable sig/BBcode/smilies/url)
	* @noparam
	****/
	public function post_options()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);
		$this->post_option = $this->ajax_data['option'];
		$this->post_reason = $this->ajax_data['reason'];

		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_', true);
		$this->mcp_post_options($rows);

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_POSTS_OPTIONS_SUCCESS'][strtoupper($this->post_option)];
			$this->row_statut_ary[$val_] = true;
			add_log('mod', $this->fids[$val_], $this->tids[$val_], 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}) . '_' . strtoupper($this->post_option), $this->row_full[$val_]['username'], $this->rids_title[$val_]);
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* post_grabip()
	* Grab IPs from specified post IDs as in Ajax-mode
	* @noparam
	****/
	public function post_grabip()
	{
		$this->load_sizecheck();
		$this->ajax_data = json_decode($this->unescape_gpc(request_var('mms_data', '', true)), true);

		$rows = $this->check_ids($this->{MOD_MODE}, POSTS_TABLE, 'post_id', 'post_subject', 'm_info', true);

		foreach ($rows[$this::MMS_PASSED] AS $key_ => $val_)
		{
			$this->row_msg_ary[$val_] = $this->user->lang['MMS_STATUS_IPGRABBED'];
			$this->row_statut_ary[$val_] = $this->row_full[$val_]['poster_ip'];
		}
		foreach ($rows[$this::MMS_IGNORED] AS $key_ => $reason_)
		{
			$this->row_msg_ary[$key_] = $reason_;
			$this->row_statut_ary[$key_] = false;
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'title'			=> '',
			'message'		=> $this->row_msg_ary,
			'rids_treated'	=> $this->row_statut_ary,
			'continue'		=> true,
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true,
			'fdata'			=> false,
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****************************
	***************************
	*
	*	Json client interaction function
	*
	***************************
	****************************/

	/****
	* final_resync()
	* Do a final resync once the Ajax Job is finished!!
	* Forums/Topics (And implicitly posts)/Stats/Users counter
	* @param strinf $mode Mode Resync
	****/
	public function final_resync($mode)
	{
		$this->data_to_resync = array_keys(json_decode($this->unescape_gpc(utf8_normalize_nfc(request_var('data', '', true))), true));
		//Set hard-size limit
		$this->data_to_resync = array_slice($this->data_to_resync, 0, $this::MMS_HARD_RESYNC_LIMIT, true);

		//Uncomment below to see transformed input values.
		//print_r($this->data_to_resync);
		//exit;
		if (sizeof($this->data_to_resync) || $mode == 's')
		{
			switch($mode)
			{
				case 'f':
					sync('forum', 'forum_id', $this->data_to_resync, false, true);
				break;

				case 't':
					sync('topic', 'topic_id', $this->data_to_resync, false, true);
				break;

				case 's':
					$sql = 'SELECT COUNT(post_id) AS stat
						FROM ' . POSTS_TABLE . '
						WHERE post_approved = 1';
					$result = $this->db->sql_query($sql);
					set_config('num_posts', (int) $this->db->sql_fetchfield('stat'), true);
					$this->db->sql_freeresult($result);

					$sql = 'SELECT COUNT(topic_id) AS stat
						FROM ' . TOPICS_TABLE . '
						WHERE topic_approved = 1';
					$result = $this->db->sql_query($sql);
					set_config('num_topics', (int) $this->db->sql_fetchfield('stat'), true);
					$this->db->sql_freeresult($result);

					$sql = 'SELECT COUNT(attach_id) as stat
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE is_orphan = ' . $this::MMS_DB_FALSE;
					$result = $this->db->sql_query($sql);
					set_config('num_files', (int) $this->db->sql_fetchfield('stat'), true);
					$this->db->sql_freeresult($result);

					$sql = 'SELECT SUM(filesize) as stat
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE is_orphan = ' . $this::MMS_DB_FALSE;
					$result = $this->db->sql_query($sql);
					set_config('upload_dir_size', (float) $this->db->sql_fetchfield('stat'), true);
					$this->db->sql_freeresult($result);

				break;

				case 'u':
					switch ($this->db->sql_layer)
					{
						case 'postgres':
						case 'firebird':
							$i = 0;
							foreach($this->data_to_resync AS $id)
							{
								if ($i > $this::MMS_HARD_RESYNC_LIMIT)
								{
									break;//Hard-limit...
								}
								$count = 0;
								$sql = 'SELECT COUNT(post_id) AS count
								FROM ' . POSTS_TABLE . '
								WHERE poster_id = ' . (int) $id . '
									AND post_approved = 1
									AND post_postcount = 1';

								$result = $this->db->sql_query($sql);
								$count = (int) $this->db->sql_fetchfield('count');
								$this->db->sql_freeresult($result);

								$sql = 'UPDATE ' . USERS_TABLE . '
									SET user_posts = ' . $count . '
									WHERE user_id = ' . (int) $id;
								$this->db->sql_query($sql);
								$i++;
							}
						break;

						default:
							$sql = 'UPDATE ' . USERS_TABLE . ' u
								SET user_posts = (
										SELECT COUNT(post_id)
									FROM ' . POSTS_TABLE . ' p
									WHERE u.user_id = p.poster_id
										AND post_approved = 1
										AND post_postcount = 1
								)
								WHERE ' . $this->db->sql_in_set('user_id', $this->data_to_resync);
							$this->db->sql_query($sql);
						break;
					}
				break;
			}
		}
		$this->ajax_ary = array(
			'error' 		=> false,
			'message'		=> $this->user->lang['MMS_FINAL_RESYNC'][strtoupper($mode)],
			'action_done'	=> $mode,													//Using @ to remove an unavoidable php notice when we are in end (+1) of $this->mms_resync's array
			'action_next'	=> isset($this->user->lang['MMS_FINAL_RESYNC_NEXT'][strtoupper(@$this->mms_resync[array_search($mode, $this->mms_resync) + 1])]) ? $this->user->lang['MMS_FINAL_RESYNC_NEXT'][strtoupper($this->mms_resync[array_search($mode, $this->mms_resync) + 1])] : '',
			'redirect'		=> '',
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true
		);
		if ($this->auth->acl_get('a_') && $this->load !== false)
		{
			$this->ajax_ary += array('loadavg' => $this->load);
		}
		$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
	}

	/****
	* ajax_check_pwd()
	* Check password and update last auth time if needed
	* @noparam
	****/
	public function ajax_check_pwd()
	{
		if (!$this->config['mms_mod_password'])
		{
			return;
		}
		$now = $this->time;
		$addtional_msg = '';
		$timecheck = unserialize($this->config['mms_timecheck']);
		$password = request_var('password', '');
		$password_confirmed = false;
		if ($timecheck['last_sid'] == $this->user->session_id && $timecheck['last_uid'] == $this->user->data['user_id'])
		{
			if (empty($timecheck['last_pwd']) || $timecheck['last_pwd'] < ($now - 600))
			{
				if (phpbb_check_hash($password, $this->user->data['user_password']))
				{
					$password_confirmed = true;
				}
				else if ($password)
				{
					$addtional_msg = '<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span><span class="error">' . $this->user->lang['MMS_PASSWORD_BAD'] . '</span><br />';
				}
				$timecheck = array(
					'last_sid'	=> $this->user->session_id,
					'last_uid'	=> $this->user->data['user_id'],
					'last_time'	=> $now,
					'last_pwd'	=> $this::MMS_DB_FALSE
				);
				if ($password_confirmed)
				{
					$this->final_eval = '';
					$timecheck['last_pwd'] = $now;
				}
				else
				{
					$this->final_eval = 'pwd_confirmed = false';
				}
				set_config('mms_timecheck', serialize($timecheck));
				$this->ajax_ary = array(
					'error' 		=> !$password_confirmed,//IF password is confirmed that not an error (Allow script to continue itself)
					'title'			=> !$password_confirmed ? $this->user->lang['MMS_PASSWORD'] : '',
					'message'		=> !$password_confirmed ? $addtional_msg . $this->user->lang['MMS_PASSWORD_CONFIRM'] . ':' : '',
					'rids_treated'	=> $this->row_statut_ary,
					'continue'		=> true,
					'redirect'		=> '',
					'num_queries'	=> $this->db->sql_num_queries(),
					'final_eval'	=> $this->final_eval,
					'pwd_confirm'	=> $password_confirmed
				);
				if ($this->auth->acl_get('a_') && $this->load !== false)
				{
					$this->ajax_ary += array('loadavg' => $this->load);
				}
				$this->ajax_echo(json_encode($this->ajax_ary), JSON_HEX_QUOT);
			}
			$timecheck = array(
				'last_sid'	=> $this->user->session_id,
				'last_uid'	=> $this->user->data['user_id'],
				'last_time'	=> $now,
				'last_pwd'	=> $now,//If we're here that because we're still authed, so update....
			);
			set_config('mms_timecheck', serialize($timecheck));
		}
	}

	/****
	* ajax_error()
	* Show up an error to the user as of Ajax-mode
	* @param string $title Title of error
	* @param string $message Message of error
	* @param mixed (bool/string) $redirect Redirect user if needed
	* @param bool continue (Dis)allow client to continue the script
	****/
	public function ajax_error($title, $message, $redirect = false, $continue = false)
	{
		$this->ajax_ary = array(
			'error' 		=> true,
			'title'			=> $title,
			'message'		=> $message,
			'rids_treated'	=> '',
			'redirect'		=> $redirect,
			'continue'		=> $continue,
			'num_queries'	=> $this->db->sql_num_queries(),
			'final_eval'	=> '',
			'pwd_confirm'	=> true

		);
		$this->ajax_echo(json_encode($this->ajax_ary));
	}

	/****
	* ajax_echo()
	* Show up some datas to the user as of Ajax-mode
	* @param string $data JSON data to send @ user
	****/
	private function ajax_echo($data)
	{
		usleep(1000);//Perfect browser sync
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		echo $data;
		$this->youmadbro();
	}

	/****
	* youmadbro()
	* Terminate correctly the script... Crazy function name heh ?
	* @noparam
	****/
	private function youmadbro()
	{
		garbage_collection();
		exit_handler();
	}

	/****
	* trigger_error()
	* Our own trigger_error() depending the mode we are: normal or Ajax
	* @param string $message Message to show up to the user
	* @param int(const) $e_level Error Level. E_USER_NOTICE/E_USER_WARNING/E_USER_ERROR
	****/
	public function trigger_error($message, $e_level = E_USER_NOTICE, $redirect = false, $continue = true)
	{
		if ($this->is_ajax)
		{
			switch ($e_level)
			{
				case E_USER_NOTICE:
				case E_USER_WARNING:
					$this->ajax_error($this->user->lang['INFORMATION'], $message, $redirect, $continue);
				break;

				case E_USER_ERROR:
					$this->ajax_error($this->user->lang['INFORMATION'], $message, $redirect, false);
				break;
			}
		}
		else
		{
			trigger_error($message, $e_level);
		}
	}

	/****************************
	***************************
	*
	*	Ajax root MCP function.
	*	Major part of these function were borrowed from mcp.php, mcp_main.php, mcp_topic.php etc...
	*	Modified according MMS needing: Logs/Class method incrementation etc...
	*
	***************************
	****************************/

	/****
	* check_ids()
	* A very simplified function to check row ID (topics/posts) and sort allowed row action depending FORUM permission (final permission check)
	* Because the Mass-tool can be used simultaneously in multiple forum here we sort allowed topic/post deletion/moving/etc....
	* @param array $ids IDs we're working
	* @param string(const) $table SQL table we're working
	* @param string $collumn SQL collumn we're working
	* @param string $title_collumn SQL collumn title we're working
	* @param string $acl ACL used for sort (Un)allowed topics/posts
	* @param bool $full Grab all collumn of the SQL table if needed such Forums/Users tables.
	****/
	private function check_ids($ids, $table, $collumn, $title_collumn, $acl, $full = false)
	{
		$rows = array($this::MMS_IGNORED => array(), $this::MMS_PASSED => array());
		if (!$full)
		{
			$sql = "SELECT " . $this->db->sql_escape($collumn) . ", " . $this->db->sql_escape($title_collumn) . ", forum_id" . ($table == POSTS_TABLE ? ', topic_id, post_username, poster_id, post_edit_locked' : ', topic_first_poster_name, topic_first_post_id, topic_poster, topic_status') . "
				FROM $table
				WHERE " . $this->db->sql_in_set($collumn, $ids);
			$result = $this->db->sql_query($sql);
		}
		else
		{
			$sql_array = array(
				'SELECT'	=> 'p.*, u.*, t.*, f.*',

				'FROM'		=> array(
					USERS_TABLE		=> 'u',
					POSTS_TABLE		=> 'p',
					TOPICS_TABLE	=> 't',
				),

				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(FORUMS_TABLE => 'f'),
						'ON'	=> 'f.forum_id = t.forum_id'
					)
				),

				'WHERE'		=> $this->db->sql_in_set(($table == POSTS_TABLE ? 'p.post_id' : 't.topic_id'), $ids) . '
					AND ' . ($table == POSTS_TABLE ? 'u.user_id = p.poster_id' : 'u.user_id = t.topic_poster') . '
					AND ' . ($table == POSTS_TABLE ? 't.topic_id = p.topic_id' : 'p.topic_id = t.topic_id'),
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
		}

		while ($row = $this->db->sql_fetchrow($result))
		{
			//For each action the user need to have a valid access for the forum ID...
			if ($this->auth->acl_gets('f_list', 'f_read', $row['forum_id']) && $this->auth->acl_get($acl, $row['forum_id']))
			{
				if (!empty($this->to_fid) && ($row['forum_id'] == $this->to_fid) && $acl == 'm_move')
				{
					$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_SAME_FORUM'];
				}
				else if (!empty($this->to_tid) && ($row['topic_id'] == $this->to_tid) && ($acl == 'm_move' || $acl == 'm_merge'))
				{
					$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_SAME_TOPIC'];
				}
				else if ($this->row_mode == 'post' && (($this->{'mms_' . $this->row_mode . '_action'} == 'lock' && $row['post_edit_locked'] == ITEM_LOCKED) || ($this->{'mms_' . $this->row_mode . '_action'} == 'unlock' && $row['post_edit_locked'] == ITEM_UNLOCKED)))
				{
					$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_POST_ALREADY_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}) . 'ED'];
				}
				else if ($this->row_mode == 'post' && ($this->{'mms_' . $this->row_mode . '_action'} == 'chgposter' && $row['poster_id'] == $this->to_uid['user_id']))
				{
					$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_SAME_USERNAME'];
				}
				else if ($this->row_mode == 'topic' && (($this->{'mms_' . $this->row_mode . '_action'} == 'lock' && $row['topic_status'] == ITEM_LOCKED) || ($this->{'mms_' . $this->row_mode . '_action'} == 'unlock' && $row['topic_status'] == ITEM_UNLOCKED)))
				{
					$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_TOPIC_ALREADY_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}) . 'ED'];
				}
				else
				{
					if ($full)
					{
						$this->row_full[$row[$collumn]] = $row;
					}
					$rows[$this::MMS_PASSED][$row[$collumn]] = $row[$collumn];
					//Prepare some data for the final resync
					$this->fdata['t'][] = (int)  $row['topic_id'];
					$this->fdata['f'][] = (int)  $row['forum_id'];
					$this->fdata['u'][] = (int)  ($table == POSTS_TABLE ? $row['poster_id'] : $row['topic_poster']);
				}
			}
			else if (($this->auth->acl_gets('f_list', 'f_read', $row['forum_id']) && !$this->auth->acl_get($acl, $row['forum_id'])))
			{
				$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_NO_MPERMISSION'];
			}
			else
			{
				$rows[$this::MMS_IGNORED][$row[$collumn]] = $this->user->lang['MMS_NO_FPERMISSION'];
			}

			$this->uids[$row[$collumn]] = ($table == POSTS_TABLE ? $row['poster_id'] : $row['topic_poster']);
			$this->fids[$row[$collumn]] = $row['forum_id'];
			$this->tids[$row[$collumn]] = $row['topic_id'];
			$this->pids[$row[$collumn]] = ($table == TOPICS_TABLE ? $row['topic_first_post_id'] : $row['post_id']);
			$this->rids_title[$row[$collumn]] = $row[$title_collumn];
			$this->unms[$row[$collumn]] = ($table == POSTS_TABLE ? $row['post_username'] : $row['topic_first_poster_name']);
		}
		$this->db->sql_freeresult($result);

		//remove possible duplicate (array_unique() doesn't support multi dimensional arrays)
		$this->fdata['t'] = array_unique($this->fdata['t']);
		$this->fdata['f'] = array_unique($this->fdata['f']);
		$this->fdata['u'] = array_unique($this->fdata['u']);

		$ignored_keys = array();
		foreach ($rows[$this::MMS_IGNORED] AS $key => $val)
		{
			$ignored_keys[$key] = $key;
		}
		$missing_rows = array_diff($ids, array_merge($ignored_keys, $rows[$this::MMS_PASSED]));
		foreach ($missing_rows AS $missing_rows_)
		{
			$rows[$this::MMS_IGNORED][$missing_rows_] = $this->user->lang['MMS_' . strtoupper($this->row_mode) . '_DELETED'];
		}

		return $rows;
	}

	/****
	* adv_check_ids()
	* Original phpBB function bit modified as MMS need.
	* @ref param array $ids IDs we're working
	* @param string(const) $table SQL table we're working
	* @param string $sql_id SQL collumn we're working
	* @param bool $acl_list Auth checking
	* @param bool $single_forum Single forum? :mrgreen:
	****/
	function adv_check_ids(&$ids, $table, $sql_id, $acl_list = false, $single_forum = false)
	{
		if (!is_array($ids) || empty($ids))
		{
			return false;
		}

		$sql = "SELECT $sql_id, forum_id FROM $table
			WHERE " . $this->db->sql_in_set($sql_id, $ids);
		$result = $this->db->sql_query($sql);

		$ids = array();
		$forum_id = false;

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($acl_list && $row['forum_id'] && !$this->auth->acl_gets($acl_list, $row['forum_id']))
			{
				continue;
			}

			if ($acl_list && !$row['forum_id'] && !$this->auth->acl_getf_global($acl_list))
			{
				continue;
			}

			// Limit forum? If not, just assign the id.
			if ($single_forum === false)
			{
				$ids[] = $row[$sql_id];
				continue;
			}

			// Limit forum to a specific forum id?
			// This can get really tricky, because we do not want to create a failure on global topics. :)
			if ($row['forum_id'])
			{
				if ($single_forum !== true && $row['forum_id'] == (int) $single_forum)
				{
					$forum_id = (int) $single_forum;
				}
				else if ($forum_id === false)
				{
					$forum_id = $row['forum_id'];
				}

				if ($row['forum_id'] == $forum_id)
				{
					$ids[] = $row[$sql_id];
				}
			}
			else
			{
				// Always add a global topic
				$ids[] = $row[$sql_id];
			}
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($ids))
		{
			return false;
		}

		// If forum id is false and ids populated we may have only global announcements selected (returning 0 because of (int) $forum_id)

		return ($single_forum === false) ? true : (int) $forum_id;
	}

	/****
	* mcp_move_topic()
	* Move topics
	* @ref param array $ids IDs we're working
	* @param int $topic_Topic id to move
	* @param bool $shadow let shadow in source forum?
	* @param array $forum_data Forum datas.
	****/
	function mcp_move_topic($topic_id, $shadow, $forum_data)
	{
		// Here we limit the operation to one forum only
		$forum_id = $this->adv_check_ids($topic_id, TOPICS_TABLE, 'topic_id', array('m_move'), true);

		$topic_data = $this->get_full_topic_data($topic_id);

		$forum_sync_data = array();

		$forum_sync_data[$forum_id] = current($topic_data);
		$forum_sync_data[$this->to_fid] = $forum_data;

		// Real topics added to target forum
		$topics_moved = sizeof($topic_data);

		// Approved topics added to target forum
		$topics_authed_moved = $this::MMS_DB_FALSE;

		// Posts (topic replies + topic post if approved) added to target forum
		$topic_posts_added = $this::MMS_DB_FALSE;

		// Posts (topic replies + topic post if approved and not global announcement) removed from source forum
		$topic_posts_removed = $this::MMS_DB_FALSE;

		// Real topics removed from source forum (all topics without global announcements)
		$topics_removed = $this::MMS_DB_FALSE;

		// Approved topics removed from source forum (except global announcements)
		$topics_authed_removed = $this::MMS_DB_FALSE;

		foreach ($topic_data AS $topic_id => $topic_info)
		{
			if ($topic_info['topic_approved'])
			{
				$topics_authed_moved++;
				$topic_posts_added++;
			}

			$topic_posts_added += $topic_info['topic_replies'];

			if ($topic_info['topic_type'] != POST_GLOBAL)
			{
				$topics_removed++;
				$topic_posts_removed += $topic_info['topic_replies'];

				if ($topic_info['topic_approved'])
				{
					$topics_authed_removed++;
					$topic_posts_removed++;
				}
			}
		}

		$this->db->sql_transaction('begin');

		$sync_sql = array();

		if ($topic_posts_added)
		{
			$sync_sql[$this->to_fid][] = 'forum_posts = forum_posts + ' . $topic_posts_added;
		}

		if ($topics_authed_moved)
		{
			$sync_sql[$this->to_fid][] = 'forum_topics = forum_topics + ' . (int) $topics_authed_moved;
		}

		$sync_sql[$this->to_fid][] = 'forum_topics_real = forum_topics_real + ' . (int) $topics_moved;

		// Move topics, but do not resync yet
		move_topics($topic_id, $this->to_fid, false);

		$forum_ids = array($this->to_fid);
		foreach ($topic_data AS $topic_id => $row)
		{
			// Get the list of forums to resync, add a log entry
			$forum_ids[] = $row['forum_id'];
			add_log('mod', $this->to_fid, $topic_id, 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $row['forum_name'], $forum_data['forum_name']);

			$viewforum_url = append_sid("{$this->phpbb_root_path}viewforum.$this->phpEx", 'f=' . $this->to_fid . '#page-body');
			$this->row_msg_ary[$topic_id] = $this->user->lang('MMS_STATUS_MOVED', '<a class="mms_wo" href="' . $this->build_mms_ipreview_url($viewforum_url, $forum_data['forum_name']) . '" title="' . $forum_data['forum_name'] . '">' . $forum_data['forum_name'] . '</a>');
			$this->row_statut_ary[$topic_id] = true;

			// If we have moved a global announcement, we need to correct the topic type
			if ($row['topic_type'] == POST_GLOBAL)
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_type = ' . POST_ANNOUNCE . '
					WHERE topic_id = ' . (int) $row['topic_id'];
				$this->db->sql_query($sql);
			}

			// Leave a redirection if required and only if the topic is visible to users
			if ($shadow && $row['topic_approved'] && $row['topic_type'] != POST_GLOBAL)
			{
				$shadow = array(
					'forum_id'				=>	(int) $row['forum_id'],
					'icon_id'				=>	(int) $row['icon_id'],
					'topic_attachment'		=>	(int) $row['topic_attachment'],
					'topic_approved'		=>	$this::MMS_DB_TRUE, // a shadow topic is always approved
					'topic_reported'		=>	$this::MMS_DB_FALSE, // a shadow topic is never reported
					'topic_title'			=>	(string) $row['topic_title'],
					'topic_poster'			=>	(int) $row['topic_poster'],
					'topic_time'			=>	(int) $row['topic_time'],
					'topic_time_limit'		=>	(int) $row['topic_time_limit'],
					'topic_views'			=>	(int) $row['topic_views'],
					'topic_replies'			=>	(int) $row['topic_replies'],
					'topic_replies_real'	=>	(int) $row['topic_replies_real'],
					'topic_status'			=>	ITEM_MOVED,
					'topic_type'			=>	POST_NORMAL,
					'topic_first_post_id'	=>	(int) $row['topic_first_post_id'],
					'topic_first_poster_colour'=>(string) $row['topic_first_poster_colour'],
					'topic_first_poster_name'=>	(string) $row['topic_first_poster_name'],
					'topic_last_post_id'	=>	(int) $row['topic_last_post_id'],
					'topic_last_poster_id'	=>	(int) $row['topic_last_poster_id'],
					'topic_last_poster_colour'=>(string) $row['topic_last_poster_colour'],
					'topic_last_poster_name'=>	(string) $row['topic_last_poster_name'],
					'topic_last_post_subject'=>	(string)  $row['topic_last_post_subject'],
					'topic_last_post_time'	=>	(int) $row['topic_last_post_time'],
					'topic_last_view_time'	=>	(int) $row['topic_last_view_time'],
					'topic_moved_id'		=>	(int) $row['topic_id'],
					'topic_bumped'			=>	(int) $row['topic_bumped'],
					'topic_bumper'			=>	(int) $row['topic_bumper'],
					'poll_title'			=>	(string) $row['poll_title'],
					'poll_start'			=>	(int) $row['poll_start'],
					'poll_length'			=>	(int) $row['poll_length'],
					'poll_max_options'		=>	(int) $row['poll_max_options'],
					'poll_last_vote'		=>	(int) $row['poll_last_vote']
				);

				$this->db->sql_query('INSERT INTO ' . TOPICS_TABLE . $this->db->sql_build_array('INSERT', $shadow));

				// Shadow topics only count on new "topics" and not posts... a shadow topic alone has 0 posts
				$topics_removed--;
				$topics_authed_removed--;
			}
		}
		unset($topic_data);

		if ($topic_posts_removed)
		{
			$sync_sql[$forum_id][] = 'forum_posts = forum_posts - ' . $topic_posts_removed;
		}

		if ($topics_removed)
		{
			$sync_sql[$forum_id][]	= 'forum_topics_real = forum_topics_real - ' . (int) $topics_removed;
		}

		if ($topics_authed_removed)
		{
			$sync_sql[$forum_id][]	= 'forum_topics = forum_topics - ' . (int) $topics_authed_removed;
		}

		foreach ($sync_sql AS $forum_id_key => $array)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . implode(', ', $array) . '
				WHERE forum_id = ' . (int) $forum_id_key;
			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');

		sync('forum', 'forum_id', array($forum_id, $this->to_fid), false, true);
	}

	/****
	* mcp_fork_topic()
	* Fork topics
	* @param array $topic_ids id to fork
	* @param int $to_forum_id Destination forum
	****/
	private function mcp_fork_topic($topic_ids, $to_forum_id)
	{
		$counter = array();
		if($topic_ids && !is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}
		if ($to_forum_id)
		{
			$forum_data = $this->get_forum_data($to_forum_id);

			if (!sizeof($topic_ids))
			{
				$this->trigger_error($this->user->lang['NO_TOPIC_SELECTED'], E_USER_NOTICE, false);
			}
			else if (!sizeof($forum_data))
			{
				$this->trigger_error($this->user->lang['FORUM_NOT_EXIST'], E_USER_NOTICE, false);
			}
			else
			{
				$forum_data = $forum_data[$to_forum_id];

				if ($forum_data['forum_type'] != FORUM_POST)
				{
					$this->trigger_error($this->user->lang['FORUM_NOT_POSTABLE'], E_USER_NOTICE, false);
				}
				else if (!$this->auth->acl_get('f_post', $to_forum_id))
				{
					$this->trigger_error($this->user->lang['USER_CANNOT_POST'], E_USER_NOTICE, false);
				}
			}
		}

		$topic_data = $this->get_full_topic_data($topic_ids, 'f_post');

		$total_posts = $this::MMS_DB_FALSE;
		$new_topic_id_list = array();

		foreach ($topic_data AS $topic_id => $topic_row)
		{
			if (!isset($search_type) && $topic_row['enable_indexing'])
			{
				// Select the search method and do some additional checks to ensure it can actually be utilised
				$search_type = basename($this->config['search_type']);

				if (!file_exists($this->phpbb_root_path . 'includes/search/' . $search_type . '.' . $this->phpEx))
				{
					$this->trigger_error($this->user->lang['NO_SUCH_SEARCH_MODULE'], E_USER_ERROR, false);
				}

				if (!class_exists($search_type))
				{
					//I'm just trolled by this %$$%@## include...
					$phpbb_root_path = $this->phpbb_root_path;
					$phpEx = $this->phpEx;
					include($this->phpbb_root_path . "includes/search/$search_type." . $this->phpEx);
				}

				$error = false;
				$search = new $search_type($error);
				$search_mode = 'post';

				if ($error)
				{
					$this->trigger_error($error, E_USER_ERROR, false);
				}
			}
			else if (!isset($search_type) && !$topic_row['enable_indexing'])
			{
				$search_type = false;
			}

			$sql_ary = array(
				'forum_id'					=> (int) $to_forum_id,
				'icon_id'					=> (int) $topic_row['icon_id'],
				'topic_attachment'			=> (int) $topic_row['topic_attachment'],
				'topic_approved'			=> $this::MMS_DB_TRUE,
				'topic_reported'			=> $this::MMS_DB_FALSE,
				'topic_title'				=> (string) $topic_row['topic_title'],
				'topic_poster'				=> (int) $topic_row['topic_poster'],
				'topic_time'				=> (int) $topic_row['topic_time'],
				'topic_replies'				=> (int) $topic_row['topic_replies_real'],
				'topic_replies_real'		=> (int) $topic_row['topic_replies_real'],
				'topic_status'				=> (int) $topic_row['topic_status'],
				'topic_type'				=> (int) $topic_row['topic_type'],
				'topic_first_poster_name'	=> (string) $topic_row['topic_first_poster_name'],
				'topic_last_poster_id'		=> (int) $topic_row['topic_last_poster_id'],
				'topic_last_poster_name'	=> (string) $topic_row['topic_last_poster_name'],
				'topic_last_post_time'		=> (int) $topic_row['topic_last_post_time'],
				'topic_last_view_time'		=> (int) $topic_row['topic_last_view_time'],
				'topic_bumped'				=> (int) $topic_row['topic_bumped'],
				'topic_bumper'				=> (int) $topic_row['topic_bumper'],
				'poll_title'				=> (string) $topic_row['poll_title'],
				'poll_start'				=> (int) $topic_row['poll_start'],
				'poll_length'				=> (int) $topic_row['poll_length'],
				'poll_max_options'			=> (int) $topic_row['poll_max_options'],
				'poll_vote_change'			=> (int) $topic_row['poll_vote_change'],
			);

			$this->db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
			$new_topic_id = $this->db->sql_nextid();

			//Special treatment for this mode:
			$this->fdata['t'][] = (int)  $new_topic_id;

			$new_topic_id_list[$topic_id] = $new_topic_id;
			$viewtopic_url = append_sid("{$this->phpbb_root_path}viewtopic.$this->phpEx", 't=' . $new_topic_id . '#page-body');
			$viewforum_url = append_sid("{$this->phpbb_root_path}viewforum.$this->phpEx", 'f=' . $to_forum_id . '#page-body');

			$this->row_msg_ary[$topic_id] = $this->user->lang('MMS_STATUS_FORKED', '<a class="mms_wo" href="' . $this->build_mms_ipreview_url($viewforum_url, $topic_row['topic_title']) . '" title="' . $topic_row['topic_title'] . '">' . $forum_data['forum_name'] . '</a>', '<a class="mms_wo" href="' . $this->build_mms_ipreview_url($viewtopic_url, $topic_row['topic_title']) . '" title="' . $topic_row['topic_title'] . '">' . $new_topic_id . '</a>');
			$this->row_statut_ary[$topic_id] = true;
			if ($topic_row['poll_start'])
			{
				$poll_rows = array();

				$sql = 'SELECT *
					FROM ' . POLL_OPTIONS_TABLE . "
					WHERE topic_id = " . (int) $topic_id;
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_ary = array(
						'poll_option_id'	=> (int) $row['poll_option_id'],
						'topic_id'			=> (int) $new_topic_id,
						'poll_option_text'	=> (string) $row['poll_option_text'],
						'poll_option_total'	=> $this::MMS_DB_FALSE
					);

					$this->db->sql_query('INSERT INTO ' . POLL_OPTIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
				}
			}

			$sql = 'SELECT *
				FROM ' . POSTS_TABLE . "
				WHERE topic_id = $topic_id
				ORDER BY post_time ASC";
			$result = $this->db->sql_query($sql);

			$post_rows = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_rows[] = $row;
			}
			$this->db->sql_freeresult($result);

			if (!sizeof($post_rows))
			{
				continue;
			}

			$total_posts += sizeof($post_rows);
			foreach ($post_rows AS $row)
			{
				$sql_ary = array(
					'topic_id'			=> (int) $new_topic_id,
					'forum_id'			=> (int) $to_forum_id,
					'poster_id'			=> (int) $row['poster_id'],
					'icon_id'			=> (int) $row['icon_id'],
					'poster_ip'			=> (string) $row['poster_ip'],
					'post_time'			=> (int) $row['post_time'],
					'post_approved'		=> $this::MMS_DB_TRUE,
					'post_reported'		=> $this::MMS_DB_FALSE,
					'enable_bbcode'		=> (int) $row['enable_bbcode'],
					'enable_smilies'	=> (int) $row['enable_smilies'],
					'enable_magic_url'	=> (int) $row['enable_magic_url'],
					'enable_sig'		=> (int) $row['enable_sig'],
					'post_username'		=> (string) $row['post_username'],
					'post_subject'		=> (string) $row['post_subject'],
					'post_text'			=> (string) $row['post_text'],
					'post_edit_reason'	=> (string) $row['post_edit_reason'],
					'post_edit_user'	=> (int) $row['post_edit_user'],
					'post_checksum'		=> (string) $row['post_checksum'],
					'post_attachment'	=> (int) $row['post_attachment'],
					'bbcode_bitfield'	=> $row['bbcode_bitfield'],
					'bbcode_uid'		=> (string) $row['bbcode_uid'],
					'post_edit_time'	=> (int) $row['post_edit_time'],
					'post_edit_count'	=> (int) $row['post_edit_count'],
					'post_edit_locked'	=> (int) $row['post_edit_locked'],
					'post_postcount'	=> $row['post_postcount'],
				);
				// Adjust post counts... only if the post can be incremented to the user counter (else, it was not added the users post count anyway)
				//Fixed an error of phpBB: http://tracker.phpbb.com/browse/PHPBB3-11520
				//Do not do the query here but later, we just increment the count of posts until the loop is finished, then do new posts counters.
				if ($row['post_postcount'])
				{
					isset($counter[$row['poster_id']]) ? $counter[$row['poster_id']]++ : $counter[$row['poster_id']] = 1;
				}
				$this->db->sql_query('INSERT INTO ' . POSTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
				$new_post_id = $this->db->sql_nextid();

				// Copy whether the topic is dotted
				markread('post', $to_forum_id, $new_topic_id, 0, $row['poster_id']);

				if (!empty($search_type))
				{
					$search->index($search_mode, $new_post_id, $sql_ary['post_text'], $sql_ary['post_subject'], $sql_ary['poster_id'], ($topic_row['topic_type'] == POST_GLOBAL) ? 0 : $to_forum_id);
					$search_mode = 'reply'; // After one we index replies
				}

				// Copy Attachments
				if ($row['post_attachment'])
				{
					$sql = 'SELECT * FROM ' . ATTACHMENTS_TABLE . "
						WHERE post_msg_id = {$row['post_id']}
							AND topic_id = $topic_id
							AND in_message = " . $this::MMS_DB_FALSE;
					$result = $this->db->sql_query($sql);

					$sql_ary = array();
					while ($attach_row = $this->db->sql_fetchrow($result))
					{
						$sql_ary[] = array(
							'post_msg_id'		=> (int) $new_post_id,
							'topic_id'			=> (int) $new_topic_id,
							'in_message'		=> $this::MMS_DB_FALSE,
							'is_orphan'			=> (int) $attach_row['is_orphan'],
							'poster_id'			=> (int) $attach_row['poster_id'],
							'physical_filename'	=> (string) utf8_basename($attach_row['physical_filename']),
							'real_filename'		=> (string) utf8_basename($attach_row['real_filename']),
							'download_count'	=> (int) $attach_row['download_count'],
							'attach_comment'	=> (string) $attach_row['attach_comment'],
							'extension'			=> (string) $attach_row['extension'],
							'mimetype'			=> (string) $attach_row['mimetype'],
							'filesize'			=> (int) $attach_row['filesize'],
							'filetime'			=> (int) $attach_row['filetime'],
							'thumbnail'			=> (int) $attach_row['thumbnail']
						);
					}
					$this->db->sql_freeresult($result);

					if (sizeof($sql_ary))
					{
						$this->db->sql_multi_insert(ATTACHMENTS_TABLE, $sql_ary);
					}
				}
			}

			$sql = 'SELECT user_id, notify_status
				FROM ' . TOPICS_WATCH_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $this->db->sql_query($sql);

			$sql_ary = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_ary[] = array(
					'topic_id'		=> (int) $new_topic_id,
					'user_id'		=> (int) $row['user_id'],
					'notify_status'	=> (int) $row['notify_status'],
				);
			}
			$this->db->sql_freeresult($result);

			if (sizeof($sql_ary))
			{
				$this->db->sql_multi_insert(TOPICS_WATCH_TABLE, $sql_ary);
			}
			add_log('mod', $to_forum_id, $new_topic_id, 'MMS_LOG_' . strtoupper($this->row_mode) . '_' . strtoupper($this->{'mms_' . $this->row_mode . '_action'}), $topic_row['forum_name']);
		}
		// Sync new topics, parent forums and board stats
		sync('topic', 'topic_id', $new_topic_id_list);

		$sync_sql = array();

		$sync_sql[$to_forum_id][]	= 'forum_posts = forum_posts + ' . (int) $total_posts;
		$sync_sql[$to_forum_id][]	= 'forum_topics = forum_topics + ' . (int) sizeof($new_topic_id_list);
		$sync_sql[$to_forum_id][]	= 'forum_topics_real = forum_topics_real + ' . (int) sizeof($new_topic_id_list);

		if (sizeof($counter))
		{
			//Do only one query per user and not a query PER post!!
			foreach ($counter AS $uid => $count)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + ' . (int) $count . '
					WHERE user_id = ' . (int) $uid;
				$this->db->sql_query($sql);
			}
		}
		foreach ($sync_sql AS $forum_id_key => $array)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . implode(', ', $array) . '
				WHERE forum_id = ' . (int) $forum_id_key;
			$this->db->sql_query($sql);
		}

		sync('forum', 'forum_id', $to_forum_id, false, true);
		set_config_count('num_topics', sizeof($new_topic_id_list), true);
		set_config_count('num_posts', $total_posts, true);
	}

	/****
	* mcp_merge_topic()
	* Merge selected topics into selected topic
	* @param array $topic_ids topics IDs list awaiting to be merged
	* @param int $to_topic_id topic ID to merge
	****/
	function mcp_merge_topic($topic_ids, $to_topic_id)
	{
		$topic_data = $this->get_topic_data(array($to_topic_id), 'm_merge');

		if (!sizeof($topic_data))
		{
			$this->trigger_error($this->user->lang['NO_FINAL_TOPIC_SELECTED'], E_USER_NOTICE, false, true);
		}

		$topic_data = $topic_data[$to_topic_id];
		$post_id_list	= array();

		$sql = 'SELECT post_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id_list[] = $row['post_id'];
		}
		$this->db->sql_freeresult($result);
		move_posts($post_id_list, $to_topic_id);

		// If the topic no longer exist, we will update the topic watch table.
		// To not let it error out on users watching both topics, we just return on an error...
		$this->db->sql_return_on_error(true);
		$this->db->sql_query('UPDATE ' . TOPICS_WATCH_TABLE . ' SET topic_id = ' . (int) $to_topic_id . ' WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids));
		$this->db->sql_return_on_error(false);

		$this->db->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . ' WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids));
	}

	/****
	* mcp_post_options()
	* Do some changes in specified posts
	* @param array $rows posts ID to change
	****/
	public function mcp_post_options(&$rows)
	{
		switch($this->post_option)
		{
			case 'disable_sig':
			case 'enable_sig':
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $value_;
					}
					{
						$rows_passed[$key_] = $value_;
					}
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'enable_sig' => ($this->post_option == 'enable_sig') ? $this::MMS_DB_TRUE : $this::MMS_DB_FALSE,
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);
			break;

			case 'disable_links':
			case 'enable_links':
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				if (!class_exists('parse_message'))
				{
					//B**** please :roll:
					$phpbb_root_path = $this->phpbb_root_path;
					$phpEx = $this->phpEx;
					include($this->phpbb_root_path . 'includes/message_parser.' . $this->phpEx);
				}
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $value_;
					}
					{
						$rows_passed[$key_] = $value_;
					}
					$text = html_entity_decode(utf8_normalize_nfc($this->row_full[$key_]['post_text']), ENT_COMPAT | ENT_HTML401, 'UTF-8');
					$message = generate_text_for_edit($text, $this->row_full[$key_]['bbcode_uid'], '');
					$mms_parser = new parse_message();
					$mms_parser->message = $message['text'];

					if (isset($this->row_full[$key_]['bbcode_uid']) && $this->row_full[$key_]['bbcode_uid'] > 0)
					{
						$mms_parser->bbcode_uid = $this->row_full[$key_]['bbcode_uid'];
					}
					$mms_parser->parse($this->row_full[$key_]['enable_bbcode'], (($this->post_option == 'enable_links') ? true : false), $this->row_full[$key_]['enable_smilies']);
					// insert info into the sql_ary
					$uid = $mms_parser->bbcode_uid;
					$bitfield = $mms_parser->bbcode_bitfield;
					//Don't blame me about sql query in loop: remember MMS_AJAX_PACKETS const !!
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE',
								array(
									'post_text'			=> $mms_parser->message,
									'post_checksum'		=> md5($mms_parser->message),
									'bbcode_bitfield'	=> is_null($bitfield) ? '' : $bitfield,
									'bbcode_uid'		=> is_null($uid) ? '' : $uid,
								)
							) . ' WHERE post_id = ' . (int) $key_;
					$this->db->sql_query($sql);
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'enable_magic_url' => ($this->post_option == 'enable_links') ? $this::MMS_DB_TRUE : $this::MMS_DB_FALSE,
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);
			break;

			case 'disable_bbcodes':
			case 'enable_bbcodes':
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				if (!class_exists('parse_message'))
				{
					//nub more please :roll:
					$phpbb_root_path = $this->phpbb_root_path;
					$phpEx = $this->phpEx;
					include($this->phpbb_root_path . 'includes/message_parser.' . $this->phpEx);
				}
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					$bbcode_status	= ($this->config['allow_bbcode'] && $this->post_option == 'enable_bbcodes' &&($this->auth->acl_get('f_bbcode', $this->row_full[$value_]['forum_id']) || $this->row_full[$value_]['forum_id'] == 0)) ? true : false;
					$smilies_status	= ($this->config['allow_smilies'] && $this->auth->acl_get('f_smilies', $this->row_full[$value_]['forum_id'])) ? true : false;
					$img_status		= ($bbcode_status && $this->auth->acl_get('f_img', $this->row_full[$value_]['forum_id'])) ? true : false;
					$url_status		= ($this->config['allow_post_links'] && $this->row_full[$key_]['enable_magic_url'] ) ? true : false;
					$flash_status	= ($bbcode_status && $this->auth->acl_get('f_flash', $this->row_full[$value_]['forum_id']) && $this->config['allow_post_flash']) ? true : false;
					$quote_status	= ($this->auth->acl_get('f_reply', $this->row_full[$value_]['forum_id'])) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $value_;
					}
					else
					{
						$rows_passed[$key_] = $value_;
					}
					$text = html_entity_decode(utf8_normalize_nfc($this->row_full[$key_]['post_text']), ENT_COMPAT | ENT_XHTML, 'UTF-8');
					$message = generate_text_for_edit($text, $this->row_full[$key_]['bbcode_uid'], '');
					$mms_parser = new parse_message();
					$mms_parser->message = $message['text'];

					if (isset($this->row_full[$key_]['bbcode_uid']) && $this->row_full[$key_]['bbcode_uid'] > 0)
					{
						$mms_parser->bbcode_uid = $this->row_full[$key_]['bbcode_uid'];
					}
					$mms_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $this->config['allow_post_links'], true);
					// insert info into the sql_ary
					$uid = $mms_parser->bbcode_uid;
					$bitfield = $mms_parser->bbcode_bitfield;

					//Don't blame me about sql query in loop: remember MMS_AJAX_PACKETS const !!
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE',
								array(
									'post_text'			=> $mms_parser->message,
									'post_checksum'		=> md5($mms_parser->message),
									'bbcode_bitfield'	=> is_null($bitfield) ? '' : $bitfield,
									'bbcode_uid'		=> is_null($uid) ? '' : $uid,
								)
							) . ' WHERE post_id = ' . (int) $key_;
					$this->db->sql_query($sql);
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'enable_bbcode ' => ($this->post_option == 'enable_bbcodes') ? $this::MMS_DB_TRUE : $this::MMS_DB_FALSE,
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);
			break;

			case 'disable_smilies':
			case 'enable_smilies':
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				if (!class_exists('parse_message'))
				{
					//nub more please :roll:
					$phpbb_root_path = $this->phpbb_root_path;
					$phpEx = $this->phpEx;
					include($this->phpbb_root_path . 'includes/message_parser.' . $this->phpEx);
				}
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					$bbcode_status	= ($this->config['allow_bbcode'] && ($this->auth->acl_get('f_bbcode', $this->row_full[$value_]['forum_id']) || $this->row_full[$value_]['forum_id'] == 0)) ? true : false;
					$smilies_status	= ($this->config['allow_smilies'] && $this->post_option == 'enable_smilies' && $this->auth->acl_get('f_smilies', $this->row_full[$value_]['forum_id'])) ? true : false;
					$img_status		= ($bbcode_status && $this->auth->acl_get('f_img', $this->row_full[$value_]['forum_id'])) ? true : false;
					$url_status		= ($this->config['allow_post_links'] && $this->row_full[$key_]['enable_magic_url'] ) ? true : false;
					$flash_status	= ($bbcode_status && $this->auth->acl_get('f_flash', $this->row_full[$value_]['forum_id']) && $this->config['allow_post_flash']) ? true : false;
					$quote_status	= ($bbcode_status && $this->auth->acl_get('f_reply', $this->row_full[$value_]['forum_id'])) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $value_;
					}
					{
						$rows_passed[$key_] = $value_;
					}
					$text = html_entity_decode(utf8_normalize_nfc($this->row_full[$key_]['post_text']), ENT_COMPAT | ENT_HTML401, 'UTF-8');
					$message = generate_text_for_edit($text, $this->row_full[$key_]['bbcode_uid'], '');
					$mms_parser = new parse_message();
					$mms_parser->message = &$message['text'];

					if (isset($this->row_full[$key_]['bbcode_uid']) && $this->row_full[$key_]['bbcode_uid'] > 0)
					{
						$mms_parser->bbcode_uid = $this->row_full[$key_]['bbcode_uid'];
					}
					$mms_parser->parse($bbcode_status, $url_status, $smilies_status, $img_status, $flash_status, $quote_status, $this->config['allow_post_links'], true);
					// insert info into the sql_ary
					$uid = $mms_parser->bbcode_uid;
					$bitfield = $mms_parser->bbcode_bitfield;
					//Don't blame me about sql query in loop: remember MMS_AJAX_PACKETS const !!
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE',
								array(
									'post_text'			=> $mms_parser->message,
									'post_checksum'		=> md5($mms_parser->message),
									'bbcode_bitfield'	=> is_null($bitfield) ? '' : $bitfield,
									'bbcode_uid'		=> is_null($uid) ? '' : $uid,
								)
							) . ' WHERE post_id = ' . (int) $key_;
					$this->db->sql_query($sql);
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'enable_smilies ' => ($this->post_option == 'enable_smilies') ? $this::MMS_DB_TRUE : $this::MMS_DB_FALSE,
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);
			break;

			case 'remove_attachment':
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				$rowset = $this->get_post_data($rows[$this::MMS_PASSED]);
				//(Borrowed from message_parser.php)
				if (!function_exists('delete_attachments'))
				{
					include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
				}
				delete_attachments('post', $rows[$this::MMS_PASSED], true);//Do the resync now!
				$this->db->sql_transaction('begin');
				foreach($rowset AS $key_ => $value_)
				{
					if ($this->row_full[$key_]['post_attachment'])
					{
						//Remove each "attachment BBcodes" from posts
						$post_text = utf8_normalize_nfc(preg_replace('#\[attachment=([0-9]+):([a-zA-Z0-9]+)\](.*?)\[\/attachment:([a-zA-Z0-9]+)\]#e', '', $this->row_full[$key_]['post_text']));
						$sql_ary = array(
							'post_attachment'	=> $this::MMS_DB_FALSE,
							'post_text'			=> $post_text,
							'post_checksum'		=> md5($post_text)
						);
						if ($this->post_reason)
						{
							$sql_ary += array(
								'post_edit_time'	=> $this->time,
								'post_edit_user'	=> $this->user->data['user_id'],
								//'post_edit_count'	=> 'post_edit_count + 1', //*
								'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
							);
						}
						$sql = 'UPDATE ' . POSTS_TABLE . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE post_id = ' . (int) $this->row_full[$key_]['post_id'];
						if ($this->post_reason)
						{
							//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
							$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
						}
						$this->db->sql_query($sql);
						$rows_passed[$key_] = $this->row_full[$key_]['post_id'];
					}
					else
					{
						$rows_ignored[$key_] = $this->user->lang['MMS_POSTS_OPTIONS_ERROR'][strtoupper($this->post_option)];
					}
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$this->db->sql_transaction('commit');
			break;
			//-->
			//Addons
			//-->
			case 'disable_hpiv':
			case 'enable_hpiv':
				if (!$this->addons['hpiv'])
				{
					//Add-on missing, stop the script definitely!
					$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR, false, false);
				}
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $this->user->lang['MMS_NO_MPERMISSION'];
					}
					{
						$rows_passed[$key_] = $value_;
					}
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'post_profile' => ($this->post_option == 'enable_hpiv') ? $this::MMS_DB_TRUE : $this::MMS_DB_FALSE,
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);
			break;

			case 'remove_ppr':
				if (!$this->addons['ppr'])
				{
					//Add-on missing, stop the script definitely!
					$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR, false, false);
				}
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "DELETE" moderator permission are not respected
						$rows_ignored[$key_] = $value_;
					}
					{
						$rows_passed[$key_] = $value_;
					}
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;

				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				$this->db->sql_query($sql);

				if ($this->post_reason)
				{
					$sql_ary = array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}

				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);

				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
					$this->db->sql_query($sql);
				}
			break;

			case 'remove_mm':
				if (!$this->addons['mm'])
				{
					//Add-on missing, stop the script definitely!
					$this->trigger_error($this->user->lang['MMS_ADDON_DISABLED'], E_USER_ERROR, false, false);
				}
				$mm_topic_resync = array();
				$rows_passed = $rows[$this::MMS_PASSED];
				$rows_ignored = $rows[$this::MMS_IGNORED];
				foreach($rows[$this::MMS_PASSED] AS $key_ => $value_)
				{
					$is_me = ($this->row_full[$value_]['poster_id'] == $this->user->data['user_id']) ? true : false;
					if (!$this->check_post_options_acl($this->row_full[$value_]['forum_id'], $is_me))
					{
						//Move passed row into ignored row since the forum permission
						//and/or "EDIT" moderator permission are not respected
						$rows_ignored[$key_] = $this->user->lang['MMS_NO_MPERMISSION'];
					}
					{
						$rows_passed[$key_] = $value_;
						if (isset($mm_topic_resync[$this->row_full[$value_]['topic_id']]))
						{
							$mm_topic_resync[$this->row_full[$value_]['topic_id']] = 1;
						}
						else
						{
							$mm_topic_resync[$this->row_full[$value_]['topic_id']]++;
						}
					}
				}
				$rows[$this::MMS_PASSED] = $rows_passed;
				$rows[$this::MMS_IGNORED] += $rows_ignored;
				$sql_ary = array(
					'post_moderation' => '',
					'post_moderation_user_id' => 0,
					'post_moderation_username' => '',
					'post_moderation_user_colour' => '',
				);
				if ($this->post_reason)
				{
					$sql_ary += array(
						'post_edit_time'	=> $this->time,
						'post_edit_user'	=> $this->user->data['user_id'],
						//'post_edit_count'	=> 'post_edit_count + 1', //*
						'post_edit_reason'	=> substr($this->user->lang['MMS_POSTS_OPTIONS_REASON'][strtoupper($this->post_option)], 0, 254),
					);
				}
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . $this->db->sql_in_set('post_id', $rows[$this::MMS_PASSED]);
				if ($this->post_reason)
				{
					//* Will return an SQL Error: Incorrect integer value[...]. Using preg_replace() instead of str_replace() for "limit" param...
					$sql = preg_replace('#SET#', 'SET post_edit_count = post_edit_count + 1, ', $sql, 1);
				}
				$this->db->sql_query($sql);

				//Update subtracted Moderation Message counter per topic
				//This foreach will never reach 10 loop (Master packet-size setting)
				foreach($mm_topic_resync AS $topic_id_ => $sub_count_)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET posts_moderation_total = posts_moderation_total - ' . (int) $sub_count_ . '
						WHERE topic_id = ' . (int) $topic_id_ ;
					$this->db->sql_query($sql);
				}
		}

	}

	/****
	* get_forum_data()
	* Get simple forum data
	* @param array $forum_ids forums IDs to get
	****/
	private function get_forum_data($forum_ids)
	{
		$rows = array();

		if (!is_array($forum_ids))
		{
			$forum_ids = array($forum_ids);
		}

		if (!sizeof($forum_ids))
		{
			return array();
		}

		$sql = "SELECT *
			FROM " . FORUMS_TABLE . "
			WHERE " . $this->db->sql_in_set('forum_id', $forum_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[$row['forum_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/****
	* get_topic_data()
	* Get simple topic data
	* @param array $topic_ids topics IDs to get
	****/
	private function get_topic_data($topic_ids)
	{
		$rows = array();

		if (!is_array($topic_ids))
		{
			$topic_ids = array($topic_ids);
		}

		if (!sizeof($topic_ids))
		{
			return array();
		}

		$sql = "SELECT *
			FROM " . TOPICS_TABLE . "
			WHERE " . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[$row['topic_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/****
	* post_resync_username()
	* Do a final username resync
	* @noparam
	****/
	private function post_resync_username()
	{
		if (!empty($this->pids))
		{
			//Set hard-size limit
			$this->pids = array_slice($this->pids, 0, $this::MMS_HARD_RESYNC_LIMIT, true);

			$sql = 'UPDATE ' . POSTS_TABLE . " p
				LEFT JOIN " . USERS_TABLE . " u
					ON u.user_id = p.poster_id
				SET p.post_username = u.username
				WHERE " . $this->db->sql_in_set('p.post_id', $this->pids);
			$this->db->sql_query($sql);
		}
	}

	/****
	* change_poster()
	* Change post poster
	* @ref param array $post_info Full post info we're working
	* @param array $userdata future $post_info poster
	****/
	private function change_poster(&$post_info, $userdata)
	{
		$post_id = $post_info['post_id'];

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET poster_id = ' . (int) $userdata['user_id'] . '
			WHERE post_id = ' . (int) $post_id;
		$this->db->sql_query($sql);

		// Resync topic/forum if needed
		if ($post_info['topic_last_post_id'] == $post_id || $post_info['forum_last_post_id'] == $post_id || $post_info['topic_first_post_id'] == $post_id)
		{
			sync('topic', 'topic_id', $post_info['topic_id'], false, false);
			sync('forum', 'forum_id', $post_info['forum_id'], false, false);
		}

		// Adjust post counts... only if the post is approved (else, it was not added the users post count anyway)
		if ($post_info['post_postcount'] && $post_info['post_approved'])
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = user_posts - 1
				WHERE user_id = ' . (int) $post_info['user_id'] .'
				AND user_posts > ' . $this::MMS_DB_FALSE;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = user_posts + 1
				WHERE user_id = ' . (int) $userdata['user_id'];
			$this->db->sql_query($sql);
		}

		// Add posted to information for this topic for the new user
		markread('post', $post_info['forum_id'], $post_info['topic_id'], $this->time, $userdata['user_id']);

		// Remove the dotted topic option if the old user has no more posts within this topic
		if ($this->config['load_db_track'] && $post_info['user_id'] != ANONYMOUS)
		{
			$sql = 'SELECT topic_id
				FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . (int) $post_info['topic_id'] . '
					AND poster_id = ' . (int) $post_info['user_id'];
			$result = $this->db->sql_query_limit($sql, 1);
			$topic_id = (int) $this->db->sql_fetchfield('topic_id');
			$this->db->sql_freeresult($result);

			if (!$topic_id)
			{
				$sql = 'DELETE FROM ' . TOPICS_POSTED_TABLE . '
					WHERE user_id = ' . (int) $post_info['user_id'] . '
						AND topic_id = ' . (int) $post_info['topic_id'];
				$this->db->sql_query($sql);
			}
		}

		// change the poster_id within the attachments table, else the data becomes out of sync and errors displayed because of wrong ownership
		if ($post_info['post_attachment'])
		{
			$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
				SET poster_id = ' . (int) $userdata['user_id'] . '
				WHERE poster_id = ' . (int) $post_info['user_id'] . '
					AND post_msg_id = ' . (int) $post_info['post_id'] . '
					AND topic_id = ' . (int) $post_info['topic_id'];
			$this->db->sql_query($sql);
		}

		$from_username = $post_info['username'];
		$to_username = $userdata['username'];

		// Renew post info
		$post_info = $this->get_post_data(array($post_id), false, true);

		if (!sizeof($post_info))
		{
			$this->trigger_error('POST_NOT_EXIST');
		}

		$post_info = $post_info[$post_id];

	}

	/****
	* search_destroy_cache()
	* Destruct search's cache for target UID
	* @param array $uids User ID to destruct search's cache
	****/
	private function search_destroy_cache($uids)
	{
		// refresh search cache of this post
		$search_type = basename($this->config['search_type']);

		if (file_exists($this->phpbb_root_path . 'includes/search/' . $search_type . '.' . $this->phpEx))
		{
			//I'm just trolled by this %$$%@## include...
			$phpbb_root_path = $this->phpbb_root_path;
			$phpEx = $this->phpEx;
			require($this->phpbb_root_path . "includes/search/$search_type." . $this->phpEx);

			// We do some additional checks in the module to ensure it can actually be utilised
			$error = false;
			$search = new $search_type($error);

			if (!$error && method_exists($search, 'destroy_cache'))
			{
				$search->destroy_cache(array(), $uids);
			}
		}
	}

	/****
	* get_post_data()
	* Get simple post data
	* @param array $post_ids post IDs to get
	* @param array $acl_list ACLs list auth to check
	* @param bool $read_tracking Keep tracking?
	****/
	private function get_post_data($post_ids, $acl_list = false, $read_tracking = false)
	{
		$rowset = array();

		if (!sizeof($post_ids))
		{
			return array();
		}

		$sql_array = array(
			'SELECT'	=> 'p.*, u.*, t.*, f.*',

			'FROM'		=> array(
				USERS_TABLE		=> 'u',
				POSTS_TABLE		=> 'p',
				TOPICS_TABLE	=> 't',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(FORUMS_TABLE => 'f'),
					'ON'	=> 'f.forum_id = t.forum_id'
				)
			),

			'WHERE'		=> $this->db->sql_in_set('p.post_id', $post_ids) . '
				AND u.user_id = p.poster_id
				AND t.topic_id = p.topic_id',
		);

		if ($read_tracking && $this->config['load_db_lastread'])
		{
			$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
				'ON'	=> 'tt.user_id = ' . $this->user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
			);

			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
				'ON'	=> 'ft.user_id = ' . $this->user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
			);
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		unset($sql_array);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$row['forum_id'])
			{
				// Global Announcement?
				$row['forum_id'] = $this->to_fid;
			}

			if ($acl_list && !$this->auth->acl_gets($acl_list, $row['forum_id']))
			{
				continue;
			}

			if (!$row['post_approved'] && !$this->auth->acl_get('m_approve', $row['forum_id']))
			{
				// Moderators without the permission to approve post should at least not see them. ;)
				continue;
			}

			$rowset[$row['post_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/****
	* get_full_topic_data()
	* Get full topic data
	* @param array $topic_ids topics IDs to get
	* @param array $acl_list ACLs list auth to check
	* @param bool $read_tracking Keep tracking?
	****/
	private function get_full_topic_data($topic_ids, $acl_list = false, $read_tracking = false)
	{
		static $rowset = array();

		$topics = array();

		if (!sizeof($topic_ids))
		{
			return array();
		}

		// cache might not contain read tracking info, so we can't use it if read
		// tracking information is requested
		if (!$read_tracking)
		{
			$cache_topic_ids = array_intersect($topic_ids, array_keys($rowset));
			$topic_ids = array_diff($topic_ids, array_keys($rowset));
		}
		else
		{
			$cache_topic_ids = array();
		}

		if (sizeof($topic_ids))
		{
			$sql_array = array(
				'SELECT'	=> 't.*, f.*',

				'FROM'		=> array(
					TOPICS_TABLE	=> 't',
				),

				'LEFT_JOIN'	=> array(
					array(
						'FROM'	=> array(FORUMS_TABLE => 'f'),
						'ON'	=> 'f.forum_id = t.forum_id'
					)
				),

				'WHERE'		=> $this->db->sql_in_set('t.topic_id', $topic_ids)
			);

			if ($read_tracking && $this->config['load_db_lastread'])
			{
				$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
					'ON'	=> 'tt.user_id = ' . $this->user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
				);

				$sql_array['LEFT_JOIN'][] = array(
					'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
					'ON'	=> 'ft.user_id = ' . $this->user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
				);
			}

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!$row['forum_id'])
				{
					// Global Announcement?
					$row['forum_id'] = $this->to_fid;
				}

				$rowset[$row['topic_id']] = $row;

				if ($acl_list && !$this->auth->acl_gets($acl_list, $row['forum_id']))
				{
					continue;
				}

				$topics[$row['topic_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		foreach ($cache_topic_ids AS $id)
		{
			if (!$acl_list || $this->auth->acl_gets($acl_list, $rowset[$id]['forum_id']))
			{
				$topics[$id] = $rowset[$id];
			}
		}
		return $topics;
	}

	/****
	* posting_gen_topic_icons()
	* Generate Topic Icons for display (2th)
	* @param int $icon_id icon ID to pre-check
	****/
	private function posting_gen_topic_icons($icon_id = 0)
	{
		// Grab icons
		$icons = $this->cache->obtain_icons();

		if (!$icon_id)
		{
			$this->template->assign_var('S_NO_ICON_CHECKED', ' checked="checked"');
		}

		if (sizeof($icons))
		{
			foreach ($icons AS $id => $data)
			{
				if ($data['display'])
				{
					$this->template->assign_block_vars('topic_icon2', array(
						'ICON_ID'		=> $id,
						'ICON_IMG'		=> $this->phpbb_root_path . $this->config['icons_path'] . '/' . $data['img'],
						'ICON_WIDTH'	=> $data['width'],
						'ICON_HEIGHT'	=> $data['height'],
						'S_CHECKED'			=> ($id == $icon_id) ? true : false,
						'S_ICON_CHECKED'	=> ($id == $icon_id) ? ' checked="checked"' : '')
					);
				}
			}

			return true;
		}
		return false;
	}
}

?>