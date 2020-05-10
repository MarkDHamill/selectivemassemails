<?php
/**
 *
 * Selective mass emails. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Mark D. Hamill, https://www.phpbbservices.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbservices\selectivemassemails\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Selective mass emails Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			'core.acp_email_modify_sql'					=> 'add_criteria_fields',
			'core.acp_email_display'					=> 'add_template_variables',
		);
	}

	protected $db;
	protected $language;
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request	$request		Request object
	 * @param \phpbb\db\driver\factory 	$db 			The database factory object
	 * @param \phpbb\language\language 	$language 		Language object
	 */

	public function __construct(\phpbb\request\request $request, \phpbb\db\driver\factory $db, \phpbb\language\language $language)
	{
		$this->request	= $request;
		$this->db		= $db;
		$this->language	= $language;
	}

	/**
	 * Modify custom email template data before we display the form
	 *
	 * @event core.acp_email_display
	 * @var	array	template_data		Array with template data assigned to email template
	 * @var	array	exclude				Array with groups which are excluded from group selection
	 * @var	array	usernames			Usernames which will be displayed in form
	 *
	 * @since 3.1.4-RC1
	 */
	public function add_template_variables($event)
	{

		$this->language->add_lang('common','phpbbservices/selectivemassemails');

		// Hook in the CSS and Javascript files used by the extension
		$template_data = $event['template_data'];
		$template_data['S_INCLUDE_SME_CSS'] = true;
		$template_data['S_INCLUDE_SME_JS'] = true;

		// Add ranks
		$sql_ary = array(
			'SELECT'	=>	'*',
			'FROM' 		=> array(RANKS_TABLE => 'r')
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$rank_options = '';
		$ranks_found = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rank_options .= '<option value="' . $row['rank_id'] . '">' . $row['rank_title'] . '</option>';
			$ranks_found++;
		}

		$template_data['RANK_OPTIONS'] = $rank_options;
		$this->db->sql_freeresult($result); // Query be gone!

		$template_data['S_SHOW_RANKS'] = ($ranks_found > 0) ? true : false;
		$template_data['RANK_SIZE'] = min(5, $ranks_found);	// Set size of the ranks select control

		$event['template_data'] = $template_data;

	}

	/**
	 * Modify sql query to change the list of users the email is sent to
	 *
	 * @event core.acp_email_modify_sql
	 * @var	array	sql_ary		Array which is used to build the sql query
	 * @since 3.1.2-RC1
	 */
	public function add_criteria_fields($event)
	{

		static $operators = array('lt' => '< ', 'le' => '<= ', 'eq' => '= ', 'ne' => '<> ', 'ge' => '>= ', 'gt' => '> ');

		$sql_ary = $event['sql_ary'];

		// Get the criteria variables from the form
		$inactive = $this->request->variable('inactive', '');
		$lastpost = $this->request->variable('lastpost', '', true);
		$lastpost_comparison = $this->request->variable('lastpost_comparison', '');
		$lastvisit = $this->request->variable('lastvisit', '', true);
		$lastvisit_comparison = $this->request->variable('lastvisit_comparison', '');
		$posts = $this->request->variable('posts', 0);
		$posts_comparison = $this->request->variable('posts_comparison', '');
		$posts_unapproved = $this->request->variable('posts_unapproved', 0);
		$posts_unapproved_comparison = $this->request->variable('posts_unapproved_comparison', '');
		$ranks = $this->request->variable('ranks', array('' => 0));
		$unread_pm_comparison = $this->request->variable('unread_pm_comparison', '');
		$unread_privmsg = $this->request->variable('unread_privmsg', 0);

		// Add the applicable criteria to the SQL query, but only if specified

		if ($posts > 0)
		{
			$sql_ary['WHERE'] .= ' AND u.user_posts ' . $this->db->sql_escape($operators[$posts_comparison]) . (int) $posts;
		}

		if ($posts_unapproved > 0)
		{

			// Infer type of query:
			//		usernames - a list of usernames are supplied, so any groups specified is not used
			//		group - a group is specified (but not everyone)
			//		all - goes to all users because no group is specified and no usernames were supplied
			if ($this->request->variable('usernames', '') !== '')
			{
				$sql_type = 'usernames';
			}
			else if ($this->request->variable('g', 0) !== 0)
			{
				$sql_type = 'group';
			}
			else
			{
				$sql_type = 'all';
			}

			$sql_ary['SELECT'] .= ', p.poster_id, count(*)';
			$sql_ary['GROUP_BY'] = 'username, username_clean, user_email, user_jabber, user_lang, user_notify_type, p.poster_id HAVING count(*) ' . $operators[$posts_unapproved_comparison] . $posts_unapproved;

			switch ($sql_type)
			{
				case 'group':
					$sql_ary['WHERE'] .= ' AND post_visibility = ' . ITEM_UNAPPROVED;
					$sql_ary['LEFT_JOIN'] = array(
						array(
							'FROM' => array(POSTS_TABLE => 'p'),
							'ON'   => 'p.poster_id = u.user_id',
						),
						array(
							'FROM' => array(BANLIST_TABLE => 'b'),
							'ON'   => 'b.ban_id = u.user_id',
						),
					);
				break;

				case 'all':
				default:
					$sql_ary['WHERE'] = 'post_visibility = ' . ITEM_UNAPPROVED;
					$sql_ary['LEFT_JOIN'] = array(
						array(
							'FROM' => array(POSTS_TABLE => 'p'),
							'ON'   => 'p.poster_id = user_id',
						),
						array(
							'FROM' => array(BANLIST_TABLE => 'b'),
							'ON'   => 'b.ban_id = user_id',
						),
					);
				break;

				case 'usernames':
					$sql_ary['WHERE'] .= ' AND post_visibility = ' . ITEM_UNAPPROVED;
					$sql_ary['LEFT_JOIN'] = array(
						array(
							'FROM' => array(POSTS_TABLE => 'p'),
							'ON'   => 'p.poster_id = user_id',
						),
						array(
							'FROM' => array(BANLIST_TABLE => 'b'),
							'ON'   => 'b.ban_id = user_id',
						),
					);
				break;

			}

		}

		if ($lastvisit != '')
		{
			switch($lastvisit_comparison)
			{
				case 'le':
				case 'gt':
					// Need to include all timestamps up to 23:59:59 for the date
					$sql_ary['WHERE'] .= ' AND u.user_lastvisit ' . $this->db->sql_escape($operators[$lastvisit_comparison]) . (int) (strtotime($lastvisit) + (24 * 60 * 60) - 1);
				break;

				default:
					$sql_ary['WHERE'] .= ' AND u.user_lastvisit ' . $this->db->sql_escape($operators[$lastvisit_comparison]) . (int) strtotime($lastvisit);
				break;
			}
		}

		if ($inactive == 'on')
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('user_type', USER_INACTIVE);
		}

		if ($lastpost != '')
		{
			switch($lastpost_comparison)
			{
				case 'le':
				case 'gt':
					// Need to include all timestamps up to 23:59:59 for the date
					$sql_ary['WHERE'] .= ' AND u.user_lastpost_time ' . $this->db->sql_escape($operators[$lastpost_comparison]) . (int) (strtotime($lastpost) + (24 * 60 * 60) - 1);
				break;

				default:
					$sql_ary['WHERE'] .= ' AND u.user_lastpost_time ' . $this->db->sql_escape($operators[$lastpost_comparison]) . (int) strtotime($lastpost);
				break;
			}
		}

		if ($unread_privmsg > 0)
		{
			$sql_ary['WHERE'] .= ' AND u.user_unread_privmsg ' . $this->db->sql_escape($operators[$unread_pm_comparison]) . $unread_privmsg;
		}

		if (count($ranks) > 0)
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('user_rank', $ranks);
		}

		$event['sql_ary'] = $sql_ary;

	}

}
