<tr>
	<td width='1%'></td>
	<td>
		{{ HTML::link($board['link'], $board['name']) }}
		<div>
			{{ $board['description'] }}
			{{ $board['sub_childs'] }}
		</div>
	</td>
	<td>{{ $board['thread_count'] }}</td>
	<td>{{ $board['post_count'] }}</td>
	<td>{{ $board['last_post'] }}</td>
</tr>