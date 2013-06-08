<div style='padding: 10px;'>
	<h4>{{ $name }}</h4>
	<span style='font-size: 10px;'>{{ $description }}</span>
</div>

<!-- Children boards (S) -->
<div style='padding: 10px; background: rgba(0, 0, 0, 0.5);'>
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
<!-- Children boards (E) -->