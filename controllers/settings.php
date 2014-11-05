<?php

$user = User::getLoggedUser();
if ( $user == null ) {
	header('Location: ' . SERVER_URL);
	die();
}

$smarty->assign('page', array(
    'title' => 'Settings - ' . APPLICATION_NAME,
    'styles' => array(
	'style.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'style.css'))
    ),
    'scripts' => array(
	'tickhub.js'
    )
));

$smarty->display('settings.tpl');

