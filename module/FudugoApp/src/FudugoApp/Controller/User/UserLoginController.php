<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\User;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Notification\NotificationController as NotificationController;
	use FudugoApp\Controller\SubscriptionPackage\PackageController as PackageController;
	use GcFrontend\Controller\FunctionsController as FunctionController;
	Class UserLoginController
	{
		public function app_user_login($user_data){
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();			
			$notifyController 	= new NotificationController();
			$packageController 	= new PackageController();
			//check user login and update information of session data
			
				//fresh login then sha1 will give result and token
				$login = $user_data['email'];
	                      $token=$user_data['token'];
			    $password = sha1($user_data['password']);	
			
			
			$res_count="";

			$user = $this->app_user_is_active($login, $password, $adapter);
			if ($user) {
				//Please check Package is expire or not start
				$functionController = new FunctionController();
				$check_membership = $functionController->checkUserMembershipPlan($user['id'],$adapter);
				
				$is_expired = 0;
				
				if ($check_membership == 0 && $user['user_acl_role_id']==2) {
				$is_expired = 1;
				}
				
				//Please check Package is expire or not end
				if($is_expired==0 || $user['is_free']==1){
					
					switch ($user['active']) {
					case '0':
					    if($user['user_acl_role_id']==3){
					    	return "Your profile is under verification process and it will take around 48 hours from the time of registration.";
					    }else{
					    	return "Please verify email address.";
					    }
						
						break;
					case '3':
						return "Please complete payment through website.";
						break;							
					default:
						if($token!=''){
	                       $notifyController->insert_notification_data($user['id'],$token);  
	                    }
	                    $user['financial_year']=$packageController->get_financial_detail($user['id'],$adapter);
	                    




	                    return json_encode($user);
						break;

				    }
			 	}else{
			 		return "Your package is expired.Please upgrade it from website.";
			 	}
	                
                              
			}else{
				return "Please enter valid details.";
			}			
		}
		//Update session data if user login
		public function app_user_session_update($user_data){
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();			
			$notifyController 	= new NotificationController();
			$packageController 	= new PackageController();
			//check user login and update information of session data			
			//fresh login then sha1 will give result and token
			$login = $user_data['email'];
			$token='';
		    $password = $user_data['password'];	
			
			
			$res_count="";

			$user = $this->app_user_is_active($login, $password, $adapter);
			if ($user) {
				//Please check Package is expire or not start
				$functionController = new FunctionController();
				$check_membership = $functionController->checkUserMembershipPlan($user['id'],$adapter);
				
				$is_expired = 0;
				
				if ($check_membership == 0 && $user['user_acl_role_id']==2) {
				$is_expired = 1;
				}
				
				//Please check Package is expire or not end
				if($is_expired==0 || $user['is_free']==1){
					
					switch ($user['active']) {
					case '0':					   
					    return false;
						break;
					case '3':
						return false;
						break;							
					default:
						if($token!=''){
	                       $notifyController->insert_notification_data($user['id'],$token);  
	                    }
	                    $user['financial_year'] = $packageController->get_financial_detail($user['id'],$adapter);
	                    $this->last_login_entry($user['id'],$adapter);	
	                    return json_encode($user);
						break;

				    }
			 	}else{
			 		return false;
			 	}
	                
                              
			}else{
				return false;
			}			
		}
		/* Check user is present & active */
		public function app_user_is_active($login, $password, $adapter){

			$sql_check_user="SELECT id,password,firstname,lastname,email,active,user_acl_role_id,user_acl_profession_id,user_acl_package_id,is_free,DATE_FORMAT(created_at, '%Y-%m-%d') as created_at FROM user WHERE (login='$login' OR email = '$login') AND password='$password'"; 
		    $check_user_obj = $adapter->query($sql_check_user, $adapter::QUERY_MODE_EXECUTE);
			$user = $check_user_obj->current();
			if (!empty($user)) {
				return $user;
			}else{
				return 	false;
			}    
		}

		/* Check user is present & active */
		public function logoutMobile($user_data){			
			$notifyController = new NotificationController();
			if(isset($user_data['token'])){
				$token = isset($user_data['token']) ? $user_data['token'] : '';
				$done=$notifyController->deleteToken($token);
				return $done;
			}
		}



		/* Last login entry */
		public function last_login_entry($id, $adapter){
			$sqlString_sel="select * from last_login_user where uid='$id'";	
	        $results0 = $adapter->query($sqlString_sel, $adapter::QUERY_MODE_EXECUTE);
			$row22 = $results0->current();
			if(isset($row22['id']) && $row22['id']!=''){
	            $lastLogin = $row22['login_at'];
	            $sqlString_update="UPDATE last_login_user SET login_at = NOW(), last_login_at = '$lastLogin'  WHERE uid='$id'"; 
	            $results3 = $adapter->query($sqlString_update, $adapter::QUERY_MODE_EXECUTE);
	        }else{
	            $sqlString_ins="insert into last_login_user (uid,login_at) values('$id',NOW())";    
	            $results4 = $adapter->query($sqlString_ins, $adapter::QUERY_MODE_EXECUTE);
	        }
		}

	}