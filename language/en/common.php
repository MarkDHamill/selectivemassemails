<?php
/**
 *
 * Selective mass emails. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Mark D. Hamill, https://www.phpbbservices.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(

	'SME_CRITERIA_INTRODUCTION'				=> 'Each additional criteria selected should reduce the number of emails sent. <em>If the combination of criteria results in no users selected, you will see a message saying the requested user does not exist</em>.',
	'SME_EQUAL_TO' 							=> '== (Equal to)',
	'SME_GREATER_THAN' 						=> '&gt; (Greater than)',
	'SME_GREATER_THAN_OR_EQUAL_TO' 			=> '&gt;= (Greater than or equal to)',
	'SME_INACTIVE_EXPLAIN'					=> 'Send group emails to inactive users only within the group specified above.',
	'SME_LASTPOST'							=> 'Last post',
	'SME_LASTPOST_EXPLAIN'					=> 'Send group emails to users based on their last post date. Use the date picker to select the date.',
	'SME_LASTVISIT'							=> 'Last visited',
	'SME_LASTVISIT_EXPLAIN'					=> 'Send group emails to users based on their last visited date. Use the date picker to select the date.',
	'SME_LESS_THAN'							=> '&lt; (Less than)',
	'SME_LESS_THAN_OR_EQUAL_TO' 			=> '&lt;= (Less than or equal to)',
	'SME_NOT_EQUAL_TO'						=> '!= (Not equal to)',
	'SME_POSTS_EXPLAIN'						=> 'Send group emails to users whose number of posts meet this criteria.',
	'SME_RANKS'								=> 'Ranks',
	'SME_RANKS_EXPLAIN'						=> 'You can select more than one rank.',
	'SME_SELECTED_USERS'					=> 'Selected users',
	'SME_UNAPPROVED_POSTS'					=> 'Unapproved posts',
	'SME_UNAPPROVED_POSTS_EXPLAIN'			=> 'Posts waiting in the moderation queue or that are unapproved for other reasons.',
	'SME_UNREAD_PRIVATE_MESSAGES'			=> 'Unread private messages',
	'SME_UNREAD_PRIVATE_MESSAGES_EXPLAIN'	=> 'Send group emails to users with whose number of unread private messages meet this criteria.',
	'SME_UNSELECT_RANKS'					=> 'Unselect all ranks',

));
