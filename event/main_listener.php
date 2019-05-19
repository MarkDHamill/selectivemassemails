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
			'core.acp_email_modify_sql'					=> 'add_criteria_fields'
		);
	}

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var string phpEx */
	protected $php_ext;

	/** @var \phpbb\request\request */
	private $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param string                    $php_ext    phpEx
	 * @param \phpbb\request\request	$request	Request object
	 */

	public function __construct(\phpbb\language\language $language, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext, \phpbb\request\request $request)
	{
		$this->language = $language;
		$this->helper   = $helper;
		$this->template = $template;
		$this->php_ext  = $php_ext;
		$this->request	= $request;
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
	 * Modify sql query to change the list of users the email is sent to
	 *
	 * @event core.acp_email_modify_sql
	 * @var	array	sql_ary		Array which is used to build the sql query
	 * @since 3.1.2-RC1
	 */

	public function add_criteria_fields($event)
	{

		$sql_ary = $event['sql_ary'];

		// Get the criteria variables from the screen.
		$inactive = $this->request->variable('inactive', '');
		$lastpost_after = $this->request->variable('lastpost_after', '', true);
		$lastpost_before = $this->request->variable('lastpost_before', '', true);
		$lastvisit_after = $this->request->variable('lastvisit_after', '', true);
		$lastvisit_before = $this->request->variable('lastvisit_before', '', true);
		$posts_fewer = $this->request->variable('posts_fewer', 0);
		$posts_more = $this->request->variable('posts_more', 0);
		$unread_privmsg = $this->request->variable('unread_privmsg', 0, true);

		// Add applicable criteria to the SQL query
		if ($posts_fewer > 0)
		{
			$sql_ary['WHERE'] .= ' AND user_posts <= ' . $posts_fewer;
		}
		if ($posts_more > 0)
		{
			$sql_ary['WHERE'] .= ' AND user_posts >= ' . $posts_more;
		}
		if ($lastvisit_before != '')
		{
			$sql_ary['WHERE'] .= ' AND user_lastvisit <= ' . strtotime($lastvisit_before);
		}
		if ($lastvisit_after != '')
		{
			$sql_ary['WHERE'] .= ' AND user_lastvisit >= ' . strtotime($lastvisit_after);
		}
		if ($inactive == 'on')
		{
			$sql_ary['WHERE'] .= ' AND user_type = ' . USER_INACTIVE;
		}
		if ($lastpost_before != '')
		{
			$sql_ary['WHERE'] .= ' AND user_lastpost_time <= ' . strtotime($lastpost_before);
		}
		if ($lastpost_after != '')
		{
			$sql_ary['WHERE'] .= ' AND user_lastpost_time >= ' . strtotime($lastpost_after);
		}
		if ($unread_privmsg > 0)
		{
			$sql_ary['WHERE'] .= ' AND user_unread_privmsg >= ' . $unread_privmsg;
		}

		$event['sql_ary'] = $sql_ary;

	}
}
