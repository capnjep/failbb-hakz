<div id='new-reply-parent-{{ time() }}'>
	<div id='new-reply-container-{{ time() }}' class='content'>
		<div>
			<form method='post' id='form-reply-{{ time() }}'>
				<!-- Hiden Inputs -->
				<input type='hidden' name='fid' value="{{ $fid }}" />
				<input type='hidden' name='hash' value="{{ $hash  }}" />
				<input type='hidden' name='_token' value='<?php echo csrf_token(); ?>' />
				<!-- Hidden Inputs -->

				<div align='center'><textarea uid='new-r-content' name='contents' style='width: 98%; height: 100px;'></textarea></div>
			</form>

			<div align='center'><button id='btnSbt-{{ time() }}' class='btn btn-primary'>Post Reply</button></div>
		</div>
	</div>
</div>
<script>
$('#btnSbt-{{ time() }}').click(function () {
	$('#new-reply-container-{{ time() }}').fadeOut('1000');
});

$('#btnSbt-{{ time() }}').click(function () {
	$('#new-reply-container-{{ time() }}').fadeOut('1000', function () { // Fades out the current content
		$.post("{{ URL::to('boards/r/' . $hash) }}", $('#form-reply-{{ time() }}').serialize(), function (data) { // Send the data
			$('#new-reply-parent-{{ time() }}').html(data);
		});
	});
});
</script>