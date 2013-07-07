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
	 * @link boards/b/{board-slug}
	 */
	Route::get('b/{name}', function($name) {
		BoardsController::__callFunc('fetchBoard', $name);
	})->where('name', '[a-z\-]+');

	/**
	 * @link boards/t/{sha1_hash}.html
	 */
	Route::get('t/{thread}.html', function($thread) {
		BoardsController::__callFunc('fetchThread', $thread);
	})->where('thread', '[a-z0-9]{40}');

	/**
	 * @link boards/p/{board-slug} [AJAX]
	 */
	Route::get('p/{name}', function($name) {
		BoardsController::__callFunc('newPost', $name);
	})->where('name', '[a-z0-9A-Z\-]');

	/**
	 * @link boards/e/{hash-slug} [AJAX]
	 */
	Route::get('e/{slug}', function($slug) {
		BoardsController::__callFunc('alterPost', $slug);
	})->where('thread', '[a-z0-9]{40}');

});

Route::controller('boards', 'BoardsController');