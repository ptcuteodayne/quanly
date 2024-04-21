<?php
/*************************************************************************
Class ProductCategories
----------------------------------------------------------------
BiDo.vn Project
Last updated: 06/23/2010
Author: Mai Minh (http://maiminh.vnweblogs.com)
**************************************************************************/
include_once(ROOT_PATH."classes/database/model.class.php");
include_once(ROOT_PATH."classes/dao/quanlycategoryinfo.class.php");

class QuanLyCategories extends Model {
	var $table;
	var $_db;
	var $store_id;
	public function __construct($store_id = 0, $database = '') {
		if(!$database) {
			global $db;
			$this->_db = $db;
		} else $this->_db = $database;
		$this->table = DB_PREFIX."quanly_categories";
		$this->store_id = $store_id;
		
	}
	public function QuanLyCategories($store_id = 0, $database = '') {
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
			$object = new QuanLyCategoryInfo
			(	
            $result[0]['name'],
            $result[0]['properties'],
			$result[0]['parent_id'],
            $result[0]['status'],
            $result[0]['slug'],
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
				$objects[] = new QuanLyCategoryInfo
				(
           	 	$result[0]['name'],
           	 	$result[0]['properties'],
				$result[0]['parent_id'],
            	$result[0]['status'],
            	$result[0]['slug'],
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

	# Change parent category
	function changePId($id = 0, $pId = 0) {
		if(!$id) return 0;
		if($this->update(array('parent_id' => $pId), "`store_id` = '".$this->store_id."' AND `id` = '$id'")) return 1;
		return 0;
	}


	# Clean trash
	function cleanTrash() {
		$results = $this->select('id',"`store_id` = '".$this->store_id."' AND `status` = ".S_DELETED);
		if($results) {
			include_once(ROOT_PATH."classes/dao/quanlys.class.php");
			$quanlys = new QuanLys($this->store_id);
			# Loop all DELETED categories
			foreach($results as $key => $result) {
				# Change status of all products in each category to DELETED too
				$quanlys->update(array('status' => S_DELETED),"`store_id` = '".$this->store_id."' AND `cat_id` = '".$result['id']."'");
			}	
		}
		$result = $this->delete("`store_id` = '".$this->store_id."' AND `status` = ".S_DELETED);
		if($result) return 1;
		return 0;
	}	
		
	function getParentObject($parent_id) {
		return $this->getObject($parent_id,'parent_id');
	}

	# Return a ProductCategory Id from provided ID
	function getIdFromSlug($slug='') {
		if(!$slug) return 0;
		$result = $this->select('id',"`store_id` = '".$this->store_id."' AND slug = '$slug'");
		if($result) return $result[0]['id'];
		return 0;
	}

	# Return a ProductCategory Name from provided slug
	function getNameFromSlug($slug='') {
		if(!$slug) return '';
		$result = $this->select('name',"`store_id` = '".$this->store_id."' AND slug = '$slug'");
		if($result) return $result[0]['name'];
		return '';
	}

	# Return a ProductCategory slug from provided ID
	function getSlugFromId($id='') {
		if(!$id) return '';
		$result = $this->select('slug',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['slug'];
		return '';
	}
	function getParentIdFromId($id='') {
		if(!$id) return '';
		$result = $this->select('parent_id',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['parent_id'];
		return '';
	}

	# Return a ProductCategory name from provided ID
	function getNameFromId($id='0') {
		global $amessages;
		if(!$id) return $amessages['root'];
		$result = $this->select('name',"`store_id` = '".$this->store_id."' AND id = '$id'");
		if($result) return $result[0]['name'];
		return '';
	}

# Return ProductCategory from provided parent_id
	function getAllSubCategory($pId) {
		$results = $this->select("id", "status =1 AND `parent_id` = '$pId'",array('position'=>'ASC'));
		if($results) {
			$categoryInfos = array();
			foreach($results as $key => $result) {
				$a= $result['id'];
				$categoryInfos[]=$result['id'];
			$results1 = $this->select("id", "status =1 AND `parent_id` = '$a'",array('position'=>'ASC'));	
			foreach($results1 as $key => $result_1) {
					$b = $result_1['id'];
					$categoryInfos[]=$result_1['id'];
					$results2 = $this->select("id", "status =1 AND `parent_id` = '$b'",array('position'=>'ASC'));					
			foreach($results2 as $key => $result_2) {
				$c = $result_2['id'];
				$categoryInfos[]=$result_2['id'];
					$results3 = $this->select("id", "status =1 AND `parent_id` = '$c'",array('position'=>'ASC'));
			foreach($results3 as $key => $result_3) {
					$d=$result_3['id'];
					$categoryInfos[]=$result_3['id'];
				
			}
			}		
			}
			}
			if($pId){
			return implode(",",$categoryInfos).",$pId";
			}else{
				return implode(",",$categoryInfos);
			}
			
		}
		return($pId);
	}

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
	
	function generateCombo($value='',$noroot = 0) {
		global $amessages;
		$combo = '';
		// if(!$noroot) $combo = '<option value="0"'.($value=='0'?" selected":"").'>'.$amessages['root'].'</option>';
		$results = $this->select('id,name',"`store_id` = '".$this->store_id."' AND parent_id = '0'");
		if($results) {
			foreach($results as $key => $result) {
				$combo .= "<option value='".$result['id']."'".($value==$result['id']?" selected":"").">&nbsp;&nbsp;&nbsp;l--".$result['name']."</option>";	
				$s1results = $this->select('id,name',"`store_id` = '".$this->store_id."' AND parent_id = '".$result['id']."'");
				if($s1results) {
					foreach($s1results as $key1 => $result1) {
						$combo .= "<option value='".$result1['id']."'".($value==$result1['id']?" selected":"").">&nbsp;&nbsp;&nbsp;l&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ll--".$result1['name']."</option>";
								$s1resultss = $this->select('id,name',"`store_id` = '".$this->store_id."' AND parent_id = '".$result1['id']."'");
								if($s1resultss) {
							foreach($s1resultss as $key2 => $result2) {
								$combo .= "<option value='".$result2['id']."'".($value==$result2['id']?" selected":"").">&nbsp;&nbsp;&nbsp;l&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;lll--".$result2['name']."</option>";
										$s2resultss = $this->select('id,name',"`store_id` = '".$this->store_id."' AND parent_id = '".$result2['id']."'");
										if($s2resultss) {
									foreach($s2resultss as $key3 => $result3) {
										$combo .= "<option value='".$result3['id']."'".($value==$result3['id']?" selected":"").">&nbsp;&nbsp;&nbsp;l&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;llll--".$result3['name']."</option>";
								
									}
								}
						
							}
						}
					}
				}			
			}
		}
		return $combo;
	}
}
?>