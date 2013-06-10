<?php

/**
 * Hakz Project in Laravel
 * @author roop <roop@hakz.co>
 * @copyright 2013, Hakz Project, http://hakz.co/
 * @package Routes
 */


/**
 * @link boards/b/{board-slug}
 */
Route::get('boards/b/{name}', function($name) {
	BoardsController::__callFunc('fetchBoard', $name);
})->where('name', '[a-z\-]+');

/**
 * @link boards/t/{hash-slug}.html
 */
Route::get('boards/t/{thread}.html', function($thread) {
	BoardsController::__callFunc('fetchThread', $thread);
})->where('thread', '[a-z0-9]{40}');

/**
 * @link boards/p/{board-slug}
 */
Route::get('boards/p/{name}', function($name) {
	BoardsController::__callFunc('newPost', $name);
});

/**
 * @link boards/e/{hash-slug}
 */
Route::get('boards/e/{slug}', function($slug) {
	BoardsController::__callFunc('alterPost', $slug);
})->where('thread', '[a-z0-9]{40}');

Route::controller('boards', 'BoardsController');