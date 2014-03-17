<?php
/**
*
* @package acp INFO Mass Moderation In Search
* @version $Id: acp_mms.php v1.1.1 07h79 03/16/2014 Geolim4 Exp $
* @copyright (c) 2012 Geolim4.com  http://Geolim4.com
* @bug/function request: http://geolim4.com/tracker.php
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @package module_install
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* @package module_install
*/
class acp_mms_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_mms',
			'title'		=> 'ACP_MMS_CONFIG',
			'version'	=> '1.1.1',
			'modes'		=> array(
			'configuration'		=> array('title' => 'ACP_MMS_CONFIG', 'auth' => '', 'cat' => array('ACP_MMS_CONFIG')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>