
<div style="margin-top:20px">
	<table class="widefat">
		<thead>
			<tr>
				<th>
					Email Address
				</th>
				<th>
					Name
				</th>
				<th>
					Action
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>
					Email Address
				</th>
				<th>
					Name
				</th>
				<th>
					Action
				</th>
			</tr>
		</tfoot>
		<tbody>
<?php
			foreach($subscribers as $subscriber) {
?>
				<tr>
					<td>
						<?php echo $subscriber->email_address;?>
					</td>
					<td>
						<?php echo $subscriber->name;?>
					</td>
					<td>
						<?php
							if($subscriber->subscribed){
								echo '<a href="'.site_url().'/?email-subscriber='.$subscriber->email_address.'&unsubscribe">unsubscribe</a>';
							}
							else{
								echo '<a href="'.site_url().'/?email-subscriber='.$subscriber->email_address.'">subscribe</a>';
							}
						?>
					</td>
				</tr>
	
<?php
			} //end foreach
?>
		</tbody>
	</table>
	
</div>