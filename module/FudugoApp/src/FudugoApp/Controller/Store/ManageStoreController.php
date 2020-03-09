<?php
	/**
	* Develop by Rizwana Ansari 
	*/
	namespace FudugoApp\Controller\Store;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	Class ManageStoreController
	{
		public function get_stores_list($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = isset($user_data['uid']) ? $user_data['uid'] : '';
			switch ($user_data['type']) {
				case 'add-new':
					$add_new_store = $this->add_new_stores($user_data,$adapter);
					break;
				case 'edit':					
					$update_user_stores = $this->update_user_stores($user_data,$adapter);
					break;
				case 'delete':
					$delete_user_stores = $this->delete_user_stores($user_data,$adapter);
					break;

			}
			if($user_data['type']=='getByID'){	
			    $sid = isset($user_data['sid']) ? $user_data['sid'] : '';			
				$sqlGetRecord="SELECT emp_store_name,emp_store_address,emp_store_region,emp_store_zip,store_start_time,store_end_time,store_lunch_time FROM employer_store_list  WHERE emp_id='$uid' AND emp_st_id='$sid' AND status = 0 ORDER BY emp_store_name ASC"; 
			}else{
			    $sqlGetRecord="SELECT emp_st_id,emp_store_name,emp_store_address,emp_store_region,emp_store_zip,store_start_time,store_end_time,store_lunch_time FROM employer_store_list  WHERE emp_id='$uid' AND status = 0 ORDER BY emp_store_name ASC"; 
		  	}
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();		  	
		  	if(!empty($uid)){
				foreach ($getvalues as $key => $value) {
					$timelistriz=array();
					if ($value['store_start_time'] != '') {
			    		$value['store_start_time'] = unserialize($value['store_start_time']);
			    	}
			    	if ($value['store_end_time'] != '') {
			    		$value['store_end_time'] = unserialize($value['store_end_time']);
			    	}
			    	if ($value['store_lunch_time'] != '') {
			    		$value['store_lunch_time'] = unserialize($value['store_lunch_time']);
			    	}
					$results['storelist'][$key]=$value;
					/*****Make Time list day wise ****/			    	
			    		$results['storelist'][$key]['timelist'][0]['day']='Mon';
			    		$results['storelist'][$key]['timelist'][1]['day']='Tue';
			    		$results['storelist'][$key]['timelist'][2]['day']='Wed';
			    		$results['storelist'][$key]['timelist'][3]['day']='Thu';
			    		$results['storelist'][$key]['timelist'][4]['day']='Fri';
			    		$results['storelist'][$key]['timelist'][5]['day']='Sat';
			    		$results['storelist'][$key]['timelist'][6]['day']='Sun';
			    		$results['storelist'][$key]['timelist'][0]['start']=$value['store_start_time'][0]['Monday'];
			    		$results['storelist'][$key]['timelist'][1]['start']=$value['store_start_time'][1]['Tuesday'];
			    		$results['storelist'][$key]['timelist'][2]['start']=$value['store_start_time'][2]['Wednesday'];
			    		$results['storelist'][$key]['timelist'][3]['start']=$value['store_start_time'][3]['Thursday'];
			    		$results['storelist'][$key]['timelist'][4]['start']=$value['store_start_time'][4]['Friday'];
			    		$results['storelist'][$key]['timelist'][5]['start']=$value['store_start_time'][5]['Saturday'];
			    		$results['storelist'][$key]['timelist'][6]['start']=$value['store_start_time'][6]['Sunday'];
			    		$results['storelist'][$key]['timelist'][0]['lunch']=$value['store_lunch_time'][0]['Monday'];
			    		$results['storelist'][$key]['timelist'][1]['lunch']=$value['store_lunch_time'][1]['Tuesday'];
			    		$results['storelist'][$key]['timelist'][2]['lunch']=$value['store_lunch_time'][2]['Wednesday'];
			    		$results['storelist'][$key]['timelist'][3]['lunch']=$value['store_lunch_time'][3]['Thursday'];
			    		$results['storelist'][$key]['timelist'][4]['lunch']=$value['store_lunch_time'][4]['Friday'];
			    		$results['storelist'][$key]['timelist'][5]['lunch']=$value['store_lunch_time'][5]['Saturday'];
			    		$results['storelist'][$key]['timelist'][6]['lunch']=$value['store_lunch_time'][6]['Sunday'];
			    		$results['storelist'][$key]['timelist'][0]['end']=$value['store_end_time'][0]['Monday'];
			    		$results['storelist'][$key]['timelist'][1]['end']=$value['store_end_time'][1]['Tuesday'];
			    		$results['storelist'][$key]['timelist'][2]['end']=$value['store_end_time'][2]['Wednesday'];
			    		$results['storelist'][$key]['timelist'][3]['end']=$value['store_end_time'][3]['Thursday'];
			    		$results['storelist'][$key]['timelist'][4]['end']=$value['store_end_time'][4]['Friday'];
			    		$results['storelist'][$key]['timelist'][5]['end']=$value['store_end_time'][5]['Saturday'];
			    		$results['storelist'][$key]['timelist'][6]['end']=$value['store_end_time'][6]['Sunday'];			    		 		
			    }
			}
			
			
				return json_encode($results);
			
		  		
		}

		public function add_new_stores($storedata,$adapter)
		{
			
			$store_info= isset($storedata['store_info']) ? $storedata['store_info'] : '';
			//Save store info
			$uid=isset($store_info['id'])?$store_info['id']:'';
			$store_name = isset($store_info['name'])?$store_info['name']:'';
			$store_address = isset($store_info['address'])?$store_info['address']:'';
			$store_town = isset($store_info['town'])?$store_info['town']:'';
			$store_postcode = isset($store_info['postcode'])?$store_info['postcode']:'';
			$store_start_time =array();
			$store_end_time = array();
			$store_lunch_time = array();
			foreach ($store_info as $key => $store_time) {
				if (strpos($key, '_start_time') !== false) {
					$day = ucfirst(str_replace('_start_time', '', $key));
					$store_start_time[] = array($day => $store_time);
				}
				if (strpos($key, '_end_time') !== false) {
					$day = ucfirst(str_replace('_end_time', '', $key));
					$store_end_time[] = array($day => $store_time);
				}
				if (strpos($key, '_lunch_time') !== false) {
					$day = ucfirst(str_replace('_lunch_time', '', $key));
					$store_lunch_time[] =array($day => str_replace('00:','',$store_time));
				}
			}

			if (!empty($store_start_time)) {
				$store_start_time = serialize($store_start_time);
			}
			if (!empty($store_end_time)) {
				$store_end_time = serialize($store_end_time);
			}
			if (!empty($store_lunch_time)) {
				$store_lunch_time = serialize($store_lunch_time);
			}

			$sql_emp_store="INSERT INTO employer_store_list (emp_id,emp_store_name,emp_store_address,emp_store_region,emp_store_zip,store_start_time,store_end_time,store_lunch_time,date_added) values('$uid','$store_name','$store_address','$store_town','$store_postcode','$store_start_time','$store_end_time','$store_lunch_time',NOW())";
			$resultsemp = $adapter->query($sql_emp_store, $adapter::QUERY_MODE_EXECUTE);
			
		    return json_encode($resultsemp->getGeneratedValue());
		}
		
		public function update_user_stores($storedata,$adapter)
		{			
			$store_info= isset($storedata['store_info']) ? $storedata['store_info'] : '';
			//Save store info
			$uid=isset($store_info['id'])?$store_info['id']:'';
			$sid=isset($store_info['sid'])?$store_info['sid']:'';
			$store_name = isset($store_info['name'])?$store_info['name']:'';
			$store_address = isset($store_info['address'])?$store_info['address']:'';
			$store_town = isset($store_info['town'])?$store_info['town']:'';
			$store_postcode = isset($store_info['postcode'])?$store_info['postcode']:'';
			$store_start_time = array();
			$store_end_time = array();
			$store_lunch_time = array();
			foreach ($store_info as $key => $store_time) {
				if (strpos($key, '_start_time') !== false) {
					$day = ucfirst(str_replace('_start_time', '', $key));
					$store_start_time[] = array($day => $store_time);
				}
				if (strpos($key, '_end_time') !== false) {
					$day = ucfirst(str_replace('_end_time', '', $key));
					$store_end_time[] = array($day => $store_time);
				}
				if (strpos($key, '_lunch_time') !== false) {
					$day = ucfirst(str_replace('_lunch_time', '', $key));
					$store_lunch_time[] =array($day => str_replace('00:','',$store_time));
				}
			}
			if (!empty($store_start_time)) {
				$store_start_time = serialize($store_start_time);
			}
			if (!empty($store_end_time)) {
				$store_end_time = serialize($store_end_time);
			}
			if (!empty($store_lunch_time)) {
				$store_lunch_time = serialize($store_lunch_time);
			}
			
			$sql_emp_store="UPDATE employer_store_list SET emp_store_name='$store_name',emp_store_address='$store_address',emp_store_region='$store_town',emp_store_zip='$store_postcode',store_start_time='$store_start_time',store_end_time='$store_end_time',store_lunch_time='$store_lunch_time',date_added=NOW() WHERE emp_id='$uid' AND emp_st_id='$sid' ";
			$resultsemp = $adapter->query($sql_emp_store, $adapter::QUERY_MODE_EXECUTE);
			
		    return json_encode($resultsemp);
		}
		public function delete_user_stores($storedata,$adapter)
		{				
			//Save store info
			$uid=isset($storedata['uid']) ? $storedata['uid']:'';
			$sid=isset($storedata['sid']) ? $storedata['sid']:'';
			$sql_emp_store="UPDATE employer_store_list SET status = 1 WHERE  emp_id='$uid' AND emp_st_id='$sid' ";
			$resultsemp = $adapter->query($sql_emp_store, $adapter::QUERY_MODE_EXECUTE);
		    return json_encode($resultsemp);
		}

	}