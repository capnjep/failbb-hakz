<?php

/**
 * Hakz Project in Laravel
 * @author roop <roop@hakz.co>
 * @copyright 2013, Hakz Project, http://hakz.co/
 * @package Routes
 */

/**
 * Discussion Board Routes
 * @link boards/*
 */
Route::group(array('prefix' => 'boards'), function () {

	/**
	 * Views a board
	 * @link boards/b/{board-slug}
	 */
	Route::get('b/{name}', function($name) {
		BoardsController::__callFunc('fetchBoard', $name);
	})->where('name', '[a-z0-9\-]+');

	/**
	 * Views a thread
	 * @link boards/t/{sha1_hash}.html
	 */
	Route::get('t/{post}.html', function($post) {
		BoardsController::__callFunc('fetchThread', $post);
	})->where('post', '[a-z0-9]{40}');

	/**
	 * Edits a specific post
	 * @link boards/e/{sha1_hash}
	 */
	Route::post('e/{post}', array('before' => 'csrf', function($post) {
		BoardsController::__call('editPost', $post);
	}))->where('post', '[a-z\-]+');

	/**
	 * Posts a new thread
	 * @link boards/p/{board-slug}
	 */
	Route::post('p/{slug}', array('before' => 'csrf', function($slug) {
		BoardsController::__callFunc('newThread', $slug);
	}))->where('slug', '[a-z\-]+');

	/**
	 * Replies to a thread
	 * @link boards/r/{sha1_hash}
	 */
	Route::post('r/{hash}', array('before' => 'csrf', function($hash) {
		BoardsController::__callFunc('newReply', $hash);
	}))->where('thread', '[a-z0-9]{40}');

});

Route::controller('boards', 'BoardsController');
