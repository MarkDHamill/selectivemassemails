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
			'core.user_setup'							=> 'load_language_on_setup',
			'core.acp_email_modify_sql'					=> 'add_criteria_fields',
			'core.acp_email_display'					=> 'add_template_variables',
		);
	}

	protected $db;
	protected $helper;
	protected $language;
	protected $php_ext;
	protected $request;
	protected $template;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language		Language object
	 * @param \phpbb\controller\helper	$helper			Controller helper object
	 * @param \phpbb\template\template	$template		Template object
	 * @param string                    $php_ext    	phpEx
	 * @param \phpbb\request\request	$request		Request object
	 * @param \phpbb\db\driver\factory 	$db 			The database factory object
	 */

	public function __construct(\phpbb\language\language $language, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext, \phpbb\request\request $request, \phpbb\db\driver\factory $db)
	{
		$this->language = $language;
		$this->helper   = $helper;
		$this->template = $template;
		$this->php_ext  = $php_ext;
		$this->request	= $request;
		$this->db		= $db;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbbservices/selectivemassemails',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
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
		$ranks = $this->request->variable('ranks', array('' => 0));
		$unread_pm_comparison = $this->request->variable('unread_pm_comparison', '');
		$unread_privmsg = $this->request->variable('unread_privmsg', 0, true);

		// Add the applicable criteria to the SQL query, but only if specified

		if ($posts > 0)
		{
			$sql_ary['WHERE'] .= ' AND u.user_posts ' . $operators[$posts_comparison] . $posts;
		}
		if ($lastvisit != '')
		{
			$sql_ary['WHERE'] .= ' AND u.user_lastvisit ' . $operators[$lastvisit_comparison] . strtotime($lastvisit);
		}
		if ($inactive == 'on')
		{
			$sql_ary['WHERE'] = str_replace('u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')', 'u.user_type = ' . USER_INACTIVE, $sql_ary['WHERE']);
		}
		if ($lastpost != '')
		{
			$sql_ary['WHERE'] .= ' AND u.user_lastpost_time ' . $operators[$lastpost_comparison] . strtotime($lastpost);
		}
		if ($unread_privmsg > 0)
		{
			$sql_ary['WHERE'] .= ' AND u.user_unread_privmsg ' . $operators[$unread_pm_comparison] . $unread_privmsg;
		}
		if (count($ranks) > 0)
		{
			$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('user_rank', $ranks);
		}

		$event['sql_ary'] = $sql_ary;

	}

}
