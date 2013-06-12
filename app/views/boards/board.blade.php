<div class='single-column bgf_111'>
	<div class='clearfix'>
		<div class='pull-left'>
			<div class='content'>
				{{ $crumbs }}
			</div>
		</div>
		<div class='pull-right'>
			<div class='content'>
				{{ $pagination }}
			</div>
		</div>
	</div>
</div>
@if($error === true)

@else
<div class='clearfix' style='padding: 10px;'>
	<div class='float-left'>
		<h4>{{ $name }}</h4>
		<span style='font-size: 10px;'>{{ $description }}</span>
	</div>
	<div class='float-right'>
		{{ $buttons }}
	</div>
</div>

<div id='thread_container'>
	<!-- Sub-boards of the parent -->
	@if(is_array($children))
	<div class='bgf_000'>
		<table width='100%'>
			<thead align='left'>
				<th width='45%' colspan='2'>Forum</th>
				<th width='10%'>Threads</th>
				<th width='10%'>Posts</th>
				<th width='30%'>Latest Post</th>
			</thead>
			<tbody>
				@foreach ($children[$fid] as $board)
					{{ View::make('boards.list-board')->with('board', $board) }}
				@endforeach
			</tbody>
		</table>
	</div>
	<!-- Sub-boards of the parent -->
	@endif
	<div class='clearfix'></div>

	@if(is_array($threads))
	<!-- Threads Container -->
	<table width='100%' id='thread_table'>
		<thead class='bgf_111' align='left'>
			<th width='40%' colspan='2'>Topic</th>
			<th width='15%'>Author</th>
			<th width='10%'>Views</th>
			<th width='10%'>Replies</th>
			<th width='25%'>Latest Post</th>
		</thead>
		<tbody>
			@foreach ($threads as $thread)
				{{ View::make('boards.list-threads')->with('thread', $thread) }}
			@endforeach
		</tbody>
	</table>
	<!-- Thread Container -->
	@endif

</div>

<script>
$('title').prepend('{{ $name }} - ');

$('#btn-newThread').live('click', function () {
	var fid = $(this).attr('fid');

	$('#thread_container').fadeOut('1000', function () { // Fades out the current content
		$('#thread_container').html(loader); // Shows the loader
		$('#thread_container').fadeIn('1000', function () {
			$.post("boards/new_thread", {'fid':fid}, function(data) { // Send the data
				$('#thread_container').html(data); // Display template
			});
		});
	});
});
</script>
@endif