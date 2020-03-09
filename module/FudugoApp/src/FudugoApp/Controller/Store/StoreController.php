<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Store;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	Class StoreController
	{
		public function get_stores($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = $user_data['user_id'];			
			//Get store list
			$sql_store_list="SELECT * FROM employer_store_list WHERE emp_id='$uid' AND status = 0 ORDER BY emp_store_name ASC";	
		    $store_list_obj = $adapter->query($sql_store_list, $adapter::QUERY_MODE_EXECUTE);
		    $store_lists = $store_list_obj->toArray(); 
			return json_encode($store_lists);
		}

		public function get_store_by_id($store_id, $adapter)
		{
			$sql_store="SELECT * FROM employer_store_list WHERE emp_st_id='$store_id' AND status = 0 ORDER BY emp_store_name ASC";	
		    $store_obj = $adapter->query($sql_store, $adapter::QUERY_MODE_EXECUTE);
		    $store = $store_obj->toArray(); 
		    return $store[0];
		}
	}