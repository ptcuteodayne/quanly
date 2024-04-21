<?php

include_once(ROOT_PATH.'classes/dao/quanly.class.php');
include_once(ROOT_PATH.'classes/dao/quanlycategories.class.php');
include_once(ROOT_PATH.'classes/dao/menus.class.php');
$menus = new Menus(1);

$quanly = new quanly(1);
$quanlycategories = new quanlycategories(1);

$templateFile = 'quanly.tpl.html';



$listCar = $quanly->getObjects(1,"`status`= 1",array("id"=>"DESC"),10);

if($list)$template->assign('list',$list);
$menuInfoFd = $menus->getObject(146);
$topNav = [
	[
		"name" => $messages["home_".$lang],
		"url" => "/".$lang
	],
	[
		"name" => $menuInfoFd->getName($lang),
		"type" => '1',

		"url" =>  '/'.$lang.$menuInfoFd->getUrl()
	],
];
if($topNav)$template->assign('topNav',$topNav);

if($menuInfoFd)$template->assign('menuInfoFd',$menuInfoFd);
if($menuInfoFd){
    $pageDescription    = $menuInfoFd->getIntro($lang);
    $pageKeyword        = $menuInfoFd->getKeyword($lang);
    $pageTitle          = $menuInfoFd->getName($lang);
    $pageImage          = PROTOCOL.DOMAIN.'/upload/1/resources/l_'.$estore->getProperty('logo');
    $template->assign('pageDescription', $pageDescription);
    $template->assign('pageKeyword', $pageKeyword);
    $template->assign('pageTitle', $pageTitle);
    $template->assign('pageImage', $pageImage);
}
?>