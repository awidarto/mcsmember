<div id="form">
	<div id="form_box">
			<form method="post" action="<?php echo site_url($act_url)?>">
			<input type="hidden" name="merchant_id" value="<?php echo $merchant_id ?>" />

			<?php print form_fieldset('Application Info'); ?>
			<!--
			Merchant:<br />
			<strong><?php echo $merchant_name;?></strong><br /><br />
			-->
			Application Name:<br />
			<input type="text" name="application_name" size="50" class="form" value="<?php echo set_value('application_name'); ?>" /><br /><?php echo form_error('application_name'); ?><br />

			Application Domain:<br />
			<input type="text" name="domain" size="50" class="form" value="<?php echo set_value('domain'); ?>" /><br /><?php echo form_error('domain'); ?><br />

			Callback URL:<br />
			<input type="text" name="callback_url" size="50" class="form" value="<?php echo set_value('callback_url'); ?>" /><br /><?php echo form_error('callback_url'); ?><br />

			Fetch Detail URL:<br />
			<input type="text" name="fetch_detail_url" size="50" class="form" value="<?php echo set_value('fetch_detail_url'); ?>" /><br /><?php echo form_error('fetch_detail_url'); ?><br />

			Fetch Method:<br />
			<?php echo form_dropdown('fetch_method',$this->config->item('fetch_method'),set_value('fetch_method'))?><br />

			Application Description:<br />
			<textarea name="application_description" cols="60" rows="10"><?php echo set_value('application_description'); ?></textarea><br />

			<?php print form_fieldset_close(); ?>

			<?php print form_fieldset('Email Customization'); ?>

			Logo URL:<br />
			<input type="text" name="logo_url" size="50" class="form" value="<?php echo set_value('logo_url'); ?>" /><br /><?php echo form_error('logo_url'); ?><br />

			Signature:<br />
			<textarea name="signature" cols="60" rows="10"><?php echo set_value('signature'); ?></textarea><br />

			<?php print form_fieldset_close(); ?>

			<input type="submit" value="Add" name="add" />
				<?php
					print $back_url;
				?>
			</form>
	</div>
</div>