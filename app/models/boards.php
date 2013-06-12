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
		// Fetch all the parent boards
		if(Cache::has('q.allBoards') != true) {
			$boards = DB::table('boards')->where('parent', '=', 0)->get();
			Cache::put('q.allBoards', json_encode($boards), 10);
		} else {
			$boards = json_decode(Cache::get('q.allBoards'), true);
		}

		$tpl = '';

		// Query all their subchilds individually
		if(is_array($boards)) {
			foreach($boards as $parent) {
				$permissions = Hakz::parseSerial($parent['permissions'], 'decode')['view'];

				if(array_key_exists(Session::get('usergroup.gid'), $permissions) && $permissions[key($permissions)] == true) {
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
			}
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
			return Response::view('errors.missing', array(), 404);
		}

		// Retrieve the board's children
		$children = self::fetchBoardChild($board['fid']);

		// Generate Crumbs
		$crumbs = self::generateCrumbs($board['fid']);

		/**
		 * Only allow posting and other queries to run on boards not counted as top parent
		 */
		if($board['parent'] != 0) {
			// Check post permissions
			$canPost = $usergroup['can_failbb_post'] == true && $permissions['post'][$usergroup['gid']] == true ? true : false;

			// Retrieve the board's threads
			$threads = self::fetchBoardThreads($board['fid']);
			if(is_array($threads)) {
				foreach($threads as $thread) {
					// Query the last poster of the thread
					$lastPoster = self::fetchLastPost($thread['pid'], false);

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
						'display_name' => $displayName,
						'views' => $thread['views'],
						'replies' => $threadReplies,
					);
				}
			}
		}

		// Saturate data
		$data = array(
			'fid' => $board['fid'],
			'name' => $board['name'],
			'description' => $board['description'],
			'crumbs' => $crumbs,
			'children' => $children,
			'threads' => $threadlist
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

		if(is_array($boards)) {
			foreach($boards as $board) {
				// Resets data
				$subchilds = null;

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

			return $data;
		}
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
			DB::select("SELECT p1.hash hash_r, p1.topic topic_r, p2.topic topic_m,
					p2.hash hash_m, p1.date_posted, p4.username, p4.display_name
				FROM `hkz_posts` as P1
				LEFT JOIN `hkz_posts` as P2 ON (`P1`.`reply_to` = `P2`.`pid`)
				LEFT JOIN `hkz_users` as P4 ON `P1`.`author` = `P4`.`uid`
				WHERE `p1`.`board` = ? ORDER BY `p1`.`date_posted` DESC LIMIT 1", array($id)): 
			DB::select("SELECT p1.hash hash_r, p2.topic topic_m, p2.hash hash_m,
					p2.date_posted, p4.username, p4.display_name
				FROM `hkz_posts` as P1 
				LEFT JOIN `hkz_posts` as P2 ON `P1`.`reply_to` = `P2`.`pid`
				LEFT JOIN `hkz_users` as P4 ON `P1`.`author` = `P4`.`uid`
				WHERE `p1`.`reply_to` = ? ORDER BY `p1`.`date_posted` DESC LIMIT 1", array($id));
		
		$lastpost = $lastpost[0];

		if(!is_array($lastpost)) { 
			return "<div align='center'>-</div>";
		}

		if($boardMode == true) {
			$title = empty($lastpost['topic_m']) ? 
				$lastpost['topic_r'] . " &gt;&gt; " . substr($lastpost['hash_r'], -10, 10): 
				$lastpost['topic_m'] . " &gt;&gt; " . substr($lastpost['hash_r'], -10, 10);
		} else {
			$title = substr($lastpost['hash_r'], 0, 20);
		}

		$link = isset($lastpost['hash_m']) ?
			URL::to("boards/t/" . $lastpost['hash_m'] . ".html#hash-" . $lastpost['hash_r']):
			URL::to("boards/t/" . $lastpost['hash_r'] . ".html#hash-" . $lastpost['hash_r']);

		$displayname = !empty($lastpost['display_name']) ? $lastpost['display_name'] : $lastpost['username']; 

		$data = array(
			'link' => $link,
			'date' => Hakz::parseTime($lastpost['date_posted']),
			'user' => $displayname,
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

		if(!is_array($thread)) {
			die(View::make('hakz.error'));
		}

		// Process some queries
		DB::table('posts')->where('pid', '=', $thread['pid'])->increment('views');
		$crumbs = self::generateCrumbs($thread['board'], array(
			999 => array(
				'name' => $thread['topic'],
				'link' => HTML::link('boards/t/' . $thread['hash'] . '.html', $thread['topic'])
			)
		));

		$reply = Session::get('usergroup.can_failbb_reply_thread') == true ? true : false; // If the user can reply

		// Process posts
		$posts = self::fetchThreadPosts($thread['pid']);

		// Return data
		$data = array(
			'topic'	 => $thread['topic'],
			'crumbs' => $crumbs,
			'reply' => $reply,
			'posts' => $posts,
			'hash' => $thread['hash'],
			'posts' => $posts
		);

		return $data;
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
				->get():
			DB::table('posts')
				->where('reply_to', '=', $tid)
				->where('hash', '=', $hash)
				->get();


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


		return $data;
	}

	/**
	 * Fetches the permission of a specific board
	 * @param int Board ID
	 * @return array
	 */
	public static function fetchPermissions($fid) {

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

		return Hakz::parseSerial($board['permissions'], 'decode');
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
				$tpl[$key] = "<strong>" . $val['link'] . "</strong>";
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
	 * Creates a new thread to a certain board
	 * @param array Post parameters
	 * @return array
	 */
	public static function newPost() {

		// Form the data array to insert
		$data = array(
			'author' => Session::get('uid'),
			'hash' => sha1(Input::post('topic') . time()),
			'board' => Input::post('board'),
			'contents' => Input::post('contents'),
			'ip' => $_SERVER['REMOTE_ADDR']
		);

		// Additional processing
		if(Input::has('topic') == true) {
			$thread = true;
			$data = array_merge($data, array('topic' => Input::post('topic')));
		}

		// Perm the insert query
		DB::table('user')->increment('posts')->where('uid', '=', Session::get('uid'));
		$insert = DB::table('posts')->insertGetId($data);

		/**
		 * Determine what will the system do about the next process
		 * >> The process will redirect the user if it's a thread, or print it if it's a post
		 * >> Template processing will be conducted on the controller instead
		 */ 
		if($thread == true) {
			return array('thread' => true, 'pid' => $insert);
		} else {
			return array('thread' => false, 'pid' => $insert);
		}

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
			'edit_data' => $edit
		);

		DB::table('posts')->update($data)->where('hash', '=', Input::post('hash'));
		$data = self::fetchThreadPosts(Input::post('reply_to'), Input::post('hash'));

		return $data;
	}

}