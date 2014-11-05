<?php

require('../configs/config.php');

$user = User::getLoggedUser();

if ( $user != null ) {
	//redirect to the dashboard
	header("Location: http://".$_SERVER['SERVER_NAME'].'/dashboard/');
	exit;
}

$smarty->assign('page', array(
    'title' => 'Home - ' . APPLICATION_NAME,
    'styles' => array(
	'style.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'style.css')),
	'lightbox.css?m='.md5(filemtime(WEBROOT_DIR.'media'.DS.'css'.DS.'lightbox.css'))
    ),
    'scripts' => array(
	'tickhub.js'
    )
));

$smarty->display('index.tpl');

?>