<?php
/*************************************************************************
Product category listing module
----------------------------------------------------------------
Derasoft CMS Project
Company: Derasoft Co., Ltd                                  
Email: info@derasoft.com                                    
Last updated: 23/08/2011
**************************************************************************/
$userInfo->checkPermission('quanly','view');
$templateFile = 'managequanly.tpl.html';
include_once(ROOT_PATH.'classes/dao/quanlycategories.class.php');
$quanlycategories = new quanlycategories($storeId);
$topNav = array(
				$amessages['manage_website'] => '/'.ADMIN_SCRIPT.'?op=manage',
				$amessages['manage_products'] => '/'.ADMIN_SCRIPT.'?op=manage&act=quanly',
				$amessages['list_products_category'] => '');
$tabLink = '/'.ADMIN_SCRIPT.'?op=manage&act=quanly';
$listTabs = array($amessages['list_item'] => $tabLink.'&mod=list',
				$amessages['add_new'] => $tabLink.'&mod=add',
				$amessages['list_category'] => $tabLink.'&mod=listcategory',
				$amessages['add_product_category'] => $tabLink.'&mod=addcategory',
				$amessages['clean_trash'] => $tabLink.'&mod=cleantrash');			
$template->assign('listTabs',$listTabs);
$template->assign('currentTab',3);
# Get parameters
$items_per_page = $request->element('ipp')?$request->element('ipp'):DEFAULT_ADMIN_ROWS_PER_PAGE;
if($items_per_page) $template->assign('ipp',$items_per_page);
$page = $request->element('pg')?$request->element('pg'):1;
if($page) $template->assign('pg',$page);
$sort_key = $request->element('sk')?$request->element('sk'):'id';
if($sort_key) $template->assign('sk',$sort_key);
$sort_direction = $request->element('sd')?$request->element('sd'):'DESC';
if($sort_direction) $template->assign('sd',$sort_direction);
$do = $request->element('doo')?$request->element('doo'):'';
if($do) $template->assign('do',$do);
$kw = $request->element('kw')?$request->element('kw'):'';
if($kw) $template->assign('kw',$kw);
$pId = $request->element('pId')?$request->element('pId'):0;
if($pId) {
	$gfId = $quanlyCategories->getParentIdFromId($pId);
	$template->assign('pId',$pId);
	$template->assign('gfId',$gfId);
	$topNav[$amessages['list_products_category']] = '/'.ADMIN_SCRIPT.'?op=manage&act=quanly&mod=listcategory';
	$topNav[$quanlyCategories->getNameFromId($pId)] = '';
}	

# Build WHERE condition
$condition = "`parent_id` = '$pId'";
if($kw) $condition = "(`id`='$kw' OR `slug` LIKE '%$kw%' OR `name` LIKE '%$kw%')";
$pages_condition = "`store_id` = '$storeId' AND ($condition)";
$sort = array($sort_key => $sort_direction);

# Page navigation
$rowsPages = $quanlyCategories->getNumItems('id', $pages_condition,$items_per_page);
$template->assign('rowsPages',$rowsPages);
if($page < 1) $page = 1;
if($page > $rowsPages['pages']) $page = $rowsPages['pages'];
$start_num = ($page-1)*$items_per_page+1;
$template->assign('startNum',$start_num);
$url = '/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=listcategory&doo=$do&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pId=$pId&pg=%d";
$pager = Url::genPager($url,$rowsPages['pages'],$page);
$template->assign('pager',$pager);

# Get objects
$listItems = $quanlyCategories->getObjects($page,$condition,$sort,$items_per_page);
if($listItems) $template->assign('listItems',$listItems);

# Result code
$result_code = $request->element('rcode');
if($result_code) $template->assign('result_code',$result_code);
$error_code = $request->element('ecode');
if($error_code) $template->assign('error_code',$error_code);

# Link
$link = '/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=listcategory&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pId=$pId&pg=$page";
$template->assign('link',$link);

#bottom Action Combo
$categoryCombo = $quanlyCategories->generateCombo($pId);
if($categoryCombo) $template->assign('categoryCombo',$categoryCombo);

# ALlow URL popup
$template->assign('urlPopup', 1);

if($_POST) {
	switch($do) {
		case 'enable':
			$userInfo->checkPermission('quanly','editcategory');
			$id = $request->element('id');
			if($id) {
				$quanlyCategories->changeStatus($id,S_ENABLED);
				$result_code = 1;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['enable_product_category'],$quanlyCategories->getNameFromId($id)),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			} else {		
				$ids = $request->element('ids');
				if($ids) {
					$listCategory = '';
					foreach ($ids as $id) {
						$quanlyCategories->changeStatus($id,S_ENABLED);
						$listCategory .= ($listCategory?',&nbsp;':'').$quanlyCategories->getNameFromId($id);
					}
					$result_code = 1;
					# Operation tracking
					$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['enable_product_category'],$listCategory),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'disable':
			$userInfo->checkPermission('quanly','editcategory');
			$id = $request->element('id');
			if($id) {
				$quanlyCategories->changeStatus($id,S_DISABLED);
				$result_code = 2;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['disable_product_category'],$quanlyCategories->getNameFromId($id)),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			} else {
				$ids = $request->element('ids');
				if($ids) {
					$listCategory = '';
					foreach ($ids as $id) {
						$quanlyCategories->changeStatus($id,S_DISABLED);
						$listCategory .= ($listCategory?',&nbsp;':'').$quanlyCategories->getNameFromId($id);
					}
					$result_code = 2;
					# Operation tracking
					$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['disable_product_category'],$listCategory),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'delete':
			$userInfo->checkPermission('quanly','deletecategory');
			$id = $request->element('id');
			if($id) {
				$rowsPages = $quanlyCategories->getNumItems('id', "`parent_id`='$id' AND `status` <> '".S_DELETED."'",1);
				if($rowsPages['rows']) {
					$error_code = 10;
				} else {
					$quanlyCategories->changeStatus($id,S_DELETED);
					$result_code = 3;
					# Operation tracking
					$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['delete_product_category'],$quanlyCategories->getNameFromId($id)),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
				}
			} else {
				$ids = $request->element('ids');
				if($ids) {
					$listCategory = '';
					$warning = 0;
					foreach ($ids as $id) {
						$rowsPages = $quanlyCategories->getNumItems('id', "`parent_id`='$id' AND `status` <> '".S_DELETED."'",1);
						if(!$rowsPages['rows']) {
							$quanlyCategories->changeStatus($id,S_DELETED);
							$listCategory .= ($listCategory?',&nbsp;':'').$quanlyCategories->getNameFromId($id);
						} else $warning = 1;
					}
					if($warning) $error_code = 10;
					else $result_code = 3;
					# Operation tracking
					$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['delete_product_category'],$listCategory),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'changegroup':
			$userInfo->checkPermission('quanly','editcategory');
			
			$ids = $request->element('ids');
			$parent_id = $request->element('parent_id');
			if($ids) {
				$listCategory = '';
				foreach ($ids as $id) {
					$quanlyCategories->changePId($id,$parent_id);
					$listCategory .= ($listCategory?',&nbsp;':'').$quanlyCategories->getNameFromId($id);
				}
				$result_code = 4;
				$pId = $parent_id;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['change_parent_product_category'],$listCategory,$quanlyCategories->getNameFromId($parent_id)),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			} else $error_code = 5;
			break;
		case 'changeposition':
			$userInfo->checkPermission('quanly','editcategory');
			$positions = $request->element('positions');
			if($positions) {
				foreach ($positions as $key=>$value) {
					$quanlyCategories->changePosition($key,$value);
				}
				$result_code = 4;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>$amessages['tracking']['change_position_product_category'],'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			} else $error_code = 5;
			break;
			
		case 'cancel':		
			header('location:'.'/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=listcategory&lang=$lang&ecode=7&pId=$pId");
			exit;
			break;
	}
	header('location:'.'/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=listcategory&doo=$do&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pg=$page&ecode=$error_code&rcode=$result_code&pId=$pId");
} else {
	
}
?>