<?php
/*************************************************************************
Adding product category module
----------------------------------------------------------------
DeraCMS 3.0 Project
Company: Derasoft Co., Ltd
Coder: Mai Minh
Email: info@derasoft.com
Last updated: 10/05/2012
Checked by: Mai Minh (10/05/2012)
**************************************************************************/
$userInfo->checkPermission('quanly','addcategory');
$templateFile = 'managequanly.tpl.html';

include_once(ROOT_PATH.'classes/dao/quanlycategories.class.php');
include_once(ROOT_PATH."classes/data/textfilter.class.php");
include_once(ROOT_PATH.'classes/dao/fields.class.php');
include_once(ROOT_PATH.'classes/dao/languages.class.php');
$languages = new Languages($storeId);
$languagesList = $languages->getObjects(1,"`status` = 1 ",array("position"=>"ASC"),9999);
if($languagesList) $template->assign('languagesList',$languagesList);
$fields = new Fields($storeId);
$quanlyCategories = new quanlyCategories($storeId);
$topNav = array(
				$amessages['manage_website'] => '/'.ADMIN_SCRIPT.'?op=manage',
				$amessages['manage_products'] => '/'.ADMIN_SCRIPT.'?op=manage&act=quanly',
				$amessages['add_product_category'] => '');
				
$tabLink = '/'.ADMIN_SCRIPT.'?op=manage&act=quanly';
$listTabs = array($amessages['list_item'] => $tabLink.'&mod=list',
				$amessages['add_new'] => $tabLink.'&mod=add',
				$amessages['list_category'] => $tabLink.'&mod=listcategory',
				$amessages['add_product_category'] => $tabLink.'&mod=addcategory',
				$amessages['clean_trash'] => $tabLink.'&mod=cleantrash');			
$template->assign('listTabs',$listTabs);
$template->assign('currentTab',4);


$gallery_root = ROOT_PATH."upload/$storeId/";
$gallery_path = $gallery_root."quanlys/";

$result_code = $request->element('rcode');
if($result_code) $template->assign('result_code',$result_code);

# Category combo box
$categoryCombo = $quanlyCategories->generateCombo($request->element('pId'));
if($categoryCombo) $template->assign('categoryCombo',$categoryCombo);
# Get list of custom fields
$fieldList = $fields->getObjects(1,"`status`='1' AND `module`='quanlycategories'",array('position' => 'ASC'));
if($fieldList) $template->assign('fieldList',$fieldList);

# Allow some javascript
$template->assign('ckEditor',1);
if($_POST && $request->element('doo') == 'submit') { # if form is submitted
	# Validate the data input
	$validate = validateData($request);
	if($validate['invalid']) {	# data input is not in valid form
		$template->assign('error',$validate);
	} else { # Valid data input
		# check duplicate category name
		
		// if($quanlyCategories->checkDuplicate($request->element('name'),"parent_id =". $request->element('parent_id'))) {
		// 	$validate['INPUT']['name']['message'] = $amessages['name_duplicated'];
		// 	$validate['INPUT']['name']['error'] = 1;
		// 	$validate['invalid'] = 1;
		// 	$template->assign('error',$validate);
		// }
		
		# Check if gallery folder is exists
			if(!file_exists($gallery_root)) mkdir("$gallery_root");
			if(!file_exists($gallery_path)) mkdir("$gallery_path");
			# User upload
			 $userUpload = $userInfo->getUsername();
		
			#File Avatar
			$fileAvatr = isset($_FILES['avatar'])?$_FILES['avatar']:'';
			if($fileAvatr) {				
				// $img = addslashes(Filter(rand()."_".$fileAvatr['name']));
				$img = addslashes(rand()."_".$fileAvatr['name']);
				$tmp_img = $fileAvatr['tmp_name'];
				$size = $fileAvatr['size'];
				$type=strtolower(substr($img,-3));
				if(preg_match("/".ALLOW_FILE_TYPES."/",strtolower($img))) {
					# Upload
					if(isImage($img)) {
						$new_img = $img;
						move_uploaded_file($tmp_img,$gallery_path.'l_'.$img);
						if(!isJpeg($img)) $new_img = preg_replace("/(png$|bmp$|gif$)/","jpg",$img);
						resize($gallery_path,$gallery_path,'l_'.$img,'l_'.$new_img,DEFAULT_LARGE_SIZE,DEFAULT_LARGE_SQUARE,DEFAULT_PHOTO_QUALITY);
						resize($gallery_path,$gallery_path,'l_'.$img,'g_'.$img,DEFAULT_LARGE_SIZE,DEFAULT_LARGE_SQUARE,DEFAULT_PHOTO_QUALITY);
						resize($gallery_path,$gallery_path,'l_'.$img,'a_'.$new_img,DEFAULT_AVATAR_SIZE,DEFAULT_AVATAR_SQUARE,DEFAULT_PHOTO_QUALITY);
					
					
						if($img != $new_img) unlink($gallery_path,'l_'.$img);	# Delete file if it's not a JPEG
						$avatar = $new_img;
						$avatar1 = $img;
					} 
				} #/if (preg_match
			}
	
		# Check if duplicate slug
		$slug = TextFilter::urlize($request->element('name'),false,'-');
		$i = 0;
		$dup = 1;
		
		while($dup) {
			$dup = $quanlyCategories->checkDuplicate($slug.($i?'-'.$i:''),'slug');
			if($dup) $i++;
		}
		$slug .= $i?'-'.$i:'';
	
		# Everything is ok. Add data to DB
		if(!$validate['invalid']) {
			$properties = array('');			
			$properties['avatar'] = $avatar;
			$properties['sort_type'] = $request->element('sort_type');
			$properties['sort_dir'] = $request->element('sort_dir');
			$properties['display'] = $request->element('display');
			$properties['ipp'] = $request->element('ipp');
			$properties['landing'] = $request->element('landing');
			$properties['landing_page'] = $request->element('landing_page');
		
			
			if($languagesList){
				foreach($languagesList as $itemLang){
					if($itemLang->getPrefix()=="vn"){
						$langNew = '';
					}else{
						$langNew = '_'.$itemLang->getPrefix();
					}
					$properties['name'.$langNew] 	=  $request->element('name'.$langNew);
				}
			}

			# Custom fields
			foreach($fieldList as $field) {				
				$properties[$field->getName()] = $request->element("".$field->getName()."");
			}
			$data = array('store_id' => $storeId,
						  'parent_id' => $request->element('parent_id'),
						  'slug' => $slug,
						  'name' => $request->element('name'),
						  'status' => $request->element('status'),
						  'properties' => serialize($properties));
			$quanlyCategories->addData($data)
			# Operation tracking
			
			$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['add_quanly_category'],$request->element('name')),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			header('location:'.'/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=listcategory&pId=".$request->element('parent_id')."&rcode=6");
		}
	}
}

# Ham kiem tra du lieu nguoi dung nhap vao
function validateData($request) {
	global $amessages;
	include_once(ROOT_PATH.'classes/data/validate.class.php');
	$error = array();
	$validate = new Validate();
	$error['INPUT']['parent_id'] = $validate->pasteString($request->element('parent_id'));
	$error['INPUT']['name'] = $validate->validString($request->element('name'),$amessages['name']);
	$error['INPUT']['status'] = $validate->pasteString($request->element('status'));
	$error['INPUT']['name_en'] = $validate->pasteString($request->element('name_en'));
	$error['INPUT']['name_fr'] = $validate->pasteString($request->element('name_fr'));
	$error['INPUT']['name_jp'] = $validate->pasteString($request->element('name_en'));

	
	// if($error['INPUT']['name']['error']) {
	// 	$error['invalid'] = 1;
	// 	return $error;
	// }
	$error['invalid'] = 0;
	return $error;
}
?>