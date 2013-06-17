<tr class='bgf_000' valign='top'>
	<td width='1%'></td>
	<td>
		{{ HTML::link("boards/t/{$thread['hash']}.html", $thread['topic']) }}
		<div style='font-size: 9px;'>Posted on {{ $thread['posted_on'] }}; By {{ $thread['display_name'] }}</div>
	</td>
	<td>{{ $thread['views'] }}</td>
	<td>{{ $thread['replies'] }}</td>
	<Td>{{ $thread['last_post'] }}</td>
</tr>