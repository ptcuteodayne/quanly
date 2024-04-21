<?php
/*************************************************************************
Class ProductCategory
----------------------------------------------------------------
DeraCMS 3.0 Project
Company: Derasoft Co., Ltd
Last updated: 06/23/2010
Author: Mai Minh (http://maiminh.vnweblogs.com)
**************************************************************************/

class QuanLyInfo {
	var $id;			# Primary key
	var $store_id;		# Estore id
	var $cat_id;		# Cateid
	var $slug;			# Slug
	var $name;			# Category name
	var $properties;	# Properties
	var $status;		# 0-Disabled, 1-Active, 2-Deleted, 3-Unpublished
	# Constructor
	
	function __construct($name,$slug, $cat_id, $properties, $status, $store_id = 0, $parent_id = 0, $id = 0)
	{
		$this->id = $id;
		$this->store_id = $store_id;
		$this->cat_id = $cat_id;
		$this->slug = $slug;
		$this->name = stripslashes($name);
		$this->properties = unserialize($properties);
		$this->status = $status;
	}
	public function QuanLyCategoryInfo($slug, $name,$cat_id, $properties, $status, $store_id = 0, $parent_id = 0, $id = 0)	{
		$this->__construct($slug, $name, $cat_id, $properties, $status, $store_id, $parent_id, $id);
	}
    function getId()
    {
        return $this->id;
    }
    function setId($nValue)
    {
        $this->id = $nValue;
    }

    function getStoreId()
    {
        return $this->store_id;
    }

    function setStoreId($nValue)
    {
        $this->store_id = $nValue;
    }

    function getCatId()
    {
        return $this->cat_id;
    }

    function setCatId($nValue)
    {
        $this->cat_id = $nValue;
    }

    function getCatSlug()
    {
        include_once(ROOT_PATH . "classes/dao/quanlycategories.class.php");
        $quanlyCategories = new QuanLyCategories($this->store_id);
        return $quanlyCategories->getSlugFromId($this->cat_id);
    }

    function getCatName()
    {
        include_once(ROOT_PATH . "classes/dao/quanlycategories.class.php");
        $quanlyCategories = new QuanLyCategories($this->store_id);

        return $quanlyCategories->getNameFromId($this->cat_id);
    }

    function getSlug()
    {
        return $this->slug;
    }

    function setSlug($nValue)
    {
        $this->slug = stripslashes($nValue);
    }

    function getName($lang = 'vn')
    {
        if ($lang == 'vn') return $this->name;
        elseif (isset($this->properties['name_'. $lang]) && $this->properties['name_'. $lang]) return $this->properties['name_'. $lang];
        return $this->name;
    }
    function getProperty($key)
    {
        if (isset($this->properties[$key])) return $this->properties[$key];
        return '';
    }

    function setProperty($key, $nValue)
    {
        $this->properties[$key] = $nValue;
    }
    function getProperties()
    {
        return $this->properties;
    }

    function setProperties($nValue)
    {
        $this->properties = $nValue;
    }

    function getStatus()
    {
        return $this->status;
    }

    function setStatus($nValue)
    {
        $this->status = $nValue;
    }

    function getStatusTextBackend()
    {
        global $amessages;
        return $amessages['status_product'][$this->status];
    }
# Return 1 if File is not null
function getNullFile($n)
{
	for ($i = 1; $i <= $n; $i++) {
		$key = "file" . $i;
		if ($this->$key != '')
			return 1;
	}
	return '';
}

function getIntro($lang = 'vn') {
	if($lang == 'vn')	return $this->properties['intro'];
	else return $this->properties['intro_'.$lang];
}
function getTextTransmission($lang = 'vn') {
	if ($lang == 'vn') return $this->properties['text_transmission'];
	elseif (isset($this->properties['text_transmission_'. $lang]) && $this->properties['text_transmission_'. $lang]) return $this->properties['text_transmission_'. $lang];
	return $this->properties['text_transmission'];

}
function getUrl($lang = 'vn')
{
	$url = '';
	if (URL_TYPE == 1) {    # Query string
		$url = '/' . SCRIPT . '?act=quanly&id=' . $this->id;
		return $url;
	} elseif (URL_TYPE == 2) {    # SEO
		$url = '/' . $this->slug . '-p' . $this->id . '.html';
		return $url;
	} else return '';
}

function getColors($only = 0)
{
	include_once(ROOT_PATH . 'classes/dao/albums.class.php');
	$albums = new Albums();
	$listAlbums = $albums->getObjects(1, "status=1 and pid=" . $this->id, array(), 100);
	if ($only) return $listAlbums[0];
	return $listAlbums;
}
function getColor($cid)
{
	include_once(ROOT_PATH . 'classes/dao/albums.class.php');
	$albums = new Albums();
	$info = $albums->getObject($cid);
	if ($info) return $info->getName();
	return "";
}

function getOpIds()
{
	include_once(ROOT_PATH . 'classes/dao/oppros.class.php');
	$oppros = new Oppros();
	return $oppros->getOpIdFromProId($this->id);
}

function getIcoProIds()
{
	include_once(ROOT_PATH . 'classes/dao/prefericoproducts.class.php');
	$oppros = new PreferIcoProducts();
	return $oppros->getIcoIdFromProId($this->id);
}

function getIcoProducts()
{
	include_once(ROOT_PATH . 'classes/dao/icoproducts.class.php');
	$icoproducts = new IcoProducts();
	$listIcoProducts = $icoproducts->getObjects(1, "status = 1 AND id in (select icopro_id from dc_prefer_ico_product where pro_id = " . $this->id . ")", array("position" => 'ASC'), 6);
	if ($listIcoProducts) return $listIcoProducts;
	else return '';
}
}
?>