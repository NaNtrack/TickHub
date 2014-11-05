<div class="lightbox tickspot_box">
	<span class="title">Connect to Tickspot</span>
	<form id="tickspot_form" action="/ajax/tickpost_connect/" method="POST">
		<label for="tickspot_company">Tick URL</label>
		<input type="text" name="tickspot_company" id="tickspot_company" value="" /> <span class="company">.tickspot.com</span><br/>
		<label for="tickspot_email">Email</label>
		<input type="text" name="tickspot_email" id="tickspot_email" value="" /><br/>
		<label for="tickspot_password">Password</label>
		<input type="password" name="tickspot_password" id="tickspot_password" value="" /><br/>
		<label>&nbsp;</label>
		<input type="submit" name="tickspot_submit" id="tickspot_submit" value="Connect!"/>
	</form>
</div>