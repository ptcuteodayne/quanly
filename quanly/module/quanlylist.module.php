<?php

/*************************************************************************
Car listing module
----------------------------------------------------------------
DeraCMS 3.0 Project
Company: Derasoft Co., Ltd                                  
Email: info@derasoft.com                                    
Last updated: 10/05/2012
Checked by: Mai Minh (10/05/2012)
 **************************************************************************/
$userInfo->checkPermission('quanly', 'list');
// echo "alo";
$templateFile = 'managequanly.tpl.html';
include_once(ROOT_PATH . 'classes/dao/quanly.class.php');
include_once(ROOT_PATH . 'classes/dao/quanlycategories.class.php');
include_once(ROOT_PATH . 'classes/dao/projects.class.php');
include_once(ROOT_PATH . 'classes/dao/mass.class.php');
$mass = new Mass($storeId);
if ($mass) $template->assign('mass', $mass);
$projects = new Projects();
$quanly = new QuanLy($storeId);
$QuanLyCategories = new QuanLyCategories($storeId);
$topNav = array(
	$amessages['manage_website'] => '/' . ADMIN_SCRIPT . '?op=manage',
	$amessages['manage_products'] => '/' . ADMIN_SCRIPT . '?op=manage&act=quanly',
	$amessages['list_item'] => ''
);

$tabLink = '/' . ADMIN_SCRIPT . '?op=manage&act=quanly';
$listTabs = array(
	$amessages['list_item'] => $tabLink . '&mod=list',
	$amessages['add_new'] => $tabLink . '&mod=add',
	$amessages['list_category'] => $tabLink . '&mod=listcategory',
	$amessages['add_product_category'] => $tabLink . '&mod=addcategory',
	$amessages['clean_trash'] => $tabLink . '&mod=cleantrash'
);
$template->assign('listTabs', $listTabs);
$template->assign('currentTab', 1);



# Get parameters
$items_per_page = $request->element('ipp') ? $request->element('ipp') : DEFAULT_ADMIN_ROWS_PER_PAGE;
if ($items_per_page) $template->assign('ipp', $items_per_page);
$page = $request->element('pg') ? $request->element('pg') : 1;
if ($page) $template->assign('pg', $page);
$sort_key = $request->element('sk') ? $request->element('sk') : 'id';
if ($sort_key) $template->assign('sk', $sort_key);
$sort_direction = $request->element('sd') ? $request->element('sd') : 'DESC';
if ($sort_direction) $template->assign('sd', $sort_direction);
$do = $request->element('doo') ? $request->element('doo') : '';
if ($do) $template->assign('do', $do);
$dk = $request->element('dk') ? $request->element('dk') : '';
if ($dk) $template->assign('dk', $dk);
$kw = $request->element('kw') ? $request->element('kw') : '';
if ($kw) $template->assign('kw', $kw);
$pId = $request->element('pId', '-1');
if ($request->element('quanly_status') != "") {
	$quanly_status = $request->element('quanly_status');
} else {
	$quanly_status = "-1";
}
if ($quanly_status) $template->assign('quanly_status', $quanly_status);

if ($pId > 0) {
	$gfId = $quanlyCategories->getParentIdFromId($pId);
	$template->assign('pId', $pId);
	$template->assign('gfId', $gfId);
	$topNav[$amessages['list_item']] = '/' . ADMIN_SCRIPT . '?op=manage&act=quanly&mod=list';
	$topNav[$quanlyCategories->getNameFromId($pId)] = '';
}

# Build WHERE condition
$condition = $pId > 0 ? "`cat_id` = '$pId'  " : "1>0 AND `status` <> '3'";
if ($kw) $condition = "(`id`='$kw' OR `slug` LIKE '%$kw%' OR `sku` LIKE '%$kw%' OR `name` LIKE '%$kw%' OR `price` LIKE '%$kw%')";
if ($dk) $condition .= " AND ($dk)";
if ($quanly_status != "-1") $condition .= " AND `id_tab` = $quanly_status";
$pages_condition = "`store_id` = '$storeId' AND ($condition)";
$sort = array($sort_key => $sort_direction);

# Page navigation
$rowsPages = $quanly->getNumItems('id', $pages_condition, $items_per_page);
$template->assign('rowsPages', $rowsPages);
if ($page < 1) $page = 1;
if ($page > $rowsPages['pages']) $page = $rowsPages['pages'];
$start_num = ($page - 1) * $items_per_page + 1;
$template->assign('startNum', $start_num);
$url = '/' . ADMIN_SCRIPT . "?op=manage&act=quanly&mod=list&doo=$do&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pId=$pId&pg=%d&dk=$dk&quanly_status=$quanly_status";
$pager = Url::genPager($url, $rowsPages['pages'], $page);
$template->assign('pager', $pager);
# Get objects
$listItems = $quanly->getObjects($page, $condition, $sort, $items_per_page);
if ($listItems) $template->assign('listItems', $listItems);
# Result code
$result_code = $request->element('rcode');
if ($result_code) $template->assign('result_code', $result_code);
$error_code = $request->element('ecode');
if ($error_code) $template->assign('error_code', $error_code);
# Link
$link = '/' . ADMIN_SCRIPT . "?op=manage&act=quanly&mod=list&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pId=$pId&pg=$page&dk=$dk&quanly_status=$quanly_status";
$template->assign('link', $link);

#bottom Action Combo
$categoryCombo = $quanlyCategories->generateCombo($pId, 1);
if ($categoryCombo) $template->assign('categoryCombo', $categoryCombo);

# ALlow URL popup
$template->assign('urlPopup', 1);

if ($_POST) {
	switch ($do) 
		{
			case 'sale':

				$userInfo->checkPermission('quanly', 'add');
				$id = $request->element('iddiscount');
				if ($id) {
					$quanlyInfo = $quanly->getObject($id);
				$properties = [];
				$properties = $quanlyInfo->getProperties();
				$properties['discount'] = $request->element('discount');
				$data = array('properties' => serialize($properties));
				$quanly->updateData($data, $id);
				$result_code = 8;
				}
				break;
			case 'duplicate':	
				$userInfo->checkPermission('quanly', 'add');
				$id = $request->element('id');
				if ($id) {
					$quanlyInfo = $quanly->getObject($id);
					$property = $quanlyInfo->getProperties();
					$properties = array(
						'user_upload' =>  $userInfo->getUsername(),
						'avatar' => '',
						'photos' => '',
						'videos' => '',
						'files' =>  ''
					);
					$slug = $quanlyInfo->getSlug();
					$cat_id = $quanlyInfo->getCatId();
					# Check if duplicate slug
					include_once(ROOT_PATH . 'classes/data/textfilter.class.php');
					$slug = TextFilter::urlize($slug, false, '-');
					$i = 0;
					$dup = 1;
					while ($dup) {
						$dup = $quanly->checkDuplicate($slug . ($i ? '-' . $i : ''), 'slug', "cat_id = '$cat_id'");
						if ($dup) $i++;
				}
				$slug .= $i ? '-' . $i : '';

				$data = array(
					'store_id' => $storeId,
					'cat_id' => $quanlyInfo->getCatId(),
					'slug' => $slug,
					'name' => $quanlyInfo->getName(),
					'status' => $quanlyinfo->getStatus(),
					'properties' => serialize($properties)
				);
				$quanly->addData($data);
				$result_code = 8;
				# Operation tracking
				$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['duplicate_quanly'], $quanlyinfo->getName()), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
			}
			break;
		case 'enable':
			$userInfo->checkPermission('quanly', 'edit');
			$id = $request->element('id');
			if ($id) {
				$quanly->changeStatus($id, S_ENABLED);
				$result_code = 1;
				# Operation tracking
				$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['enable_quanly'], $quanly->getNameFromId($id)), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
			} else {
				$ids = $request->element('ids');
				if ($ids) {
					$listquanly = '';
					foreach ($ids as $id) {
						$quanly->changeStatus($id, S_ENABLED);
						$listQuanLy .= ($listQuanLy ? ',&nbsp;' : '') . $quanly->getNameFromId($id);
					}
					$result_code = 1;
					# Operation tracking
					$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['enable_quanly'], $listquanly), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'disable':
			$userInfo->checkPermission('quanly', 'edit');
			$id = $request->element('id');
			if ($id) {
				$quanly->changeStatus($id, S_DISABLED);
				$result_code = 2;
				# Operation tracking
				$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['disable_quanly'], $quanly->getNameFromId($id)), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
			} else {
				$ids = $request->element('ids');
				if ($ids) {
					$listQuanLy = '';
					foreach ($ids as $id) {
						$quanly->changeStatus($id, S_DISABLED);
						$listQuanLy .= ($listQuanLy ? ',&nbsp;' : '') . $quanly->getNameFromId($id);
					}
					$result_code = 2;
					# Operation tracking
					$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['disable_quanly'], $listquanly), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'delete':
			$userInfo->checkPermission('quanly', 'delete');
			$id = $request->element('id');
			if ($id) {
				$quanly->changeStatus($id, S_DELETED);
				$result_code = 3;
				# Operation tracking
				$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['delete_quanly'], $quanly->getNameFromId($id)), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
			} else {
				$ids = $request->element('ids');
				if ($ids) {
					$listQuanLy = '';
					foreach ($ids as $id) {
						$quanly->changeStatus($id, S_DELETED);
						$listQuanLy .= ($listQuanLy ? ',&nbsp;' : '') . $quanly->getNameFromId($id);
					}
					$result_code = 3;
					# Operation tracking
					$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['delete_quanly'], $listquanly), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'changegroup':
			$userInfo->checkPermission('quanly', 'edit');
			$ids = $request->element('ids');
			$parent_id = $request->element('parent_id');
			if (!$parent_id) $error_code = 9;
			else {
				if ($ids) {
					$listQuanLy = '';
					foreach ($ids as $id) {
						$quanly->changeCatId($id, $parent_id);
						$listQuanLy .= ($listQuanLy ? ',&nbsp;' : '') . $quanly->getNameFromId($id);
					}
					$result_code = 4;
					$pId = $parent_id;
					# Operation tracking
					$trackings->addData(array('store_id' => $storeId, 'username' => $userInfo->getUsername(), 'action' => sprintf($amessages['tracking']['change_quanly_group'], $listQuanLy, $quanlyCategories->getNameFromId($parent_id)), 'date_created' => date("Y-m-d H:i:s"), 'ip' => $_SERVER['REMOTE_ADDR']));
				} else $error_code = 5;
			}
			break;
		case 'cleantrash':
			$userInfo->checkPermission('quanly','clean',0);
			$cleanCategories = $request->element('categories'); 
			$cleanItems = $request->element('items');
            $cleanSizes = $request->element('sizes');
			if($cleanCategories == 1) { 
				$quanlyCategories->cleanTrash();
				$result_code = 5;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>$amessages['tracking']['clean_trash_quanly_category'],'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			}
			if($cleanItems == 1) {
				$quanly->cleanTrash();
				$result_code = 5;
				# Operation tracking
				$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>$amessages['tracking']['clean_trash_quanly'],'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			}
            if($cleanSizes == 1){
                include_once(ROOT_PATH.'classes/dao/sizes.class.php');
                $sizes = new Sizes();
                $sizes->cleanTrash();
            }
			break;	
		case 'cancel':
			header('location:' . '/' . ADMIN_SCRIPT . "?op=manage&act=quanly&mod=list&lang=$lang&ecode=7&pId=$pId&dk=$dk&quanly_status=$quanly_status");
			exit;
			break;
	}
header('location:' . '/' . ADMIN_SCRIPT . "?op=manage&act=quanly&mod=list&doo=$do&kw=$kw&lang=$lang&ipp=$items_per_page&sk=$sort_key&sd=$sort_direction&pg=$page&ecode=$error_code&rcode=$result_code&pId=$pId&dk=$dk&quanly_status=$quanly_status");
}else {
}
