<div class="lightbox login_box">
	<span class="title">Login to {$application.name}</span>
	<form action="/ajax/login/" id="login_form" method="POST">
		<label for="user_email" >Email</label>
		<input type="text" name="user_email" id="user_email" value="" />
		<label for="user_password" >Password</label>
		<input type="password" name="user_password" id="user_password" />
		<label>&nbsp;</label>
		<input type="submit" name="login_submit" id="login_submit" value="Login" />
	</form>
</div>