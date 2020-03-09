<?php 
	/**
	* Develop by Rizwana Ansari
	*/
	namespace FudugoApp\Controller\User;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use GcFrontend\Controller\JobmailController as MailController;	
	use Gc\User\Role\Model;
	use Gc\User\Professional\Model as Model2;
	use Gc\User\Package\Model as Model3;
	use GcFrontend\Controller\DistancecalculateController as Distancecal;
	
	Class BlockedUserController
	{	
		/******UNLOCKED FREELANCER/LOCUMS GET ALL RECORD OF LOCKED LOCUMS/FREELANCE****/
		public function get_blocked_user($user_data)
		{	
	
			$dbController = new DbController();			
			$adapter = $dbController->locumkitDbConfig();			
			$uid = isset($user_data['uid']) ? $user_data['uid'] : '';
			if($user_data['type']=='delete'){
				$deleted=$this->updateBlockFreelancer($user_data['bid'],$adapter);
			}			
			if($user_data['type']=='delete-account'){
				$results['delete']=$this->deleteUserAccount($user_data,$adapter);
			}
			
			if($user_data['type']=='get'){
				$results=array();
				/*****get all records of blocked FREELANCER/LOCUMS ***/
				$sqlGetRecord="SELECT bid,frelan_id,user_block_date FROM block_user WHERE emp_id='$uid'"; 
			  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
			  	$getvalues= $getRecord->toArray();
			    $results_count=$getRecord->count();	
			    if($results_count>0){
			    	/***GET FREELANCER/LOCUMS Details **/
			    	 foreach ($getvalues as $key => $value) {
			    	 	$free_id=$value['frelan_id'];
			    	 	$sqlGetRecord="SELECT firstname,lastname,email FROM user WHERE id='$free_id'"; 
					  	$getRecord2 = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
					  	$getvalues2= $getRecord2->toArray();
					  	$results_count2=$getRecord2->count();
					  	if($results_count2>0){
					  		foreach ($getvalues2 as $key2 => $value2) {
					  			$results['results'][$key]['bid']=$value['bid'];
					  			$results['results'][$key]['free_id']=$free_id;
					  			$results['results'][$key]['name']=$value2['firstname'].' '.$value2['lastname'];
					  			$results['results'][$key]['email']=$value2['email'];
					  			$results['results'][$key]['block_date']=$value['user_block_date'];
					  	    }
					  	}
			    	 }
			    }
			}
		  	return json_encode($results);	
     	}
     	
		/* update block Freelancer */
	    public function updateBlockFreelancer($uid,$adapter)
	    {
	    	 
	        $sqlGetRecord="DELETE FROM block_user  WHERE bid='$uid'"; 
			$getRecord2 = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
	        return $getRecord2;
	    }
	    

	    /* update block Freelancer */
	    public function deleteUserAccount($user_data,$adapter)
	    {
	    	
	    	$uid = isset($user_data['uid']) ? $user_data['uid'] : '';	    	
	    	$user_email = isset($user_data['email']) ? $user_data['email'] : '';
	    	$user_name = isset($user_data['username']) ? $user_data['username'] : '';
	    	$reason = isset($user_data['uservalue']) ? $user_data['uservalue'] : '';
	    	//get user email and user name by user id
	    	$sql_user = "SELECT email, login from user where id='$uid'";	    	
			$resultsuser = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			$getvalues= $resultsuser->toArray();
			
		    $results_count=count($getvalues);	
		    if($results_count==1){
		    	foreach ($getvalues as $key => $value) {
		    		$user_email=$value['email'];
		    		$user_name=$value['login'];
		    	}
		    }

	        $sqlString_insert="insert into user_leavers_table (uid,user_email,user_name,user_reason_to_leave,created_at) values('$uid','$user_email','$user_name','$reason',NOW())";
	        
			$results_ans_paypal = $adapter->query($sqlString_insert, $adapter::QUERY_MODE_EXECUTE);
			
			$sql_del="delete from user where id='$uid'";
			$results = $adapter->query($sql_del, $adapter::QUERY_MODE_EXECUTE);			

			$user_data="sucess";
	        return $user_data;
	    }
     }
   ?>