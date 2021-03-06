
<div class="row amun-service-login-register">

	<form id="register" method="POST" action="<?php echo $self; ?>">

	<input type="hidden" name="longitude" id="longitude" value="" />
	<input type="hidden" name="latitude" id="latitude" value="" />

	<div class="col-md-8 amun-service-login-register-description">

		<h3>Register at <?php echo $registry['core.title']; ?></h3>

		<?php if($registry['login.registration_enabled']): ?>

			<p>Here you can register an new account. After registration we will send
			you an <b>Email</b> to the provided address with an activation link. You
			have to activate your account within the next 24 hours.</p>

			<hr />

			<p><b><?php echo $registry['core.title']; ?></b> takes privacy serious. Your Email
			address and password will be saved <a href="http://wikipedia.org/wiki/Sha1">SHA-1</a> + <a href="http://en.wikipedia.org/wiki/Password_salt">Salt</a>
			encrypted. After the registration you can attach additional public informations
			to your account.</p>

		<?php else: ?>

			<p><b>Note</b>: Registration was disabled for <?php echo $registry['core.title']; ?>.
			Please contact the website administrator in order to get an account.</p>

		<?php endif; ?>

	</div>

	<div class="col-md-4 amun-service-login-register-form">

		<h3>Register</h3>

		<?php if(isset($success) && $success === true): ?>

			<div class="alert alert-success">You have successful registered. We have send you an activation email to the provided address. Please activate your account in the next 24 hours.</div>

		<?php else: ?>

			<?php if(isset($error)): ?>
			<div class="alert alert-danger">
				<img src="<?php echo $base; ?>/img/icons/login/exclamation.png" />
				<?php echo $error; ?>
			</div>
			<?php endif; ?>

			<p>
				<label for="name">Name:</label>
				<input type="text" name="name" id="name" value="<?php echo isset($name) ? $name : ''; ?>" maxlength="255" required="required" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> class="form-control" />
			</p>

			<p>
				<label for="identity">Email:</label>
				<input type="email" name="identity" id="identity" value="<?php echo isset($identity) ? $identity : ''; ?>" maxlength="255" required="required" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> class="form-control" />
			</p>

			<p>
				<label for="pw">Password:</label>
				<input type="password" name="pw" id="pw" value="" maxlength="64" required="required" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> class="form-control" />
			</p>

			<p>
				<label for="pwRepeat">Password repeat:</label>
				<input type="password" name="pwRepeat" id="pwRepeat" value="" maxlength="64" required="required" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> class="form-control" />
			</p>

			<p>
				<label for="captcha">Captcha:</label><br />
				<img src="<?php echo $captcha; ?>" alt="Captcha" class="form-captcha" id="amun-service-login-register-form-captcha" /><br />
				<input type="text" name="captcha" id="captcha" value="" maxlength="64" required="required" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> class="form-control" />
			</p>

			<p>
				<input class="btn btn-primary" type="submit" id="register" name="register" value="Register" <?php echo !$registry['login.registration_enabled'] ? 'disabled="disabled"' : ''; ?> />
			</p>

		<?php endif; ?>

	</div>

	</form>

	<hr />

</div>


<script type="text/javascript">
$(document).ready(function(){

	// get location if available
	var hasLocation = false;

	if(document.cookie)
	{
		var cookies = document.cookie.split(";")

		for(var i = 0; i < cookies.length; i++)
		{
			parts = cookies[i].split('=');

			if(parts.length == 2)
			{
				if(parts[0] == 'longitude' || parts[0] == 'latitude')
				{
					$('#' + parts[0]).val(parts[1]);

					hasLocation = true;
				}
			}
		}
	}

	if(!hasLocation && typeof(navigator.geolocation) != 'undefined')
	{
		navigator.geolocation.getCurrentPosition(function(position){

			$('#longitude').val(position.coords.longitude);
			$('#latitude').val(position.coords.latitude);

			document.cookie = "longitude=" + escape(position.coords.longitude);
			document.cookie = "latitude=" + escape(position.coords.latitude);

		});
	}

});
</script>


