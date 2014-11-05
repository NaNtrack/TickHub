<?php


$smarty->assign('page', array(
    'title' => 'Not Found! - ' . APPLICATION_NAME,
    'styles' => array(
	'style.css'
    )
));

$smarty->display('404.tpl');
