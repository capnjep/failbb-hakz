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
		$params = is_array($param) ? implode('\'', $params) : $params;
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
	 * Edits a user post
	 * @link board/e/{hash}
	 */
	private static function alterPost($hash) {

	}

	/**
	 * Submits a user post
	 * @link board/p/{board-slug}
	 */
	private static function newPost($board) {

	}

}