<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Job;
	use Gc\Mail;
	use Gc\Registry;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use FudugoApp\Controller\Notification\NotificationController as NotificationController;
	use FudugoApp\Controller\Store\StoreController as StoreController;
	use GcFrontend\Controller\JobmailController as MailController;
	use GcFrontend\Controller\FunctionsController as FunctionsController;
	use GcFrontend\Controller\EndecryptController as Endecrypt;
	use Gc\User\JobAction\Model as ActionModel;
	use Gc\User\Job\Model as JobModel;
	use Gc\User\JobCancel\Model as CancelModel;
	use Gc\User\Finance\Employertrans\Model as EmployertransModel;
	use Gc\view\Helper\Config as ConfigModule;
	use Gc\Core\Config as CoreConfig;
	use Gc\User\JobReminder\ReminderModel as ReminderModel;
	use Gc\User\JobReminder\OnDayModel as OnDayModel;
	use GcFrontend\Controller\ManageBlockDateController as ManageBlockDateController;
	use GcFrontend\Controller\FunctionsController as FunctionController;
	use Gc\User\Finance\Model as FinanceModel; 
  	use Gc\User\Finance\PrivateJobModel as PrivateJobFinanceModel; 
  	use Gc\User\Job\Collection as JobCollection;
  	use Gc\User\Finance\Income\Model as IncomeModel;
  	use Gc\User\JobReminder\OnDayModel as OnDayModule;
  	use GcFrontend\Controller\EndecryptController as Ecryption;
  	use GcFrontend\Helper\FinanceHelper as FinanceHelper;
  	use Gc\User\Finance\Expense\Model as ExpenseModel;
  	use GcFrontend\Controller\PackagePrivilegesController;

	Class JobActionController
	{
		public function jobAction($user_data)
		{
			$dbController 	= new DbController();	
			//call  Package Privileges Controller to check eligibility of package resources 
			$packagePrivilegesController = new PackagePrivilegesController();		
			$adapter 		= $dbController->locumkitDbConfig();
			$user_id		= $user_data['user_id'];
			$page_id		= $user_data['page_id'];
			$jobActionResponse = array();			
			switch ($page_id) {
				case 'interested-job-list':
					$jobActionResponse = $this->get_invite_job_list($user_id,$adapter);
					break;
				case 'accept-job':					
					$job_id = isset($user_data['job_id']) ? $user_data['job_id'] : null;
					$jobActionResponse = $this->job_accept($user_id,$job_id,$adapter);
					break;
				case 'freeze-job':
					$is_user_pkg_allow_job_freeze = $packagePrivilegesController->getCurrentPackagePrivilegesResources('job_freeze',$user_id,$adapter);
					if($is_user_pkg_allow_job_freeze==1){
						$job_id = isset($user_data['job_id']) ? $user_data['job_id'] : null;
						$jobActionResponse = $this->job_freeze($user_id,$job_id,$adapter);
					}else{
						$jobActionResponse=0;
					}
					break;
				case 'attend-job':					
					$job_id = isset($user_data['job_id']) ? $user_data['job_id'] : null;
					$attend = isset($user_data['is_attend']) ? $user_data['is_attend'] : null;
					$user_role = isset($user_data['user_role']) ? $user_data['user_role'] : null;
					$job_type = isset($user_data['job_type']) ? $user_data['job_type'] : null;
					$jobActionResponse = $this->job_attendance($user_id,$user_role,$job_id,$attend,$job_type,$adapter);
					break;
				case 'job-expense':										
					$job_id 	= isset($user_data['job_id']) ? $user_data['job_id'] : null;
					$job_type 	= isset($user_data['job_type']) ? $user_data['job_type'] : null;
					$request 	= isset($user_data['request']) ? $user_data['request'] : null;
					$data 		= isset($user_data['data']) ? $user_data['data'] : null;
					$jobActionResponse = $this->job_expense($user_id,$job_id,$job_type,$request,$data,$adapter);
					break;
			}
			
			return $jobActionResponse;
		}

		public function get_invite_job_list($user_id,$adapter){
		    $helpController = new HelperController();
		    $functionController = new FunctionController();
		    $aviJobArray = $functionController->getLocumInterestedJobList($user_id, $adapter);
		    foreach ($aviJobArray as $key => $jobRecords) {
		    	$aviJobArray[$key]['job_rate'] = $helpController->formate_price($jobRecords['job_rate']); 
		    	$aviJobArray[$key]['store_id'] = $this->get_store_name_by_id($jobRecords['store_id'], $adapter);
		    	$aviJobArray[$key]['allow_freeze'] = $this->is_job_allow_to_freeze($user_id,$jobRecords['job_id'],$jobRecords['job_date'],$jobRecords['job_status'], $adapter);
		    	
		    }

		    return json_encode($aviJobArray);
		}

		public function get_store_name_by_id($store_id, $adapter)
		{
			$sqlGetStore = "SELECT emp_store_name FROM employer_store_list WHERE emp_st_id='".$store_id."' ";
            $getStore = $adapter->query($sqlGetStore, $adapter::QUERY_MODE_EXECUTE);
            $getCurrentStore = $getStore->current();
            return $getCurrentStore['emp_store_name'];
		}

		public function is_job_allow_to_freeze($user_id,$job_id,$job_date,$status,$adapter)
		{
			$sqlFreezeJobId="SELECT * FROM  job_action WHERE  job_id = '".$job_id."' AND  f_notification = '1' AND f_id = '$user_id'";
            $freezeJobIdObj = $adapter->query($sqlFreezeJobId, $adapter::QUERY_MODE_EXECUTE);
            $freezeJobId = $freezeJobIdObj->current();
            $jobAllowFreezeDate = strtotime('+2 days');
            $jobWorkDate = strtotime(str_replace('/', '-', $job_date));
            if($status != 6 && empty($freezeJobId) && $jobAllowFreezeDate < $jobWorkDate) {
            	return 1;
            }else{
            	return 0;
            }
		}
		

		public function job_accept($user_id,$job_id,$adapter)
		{
			$manageBlockDateController 	= new ManageBlockDateController();
			$mailController 			= new MailController();
			$actionModel 				= new ActionModel();
			$jobModel 					= new JobModel();	
			$jobReminder    			= new ReminderModel(); 
			$onDayReminder  			= new OnDayModel(); 			
			$endecrypt 					= new Endecrypt();
			$functionController 		= new FunctionController();
			$notifyController 			= new NotificationController();

			$uid 			= $user_id;	
			$cjid 			= $job_id;
			$action_status 	= ''; 

			//  Check if user already booked for current date
			$sqlJobData 		= "SELECT job_date from job_post WHERE job_id = '$cjid'";	
	        $jobDataArray 		= $adapter->query($sqlJobData, $adapter::QUERY_MODE_EXECUTE);
	        $currentJobData 	= $jobDataArray->toArray();
	        $jobStartDate 		= $currentJobData[0]['job_date'];
			$count_book_date 	= $functionController->getBookDate($uid,$jobStartDate,$adapter);

			/* Check if invitetion send to current login user or not*/
			$sqlUser 	= "SELECT uid from job_invited_user WHERE uid='$uid' AND jid = '$cjid'";
	        $inviteUser = $adapter->query($sqlUser, $adapter::QUERY_MODE_EXECUTE);
	        $user 		= $inviteUser->toArray();

	        /* Check if current user already accept this job or not */
	        $sqlFreeze 		= "SELECT f_id,action,f_notification from job_action WHERE f_id='$uid' AND job_id = '$cjid'";	
	        $acceptUsers 	= $adapter->query($sqlFreeze, $adapter::QUERY_MODE_EXECUTE);
	        $acceptUser 	= $acceptUsers->toArray();
	            
	        if (!empty($user) && $uid == $user[0]['uid'] && $count_book_date == 0 ) {

	        	/* Check the job status */
	        	$sqlJobStatus 		= "SELECT job_status from job_post WHERE job_id = '$cjid'";	
		        $jobStatusArray 	= $adapter->query($sqlJobStatus, $adapter::QUERY_MODE_EXECUTE);
		        $jobStatus 			= $jobStatusArray->toArray();
		        $currentJobStatus 	= $jobStatus[0]['job_status'];

		        switch ($currentJobStatus) {
		        	case 1:	        	
		        		if (!empty($acceptUser)) {
				        	if ($acceptUser[0]['action'] == 0 || $acceptUser[0]['action'] == 1 ) {
				        		/* 
				        		*  Check the timeline of current job if current time is 30 
				        		*  min less than timeline time then user can not accept it
				        		*/
				        		date_default_timezone_set('Europe/London');
				        		$currentDate 	= date("d/m/Y");	        		
				        		$currentHr 		= date("H");
				        		$currentMin 	= date("i");
				        		$sqlTimeline	= "SELECT job_id,job_date_new,job_timeline_hrs from job_post_timeline WHERE job_id = '$cjid' AND (job_timeline_status = '3' OR job_timeline_status = '1')";	
						        $timeLineJobs 	= $adapter->query($sqlTimeline, $adapter::QUERY_MODE_EXECUTE);
						        $timeLineJob 	= $timeLineJobs->toArray();
					        	$currentHRMin 	= strtotime(date('d-m-Y H.i',strtotime("+30 minutes")));
					        	
						        if (!empty($timeLineJob)) {

						        	$myDate = $timeLineJob[0]['job_date_new'];
									$myDateNew = str_replace('/', '-', $myDate);
									$d = strtotime($myDateNew);
									$newJobDate = strtotime(date('d-m-Y H.i', $d));
							        
							        if ($newJobDate > $currentHRMin ) {	
							        	$jobModel->jobStatusUpdate($cjid,4);
							        	/* Upadate job action status*/
							        	$sqlJobData = "SELECT job_date,e_id from job_post WHERE job_id = '$cjid'";	
								        $jobDataArray = $adapter->query($sqlJobData, $adapter::QUERY_MODE_EXECUTE);
								        $currentJobData = $jobDataArray->toArray();
							        	$jobStartDate = $currentJobData[0]['job_date'];
							        	$jobEmpId = $currentJobData[0]['e_id'];
              							$jobReminderDate = $jobReminder->dateJobReminder($jobStartDate);
							        	$jobModel->jobStatusUpdate($cjid,4);
							        	$actionModel->updateJobaction($cjid,$uid,3,0);
							        	$jobReminder->insertJobReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
              							$onDayReminder->insertJobOnDayReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
							        	$mailController->sendAcceptMailToUser($uid,$cjid,$adapter);	
							        	$action_status = 'Job accepted successfully.';
							        	//$mobile_invitation_send = $notifyController->notification($job_id,$message="We are pleased to inform that the on of your booking has been confirmed for you.",$title="Booking confirmation",$jobEmpId,$types=""); 
								    }else{							        	
							        	$action_status = 'Sorry - this job is no longer available.';
							        }
						        		
						        }else{
						        	$sqlJobCheck = "SELECT job_date,job_start_time from job_post WHERE job_id = '$cjid' AND job_date > '$currentDate' ";	
							        $isJobCheck = $adapter->query($sqlJobCheck, $adapter::QUERY_MODE_EXECUTE);
							        $JobCheck = $isJobCheck->toArray();
							        //print_r($JobCheck);
							        if (!empty($JobCheck)) {
							        	$jobModel->jobStatusUpdate($cjid,4);
							        	/* Upadate job action status*/
							        	$sqlJobData = "SELECT job_date,e_id from job_post WHERE job_id = '$cjid'";	
								        $jobDataArray = $adapter->query($sqlJobData, $adapter::QUERY_MODE_EXECUTE);
								        $currentJobData = $jobDataArray->toArray();
							        	$jobStartDate = $currentJobData[0]['job_date'];
							        	$jobEmpId = $currentJobData[0]['e_id'];
              							$jobReminderDate = $jobReminder->dateJobReminder($jobStartDate);
							        	$jobModel->jobStatusUpdate($cjid,4);
							        	$actionModel->updateJobaction($cjid,$uid,3,0);
							        	$jobReminder->insertJobReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
              							$onDayReminder->insertJobOnDayReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
							        	$mailController->sendAcceptMailToUser($uid,$cjid,$adapter);
							        	$action_status = 'Job accepted successfully.';
							        	//$mobile_invitation_send = $notifyController->notification($job_id,$message="We are pleased to inform that the on of your booking has been confirmed for you.",$title="Booking confirmation",$jobEmpId,$types=""); 
								    }else{
								    	$action_status =  'Sorry - this job is no longer available.';
								    }
						        }
				        	}else{
				        		switch ($acceptUser[0]['action']) {
				        			case 2:
				        				$action_status = 'You have already apply for this job.';
				        				break;
				        			case 3:				        				
				        				$action_status = 'You have already accepted this job.';
				        				break;
				        			case 4:				        				
				        				$action_status = 'This job is done.';
				        				break;
				        			default:				        				
				        				$action_status = 'Invalid action.';
				        				break;
				        		}
				        	}	        	
				        }
		        		break;
		        	case 2:		        		
					    $action_status = 'Sorry - this job is no longer available.';
		        		break;
		        	case 3:		        		
		        		$action_status = 'Employer no longer needs a locum for this day and hence has removed the posting.';						
		        		break;
		        	case 4:	        		
		        		if (!empty($acceptUser) && $acceptUser[0]['action'] == 3 ) {
		        			$action_status = 'You have already accepted this job.';	
		        		}else{
		        			$action_status = 'Sorry - this job is no longer available.';
		        		}
		        		break;
		        	case 6:	       	
		        		/* Update job action status*/
			        	$sqlJobData 	= "SELECT job_date,e_id from job_post WHERE job_id = '$cjid'";	
				        $jobDataArray 	= $adapter->query($sqlJobData, $adapter::QUERY_MODE_EXECUTE);
				        $currentJobData = $jobDataArray->toArray();
			        	$jobStartDate 	= $currentJobData[0]['job_date'];
			        	$jobEmpId 		= $currentJobData[0]['e_id'];

			        	if ($uid == $acceptUser[0]['f_id'] && $acceptUser[0]['action'] == 1 && $acceptUser[0]['f_notification'] == 1) {
							$jobReminderDate = $jobReminder->dateJobReminder($jobStartDate);
				        	$jobModel->jobStatusUpdate($cjid,4);
				        	$actionModel->updateJobaction($cjid,$uid,3,0);
				        	$jobReminder->insertJobReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
							$onDayReminder->insertJobOnDayReminder($cjid,$jobEmpId,$uid,$jobStartDate,$jobReminderDate);
							$mailController->sendAcceptMailToUser($uid,$cjid,$adapter);	
				        	$action_status = 'Job accepted successfully.';
				        	//$mobile_invitation_send = $notifyController->notification($job_id,$message="We are pleased to inform that the on of your booking has been confirmed for you.",$title="Booking confirmation",$jobEmpId,$types=""); 
	    				}else{	    					
	    					$action_status = 'Thank you for your interest however this job is currently held by another locum - If it goes live again we shall notify you.';
	    				}	
		        		break;
		        	case 7:		        		
		        		$action_status = 'Employer no longer needs a locum for this day and hence has removed the posting.';
		        		break;	
		        	case 8:		        		
		        		$action_status = 'Employer no longer needs a locum for this day and hence has removed the posting.';
		        		break;	        	
		        	default:		        		
		        		$action_status = 'This is not a valid job.';							
		        		break;
		        }
	        }else{
	        	if($count_book_date != 0){
	        		$action_status = 'Sorry - you have already booking on this date.';
	        	}else{
	        		$action_status = 'Sorry - this job is no longer available.';
	        	}
	        }	        
	        return $action_status;
		}

		public function job_freeze($user_id,$job_id,$adapter)
		{
			$actionModel 	= new ActionModel();
			$jobModel 		= new JobModel();
			$uid 	= $user_id;
			$cjid 	= $job_id;

			/* Check if invitetion send to current login user or not */
			$sqlUser 	= "SELECT uid from job_invited_user WHERE uid='$uid' AND jid = '$cjid'";
	         $inviteUser = $adapter->query($sqlUser, $adapter::QUERY_MODE_EXECUTE);
	        $user 		= $inviteUser->toArray();

	        /* Check if current user already freeze this job or not */
	        $sqlFreeze 		= "SELECT f_id,action,f_notification from job_action WHERE f_id='$uid' AND job_id = '$cjid'";	
	        $freezeUsers 	= $adapter->query($sqlFreeze, $adapter::QUERY_MODE_EXECUTE);
	        $freezeUser 	= $freezeUsers->toArray();
	              
	        if (!empty($user) && $uid == $user[0]['uid']) {
	        	/* Check the job status */
	        	$sqlJobStatus = "SELECT job_status from job_post WHERE job_id = '$cjid'";	
		        $jobStatusArray = $adapter->query($sqlJobStatus, $adapter::QUERY_MODE_EXECUTE);
		        $jobStatus = $jobStatusArray->toArray();
		        $currentJobStatus = $jobStatus[0]['job_status'];
		        switch ($currentJobStatus) {
		        	case 1:
		        		if (!empty($freezeUser)) {
				        	if ($freezeUser[0]['f_notification'] < 1) {
				        		/* 
				        		*  Check the timeline of current job if current time is 30 
				        		*  min less than timeline time then user can not freeze it
				        		*/
				        		$sqlIsTimeline 	= "SELECT job_id from job_post_timeline WHERE job_id = '$cjid'";
						        $isTimeLineJobs = $adapter->query($sqlIsTimeline, $adapter::QUERY_MODE_EXECUTE);
						        $isTimeLineJob 	= $isTimeLineJobs->toArray();
						        date_default_timezone_set('Europe/London');
				        		$currentDate 	= date("m/d/Y");
				        		$currentHr 		= date("H");
				        		$currentMin 	= date("i");
				        		$currentHRMin 	= date('H.i',strtotime("+2 days"));

						        if (!empty($isTimeLineJob)) {
						        	$currentDate 	= date("d/m/Y");
					        		$sqlTimeline 	= "SELECT job_id,job_timeline_hrs,job_date_new from job_post_timeline WHERE job_id = '$cjid'";	
							        $timeLineJobs 	= $adapter->query($sqlTimeline, $adapter::QUERY_MODE_EXECUTE);
							        $timeLineJob 	= $timeLineJobs->toArray();
							        
							        $timelineNewDate 	= str_replace('/','-',$timeLineJob[0]['job_date_new']);
							        $currentDate 		= str_replace('/','-',$currentDate);
							        $date2 				= date_create($timelineNewDate);
									$date1 				= date_create($currentDate);
									$diff 				= date_diff($date1,$date2);
									$diff_timeline_date =  $diff->format("%a days");
							        
							        if (!empty($timeLineJob) && $diff_timeline_date > 0) {
							        	$actionModel->updateWaitingUnFreezeJobaction($cjid,$uid,5);
							        	$actionModel->updateFreezeJobaction($cjid,$uid,1,1);
							        	$jobModel->jobStatusUpdate($cjid,6);
							        	
							        	//$action_status = '<div class="notification success">Job freeze for 15 minute only.</div>';
							        	$action_status = 1;
							        }elseif($timelineNewDate == $currentDate && $timeLineJob[0]['job_timeline_hrs'] > $currentHRMin){
							        	$actionModel->updateWaitingUnFreezeJobaction($cjid,$uid,5,0);
							        	$actionModel->updateFreezeJobaction($cjid,$uid,1,1);
							        	$jobModel->jobStatusUpdate($cjid,6);
							        	
							        	//$action_status = '<div class="notification success">Job freeze for 15 minute only.</div>';
							        	$action_status = 1;
									}else{
							        	//$action_status = '<div class="notification error">Job is closed by employer.</div>';
							        	$action_status = 2;
							        }
							        	
							        
						        }else{
					        		$sqlJobCheck = "SELECT job_date,job_start_time from job_post WHERE job_id = '$cjid' AND job_date > '$currentDate' ";	
							        $isJobCheck = $adapter->query($sqlJobCheck, $adapter::QUERY_MODE_EXECUTE);
							        $JobCheck 	= $isJobCheck->toArray();
							       
							        if (!empty($JobCheck)) {
							        	$actionModel->updateWaitingUnFreezeJobaction($cjid,$uid,5,0);
							        	$actionModel->updateFreezeJobaction($cjid,$uid,1,1);
							        	$jobModel->jobStatusUpdate($cjid,6);
							        	
							        	//$action_status = '<div class="notification success">Job freeze for 15 minute only.</div>';
							        	$action_status = 1;
							        }else{
							        	//$action_status = '<div class="notification error">Job is closed by employer.</div>';
							        	$action_status = 2;
							        }
						        }
				        		
				        	}else{
				        		//$action_status ='<div class="notification error">You have already freeze this job , you cannot freeze it again.</div>';
				        		$action_status = 3;
				        	}	        	
				        }
		        		break;
		        	case 2:
		        		//$action_status = '<div class="notification error">Job is closed.</div>';
		        		$action_status = 2;
		        		break;
		        	case 3:		        		
		        		//$action_status = '<div class="notification error">Job is Disable</div>';
		        		$action_status = 4;
		        		break;
		        	case 4:
		        		//$action_status = '<div class="notification error">Job is Accepted by locum.</div>';
		        		$action_status = 5;
		        		break;
		        	case 6:
		        		if (!empty($freezeUser) && $freezeUser[0]['f_notification'] >= 1 ) {
		        			//$action_status = '<div class="notification error">You have already FREEZE this job.</div>';
		        			$action_status = 3;
		        		}else{		        			
		        			//$action_status = '<div class="notification error">Job is  freeze please check after some time.</div>';
		        			$action_status = 6;
		        		}	        		
		        		break;
		        	case 7:
		        		//$action_status = '<div class="notification error">Job is Deleted.</div>';
		        		$action_status = 7;
		        		break;
		        	case 8:
		        		//$action_status = '<div class="notification error">Job is Canceled By Employer.</div>';
		        		$action_status = 8;
		        		break;	        	
		        	default:
		        		//$action_status = '<div class="notification error">This is not a valid job.</div>';
		        		$action_status = 0;
		        		break;
		        }
	        }else{
	        	//$action_status = '<div class="notification error">You are not able to FREEZE this job.</div>';
	        	$action_status = 9;
	        }

	        return $action_status;
		}

		public function job_attendance($user_id,$user_role,$job_id,$attend,$job_type,$adapter)
		{
			$onDayModule 			= new OnDayModule();
			$mailController 		= new MailController();
			$financeModel 			= new FinanceModel();
			$privateJobFinanceModel = new PrivateJobFinanceModel();
			$jobCollection 			= new JobCollection();
			$functionController 	= new FunctionController();
			$financeHelper 			= new FinanceHelper();
			$incomefinance 			= new IncomeModel();
			$encypt 				= new Ecryption();
			
			$currentDate 			= date("Y-m-d");
			$u_id 					= $user_id;
			$presentStatus 			= ($attend == 0) ? 'no' : 'yes';
			$check_job_status 		= 'Attendance is already done.';


			if ($user_role == 2) {
				if (is_numeric($job_id) && $job_id > 0 && $presentStatus == 'yes') {
				  	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
				  	if ($job_type == 1) {
				  		/* Get job reminder dates information */
						$sqlOnDayJob = "SELECT * from job_on_day WHERE DATE(STR_TO_DATE(job_date, '%d/%m/%Y')) = DATE(NOW()) AND status='0' AND f_id = '$u_id'"; 
						$jobOnDayData = $adapter->query($sqlOnDayJob, $adapter::QUERY_MODE_EXECUTE);
						$jobOnDay = $jobOnDayData->toArray();

					    if (!empty($jobOnDay)) {
					      	$onDayModule->updateJobOnDayStatus($job_id,2);					      	
					      	foreach ($jobOnDay as $key => $value) {
						        $jobId  = $value['j_id'];
						        $jobDate  = $value['job_date'];
						        $jobFid = $value['f_id'];
						        $jobEid = $value['e_id'];
						        $yesBtnLink = '<a href="'.$serverUrl().'/attendance?j='.$encypt->encryptIt($jobId).'&u='.$encypt->encryptIt($jobEid).'&s='.$encypt->encryptIt('yes').'" style="padding: 8px 30px; font-size: 16px; font-weight: 700; background: #00A9E0; color: #fff; ">Yes</a> <a href="'.$serverUrl().'/attendance?j='.$encypt->encryptIt($jobId).'&u='.$encypt->encryptIt($jobEid).'&s='.$encypt->encryptIt('no').'" style="padding: 8px 30px; font-size: 16px; font-weight: 700; background: #ff0000; color: #fff; ">No</a> ';						        
				                $jobDetails = $financeHelper->getWebsiteJobdetail($jobId);
				                $check_finance = $financeHelper->checkFinanceincome($jobId,$jobFid,date('Y-m-d', strtotime(str_replace('/', '-', $jobDetails['job_date']))),1,1);
				             if (empty($check_finance)) { 
				                $check_job_status = 'Attendance confirmed.';
				                $mailController->sendOnDayNotificationToEmployer($jobId,$jobFid,$jobEid,$yesBtnLink,$adapter) ;
				                $financeIncomeSaveArray = array(
				                    'job_id'    => $jobId,
				                    'job_type'  => 1,
				                    'fre_id'    => $jobFid,
				                    'emp_id'    => $jobEid,
				                    'job_rate'  => $jobDetails['job_rate'],
				                    'job_date'  => date('Y-m-d', strtotime(str_replace('/', '-', $jobDetails['job_date']))),
				                    'location'  => $jobDetails['job_region'],
				                    'store'  => $jobDetails['store_nm'],
				                    'supplier'  => $jobDetails['first_nm'].' '.$jobDetails['last_nm'],
				                    'income_type'  => 1,
				                    'invoice_id'  => 0,
				                    'status'  => 1
				                );
				                $res =  $incomefinance->save($financeIncomeSaveArray);
				                if($res){
				                    $finance_trans = array(
				                        'trans_type_id' => $res ,
				                        'trans_type' 	=> '1'
				                    );
				                    $financeModel->save($finance_trans);
				                }
					     }else{
					       $check_job_status = 'Attendance is already done.';
					     }          
				            }
				        }
				  	}elseif($job_type == 2){
				  		/* Get private job information */ 
			          	$insert = 0;   
			         	$privateJobObj = $functionController->getPrivateJobInfo($adapter, $job_id);
			          	if (!empty($privateJobObj)) {            
				            $pJobId       =  $privateJobObj->pv_id; 
				            $pJobFid      =  $privateJobObj->f_id; 
				            $pJobEmpName  =  $privateJobObj->emp_name; 
				            $pJobRate     =  $privateJobObj->priv_job_rate; 
				            $pJobDate     =  $privateJobObj->priv_job_start_date;
				            $pJobLocation     =  $privateJobObj->priv_job_location;

			            	$check_finance = $financeHelper->checkFinanceincome($pJobId,$pJobFid,$pJobDate,1,2);           
			           		if (empty($check_finance)) {  
			           			$check_job_status = 'Attendance confirmed.';            
				              	$financeSaveArray = array(
					                'job_id'        => $pJobId,
					                'fre_id'        => $pJobFid,
					                'emp_name'      => $pJobEmpName,
					                'job_rate'      => $pJobRate,
					                'job_date'      => date('d/m/Y',strtotime( $pJobDate ))
				              	);
				              	$insert = $privateJobFinanceModel->save($financeSaveArray);

				              	/*---start---*/
				              	$financeIncomeSaveArray = array(
									'job_id'    => $pJobId,
									'job_type'   => 2,
									'fre_id'    => $pJobFid,
									'emp_id'    => 0,
									'job_rate'  => $pJobRate,
									'job_date'  => $pJobDate,
									'location'  => $pJobLocation,
									'store'     => $pJobEmpName,
									'supplier'  => $pJobEmpName,
									'income_type'  => 1,
									'invoice_id'  => 0,
									'status'  => 1
								);
				              	$res =  $incomefinance->save($financeIncomeSaveArray);
				              	if($res){
				                  	$finance_trans = array(
				                      	'trans_type_id' => $res ,
				                      	'trans_type'  => '1'
				                  	);
				                  	$financeModel->save($finance_trans);
				              	}
			              	/*---end---*/
			            	}else{
			            		$check_job_status = 'Attendance is already done.';
			            	}
				  		}
				  	}
			    }else{
			        $check_job_status = 'Offfsss...! Please inform employer about the reason.';
			    }
			}elseif($user_role == 3){
				if (is_numeric($job_id) && $job_id > 0 && $presentStatus == 'yes') {
					$sqlOnDayJob = "SELECT * from job_on_day WHERE DATE(STR_TO_DATE(job_date, '%d/%m/%Y')) = '$currentDate' AND status='1' AND e_id = '$u_id'";
					$jobOnDayData = $adapter->query($sqlOnDayJob, $adapter::QUERY_MODE_EXECUTE);
					$jobOnDay = $jobOnDayData->toArray();
					if (!empty($jobOnDay)) {
						$onDayModule->updateJobOnDayStatus($job_id,2);
						$check_job_status = 'Thanks...Have a nice time...';
					}else{
						$check_job_status = 'Offfsss...! Please ask locum about the reason.';
					}
				}else{
					$check_job_status = 'Offfsss...! Please ask locum about the reason.';
				}
			}

			return $check_job_status;
		}

		public function job_expense($user_id,$job_id,$job_type,$request,$data,$adapter)
		{
			$financeHelper 	= new FinanceHelper();
			if ($request == 1) {
				//get expense form info
				$cattype 		= $financeHelper->getExpencetype();				
				return json_encode($cattype);
			}
		

			if ($request == 2) {
				$jobCollection 			= new JobCollection();
				$expenseModel 			= new ExpenseModel();
				$financeModel 			= new FinanceModel();
				$functionController 	= new FunctionController();
				$privateJobFinanceModel = new PrivateJobFinanceModel();

				$cats = $data['cats'];
				$cost = $data['cost'];
				$expense_status = '';
				$jobDetails = $jobCollection->getJobDetailsByJobId($job_id);
				if ($job_type == 1) {
					foreach($cats as $key => $cat){
						$jobRate = '';
		                foreach ($jobDetails as $jobDetail) {
		                    $jobRate = $jobDetail->getJobRate();
		                    $jobDate = $jobDetail->getJobDate();
		                }

		                if($expenseModel->expenseCheck($user_id,$job_id,$cat,1)){
		                    $saveExpenseArray = array(
		                        'job_id' 		=> $job_id,
		                        'job_type' 		=> 1,
		                        'fre_id' 		=> $user_id,
		                        'cost' 		    => $cost[$key],
		                        'job_date' 	    => date('Y-m-d', strtotime(str_replace('/', '-', $jobDate))),
		                        'expense_type_id' 	=> $cat,
		                        'bank' 			=> 1, 
		                        'bank_date' 	=> date('Y-m-d'), 
		                    );

		                    $res =  $expenseModel->save($saveExpenseArray);
		                    if($res){
		                        $finance_trans = array(
		                            'trans_type_id' => $res ,
		                            'trans_type' 	=> '2'
		                        );
		                        $financeModel->save($finance_trans);
		                    }		                    
		                }else{
		                    $expense_status = '<div class="notification error"> You have already submited the expenses.</div>';
		                }
		                $expense_status = '<div class="notification success"> You have successfully submited the expenses.</div>';

					}
				}elseif($job_type == 2){
					$pJobDate = $pJobEmpName = '';
			        $privateJobObj = $functionController->getPrivateJobInfo($adapter, $job_id);
			         if (!empty($privateJobObj)) {
		                $pJobId = $privateJobObj->pv_id;
		                $pJobFid = $privateJobObj->f_id;
		                $pJobEmpName = $privateJobObj->emp_name;
		                $pJobRate = $privateJobObj->priv_job_rate;
		                $pJobDate = $privateJobObj->priv_job_start_date;
		                //     $pJobLocation = $privateJobObj->priv_job_location;
		            }
		            foreach($cats as $key => $cat){
		                if($expenseModel->expenseCheck($user_id,$job_id,$cat,2)){
		                    $saveExpenseArray = array(
		                        'job_id' 		=> $job_id,
		                        'job_type' 		=> 2,
		                        'fre_id' 		=> $user_id,
		                        'cost' 		    => $cost[$key],
		                        'job_date' 	    => date('Y-m-d', strtotime(str_replace('/', '-', $pJobDate))),
		                        'expense_type_id' => $cat,
		                        'description' 	=> $pJobEmpName,
		                        'bank' 			=> 1, 
		                        'bank_date' 	=> date('Y-m-d'),
		                    );

		                    $res =  $expenseModel->save($saveExpenseArray);
		                    if($res){
		                        $finance_trans = array(
		                            'trans_type_id' => $res ,
		                            'trans_type' 	=> '2'
		                        );
		                        $financeModel->save($finance_trans);
		                    }
		                   
		                }else{
		                   $expense_status = '<div class="notification error"> You have already submited the expenses.</div>';
		                }
		                $expense_status = '<div class="notification success"> You have successfully submited the expenses.</div>';
		            }
				}

				return $expense_status;

			}
		}

	}