
<?php
/*************************************************************************
Adding product module
----------------------------------------------------------------
DeraCMS 3.0 Project
Company: Derasoft Co., Ltd
Coder: Mai Minh
Email: info@derasoft.com
Last updated: 10/05/2012
Checked by: Mai Minh (10/05/2012)
**************************************************************************/
$userInfo->checkPermission('quanly','add');
$templateFile = 'managequanly.tpl.html';
// echo "alo";
include_once(ROOT_PATH.'classes/dao/quanlycategories.class.php');
include_once(ROOT_PATH.'classes/dao/quanly.class.php');

include_once(ROOT_PATH.'classes/dao/projects.class.php');
include_once(ROOT_PATH.'classes/dao/oppros.class.php');
include_once(ROOT_PATH.'classes/dao/fields.class.php');
include_once(ROOT_PATH."classes/data/textfilter.class.php");
include_once(ROOT_PATH.'classes/dao/icoproducts.class.php');
include_once(ROOT_PATH.'classes/dao/prefericoproducts.class.php');
include_once(ROOT_PATH.'classes/dao/mass.class.php');
include_once(ROOT_PATH.'classes/dao/languages.class.php');

$languages = new Languages($storeId);
$languagesList = $languages->getObjects(1,"`status` = 1 ",array("position"=>"ASC"),9999);
if($languagesList) $template->assign('languagesList',$languagesList);
$icoproducts = new IcoProducts();
$prefericoproducts = new PreferIcoProducts();
$projects = new Projects();
$oppros = new Oppros();
$quanlyCategories = new QuanLyCategories($storeId);
$quanly = new QuanLy($storeId);
$fields = new Fields($storeId);
$mass = new Mass($storeId);
if($mass) $template->assign('mass',$mass);


# $gallery_root = ROOT_PATH."upload/$storeId/";
# $gallery_path = $gallery_root."quanly/";
$topNav = array(
				$amessages['manage_website'] => '/'.ADMIN_SCRIPT.'?op=manage',
				$amessages['quanly'] => '/'.ADMIN_SCRIPT.'?op=manage&act=quanly');

$tabLink = '/'.ADMIN_SCRIPT.'?op=manage&act=quanly';
$listTabs = array($amessages['list_item'] => $tabLink.'&mod=list',
				$amessages['add_new'] => $tablink.'&mod=add',
				$amessages['clean_trash'] => $tabLink.'&mod=cleantrash');			
$template->assign('listTabs',$listTabs);
$template->assign('currentTab',2);
$result_code = $request->element('rcode');
if($result_code) $template->assign('result_code',$result_code);

# Category combo box
$categoryCombo = $quanlyCategories->generateCombo($request->element('cat_id'),1);
if($categoryCombo) $template->assign('categoryCombo',$categoryCombo);

# Khối lượng
$massCombo = $mass->getObjects(1,"`status` = 1 ",array("id"=>"DESC"),999);
if($massCombo) $template->assign('massCombo',$massCombo);



# Get list of custom fields
$fieldList = $fields->getObjects(1,"`status`='1' AND `module`='quanly'",array('position' => 'ASC'));
if($fieldList) $template->assign('fieldList',$fieldList);
# Allow some javascript
$template->assign('ckEditor',1);

if($_POST && $request->element('doo') == 'submit') { # if form is submitted
	# Validate the data input
	$validate = validateData($request);
	if($validate['invalid']) {	# data input is not in valid form
		$template->assign('error',$validate);	
		# Category combo box
		$categoryCombo = $quanlyCategories->generateCombo($request->element('cat_id'));
		if($categoryCombo) $template->assign('categoryCombo',$categoryCombo);
		$massCombo = $mass->getObjects(1,"`status` = 1 ",array("id"=>"DESC"),999);
		if($massCombo) $template->assign('massCombo',$massCombo);
		
	} else { # Valid data input
		# check duplicate product name
		if($estore->getProperty('check_duplicate_product_name')) {
			if($products->checkDuplicate($request->element('name'),'name',"1>0")) {
				$validate['INPUT']['name']['message'] = $amessages['name_duplicated'];
				$validate['INPUT']['name']['error'] = 1;
				$validate['invalid'] = 1;
				$template->assign('error',$validate);
			}
		}
			
		# Check if duplicate slug
		$slug = TextFilter::urlize($request->element('name'),false,'-');
		$i = 0;
		$dup = 1;
		while($dup) {
			$dup = $quanly->checkDuplicate($slug.($i?'-'.$i:''),'slug',"cat_id = '".$request->element('cat_id')."'");
			if($dup) $i++;
		}
		$slug .= $i?'-'.$i:'';
		
		# Everything is ok. Add data to DB
		if(!$validate['invalid']) {
			$properties = array('');

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
						if(!isJpeg($img)) $new_img = preg_replace("/(png$|bmp$|gif$)/","png",$img);
						resize($gallery_path,$gallery_path,'l_'.$img,'l_'.$new_img,DEFAULT_LARGE_SIZE,DEFAULT_LARGE_SQUARE,DEFAULT_PHOTO_QUALITY);
						resize($gallery_path,$gallery_path,'l_'.$img,'a_'.$new_img,DEFAULT_AVATAR_SIZE,DEFAULT_AVATAR_SQUARE,DEFAULT_PHOTO_QUALITY);
						resize($gallery_path,$gallery_path,'l_'.$img,'m_'.$new_img,DEFAULT_MEDIUM_SIZE,DEFAULT_MEDIUM_SQUARE,DEFAULT_PHOTO_QUALITY);
						resize($gallery_path,$gallery_path,'l_'.$img,'t_'.$new_img,DEFAULT_THUMBNAIL_SIZE,DEFAULT_THUMBNAIL_SQUARE,DEFAULT_PHOTO_QUALITY);
						if($img != $new_img) unlink($gallery_path,'l_'.$img);	# Delete file if it's not a JPEG
						$avatar = $new_img;
					} 
				} #/if (preg_match
			}
			# Files upload
			$files = isset($_FILES['files'])?$_FILES['files']:'';
			if($files) {
				$uphotos = array();
				$uvideos = array();
				$ufiles = array();
				for($i=0; $i<count($files['name']);$i++) {
			// 		// $img = addslashes(Filter(rand()."_".$files['name'][$i]));
					$img = addslashes(rand()."_".$files['name']);
					$tmp_img = $files['tmp_name'];
					$size = $files['size'];
					$type=strtolower(substr($img,-3));
					if(preg_match("/".ALLOW_FILE_TYPES."/",strtolower($img))) {
						# Upload
						if(isImage($img)) {
							$new_img = $img;
							move_uploaded_file($tmp_img,$gallery_path.'l_'.$img);
							if(!isJpeg($img)) $new_img = preg_replace("/(png$|bmp$|gif$)/","png",$img);
							resize($gallery_path,$gallery_path,'l_'.$img,'l_'.$new_img,DEFAULT_LARGE_SIZE,DEFAULT_LARGE_SQUARE,DEFAULT_PHOTO_QUALITY);
							resize($gallery_path,$gallery_path,'l_'.$img,'m_'.$new_img,DEFAULT_MEDIUM_SIZE,DEFAULT_MEDIUM_SQUARE,DEFAULT_PHOTO_QUALITY);
							resize($gallery_path,$gallery_path,'l_'.$img,'t_'.$new_img,DEFAULT_THUMBNAIL_SIZE,DEFAULT_THUMBNAIL_SQUARE,DEFAULT_PHOTO_QUALITY);
							resize($gallery_path,$gallery_path,'l_'.$img,'a_'.$new_img,DEFAULT_AVATAR_SIZE,DEFAULT_AVATAR_SQUARE,DEFAULT_PHOTO_QUALITY);
							#if($img != $new_img) unlink($gallery_path,'l_'.$img);	# Delete file if it's not a JPEG
							$uphotos[] = "/upload/1/cars/l_" .$new_img;
						} elseif(isVideo($img)) {
							move_uploaded_file($tmp_img,$gallery_path.$img);
							$uvideos[] = $img;
						} else {
							move_uploaded_file($tmp_img,$gallery_path.$img);
							$ufiles[] = $img;
						}
					} #/if (preg_match
				} #/for($i=0;
			}
			
				# Files upload
				$filess = isset($_FILES['images'])?$_FILES['images']:'';

				if($filess) {
					$images = array();
					$uvideos = array();
					$ufiles = array();
					for($i=0; $i<count($filess['name']);$i++) {
			
						$img = addslashes(rand()."_".$filess['name'][$i]);

						$tmp_img = $filess['tmp_name'][$i];
						$size = $filess['size'][$i];
						$type=strtolower(substr($img,-3));
						if(preg_match("/".ALLOW_FILE_TYPES."/",strtolower($img))) {
							# Upload
							if(isImage($img)) {
								$new_img = $img;
								move_uploaded_file($tmp_img,$gallery_path.'l_'.$img);
								if(!isJpeg($img)) $new_img = preg_replace("/(png$|bmp$|gif$)/","png",$img);
								resize($gallery_path,$gallery_path,'l_'.$img,'l_'.$new_img,DEFAULT_LARGE_SIZE,DEFAULT_LARGE_SQUARE,DEFAULT_PHOTO_QUALITY);
								resize($gallery_path,$gallery_path,'l_'.$img,'m_'.$new_img,DEFAULT_MEDIUM_SIZE,DEFAULT_MEDIUM_SQUARE,DEFAULT_PHOTO_QUALITY);
								resize($gallery_path,$gallery_path,'l_'.$img,'t_'.$new_img,DEFAULT_THUMBNAIL_SIZE,DEFAULT_THUMBNAIL_SQUARE,DEFAULT_PHOTO_QUALITY);
								resize($gallery_path,$gallery_path,'l_'.$img,'a_'.$new_img,DEFAULT_AVATAR_SIZE,DEFAULT_AVATAR_SQUARE,DEFAULT_PHOTO_QUALITY);
								#if($img != $new_img) unlink($gallery_path,'l_'.$img);	# Delete file if it's not a JPEG
								$images[] = "/upload/1/cars/l_" .$new_img;
							} elseif(isVideo($img)) {
								move_uploaded_file($tmp_img,$gallery_path.$img);
								$uvideos[] = $img;
							} else {
								move_uploaded_file($tmp_img,$gallery_path.$img);
								$ufiles[] = $img;
							}
						} #/if (preg_match
					} #/for($i=0;
				}
			$properties = array("avatar" => $avatar,
								"photos" => $uphotos,
								"images" => $images,
								'videos' => $uvideos,
								'files' => $ufiles,
								'user_upload' => $userUpload,
								'discount' => $request->element('discount'),
								'product_origin' => $request->element('product_origin'),
								'intro' => $request->element('intro'),
								);
			
			# Custom fields
			foreach($fieldList as $field) {
				$properties[$field->getName()] = stripslashes($request->element($field->getName()));
			}
			if($request->element("massList")) {
				$stringList = implode(",", $request->element("massList"));
			}else{
				$stringList='';
			}
		
			if($languagesList){
				foreach($languagesList as $itemLang){
					if($itemLang->getPrefix()=="vn"){
						$langNew = '';
					}else{
						$langNew = '_'.$itemLang->getPrefix();
					}
					$properties['name'.$langNew] 	=  $request->element('name'.$langNew);
					$properties['price_day'.$langNew] =  $request->element('price_day'.$langNew);
					$properties['price_week'.$langNew] =  $request->element('price_week'.$langNew);
					$properties['price_month'.$langNew] =  $request->element('price_month'.$langNew);
					$properties['keyword'.$langNew] =  $request->element('keyword'.$langNew);
					$properties['intro'.$langNew] =  $request->element('intro'.$langNew);
					$properties['text_transmission'.$langNew] =  $request->element('text_transmission'.$langNew);
				}
			}

			$properties['number_seat'] = $request->element('number_seat');
			$properties['number_door'] = $request->element('number_door');

			$data = array('store_id' => $storeId,
						  'cat_id' => $request->element('cat_id'),
						  'name' => $request->element('name'),
						  'slug' => $slug,					
						  'status' => $request->element('status'),
						  'properties' => serialize($properties),
            )
			$newId = $quanly->addData($data); 
			# Operation tracking
			$trackings->addData(array('store_id'=>$storeId,'username'=>$userInfo->getUsername(),'action'=>sprintf($amessages['tracking']['add_quanly'],$request->element('name')),'date_created'=>date("Y-m-d H:i:s"),'ip'=>$_SERVER['REMOTE_ADDR']));
			header('location:'.'/'.ADMIN_SCRIPT."?op=manage&act=quanly&mod=list&pId=".$request->element('cat_id')."&rcode=6");
		}
	}	
}	
$error['invalid'] = 0;
	return $error;
?>