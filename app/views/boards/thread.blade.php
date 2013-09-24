<div>
	<div class='crumbs content bgf_111'>
		<div class='clearfix'>
			<div class='pull-left'>
				{{ $crumbs }}
			</div>
			<div class='pull-right'>
				{{ $posts['links'] }}
			</div>
		</div>
	</div>
	@if(is_array($posts))
	<div id='thread_container'>
		<!-- (S) Thread Replies (S) -->
		@foreach ($posts['posts'] as $reply)
				{{ View::make('boards.list-posts')->with('post', $reply) }}
		@endforeach
		<!-- (E) Thread Replies (E) -->

		<!-- (S) Thread New Reply (S) -->
		@if ( $reply == true && Session::has('loggedIn') == true)
			{{ View::make('boards.new-reply')->with(array('fid' => $board, 'hash' => $hash)) }}
		@endif
		<!-- (E) Thread New Reply (E) -->
	</div>
	@endif
</div>

<!-- (S) Thread Javascript (S) -->
<script>
$('title').prepend('{{ $topic }} - ');

// Edit the post
$('[uid="btn-edit"]').on('click', function () {
	var postHash = $(this).attr('hash');
	$.post('boards/edit', {'hash':postHash}, function (data) {
		$('#post-' + postHash).html(data);
	});
});
</script>
<!-- (E) Thread Javascript (E) -->