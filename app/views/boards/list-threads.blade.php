<tr class='bgf_000' valign='top'>
	<td width='1%'></td>
	<td>
		<a href='{{ Request::root() }}/boards/t/{{ $thread['hash'] }}.html'>{{ $thread['topic'] }}</a>
		<div style='font-size: 9px;'>Posted on {{ $thread['posted_on'] }}
	</td>
	<td>{{ $thread['display_name'] }}</td>
	<td>{{ $thread['views'] }}</td>
	<td>{{ $thread['replies'] }}</td>
	<Td>{{ $thread['last_post'] }}</td>
</tr>