# Development Notes

* 26th, August 2013
	* 01 : File : Boards.php
		* `accessibleBoards` extended to contain child boards from parent accessible boards
		* Furnished `fetchUserPosts` with new logic to prevent overuse of `fetchPermissions` 
	* 02 : File : Boards.php
		* Furnished `fetchPermission` to prevent callback inconsistency
* 18th, August 2013
	* 01 : File : BoardsController.php
		* Furnished `__callFunc`
		* Furnished "new thread" (c/m)Function
		* Furnished "new reply" (c/m)Function
	* 02 : File : Boards.php
		* Furnished `fetchBoards` for efficiency
		* Furnished querying of permissions, get from Cache after being called once
		* Furnished redirect mechanism for fetchLastPost pointing it to the correct page
		* Added thread pagination
		* Added pre-process function before posting
	* 03 : File : routes.php
		* Furnished routes.php
	* 04 : Folder : views/*
		* Furnished views
* 7th, July 2013
	* 01 : File : Boards.php
		* Altered `fetchUserPosts` to enable permission settings
		* Added `fetchNumberOfPosts`
		* Fixed naming inconsistencies
	* 02 : File : routes.php
		* Grouped routing for clean organization
		* Added REGEX for post route
	* 03 : views/*
		* Fixed inconsistencies
* 18th, June 2013
	* 01 : Altered `fetchLastPost` to fix MySQL aliasing
* 17th, June 2013
	* 01 : Added function `invalidHandler`
	* 02 : Altered `generateCrumbs` in handling array inputs
	* 03 : Altered list-threads.blade.php, board.blade.php to display equal table proportions
* 16th, June 2013
	* 01 : Minor changes on `fetchThread`
* 15th, June 2013
	* 01 : Altered `fetchBoard`, `fetchThread` to display error crumb upon invalid slug query
	* 02 : Prevent thread.blade.php to show an error by adding if statement
* 14th, June 2013
	* 01 : Altered `fetchThread`, to check whether or not the thread exists
* 13th, June 2013
	* 01 : Altered `fetchBoards`, `fetchBoard`, `fetchThread`, for changes in permission stand point
* 12th, June 2013
	* 01 : Altered `generateCrumbs` function that will allow $fid to take in an array instead of inputting `null` then using $add
	* 02 : Altered boolean stand point in `fetchThreadPosts` for $hash
	* 03 : Altered `board.blade.php`, on $error boolean stand point
	* 04 : Altered `fetchBoard` for returning invalid boards
	* 05 : Added `fetchUserPosts`, Altered routes for specification