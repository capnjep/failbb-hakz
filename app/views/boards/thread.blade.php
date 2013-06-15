<div>
	<div class='crumbs content bgf_111'>
		{{ $crumbs }}
	</div>
	@if(is_array($posts))
	<div id='thread_container'>
		<!-- (S) Thread Replies (S) -->
		@foreach ($posts as $reply)
				{{ View::make('boards.list-posts')->with('post', $reply) }}
		@endforeach
		<!-- (E) Thread Replies (E) -->

		<!-- (S) Thread New Reply (S) -->
		@if ( $reply != true )
			{{ View::make('boards.new-reply')->with(array('fid' => $fid, 'hash' => $hash)) }}
		@endif
		<!-- (E) Thread New Reply (E) -->
	</div>
	@endif
</div>

<!-- (S) Thread Javascript (S) -->
<script>
$('title').prepend('{{ $topic }} - ');

// Edit the post
$('[uid="btn-edit"]').live('click', function () {
	var postHash = $(this).attr('hash');
	$.post('boards/edit', {'hash':postHash}, function (data) {
		$('#post-' + postHash).html(data);
	});
});
</script>
<!-- (E) Thread Javascript (E) -->