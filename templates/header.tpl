<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="description" content="{$head.description}"/>
    <meta name="keywords" content="{$head.keywords}"/> 
    <meta name="author" content="{$head.author}"/> 
    
    <link rel="stylesheet" type="text/css" href="{$application.home_url}media/css/jquery.fancybox-1.3.4.css" media="screen"/>
    
    {foreach $page.styles item=style}
    <link rel="stylesheet" type="text/css" href="{$application.home_url}media/css/{$style}" media="screen"/>
    {/foreach}
    
    <script type='text/javascript' src='http://code.jquery.com/jquery.min.js'></script>
    <script type='text/javascript' src='{$smarty.const.SERVER_URL}/media/js/jquery-ui-1.8.19.custom.min.js'></script>
    <script type='text/javascript' src='{$application.home_url}media/js/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
    
    {foreach $page.scripts item=script}
    <script type='text/javascript' src='{$application.home_url}media/js/{$script}'></script>
    {/foreach}
    
    <title>{$page.title}</title>
</head>
<body>
<div class="container">
	<div class="logo"><a href="{$smarty.const.SERVER_URL}">TickHub</a></div>
	<ul class="toolbar">
		{if isset($smarty.cookies.customer) } 
		<li><a href="/dashboard/">Dashboard</a></li>
		<li><a href="/settings/">Settings</a></li>
		<li><a href="/logout/">Logout</a></li>
		{else}
		<li><a href="#" onclick="return displayLoginBox()">Login</a></li>
		<li><a href="/signup/" >Create an account</a></li>
		{/if}
	</ul>
	<div class="clearer"><span></span></div>
</div>
<div class="container"> 
