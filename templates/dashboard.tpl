{include file="header.tpl" }

	<div class="content dashboard">
		<div class="connection_status">
			{if $page.user.github_ok}<span class="status_ok">Github OK</span>{else}<a class="status_error" href="{$page.user.github_authorize_url}">Connect to Github</a>{/if} 
			| 
			{if $page.user.tickspot_ok}<span class="status_ok">Tickspot OK</span>{else}<a class="status_error" href="#" onclick="return displayTickspotConnection();">Connect to Tickspot</a>{/if}
		</div>
		<h1>Dashboard</h1>
		{if $page.user.github_ok && $page.user.tickspot_ok }
		<div class="tickhub_entry">
			<h2>Create a new Tickspot entry</h2>
			<form id="form_tickspot_entry" action="#" method="POST" >
				<label for="tickspot_client">Client</label>
				{html_options name=tickspot_client options=$page.user.tickspot_clients onchange="return updateProjects(jQuery(this).val())"}
				<label for="tickspot_project">Project</label>
				<select name="tickspot_project" id="tickspot_project" onchange="return updateTasks(jQuery(this).val())">
					<option value="">---</option>
				</select>
				<label for="tickspot_task">Task</label>
				<select name="tickspot_task" id="tickspot_task">
					<option value="">---</option>
				</select>
				<label for="tickspot_date">Date</label>
				<input type="text" name="tickspot_date" id="tickspot_date" maxlength="10"/>
				<label for="tickspot_hours">Time</label>
				<input type="text" name="tickspot_hours" id="tickspot_hours" value="0" maxlength="2"/> <span class="float-left">:</span> <input type="text" name="tickspot_minutes" id="tickspot_minutes" value="0" maxlength="2"/>
				<label for="tickspot_message">Message</label>
				<textarea id="tickspot_message" name="tickspot_message" rows="15" cols="40"></textarea>
				<label>&nbsp;</label>
				<input type="button" name="tickspot_submit" id="tickspot_submit" value="Create Entry" onclick="return createTickspotEntry();" />
			</form>
		</div>
		<div class="github_commits">
			<h2>Latest commits from github</h2>
			<label for="github_repository">Repository</label>
			{html_options name=github_repository id=github_repository options=$page.user.github_repositories onchange="return updateEmails(jQuery(this).val())"}
			<div id="github_to_hide">
				<label for="github_users">Users</label>
				<div id="github_emails">
				{foreach $page.user.github_emails item=name key=email}
				<input type="checkbox" name="github_emails[]" value="{$email}" /> <span>{$name}</span>
				{/foreach}
				</div>
				<label>&nbsp;</label>
				<input type="button" name="github_submit" onclick="return updateCommits()" value="Get commits messages" />
			</div>
			<ul id="github_commits">
				<li>&nbsp;</li>
			</ul>
		</div>
		{else}
		<p>Please connect your Tickspot and Github accounts!</p>
		{/if}
		<div class="clearer"><span></span></div>
	</div>

{include file="footer.tpl"}