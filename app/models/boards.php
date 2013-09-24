<?php

/**
 * Hakz Project in Laravel
 * @author roop <roop@hakz.co>
 * @copyright 2013, Hakz Project, http://hakz.co/
 * @package Discussion Board Model
 */

class Boards {

	/**
	 * Crumb container
	 * @param string
	 * @access private
	 */
	private static $crumbs;

	/**
	 * Fetch all forum boards in the site 
	 * @param void
	 * @return mixed
	 */
	public static function fetchBoards() {
		$boards = Session::get('accessibleBoards.boards');


		foreach($boards as $parent) {
			// Retrieve children board
			$children = self::fetchBoardChild($parent['fid']);

			// Form the data array
			$data = array(
				'fid' => $parent['fid'],
				'name' => $parent['name'],
				'description' => $parent['description'],
				'children' => $children
			);

			$tpl .= View::make('boards.categories', $data);
		}

		return $tpl;
	}

	/**
	 * Fetch a specific forum board
	 * @param string Forum board navigation slug
	 * @return array
	 */
	public static function fetchBoard($slug) {

		$board = DB::table('boards')->where('navigation_slug', '=', $slug)->first();
		$permissions = self::fetchPermissions($board['fid']);

		// Check if the board really exists
		if(!is_array($board)) {
			$crumbs = self::generateCrumbs(array(
				0 => array(
					'name' => 'Invalid Board'
				)
			));

			return array('error' => true, 'crumbs' => $crumbs);
		}

		// Check whether the user's group has view permissions
		if(is_array($permissions['view']) && $permissions['view'][Session::get('usergroup.gid')] != true) {
			$crumbs = self::generateCrumbs(array(
				0 => array(
					'name' => 'Invalid Board'
				)
			));

			return array('error' => true, 'crumbs' => $crumbs);
		}

		// Retrieve the board's children
		$children = self::fetchBoardChild($board['fid']);

		// Generate Crumbs
		$crumbs = self::generateCrumbs($board['fid']);

		/**
		 * Only allow posting and other queries to run on boards not counted as top parent
		 */
		if($board['parent'] == 0) {
			return false;
		}
		// Check post permissions
		$canPost = Session::get('usergroup.can_failbb_post') == true && $permissions['post'][Session::get('usergroup.gid')] == true ? true : false;

		// Retrieve the board's threads
		$threads = self::fetchBoardThreads($board['fid']);
		if(!is_array($threads)) {
			return false;
		}
			
		foreach($threads as $thread) {
			// Query the last poster of the thread
			$lastPoster = self::fetchLastPost($thread['pid'], false);

			// Check whether the user is allowed to post
			$canPost = $permissions['post'][Session::get('usergroup.gid')] == true ? true : false;

			// Query the author of the thread
			$author = DB::table('users')->select('username', 'display_name')->where('uid', '=', $thread['author'])->first();
			$displayName = !empty($author['display_name']) ? $author['display_name'] : $author['username'];

			// Query the number of replies to the thread
			$threadReplies = DB::table('posts')->where('reply_to', '=', $thread['pid'])->count();

			// Date Posted
			$postedOn = Hakz::parseTime($thread['date_posted']);

			// Return values for view parsing
			$threadlist[] = array(
				'topic' => $thread['topic'],
				'hash' => $thread['hash'],
				'posted_on' => $postedOn,
				'last_post' => $lastPoster,
				'user' => $displayName,
				'user_link' => URL::to('user/' . strtolower($author['username']) . '/profile'),
				'views' => $thread['views'],
				'replies' => $threadReplies,
			);
		}

		// Saturate data
		$data = array(
			'fid' => $board['fid'],
			'name' => $board['name'],
			'description' => $board['description'],
			'slug' => $board['navigation_slug'],
			'crumbs' => $crumbs,
			'children' => $children,
			'threads' => $threadlist,
			'can_post' => $canPost
		);

		return $data;
		
	}

	/**
	 * Fetches all the board's sub-boards
	 * @param int Board ID
	 * @return array
	 */
	public static function fetchBoardChild($fid) {
		$boards = DB::table('boards')->where('parent', '=', $fid)->get();

		if(!_array($boards)) {
			return false;
		}
		
		foreach($boards as $board) {
			// Resets data
			$subchilds = null;

			// Get Permissions
			$permissions = empty($board['permissions']) ?
				self::fetchPermissions($board['fid']):
				Hakz::parseSerial($board['permissions'], 'decode');

			if(array_key_exists(Session::get('usergroup.gid'), $permissions['view']) && $permissions['view'][Session::get('usergroup.gid')] == true) {
				// Query second level sub-boards
				$subchild = DB::table('boards')->where('parent', '=', $board['fid'])->get();
				if(is_array($subchild) && count($subchild) > 0) {
					$childlist = array();

					foreach($subchild as $child) {
						$childlist[] = "" . HTML::link('boards/b/' . $child['navigation_slug'], $child['name']) . "";
					}

					$subchilds = "<div style='font-size: 11px;'><strong>Sub Forums:</strong> " . @implode(' - ', $childlist) . "</div>";
				}

				// Count the number of total threads in the board
				$threadCount = DB::table('posts')->where('reply_to', '=', 0)->where('board', '=', $board['fid'])->count();
				// Count the number of total posts in the board
				$postCount = DB::table('posts')->where('board', '=', $board['fid'])->count();
				// Query the last thread
				$lastPost = self::fetchLastPost($board['fid']);
				// Permissions
				$permissions = Hakz::parseSerial($board['permissions'], 'decode');

				$data[$board['parent']][] = array(
					'link' => 'boards/b/' . $board['navigation_slug'],
					'name' => $board['name'],
					'description' => $board['description'],
					'thread_count' => $threadCount,
					'post_count' => $postCount,
					'last_post' => $lastPost,
					'sub_childs' => $subchilds
				);
			}
		}

		return $data;
		
	}

	/**
	 * Fetch a specific board's threads (Automatically paginated)
	 * @param int Forum board ID
	 * @param int Number of threads to be fetched
	 * @param array additional columns to select
	 * @return mixed
	 */
	public static function fetchBoardThreads($fid, $offset = '', $addtional = '') {
		$offset = !empty($offset) && is_numeric($offset) ? $offset : 0;

		return DB::table('posts')
			->select('pid', 'topic', 'author', 'views', 'board', 'date_posted', 'hash')
			->where('board', '=', $fid)->where('reply_to','=', 0)
			->orderBy('date_posted', 'DESC')
			->take($offset)
			->get();
	}

	/**
	 * Fetches the last post of a specific board/thread
	 * @param int ID of board/thread
	 * @param boolean search mode [thread*def*|board]
	 * @return array
	 */
	public static function fetchLastPost($id, $boardMode = true) {
		
		$lastpost = $boardMode == true ? 
			DB::select("SELECT `P1`.`hash` hash_r, `P1`.`topic` topic_r, `P2`.`pid`, `P2`.`topic` topic_m,
					`P2`.`hash` hash_m, `P1`.`date_posted`, `P4`.`username`, `P4`.`display_name`
				FROM `hkz_posts` as P1
				LEFT JOIN `hkz_posts` as P2 ON (`P1`.`reply_to` = `P2`.`pid`)
				LEFT JOIN `hkz_users` as P4 ON `P1`.`author` = `P4`.`uid`
				WHERE `P1`.`board` = ? ORDER BY `P1`.`date_posted` DESC LIMIT 1", array($id)): 
			DB::select("SELECT `P1`.`hash` hash_r, `P2`.`topic` topic_m, `P2`.`pid`, `P2`.`hash` hash_m,
					`P2`.`date_posted`, `P4`.`username`, `P4`.`display_name`
				FROM `hkz_posts` as P1 
				LEFT JOIN `hkz_posts` as P2 ON `P1`.`reply_to` = `P2`.`pid`
				LEFT JOIN `hkz_users` as P4 ON `P1`.`author` = `P4`.`uid`
				WHERE `P1`.`reply_to` = ? ORDER BY `P1`.`date_posted` DESC LIMIT 1", array($id));
		
		$lastpost = $lastpost[0];
		$postcount = DB::table('posts')
			->where('reply_to', '=', $lastpost['pid'])
			->orWhere('pid', '=', $lastpost['pid'])
			->count();
		$page = ceil($postcount / Config::get('failbb.items')); // Global settings would be on /app/config/failbb.php

		if(!is_array($lastpost)) return "<div align='center'>-</div>";

		if($boardMode == true) {
			$title = empty($lastpost['topic_m']) ? 
				$lastpost['topic_r'] . " &gt;&gt; " . substr($lastpost['hash_r'], -10, 10): 
				$lastpost['topic_m'] . " &gt;&gt; " . substr($lastpost['hash_r'], -10, 10);
		} else {
			$title = substr($lastpost['hash_r'], 0, 20);
		}

		$insert = $page > 1 ? "?page={$page}#hash-" . $lastpost['hash_r'] : "#hash-" . $lastpost['hash_r'];

		$link = isset($lastpost['hash_m']) ?
			URL::to("boards/t/" . $lastpost['hash_m'] . ".html{$insert}"):
			URL::to("boards/t/" . $lastpost['hash_r'] . ".html{$insert}");

		$displayname = !empty($lastpost['display_name']) ? $lastpost['display_name'] : $lastpost['username']; 

		$data = array(
			'link' => $link,
			'date' => Hakz::parseTime($lastpost['date_posted']),
			'user' => $displayname,
			'user_link' => URL::to('user/' . strtolower($lastpost['username']) . '/profile'),
			'title' => $title
		);

		return View::make('boards.last-post', $data);
	}

	/**
	 * Fetches a thread
	 * @param string sha1 slug
	 * @return array
	 */
	public static function fetchThread($slug) {

		$thread = DB::table('posts') // Perform basic querying
		->select('pid', 'topic', 'hash', 'board')
		->where('hash', '=', $slug)
		->first();

		// Check if the thread really exists
		if(!is_array($thread)) {
			$crumbs = self::generateCrumbs(array(
				0 => array(
					'name' => 'Invalid Thread'
				)
			));

			return array('error' => true, 'crumbs' => $crumbs);
		}

		// Process some queries
		DB::table('posts')->where('pid', '=', $thread['pid'])->increment('views');
		$crumbs = self::generateCrumbs($thread['board'], array(
			999 => array(
				'name' => $thread['topic'],
				'link' => URL::to('boards/t/' . $thread['hash'] . '.html'),
				'title' => $thread['topic']
			)
		));

		// Check the permissions set
		$permissions = self::fetchPermissions($thread['board']);

		if(is_array($permissions['view']) && $permissions['view'][Session::get('usergroup.gid')] != true) {
			$crumbs = self::generateCrumbs(array(
				0 => array(
					'name' => 'Invalid Thread'
				)
			));

			return array('error' => true, 'crumbs' => $crumbs);
		}


		$reply = Session::get('usergroup.can_failbb_reply_thread') == true ? true : false; // If the user can reply

		// Process posts
		$posts = self::fetchThreadPosts($thread['pid']);

		// Return data
		$data = array(
			'topic'	 => $thread['topic'],
			'board' => $thread['board'],
			'crumbs' => $crumbs,
			'reply' => $reply,
			'posts' => $posts,
			'hash' => $thread['hash'],
			'posts' => $posts
		);

		return $data;
	}

	/**
	 * Fetches a particular user's post
	 * @param int UserID
	 * @param int Offset limitation
	 */
	public static function fetchUserPosts($uid, $limit = '', $permission = false) {
		if($permission === true) {
			foreach(Session::get('accessibleBoards.children') as $board) {
				$boards[] = $board;
			}

			$query = DB::table('posts')
				->where('author', '=', $uid)
				->whereIn('board', $boards)
				->orderBy('date_posted', 'desc')
				->take($limit)
				->get();
		} else {
			$query = DB::table('posts')
				->where('author', '=', $uid)
				->orderBy('date_posted', 'desc')
				->take($limit)
				->get();
		}

		return $query;
	}

	/**
	 * Fetch a number of posts
	 * @param int $limit number of items to display
	 */
	public static function fetchNumberOfPosts($limit = 5) {
		$query = DB::table('posts')
			->orderBy('date_posted', 'desc')
			->take($limit)
			->get();

		return $query;
	}

	/**
	 * Fetches all posts of a certain thread
	 * @param int ThreadID
	 * @param string SHA1 Hash, Post identifier
	 * @return template
	 */
	public static function fetchThreadPosts($tid, $hash = '') {
		$posts = !preg_match('/^([a-z0-9]{40})$/', $hash) && empty($hash) ?
			DB::table('posts')
				->where('reply_to', '=', $tid)
				->orWhere('pid', $tid)
				->paginate(Config::get('failbb.items')):
			DB::table('posts')
				->where('reply_to', '=', $tid)
				->where('hash', '=', $hash)
				->paginate(Config::get('failbb.items'));


		foreach($posts as $post) {
			$author = DB::table('users')->select('username', 'display_name', 'avatar', 'usergroup')->where('uid', '=', $post['author'])->first();
			$usergroup = DB::table('usergroups')->select('name')->where('gid', '=', $author['usergroup'])->first();
			
			$topic = "<strong>" . substr($post['hash'], -10, 10) . "</strong>";
			$flag = !empty($post['last_ct']) ?  "<img uid='avatar' src='" . asset("img/flags/" . $post['last_ct'] . '.png') . "'/>": '';
			$avatar = !empty($author['avatar']) ? "<img uid='avatar' src='" . asset("img/avatars/" . $author['avatar']) ."' class='img-polaroid' />": '';
			$display = !empty($author['display_name']) ? HTML::link('user/'.strtolower($author['username']) .'/profile', $author['display_name']): HTML::link('user/'.strtolower($author['username']) .'/profile', $author['username']);
			$contents = Parser::parseMessage($post['contents']);
			$posted = Hakz::parseTime($post['date_posted']);


			// Process buttons
			if(Session::get('usergroup.can_failbb_edit_thread') && $post['author'] == Session::get('uid')) {
				$btn[] = "<a uid='btn-edit' hash='{$post['hash']}'><i class'icon-pencil icon-white'></i></a>";
			}
			$buttons = is_array($btn) ? "[ " . @implode(' - ', $btn) . " ]" : '';

			$data[] = array(
				'topic' => $topic,
				'hash' => $post['hash'],
				'posted_on' => $posted,
				'flag' => $flag,
				'avatar' => $avatar,
				'display' => $display,
				'contents' => $contents
			);

			unset($btn);
		}


		return array('posts' => $data, 'links' => $posts->links());
	}

	/**
	 * Fetches the permission of a specific board
	 * @param int Board ID
	 * @return array
	 */
	public static function fetchPermissions($fid) {
		$key = sha1($fid);

		if(Cache::has($key)) {
			return Cache::get($key);
		} else {
			// Check permissions
			if(empty($board['permissions'])) {
				do {
					if(!is_array($parentBoard)) {
						$parentBoard = DB::table('boards')->select('permissions', 'parent')->where('fid', '=', $fid)->first();
					} else {
						$parentBoard = DB::table('boards')->select('permissions', 'parent')->where('fid', '=', $parentBoard['parent'])->first();
					}

					if(!empty($parentBoard['permissions'])) {
						$board['permissions'] = $parentBoard['permissions']; // Overrides the empty permission set of the current board with it's parent
					}
				} while (!empty($parentBoard['permissions']) || $parentBoard['parent'] != 0);
			}

			$data = Hakz::parseSerial($board['permissions'], 'decode');
			// Cache it
			Cache::put($key, $data, 30);
		}

		return $data;
	}


	/**
	 * (FUR) Generate breacd crumbs
	 * @param int|array BoardID|links to add
	 * @return array
	 */
	public static function generateCrumbs($fid, $add = '') {

		$cmb[] = array('order' => -1, 'link' => HTML::link('/boards', 'Home')); // Adds the initial board home

		// Initialize by adding items from the $add variable
		if(is_array($add)) {
			foreach($add as $key => $val) {
				$tpl[$key] = "<strong>" . HTML::link($val['link'], $val['title']) . "</strong>";
			}
		}

		if(is_array($fid)) {

			foreach($fid as $key => $val) {
				$tpl[$key] = array_key_exists('link', $val) ?
					"<strong>" . $val['link'] . "</strong>":
					"<strong>" . $val['name'] . "</strong>";
			}

		} else {

			// Only do this if $fid is int
			do {
				$dbc = !is_array($dbc) ? 
					DB::table('boards')->where('fid', '=', $fid)->select('name', 'parent', 'navigation_slug')->first() :
					DB::table('boards')->where('fid', '=', $dbc['parent'])->select('name', 'parent', 'navigation_slug')->first();


				$cmb[] = array(
					'order' => $dbc['parent'],
					'link' => HTML::link('boards/b/' . strtolower($dbc['navigation_slug']), $dbc['name'])
				);

			} while ($dbc['parent'] != 0);

		}

		foreach($cmb as $key => $val) {
			$tpl[$val['order']] = "<strong>".$val['link']."</strong>";
		}

		ksort($tpl);
		return @implode(" &gt;&gt; ", $tpl);
	}

	/**
	 * Creates a new (thread/reply) to a certain (board/thread)
	 * @param array Post parameters
	 * @return array
	 */
	public static function newPost() {

		$hash = Input::has('topic') ? sha1(Input::get('topic') . time()) : sha1(time());

		// Form the data array to insert
		$data = array(
			'author' => Session::get('uid'),
			'hash' => $hash,
			'board' => Input::get('fid'),
			'contents' => Input::get('contents'),
			'date_posted' => time(),
			'ip' => $_SERVER['REMOTE_ADDR']
		);

		// Additional processing
		if(Input::has('topic') == true) {
			$data = array_merge($data, array('topic' => Input::get('topic')));
		}

		if(Input::has('hash') == true) {
			$thread = DB::table('posts')->select('pid')->where('hash', '=', Input::get('hash'))->first();
			$data = array_merge($data, array('reply_to' => $thread['pid']));
		}

		// Perm the insert query
		DB::table('users')->where('uid', '=', Session::get('uid'))->increment('posts');
		$insert = DB::table('posts')->insertGetId($data);

		$return = !is_array($thread) ? array('pid' => $insert, 'hash' => $hash) : array('thash' => Input::get('hash'), 'hash' => $hash, 'reply_to' => $thread['pid']);
		return $return;

	}

	/**
	 * Edits a certain post
	 * @param array Post parameters
	 * @return array
	 */
	public static function editPost() {

		// Edit Data
		$edit = array(
			'edited_on' => time(),
			'edited_by' => Session::get('uid'),
			'edited_ip' => $_SERVER['REMOTE_ADDR']
		);

		// Form the data array to update
		$data = array(
			'contents' => Input::post('contents'),
			'edit_data' => json_encode($edit)
		);

		DB::table('posts')->update($data)->where('hash', '=', Input::post('hash'));
		$data = self::fetchThreadPosts(Input::post('reply_to'), Input::post('hash'));

		return $data;
	}

	/**
	 * Returns as invalid whenever there's a mismatch
	 * @param boolean $mode [board(def)|thread]
	 * @return template
	 */
	public static function invalidHandler($mode = true) {
		$head = $mode == true ? 'Invalid Board' : 'Invalid Thread';

		$crumbs = self::generateCrumbs(array(
			0 => array(
				'name' => $head
			)
		));

		return array('error' => true, 'crumbs' => $crumbs);
	}

	/**
	 * Pre-processes a new thread template
	 * @param string $slug Board slug
	 * @param mixed
	 */
	public static function preNewThread($slug) {
		return DB::table('boards')
			->select('fid', 'navigation_slug')
			->where('navigation_slug', '=', $slug)
			->first();
	}

	/**
	 * Sets accessible boards for the current usergroup
	 * @param void
	 * @return array
	 */
	public static function setAccessibleBoards() {
		$boards = json_decode(Cache::get('allBoards'), true);

		foreach($boards as $board) {
			$permissions = Hakz::parseSerial($board['permissions'], 'decode')['view'];

			if(array_key_exists(Session::get('usergroup.gid'), $permissions) && $permissions[Session::get('usergroup.gid')] == true) {
				$boardList['boards'][] = $board;

				// Query children
				do {
					$child = ! is_array($child) ?
						DB::table('boards')->select('fid')->where('parent', '=', $board['fid'])->first():
						DB::table('boards')->select('fid')->where('parent', '=', $child['fid'])->first();

					if($child['fid'] == null) break; // Prevent adding 'null' values

					$boardList['children'][] = $child['fid'];
				} while (is_array($child));	

			}
		}

		Session::put('accessibleBoards', $boardList);

		return true;
	}

}