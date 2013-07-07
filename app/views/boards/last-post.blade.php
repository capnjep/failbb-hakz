<div style='font-size: 9px'>
	<header>
		<a href='{{ $link }}'>{{ $title }}</a>
	</header>

	<section>
		By {{ HTML::link($user_link, $user) }}, on {{ $date }}
	</section>
</div>