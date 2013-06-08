<div id='new-reply-parent-{{ time()  }}'>
	<div id='new-reply-container-{{ time() }}>' class='content'>
		<div>
			<form method='post' id='form-reply-{{ time() }}'>
				<!-- Hiden Inputs -->
				<input type='hidden' name='fid' value="{{ $fid }}" />
				<input type='hidden' name='hash' value="{{ $hash  }}" />
				<input type='hidden' name='submit' value='1' />
				<!-- Hidden Inputs -->

				<div align='center'><textarea uid='new-r-content' name='contents' style='width: 98%; height: 100px;'></textarea></div>
				<div align='center'><span id='btn-submitNewReply' class='btn btn-primary'>Post Reply</span></div>
			</form>
		</div>
	</div>
</div>
<script>
$('#btn-closeNewReply').click(function () {
	$('#new-reply-container-{{ time() }}').fadeOut('1000', function() {
		$('#new-reply-parent-{{ time() }}').prepend(loader);
	});
});

$('#btn-submitNewReply').click(function () {
	$('#new-reply-container-{{ time() }}').fadeOut('1000', function () { // Fades out the current content
		$('#new-reply-parent-{{ time() }}').prepend(loader); // Shows the loader
		$.post("{{ URL::to('boards/new_reply') }}", $('#form-reply-{{ time() }}').serialize(), function (data) { // Send the data
			$('#loader_gif').remove();
			$('#new-reply-parent-{{ time() }}').html(data);
		});
	});
});

$("[uid='new-r-content']").change(function () {
	height( $("[new-r-content']")[0].scrollHeight);
})
</script>