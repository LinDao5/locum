<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\SubscriptionPackage;
	use GcFrontend\Controller\DbController as DbController;	
	use Gc\Registry;
    use GcFrontend\Controller\JobmailController as MailController;
	use GcFrontend\Controller\PackagePrivilegesController;
    use Gc\User\UserPackage as UserPackage;
	Class PackageController
	{	
		//Manage Locums Package 
		public function manage_package($form_data){
			$dbController = new DbController();
			$adapter = $dbController->locumkitDbConfig();			
			switch ($form_data['page_id']) {
				case 'package':
					$response['package']=$this->updatePackage($form_data,$adapter);
					break;
				case 'manage-financial-year':
					$response = $this->manage_financial_year($form_data,$adapter);
					break;							
				
				default:
					$response = 0;
					//$response = $this->get_form_field_setp_3($form_step,$adapter);
					break;
			}
			return $response;
					
		}	
		/******Payment Package List *****/
		public function get_package_list_api($adapter){
            $sql_package="SELECT value FROM  core_config_data WHERE identifier='payment_api_key' OR identifier='payment_mode' ";
            $package_obj2 = $adapter->query($sql_package, $adapter::QUERY_MODE_EXECUTE);
            $package_obj_array= $package_obj2->toArray();
            return $package_obj_array;
		}

	 	/******Payment Package List *****/
		public function get_package_list($adapter){
			$sql_package="SELECT id,name,price,description FROM  user_acl_package WHERE 1 ORDER BY id DESC";
            $package_obj = $adapter->query($sql_package, $adapter::QUERY_MODE_EXECUTE);
            $package_obj_array['package_list']= $package_obj->toArray();
            //Pakcage resources preiviliege infomation 
            $sqlInserPkgInfo = "SELECT * FROM pkg_privilege_info WHERE 1"; 
			$getPkgInfo = $adapter->query($sqlInserPkgInfo, $adapter::QUERY_MODE_EXECUTE);
			$getAllPkgInfo = $getPkgInfo->toArray();
			foreach ($getAllPkgInfo as $key => $value) {
				$label = $value['p_label'];
				$bronze = $value['p_bronze'];
				$silver = $value['p_silver'];
				$gold = $value['p_gold'];
				if($bronze==1){
					$package_obj_array['info']['bronze'][$key]['label']=$label;
				}
				if($silver==1){
					$package_obj_array['info']['silver'][$key]['label']=$label;
				}
				if($gold==1){
					$package_obj_array['info']['gold'][$key]['label']=$label;
				}
				
			}              
            return $package_obj_array;
		}

		//Insert locum Package information in package details table
		public function insert_user_package($user_data,$uid,$adapter,$type=''){
			$package_id = isset($user_data['payment_info']['intent']) ? $user_data['payment_info']['intent'] : '';
			$price = isset($user_data['payment_info']['amount']) ? $user_data['payment_info']['amount'] : '';
			$payment_data = isset($user_data['payment_info']['authorization_id']) ? $user_data['payment_info']['authorization_id'] : '';
			$checkFree=isset($user_data['payment_info']['freeSubscription']) ? $user_data['payment_info']['freeSubscription'] : '0';
			//insert into user package table 
			$pkg_active_date = date("Y-m-d"); 
			$package_active_date=date('Y-m-d');
			$time = strtotime(date('Y-m-d'));
			$package_expire_date = date("Y-m-d", strtotime("+1 year", $time));
			if($price==0){
				$package_expire_date = date("Y-m-d", strtotime("+24 months", $time));
				$sql_user = "UPDATE user SET is_free=1 WHERE id='$uid'";
			    $insert_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			}
			//If payment done success then set transaction id
			$payment_success_done=$this->insert_payment_info($user_data,$uid,$adapter);
			
			$sql_package="insert into user_package_details (user_id,package_id,package_active_date,package_expire_date,package_status) values('$uid','$package_id','$pkg_active_date','$package_expire_date','1')";
			$results_package = $adapter->query($sql_package, $adapter::QUERY_MODE_EXECUTE);
			if($type=='register'){
				//send activation mail to users
				$mailController = new MailController();
				$header = $mailController->mailHeader();
				$footer = $mailController->mailFooter();
				//$host = $this->getRequest()->getUri()->getHost();
				$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
				$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
				$adminEmail = $configGet->get('mail_from');
				$mailController->sendVerifyEmailtofreelancer($uid,$price,$adapter);
			}
			return $results_package;
		}
		//insert payment information
		public function insert_payment_info($user_data,$uid,$adapter){			
			$payment_data = isset($user_data['payment_info']['authorization_id']) ? $user_data['payment_info']['authorization_id'] : '';
			$payment_type= isset($user_data['payment_info']['payment_type']) ? $user_data['payment_info']['payment_type'] : 'paypal';
			$price = isset($user_data['payment_info']['amount']) ? $user_data['payment_info']['amount'] : '';
			$package_id = isset($user_data['payment_info']['intent']) ? $user_data['payment_info']['intent'] : '';
			if($price==0){
				$payment_data='Free Subscription';
				$payment_type='FREE';
			}
			// Insert Paypal payment information
			$sql_paypal = "INSERT INTO user_payment_info (uid,payment_type,package_id,payment_data,price,payment_status) values('$uid','$payment_type','$package_id','$payment_data','$price','1')";
			$insert_sql_paypal_data = $adapter->query($sql_paypal, $adapter::QUERY_MODE_EXECUTE);
			
			return $insert_sql_paypal_data;
		}
		//update payment information
	    public function updatePackage($user_data,$adapter)
	    {
		    $mailController = new MailController();
		    $userPackage=new UserPackage();		    
	    	$uid = isset($user_data['uid']) ? $user_data['uid'] : ''; //user id
	    	$pid = isset($user_data['pid']) ? $user_data['pid'] : ''; //package id
	    	$package_id = isset($user_data['payment_info']['intent']) ? $user_data['payment_info']['intent'] : '';
			$price = isset($user_data['payment_info']['amount']) ? $user_data['payment_info']['amount'] : '';
			$payment_data = isset($user_data['payment_info']['authorization_id']) ? $user_data['payment_info']['authorization_id'] : '';
			$payment_type= isset($user_data['payment_info']['payment_type']) ? $user_data['payment_info']['payment_type'] : 'paypal';
	    	$serverUrl = $userPackage->insertPackageInfo($uid,$pid);
			//update user package details status
			$sqlString_update="update user_package_details set package_status=3 where user_id='$uid'";	
		    $results_update = $adapter->query($sqlString_update, $adapter::QUERY_MODE_EXECUTE);
	     	//insert into user package table 
			$results_package = $this->insert_user_package($user_data,$uid,$adapter);			
	        //update user table package ID
			$sql_user = "UPDATE user SET user_acl_package_id='$pid',is_free=0 WHERE id='$uid'";
			$insert_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);		
			$mailController->sendPackageRenewMail($uid,$price,$adapter) ;
	        return $getRecord2;
	    }

	    //Insert and Update Financial year 
	    public function manage_financial_year($user_data,$adapter){
	    	$uid = isset($user_data['user_id']) ? $user_data['user_id'] : '';
	    	$managefinancialyear=isset($user_data['managefinancialyear']) ? $user_data['managefinancialyear'] : '';
			if (isset($managefinancialyear)) {
				$fyid = isset($user_data['fyid']) ? $user_data['fyid'] : '';				
				$fiusertype = isset($user_data['fiusertype']) ? $user_data['fiusertype'] : '';
$sql_check_financial_year="SELECT id,user_type,month_start,month_end FROM financial_year WHERE user_id='$uid'"; 
			$check_user_year = $adapter->query($sql_check_financial_year, $adapter::QUERY_MODE_EXECUTE);
			$user= $check_user_year->current();
				if(isset($fiusertype) && $fiusertype == 'soletrader' && count($user)==1){
					$monthstart =  '4' ;
					$monthend = '3' ;
				}else{
					$monthstart =  $managefinancialyear ;
					$monthend = @$monthstart == 1 ? '12' : $monthstart - 1 ;
				}  



				if(isset($fyid) and $fyid != '' and count($user)==4){
					$sqlGetjob = "UPDATE `financial_year` SET month_start=$monthstart,month_end=$monthend,user_type='$fiusertype' WHERE  user_id = '$uid'";
					    $getjobs = $adapter->query($sqlGetjob, $adapter::QUERY_MODE_EXECUTE);
					    if($getjobs){
					        echo  '2';
					    }
				}else{
				    $sqlGetjob = "INSERT INTO financial_year(user_id, user_type, month_start, month_end) VALUES ('$uid','$fiusertype','$monthstart','$monthend')";
				    $getjobs = $adapter->query($sqlGetjob, $adapter::QUERY_MODE_EXECUTE);
				    if($getjobs){
				        echo '1';
				    }
				}
			}
	    }

	    public function get_financial_detail($user_id,$adapter){
			
			//get financial info
			$sql_check_financial_year="SELECT id,user_type,month_start,month_end FROM financial_year WHERE user_id='$user_id'"; 
			$check_user_year = $adapter->query($sql_check_financial_year, $adapter::QUERY_MODE_EXECUTE);
			$user= $check_user_year->current();
			return $user;
	    }
	}