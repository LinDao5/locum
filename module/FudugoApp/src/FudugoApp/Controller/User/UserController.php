<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\User;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use Gc\User\Role\Model;
	use Gc\User\Professional\Model as Model2;
	use Gc\User\Package\Model as Model3;
	use FudugoApp\Controller\User\RegisterController  as RegisterController;
	use GcFrontend\Controller\DistancecalculateController as Distancecal;
	use GcFrontend\Controller\FunctionsController;
	use GcFrontend\Controller\PackagePrivilegesController;
	use FudugoApp\Controller\Mails\MailsController as MailsController;
    
	Class UserController
	{		
		public function get_block_date($user_data){
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			//call  Package Privileges Controller to check eligibility of package resources 
			$packagePrivilegesController = new PackagePrivilegesController();	
			$uid = $user_data['user_id']; 
			$block_dates="";  
			$helper = new HelperController();
			if ($user_data['user_role'] == 2) {
				$is_user_pkg_allow_finance = $packagePrivilegesController->getCurrentPackagePrivilegesResources('finance',$uid,$adapter);
				$block_dates['check_previliage']=$is_user_pkg_allow_finance;

				$currentdate = date('d/m/Y');
	            $sql_booked_date = "SELECT ja.*,jp.* FROM job_action ja,job_post jp WHERE ja.f_id='$uid' AND jp.job_id=ja.job_id AND (ja.action = 3 OR ja.action = 4) AND (jp.job_status = 4 OR jp.job_status = 5)  ";
	            $obj_booked_dates = $adapter->query($sql_booked_date, $adapter::QUERY_MODE_EXECUTE);
	            $booked_dates = $obj_booked_dates->toArray();
	            $block_dates['booked'] = "";
	            if (!empty($booked_dates)) { 
	                foreach($booked_dates as $booked_date){
	                	$book_date = date("m/d/Y 5:30:00", strtotime( str_replace('/', '-', $booked_date['job_date']))); 
	                	$store_name = $this->getStoreName($booked_date['store_id'],$adapter);                   
	                    $block_dates['booked'][] = array(
	                    		'title' => $booked_date['job_title'],
	                    		'location' => $booked_date['job_address'].', '.$booked_date['job_region'].', '.$booked_date['job_zip'],
	                    		'rate' =>  $helper->formate_price($booked_date['job_rate']),
	                    		'company' => $store_name['emp_store_name'],
	                    		'startTime' => $book_date,
	                    		'endTime' => $book_date,
	                    		'allDay' => false,
	                    		'job_type' => 'web'
	                    	);
	                }
	            }
                $private_jobs = $this->get_private_jobs($uid,$adapter);
                if (!empty($private_jobs)) {
	                foreach ($private_jobs as $key => $private_job) {
	                	$private_book_date = date("m/d/Y 5:30:00", strtotime($private_job['priv_job_start_date']));  
	                	$block_dates['booked'][] = array(
	                    		'title' => $private_job['priv_job_title'],
	                    		'location' => $private_job['priv_job_location'],
	                    		'rate' =>  $helper->formate_price($private_job['priv_job_rate']),
	                    		'company' => $private_job['emp_name'],
	                    		'startTime' => $private_book_date,
	                    		'endTime' => $private_book_date,
	                    		'allDay' => false,
	                    		'job_type' => 'private'
	                    	);
	                }
	            }
                $block_dates['block'] = $this->get_not_available_date($uid,$adapter);
			}else{				
				$bookings = $this->get_emp_bookings($uid, $adapter);
				$block_dates[] = "";
				if (!empty($bookings)) {
					foreach ($bookings as $key => $booking) {

						$book_date = date("m/d/Y 5:30:00", strtotime(str_replace('/', '-', $booking['date'])));
						$block_dates[] = array(
								'title' => $booking['title'],
								'locum' => $booking['locum'],
								'rate' =>  $booking['rate'],
								'startTime' => $book_date,
								'endTime' => $book_date,
								'allDay' => false,
								'job_type' => $booking['job_type']
							);
					}

				}
			}
			
			return json_encode($block_dates);
		}

		public function get_not_available_date($uid,$adapter){
			$block_dates = '';
			$sql_block_date = "SELECT block_date FROM user_work_calender WHERE uid = '$uid'";
			$obj_block_dates = $adapter->query($sql_block_date, $adapter::QUERY_MODE_EXECUTE);
	        $block_dates_data = $obj_block_dates->current();
	        $block_dates_array = unserialize($block_dates_data['block_date']);
	        $block_dates = '';
	        if (!empty($block_dates_array)) {
	        	foreach ($block_dates_array as $key => $block_date) {
		        	$block_dates[] = date("m/d/Y 5:30:00", strtotime($block_date['date']));
		        }
	        }
	        
	        return $block_dates;
		}

		public function getStoreName($store_id,$adapter){
			$sql_store = "SELECT * FROM employer_store_list WHERE emp_st_id = '$store_id'";
			$obj_store = $adapter->query($sql_store, $adapter::QUERY_MODE_EXECUTE);
	        $store_data = $obj_store->current();
	        return $store_data;
		}

		//Get Current Month Bookings
		public function get_current_month_bookings($user_data){
			$dbController = new DbController();
			$adapter = $dbController->locumkitDbConfig();
			$uid = $user_data['user_id'];
			$user_role = $user_data['user_role'];
			$current_month_booking = '';
			if ($user_role == 2) {
				$current_month_booking['private_jobs'] = $this->get_private_jobs_current_month_booking($uid,$adapter);
				$current_month_booking['website_jobs'] = $this->get_fre_website_job_current_month_booking($uid, $adapter);	
			}elseif($user_role == 3){				
				$current_month_booking = $this->get_emp_current_month_booking($uid, $adapter);
			}						
			return json_encode($current_month_booking);			
		}

		public function get_private_jobs($uid,$adapter){
			$current_date = date("Y-m-d");
	        $sql_p_bookings = "SELECT * from freelancer_private_job WHERE f_id = $uid ORDER BY priv_job_start_date ASC";
	        $get_p_booking = $adapter->query($sql_p_bookings, $adapter::QUERY_MODE_EXECUTE);
	        $get_p_current_bookings = $get_p_booking->toArray();	       
	        return $get_p_current_bookings;
		}

		/* Get current month Website job bookings */
		public function get_fre_website_job_current_month_booking($uid, $adapter){
			$website_jobs = '';
			$helper = new HelperController();
	        $sqlGetBookings = "SELECT job_date,job_post_desc,job_rate,store_id FROM job_post WHERE job_id IN ( SELECT job_id FROM job_action WHERE f_id ='$uid' AND action = 3) and MONTH (STR_TO_DATE(job_date, '%d/%m/%Y')) = MONTH(NOW()) and YEAR (STR_TO_DATE(job_date, '%d/%m/%Y')) = YEAR(NOW()) ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') ASC";	        
	        $getBooking = $adapter->query($sqlGetBookings, $adapter::QUERY_MODE_EXECUTE);
	        $getCurrentBookings = $getBooking->toArray();
	        if (!empty($getCurrentBookings)) {
		        foreach ($getCurrentBookings as $key => $currentBookings) {
		        	$store = $this->getStoreName($currentBookings['store_id'],$adapter);
		        	$timestamp = strtotime(str_replace('/', '-', $currentBookings['job_date']) );
	                $day = date('D', $timestamp);
		        	$website_jobs[] = array(
		        			'date' => $currentBookings['job_date'], 
		        			'day' => $day,
		        			'rate' =>  $helper->formate_price($currentBookings['job_rate']),
		        			'store' => $store['emp_store_name'],
		        			'location' => $store['emp_store_address']
		        		); 
		        }
		    }
	        return $website_jobs;	        
		}

		/*Get Current Month Private Job */
		public function get_private_jobs_current_month_booking($uid, $adapter){
			$first_day_this_month = date('Y-m-01');
        	$last_day_this_month  = date('Y-m-t');
        	$private_jobs = '';
        	$helper = new HelperController();
			$sqlGetCurrentMonthPBookings = "SELECT * from freelancer_private_job WHERE priv_job_start_date >= '$first_day_this_month' AND priv_job_start_date <= '$last_day_this_month' AND f_id = $uid  ORDER BY priv_job_start_date ASC";
	        $getCurrentMonthPBooking = $adapter->query($sqlGetCurrentMonthPBookings, $adapter::QUERY_MODE_EXECUTE);
	        $getCurrentMonthPBooking = $getCurrentMonthPBooking->toArray();
	        if (!empty($getCurrentMonthPBooking)) {
	        	foreach ($getCurrentMonthPBooking as $key => $currentMonthPBooking) {
	        		$private_jobs[] = array(
	        			'date' => date('d/m/Y',strtotime($currentMonthPBooking['priv_job_start_date'])), 
	        			'day' => date('D',strtotime($currentMonthPBooking['priv_job_start_date'])),
	        			'rate' => $helper->formate_price($currentMonthPBooking['priv_job_rate']),
	        			'store' => $currentMonthPBooking['emp_name'],
	        			'location' => $currentMonthPBooking['priv_job_location']
	        		); 
	        	}
	        }	

	        return $private_jobs;
		}

		/* Get Employer All booking */
		public function get_emp_bookings($uid,$adapter){
			$helper = new HelperController();
			$sqlGetBookings = "SELECT job_id,job_title,job_date,job_post_desc,job_rate FROM job_post WHERE ( job_status = 4 OR job_status = 5 ) and e_id='$uid'  ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') ASC";
            $getBooking = $adapter->query($sqlGetBookings, $adapter::QUERY_MODE_EXECUTE);
            $getCurrentBookings = $getBooking->toArray();
            $current_month_booking = '';
            if (!empty($getCurrentBookings)) {
	            foreach ($getCurrentBookings as $key => $currentBooking) {
	            	$locum_info = $this->getLocumInfoByJobId($currentBooking['job_id'],$adapter);
	            	$current_month_booking[] = array(
	            			'title' => $currentBooking['job_title'], 
	            			'date' => date('d/m/Y',strtotime(str_replace('/', '-', $currentBooking['job_date']))), 
		        			'day' => date('D',strtotime(str_replace('/', '-', $currentBooking['job_date']))),
		        			'rate' => $helper->formate_price($currentBooking['job_rate']),
		        			'locum' => $locum_info['user_info'],
		        			'view_id' => $currentBooking['job_id'],
		        			'job_type' => $locum_info['user_type']
	            		);
	            }
	        }

	        // for privat user
	        /*$sql_userp="SELECT jp.job_id,ja.puid,jp.job_title,jp.job_date,jp.job_rate FROM job_post jp, private_user_job_action ja WHERE ( jp.job_status = 4 OR jp.job_status = 5 ) AND ( ja.status = 3 OR ja.status = 4 )  AND jp.job_id=ja.j_id and jp.e_id='$uid'";
	        $getRecordUserp 	= $adapter->query($sql_userp, $adapter::QUERY_MODE_EXECUTE);
	        $fetRecordUserp		= $getRecordUserp->toArray();
	        $countRecordUserp 	= $getRecordUserp->count();
	        if($countRecordUserp > 0){
	            foreach($fetRecordUserp as $res){
	                $current_month_booking[] = array(
	            			'title' 	=> $res['job_title'], 
	            			'date' 		=> date('d/m/Y',strtotime(str_replace('/', '-', $res['job_date']))), 
		        			'day' 		=> date('D',strtotime(str_replace('/', '-', $res['job_date']))),
		        			'rate' 		=> $helper->formate_price($res['job_rate']),
		        			'locum' 	=> 'Private Locum',
		        			'view_id' 	=> $res['job_id'],
		        			'job_type'	=> 'private'
	            		);
	            }
	        }*/

            return $current_month_booking;
		}

		/* Get Employer current month booking */
		public function get_emp_current_month_booking($uid,$adapter){
			$helper = new HelperController();
			$sqlGetBookings = "SELECT job_id,job_title,job_date,job_post_desc,job_rate FROM job_post WHERE job_status=4 and e_id='$uid' and MONTH(STR_TO_DATE(job_date, '%d/%m/%Y')) = MONTH(NOW()) and YEAR(STR_TO_DATE(job_date, '%d/%m/%Y')) = YEAR(NOW()) ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') ASC";
            $getBooking = $adapter->query($sqlGetBookings, $adapter::QUERY_MODE_EXECUTE);
            $getCurrentBookings = $getBooking->toArray();
            $current_month_booking = '';
            if (!empty($getCurrentBookings)) {
	            foreach ($getCurrentBookings as $key => $currentBooking) {
	            	$current_month_booking[] = array(
	            			'title' => $currentBooking['job_title'], 
	            			'date' => date('d/m/Y',strtotime(str_replace('/', '-', $currentBooking['job_date']))), 
		        			'day' => date('D',strtotime(str_replace('/', '-', $currentBooking['job_date']))),
		        			'rate' => $helper->formate_price($currentBooking['job_rate']),
		        			'locum' => $this->getLocumInfoByJobId($currentBooking['job_id'],$adapter),
		        			'view_id' => $currentBooking['job_id']
	            		);
	            }
	        }
            return $current_month_booking;
		}

		public function getLocumInfoByJobId($job_id,$adapter){
			$locum = '';
			$sql_fre = "SELECT firstname,lastname,id FROM user WHERE id IN ( SELECT f_id FROM job_action WHERE job_id = '".$job_id."' AND (action = '3' OR action = '4'))";
            $get_fre_info = $adapter->query($sql_fre, $adapter::QUERY_MODE_EXECUTE);
            $fre_info = $get_fre_info->toArray();
            $fre_info_count = $get_fre_info->count();
            if($fre_info_count > 0){
                $fid = $fre_info[0]['id'];
                $freelancer_name = $fre_info[0]['firstname'].' '.$fre_info[0]['lastname'];
                $locum = array('user_info'=>$freelancer_name.' ('.$fid.')','user_type' => 'web');
            }else{
                $sql_fre = "SELECT p_name,p_email,p_uid FROM private_user WHERE p_uid IN ( SELECT puid FROM private_user_job_action WHERE j_id = '".$job_id."' AND status = '3')";
                $get_fre_info = $adapter->query($sql_fre, $adapter::QUERY_MODE_EXECUTE);
                $fre_info = $get_fre_info->toArray();
              	//  $fid = $fre_info[0]['p_uid'];
				$fid = 'Private Locum';
                $freelancer_name=$fre_info[0]['p_name'];
                $locum = array('user_info'=>$freelancer_name.' ('.$fid.')','user_type' => 'private');
            }
            return $locum;
		}

		/* Manage Freelancer Calendar */
		public function set_fre_calendar($user_data){
			$dbController = new DbController();
			$adapter = $dbController->locumkitDbConfig();
			$uid = $user_data['user_id']; 
			$is_available = $user_data['is_available'];
			$date = $user_data['date'];
			$rate = ($is_available == 1) ? $user_data['rate'] : '';			
			if ($is_available == 1) {
				$response = $this->set_day_rate($uid,$date,$rate,$adapter);
			}elseif($is_available == 2){
				$response = $this->set_day_to_not_available($uid,$date,$adapter);
			}else{
				$response = false;
			}
			return $response;
		}

		public function set_day_rate($uid,$selected_date,$min_rate_date,$adapter){
			$calendar_record = $this->get_present_calendar_data($uid,$adapter);
			if (!empty($calendar_record)) {
				$blockDatesString = $calendar_record[0]['block_date'];
	  			$blockDatesArray = unserialize( $calendar_record[0]['block_date'] );
	  			
	  			foreach ($blockDatesArray as $key => $value) {
	  				if (in_array($selected_date, $value)) {
	  					if ($value['date'] == $selected_date) {
	  						unset($blockDatesArray[$key]);
	  						/* update block array by removing unblock date */
	  						$insertUpdateArray = serialize($blockDatesArray);
				  			$sqlString_data="UPDATE user_work_calender SET block_date = '$insertUpdateArray' WHERE uid = '$uid'"; 
				  			$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
	  					}
	  				}
	  			}
	  			

	  			$updateArray = array();
	  			$tempExistArray = array();
	  			$existArray = unserialize( $calendar_record[0]['available_date'] );
	  			$tempExistArray = array();
	  			$dateExist = 0;
	  			if (!empty($existArray)) {
	  				$dateExist = 2;
	  				foreach ($existArray as $key => $value) {
		  				if (in_array($selected_date, $value)) {
		  					if ($value['date'] == $selected_date) {
		  						$existArray[$key] =  array(
					  				'date' => $selected_date,
					  				'min_rate' => $min_rate_date,
					  			);	
		  					}
		  					$dateExist = 1;
		  				}
		  				
		  			}
	  				
	  			}
	  			if ($dateExist == 0) {
  					$updateArray[] = array(
		  				'date' => $selected_date,
		  				'min_rate' => $min_rate_date,
		  			);
		  		}elseif ($dateExist == 2) {
		  			$updateArray[] = array(
		  				'date' => $selected_date,
		  				'min_rate' => $min_rate_date,
		  			);
		  			$updateArray = array_merge($updateArray,$existArray);
		  		}else{
	  				$updateArray = $existArray;
	  			}	
  				
	  			
	  			$insertUpdateArray = serialize($updateArray);
	  			$sqlString_data="UPDATE user_work_calender SET available_date = '$insertUpdateArray' WHERE uid = '$uid'"; 
	  			$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
			}else{
				$allAvailableDates[] = array(
	  				'date' => $selected_date,
	  				'min_rate' => $min_rate_date,
	  				
	  			);
	  			$availableDates = serialize($allAvailableDates);
	  			$sqlString_data="INSERT INTO user_work_calender VALUES ('','$uid','','$availableDates')"; 
	  			$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
			}
			return true;			
		}

		public function set_day_to_not_available($uid,$selected_date,$adapter){
			$calendar_record = $this->get_present_calendar_data($uid,$adapter);
			if (!empty($calendar_record) || $calendar_record == 'N;') {
				$updateArray[] = array(
	  				'date' => $selected_date,
	  			);
	  			if (!empty($calendar_record[0]['block_date'])) {
	  				$existArray = unserialize( $calendar_record[0]['block_date'] );
	  				$updateArray = array_merge($updateArray,$existArray);
	  			}
	  			$insertUpdateArray = serialize($updateArray);

	  			$sqlString_data="UPDATE user_work_calender SET block_date = '$insertUpdateArray' WHERE uid = '$uid'"; 
	  			$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
			}else{
				$allBlockDates[] = array(
	  				'date' => $selected_date,
	  			);
	  			$blockDates = serialize($allBlockDates);
	  			$sqlString_data="INSERT INTO user_work_calender VALUES ('','$uid','$blockDates','')"; 
	  			$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
			}
			return true;
		}

		/*Get alreday present calendar data */
		public function get_present_calendar_data($uid,$adapter){
			$sqlGetRecord="SELECT * FROM user_work_calender  WHERE uid = '$uid'"; 
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$calendar_record = $getRecord->toArray();
		  	return $calendar_record;
		}
		/* Get User Personal Info*/
		public function get_user_personal_info($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = $user_data['user_id']; 
			$personal_info = '';
			$sql_user_data="SELECT u.* FROM user u WHERE u.id='$uid'";	
		    $user_data_obj = $adapter->query($sql_user_data, $adapter::QUERY_MODE_EXECUTE);
			$user_data = $user_data_obj->current();
			if($user_data['id']!=''){
				// user extra info
				$sql_user_extra_data="SELECT ux.* FROM user_extra_info ux WHERE ux.uid='$uid'";	
				$user_extra_data_obj = $adapter->query($sql_user_extra_data, $adapter::QUERY_MODE_EXECUTE);
				$user_extra_data = $user_extra_data_obj->current();
				
			}
		    
			$roleTable = new Model();
		    $user_acl_role = $roleTable->fetchRow($roleTable->select(array('id' => (int) $user_data['user_acl_role_id'])));
		    
		    $professionalTable = new Model2();
		    $user_acl_profession = $professionalTable->fetchRow($professionalTable->select(array('id' => (int) $user_data['user_acl_profession_id'])));
		    
			$packageTable = new Model3();
			$user_acl_package = $packageTable->fetchRow($packageTable->select(array('id' => (int) $user_data['user_acl_package_id'])));		    

		    $sql_store_list = "SELECT * FROM site_town_table WHERE Lat != '' AND Lon != ''";    
		    $store_list_obj = $adapter->query($sql_store_list, $adapter::QUERY_MODE_EXECUTE); //print_r($storeDetails);
		    $store_list = $store_list_obj->toArray();
		    $townListString = '';
		    foreach ($store_list as $key => $town) {        
		        $townListString .= '"'.$town['Town'].'",';
		    }

		    $personal_info = array(
		    		'firstname' 	=> isset($user_data['firstname']) ? $user_data['firstname'] : '',
		    		'lastname' 		=> isset($user_data['lastname']) ? $user_data['lastname'] : '' ,
		    		'email' 		=> isset($user_data['email']) ? $user_data['email'] : '' ,
		    		'role' 			=> isset($user_acl_role['name']) ? $user_acl_role['name'] : '' ,
		    		'profession' 	=> isset($user_acl_profession['name']) ? $user_acl_profession['name'] : '' ,
		    		'package' 		=> isset($user_acl_package['name']) ? $user_acl_package['name'].' (£'.$user_acl_package['price'].')' : '' ,
		    		'username' 		=> isset($user_data['login']) ? $user_data['login'] : '' ,
		    		'company_name' 	=> isset($user_extra_data['company']) ? $user_extra_data['company'] : '' ,
		    		'store_name' 	=> isset($user_extra_data['company']) ? $user_extra_data['company'] : '' ,
		    		'address' 		=> isset($user_extra_data['address']) ? $user_extra_data['address'] : '' ,
		    		'town' 			=> isset($user_extra_data['city']) ? $user_extra_data['city'] : '' ,
		    		'postcode' 		=> isset($user_extra_data['zip']) ? $user_extra_data['zip'] : '' ,
		    		'telephone' 	=> isset($user_extra_data['telephone']) ? $user_extra_data['telephone'] : '' ,
		    		'mobile_number' => isset($user_extra_data['mobile']) ?  $user_extra_data['mobile'] : ''
		    	);
			return json_encode($personal_info);
		}
		/*Update User Personal Information*/
		public function update_user_personal_info($user_data)
		{

			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = isset($user_data['user_id']) ? $user_data['user_id']: ''; 			
			$fname = isset($user_data['user_info']['firstname']) ? $user_data['user_info']['firstname'] : ''; 
			$lname = isset($user_data['user_info']['lastname']) ? $user_data['user_info']['lastname'] : ''; 
			$company='';			
			if($user_data['user_role']=='2'){
				$company = isset($user_data['user_info']['company_name']) ? $user_data['user_info']['company_name'] : '';
			}else{
				$company = isset($user_data['user_info']['store_name']) ? $user_data['user_info']['store_name'] : '';
			}	    
		   
			$address = isset($user_data['user_info']['address']) ? $user_data['user_info']['address'] : '';
			$city = isset($user_data['user_info']['town']) ? $user_data['user_info']['town'] : '';
			$zip = isset($user_data['user_info']['postcode']) ? $user_data['user_info']['postcode'] : '';
			$password = sha1(isset($user_data['user_info']['password']) ? $user_data['user_info']['password'] : '');			
			$telephone = isset($user_data['user_info']['telephone']) ? $user_data['user_info']['telephone'] : '';
			$mobile = isset($user_data['user_info']['mobile_number']) ? $user_data['user_info']['mobile_number'] : '';			
			if(isset($user_data['user_info']['password'])){
				$sqlGetRecord="UPDATE user SET lastname='$lname', firstname='$fname', password='$password'  WHERE id = '$uid'"; 
			}else{
				$sqlGetRecord="UPDATE user SET lastname='$lname', firstname='$fname'  WHERE id = '$uid'"; 
		  	}
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$sqlGetRecordextra="UPDATE user_extra_info SET mobile='$mobile',address='$address',city='$city',zip='$zip',telephone='$telephone',company='$company' WHERE uid = '$uid'"; 
		  	$getRecordextra = $adapter->query($sqlGetRecordextra, $adapter::QUERY_MODE_EXECUTE);
		  	//Send mail after update profile
		  	$job_data_mail['firstname']=$fname;
			$job_data_mail['lastname']=$lname;
			$job_data_mail['email']=isset($user_data['user_info']['email']) ? $user_data['user_info']['email'] : '';;

		  	$mail_sent=new MailsController();
			$updateMail=$mail_sent->updateProfileMails($job_data_mail);		  	
			return json_encode($user_data);
		}
		
		/*Update Questions/Answers Information*/
		public function update_user_answers_info($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = isset($user_data['personal_info']['id']) ? $user_data['personal_info']['id']: ''; 
			$aoc_id = isset($user_data['personal_info']['opl']) ? $user_data['personal_info']['opl'] : '';
			$minimum_rate = isset($user_data['personal_info']['min_rate']) ? serialize($user_data['personal_info']['min_rate']) : '';
			$max_distance = isset($user_data['personal_info']['max_distance']) ? $user_data['personal_info']['max_distance'] : '';
			$goc = isset($user_data['personal_info']['goc']) ? $user_data['personal_info']['goc'] : '';
			$cet = isset($user_data['personal_info']['cet']) ? $user_data['personal_info']['cet'] : '';
			$aop = isset($user_data['personal_info']['aop']) ? $user_data['personal_info']['aop'] : '';
			$inshurance_company = isset($user_data['personal_info']['inshurance_company']) ? $user_data['personal_info']['inshurance_company'] : '';
			$inshurance_no = isset($user_data['personal_info']['inshurance_no']) ? $user_data['personal_info']['inshurance_no'] : '';
			$inshurance_renewal_date = isset($user_data['personal_info']['inshurance_renewal_date']) ? $user_data['personal_info']['inshurance_renewal_date'] : '';
			$store_id = isset($user_data['personal_info']['store_info']) ? $user_data['personal_info']['store_info'] : '';
			$store_data = isset($user_data['personal_info']['store_data']) ? $user_data['personal_info']['store_data'] : '';
			$store_value='';
			$store_info = array();
			$store_info_data = array();
	        //If Role is locum then add store id in array 
	        if($user_data['personal_info']['user_acl_role_id']=='2'){
				if(!empty($store_data)){
					foreach ($store_data as $key => $value) {
						//if($key!=""){
						    $store_info_data[] = $value;
						//}		
						
					}
					$store_info_data=implode(",", $store_info_data);
			    }
			    if(!empty($store_id)){
					foreach ($store_id as $key => $value) {
						//if($key==1){
							$value_replaced=str_replace("_"," ",$key);
						    $store_info[] = $value_replaced;
						//}
					}
					$store_info=implode(",", $store_info);
			    }
		    }else{
		    	$store_info=$store_id;
		    	$store_info=implode(",", $store_info);
		    	$store_info_data='';
		    }

			$ans_val = isset($user_data['ans_val']) ? $user_data['ans_val'] : '';
			$sql_user_ans ="SELECT user_id FROM user_answer WHERE user_id='$uid'";
			$result_user_ans = $adapter->query($sql_user_ans, $adapter::QUERY_MODE_EXECUTE);				
			$count_useransdata=$result_user_ans->count();
			if(!empty($ans_val)){
				foreach ($ans_val as $key => $value) {
					if (is_array($value)) {
						$value = implode(',', $value);
					}
					if($count_useransdata>0){
						$sqlString_ansup="UPDATE user_answer SET type_value='$value' WHERE user_id='$uid' AND question_id='$key'";
						$results_ansup = $adapter->query($sqlString_ansup, $adapter::QUERY_MODE_EXECUTE);
					}else{
						$sqlString_insert_ans="INSERT INTO user_answer (user_id,question_id,type_value) VALUES('$uid','$key','$value')";
						$results_ans = $adapter->query($sqlString_insert_ans, $adapter::QUERY_MODE_EXECUTE);
					}
				}
			}
			//Update /Insert store list
			$storelist = isset($user_data['storelist']) ? $user_data['storelist'] : '';
			$sql_user_ans ="SELECT * FROM user_store_list WHERE s_uid='$uid'";
			$result_storelist = $adapter->query($sql_user_ans, $adapter::QUERY_MODE_EXECUTE);				
			$count_useransdata=$result_storelist->count();
			$storelistss='';
			if(!empty($storelist)){
					foreach ($storelist as $key => $value) {
						$storelistss.=$value[$key].',';
					}
					if($count_useransdata>0){
						$sqlString_ansup="UPDATE user_store_list SET store_list='$storelistss',date_created=Now() WHERE s_uid='$uid'";
						$results_ansup = $adapter->query($sqlString_ansup, $adapter::QUERY_MODE_EXECUTE);
					}else{
						$sqlString_insert_ans="INSERT INTO user_store_list (s_uid,store_list,date_created) VALUES('$uid','$storelistss',Now())";
						$results_ans = $adapter->query($sqlString_insert_ans, $adapter::QUERY_MODE_EXECUTE);
					}
			}

		  	$sqlGetRecordextra="UPDATE user_extra_info SET aoc_id='$aoc_id',minimum_rate='$minimum_rate',max_distance='$max_distance',cet='$cet',goc='$goc',aop='$aop',inshurance_company='$inshurance_company',inshurance_no='$inshurance_no',inshurance_renewal_date='$inshurance_renewal_date',store_data='$store_info_data',store_id='$store_info' WHERE uid = '$uid'"; 
		  	$getRecordextra = $adapter->query($sqlGetRecordextra, $adapter::QUERY_MODE_EXECUTE);
			return json_encode($user_data);
		}
		/******Select user selected Questions/ansawers ****/
		public function get_user_questions_info($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = $user_data['user_id'];
		  	/****select all questions ****/
		  	$role = isset($user_data['role']) ? $user_data['role'] : '';
			$profession = isset($user_data['profession']) ? $user_data['profession'] : '';
			/***question section extra information start ***/
			$sqlGetRecordextra="SELECT address,city,zip,minimum_rate,max_distance,store_week_time,store_unique_time,cet,goc,aop,inshurance_company,inshurance_no,store_data,store_id,form_id,aoc_id,inshurance_renewal_date FROM user_extra_info WHERE uid = '$uid'"; 
		  	$qextrainfo= $adapter->query($sqlGetRecordextra, $adapter::QUERY_MODE_EXECUTE);
		  	$extrainfo = $qextrainfo->toArray();
		  	
		  	if(!empty($extrainfo)){
		  		
			  	//foreach ($extrainfo as $key=>$extraValue) {
			  		$quesData = $extrainfo[0];
			  		if ($extrainfo[0]['store_data'] != '') {
							$quesData['store_data'] = explode(',', $extrainfo[0]['store_data']);
					}
					if ($extrainfo[0]['store_id'] != '') {
							$quesData['store_id'] = explode(',', $extrainfo[0]['store_id']);
					}
		  		    if ($extrainfo[0]['minimum_rate'] != '') {		  		    	
			    		$quesData['minimum_rate'] = unserialize($extrainfo[0]['minimum_rate']);	
			    	}
			    	if ($extrainfo[0]['store_unique_time'] != '') {
			    		$quesData['store_unique_time'] = unserialize($extrainfo[0]['store_unique_time']);
			    	}			  		
			  	//}
		    }
		    //print_r($quesData['minimum_rate']);
		    /***question section extra information end ***/
		    /***question section information start ***/
			if ($role != '' && $profession != '') {
				if($role == 3){
					$sqlFreQues = "SELECT id,equestion AS q,type_key,type_value,required_status,range_type_unit,range_type_condition from user_question WHERE (equestion != '' OR equestion != null) AND cat_id = '$profession' ORDER BY sort_order ASC";	
				    $quesDataObj = $adapter->query($sqlFreQues, $adapter::QUERY_MODE_EXECUTE);
				    $quesData['questions'] = $quesDataObj->toArray();
				    foreach ($quesData['questions'] as $key => $que) {
				    	$ques_id=$que['id'];
				    	if ($que['type_value'] != '') {
				    		$quesData['questions'][$key]['type_value'] = unserialize($que['type_value']);
				    	}
				    	if ($que['type_key'] == 5) {				    		
				    		$quesData['questions'][$key]['type_key'] = 3;
				    	}
				    	$sqlGetRecord="SELECT type_value FROM user_answer  WHERE user_id = '$uid' AND question_id='$ques_id'"; 
					  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
					  	$getvalues= $getRecord->toArray();	
					  	if(sizeof($getvalues)>0){
							$kl= explode(',',$getvalues[0]['type_value']);						
					  	    array_push($quesData['questions'][$key],$kl);			  		
					  	}
				    }
				    	
			    }elseif($role == 2){
			    	$sqlFreQues = "SELECT id,fquestion AS q,type_key,type_value,required_status,range_type_unit,range_type_condition from user_question WHERE (fquestion != '' OR fquestion != null) AND cat_id = '$profession' ORDER BY sort_order ASC";	
				    $quesDataObj = $adapter->query($sqlFreQues, $adapter::QUERY_MODE_EXECUTE);
				    $quesData['questions'] = $quesDataObj->toArray();
				    foreach ($quesData['questions'] as $key => $que) {				    	
				    	$ques_id = $que['id'];				  	
				    	if ($que['type_value'] != '') {
				    		$quesData['questions'][$key]['type_value'] = unserialize($que['type_value']);
				    	}
				    	$sqlGetRecord="SELECT type_value FROM user_answer  WHERE user_id = '$uid' AND question_id='$ques_id' "; 
					  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
					  	$getvalues= $getRecord->toArray();
					  	if(sizeof($getvalues)>0){
					  	    $kl= explode(',',$getvalues[0]['type_value']);						
					  	    array_push($quesData['questions'][$key],$kl);		  		
					  	}				  	
				    }
				}
				//print_r($quesData);
				return json_encode($quesData);
			  }else{
				return 0;
			}
			/***question section  information end ***/	
		}	

	 	/******Get User Nearby Store List name ****/
		public function get_user_store_list($user_data)
		{

			$dbController = new DbController();	
 			$distancecal = new Distancecal();
			$adapter = $dbController->locumkitDbConfig();
       		//if(isset($user_data['get_list']) && $user_data['get_list']==1){
			if (isset($user_data['town_page'])) {
				$page = $user_data['town_page'];
			}else{
				$page = $user_data['town_page'];
			}

			$val 		= "";
			$list 		= ""; 
			$distance 	= "";
			$zip			= str_replace(" ", "", $user_data['zip'].'+UK');
			$town			= str_replace("'", "", $user_data['city']);		
			$addr 			= str_replace("'", "", $user_data['city'].'+UK');
			$max_dis 		= $user_data['max_dis'];
			$cat_id			= $user_data['cat_id'];		
			$sourceLatLng 	= $distancecal->getDistanceLatLng($zip,$addr);
			$sourceLat 		= $sourceLatLng['lat'];
			$sourceLng 		= $sourceLatLng['lng'];	
			$getvalues 		= array();
        	$lat 			= $sourceLat; // latitude of center of bounding circle in degrees
        	$lon 			= $sourceLng; // longitude of center of bounding circle in degrees
	        if ($max_dis == "Over 50") {
	        	$rad = 6371; 
	        }else{
	        	$rad = $max_dis; // radius of bounding circle in kilometers
	        }
	        $R = 6371;  // earth's mean radius, km
	        // first-cut bounding box (in degrees)
	        $maxLat = $lat + rad2deg($rad/$R);
	        $minLat = $lat - rad2deg($rad/$R);
	        // compensate for degrees longitude getting smaller with increasing latitude
	        $maxLon = $lon + rad2deg($rad/$R/cos(deg2rad($lat)));
	        $minLon = $lon - rad2deg($rad/$R/cos(deg2rad($lat)));
	        $lat 	= deg2rad($lat);
	        $lon 	= deg2rad($lon);
	        $sqlList = "Select tw_id, Town, county , acos(sin($lat)*sin(radians(Lat)) + cos($lat)*cos(radians(Lat))*cos(radians(Lon)-$lon)) * $R As D
	                From (Select *
	                    From site_town_table
	                    Where Lat Between '$minLat' And '$maxLat'
	                      And Lon Between '$minLon' And '$maxLon'
	                ) As FirstCut
	                Where acos(sin($lat)*sin(radians(Lat)) + cos($lat)*cos(radians(Lat))*cos(radians(Lon)-$lon)) * $R < $rad
	                Order by D";
	        $getDiust 	= $adapter->query($sqlList, $adapter::QUERY_MODE_EXECUTE); 
	        $results 	= $getDiust->toArray();
			$results_count = $getDiust->count();
			$select_county = $county_option = '';
			if (!empty($results)) {
		       foreach ($results as $key => $value) { $arr_con[] = $value['county'] ; }
		       $arr_counties =  array_values(array_unique($arr_con));
			}			
			if (!empty($results)) {
			  	$county_option;
	            foreach ($results as $key => $value) {
	                $getvalues[$key]['town_id'] = $value['tw_id'];
	                $getvalues[$key]['county'] = $value['county'];
	                //echo '<td>'.$value['county'].'</td>';
	                $getvalues[$key]['town_name'] = $value['Town'];
	                $getvalues[$key]['miles'] = number_format((float)$value['D'], 2, '.', '').' Miles';
	            }
	        }else{
	            return false;
	        } 
		   	
		   	$getvalues = array(
	   			'town_list' => $getvalues,
	   			'county_list' => $arr_counties
	   		);
		  	return json_encode($getvalues);		
     	} 
         /******Search town/city name ****/
		public function town_search($user_data)
		{
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = str_replace("'", "", $user_data['name']) ; 
			$sqlGetRecord="SELECT Town FROM site_town_table  WHERE Town LIKE '%$uid%' limit 10"; 
		  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
		  	$getvalues= $getRecord->toArray();
		  	return json_encode($getvalues);		
     	} 

     	public function getCancellationRate($user_data)
     	{     		
     		$user_id 	= $user_data['user_id'];
     		$user_role 	= $user_data['user_role'];
     		$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$functionsController = new FunctionsController();

     		$cancellationRate = 0;
	        $progress = 0;
	        if ($user_role == 3 ) {
	            $cancellationRate = $functionsController->getEmpCancellationRate($user_id,$adapter);	            
	        }elseif($user_role == 2 ){
	            $cancellationRate = $functionsController->getFreCancellationRate($user_id,$adapter);
	            
	        }
	        return $cancellationRate;
     		
     	}
    	public function get_fre_min_rate($user_data){
            $user_id 	= $user_data['user_id'];
            $date 	= $user_data['date'];
            $dbController 	= new DbController();
            $helper = new HelperController();			
            $adapter 	= $dbController->locumkitDbConfig();
            $functionsController = new FunctionsController();
            $min_rate = '';
            $sqlGetRecord="SELECT available_date FROM user_work_calender  WHERE uid = '$user_id'"; 
            $getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
            $allRecord = $getRecord->toArray();
            $recordArray = unserialize($allRecord[0]['available_date']);
            if (!empty($recordArray)) {
                foreach ($recordArray as $key => $value) {
                    if (date("Y-n-d", strtotime($value['date'])) == $date) {
                        $min_rate = array('min_rate' => $helper->formate_price($value['min_rate']));
                    }
                }		
            }
            if(empty($min_rate)){
                $sqlGetDayRecord="SELECT minimum_rate FROM user_extra_info  WHERE uid = '$user_id'"; 
                $getDayRecord = $adapter->query($sqlGetDayRecord, $adapter::QUERY_MODE_EXECUTE);
                $allDayRecord = $getDayRecord->toArray();
                
                if (!empty($allDayRecord)) {
                    $dayWaiseRate = unserialize($allDayRecord[0]['minimum_rate']);
                    $day = date('l', strtotime($date));
                    foreach ($dayWaiseRate as $key => $dayRate) {
                        if ($key == $day) {
                            $min_rate = array('min_rate' => $helper->formate_price($dayRate));
                        }
                    }
                }
            }
            return json_encode($min_rate);	
     	}

     	public function getUserPermission($data)
     	{
     		$user_id 	= $data['user_id'];
     		$permissions= $data['permission'];
     		$dbController 	= new DbController();
     		$adapter 	= $dbController->locumkitDbConfig();
     		$packagePrivilegesController    = new PackagePrivilegesController();
     		$is_permission = array();     		
     		foreach ($permissions as $key => $permission) {
     			$is_permission[$permission] = ($packagePrivilegesController->getCurrentPackagePrivilegesResources($permission,$user_id,$adapter) == 1) ? true : false;
     		}     		
     		return json_encode($is_permission);     		
     	}

     	public function check_user_availability_by_date($data)
     	{
     		$is_available = 0;

     		$requested_date = date('m/d/Y', strtotime($data['job_info']['date'])).' 5:30:00';
     		$user_booking_details = json_decode( $this->get_block_date($data) );
     		$booked_date = $user_booking_details->booked;
     		$block_date = $user_booking_details->block;
     		$book_dates_array = array();
     		$getPrivateJobRecordArray = array();
     		//if job action is edit 
     		if (isset($data['job_info']['pid'])) {
     			$pv_job_id = $data['job_info']['pid'];
     			$edit_requested_date = date('Y-m-d', strtotime($data['job_info']['date']));
     			$dbController 	= new DbController();
     			$adapter 	= $dbController->locumkitDbConfig();
     			$check_private_job_edit_sql = "SELECT * FROM freelancer_private_job WHERE pv_id = '$pv_job_id' AND priv_job_start_date = '$edit_requested_date'";
     			$getPrivateJobRecord = $adapter->query($check_private_job_edit_sql, $adapter::QUERY_MODE_EXECUTE);
     			$getPrivateJobRecordArray = $getPrivateJobRecord->toArray();
     		}
     		if (empty($getPrivateJobRecordArray)) {
     			foreach ($booked_date as $key => $booked_date) {
	     			$book_dates_array[] = $booked_date->startTime;
	     		}
	     		if (in_array($requested_date, $book_dates_array)){
	     			$is_available = 1;
	     		}
	     		if (in_array($requested_date, $block_date)){
	     			$is_available = 2;
	     		}
     		}
     		
     		return $is_available;
     	}

     	public function is_profile_completed($data){
     		$user_id = $data['user_id'];
     		$profession_id = $data['profession_id'];
            $dbController 	= new DbController();           	
            $adapter 	= $dbController->locumkitDbConfig();
            $functionsController = new FunctionsController();
            return $functionsController->checkIfProfileComplete($user_id, $profession_id, $adapter);
     	}

     	/* Update User Password */
     	public function updateUserPassword($data){     		
     		$user_id 		= $data['user_id'];
     		$user_data 		= $data['user_data'];
     		$dbController 	= new DbController();           	
            $adapter 		= $dbController->locumkitDbConfig();
            $enteredOldPass = sha1(isset($user_data['oldpassword']) ? $user_data['oldpassword'] : '');

            $checkOldPass 	= "SELECT * FROM user WHERE password = '$enteredOldPass' AND id = '$user_id'";
 			$oldPassRecord 	= $adapter->query($checkOldPass, $adapter::QUERY_MODE_EXECUTE);
 			$oldPassRecordArray = $oldPassRecord->current();
 			if (!empty($oldPassRecordArray)) {
 				$newPassword 	= sha1(isset($user_data['password']) ? $user_data['password'] : '');
 				$sqlPassData = "UPDATE user SET password = '$newPassword' WHERE id = '$user_id'"; 
	  			$passDataResult = $adapter->query($sqlPassData, $adapter::QUERY_MODE_EXECUTE);
	  			return 1;
 			}else{
 				return 0;
 			}
            
     	}

	}