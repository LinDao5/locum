<?php
	/**
	* Develop by Rizwana Ansari (@FuduGo.com)
	*/
	namespace FudugoApp\Controller\Notification;
	use GcFrontend\Controller\DbController as DbController;

	Class NotificationController
	{
		/****Send Push Notification on Mobile ***/
		public function notification($jobId,$message,$title,$userId,$types)
		{	
				$uid = isset($userId) ? $userId : '';
				$token_gts=array();
				if($uid!=''||$uid!=null){
				  $token_gts=$this->getTokenByID($userId);
				}

				$apiKey = "AIzaSyCe_r1u8_HGAHrOK_2M1TupjuPcnhQWpKI";
                                 foreach ($token_gts as $key => $token) {
				$registrationIds = array($token['token_id']);
				$fields = array
				(
					 'registration_ids'  => $registrationIds,
					 'priority'=>'high',
					 'notification' => array(
    					 	 "title" => $title,
    					 	 "text" => $message,
    					 	 "click_action" => "FCM_PLUGIN_ACTIVITY",
    					 	 "jobid" => $jobId,
						 "userid" => $userId,
						 "type"  => $types,
						 "sound" => "default",
						 "icon"  => 'fcm_notification_icon'
					 ),
					 'data' => array (
									"message" => $message,
									"title" => $title,
									"jobid" => $jobId,
									"userid" => $userId,
									"type" => $types

							)
				);
				$headers = array
				 (
				  'Authorization: key=' . $apiKey,
				  'Content-Type: application/json'
				 );
				 $ch = curl_init();
				 curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
				 curl_setopt( $ch,CURLOPT_POST, true );
				 curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
				 curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
				 curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
				 curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
				 $result = curl_exec($ch );

				 curl_close( $ch );
				 $status = 0;
				 $finalResult = json_decode($result);
//echo '<pre>';
//print_r($result); 
//echo '</pre>';
//		die();		 
				 	if($finalResult->success){
				 	  $status = 1;
					}else{
					  $status = 2;
					}
				 
				 
				 //update status notification
				 $done=$this->updateTokenStatus($status,$token['token_id']);

//die();

                                }
				return $result;
		}
		/****Insert Mobile Token in Mobile Notification table for  Mobile push notification ***/
		public function insert_notification_data($userId,$token_id){
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$uid = isset($userId) ? $userId : '';
			$insert_user_data=array();
			$token_id = isset($token_id) ? $token_id : '';
			//check  user record If token already exist
			$sql_user_delete = "SELECT token_id FROM mobile_notification WHERE token_id='$token_id'";
			$get_user_data_delete = $adapter->query($sql_user_delete, $adapter::QUERY_MODE_EXECUTE);
			$tokenIDExist = $get_user_data_delete->toArray();
			//Delete Token If already exist Yes
			if(sizeof($tokenIDExist)==1){
         		$deleted=$this->deleteToken($token_id);
			}
			
			//check user id and token already exist or not
			$sql_user1 = "SELECT * FROM mobile_notification WHERE user_id='$uid' AND token_id='$token_id'";
			$get_user_data = $adapter->query($sql_user1, $adapter::QUERY_MODE_EXECUTE);
			$tokenID = $get_user_data->toArray();
			if(sizeof($tokenID)==0){
				$sql_user = "INSERT INTO mobile_notification (user_id,token_id,status) VALUES ('$uid','$token_id',0)";
				$insert_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			}
			
			return $insert_user_data;
		}
		/****Get Token By User Id ***/
		public function getTokenByID($userId){
			$dbController 	= new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$uid = isset($userId) ? $userId : '';
			$sql_user = "SELECT token_id FROM mobile_notification WHERE user_id='$uid'";
			$get_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			$tokenID = $get_user_data->toArray();			
			return $tokenID;
		}
         /****Delete Token AND User Id If User is logged out From mobile***/
		public function deleteToken($token){
			$dbController 	= new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$sql_user = "DELETE FROM mobile_notification WHERE token_id='$token'";
			$get_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			return $get_user_data;
		}
		/****Update Token AND User Id If User is logged out From mobile***/
		public function updateTokenStatus($status,$token){
			$dbController 	= new DbController();			
			$adapter = $dbController->locumkitDbConfig();
			$sql_user = "UPDATE mobile_notification SET status='$status' WHERE token_id='$token'";
			$get_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			return $get_user_data;
		}
		
	}