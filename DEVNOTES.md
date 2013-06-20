# Development Notes

* 12th, June 2013
	* 01 : Altered 'generateCrumbs' function that will allow $fid to take in an array instead of inputting 'null' then using $add
	* 02 : Altered boolean stand point in 'fetchThreadPosts' for $hash
	* 03 : Altered 'board.blade.php', on $error boolean stand point
	* 04 : Altered 'fetchBoard' for returning invalid boards
	* 05 : Added 'fetchUserPosts'; Altered routes for specification
* 13th, June 2013
	* 01 : Altered fetchBoards, fetchBoard, fetchThread, for changes in permission stand point
* 14th, June 2013
	* 01 : Altered fetchThread, to check whether or not the thread exists
* 15th, June 2013
	* 01 : Altered fetchBoard, fetchThread to display error crumb upon invalid slug query
	* 02 : Prevent thread.blade.php to show an error by adding if statement
* 16th, June 2013
	* 01 : Minor changes on fetchThread
* 17th, June 2013
	* 01 : Added function 'invalidHandler'
	* 02 : Altered generateCrumbs in handling array inputs
	* 03 : Altered list-threads.blade.php, board.blade.php to display equal table proportions
* 18th, June 2013
	* 01 : Altered 'fetchLastPost' to fix MySQL aliasing