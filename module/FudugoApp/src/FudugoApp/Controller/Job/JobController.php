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

	Class JobController
	{
		public function post_job($job_data)
		{

			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$storeController= new StoreController();
			$store_id 		= $job_data['job_info']['store_id'];
			$is_timeline	= isset($job_data['job_info']['is_timeline']) ? $job_data['job_info']['is_timeline'] : 0;			
			$store_info 	= $storeController->get_store_by_id($store_id, $adapter);

			$timeline_data 	= '';

			$e_id 			= $job_data['user_id'];
			$job_title 		= $job_data['job_info']['job_title'];
			$job_date 		= date('d/m/Y',strtotime($job_data['job_info']['job_date']));
			$job_rate 		= $job_data['job_info']['job_rate'];
			$job_post_desc	= $job_data['job_info']['job_post_desc'];
			$cat_id 		= $job_data['cat_id'];
			$job_region 	= $store_info['emp_store_region'];
			$job_zip 		= $store_info['emp_store_zip'];			
			$job_address 	= $store_info['emp_store_address'];
			$job_status 	= 1;
			$job_type 		= 1;
			$is_relist 		= 1;
			$job_edit_id = $job_data['job_edit_id'];
			if($job_edit_id != 0){
				$sql_old_job_delete = "UPDATE job_post SET job_status = 7 WHERE job_id = '$job_edit_id'";
				$adapter->query($sql_old_job_delete, $adapter::QUERY_MODE_EXECUTE);
			}

			$sql_job_insert = "INSERT INTO job_post (job_id,e_id,cat_id,job_title,job_date,job_start_time,job_post_desc,job_rate,job_type,job_address,job_region,job_zip,store_id,job_status,job_relist,job_create_date) VALUES('','$e_id','$cat_id','$job_title','$job_date','10','$job_post_desc','$job_rate','$job_type','$job_address','$job_region','$job_zip','$store_id','1','$is_relist',NOW())";
			$job_obj = $adapter->query($sql_job_insert, $adapter::QUERY_MODE_EXECUTE);
			$job_id = $adapter->getDriver()->getLastGeneratedValue();

			$sql_timeline="INSERT INTO job_post_timeline (job_id,job_date_new,job_timeline_hrs,job_rate_new,job_timeline_status) VALUES('".$job_id."','".$job_date."','10','".$job_rate."','1')";
			$timeline_data = $adapter->query($sql_timeline, $adapter::QUERY_MODE_EXECUTE);

			if ($is_timeline) {
				$timeline_data = $job_data['job_info']['timeline_data'];
				$result = array();
				foreach ( $timeline_data['job_date_new'] as $key => $name ) {
					$result[] = array( 
						'job_date_new' 		=> date('d/m/Y', strtotime($name)), 
						'job_rate_new' 		=> $timeline_data['job_rate_new'][$key], 
						'job_timeline_hrs' 	=> isset($timeline_data['job_timeline_hrs'][ $key ] ) ? $timeline_data['job_timeline_hrs'][ $key ] : 10
					);
				}
				
				// insert into job_post_timeline table values			
				if(!empty($result)) {
					foreach($result as $val){
						if ($val['job_rate_new'] && $val['job_rate_new'] != '') {
							$hours = date('G', strtotime($val['job_timeline_hrs'])) ;
							$sqlString_insert22="insert into job_post_timeline (job_id,job_date_new,job_timeline_hrs,job_rate_new,job_timeline_status) values('".$job_id."','".$val['job_date_new']."','".$hours."','".$val['job_rate_new']."','3')";
							$results22 = $adapter->query($sqlString_insert22, $adapter::QUERY_MODE_EXECUTE);
						}
					}
				}
			}

			return json_encode(array('job_id'=>$job_id));
		}

		/* Get Job Information */
		public function get_job_info_by_id($job_id, $adapter)
		{
			$sql_job="SELECT * FROM job_post WHERE job_id='$job_id' ";
			$job_obj = $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
			$job = $job_obj->toArray();
			return $job[0];
		}

		/* Send Job Invitation */
		public function send_job_invitation($user_invitation_data){

			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$emp_id 		= $user_invitation_data['user_id'];
			$cat_id 		= $user_invitation_data['cat_id'];
			$job_id 		= $user_invitation_data['job_id'];	
			$web_freelancer = (!empty($user_invitation_data['web_freelancer'])) ? array_filter($user_invitation_data['web_freelancer']) : 0;
			$pri_freelancer = (!empty($user_invitation_data['pri_freelancer'])) ? array_filter($user_invitation_data['pri_freelancer']) : 0;
			$all_freelancer = array(
					'web_freelancer' => $web_freelancer,
					'pri_freelancer' => $pri_freelancer
				);

			$is_invitation_send = $this->send_invitation($job_id,$all_freelancer,$adapter);
			return $is_invitation_send;
		}

		public function send_invitation($job_id,$all_freelancer,$adapter)
		{
			$mailController = new MailController();
			$helpController = new HelperController();
			$storeController= new StoreController();
			$funController 	= new FunctionsController();
			$endecrypt 		= new Endecrypt();			
			$notifyController = new NotificationController();
			$serverUrl 		= Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
			$host 			= $serverUrl();
			$config 		= Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$currency 		= $config->get('email_currency');
			$currency_code = $config->get('site_currency');
			$invite_satus	= array();

			$web_freelancer = $all_freelancer['web_freelancer'];
			$pri_freelancer = $all_freelancer['pri_freelancer'];
			$header 		= $mailController->mailHeader();
			$footer 		= $mailController->mailFooter();
			$job_info 		= $this->get_job_info_by_id($job_id, $adapter);

			$job_title		= $job_info['job_title'];
			$job_id 		= $job_info['job_id'];
			$j_id 			= $job_id;
			$jtype 			= 1;
			$emp_id 		= $job_info['e_id'];
			$job_rate 		= $currency.number_format($job_info['job_rate'],2);
			$subject_job_rate = $currency_code.number_format($job_info['job_rate'],2);
			$job_date 		= $job_info['job_date'];
			$job_address 	= $job_info['job_address'];
			$job_type 		= $job_info['job_type'];
			$job_post_desc 	= $job_info['job_post_desc'];
			$store_id 		= $job_info['store_id'];
			$job_zip 		= str_replace(' ', '',$job_info['job_zip']);
			$job_region 	= $job_info['job_region'];
			$job_create_date= $job_info['job_create_date'];
			$job_start_time = $job_info['job_start_time'];
			$job_day 		= date('l', strtotime(str_replace('/','-',$job_date)));


			//Current EMP cancellation percentage
			$cancellationRate = $funController->getEmpCancellationRate($emp_id,$adapter);
			$cancellationRate = ($cancellationRate > 0) ? $cancellationRate.'%' : '0.00%';
			//Current EMP feedback percentage
			$currentFeedbackData =  $funController->getFeedbackByUserId($adapter, $emp_id, 2);
			$feedbackRate = round($funController->getOverallRating($currentFeedbackData),2);
			$feedbackRate = ($feedbackRate > 0) ? $feedbackRate.'%' : '0.00%';
			

			$timeline_data 	= $this->get_job_timeline_info($job_id,$adapter);
			$sent_post_count= count($web_freelancer) + count($pri_freelancer);

			$store_info 		= $storeController->get_store_by_id($store_id, $adapter);

			$emp_store_name		= $store_info['emp_store_name'];
			$emp_store_address	= $store_info['emp_store_address'];
			$emp_store_region 	= $store_info['emp_store_region'];
			$emp_store_zip 		= $store_info['emp_store_zip'];
			$startTime 			= @unserialize( $store_info['store_start_time']);
			$endTime 			= @unserialize( $store_info['store_end_time']);
			$lunchTime 			= @unserialize( $store_info['store_lunch_time']);
			$emp_store_address .= ', '.$emp_store_region.', '.$emp_store_zip;

			//Store timing for posted day 
			$store_start_time 	= $funController->getTimeOfDay($startTime,$job_day);
			$store_end_time 	= $funController->getTimeOfDay($endTime,$job_day);
			$store_lunch_time 	= $funController->getTimeOfDay($lunchTime,$job_day).':00 (Min)';

			// Employer info
			$emp_details 		= $this->get_user_info_by_id($emp_id,$adapter);			
			$fname_emp			= $emp_details['firstname'].' '.$emp_details['lastname'];
			$email_emp			= $emp_details['email'];
			$uid 				= $emp_id;

			//employer extra info
			$viewDetailsEmpx 	= $this->get_employer_extra_info($emp_id,$adapter);
			$store_unique_time 	= unserialize($viewDetailsEmpx['store_unique_time']);
			$store_telephone 	= $viewDetailsEmpx['telephone'];
			$store_mobile 		= $viewDetailsEmpx['mobile'];
			if($store_telephone!=''){
				$store_contact_details = $store_telephone;
			}elseif($store_mobile!=''){
				$store_contact_details = $store_mobile;
			}			

			$job_timeline_data 	= $this->get_job_timeline_info($job_id,$adapter);

			//Admin Info 
			$admin_mail 		= $config->get('mail_from');
			$admin_name 		= $config->get('mail_from_name');

			// eamil variables  
			$freelancer_email_section1='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px;background-color:#2DC9FF;" colspan="2">LocumKit Job Invitation (Key Details)</th>
					</tr>
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_date.'</td>
					</tr>
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_rate.'</td>
					</tr>
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Contact Details</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_contact_details.'</td>
					</tr>
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Address</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$emp_store_address.'</td>
					</tr>
					<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">LocumKit Additional Booking Info</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;color:red; font-weight:bold;">'.$job_post_desc.'</td>
					</tr>
					</table>';
			$admin_email_section1='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px;background-color:#2DC9FF;" colspan="2">LocumKit Job Invitation (Key Details)</th>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$fname_emp.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer ID</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$uid.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ref</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_id.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_date.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_rate.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Increase rate timeline</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_timeline_data.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Contact Details</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_contact_details.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Address</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$emp_store_address.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;color:red; font-weight:bold;">'.$job_post_desc.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date posted</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d/m/Y',strtotime(str_replace('/', '-', $job_create_date) )).'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Number of people sent to</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$sent_post_count.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;"></th>
						<td style=" border: 1px solid black;  text-align:left;"> 
						<table style="text-align:left;" width="100%">
					 	<tr>
					 		<td width="50%" style="border-right:1px solid black;">SMS SEND : 0 </td>
							<td style="margin-left: 10px; display: block;">EMAIL SEND : '.$sent_post_count.'</td>
					 	</tr>
						</td>
					</tr>
				</table>';
			$employer_email_section1='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; background-color:#2DC9FF;" colspan="2">LocumKit Job Invitation (Key Details)</th>
					  </tr>
					 <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ref</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_id.'</td>
					 </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_date.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_rate.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Increase rate timeline</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job_timeline_data.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Contact Details</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_contact_details.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Address</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$emp_store_address.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;color:red; font-weight:bold;">'.$job_post_desc.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date posted</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d/m/Y',strtotime(str_replace('/', '-', $job_create_date) )).'</td>
					  </tr>
					  </table>';
		  	$privat_freelancer_email_section2='';

		  	// freelancer and private user terms and condition
			$freelancer_email_section4 = $mailController->locum_email_terms();

			//Check invitation already send 
			$invi_count = $this->check_invitation_send($job_id,$adapter);			
			$freelancer_email_section3 = $email_data = $email_data_emp1 = '';
			$send = 0;
			if ($invi_count == 0 ) {
			//if (1 ) {

				//Send invitation to web freelancer
				if(!empty($web_freelancer)){ 
					foreach($web_freelancer as $u_id => $u_status){	
						$freelancer_email_section3 = $email_data = $email_data_emp1 = '';
						$fre_data  	= $this->get_user_info_by_id($u_id,$adapter);		
						$email 		= $fre_data['email'];
						$invite_id 	= $u_id;
						$fname 		= $fre_data['firstname'];
						
						$sqlString_qu_email="select ua.*, uq.fquestion from user_answer ua,user_question uq where uq.fquestion!='' and uq.id=ua.question_id and ua.user_id='$invite_id'";
						$results_qu_email = $adapter->query($sqlString_qu_email, $adapter::QUERY_MODE_EXECUTE);
						$results_qu_email2 = $results_qu_email->toArray();
						$email_data = '';
						foreach ($results_qu_email2 as $ans_value) {
							$email_data.='
							  <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$ans_value['fquestion'].'</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$ans_value['type_value']).'</td>
							  </tr>';
						}
						
						

						$sqlString_empqu_email="select ua.*, uq.equestion from user_answer ua,user_question uq where uq.equestion!='' and uq.id=ua.question_id and ua.user_id='$uid'";
						$results_empqu_email = $adapter->query($sqlString_empqu_email, $adapter::QUERY_MODE_EXECUTE);
						$results_empqu_email2 = $results_empqu_email->toArray();
						$email_data_emp = '';
						foreach ($results_empqu_email2 as $ans_value) {
						  	$email_data_emp.='
							  <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$ans_value['equestion'].'</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$ans_value['type_value']).'</td>
							  </tr>';
						  
						}
			$email_data_emp.='
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store cancellation percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancellationRate.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store feedback percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$feedbackRate.'</td>
					</tr>';
						$freelancer_email_section2='<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						  </tr>
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						  </tr>
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes):</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						  </tr>'.$email_data_emp;
						$sqlString_details="select ux.*,u.user_acl_profession_id from user_extra_info ux, user u where ux.uid='$invite_id' and ux.uid=u.id";
						$results_user = $adapter->query($sqlString_details, $adapter::QUERY_MODE_EXECUTE);
						$results_user_data = $results_user->current();
						$search_category_id=$results_user_data['user_acl_profession_id'];
						$minimum_rate =$results_user_data['minimum_rate'];
						
						// get daily rate
						$new_date=str_replace("/","-",$job_date);
						$timepstamp=strtotime($new_date);
						$currentDay = date('l',$timepstamp);
						$fre_rate = unserialize( $minimum_rate);
						$daily_rate = $currency.number_format($fre_rate[$currentDay],2);
						
						//optometry infomation
						$freelancer_email_section3.='<tr>
							  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">GOC Number:</th>
							  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$results_user_data['goc'].'</td>
						  </tr>
						  <tr>
							  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Opthalmic number (OPL):</th>
							  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$results_user_data['aoc_id'].'</td>
						  </tr>';
						if ($results_user_data['aop']!= '') {
						  	$freelancer_email_section3.='<tr>
								  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance (AOP):</th>
								  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$results_user_data['aop'].'</td>
							  </tr>';
						}elseif($results_user_data['inshurance_company'] != '' && $results_user_data['inshurance_no'] != ''){
							$freelancer_email_section3.='<tr>
								  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance:</th>
								  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.ucfirst($results_user_data['inshurance_company']).'-'.$results_user_data['inshurance_no'].'</td>
							  	</tr>
								<tr>
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance expiry:</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$results_user_data['inshurance_renewal_date'].'</td>
							  	</tr>';
						}

						$freelancer_email_section3.= $email_data;
					 
						// insert into job_action table
						$sql_insertj = "INSERT INTO job_action(job_id,f_id,create_date) VALUES('$j_id','$invite_id',NOW())";
					    $rows_insertj = $adapter->query($sql_insertj, $adapter::QUERY_MODE_EXECUTE);
						
						$sql_insert = "INSERT INTO job_invited_user(uid,jid) VALUES('$invite_id','$j_id')";
					    $rows_insert = $adapter->query($sql_insert, $adapter::QUERY_MODE_EXECUTE);
						
					    if($jtype==1){
					    	$jobAllowFreezeDate = strtotime('+2 days');
					    	$jobWorkDate = strtotime(str_replace('/', '-', $job_date));

							
						//$link ='<a href="'.$host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('n').'&u='.$endecrypt->encryptIt($invite_id).'" style="float: left;  margin-bottom: 15px;  margin-top: -10px;"><img src="'.$host.'/public/frontend/images/accept.png" style="width:170px;"/></a>';
						
						$link ='<a href="'.$host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('n').'&u='.$endecrypt->encryptIt($invite_id).'" style="outline: none!important; border-radius: 25px;    float: left; margin-bottom: 15px; font-size: 18px; color: #fff; background-color: #2dc9ff; padding: 10px 35px; text-decoration: none; text-transform: uppercase; font-weight: 500;">Accept</a>';
						
							$smsLinkA = $host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('n').'&u='.$endecrypt->encryptIt($invite_id);

						if ($jobAllowFreezeDate < $jobWorkDate) {
						//$link .=' <p style="float: left; margin: 13px; font-size: 20px;"> OR &nbsp; </p> <a href="'.$host.'/freeze-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('n').'&u='.$endecrypt->encryptIt($invite_id).'" style="float: left;"><img src="'.$host.'/public/frontend/images/freez.png"/></a>';
						
						$link .=' <p style="float: left; margin: 13px; font-size: 20px;"> OR &nbsp; </p> <a href="'.$host.'/freeze-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('n').'&u='.$endecrypt->encryptIt($invite_id).'" style="outline: none!important; border-radius: 25px; float: left; margin-bottom: 22px; font-size: 18px;color: #fff; background-color: #2dc9ff; padding: 10px 35px; text-decoration: none; text-transform: uppercase;  font-weight: 500;">Freeze</a>';
							}
						}else{
							$link ='<a href="'.$host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('p').'&u='.$endecrypt->encryptIt($invite_id).'" style="outline: none!important; border-radius: 25px;    float: left; margin-bottom: 15px; font-size: 18px; color: #fff; background-color: #2dc9ff; padding: 10px 35px; text-decoration: none; text-transform: uppercase; font-weight: 500;">Accept</a> ';
						
								$smsLinkA = $host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('p').'&u='.$endecrypt->encryptIt($invite_id);
						}
						
						
						// send email to no of user
						$freelancer_email_section2=$freelancer_email_section2;
						$freelancer_email_section3=$freelancer_email_section3;
						if($freelancer_email_section3!=''){
							$freelancer_email_section3_data='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
							       <tr>
									<th style=" border: 1px solid black;  text-align:left; padding:5px;background-color:#2DC9FF;" colspan="2"> LocumKit Job Invitation – Information you provided us
									</th>
								  </tr>
								  <tr>
									<th style=" border: 1px solid black;  text-align:left; padding:5px;color:red; font-weight:bold;text-align:center;" colspan="2">
									Please check the details below and advise us immediately if this information is incorrect
									</th>
								  </tr>
								'.$freelancer_email_section3.'
								</table>';
						}
						
						$message_free= $header.'
								<div style="padding: 25px 50px 30px; text-align: left; ">
								<p>Hi '.$fname.',</p>
								<p>We would like to inform you that a new job that matches your requirements has been posted. You can see the job details below:</p>
								<h3>Job Information</h3>
								'.$freelancer_email_section1.'
								<br/>
								<p>'.$link.'<p>
								<br/>
								<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
								   <tr>
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; background-color:#2DC9FF;" colspan="2"> LocumKit Job Invitation (additional information)</th>
								  </tr>
								'.$freelancer_email_section2.'
								</table>
								<br/>
								'.$freelancer_email_section3_data.'
								<br/>
								'.$freelancer_email_section4.'
								
								</div>'.$footer;
								
						$fre_sub = 'LocumKit job notification: Date : '.$job_date.' / Location : '.$emp_store_address.' / Rate : '.$subject_job_rate;	
						
						$send=1;

						//send mobile push notification
						$mobile_invitation_send = $notifyController->notification($job_id,$message="Job Ref: $job_id , Date: $job_date, Location:$emp_store_address, Rate:$subject_job_rate, Open this message to view full details",$title="Job Invitation",$u_id,$types="acceptJob");

						// freelancer					
						$mailController->sendSMTPMail($message_free, $admin_mail, $email, $fname, $fre_sub);
						/*try{						
							$mail = new \Gc\Mail('utf-8', $message_free);
							$mail->getHeaders()->addHeaderLine('Content-type','text/html');
							$mail->setSubject($fre_sub);
							$mail->setFrom($admin_mail,$admin_name);
							$mail->addTo($email,$fname);
							$mail->send();
					
						}catch (Exception $e) { }*/
				
					}
				}
				
				
				// invitation for private user only
				
				if(!empty($pri_freelancer)){ 
					
				 	foreach($pri_freelancer as $u_id => $u_status){
				 		//send mobile push notification
						$mobile_invitation_send = $notifyController->notification($job_id,$message="Invited you for new Job.",$title="Job Invitation",$u_id,$types="acceptJob");	
						
						$pri_fre_data  	= $this->get_pri_user_info_by_id($u_id,$adapter);
						$email2 		= $pri_fre_data['p_email'];
						$invite_id2 	= $u_id;
						$name2 			= $pri_fre_data['p_name'];
						
						// insert into job action table
						$sql_insert_pj="INSERT INTO private_user_job_action(puid,emp_id,j_id,status) VALUES('$invite_id2','$uid','$j_id',1)";
				        $rows_insert_pj = $adapter->query($sql_insert_pj, $adapter::QUERY_MODE_EXECUTE); 
						
						$link2 ='<a href="'.$host.'/accept-job?j='.$endecrypt->encryptIt($job_id).'&jtype='.$endecrypt->encryptIt('1').'&utype='.$endecrypt->encryptIt('p').'&u='.$endecrypt->encryptIt($invite_id2).'" style="font-size: 18px;background-color: #2dc9ff;padding: 7px 30px;
color: #fff;text-transform: uppercase;text-decoration: none;border-radius: 25px;outline: none !important;margin-right:20px">Accept</a>';
						
						$sqlString_qu_email="select ua.*, uq.equestion from user_answer ua,user_question uq where uq.equestion!='' and uq.id=ua.question_id and ua.user_id='$uid'";
						$results_qu_email = $adapter->query($sqlString_qu_email, $adapter::QUERY_MODE_EXECUTE);
						$results_qu_email2 = $results_qu_email->toArray();
						$email_data = '';
						foreach ($results_qu_email2 as $ans_value) {
							$email_data.='
							<tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$ans_value['equestion'].'</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$ans_value['type_value']).'</td>
							  </tr>';
						}

					$email_data.='
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store cancellation percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancellationRate.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store feedback percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$feedbackRate.'</td>
					</tr>';
						$private_freelancer_email_section2 = $email_data;
						
							// send email to no of user
					    $message2=$header.'
							  <div style="padding: 25px 50px 5px; text-align: left; ">
							  <p>Hello '.$name2.',</p>
							   <p>Locumkit is a platform that matches employers with locums, with no middleman involved.</p>
							   <p>To find out more about Locumkit, please <a href="https://www.youtube.com/watch?v=uM4Og3BxQm0" target="_blank">click here</a> </p>
							<p>Our client is looking for a locum - please find below details for the day in question. To accept the job, please click on accept and we shall notify the employer, who can then close the job. </p>
							   <h3>Job Information</h3>
							  '.$freelancer_email_section1.'
							  <br/>
							  <p style="float:left;width:100%;">'.$link2.'<p>
<br/>
							  <p>To continue receiving job notifications like these please <a href="'.$host.'/private-invitation/" target="_blank">click here</a></p>
							  <br/>
							   <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
							   <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; background-color:#2DC9FF;" colspan="2"> LocumKit Job Invitation (additional information)</th>
							  </tr>
							  <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time:</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
							  </tr>
							  <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time:</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
							  </tr>
							  <tr>
								<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes):</th>
								<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
							  </tr>
								'.$private_freelancer_email_section2.'
							   </table>
							   <br/>
								  '.$freelancer_email_section4.'
							<br/>
							
							<p>About Locumkit:</p>
							<p>Locumkit is designed to connect employers with locums. Locumkit offers plenty of benefits, functions, and services that you will certainly find very useful. From a single location, you will be able to monitor your bookings, work history, financials, new job opportunities, and much more. </p>
							<p>Locumkit not only puts you at the center of our focus, we field highly cable teams, with depth and experience of Optometry and Accounting, on every job. Locumkit is a  bespoke & innovative platform created and run by experienced optometrists over 25 years of first hand experience of which 15 years has been as locums with a range of employers from multiples, independents, to eye casualties and domiciliary. </p>
							<p>In addition to that there are many other benefits of Locumkit such as: </p>
							<ul>
								<li><p>Get many more job bookings like this</p></li>
								<li><p>Get job bookings tailored to your requirements; day rate, distance willing to travel</p></li>
								<li><p>Get job reminders irrespective if from our website or "off website"</p></li>
								<li><p>Upto date accounting - accessed from anywhere, anytime</p></li>
								<li><p>Automated book keeping and all your statutory financial compliance taken care of</p></li>
							</ul>

							<p>Why not visit Locumkit and join the platform where you can have that many significant benefits and dramatically boost your job opportunities?</p>

							<p>Please visit our website for more information <a href="<a href="'.$host.'">localhost</a></p>
							<p>&nbsp;</p>
							</div>'.$footer;
						$send=1;
						$p_fre_sub = 'Locumkit job notification: Date : '.$job_date.' / Location : '.$emp_store_address.' / Rate : '.$subject_job_rate;
						$mailController->sendSMTPMail($message2, $admin_mail, $email2, $name2, $p_fre_sub);
						/*try{
							$mail = new \Gc\Mail('utf-8', $message2);
							$mail->getHeaders()->addHeaderLine('Content-type','text/html');
							$mail->setSubject($p_fre_sub);
							$mail->setFrom($admin_mail,$admin_name);
							$mail->addTo($email2,$name2);
							$mail->send();
						}catch (Exception $e){
				        }*/
				      
					}// foreach end for private
				 	
				}
				
				//send admin and employer email
				$sqlString_empqu_email="select ua.*, uq.equestion from user_answer ua,user_question uq where uq.equestion!='' and uq.id=ua.question_id and ua.user_id='$uid'";
				$results_empqu_email = $adapter->query($sqlString_empqu_email, $adapter::QUERY_MODE_EXECUTE);
				$results_empqu_email2 = $results_empqu_email->toArray();
				$email_data_emp1= '';
				foreach ($results_empqu_email2 as $ans_value) {
				  	$email_data_emp1.='
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$ans_value['equestion'].'</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$ans_value['type_value']).'</td>
					  </tr>';		  
				}
				$email_data_emp1.='
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store cancellation percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancellationRate.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store feedback percentage</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$feedbackRate.'</td>
					</tr>';
				
				$freelancer_email_section2='<tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time:</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
				  </tr>
				  <tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time:</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
				  </tr>
				  <tr>
					<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes):</th>
					<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
				  </tr>'.$email_data_emp1;
				  
				$message_admin=$header.'
					<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; ">
					<p>Hello <b>Admin</b>,</p>
					<p>A new job has just been posted by: <b>'.$fname_emp.'</b></p>
					<h3>Job Information</h3>
					'.$admin_email_section1.'
					<br/>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; background-color:#2DC9FF;" colspan="2"> LocumKit Job Invitation (additional information)</th>
					  </tr>
					'.$freelancer_email_section2.'
					</table>
					<br/>
					</div>'.$footer;
					$emp_sub = 'LocumKit: New job posting (#'.$job_id.')';		
					$message_employer=$header.'
					<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; ">
					<p>Hello <b>'.$fname_emp.'</b>,</p>
					<p>Your job posting has been confirmed and is now live. All selected locums have been notified and you will be notified once your job has been accepted. The job specifics are detailed below. </p>
					<h3>Job Information</h3>
					'.$employer_email_section1.'
					<br/>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; background-color:#2DC9FF;" colspan="2"> LocumKit Job Invitation (additional information)</th>
					  </tr>
					'.$freelancer_email_section2.'
					</table>
					<br/>
					<p>Should you need to cancel this job, please <a href="'.$host.'/cancel-job?e='.$job_id.'">click here</a>.</p>
					<p>Should you need to edit this job, please <a href="'.$host.'/managejob?e='.$job_id.'">click here</a>.</p>
					</div>'.$footer;

				$admin_sub = 'LocumKit job notification: New job posting : #'.$job_id;
				
				// admin
				try {
					$mailAdmin = new \Gc\Mail('utf-8', $message_admin);
					$mailAdmin->getHeaders()->addHeaderLine('Content-type','text/html');
					$mailAdmin->setSubject($admin_sub);
					$mailAdmin->setFrom($admin_mail,$admin_name);
					$mailAdmin->addTo($admin_mail,$admin_name);
					$mailAdmin->send();
				}catch (Exception $e){
				}
				
				//employer
				try {
					$mailEmp = new \Gc\Mail('utf-8', $message_employer);
					$mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
					$mailEmp->setSubject($emp_sub);
					$mailEmp->setFrom($admin_mail,$admin_name);
					$mailEmp->addTo($email_emp,$fname_emp);
					$mailEmp->send();
				}catch (Exception $e){
				}
				
				if($send==1){
					$sql_insert_invi="INSERT INTO check_invitation(job_id,invi_status,date_created) VALUES('$j_id','1',NOW())";
					$rows_insert_invi = $adapter->query($sql_insert_invi, $adapter::QUERY_MODE_EXECUTE);
				}
				$invite_satus['send'] = 1;
			}else{
				$invite_satus['send'] = 2;
			}
			
			return json_encode($invite_satus);
		}

		

		public function get_job_timeline_info($job_id,$adapter)
		{
			$config 		= Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$currency 		= $config->get('site_currency');
			$job_timeline_data = '';
			$sql_job_timeline	="SELECT * FROM job_post_timeline WHERE job_id='$job_id' AND job_timeline_status=3";
			$rows_jobtimeline 	= $adapter->query($sql_job_timeline, $adapter::QUERY_MODE_EXECUTE);
			$job_infotimeline	= $rows_jobtimeline->toArray();
			$job_infotimelinecnt= $rows_jobtimeline->count();
			if($job_infotimelinecnt >0){
				foreach($job_infotimeline as $result){
					$job_timeline_data.= '<p><strong>Date:</strong> '.$result['job_date_new'].' <strong>Rate:</strong> '.$currency.number_format($result['job_rate_new'],2).'</p>';
				}
			}else{
				$job_timeline_data = "N/A";
			}
			return $job_timeline_data;
		}

		public function get_user_info_by_id($user_id,$adapter)
		{
			$sqlUserinfo = "SELECT * FROM user WHERE id = '$user_id'";	
			$viewUserdata = $adapter->query($sqlUserinfo, $adapter::QUERY_MODE_EXECUTE);
			$userData = $viewUserdata->current();
			return $userData;
		}

		public function get_pri_user_info_by_id($u_id,$adapter){
			$sqlPriUserinfo = "SELECT * FROM private_user WHERE p_uid = '$u_id'";	
			$priUserinfoObj = $adapter->query($sqlPriUserinfo, $adapter::QUERY_MODE_EXECUTE);
			$priUserinfo 	= $priUserinfoObj->current();
			return $priUserinfo;
		}

		public function get_employer_extra_info($user_id,$adapter)
		{
			$sqlEmpinfo = "SELECT store_unique_time,telephone,mobile FROM user_extra_info WHERE uid = '$user_id'";	
			$viewEmpdata = $adapter->query($sqlEmpinfo, $adapter::QUERY_MODE_EXECUTE);
			$viewDetailsEmpx = $viewEmpdata->current();
			return $viewDetailsEmpx;
		}

		/* Check if invitation already send */
		public function check_invitation_send($job_id,$adapter)
		{
			$sql_invi	= "SELECT * FROM check_invitation WHERE job_id='$job_id' AND invi_status='1'";
			$rows_invi 	= $adapter->query($sql_invi, $adapter::QUERY_MODE_EXECUTE);
			$invi_count = $rows_invi->count();
			return $invi_count;
		}

		/* Add Private Freelancer */
		public function add_private_freelancer($user_data)
		{ 
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$user_id 		= $user_data['user_id'];
			$p_name 		= $user_data['private_freelancer']['p_name'];
			$p_email 		= $user_data['private_freelancer']['p_email'];
			$p_mobile 		= $user_data['private_freelancer']['p_mobile'];
			$updated_pri_fre= array();
			$check_pri_fre 	= $this->check_private_freelancer($user_id, $p_email, $adapter);
			if (!$check_pri_fre) {
				$status = 1;				
				$sql_check_pri_fre 	= "SELECT * FROM private_user WHERE p_email='$p_email' AND emp_id ='$user_id' AND status = '2' ";	
				
				$check_pri_fre_obj 	= $adapter->query($sql_check_pri_fre, $adapter::QUERY_MODE_EXECUTE);				
				$check_pri_fre = $check_pri_fre_obj->current();

				if (!empty($check_pri_fre)) {
					$sql_pri_fre 	= "UPDATE private_user SET status = '0', p_name='$p_name', p_mobile='$p_mobile' where p_email='$check_pri_fre->p_email'";
			   		$pri_fre 	= $adapter->query($sql_pri_fre, $adapter::QUERY_MODE_EXECUTE);
				}else{
					$sql_pri_fre 	= "INSERT INTO private_user (emp_id, p_name, p_email, p_mobile, status) values('".$user_id."','".$p_name."','".$p_email."','".$p_mobile."','0')";
			    	$pri_fre 	= $adapter->query($sql_pri_fre, $adapter::QUERY_MODE_EXECUTE);
				}
				
				$updated_pri_fre = array(
						'status' => 1,
						'data'	 => $this->getPrivateLocum($user_id,$adapter)
					);
			}else{
				$updated_pri_fre = array(
						'status' => 2
					);
			}
			return json_encode($updated_pri_fre);
		}

		/* Delete Private freelancer */
		public function delete_private_freelancer($user_data)
		{
			$dbController 	= new DbController();			
			$adapter 		= $dbController->locumkitDbConfig();
			$user_id 		= $user_data['user_id'];
			$p_uid 			= $user_data['p_uid'];			
			$updated_pri_fre= array();
			$sql_pri_fre 	= "UPDATE private_user SET status = '2' where p_uid='$p_uid'";
			$pri_fre 		= $adapter->query($sql_pri_fre, $adapter::QUERY_MODE_EXECUTE);
			$updated_pri_fre = array(
						'status' => 3,
						'data'	 => $this->getPrivateLocum($user_id,$adapter)
					);
			return json_encode($updated_pri_fre);
		}


		/* Check if private freelancer already added */
		public function check_private_freelancer($emp_id, $p_email, $adapter)
		{
			$sql_pri_fre 	= "SELECT p_email FROM private_user WHERE p_email='$p_email' AND emp_id ='$emp_id' AND status != 2 AND p_email != 'admin@localhost.com'";
			$pri_fre 		= $adapter->query($sql_pri_fre, $adapter::QUERY_MODE_EXECUTE);

			$sql_web_fre 	= "SELECT email FROM user WHERE email='$p_email' AND user_acl_role_id ='2'";
			$web_fre 		= $adapter->query($sql_web_fre, $adapter::QUERY_MODE_EXECUTE);

			if($pri_fre->count() == 0 && $web_fre->count() == 0){
				return false;
			}else{
				return true;
			}
		}

		public function getPrivateLocum($emp_id, $adapter)
		{
			$sql_pri_fre="SELECT * FROM private_user WHERE  emp_id ='$emp_id' AND status = '0' AND status != 2 ORDER BY p_name ASC";
			$pri_fre = $adapter->query($sql_pri_fre, $adapter::QUERY_MODE_EXECUTE);
			return $pri_fre->toArray();
		}

		/* Get list of job by user ID */
		public function get_job_list($user_data)
		{	

			$dbController 	= new DbController();
			$helpController = new HelperController();
			$storeController= new StoreController();
			$adapter 		= $dbController->locumkitDbConfig();
			$user_id 		= $user_data['user_id'];
			$role_id 		= $user_data['user_role'];
			$filter 		= isset($user_data['filter']) ? $user_data['filter'] : '';
			$limit 			= isset($user_data['offset']) ? ($user_data['offset'] * 15) : 15;
			$sortby			= isset($user_data['sortby']) ? $user_data['sortby'] : '';
			$sortele		= isset($user_data['sortele']) ? $user_data['sortele'] : 'DESC';
			$job_list 		= array();

			/* Employer Job List */
			if ($role_id == 3) {
				if ($filter) {
					if ($sortby != '' && $sortby == 'job_title') {
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status = '$filter' ORDER BY $sortby $sortele LIMIT 0, $limit";
					}else if($sortby != '' && $sortby != 'date') {
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status = '$filter' ORDER BY CAST($sortby AS UNSIGNED) $sortele LIMIT 0, $limit";
					}else{
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status = '$filter' ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') $sortele LIMIT 0, $limit";
					}					
				}else{
					if ($sortby != '' && $sortby == 'job_title') {
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status != '' AND job_status != '7'  ORDER BY $sortby $sortele LIMIT 0, $limit";
					}else if($sortby != '' && $sortby != 'date') {
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status != '' AND job_status != '7'  ORDER BY CAST($sortby AS UNSIGNED) $sortele LIMIT 0, $limit";
					}else{
						$sql_job_list	= "SELECT job_id,job_title,job_rate,job_date,job_status FROM job_post WHERE e_id = '$user_id' AND job_status != '' AND job_status != '7'  ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') $sortele LIMIT 0, $limit";
					}					
				}			
				$job_list_obj	= $adapter->query($sql_job_list, $adapter::QUERY_MODE_EXECUTE);
				$job_list_array	= $job_list_obj->toArray();				
				foreach ($job_list_array as $key => $job) {	
					$action_by_id=$this->action_by_jobID($job['job_id'],$role_id,$user_id,$adapter);
					$job_list[]	= array(
						'job_id' 		=> $job['job_id'],
						'job_title' 	=> strlen($job['job_title']) > 10 ? substr($job['job_title'],0,10)."..." : $job['job_title'],
						'job_rate' 		=> $helpController->formate_price((float)$job['job_rate']),
						'job_date' 		=> $job['job_date'],
						'job_status' 	=> $action_by_id.' '.$this->getStatusById($job['job_status']),
						'job_status_id' => $job['job_status'],
						'job_locum' 	=> $this->get_job_fre_info($job['job_id'], $adapter)
					);			
				}
			}
			/* Freelancer Job List */
			if ($role_id == 2) {
				if ($filter) {
					if ($sortby != '' && $sortby != 'date' ) {
						$sql_job_list	= "SELECT job_id,store_id,job_rate,job_date,job_status FROM job_post WHERE job_status='$filter' AND job_id IN (SELECT job_id FROM job_action WHERE f_id = '$user_id' AND (action = 1 OR action = 3 OR action = 4 OR action = 6 OR action = 7)) ORDER BY CAST($sortby AS UNSIGNED) $sortele LIMIT 0, $limit";
					}else{
						$sql_job_list	= "SELECT job_id,store_id,job_rate,job_date,job_status FROM job_post WHERE job_status='$filter' AND job_id IN (SELECT job_id FROM job_action WHERE f_id = '$user_id' AND (action = 1 OR action = 3 OR action = 4 OR action = 6 OR action = 7)) ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') $sortele LIMIT 0, $limit";
					}
				}else{
					if ($sortby != '' && $sortby != '' && $sortby != 'date') {
						$sql_job_list	= "SELECT job_id,store_id,job_rate,job_date,job_status FROM job_post WHERE (job_status = 4 OR job_status = 5 OR job_status = 8) AND job_id IN (SELECT job_id FROM job_action WHERE f_id = '$user_id' AND (action = 1 OR action = 3 OR action = 4 OR action = 6 OR action = 7 ))  ORDER BY CAST($sortby AS UNSIGNED) $sortele LIMIT 0, $limit";						
					}else{
						$sql_job_list	= "SELECT job_id,store_id,job_rate,job_date,job_status FROM job_post WHERE (job_status = 4 OR job_status = 5 OR job_status = 8) AND job_id IN (SELECT job_id FROM job_action WHERE f_id = '$user_id' AND (action = 1 OR action = 3 OR action = 4 OR action = 6 OR action = 7 ))  ORDER BY STR_TO_DATE(job_date, '%d/%m/%Y') $sortele LIMIT 0, $limit";
					}					
				}	

				$job_list_obj	= $adapter->query($sql_job_list, $adapter::QUERY_MODE_EXECUTE);
				$job_list_array	= $job_list_obj->toArray();
				foreach ($job_list_array as $key => $job) {	
					$action_by_id=$this->action_by_jobID($job['job_id'],$role_id,$user_id,$adapter);			
					
					$store_info = $storeController->get_store_by_id($job['store_id'], $adapter);
					$job_list[]	= array(
						'job_id' 		=> $job['job_id'],						
						'job_rate' 		=> $helpController->formate_price((float)$job['job_rate']),
						'job_date' 		=> $job['job_date'],
						'job_status' 	=> $action_by_id.' '.$this->getStatusById($job['job_status']),
						'job_status_id' => $job['job_status'],
						'job_store_name'=> ($store_info['emp_store_name'] != '') ? (strlen($store_info['emp_store_name']) > 10 ? substr($store_info['emp_store_name'],0,10)."..." : $store_info['emp_store_name']) : 'Store Deleted'
					);			
				}
			}						
			
			return json_encode($job_list);
		}

		/*Get Freelancer info for job */
		public function get_job_fre_info($job_id, $adapter)
		{
			$funController 	= new FunctionsController();
			$freInfo 		= $funController->getFreelancerInfoFromAcceptedJob($job_id, $adapter);
			if(!empty($freInfo)){
				$freName = '';
				if (isset($freInfo['firstname'])) {
					$freName .= $freInfo['firstname'].' ';
				}
				if (isset($freInfo['lastname'])) {
					$freName .= $freInfo['lastname'];
				}                 
            }else{
                $privateFreInfo = $funController->getPrivateFreelancerInfoFromAcceptedJob($job_id, $adapter);
                if(!empty($privateFreInfo)){
                    $freName = $privateFreInfo['p_name'];                  
                }else{
                    $freName = 'N/A';
                }
            }
            return $freName;
		}

		public function getStatusById($job_status)
		{
			$status = '';
			switch ($job_status) {
				case '1':
	                $status = 'Waiting';
	                break;
	            case '2':
	                $status = 'Closed';
	                break;
	            case '3':
	                $status = 'Disabled';
	                break;
	            case '4':
	                $status = 'Accepted';
	                break;
	            case '5':
	                $status = 'Completed';
	                break;
	            case '6':
	                $status = 'Frozen';
	                break;
	            case '8':
	                $status = 'Cancelled';
	                break;
			}
			return $status;
		}

		// Get Job Detail Information view job page
		public function get_job_detail_info($user_data)
		{	
			$dbController 			= new DbController();
			$helpController 		= new HelperController();
			$storeController 		= new StoreController();
			$functionsController 	= new FunctionsController();
			$adapter 				= $dbController->locumkitDbConfig();
			$user_id 				= $user_data['user_id'];
			$role_id 				= $user_data['user_role'];
			$cat_id 				= $user_data['user_profession'];
			$job_id 				= $user_data['job_id'];
			$job_details			= array();
			$emp_details			= array();
			$fre_details			= array();
			if ($role_id == 2) {
				$sql_job 	= "SELECT * FROM job_post WHERE job_id IN (SELECT job_id FROM job_action WHERE  f_id = '$user_id' AND job_id = '$job_id')";
		        $result 	= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
		        $getJobArray= $result->toArray(); 
		        $getJob 	= $getJobArray[0]; 	
				
			    $sqlString_getstore = "SELECT * FROM employer_store_list WHERE emp_st_id = '".$getJob['store_id']."'";
		        $result_store 	= $adapter->query($sqlString_getstore, $adapter::QUERY_MODE_EXECUTE);
		        $getStoreObj 	= $result_store->current();		        

				$store_address 	= $getStoreObj['emp_store_address'];
				$store_region 	= $getStoreObj['emp_store_region'];
				$startTime 		= unserialize( $getStoreObj['store_start_time']);
				$endTime 		= unserialize( $getStoreObj['store_end_time']);
				$lunchTime 		= unserialize( $getStoreObj['store_lunch_time']);
				$job_date 		= str_replace('/','-',$getJob['job_date']);
				$job_day 		=  date('l', strtotime($job_date));
				//Store timing for posted day 
				
				$getJob['store_start_time'] = $functionsController->getTimeOfDay($startTime,$job_day);
				$getJob['store_end_time'] 	= $functionsController->getTimeOfDay($endTime,$job_day);
				$getJob['store_lunch_time'] = $functionsController->getTimeOfDay($lunchTime,$job_day).' (Min)';

				$empExtraInfo 		= $this->get_employer_extra_info($getJob['e_id'],$adapter);
				
				$store_telephone 	= $empExtraInfo['telephone'];
			    $store_mobile 		= $empExtraInfo['mobile'];
			    if($store_telephone!=''){
				   $store_contact_details = $store_telephone;
			    }
			    elseif($store_mobile!=''){
				   $store_contact_details = $store_mobile;
			    }
			    $getJob['store_contact_details'] = $store_contact_details;			    
			    $getJob['store_address'] = $getJob['job_address'].', '.$getJob['job_region'].', '.$getJob['job_zip'];
				
				// Get Details from timeline table  
				$sqldetail_get 	= "SELECT * FROM job_post_timeline WHERE job_id = '$job_id' order by tid DESC";
		        $result_new = $adapter->query($sqldetail_get, $adapter::QUERY_MODE_EXECUTE);
		        $getJobObj_time = $result_new->current(); 
				$getcount 		= $result_new->count(); 

				/* View job Key Details */
				$job_details['job_post_date']  = str_replace('-','/', date('d-m-Y', strtotime($getJob['job_create_date'])));
				$job_details['job_date']  = $getJob['job_date'];
				$job_details['job_title'] = $getJob['job_title'];
				$job_details['job_rate']  = $helpController->formate_price($getJob['job_rate']);
				$job_details['store_contact_details']  = $store_contact_details;
				$job_details['store_address'] 	= $getJob['job_address'].', '.$getJob['job_region'].', '.$getJob['job_zip'];
				$job_details['job_details'] 	= $getJob['job_post_desc'];
				$job_details['job_status'] 		= $getJob['job_status'];
				

				/*Booking confirmation (additional information)*/			

				$user_qus_ans['start_time'] 	= $getJob['store_start_time'];
				$user_qus_ans['end_time'] 		= $getJob['store_end_time'];
				$user_qus_ans['lunch_time'] 	= $getJob['store_lunch_time'];
				/* Booking Confirmation – Details of Employer */
				if($getJob['job_status']==1){					
					/*ques details*/
			        $user_qus_ans['qus_ans'] = $this->getQusAnsByUid($getJob['e_id'],3,$cat_id,$adapter);
				}else{
					/*ques details*/
			        $user_qus_ans['qus_ans'] = $this->getQusAnsByUid($user_id,$role_id,$cat_id,$adapter);
				}
				
				/* Booking Confirmation – Details of Employer */
				$user_info 				= $this->getUserNameByID($getJob['e_id'],$adapter);
				$emp_details['id'] 		= $getJob['e_id'];
				$emp_details['name'] 	= $user_info;				

				/*Booking confirmation (additional information)*/
				$user_extra_info = $this->get_user_extra_info($user_id,$adapter);	
				$fre_details['stores'] 		= explode(',', $user_extra_info['store_id']);
				$fre_details['stores_exp'] 	= explode(',', $user_extra_info['store_data']);
				$fre_details['goc'] 		= $user_extra_info['goc'];
				$fre_details['opl'] 		= $user_extra_info['aoc_id'];
				$fre_details['aop'] 		= $user_extra_info['aop'];
				$fre_details['insurance'] 		= $user_extra_info['inshurance_company'];
				$fre_details['insurance_exp'] 	= $user_extra_info['inshurance_renewal_date'];
				$fre_details['min_rate'] 	= $this->getFormatedMinRate(unserialize($user_extra_info['minimum_rate']));
				$fre_details['max_dist'] 	= $user_extra_info['max_distance'].' Miles';
				//echo $store_id;
				if($job_details['job_status']==1){
				$fre_details['fre_qus']	= $this->getQusAnsByUid($user_id,2,$cat_id,$adapter);
			    }
				$job_final_details[0]	=  $job_details;
				$job_final_details[1]	=  $user_qus_ans;
				$job_final_details[2]	=  $emp_details;
				$job_final_details[3]	=  $fre_details;
			}

			if ($role_id == 3) {
				$sql_job 	= "SELECT * FROM job_post WHERE job_id = '$job_id' AND e_id='$user_id'";
		        $result 	= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
		        $getJobArray= $result->toArray(); 
		        $getJob 	= $getJobArray[0]; 	
				
			    $sqlString_getstore = "SELECT * FROM employer_store_list WHERE emp_st_id = '".$getJob['store_id']."'";
		        $result_store 	= $adapter->query($sqlString_getstore, $adapter::QUERY_MODE_EXECUTE);
		        $getStoreObj 	= $result_store->current();		        

				$store_address 	= $getStoreObj['emp_store_address'];
				$store_region 	= $getStoreObj['emp_store_region'];
				$startTime 		= unserialize( $getStoreObj['store_start_time']);
				$endTime 		= unserialize( $getStoreObj['store_end_time']);
				$lunchTime 		= unserialize( $getStoreObj['store_lunch_time']);
				$job_date 		= str_replace('/','-',$getJob['job_date']);
				$job_day 		=  date('l', strtotime($job_date));
				//Store timing for posted day 
				
				$getJob['store_start_time'] = $functionsController->getTimeOfDay($startTime,$job_day);
				$getJob['store_end_time'] 	= $functionsController->getTimeOfDay($endTime,$job_day);
				$getJob['store_lunch_time'] = $functionsController->getTimeOfDay($lunchTime,$job_day).':00 (Min)';

				$empExtraInfo 		= $this->get_employer_extra_info($getJob['e_id'],$adapter);
				
				$store_telephone 	= $empExtraInfo['telephone'];
			    $store_mobile 		= $empExtraInfo['mobile'];
			    if($store_telephone!=''){
				   $store_contact_details = $store_telephone;
			    }
			    elseif($store_mobile!=''){
				   $store_contact_details = $store_mobile;
			    }
			    $getJob['store_contact_details'] = $store_contact_details;			    
			    $getJob['store_address'] = $getJob['job_address'].', '.$getJob['job_region'].', '.$getJob['job_zip'];
				
				// Get Details from timeline table  
				$sqldetail_get 	= "SELECT * FROM job_post_timeline WHERE job_id = '$job_id' order by tid DESC";
		        $result_new = $adapter->query($sqldetail_get, $adapter::QUERY_MODE_EXECUTE);
		        $getJobObj_time = $result_new->current(); 
				$getcount 		= $result_new->count(); 

				/* View job Key Details */
				$job_details['job_post_date']  =  str_replace('-','/',date('d-m-Y',strtotime($getJob['job_create_date'])));
				$job_details['job_date']  = $getJob['job_date'];
				$job_details['job_title'] = $getJob['job_title'];
				$job_details['job_rate']  = $helpController->formate_price($getJob['job_rate']);
				$job_details['store_contact_details']  = $store_contact_details;
				$job_details['store_address'] 	= $getJob['job_address'].', '.$getJob['job_region'].', '.$getJob['job_zip'];
				$job_details['job_details'] 	= $getJob['job_post_desc'];
				$job_details['job_status'] 		= $getJob['job_status'];
				

				/*Booking confirmation (additional information)*/			

				$user_qus_ans['start_time'] 	= $getJob['store_start_time'];
				$user_qus_ans['end_time'] 		= $getJob['store_end_time'];
				$user_qus_ans['lunch_time'] 	= $getJob['store_lunch_time'];

				/*ques details*/
				$user_qus_ans['qus_ans'] 		= $this->getQusAnsByUid($user_id,$role_id,$cat_id,$adapter);
				

				/* Booking Confirmation – Details of Locum */
				$getActioncount = 0;
				if($getJob['job_status']==4 || $getJob['job_status']==5){
					$sqlact_get="SELECT * FROM job_action WHERE job_id = '$job_id' AND (action = 3 OR action = 4)";
		        	$result_action = $adapter->query($sqlact_get, $adapter::QUERY_MODE_EXECUTE);
		        	$getAction = $result_action->current();	  
					$getActioncount = $result_action->count();
				}
				if ($getActioncount) {
					$uid = $getAction->f_id;
				}else{
					$uid = $user_id;
				}
				
				$user_info 				= $this->getUserNameByID($uid,$adapter);
				$fre_details['id'] 		= $uid;
				$fre_details['name'] 	= $user_info;		
                 
	              $sqlPriFreeInfo = "SELECT p_name FROM   private_user WHERE p_uid IN ( SELECT puid FROM private_user_job_action WHERE j_id = '$job_id' AND (status = 3 OR status = 4))  ";	
				  $priFreeInfoObj = $adapter->query($sqlPriFreeInfo, $adapter::QUERY_MODE_EXECUTE);
				  $priFreeInfo = $priFreeInfoObj->current();
				  if(!empty($priFreeInfo)){
				  $fre_details['name']=$priFreeInfo['p_name'];
				  $fre_details['id']='Private Locum';			  
				  	$fre_qus_details['fre_qus']=null;
				  }else{
				  	/*Booking confirmation (additional information)*/				
				     $fre_qus_details['fre_qus']	= $this->getQusAnsByUid($uid,2,$cat_id,$adapter);
				  }

				

				$job_final_details[0]	=  $job_details;
				$job_final_details[1]	=  $user_qus_ans;
				$job_final_details[2]	=  $fre_details;
				$job_final_details[3]	=  $fre_qus_details;
				
			}

			
			return json_encode($job_final_details);
		}

		public function getQusAnsByUid($uid,$role_id,$cat_id,$adapter)
		{
			$sql_qus_data 	= "SELECT qu.fquestion AS fq,qu.equestion AS eq,qu.type_key AS tk,qu.type_value AS tv,qu.id AS qid,qu.required_status,qu.range_type_unit,qu.range_type_condition FROM user_question qu WHERE cat_id='$cat_id'";	
		    $qus_data_obj 	= $adapter->query($sql_qus_data, $adapter::QUERY_MODE_EXECUTE);
			$qus_data 		= $qus_data_obj->toArray(); //print_r($result_data);
			$qus_data_count = count($qus_data);
			$txt_ans		= "";
			$sel_ans		= "";
			$chk_ans 		= "";
			$req_status 	= "";
			$ques_details 	= array();
			if( $qus_data_count > 0 ){
				foreach($qus_data as $resultset){
					$sql_ans		= "SELECT * FROM user_answer WHERE user_id='$uid' AND question_id='".$resultset['qid']."'";
					$results_ans 	= $adapter->query($sql_ans, $adapter::QUERY_MODE_EXECUTE);
			        $result_data_ans = $results_ans->current();					 
					$required 		= $resultset['required_status'];					
					if($role_id==2){
						$question = $resultset['fq'];
					}else{
						$question = $resultset['eq'];
					}

					if($resultset['tk']==1 && $question!=''){ // text field
					    // comapre user_answer and user_question table question id
						if($result_data_ans['question_id']==$resultset['qid']){ $txt_ans=$result_data_ans['type_value'];}
							$ques_details[] = array('qus' => $question, 'ans' => $txt_ans );
					}
					if($resultset['tk']==2 && $question!=''){ // select option
					    $question_data = unserialize($resultset['tv']); 
						$ques_details[]= array('qus' => $question, 'ans' =>$result_data_ans['type_value']);
					}
					if($resultset['tk']==3 && $question!=''){ // multiselect option
						$question_data2 = unserialize($resultset['tv']); 
					    $ques_details[]= array('qus' => $question, 'ans' =>str_replace(',',' / ',$result_data_ans['type_value']));
					}
					if($resultset['tk']==4 && $question!=''){ // select option for range
					     $range_type_unit=$resultset['range_type_unit'];
						 $range_type_condition=$resultset['range_type_condition'];  //1. Greater than 2. Greater than OR equel to 3. Less than 4. Less than OR equel to 5. Equel to
						//condition_arr
						$condition_arr = array("1"=>"Greater than","2"=>"Greater than OR equal to","3"=>"Less than","4"=>"Less than OR equal","5"=>"Equal to");						
						$ques_details[] = array('qus' => $question, 'ans' =>$result_data_ans['type_value'].$range_type_unit);
					}
					if($resultset['tk']==5 && $question!=''){ // select option
						$ques_details[] = array('qus' => $question, 'ans' =>str_replace(',',' / ',$result_data_ans['type_value']).' '.$resultset['range_type_unit']);
					}
					if($resultset['tk']==6 && $question!=''){ // select option
						$ques_details[] = array('qus' => $question, 'ans' =>$result_data_ans['type_value'].' '.$resultset['range_type_unit']);
					}				
				}
			}		

			return $ques_details;
		}

		public function getUserNameByID($uid,$adapter)
		{
			$user_name = '';
			$sql_user 	= "SELECT firstname,lastname FROM user WHERE id='$uid'";
			$user_obj 	= $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			$user		= $user_obj->toArray();
			if (empty($user[0])) {
				$sql_user 	= "SELECT user_name FROM user_leavers_table WHERE uid='$uid'";
				$user_obj 	= $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
				$user		= $user_obj->toArray();
				$user_name 	= $user[0]['user_name'];
			}else{
				$user_name 	= $user[0]['firstname'].' '.$user[0]['lastname'];
			}
			return $user_name;
		}
		public function get_user_extra_info($user_id,$adapter)
		{
			$sql_user_extra = "SELECT * FROM user_extra_info WHERE uid = '$user_id'";	
			$user_extra_obj	= $adapter->query($sql_user_extra, $adapter::QUERY_MODE_EXECUTE);
			$user_extra 	= $user_extra_obj->toArray();			
			return $user_extra[0];
		}

		public function getFormatedMinRate($min_rate)
		{
			$helpController 		= new HelperController();
			if (!empty($min_rate)) {
				$min_rate['Monday'] 	= $helpController->formate_price($min_rate['Monday']);
				$min_rate['Tuesday'] 	= $helpController->formate_price($min_rate['Tuesday']);
				$min_rate['Wednesday'] 	= $helpController->formate_price($min_rate['Wednesday']);
				$min_rate['Thursday'] 	= $helpController->formate_price($min_rate['Thursday']);
				$min_rate['Friday'] 	= $helpController->formate_price($min_rate['Friday']);
				$min_rate['Saturday'] 	= $helpController->formate_price($min_rate['Saturday']);
				$min_rate['Sunday'] 	= $helpController->formate_price($min_rate['Sunday']);
			}
			return $min_rate;
		}

		/* Perform job action */
		public function job_action($job_data)
		{
			$dbController 	= new DbController();
			$adapter 		= $dbController->locumkitDbConfig();
			$job_id 		= $job_data['job_id'];
			$user_id 		= $job_data['user_id'];
			$action 		= $job_data['job_action'];

			if ($action == 'duplicate') {
				$sql_job 		= "SELECT * FROM job_post WHERE job_id = '$job_id' AND e_id='$user_id'";
		        $result 		= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
		        $getJobArray	= $result->toArray();

				$sqlString_time="SELECT * FROM job_post_timeline WHERE job_id='$job_id' and job_timeline_status !=2 ";	
				$results_time = $adapter->query($sqlString_time, $adapter::QUERY_MODE_EXECUTE);
				$resultset_count = $results_time->count();  
				$resultset_time = $results_time->toArray();
				$getJobArray[0]['timeline_job_data'] = $resultset_time;				
		        $getJob 		= $getJobArray[0]; 	
		        //print_r($getJob);
				return json_encode($getJob);
			}	

			if ($action == 'delete') {
				$sql_delete_job = "UPDATE job_post SET job_status = '7' WHERE job_id = '$job_id'";
    			$delete_job = $adapter->query($sql_delete_job, $adapter::QUERY_MODE_EXECUTE);
    			return 1;
			}
			if ($action == 'disable') {
				$sql_delete_job = "UPDATE job_post SET job_status = '3' WHERE job_id = '$job_id'";
    			$delete_job = $adapter->query($sql_delete_job, $adapter::QUERY_MODE_EXECUTE);
    			return 1;
			}
			if ($action == 'enable') {
				$sql_delete_job = "UPDATE job_post SET job_status = '1' WHERE job_id = '$job_id'";
    			$delete_job = $adapter->query($sql_delete_job, $adapter::QUERY_MODE_EXECUTE);
    			return 1;
			}
			if ($action == 'cancel') {
				$this->cancelJob($job_data);
				return 1;
			}
		}

		public function cancelJob($job_data)
		{
			$employertransModel = new EmployertransModel();
			$dbConfig 			= new DbController();
			$jobModel 			= new JobModel();
			$actionModel 		= new ActionModel();
			$cancelModel 		= new CancelModel();
			$mailController 	= new MailController();
			$adapter 			= $dbConfig->locumkitDbConfig();
			$cjid 				= $job_data['job_id'];
			$uid 				= $job_data['user_id'];
			$uType 				= $job_data['user_role'];
			$cancel_reason		= $job_data['cancel_reason'];


			if ($uType == 3 ) {
				//Notification to freelancer 
				$sqlInvitedFre = "SELECT f_id FROM job_action WHERE job_id = '$cjid' AND action = 3";
				$result = $adapter->query($sqlInvitedFre, $adapter::QUERY_MODE_EXECUTE);
	        		$getFreObj = $result->toArray();

				
	        		if(count($getFreObj) > 0){
		        		$sqlCheckJobStatus="SELECT * FROM job_post WHERE job_id = '$cjid' AND e_id='$uid' AND (job_status = 4) ";
					$checkJobStatus= $adapter->query($sqlCheckJobStatus, $adapter::QUERY_MODE_EXECUTE);
					$jobStatus= $checkJobStatus->current();
					//Insert reson of cancelation
					$cancelModel->addCancelRecord($cjid,$uid,3,$cancel_reason,1);

					if(!empty($jobStatus)){
						//Employer Cancel job action update
						$actionModel->updateEmpCancelJobaction($cjid,7,1); // accepted job
		        	}else{
						//Employer Cancel job action update
						$actionModel->updateEmpCancelJobaction($cjid,8,0); // Waiting job
		        	}
		        	//Job Main table status update
					$jobModel->jobStatusUpdate($cjid,8);
		        	foreach ($getFreObj as $key => $freData) {
			        	$fid = $freData['f_id'];
			        	$mailController->cancelJobByEmpNotificationToFreelancer($fid , $cjid,$cancel_reason, $adapter );
			        	// Notify to Employer 
			        	$mailController->cancelJobByEmpNotificationToEmployer($uid ,$fid, $cjid,$cancel_reason, $adapter );
			        	// Notify to Admin 
			        	$mailController->cancelJobByEmpNotificationToAdmin($uid , $cjid,$cancel_reason, $adapter );
			        	
			        	//delete data from employer finance
		                	$employertransModel->deleteFinanceByjobid($uid,$cjid,$fid);
			        	
		        	}
		        	
		        }else{
		            //Notification to private freelancer
		     
		            $sqlInvitedpFre = "SELECT puid FROM private_user_job_action WHERE j_id = '$cjid' AND status = 3";
		            $result = $adapter->query($sqlInvitedpFre, $adapter::QUERY_MODE_EXECUTE);
		            $getpFreObj = $result->toArray();
		            //Job Main table status update
					$jobModel->jobStatusUpdate($cjid,8);
		            foreach ($getpFreObj as $key => $freData) {
		            	$sqlUpdatepFre = "UPDATE private_user_job_action SET status = 5 WHERE j_id = '$cjid' AND puid = '$fid'";
		            	$result = $adapter->query($sqlUpdatepFre, $adapter::QUERY_MODE_EXECUTE);

		                $fid = $freData['puid'];
		                $mailController->cancelJobByEmpNotificationToPrivateFreelancer($fid , $cjid,$cancel_reason, $adapter );
		                // Notify to Employer
		                $mailController->cancelJobByEmpNotifyToEmployerIFPrivatefreelancer($uid ,$fid, $cjid,$cancel_reason, $adapter );
			   			// Notify to Admin
		                $mailController->cancelJobByEmpNotificationToAdmin($uid , $cjid,$cancel_reason, $adapter );
		          
		            }   
		        }
	        	
			}elseif($uType == 2){
				
				$sqlJobEmp = "SELECT e_id,job_relist FROM job_post WHERE job_id = '$cjid'";
				$result = $adapter->query($sqlJobEmp, $adapter::QUERY_MODE_EXECUTE);
	        		$getEmpObj = $result->toArray();
	        	
				//Insert reson of cancelation
				$cancelModel->addCancelRecord($cjid,$uid,2,$cancel_reason,2);
				//Job Main table status update
				$jobModel->jobStatusUpdate($cjid,8);
				//Freelancer Cancel job action update
				$actionModel->updateJobaction($cjid,$uid,6,0);

				foreach ($getEmpObj as $key => $empData) {
		        	$eid = $empData['e_id'];
		        	$is_relist = $empData['job_relist'];

					$mailController->cancelJobByFreNotificationToFreelancer($eid ,$uid, $cjid,$cancel_reason, $adapter );
			        // Notify to Employer 
		        	$mailController->cancelJobByFreNotificationToEmployer($uid ,$eid, $cjid,$cancel_reason,$is_relist, $adapter );
		        	// Notify to Admin 
		        	$mailController->cancelJobByFreNotificationToAdmin($uid , $cjid,$cancel_reason, $adapter );
		        	
		        	//delete data from employer finance
	                $employertransModel->deleteFinanceByjobid($eid,$cjid,$uid);
		        }
			}

		}

		//Get job action by job id
		public function action_by_jobID($job_id,$roleId,$user_id,$adapter){
			if($roleId == 2){
				$sql_job 	= "SELECT action FROM job_action WHERE  f_id = '$user_id' AND job_id = '$job_id'";
			}else{
				$sql_job 	= "SELECT action FROM job_action WHERE  job_id = '$job_id' AND (action=7 OR action=6 OR action=8)";	
			}
			
	        $result 	= $adapter->query($sql_job, $adapter::QUERY_MODE_EXECUTE);
	        $getJobArray= $result->toArray(); 
	        $action_by_id='';
			if($getJobArray[0]['action']==7 || $getJobArray[0]['action']==8){
				$action_by_id='(By Employer)';
			}else if($getJobArray[0]['action']==6){
				$action_by_id='(By Locum)';
			}else{
				$sql_job_post 	= "SELECT job_status FROM job_post WHERE  job_id = '$job_id' AND job_status = 8";	
				$result_post 	= $adapter->query($sql_job_post, $adapter::QUERY_MODE_EXECUTE);
	        	$getPostJobArray = $result_post->toArray();
	        	if (!empty($getPostJobArray[0])) {
	        	 	$action_by_id='(By Employer)';
	        	} 				
			}
	        return $action_by_id ; 
		}
	}