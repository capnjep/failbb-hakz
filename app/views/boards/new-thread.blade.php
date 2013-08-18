<div id='new-thread-parent' class='bgf_111'>
	<div id='new-thread-container' class='content'>
		<div class='clearfix'>
			<div class='float-left'>
				<strong class='f_14'>New Thread</strong>
			</div>
		</div>
		<hr />
		<div>
			<form method='post' id='form-thread'>
				<table width='100%'>
					<tr valign='top'>
						<td width='50%'>
							<div><strong>Topic</strong></div>
							<div style='font-size: 9px;'>*Thread topcs must be at least 3 characters long and must related with the content</div>
						</td>
						<td>
							<input type='text' name='topic' style='width: 98%' />
						</td>
					</tr>
					
				</table>
				<!-- Hiden Inputs -->
				<input type='hidden' name='fid' value="{{ $fid }}" />
				<input type='hidden' name="_token" value="<?php echo csrf_token(); ?>">
				<!-- Hidden Inputs -->

				<div align='center'><textarea uid='new-content' name='contents' style='width: 98%; min-height: 150px'></textarea></div>
				<div align='center'><span id='btn-submitNewThread' class='btn btn-primary'>Post Thread</span></div>
			</form>
		</div>
	</div>
</div>
<script>
$('#btn-toggleAO').click(function () {
	$('#container-AO').slideToggle('fast');
});

$('#btn-submitNewThread').click(function () {
	$('#new-thread-container').fadeOut('1000', function () { // Fades out the current content
		$.post("{{ URL::to('boards/p/' . $navigation_slug) }}", $('#form-thread').serialize(), function (data) { // Send the data
			$('#thread_container').html(data);
			$('#new-thread-container').html(data);
			$('#new-thread-container').fadeIn('1000');
		});
	});
});

$("[uid='new-content']").height( $("[uid='new-content']")[0].scrollHeight);
</script>