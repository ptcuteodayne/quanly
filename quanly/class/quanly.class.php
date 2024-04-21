<?php
/*************************************************************************
Class Car
----------------------------------------------------------------
BiDo.vn Project
Last updated: 06/23/2010
Author: Mai Minh (http://maiminh.vnweblogs.com)
**************************************************************************/
include_once(ROOT_PATH."classes/database/model.class.php");
include_once(ROOT_PATH."classes/dao/quanlyinfo.class.php");

class QuanLy extends Model {
	var $table;
	var $_db;
	var $store_id;
	
	public function __construct($store_id = 0, $database = '') {
		if(!$database) {
			global $db;
			$this->_db = $db;
		} else $this->_db = $database;
		$this->table = DB_PREFIX."quanly";
		$this->store_id = $store_id;
	}
	public function QuanLy($store_id = 0, $database = '') {
		$this->__construct($store_id, $database);
	}

/* Common methods
/*-----------------------------------------------------------------------*
* Function: getObject
* Parameter: key
* Return: Info object
*-----------------------------------------------------------------------*/
	function getObject($value = '0', $key = 'id', $condition = '1>0') {
		if(!$key || !$value) return '';
		$result = $this->select('*', "`store_id` = '".$this->store_id."' AND `$key` = '$value' AND ($condition)");
		if($result) {
			$object = new QuanLyInfo
            (	$result[0]['slug'],
            $result[0]['name'],
            $result[0]['properties'],
            $result[0]['status'],
            $result[0]['slug'],
            $result[0]['cat_id'],
            $result[0]['store_id'],
            $result[0]['id']
    );
			return $object;
		}
		return 0;
	}
/*-----------------------------------------------------------------------*
* Function: getObjects
* Parameter: WHERE condition
* Return: Array of Info objects
*-----------------------------------------------------------------------*/
	function getObjects($page = 1, $condition = '1>0', $sort = array(), $items_per_page = DEFAULT_ADMIN_ROWS_PER_PAGE) {
		if(!$page) $page = 1;
		$start = ($page -1) * $items_per_page;
		$results = $this->select('*', "`store_id` = '".$this->store_id."' AND $condition", $sort, $start, $items_per_page);
		if($results) {
			$objects = array();
			foreach($results as $key => $result) {
				$objects[] = new QuanLyInfo
                (	$result[0]['slug'],
                $result[0]['name'],
                $result[0]['properties'],
                $result[0]['status'],
                $result[0]['slug'],
                $result[0]['cat_id'],
                $result[0]['store_id'],
                $result[0]['id']
        );
			}
			return $objects;
			
		}
		return 0;
	}

    function getObjectsRandom($sql='') {
	
		$results = $this->selectRandom($sql);
		if($results) {
			$objects = array();
			foreach($results as $key => $result) {
				$objects[] = new QuanLyInfo
                (	$result[0]['slug'],
                $result[0]['name'],
                $result[0]['properties'],
                $result[0]['status'],
                $result[0]['slug'],
                $result[0]['cat_id'],
                $result[0]['store_id'],
                $result[0]['id']
        );
			}
			return $objects;
			
		}
		return 0;
	}
/*-----------------------------------------------------------------------*
* Function: updateData
* Parameter: Info object
* Return: 1 if success, 0 if fail
*-----------------------------------------------------------------------*/	
	# Add record
	function addData($fields,$key = 'id') {
		$result = $this->add($fields,'$key','NULL');
		if($result) return $result;
		return 0;
	}

	# Update record
	function updateData($fields, $value = '', $key = 'id') {
		$result = $this->update($fields,"`store_id` = '".$this->store_id."' AND `$key` = '$value'");
		if($result)
			return $result;
		return 0;
	}

	# Change status
	function changeStatus($id = 0, $status = '') {
		if(!$id) return 0;
		if($this->update(array('status' => $status), "`store_id` = '".$this->store_id."' AND `id` = '$id'")) return 1;
		return 0;
	}
	# Change CatId
	function changCatId($id = 0, $home = '') {
		if(!$id) return 0;
		if($this->update(array('cat_id' => $home), "`store_id` = '".$this->store_id."' AND `id` = '$id'")) return 1;
		return 0;
	}
	 # Clean trash
     function cleanTrash()
     {
         $results = $this->select('*', "`store_id` = '" . $this->store_id . "' AND `status` = " . S_DELETED);
         if ($results) {
             $objects = array();
             foreach ($results as $key => $result) {
                 $properties = unserialize($result['properties']);
                 $avalue = $properties['avatar'];
                 if ($properties['avatar']) unlink(ROOT_PATH . "upload/" . $this->store_id . "/articles/a_" . $properties['avatar']);
                 foreach ($properties['photos'] as $pkey => $pvalue) {
                     unlink(ROOT_PATH . "upload/" . $this->store_id . "/articles/l_" . $pvalue);
                     unlink(ROOT_PATH . "upload/" . $this->store_id . "/articles/a_" . preg_replace("/(png$|bmp$|gif$)/", "jpg", $pvalue));
                 }
                 foreach ($properties['videos'] as $pkey => $pvalue) {
                     unlink(ROOT_PATH . "upload/" . $this->store_id . "/articles/" . $pvalue);
                 }
                 foreach ($properties['files'] as $pkey => $pvalue) {
                     unlink(ROOT_PATH . "upload/" . $this->store_id . "/articles/" . $pvalue);
                 }
             }
         }
         $result = $this->delete("`store_id` = '" . $this->store_id . "' AND `status` = " . S_DELETED);
         if ($result) return 1;
         return 0;
     }
	 # Return a Article name from provided ID
     function getIdFromSlug($slug='') {
		if(!$slug) return 0;
		$result = $this->select('id',"`store_id` = '".$this->store_id."' AND slug = '$slug'");
		if($result) return $result[0]['id'];
		return 0;
	}
	function getIdFromName($name='') {
		if(!$name) return 0;
		$result = $this->select('id',"`store_id` = '".$this->store_id."' AND `name` LIKE '$name'");
		if($result) return $result[0]['id'];
		return 0;
	}

	# Return a Product Name from provided slug
	function getNameFromSlug($slug='') {
		if(!$slug) return '';
		$result = $this->select('name',"`store_id` = '".$this->store_id."' AND slug = '$slug'");
		if($result) return $result[0]['name'];
		return '';
	}

	# Return a Product slug from provided ID
	function getSlugFromId($id='') {
		if(!$id) return '';
		$result = $this->select('slug',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['slug'];
		return '';
	}

	# Return a Product name from provided ID
	function getNameFromId($id='') {
		if(!$id) return '';
		$result = $this->select('name',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['name'];
		return '';
	}
	// # Return a Product price from provided ID
	// function getSKUFromId($id='') {
	// 	if(!$id) return '';
	// 	$result = $this->select('sku',"`store_id` = '".$this->store_id."' AND id = '$id'");
	// 	if($result) return $result[0]['sku'];
	// 	return '';
	// }
    /*-----------------------------------------------------------------------*
* Function: CheckDuplicate
* Parameter: Info object
* Return: 1 if key already exists, 0 if not exists
*------------------------------------------------------------------------*/
	function checkDuplicate($value = '', $key = 'name', $condition = '') {
		$result = $this->select("`$key`","`store_id` = '".$this->store_id."' AND `$key` = '$value'".($condition?" AND $condition":''));
		if($result) return 1;
		return 0;
	}

	# Return a Product name from provided ID
	function getCatIdFromId($id='') {
		if(!$id) return '';
		$result = $this->select('cat_id',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['cat_id'];
		return '';
	}
	function getProductFromPid($pId) {
		$results = $this->select('*', "`store_id` = '".$this->store_id."' AND status =1 AND `cat_id`=$pId", array('created' => 'DESC'),  $start, '');
		if($results) {
			$QuanLyInfo = array();
			foreach($results as $key => $result) {
				$QuanLyInfos[] = new QuanLyInfo 
                    (	$result[0]['slug'],
                    $result[0]['name'],
                    $result[0]['keyword'],
                    $result[0]['sapo'],
                    $result[0]['viewed'],
                    $result[0]['properties'],
                    $result[0]['status'],
                    $result[0]['store_id'],
                    $result[0]['parent_id'],
                    $result[0]['id']
            );
			}
			return $QuanLyInfos;
		}
		return '';
	}
}
?>