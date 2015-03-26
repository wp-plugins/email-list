<div style="margin-top:20px">
	<table class="widefat">
		<thead>
			<tr>
				<th>
					Email Title
				</th>
				<th>
					Email Post Id
				</th>
				<th>
					Date Sent
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>
					Email Title
				</th>
				<th>
					Email Post Id
				</th>
				<th>
					Date Sent
				</th>
			</tr>
		</tfoot>
		<tbody>
<?php
			foreach($sent_emails as $sent_email) {
?>
				<tr>
					<td>
						<?php echo $sent_email->email_title; ?>
					</td>
					<td>
						<?php echo $sent_email->email_id; ?>
					</td>
					<td>
						<?php echo $sent_email->time_sent; ?>
					</td>
				</tr>
	
<?php
			} //end foreach
?>
		</tbody>
	</table>
	
</div>