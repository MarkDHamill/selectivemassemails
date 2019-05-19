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

	'CRITERIA_INTRODUCTION'	=> 'Specifying criteria in the following fields will logically AND the results with the group selected. The more criteria specified, the fewer emails are sent. <em>If the combination of criteria results in no users selected, you will see a message saying the requested user does not exist</em>.',
	'FEWER_POSTS'						=> 'Fewer posts',
	'FEWER_POSTS_EXPLAIN'				=> 'Send group emails to users who have this many or fewer posts.',
	'INACTIVE_EXPLAIN'					=> 'Send group emails to inactive users',
	'LASTVISIT_BEFORE'					=> 'Last visit before',
	'LASTVISIT_BEFORE_EXPLAIN'			=> 'Send group emails to users with who haven\'t visited since this date. Use the format yyyy-mm-dd.',
	'LASTVISIT_AFTER'					=> 'Last visit after',
	'LASTVISIT_AFTER_EXPLAIN'			=> 'Send group emails to users with who have visited since this date. Use the format yyyy-mm-dd.',
	'LAST_POST_BEFORE'					=> 'Last posted before',
	'LAST_POST_BEFORE_EXPLAIN'			=> 'Send group emails to users with who haven\'t posted since this date. Use the format yyyy-mm-dd.',
	'LAST_POST_AFTER'					=> 'Last posted after',
	'LAST_POST_AFTER_EXPLAIN'			=> 'Send group emails to users with who have posted since this date. Use the format yyyy-mm-dd.',
	'MORE_POSTS'						=> 'More posts',
	'MORE_POSTS_EXPLAIN'				=> 'Send group emails to users with this many or more posts.',
	'UNREAD_PRIVATE_MESSAGES'			=> 'Unread private messages',
	'UNREAD_PRIVATE_MESSAGES_EXPLAIN'	=> 'Send group emails to users with who have this many or more unread private messages.',

));
