{include file="header.tpl" }

<div class="content signup">
	<h2>Signup to TickHub</h2>

{if isset($signed) && $signed == true } 
	<p>Congratulations, your account has been created!, <a onclick="return displayLoginBox()" href="#">click here</a> to access to your account</p>
{else}
	<form action="/signup/" method="post">
		<div>
			{if isset($signing_error) }
			<div class="error_message">{$signing_error}</div>
			{/if}
			<p><p>Please note that fields marked with an asterisk (<span class="alert"> * </span>) are required.</p></p>
			<label>Your name</label>
			<input name="_name" type="text" value="{if isset($_name)}{$_name}{/if}" />
			<label><span class="alert"> * </span> Email</label>
			<input name="_email" type="text" value="{if isset($_email)}{$_email}{/if}" />
			<label><span class="alert"> * </span> Password</label>
			<input name="_pass" type="password" value="" />
			<label><span class="alert"> * </span> Repeat you password</label>
			<input name="_pass_confirm" type="password" value="" />
			<label>&nbsp;</label>
			<input name="btn_submit" type="submit" value="Signup!" />
		</div>
	</form>
{/if}
</div>

{include file="footer.tpl"}