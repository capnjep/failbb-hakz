<div post='{{ $post['hash'] }}'>
<div class='bgf_111'>
	<a id='hash-{{ $post['hash'] }}'></a>
	<div class='clearfix'>
		<div class='single-column bgf_000'>
			<div class='content clearfix'>
				<div class='float-left'>
					
					<div>{{ $post['display'] }}, Posted on {{ $post['posted_on'] }} {{ $edited }}</div>
				</div>
				<div class='float-right'>
					{{ $post['topic'] }}{{ $post['buttons'] }}
				</div>
			</div>
		</div>

		<!-- Avatar Block (s) -->
		<div class='column-15 '>
			<div class='content'>
				@if ( !empty($post['avatar']) )
				<div uid="avatar-container" >
					<div style='position: absolute; bottom: 5px; right: 5px;'>{{ $post['flag'] }}</div>
					{{ $post['avatar'] }}
				</div>
				@endif
			</div>
		</div>
		<!-- Avatar Block (e) -->

		<div class='column-85' >
			<div id='post-{{ $hash }}' class='content' style='font-size: 11px;'>
				{{ $post['contents'] }}
			</div>
		</div>
	</div>
</div>
</div>