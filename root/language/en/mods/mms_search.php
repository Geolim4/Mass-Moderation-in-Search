<?php
/**
*
* mms_search.php [English]
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
	'MMS_ABORT'					=> 'Abort process?',
	'MMS_ABORTED'				=> 'The process has been aborted, you will be redirected to the index.',
	'MMS_ACCESS_DENIED'			=> 'Access denied',
	'MMS_ACTION'				=> 'Moderation action',
	'MMS_ACTION_EXPLAIN'		=> 'Choose the target moderation action.
								<br />Please note that setting will depend of your permissions in selected forums to search above.',
	'MMS_ADDON_DISABLED'		=> 'Addon not available for now!',
	'MMS_BAD_POST_MODE'			=> 'Bad post mode!',
	'MMS_BAD_TOPIC_MODE'		=> 'Bad topic mode!',
	'MMS_BAD_DATA_FORMAT'		=> 'Bad data format received!',
	'MMS_CHAR_BOTTOM'			=> '▼',
	'MMS_CHAR_TOP'				=> '▲',
									//To translators: Do not replace/translate *m*,*s*,*q*,*p*,*t*,*f*,*d* !!Considerate these string as of %s string.
	'MMS_CHRONO_POSTS'			=> 'Operation completed in *m* minut(s) and *s* seconds, *q* SQL queries for *p* altered posts: *f* failed post(s) and *d* treated post(s)',
	'MMS_CHRONO_TOPICS'			=> 'Operation completed in *m* minut(s) and *s* seconds, *q* SQL queries for *t* altered topics: *f* failed topic(s) and *d* treated topic(s)',
	'MMS_CONNECTION_FAIL'		=> 'Something went wrong while sending data to the server! Try again?',
	'MMS_DATA_ABORTED'			=> 'Aborted...',
	'MMS_DATA_SENDING'			=> 'Sending data...',
	'MMS_DATA_INTERRUPTED'		=> 'Interrupted...',
	'MMS_DATA_TERMINATED'		=> 'Finished',
	'MMS_EXIT_ALERT'			=> 'You are going to abort this process, are you really sure to continue?',
	'MMS_FAIL'					=> array(
		'post'					=> 'Failed posts',
		'topic'					=> 'Failed topics'
	),
	'MMS_FAILED'				=> array(
		'post'						=> 'Any failed post for now',
		'topic'						=> 'Any failed topic for now'
	),
	'MMS_FINAL_RESYNC'		=> array(
			'F'				=> 'Forums resynchronised',
			'T'				=> 'Topics resynchronised',
			'S'					=> 'Statistics resynchronised',
			'U'					=> 'Users resynchronised',
	),
	'MMS_FINAL_RESYNC_NEXT'		=> array(
			'F'				=> 'Resynchronising forums',
			'T'				=> 'Resynchronising topics',
			'S'					=> 'Resynchronising statistics',
			'U'					=> 'Resynchronising users',
	),
	'MMS_FIND_TOPIC'			=> 'Find a topic',
	'MMS_GLOBAL_ERROR'			=> 'Cannot manage global announcements with the Mass Moderation Tool.',
	'MMS_GRAB_EXP'				=> 'Double-click to select all',
	'MMS_HTML_DUMP'				=> '<strong>HTML Dump</strong> (including HTTP header)',
	'MMS_IPS_GRABBED'			=> 'IP(s) grabbed',
	'MMS_FORUM_ID'				=> 'Destination forum',
	'MMS_IGNORE'				=> 'Ignore',
	'MMS_IGNORED'				=> 'Ignored by user',
	'MMS_ITEM_MOVED'			=> 'The destination topic cannot be a shadow topic!',
	'MMS_LEFT'					=> array(
			'post'					=> 'Left posts',
			'topic'					=> 'Left topics'
	),
	'MMS_LEFTNO'				=> array(
			'post'					=> 'Any post in waiting for now',
			'topic'					=> 'Any topic in waiting for now'
	),
	'MMS_LOAD'					=> 'Load mass moderation tool',
	'MMS_LOADAVG'				=> 'System load',
	'MMS_LOADAVG_EXP'			=> 'Because a server up to 10% of load average will never respond in time, the real progress bar is multiplied by 10.',

	//Topics Logs
	'MMS_LOG_TOPIC_LOCK'		=> '<strong>Locked topic </strong> <em>(via Mass Moderation Tool)</em><br />» %s',
	'MMS_LOG_TOPIC_UNLOCK'		=> '<strong>Unlocked topic </strong> <em> (via Mass Moderation Tool)</em><br />» %s',
	'MMS_LOG_TOPIC_FORK'		=> '<strong>Copied topic</strong> <em>(via Mass Moderation Tool)</em><br />» from %s',
	'MMS_LOG_TOPIC_DELETE'		=> '<strong>Deleted topic “%1$s” written by</strong><br />» %2$s <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_TOPIC_MOVE'		=> '<strong>Moved topic</strong> <em>(via Mass Moderation Tool)</em><br />» from %1$s to %2$s',
	'MMS_LOG_TOPIC_RESYNC'		=> '<strong>Resynchronised topic counters</strong><br />» %s <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_TOPIC_MERGE'		=> '<strong>Merged topic « %1$s » written by</strong> %2$s <br />» <strong>to topic</strong> « %3$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_TOPIC_CHGICON'		=> '<strong>Modified topic icon</strong> <em>(via Mass Moderation Tool)</em><br />» %s',

	//Posts Logs
	'MMS_LOG_POST_LOCK'			=> '<strong>Locked post </strong><em>(via Mass Moderation Tool)</em><br />» %s',
	'MMS_LOG_POST_UNLOCK'		=> '<strong>Unlocked post </strong><em>(via Mass Moderation Tool)</em><br />» %s',
	'MMS_LOG_POST_DELETE'		=> '<strong>Deleted post “%1$s” written by</strong><br />» %2$s <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_MOVE'			=> '<strong>Moved post written by</strong> %1$s <br />» from %2$s to %3$s<em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_CHGPOSTER'	=> '<strong>Changed poster in topic “%1$s”</strong><em>(via Mass Moderation Tool)</em><br />» from %2$s to %3$s',

	//Posts Options Logs
	'MMS_LOG_POST_OPTIONS_ENABLE_SIG'	=> '<strong>Enabling signature from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_SIG'	=> '<strong>Disabling signature from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_ENABLE_BBCODES'	=> '<strong>Enabling BBCODES from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_BBCODES'	=> '<strong>Disabling BBCODES from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_ATTACHMENT'	=> '<strong>Removing attachments from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	//Posts Options Logs (Addons)
	'MMS_LOG_POST_OPTIONS_ENABLE_HPIV'	=> '<strong>Hiding post’s profile written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_DISABLE_HPIV'	=> '<strong>Showing post’s profile written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_PPR'	=> '<strong>Removing possible post’s revisions from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',
	'MMS_LOG_POST_OPTIONS_REMOVE_MM'	=> '<strong>Removing moderator message from post written by</strong> %1$s <br />» <strong>into topic</strong> « %2$s » <em>(via Mass Moderation Tool)</em>',

	'MMS_MAIN'					=> 'Main Menu',
	'MMS_MASS_POST_TOOL'		=> array(
			'lock'					=> 'Mass post lock',
			'unlock'				=> 'Mass post unlock',
			'delete'				=> 'Mass post delete',
			'chgposter'				=> 'Mass poster change',
			'move'					=> 'Mass post moving',
			'options'				=> 'Mass post options changing',
			'grabip'				=> 'Grab IPs',
	),
	'MMS_MASS_TOPIC_TOOL'		=> array(
			'lock'					=> 'Mass topic lock',
			'merge'					=> 'Mass topic merge',
			'unlock'				=> 'Mass topic unlock',
			'delete'				=> 'Mass topic delete',
			'fork'					=> 'Mass topic forking',
			'move'					=> 'Mass topic moving',
			'resync'				=> 'Mass topic resynchronizing',
			'chgicon'				=> 'Mass topic icon change',
			'attr'					=> 'Mass topic attribute change',
	),
	'MMS_MOD_DISABLED'			=> 'The mass moderation tool is disabled.',
	'MMS_MORE_INFORMATIONS'		=> 'More infos',
	'MMS_NO_DIRECT_ACCESS'		=> 'The mass moderation tool cannot be used as direct access, you must pass through the %1$sadvanced search%2$s to use this tool!',
	'MMS_NO_FPERMISSION'		=> 'Forum read permission denied',
	'MMS_NO_MPERMISSION'		=> 'Moderation permission denied',
	'MMS_NO_POST'				=> 'Any post selected!',
	'MMS_NO_TOPIC'				=> 'Any topic selected!',
	'MMS_NOT_AVAILABLE'			=> 'Not available',
	'MMS_OK'					=> 'Ok',
	'MMS_PACKET_SIZE'			=> 'Packet size is over allowed size!
									<br />For safety reasons you will be redirected to the forum index.',
	'MMS_PAGINATION'			=> 'Pagination',
	'MMS_PAGINATION_EXP'		=> 'This setting overwrite the default pagination configuration',
	'MMS_PAGINATION_POSTS_TOPICS'=> 'posts/topics per page',
	'MMS_PASSWORD'				=> 'Password confirmation',
	'MMS_PASSWORD_BAD'			=> 'Bad password!',
	'MMS_PASSWORD_CONFIRM'		=> 'Please confirm your password',
	'MMS_POST'					=> 'Post',
	'MMS_POST_ALREADY_LOCKED'	=> 'The post has been already locked.',
	'MMS_POST_ALREADY_UNLOCKED'	=> 'The post has been already unlocked.',
	'MMS_POST_DELETED'			=> 'Post has been probably deleted.',
	'MMS_POSTS_OPTIONS'			=> array(
		'DISABLE_SIG'				=> 'Detach signature',
		'DISABLE_SMILIES'			=> 'Disable smilies',
		'DISABLE_LINKS'				=> 'Disable magic URLs',
		'DISABLE_BBCODES'			=> 'Disable BBcodes',
		'ENABLE_SIG'				=> 'Attach signature',
		'ENABLE_SMILIES'			=> 'Enable smilies',
		'ENABLE_LINKS'				=> 'Enable magic URLs',
		'ENABLE_BBCODES'			=> 'Enable BBCodes',
		'REMOVE_ATTACHMENT'			=> '╚► Remove attachment',
		//Addons
		'DISABLE_HPIV'				=> 'Show poster’s profile',
		'ENABLE_HPIV'				=> 'Hide poster’s profile',
		'REMOVE_PPR'				=> '╚► Remove possible post revisions',
		'REMOVE_MM'					=> '╚► Remove moderator messages',
	),
	'MMS_POSTS_OPTIONS_ERROR'		=> array(
		'REMOVE_ATTACHMENT'			=> 'Any attachment removed',
	),
	'MMS_POSTS_OPTIONS_SUCCESS'		=> array(
		'DISABLE_SIG'				=> 'Signature detached',
		'DISABLE_SMILIES'			=> 'Smilies deactivated',
		'DISABLE_LINKS'				=> 'URLs deactivated',
		'DISABLE_BBCODES'			=> 'BBcodes deactivated',
		'ENABLE_SIG'				=> 'Signature attached',
		'ENABLE_SMILIES'			=> 'Smilies enabled',
		'ENABLE_LINKS'				=> 'URLs enabled',
		'ENABLE_BBCODES'			=> 'BBcodes enabled',
		'REMOVE_ATTACHMENT'			=> 'Attachment(s) removed',
		//Addons
		'DISABLE_HPIV'				=> 'Poster’s profile showed',
		'ENABLE_HPIV'				=> 'Poster’s profile hidden',
		'REMOVE_PPR'				=> 'Possible post revisions removed',
		'REMOVE_MM'					=> 'Moderator message removed',
	),
	'MMS_POSTS_OPTIONS_EXP'			=> 'Post options',
	//For key marked "!" try to no reach more than 255 chars per key. Otherwise the text will be hard-broken in the post-edit-log reason
	'MMS_POSTS_ICON_FAIL'			=> 'Choosen icon does not exist!',
	'MMS_POSTS_ICON_REASON'			=> 'Topic icon changing (via Mass Moderation Tool)',//!
	'MMS_POSTS_OPTIONS_REASON'		=> array(
		'DISABLE_SIG'				=> 'Disabling signature (via Mass Moderation Tool)',//!
		'DISABLE_SMILIES'			=> 'Disabling smilies (via Mass Moderation Tool)',//!
		'DISABLE_LINKS'				=> 'Disabling URLs (via Mass Moderation Tool)',//!
		'DISABLE_BBCODES'			=> 'Disabling BBcodes (via Mass Moderation Tool)',//!
		'ENABLE_SIG'				=> 'Enabling signature (via Mass Moderation Tool)',//!
		'ENABLE_SMILIES'			=> 'Enabling smilies (via Mass Moderation Tool)',//!
		'ENABLE_LINKS'				=> 'Enabling URLs (via Mass Moderation Tool)',//!
		'ENABLE_BBCODES'			=> 'Enabling BBcodes (via Mass Moderation Tool)',//!
		'REMOVE_ATTACHMENT'			=> 'Removing attachment (via Mass Moderation Tool)',//!
		//Addons
		'DISABLE_HPIV'				=> 'Showing poster’s profile (via Mass Moderation Tool)',//!
		'ENABLE_HPIV'				=> 'Hiding poster’s profile (via Mass Moderation Tool)',//!
		'REMOVE_PPR'				=> 'Removing possible post revision (via Mass Moderation Tool)',//!
		'REMOVE_MM'					=> 'Removing moderator message (via Mass Moderation Tool)',//!
	),
	'MMS_PRELOADING'			=> 'Loading document...
									<br />Please wait.',
	'MMS_PRELOADING_EXP'		=> ' If your browser doesn’t seem to respond, it may be a natural behaviour if you have selected many search criteria!',
	'MMS_PREV'					=> 'Preview',
	'MMS_PREVIEW'				=> 'Post quick preview',
	'MMS_PRIV'					=> '(Private)',
	'MMS_PRIVATE'				=> 'Your are not authorized to read this post.',
	'MMS_REASON'				=> 'Reason',
	'MMS_REDIRECT'				=> 'You will be redirected to the forum index.',
	'MMS_SAME_FORUM'			=> 'Origin forum is the same than the destination forum',
	'MMS_SAME_TOPIC'			=> 'Origin topic is the same than the destination topic',
	'MMS_SAME_USERNAME'			=> 'Current username is the same than the new username',
	'MMS_SEARCH_WARN'			=> 'You are going to show more than 1000 results simultaneously, this can increase substantially the load time of your server and your browser. Are you sure to continue ?',
	'MMS_SELECTED'				=> 'selected',
	'MMS_SELECTEDS'				=> 'selecteds',
	'MMS_SELECT_FORUM'			=> 'Select per forum',
	'MMS_SELECT_MODE'			=> 'You are in topic mode selection, so the access to some features like Moderator control panel, Administration and you user account pannel will still unavailable for now.',
	'MMS_SELECT_TOPIC'			=> 'Select a topic',
	'MMS_SELECT_TYPE'			=> 'Selector type',
	'MMS_SELECT_CHECKBOX'		=> 'Checkbox',
	'MMS_SELECT_CHECKTOPIC'		=> 'Post/topic to check',
	'MMS_SELECT_RECTANGLE'		=> 'Selection rectangle',
	'MMS_SELECT_THIS_TOPIC'		=> '<strong style="color: green;">Select this topic</strong>',
	'MMS_SELECT_USER'			=> 'Select by user',
	'MMS_SHOW_POST_REASON'		=> 'Show edit post reason?',
	'MMS_SQL_QUERIES'			=> '(*s* SQL queries)',
																//To translators: Please keep this line as a single line!!
	'MMS_SQL_WARN'				=> 'Please note that the copy of popular topics can increase substantially the amount of SQL queries, do you want to enable the query temporisation? <br />This process will increase the required load time treatement but will considerably reduce the <a href="http://dev.mysql.com/doc/refman/5.0/en/gone-away.html" onclick="window.open(this.href); return false;">timeout risk</a> from the database.',
	'MMS_STATUT'				=> 'Statut',
	'MMS_STATUT_ATTR_CHGED'		=> 'Topic attribute modified as %s',
	'MMS_STATUT_DELETED'		=> 'Deleted',
	'MMS_STATUT_FORKED'			=> 'Copied in %1$s with the ID %2$s',
	'MMS_STATUT_LOCKED'			=> 'Locked',
	'MMS_STATUT_ICONCHD'		=> 'Topic icon modified',
	'MMS_STATUT_IPGRABBED'		=> 'IP grabbed',
	'MMS_STATUT_MERGED'			=> 'Merged into %s',
	'MMS_STATUT_MOVED'			=> 'Moved in %s',
	'MMS_STATUT_POSTER_CHGED'	=> 'Poster changed as %s',
	'MMS_STATUT_RECYNC'			=> 'Resynchronised',
	'MMS_STATUT_UNLOCKED'		=> 'Unlocked',
	'MMS_SUB_ARROW'				=> '╚═►',
	'MMS_SUCCESS'				=> 'Process terminated successful!',
	'MMS_TIMEOUT'				=> 'Connection timed out',
	'MMS_TIMEOUT_EXP'			=> 'Time limit for the operation exceeded',
	'MMS_TITLE'					=> 'Mass moderation tool',
									//To translators: Please Keep the first <br /> too !!
	'MMS_TOO_MANY_USERS'		=> '<br />For safety reasons the Mass-Tool cannot be used by multi users simultaneously!
									<br />Please wait 20 seconds and try again.
									<br />User connected currently: %s',
	'MMS_TOOLS_POSTS'			=> array(
			'lock'					=> 'Lock posts  [ Prevent message editing ]',
			'unlock'				=> 'Unlock posts',
			'delete'				=> 'Delete posts',
			'chgposter'				=> 'Change poster',
			//'fork'					=> 'Fork posts',
			'move'					=> 'Move posts',
			'options'				=> 'Edit posts options',
			'grabip'				=> 'Grab IPs',
	),
	'MMS_TOOLS_TOPICS'			=> array(
			'lock'					=> 'Lock topics',
			'unlock'				=> 'Unlock topics',
			'delete'				=> 'Delete topics',
			'fork'					=> 'Fork topics',
			'move'					=> 'Move topics',
			'merge'					=> 'Merge topics',
			'resync'				=> 'Resynchronize topics',
			'chgicon'				=> 'Topic icon change',
			'attr'					=> 'Topic attribute change',
	),
	'MMS_TOPIC'					=> 'Topic',
	'MMS_TOPIC_ALREADY_LOCKED'	=> 'The topic has been already locked.',
	'MMS_TOPIC_ALREADY_UNLOCKED'=> 'The topic has been already unlocked.',
	'MMS_TOPIC_DELETED'			=> 'Topic has been probably deleted.',
	'MMS_TOPIC_ICON'			=> 'Topic icon',
	'MMS_TOPIC_ICON_NO'			=> 'No icon',
	'MMS_TOPIC_ID'				=> 'Target topic ID',
	'MMS_TOPIC_ID_EXP'			=> 'Enter the target topic ID to move selected items',
	'MMS_TOPIC_ID_INVALID'		=> 'Invalid topic ID!',
	'MMS_TREAT'					=> array(
			'post'					=> 'Treated posts',
			'topic'					=> 'Treated topics'
	),
	'MMS_TREATED'				=> array(
			'post'					=> 'Any treated post for now',
			'topic'					=> 'Any treated topic for now'
	),
	'MMS_UP_ARROW'				=> '╔►',
	'MMS_USERNAME'				=> 'New username',
	'MMS_USERNAME_CASE'			=> 'Username is case sensitive',
	'MMS_USERNAME_INVALID'		=> 'Invalid username',
	'MMS_VIA_MMS'				=> ' <em>(via Mass Moderation Tool)</em>',
	'MMS_VIEW_ALL'				=> 'View all',
	'MMS_VIEW_LESS'				=> 'View less',
	'MMS_VIEW_MORE'				=> 'View more',
	'MMS_WARNING_ACTION'		=> 'The mass moderation is a powerfull tool as dangereous, you must be sure that the action you are about to commit, especially with the removal and merging tools.
									Be aware that in case of error only database backup will be able to resolve this error, under the condition that it be recent.
									Also, the mass moderation should not be used on servers were backup delay exceed 24 hours.
									<br />By clicking on «<em>' . $lang ['SUBMIT'] . '</em>» you become aware of this warning and will proceed to the selected action, if there is any doubt, click «<em>' . $lang ['CANCEL'] . '</em>» and contact your technical manager to avoid mishandling.',

));
?>