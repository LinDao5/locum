<?php

 namespace GcFrontend\Controller;
 use Gc\Mvc\Controller\Action;
 use Gc\view\Helper\Config as ConfigModule;
 use Gc\Core\Config as CoreConfig;
 use Gc\Registry;
  use GcFrontend\Controller\DbController as DbController; 

 class FunctionsController extends Action
 {
   // job accept count
   	public function jobAcceptCount($freelancer_id,$adapter){
		$sql_accept="select ja.* from job_action ja where ja.f_id='$freelancer_id' and ja.action=3"; // job accepted count 
		$results_accept = $adapter->query($sql_accept, $adapter::QUERY_MODE_EXECUTE); 
		$result_data_accept = $results_accept->current();
		$count_accept = $results_accept->count();
		return $count_accept;
	}
   // User blocked count	
   public function blockUserCount($freelancer_id,$adapter){
		$sql_bkl_user="select * from block_user where frelan_id='$freelancer_id'"; // block user count 
		$results_bkl = $adapter->query($sql_bkl_user, $adapter::QUERY_MODE_EXECUTE); 
		$result_data_bkl = $results_bkl->current();
		$count_date_bkl = $results_bkl->count();
		return $count_date_bkl;
	}
    // Check if User blocked by current employer or not   
    public function blockByEmp($freelancer_id,$eid,$adapter){
        $sql_bkl_user="select * from block_user where frelan_id='$freelancer_id' AND emp_id = '$eid'"; // block user count 
        $results_bkl = $adapter->query($sql_bkl_user, $adapter::QUERY_MODE_EXECUTE); 
        $result_data_bkl = $results_bkl->current();
        $count_date_bkl = $results_bkl->count();
        return $count_date_bkl;
    }
   // User block date :not Available on date	
   public function userBlockDate($freelancer_id,$job_date,$adapter){
   		$job_date = str_replace("/","-",$job_date);
   		$job_date = date('Y-n-j', strtotime($job_date));
   		$job_date1 = date('Y-n-d', strtotime($job_date));
		$sqlString_block="select * from user_work_calender where uid='".$freelancer_id."'";	
		$results_block = $adapter->query($sqlString_block, $adapter::QUERY_MODE_EXECUTE); 
		$result_data_block = $results_block->current();
		$block_date_array = unserialize($result_data_block['block_date']);

		$count_block_date = 0;
		foreach ($block_date_array as $key => $block_date) {
		 	if ($job_date == $block_date['date'] || $job_date1 == $block_date['date']) {
		 		$count_block_date = 1;
		 		break;
		 	}
		}
		return $count_block_date;
	}

	//Get freelancer book dates 
	public function getBookDate($uid,$job_date,$adapter){
		$job_date = str_replace("/","-",$job_date);
   		$job_date = date('Y-n-j', strtotime($job_date));
		$pCurrentDate = date("Y-m-d");
		$sqlGetPBookings = "SELECT * from freelancer_private_job WHERE priv_job_start_date >= '$pCurrentDate' AND f_id = $uid";
		$getPBooking = $adapter->query($sqlGetPBookings, $adapter::QUERY_MODE_EXECUTE);
		$getPCurrentBookings = $getPBooking->toArray();
		if (!empty($getPCurrentBookings)) {
		foreach($getPCurrentBookings as $pBooking){
			$bookDates[] = date('Y-n-j', strtotime($pBooking['priv_job_start_date']));  
			}
		}

		/* Get current month Website job bookings */
		$sqlGetBookings = "SELECT job_date FROM job_post WHERE job_id IN ( SELECT job_id FROM job_action WHERE f_id ='$uid' AND action = 3) and MONTH (STR_TO_DATE(job_date, '%d/%m/%Y')) = MONTH(NOW()) "; 
		//$sqlGetBookings = "SELECT job_date,job_post_desc FROM job_post WHERE job_date >= '$currentdate' AND job_status = 4 AND job_id IN (SELECT job_id FROM job_action WHERE f_id ='$uid' AND action = 3) ";
		$getBooking = $adapter->query($sqlGetBookings, $adapter::QUERY_MODE_EXECUTE);
		$getCurrentBookings = $getBooking->toArray();
		/* Gell All Website job bookings */
		$sqlGetAllBookings = "SELECT job_date,job_post_desc FROM job_post WHERE job_id IN ( SELECT job_id FROM job_action WHERE f_id ='$uid' AND action = 3) 
		"; 


		$getAllBooking = $adapter->query($sqlGetAllBookings, $adapter::QUERY_MODE_EXECUTE);
		$getAllCurrentBookings = $getAllBooking->toArray();

		if (!empty($getAllCurrentBookings)) {
			foreach ($getAllCurrentBookings as $key => $allBooking) {
				$timestamp = strtotime(str_replace('/', '-', $allBooking['job_date']) );
				$bookDates[] = date('Y-n-j', $timestamp);
			}
		}
		$count_book_date = 0;
		foreach ($bookDates as $key => $bookDate) {
			//echo "$job_date == $bookDate";
			if ($job_date == $bookDate) {
				$count_book_date = 1;
				break;
			}
		} 
		return $count_book_date;
	}


	// 
	public function userExpireCount($freelancer_id,$adapter){
		$sqlString_expire="select * from user_package_details where user_id='".$freelancer_id."' and package_status=0";	
		$results_expire = $adapter->query($sqlString_expire, $adapter::QUERY_MODE_EXECUTE);
		$count_expire = $results_expire->count();
		return $count_expire;
	}

    // 
    public function checkUserMembershipPlan($freelancer_id,$adapter){
        $sql_membership = "select * from user_package_details where user_id='".$freelancer_id."' and package_status = 1 AND package_expire_date > DATE(NOW()) ORDER BY pid DESC";    
        $results_membership = $adapter->query($sql_membership, $adapter::QUERY_MODE_EXECUTE);
        $count_membership = $results_membership->count();
        return $count_membership;
    }

	// Job start time
   	public function jobStartTime($emp_id,$adapter){
		$sqlGetStartTime="SELECT store_unique_time FROM user_extra_info WHERE uid='$emp_id'";	
	    $getStartTime = $adapter->query($sqlGetStartTime, $adapter::QUERY_MODE_EXECUTE);
	    $startTimeObj = $getStartTime->current(); 
	    $startTimeArray = unserialize($startTimeObj['store_unique_time']);
		return $startTimeArray['start_time'];
	}

	/* Multi store time formater */
	public function setTime($data,$index)
    {
    	$records = '';
    	foreach ($data as $key => $value) {
    		$records[] = array(
    				$key =>$value[$index]
    			);
    	}
    	return serialize($records);
    }
	
	/* Get time of day */
	public function getTimeOfDay($data,$day)
    {
    	$timeOfDay = '';
    	foreach($data as $dTime){
			$timeOfDay = $dTime[$day];
			if($timeOfDay != '') { break; }
		}
    	return $timeOfDay;
    }

    /* Cancellation Rate Freelancer */
    public function getFreCancellationRate($uid,$adapter)
    {
    	$sqlContCancellation = "SELECT * FROM job_cancel WHERE c_uid = '$uid' AND c_date >= DATE_SUB(now(), INTERVAL 6 MONTH)";	
        $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
        $finalCount = $contCancellation->count();
        
        $sqlAcceptedJob = "SELECT * FROM job_action WHERE ( action = '6' OR action = '3' ) AND f_id = '$uid' AND  create_date >= DATE_SUB(now(), INTERVAL 6 MONTH)";	
        $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);

        $countJobAccept = $acceptedJob->count();

        $freCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
        if ($freCancellationRate >= 100) {
            $freCancellationRate = number_format(100,2);
        }
    	return $freCancellationRate;	
    }

    /*Cancellation Rate Employer */
    public function getEmpCancellationRate($uid,$adapter,$startdate = null,$enddate = null)
    {
        if($enddate == null){ $enddate = date('Y-m-d');}
        if($startdate != null || $startdate != ''){
    		$sqlContCancellation = "SELECT * FROM job_post WHERE e_id = '$uid' AND ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' AND  create_date >= DATE_SUB(now(), INTERVAL 6 MONTH)) OR  ( job_status = '8' AND job_id IN ( SELECT j_id FROM  private_user_job_action WHERE (status = '5') AND  created_at >= DATE_SUB(now(), INTERVAL 6 MONTH)))  ) AND job_create_date BETWEEN '$startdate' AND '$enddate'";	
        	$contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
       		$finalCount = $contCancellation->count();
        
       		$sqlPostedJob = "SELECT * FROM job_post WHERE e_id = '$uid' AND ( job_status = 4 OR job_status = 5 OR job_status = 6 OR ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' AND  create_date >= DATE_SUB(now(), INTERVAL 6 MONTH))) OR ( job_status = '8' AND job_id IN ( SELECT j_id FROM  private_user_job_action WHERE (status = '5' OR status = '3') AND  created_at >= DATE_SUB(now(), INTERVAL 6 MONTH)))  ) AND job_create_date BETWEEN '$startdate' AND '$enddate'";	
        	$postedJob = $adapter->query($sqlPostedJob, $adapter::QUERY_MODE_EXECUTE);
        	$countJobPost = $postedJob->count();       
	}else{
    		$sqlContCancellation = "SELECT * FROM job_post WHERE e_id = '$uid' AND ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' AND  create_date >= DATE_SUB(now(), INTERVAL 6 MONTH)) OR  ( job_status = '8' AND job_id IN ( SELECT j_id FROM  private_user_job_action WHERE (status = '5') AND  created_at >= DATE_SUB(now(), INTERVAL 6 MONTH)))  )";	
        	$contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
       		$finalCount = $contCancellation->count();
        
        	$sqlPostedJob = "SELECT * FROM job_post WHERE e_id = '$uid' AND ( job_status = 4 OR job_status = 5 OR job_status = 6 OR ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' AND  create_date >= DATE_SUB(now(), INTERVAL 6 MONTH))) OR ( job_status = '8' AND job_id IN ( SELECT j_id FROM  private_user_job_action WHERE (status = '5' OR status = '3') AND  created_at >= DATE_SUB(now(), INTERVAL 6 MONTH)))  )";	
        	$postedJob = $adapter->query($sqlPostedJob, $adapter::QUERY_MODE_EXECUTE);
        	$countJobPost = $postedJob->count();       
	}

        $empCancellationRate = number_format(($finalCount/$countJobPost)*100,2);
        if ($empCancellationRate >= 100) {
            $empCancellationRate = number_format(100,2);
        }
    	return $empCancellationRate;	
    }

    /* Manage  Job Filter */
    public function elementToSort($element,$order){
    	if ($element == 'job_date') {
    		$element = "STR_TO_DATE(job_date, '%d/%m/%Y')";
    	}
    	if( $order == 'ASC'){
    		return 'ORDER BY '.$element.' ASC';
    	}else{
    		return 'ORDER BY '.$element.' DESC';
    	}
    }

    public function elementToSortUrl($filter='',$element,$order){
    	if ($filter != '' ) {
            $sortUrl = "?filter=$filter&sort_by=$element&order=$order";
        }else{
            $sortUrl = "?sort_by=$element&order=$order"; 
        }
        return $sortUrl;
    }

    /*Get User Information */
    public function getUserInfo($uid, $adapter)
    {
    	$sqlUserInfo = "SELECT * FROM user WHERE id = '$uid'";
        $userInfoObj = $adapter->query($sqlUserInfo, $adapter::QUERY_MODE_EXECUTE);
        $userInfo = $userInfoObj->current();        
        return $userInfo;
    }

    /* Get Deleted user Info */
    public function getDeleteUserInfo($uid, $adapter)
    {
        $sqlUserInfo = "SELECT * FROM user_leavers_table WHERE uid = '$uid'";
        $userInfoObj = $adapter->query($sqlUserInfo, $adapter::QUERY_MODE_EXECUTE);
        $userInfo = $userInfoObj->current();    
        /*echo "<pre>";
        print_r($userInfo);
        echo "</pre>";   */ 
        return $userInfo;
    }

    /*Get Private User Information */
    public function getPrivateUserInfo($uid, $adapter)
    {
        $sqlUserInfo = "SELECT * FROM private_user WHERE p_uid = '$uid'";
        $userInfoObj = $adapter->query($sqlUserInfo, $adapter::QUERY_MODE_EXECUTE);
        $userInfo = $userInfoObj->current();
        return $userInfo;
    }

    /*Get Freelancer ID From accepted job id */
    public function getFreelancerInfoFromAcceptedJob($jid, $adapter)
    {
    	$sqlFreInfo = "SELECT f_id FROM job_action WHERE job_id = '$jid' AND (action = '3' OR action = '4' OR action = '6' OR action = '7')  ORDER BY update_date DESC";
        $userFreObj = $adapter->query($sqlFreInfo, $adapter::QUERY_MODE_EXECUTE);
        $freId = $userFreObj->current();
        $freInfo = $this->getUserInfo($freId->f_id, $adapter);        
        if (empty($freInfo) && $freId->f_id) {
                $freDeletedInfo = $this->getDeleteUserInfo($freId->f_id, $adapter);
                $freInfoArray = explode(' ', $freDeletedInfo->user_name);
                $freInfo['firstname'] = $freInfoArray[0];
                $freInfo['lastname'] = $freInfoArray[1];
        }
        return $freInfo;
    }

    /*Get Private Freelancer ID From accepted job id */
    public function getPrivateFreelancerInfoFromAcceptedJob($jid, $adapter)
    {
        $sqlPrivateFreInfo = "SELECT puid FROM private_user_job_action WHERE j_id = '$jid' AND ( status = '3' OR status = '5')";
        $userPrivateFreObj = $adapter->query($sqlPrivateFreInfo, $adapter::QUERY_MODE_EXECUTE);
        $freId = $userPrivateFreObj->current();
        $freInfo = $this->getPrivateUserInfo($freId['puid'], $adapter);
        return $freInfo;
    }


    /* Check who cancel the job */
    public function whoCancelJob($jid,$adapter)
    {
    	$sqlCancelJob = "SELECT c_job_status FROM job_cancel WHERE c_job_id = '$jid'";
        $cancelJobObj = $adapter->query($sqlCancelJob, $adapter::QUERY_MODE_EXECUTE);
        $cancelJob = $cancelJobObj->current();
        return $cancelJob['c_job_status'];
    }

    /* Get approve Feedback By user & type Id */
    public function getFeedbackByUserId($adapter, $uid, $uType)
    {
        /*fetch feedback data */
        if ($uType == 2) {
            $type = 'emp_id';
        }else{
            $type = 'fre_id';
        }
   // $sqlFeedback="SELECT * FROM job_feedback WHERE  $type ='$uid' AND user_type = '$uType' AND status = 1 ORDER BY feedback_id DESC LIMIT 10 ";        
    
$sqlFeedback="SELECT * FROM job_feedback WHERE  $type ='$uid' AND user_type = '$uType' AND status = 1 AND  created_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) ORDER BY feedback_id DESC";


    $currentFeedbacks = $adapter->query($sqlFeedback, $adapter::QUERY_MODE_EXECUTE);
        $currentFeedbackData = (array)$currentFeedbacks->toArray();
        return $currentFeedbackData;
    }

    /* Overall rating by 10 or less */
    public function getOverallRating($allFeedback)
    {        
        $totalFeedback = count($allFeedback);
        $totalRatingGiven = 0;
        foreach ($allFeedback as $key => $allFeed) {
            //echo $allFeed['rating'].'<br/>';
            $totalRatingGiven += $allFeed['rating'];
        }

        $overallRate = $totalFeedback > 0 ? ($totalRatingGiven/($totalFeedback*5))*100 : 0;
        if ($overallRate > 0) {
            return $overallRate;
        }else{
            return 0;
        }        
    }

  /* Get feedback data for display chart */
     public function getOverallRatingforchart($currentFeedbackData)
    {
        error_reporting(0);
        $qusdata = $qus = $quscount =  array();
        foreach($currentFeedbackData as $currentFeedback){
            foreach(unserialize($currentFeedback['feedback']) as $feedback){
                $queid = $feedback['qusId'];
                $qusdata[$queid]+= $feedback['qusRate'];
                $quscount[$queid]+= 1;
                $qus[$queid]= $feedback['qus'];
            }  }

        $i = 1 ;
        foreach($qusdata as $key => $qusdata){
            $qusdata.'=='.$quscount[$key]."==".$qus[$key] ;
            $que[] = "Que".$i.": ".$qus[$key];
            $dataX[] = "Que".$i;
            $dataY[] = round(($qusdata/($quscount[$key]*5))*100,2);
            $i++ ; }
        return array('x' => $dataX , 'y' => $dataY);
    }

    /* Get feedback user information */
    public function getFeedbackUserInfo($adapter, $feedback_uid)
    {
        /* Fetch user data */
        $sqlFeedbackUser="SELECT u.firstname,u.lastname,ux.profile_image FROM user u, user_extra_info ux WHERE  u.id ='$feedback_uid' AND ux.uid = '$feedback_uid'";
        $feedbackUser = $adapter->query($sqlFeedbackUser, $adapter::QUERY_MODE_EXECUTE);
        $feedbackUserData = $feedbackUser->current();
if(empty($feedbackUserData)){
            $deleteduser = "SELECT * from user_leavers_table WHERE uid = ".$feedback_uid."";
            $deleteduserView = $adapter->query($deleteduser, $adapter::QUERY_MODE_EXECUTE);
            $feedbackUserData = $deleteduserView ->current();
        }
        return $feedbackUserData;
    }

    /* Get private job information by job id */
    public function getPrivateJobInfo($adapter, $job_id)
    {
        $currentdate = date('Y-m-d');
        /* Fetch user data */
        $sqlPrivateJob="SELECT * FROM freelancer_private_job WHERE  pv_id ='$job_id' AND priv_job_start_date = '$currentdate'";
        $privateJob = $adapter->query($sqlPrivateJob, $adapter::QUERY_MODE_EXECUTE);
        $privateJobrData = $privateJob->current();
        return $privateJobrData;
    }
    
         public function insertdeleteduser($uid)
     {
         $dbConfig = new DbController();
         $adapter = $dbConfig->locumkitDbConfig();
         $sqlFeedbackUser="SELECT firstname,lastname,email FROM user WHERE id ='$uid'";
         $feedbackUser = $adapter->query($sqlFeedbackUser, $adapter::QUERY_MODE_EXECUTE);
         $feedbackUserData = $feedbackUser->current();
         $reason = 'Profile Deleted by Admin' ;
         $user_name = $feedbackUserData['firstname'].' '.$feedbackUserData['lastname'] ;
         $user_email = $feedbackUserData['email'];

         $sqlGerRegisterDate="insert into user_leavers_table (uid,user_email,user_name,user_reason_to_leave,created_at) values('$uid','$user_email','$user_name','$reason',NOW())";
         $adapter->query($sqlGerRegisterDate, $adapter::QUERY_MODE_EXECUTE);
         return $adapter->getDriver()->getLastGeneratedValue();
     }

    public function getCetRatesByUid($uid = null,$adapter)
    {
        $cet_rate = 0;
        if ($uid != '') {
            $sql_cet = "SELECT cet FROM user_extra_info WHERE uid = '$uid'";
            $cet_obj = $adapter->query($sql_cet, $adapter::QUERY_MODE_EXECUTE);
            $cet_data_obj = $cet_obj->current();
            $cet_rate = $cet_data_obj->cet;
        }
        return $cet_rate;
    }

    // by cheng
     public function getCetRatesViewByUid($uid = null,$adapter)
     {
         $view = 0;
         if ($uid != '') {
             $sql_view = "SELECT view FROM user WHERE id = '$uid'";
             $view_obj = $adapter->query($sql_view, $adapter::QUERY_MODE_EXECUTE);
             $cet_data_obj = $view_obj->current();
             $view = $cet_data_obj->view;
         }
         return $view;
     }

    public function checkIfProfileComplete($uid = null, $cat_id, $adapter){
        $is_complete = 0;
        if ($uid != '' && $cat_id != '') {
            $sql = "SELECT CASE WHEN (SELECT count(*) FROM user_question WHERE required_status = 1 AND fquestion != '' AND cat_id = '$cat_id' ) = (SELECT count(*) FROM user_answer WHERE user_id = '$uid' AND type_value != '' AND question_id IN (SELECT id FROM user_question WHERE required_status = 1)) THEN 1 ELSE 0 END AS RowCountResult";
            $is_complete_obj = $adapter->query($sql, $adapter::QUERY_MODE_EXECUTE);
            $is_complete = $is_complete_obj->current();
            $is_complete = $is_complete->RowCountResult;            
        }        
        return $is_complete;
    }

    public function getLocumInterestedJobList($uid, $adapter){
        $sqlAviJobId=" SELECT job_id, job_rate, job_date, job_address, job_region,job_zip,store_id,job_status FROM job_post WHERE ( job_status = 1 OR job_status = 6 ) AND job_id IN( SELECT job_id FROM job_action WHERE f_id = '$uid' AND (action = 0 OR action = 1 ) ) ORDER BY job_date ASC";
        $aviJobIdObj = $adapter->query($sqlAviJobId, $adapter::QUERY_MODE_EXECUTE);
        $aviJobArray = $aviJobIdObj->toArray();
        $availableJobArray = '';
        if (!empty($aviJobArray)) {
            foreach ($aviJobArray as $aviJob) {
                $count_book_date = $this->getBookDate($uid,$aviJob['job_date'],$adapter);
                if(empty($count_book_date)){
                    $availableJobArray[] = $aviJob;
                }
            }
        }        
        return $availableJobArray;
    }

    public function delete_supplier($sp_id,$uid=null){
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        if($uid){
            $delete_sup_sql = "DELETE FROM add_supplier WHERE supplier_id = '$sp_id'";
            $adapter->query($delete_sup_sql, $adapter::QUERY_MODE_EXECUTE);
        }
    }
    


}