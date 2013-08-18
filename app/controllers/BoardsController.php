<?php

/**
 * Hakz Project in Laravel
 * @author roop <roop@hakz.co>
 * @copyright 2013, Hakz Project, http://hakz.co/
 * @package Discussion Board Controller
 */

class BoardsController extends BaseController {

	/**
	 * Calls a local function that cannot be served with Laravel's presets
	 * @param string Function name
	 * @param string Parameters
	 */
	public static function __callFunc($method, $params) {
		$params = is_array($param) ? implode('\', \'', $params) : $params;
		eval("self::$method('$params');");
	}

	/**
	 * Discussion board index page
	 * @link boards/index
	 */
	public function getIndex() {
		$data = Boards::fetchBoards();

		$content = View::make('boards.index', array('content' => $data));
		return $this->layout->with(array('content' => $content));
	}

	/**
	 * Fetches a specific board
	 * @link boards/b/{board-slug}
	 */
	private static function fetchBoard($name) {
		$data = Boards::fetchBoard($name);

		$content = View::make('boards.board', $data);
		echo View::make('hakz.scaffolding')->with(array('content' => $content));
	}

	/**
	 * Fetches a specific thread
	 * @link boards/t/{sha1-slug}
	 */
	private static function fetchThread($slug) {
		$data = Boards::fetchThread($slug);

		$content = View::make('boards.thread', $data);
		echo View::make('hakz.scaffolding')->with(array('content' => $content));
	}

	/**
	 * Edits a user post [AJAX]
	 * @link board/e/{hash}
	 */
	private static function alterPost($hash) {

	}

	/**
	 * Adds a new reply to a thread [AJAX]
	 * @link board/r/{thread-hash}
	 */
	private static function newReply($hash) {
		$return = Boards::newPost();
		$reply = Boards::fetchThreadPosts($return['reply_to'], $return['hash']);

		$postcount = DB::table('posts')->where('reply_to', '=', $return['reply_to'])->orWhere('pid', '=', $return['reply_to'])->count();
		$page = ceil($postcount / Config::get('failbb.items'));
		$redirect = $page > 1 ? URL::to("boards/t/{$return['thash']}.html?page={$page}&time=".time()."#hash-" . $return['hash']) : "?time=".time()."#hash-" . $return['hash'];

		echo "<script>window.location.href = '".$redirect."';</script>";
	}

	/**
	 * Submits a user thread [AJAX]
	 * @link board/p/{board-slug}
	 */
	private static function newThread($board) {

		// Preprocess if there is not 'fid' present
		if(!Input::has('fid')) {
			$data = Boards::preNewThread($board);

			echo View::make('boards.new-thread', $data);
			return true;
		}

		$return = Boards::newPost(); // Call this simply
		echo "<script>window.location.href = '" . URL::to('boards/t/' . $return['hash'] . '.html') . "';</script>";
	}

}ss