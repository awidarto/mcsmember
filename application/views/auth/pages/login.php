<div id="login" style="width:300px;">
	
	<h2>Login</h2>
	<div class="form_box">
			<form method="POST">
			Email:<br />
			<input type="text" name="username" value="<?php echo set_value('username'); ?>" size="50" class="form" /><?php echo form_error('username'); ?><br /><br />
			Password:<br />
			<input type="password" name="password" value="<?php echo set_value('password'); ?>" size="50" class="form" /><?php echo form_error('password'); ?><br /><br />
			<input type="submit" value="Login" name="login" />
			</form>
			<div id="registration">
				Not yet registered ? <?php echo anchor('register', 'Register'); ?>here.<br />
				Forgot Your Password ? <?php echo anchor('resetpass', 'Reset Password'); ?>here.
			</div>
	</div>
</div>