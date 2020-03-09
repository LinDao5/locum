<?php
	/**
	* Design and develop by SURAJ WASNIK at FUDUGO
	*/
	namespace GcFrontend\Controller;
	use Gc\Mvc\Controller\Action;
	use Gc\view\Helper\Config as ConfigModule;
	use Gc\Core\Config as CoreConfig;
	use Gc\Registry;
	use Gc\User;
	use GcFrontend\Controller\FunctionsController as FunctionsController;
	use GcFrontend\Controller\EndecryptController as Endecrypt;
	use GcFrontend\Controller\JobsmsController as JobsmsController;
	
	
	
	class JobmailController extends Action
	{			
		public function sendAcceptMailToUser($uid,$cjid,$adapter)
        {
			$functionsController = new FunctionsController();
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
				$jobStoreId = $value['store_id'];
				$jobStarttime = $value['job_start_time'];
	        }
			
			$new_date=str_replace("/","-",$jobDate);
	        /* Get record of employer */
	        $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'";	
	        $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
	        $empUsers = $empUserDetails->toArray();
	        foreach ($empUsers as $key => $value) {
	        	$empName 	= $value['firstname']." ".$value['lastname'];
	        	$empEmail 	= $value['email'];
	        }
			/*Get store job details*/
			$sqlString_st00="select * from employer_store_list where emp_st_id='".$jobStoreId."'";	
            $results_st00 = $adapter->query($sqlString_st00, $adapter::QUERY_MODE_EXECUTE);
            $resultset_st00 = $results_st00->current();
			$emp_store_name=$resultset_st00['emp_store_name'];
			$emp_store_address=$resultset_st00['emp_store_address'].', '.$resultset_st00['emp_store_region'].', '.$resultset_st00['emp_store_zip'];
			$emp_store_region=$resultset_st00['emp_store_region'];
			$emp_store_zip=$resultset_st00['emp_store_zip'];  
			$startTime = unserialize( $resultset_st00['store_start_time']);
			$endTime = unserialize( $resultset_st00['store_end_time']);
			$lunchTime = unserialize( $resultset_st00['store_lunch_time']);
			$job_day =  date('l', strtotime($new_date));
			
			//Store timing for posted day 
			$store_start_time = $functionsController->getTimeOfDay($startTime,$job_day).':00';
			$store_end_time = $functionsController->getTimeOfDay($endTime,$job_day).':00';
			$store_lunch_time = $functionsController->getTimeOfDay($lunchTime,$job_day).':00 (Min)'; 

	        /* Get record of freelancer */
	        $sqlFreUser = "SELECT firstname,lastname,email,user_acl_profession_id,id from user WHERE id = '$uid'";	
	        $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
	        $freUsers = $freUserDetails->toArray();
	        foreach ($freUsers as $key => $value) {
	        	$freName 	   = $value['firstname']." ".$value['lastname'];
	        	$freEmail 	   = $value['email'];
				$freID 	       = $value['id'];
				$freprofession = $value['user_acl_profession_id'];
	        }
			/*Get Start time for employer*/
			$sqlEmpUserExtra = "SELECT store_unique_time,telephone,mobile from user_extra_info WHERE uid = '$jobEmpId'";	
	        $empUserExtraDetails = $adapter->query($sqlEmpUserExtra, $adapter::QUERY_MODE_EXECUTE);
	        $empUsersExtra = $empUserExtraDetails->toArray();
	        foreach ($empUsersExtra as $key => $value) {
				$store_telephone=$value['telephone'];
			    $store_mobile=$value['mobile'];
	        	$store_unique_time=unserialize($value['store_unique_time']);
				/*$store_start_time=$store_unique_time['start_time'].':00';
				$store_end_time=$store_unique_time['end_time'].':00';
				$store_lunch_time=$store_unique_time['lunch_time'].':00';*/
				if($store_telephone!=''){
				   $store_contact_details=$store_telephone;
				}elseif($store_mobile!=''){
				   $store_contact_details=$store_mobile;
				}
	        }
			
			
			
			
			/* Get record of freelancer answer */
	        $sqlFreUserQu = "SELECT ua.*,qu.fquestion from user_answer ua,user_question qu WHERE qu.fquestion!='' and ua.user_id = '$freID' and ua.question_id=qu.id";	
	        $freUserDetailsQu = $adapter->query($sqlFreUserQu, $adapter::QUERY_MODE_EXECUTE);
	        $freUsersQu = $freUserDetailsQu->toArray();
	        foreach ($freUsersQu as $key => $value) {
	        	$free_qu_ans.='
				        <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$value['fquestion'].'</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$value['type_value']).'</td>
						</tr>';
	        }
			
			/* Get record of employer answer */
	        $sqlEmpUserQu = "SELECT ua.*,qu.equestion from user_answer ua,user_question qu WHERE qu.equestion!='' and ua.user_id = '$uid' and ua.question_id=qu.id";	
	        $EmpUserDetailsQu = $adapter->query($sqlEmpUserQu, $adapter::QUERY_MODE_EXECUTE);
	        $empUsersQu = $EmpUserDetailsQu->toArray();
	        foreach ($empUsersQu as $key => $value) {
	        	$emp_qu_ans.='
				        <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$value['equestion'].'</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$value['type_value']).'</td>
						</tr>';
	        }
			
			/* Get record of freelancer */
	        $sqlFreUserExtra = "SELECT * from user_extra_info WHERE uid = '$uid'";	
	        $freUserExtraDetails = $adapter->query($sqlFreUserExtra, $adapter::QUERY_MODE_EXECUTE);
	        $freUsersExtra = $freUserExtraDetails->toArray();
	        foreach ($freUsersExtra as $key => $value) {
	        	$freGoc 	   = $value['goc'];
	        	$freaop 	   = $value['aop'];
				$freaoc_id 	   = $value['aoc_id'];
				$freinsurance  = $value['inshurance_company'];
				$freinsuranceno= $value['inshurance_no'];
				$freinsurance_date  = $value['inshurance_renewal_date'];
	        }
			if($freprofession==3){
				$fre_addinfo=' 
				<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
				   <tr style="background-color: #92D000;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Job Invitation – Information you provided us </th>
					</tr>
					<tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;" colspan="2">Please check the details below and advise us immediately if this information is incorrect</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Goc</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freGoc.'</td>
					</tr>';
				if($freaoc_id && $freaoc_id != ''){
				    $fre_addinfo.='<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Aoc</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freaoc_id.'</td>
					</tr>';
				}else{
				    $fre_addinfo.='<tr>
					  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance:</th>
					  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.ucfirst($freinsurance).'-'.$freinsuranceno.'</td>
				    </tr>
				    <tr>
					  <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance expiry:</th>
					  <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freinsurance_date.'</td>
				  </tr>';
				}
				 $fre_addinfo .=$free_qu_ans.'
			   </table><br>';
			}else{
				$fre_addinfo='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Job Invitation – Information you provided us </td>
						</tr>
						<tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;" colspan="2">Please check the details below and advise us immediately if this information is incorrect</td>
					</tr>
						'.$free_qu_ans.'
					</table><br>';
					
			}

	        $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
        	$configGet  = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        	$adminEmail = $configGet->get('mail_from');
	        $mail_css 	= '
	        	<style type="text/css">
					table {
					    border-collapse: collapse;
					}

					table, th, td {
					    border: 1px solid black;
					    text-align:left;
					    padding:5px;
					}
					h3{
						text-align:left;
					}
					tr:nth-child(odd){
						background-color: #f2f2f2;
					}
					th{
						width: 200px;
					}
					.mail-header{
						background: #00A9E0;
					    padding: 20px 50px;
					    width: 100%;
					    border-top: 2px solid #000;
					    border-bottom: 2px solid #000;
					    clear: both;
					}
					.mail-footer {
					    background: #252525;
					    color: #fff;
					    padding: 15px 50px;
					    margin-top: 30px;
					}
					.mail-job-info {
					    padding: 25px 50px 30px;
                        border-right: 2px solid #000;
                        border-left: 2px solid #000;
					}
				</style>'.$header;
				$freelancer_terms='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					  <tr style="background-color: #92D000;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;"> Terms and Condition</th>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>DOCUMENTATION</strong></th>
					  </tr>
					  <tr style="background-color: #f2f2f2;">
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">Please ensure you have provided us the up to date/latest:
							<ul>
								<li> GOC registration details,</li>
								<li> Evidence of current PCT listing</li>
								<li> 2 Clinical references,</li>
								<li> AOP card of Professional Indemnity Insurance</li>
								<li> Passport photo page/visa page </li>
								<li> Recent CV (not compulsory but recommended)</li>
							</ul>
						  </td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>DRESS CODE</strong></td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>CANCELLATIONS</strong></td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">In the event that you are not able to attend a date, it is important that the store is given as much notice as possible to make alternate arrangements to reduce the impact on the store and more importantly their customers. Please try to avoid cancellations, as it would impact your future bookings if you have higher cancellation rates Any cancellations should be sent to xxxx as soon as you are aware you will not be able to attend giving the reason and dates. Once the cancellation has been received, we will contact you to advise that it has been dealt with. If the cancellation is short notice then please contact the store directly as well as emailing LocumKit.
</td>
					  </tr>
					</table>';
			$job_info_free ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Contact Details</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_contact_details.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$emp_store_address.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red;font-weight:bold;">'.$jobDesc.'</td>
						</tr>	
					</table>
					<br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (additional information) </td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>
					<br>
					'.$fre_addinfo.'

    				<p><br/></p>
    				<p>Should you need to cancel this job, please <a href="'.$serverUrl().'//cancel-job?e='.$cjid.'">click here</a>. </p>
    				<p><br/></p>
					'.$freelancer_terms.'
				</div>'.$footer.'</body></html>';
				
				$job_info_emp ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
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
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$jobDesc.'</td>
						</tr>	
					</table>
					<br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking Confirmation – Details of Locum booked</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freID.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Goc</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freGoc.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freinsuranceno.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Insurance expiry</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freinsurance_date.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>
					<br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (additional information)</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$free_qu_ans.'
					</table>

    				<p>&nbsp</p>
    				<p>Should you need to cancel this job, please <a href="'.$serverUrl().'/cancel-job?e='.$cjid.'">click here</a>. </p>
				</div>'.$footer.'</body></html>';
				$job_info_admin ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
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
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$jobDesc.'</td>
						</tr>	
					</table>
					 <br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking Confirmation – Details of Locum booked</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freID.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">GOC Number</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freGoc.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>
					<br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (additional information)</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$free_qu_ans.'
					</table>
				</div>'.$footer.'</body></html>';
			$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');	
	        $massageFre = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hi <b>'.$freName.'</b>,</p>
    				<br/>
    				<p>We are pleased to inform that the following booking has been confirmed for you.</p>
    				<p>Following is your job information : </p>
    				'.$job_info_free;
    		//echo "<br/>";

	        $massageEmp = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hi <b>'.$empName.'</b>,</p>
    				<br/>
    				<p> We are pleased to inform that the following booking has been confirmed for you:
    				</p>    				
    				'.$job_info_emp;
    		//echo "<br/>";

	        $massageAdm = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
        			<p>The following booking has been confirmed </p>
        			<p>A new job has just been posted by: <b>'.$empName.'</b> </p>
        				<p>Following is job information : </p>
        			'.$job_info_admin;

               /* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Booking Confirmation');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        
				//send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->bookingConfirmationfre($uid,$cjid,null);
                //send sms end
			
			} catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  

        	/* Mail Send to employer */
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject(' Booking Confirmation: '.$jobTitle);
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	       
				//send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->bookingConfirmationemp($jobEmpId,$cjid,null);
                //send sms end

		    } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }


        	/* Mail Send to Admin */
        	try {
	            $mailAdm = new \Gc\Mail('utf-8', $massageAdm);
	            $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailAdm->setFrom($adminEmail, 'Locumkit');
	            $mailAdm->addTo($adminEmail);
	            $mailAdm->setSubject('Booking Confirmation: '.$jobTitle);
	            $mailAdm->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }

  
        }    


		
        public  function sendApplyMailToUser($uid,$cjid,$adapter)
        {
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        }

	        /* Get record of employer */
	        $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'";	
	        $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
	        $empUsers = $empUserDetails->toArray();
	        foreach ($empUsers as $key => $value) {
	        	$empName 	= $value['firstname']." ".$value['lastname'];
	        	$empEmail 	= $value['email'];
	        }

	        /* Get record of freelancer */
	        $sqlFreUser = "SELECT firstname,lastname,email from user WHERE id = '$uid'";	
	        $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
	        $freUsers = $freUserDetails->toArray();
	        foreach ($freUsers as $key => $value) {
	        	$freName 	= $value['firstname']." ".$value['lastname'];
	        	$freEmail 	= $value['email'];
	        }
	        $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        $mail_css 	= '
	        	<style type="text/css">
					table {
					    border-collapse: collapse;
					}

					table, th, td {
					    border: 1px solid black;
					    text-align:left;
					    padding:5px;
					}
					h3{
						text-align:left;
					}
					tr:nth-child(odd){
						background-color: #f2f2f2;
					}
					th{
						width: 200px;
					}
					.mail-header{
						background: #00A9E0;
					    padding: 20px 50px;
					    width: 100%;
					    border-top: 2px solid #000;
					    border-bottom: 2px solid #000;
					    clear: both;
					}
					.mail-footer {
					    background: #252525;
					    color: #fff;
					    padding: 15px 50px;
					    margin-top: 30px;
					}
					.mail-job-info {
					    padding: 25px 50px 30px;
                                            border-right: 2px solid #000;
                                            border-left: 2px solid #000;
					}
				</style>'.$header;
			$job_info ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Description</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDesc.'</td>
						</tr>	
					</table>
				</div>'.$footer.'</body></html>';

	        $massageFre = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hello <b>'.$freName.'</b>,</p>
    				<br/>
    				<p>You have successfully apply for the job, please wait next 24 hours for employer notification on the selected job.
    				</p>
    				<p>Folowwing is your job information : </p>
    				'.$job_info;
    		//echo "<br/>";

	        $massageEmp = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hello <b>'.$empName.'</b>,</p>
    				<br/>
    				<p> someone is apply for your job..
    				</p>
    				<p>Following is your job information : </p>
    				'.$job_info;
    		//echo "<br/>";
    		
	        /*echo $massageAdm = $mail_css.'
	        	<div class="mail-job-info">
        			<p>The job of employer <b>'.$empName.'</b> is accepted by the freelancer <b>'.$freName.'</b></p>
        				<p>Folowwing is your job information : </p>
        			'.$job_info;*/
        	/* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Job apply notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  

        	/* Mail Send to employer */
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject('Job apply notification');
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  

	        
        }

        public function sendAprrovalMailToUser($uid,$cjid,$adapter)
        {
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        }

	        /* Get record of employer */
	        $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'";	
	        $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
	        $empUsers = $empUserDetails->toArray();
	        foreach ($empUsers as $key => $value) {
	        	$empName 	= $value['firstname']." ".$value['lastname'];
	        	$empEmail 	= $value['email'];
	        }

	        /* Get record of freelancer */
	        $sqlFreUser = "SELECT firstname,lastname,email from user WHERE id = '$uid'";	
	        $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
	        $freUsers = $freUserDetails->toArray();
	        foreach ($freUsers as $key => $value) {
	        	$freName 	= $value['firstname']." ".$value['lastname'];
	        	$freEmail 	= $value['email'];
	        }

	        	        $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        $mail_css 	= '
	        	<style type="text/css">
					table {
					    border-collapse: collapse;
					}

					table, th, td {
					    border: 1px solid black;
					    text-align:left;
					    padding:5px;
					}
					h3{
						text-align:left;
					}
					tr:nth-child(odd){
						background-color: #f2f2f2;
					}
					th{
						width: 200px;
					}
					.mail-header{
						background: #00A9E0;
					    padding: 20px 50px;
					    width: 100%;
					    border-top: 2px solid #000;
					    border-bottom: 2px solid #000;
					    clear: both;
					}
					.mail-footer {
					    background: #252525;
					    color: #fff;
					    padding: 15px 50px;
					    margin-top: 30px;
					}
					.mail-job-info {
					    padding: 25px 50px 30px;
                                            border-right: 2px solid #000;
                                            border-left: 2px solid #000;
					}
				</style>'.$header;
			$job_info ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Description</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDesc.'</td>
						</tr>	
					</table>
				</div>'.$footer.'</body></html>';

	        $massageFre = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hello <b>'.$freName.'</b>,</p>
    				<br/>
    				<p>Congrats..! You have got the job.
    				</p>
    				<p>Following is your job information : </p>
    				'.$job_info;
    		//echo "<br/>";

	        $massageEmp = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hello <b>'.$empName.'</b>,</p>
    				<br/>
    				<p> You have assign job to the freelancer.
    				</p>
    				<p>Following is your job information : </p>
    				'.$job_info;
    		//echo "<br/>";

	        $massageAdm = $mail_css.'
	        	<div class="mail-job-info" style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
        			<p>The job of employer <b>'.$empName.'</b> is accepted by the freelancer <b>'.$freName.'</b></p>
        				<p>Following is job information : </p>
        			'.$job_info;

               /* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Job apply notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  

        	/* Mail Send to employer */
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject('Job apply notification');
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }


        	/* Mail Send to Admin */
        	try {
	            $mailAdm = new \Gc\Mail('utf-8', $massageAdm);
	            $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailAdm->setFrom($adminEmail, 'Locumkit');
	            $mailAdm->addTo($adminEmail);
	            $mailAdm->setSubject('Job apply notification');
	            $mailAdm->send();

	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }

  
        }   


        //  Approval mail to private user

        public function sendAprrovalMailToPrivateUser($puid,$cjid,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $privateUserData 	= $this->getPrivateUserInfo($puid,$adapter);
	        if (!empty($privateUserData)) {
	        	$privateUserEmail 	= $privateUserData['p_email'];
		    	$privateUserName 	= $privateUserData['p_name'];
	        }
	        $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->current();
	        $empData 	= $this->getEmployerInfo($job['e_id'],$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
	        }
	        $jobData 	= $this->getJobInfo($cjid,$adapter);
	        $massagePrivateUser = $header;
	        $massagePrivateUser .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$privateUserName.'</b>,</p>';
	        $massagePrivateUser .= '<p>You selected for the following job by employer. </p>';
	        $massagePrivateUser .= '<p>Following is your job information.</p>';
	        $massagePrivateUser .= $jobData;
	        $massagePrivateUser .= '</div>';
	        $massagePrivateUser .= $footer;
	        

	        $massageEmp = $header;
	        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$empName.'</b>,</p>';
	        $massageEmp .= '<p>Job Successfully Assigned To Freelancer. </p>';
	        $massageEmp .= '<p>Following is your job information.</p>';
	        $massageEmp .= $jobData;
	        $massageEmp .= '</div>';
	        $massageEmp .= $footer;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        $adminEmail = $configGet->get('mail_from');
	        /* Mail Send to Private user */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($privateUserEmail);
	            $mailFre->setSubject('Job aprroved notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
	
			/* Mail Send to employer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageEmp);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($empEmail);
	            $mailFre->setSubject('Job aprroved notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 

  
        }   

        /* Weekly Reminder Notification To Freelancer */
        public function sendweeklyReminderToFreelancer($adapter,$weekStartDate,$weekEndDate)
        {
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');	
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $adminEmail = $configGet->get('mail_from');
        	/* Get All Customer Data */
        	$userObj = new User\Collection();
        	$userData = $userObj->getUsers();
        	foreach ($userData as $key => $uData) {
        		if ($uData->getUserAclRoleId() == 2) {
        			$freId = $uData->getId();
        			if ($freId) {
        				$weekLiveJobArray = array();
        				$weekPrivateJobArray = array();
        				//echo $sqlJob = "SELECT * from job_post WHERE  ( date_format(STR_TO_DATE(job_date,'%d/%m/%Y'),'%d-%m-%Y') BETWEEN '$weekStartDate' AND '$weekEndDate' ) AND job_id IN ( SELECT job_id FROM job_action WHERE action = '3' AND f_id = '$freId')";	
        				$sqlJob = "SELECT * from job_post WHERE  job_id IN ( SELECT job_id FROM job_action WHERE action = '3' AND f_id = '$freId') AND job_status = '4'";        				
				        $jobArrayObj = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
				        $jobArray = $jobArrayObj->toArray();				        
				        $i = 0;
				        foreach ($jobArray as $key => $value) {				        	
				        	$jobDate = strtotime(str_replace('/', '-', $value['job_date']));
				        	if ($jobDate >= strtotime($weekStartDate) && $jobDate <= strtotime($weekEndDate)) {
				        		$i++;	
				        		$weekLiveJobArray[]= array(
				        				'job_date' 	=> $value['job_date'],
				        				'job_day'	=> date('l',$jobDate),
				        				'job_rate'	=> $this->getCurrencySymbol().''.number_format($value['job_rate'],2),
				        				'store'		=> $this->getStoreInfo($adapter,$value['store_id']),
				        				'location'	=> $value['job_address'].', '.$value['job_region'].', '.$value['job_zip'],
				        				'view'		=> $serverUrl().'/single-job?view='.$value['job_id']
				        			); 
				        	}
				        	
				        }

				        $sqlPrivateJob = "SELECT * from freelancer_private_job WHERE f_id = '$freId'";        				
				        $jobPrivateArrayObj = $adapter->query($sqlPrivateJob, $adapter::QUERY_MODE_EXECUTE);
				        $jobPrivateArray = $jobPrivateArrayObj->toArray();				        
				        
				        foreach ($jobPrivateArray as $key => $value) {				        	
				        	$jobDate = strtotime($value['priv_job_start_date']);
				        	if ($jobDate >= strtotime($weekStartDate) && $jobDate <= strtotime($weekEndDate)) {
				        		$i++;	
				        		$weekPrivateJobArray[]= array(
				        				'job_date' 	=> date('d-m-Y',strtotime($value['priv_job_start_date'])),
				        				'job_day'	=> date('l',$jobDate),
				        				'job_rate'	=> $this->getCurrencySymbol().''.number_format($value['priv_job_rate'],2),
				        				'name'		=> $value['emp_name'],
				        				'location'	=> $value['priv_job_location'],
				        				'view'		=> $serverUrl().'/private-job'
				        			); 
				        	}
				        	
				        }

				        if ($i > 0) {				        	
				        	$freData 	= $this->getFreelancerInfo($freId,$adapter);
					        if (!empty($freData)) {
					        	$freEmail 	= $freData['email'];
						    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
					        }
				        	$weeklyReminderMsg = $header;
				        	$weeklyReminderMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000; text-align: left;">';
				        	$weeklyReminderMsg .= '<p>Hi <b>'.$freName.'</b></p><p>Please see below a list of your upcoming bookings for the current week.</p>';
				        	if (!empty($weekLiveJobArray)) {
					        	$weeklyReminderMsg .= '
					        		<h3 style="text-align:left;"> Live Job </h3>
									<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; margin-bottom:20px;">
										<tr style="background-color: #f2f2f2;">
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Day</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Rate</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>	
										</tr>';
										foreach ($weekLiveJobArray as $key => $value) {
											$weeklyReminderMsg .='
												<tr> 
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['store'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['location'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
												</tr>';
										}
								$weeklyReminderMsg .='	</table>';
							}
							if (!empty($weekPrivateJobArray)) {
					        	$weeklyReminderMsg .= '
					        		<h3 style="text-align:left;"> Private Job </h3>
									<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; margin-bottom:20px;">
										<tr style="background-color: #f2f2f2;">
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Day</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Rate</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>	
										</tr>';
										foreach ($weekPrivateJobArray as $key => $value) {
											$weeklyReminderMsg .='
												<tr> 
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['name'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['location'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
												</tr>';
										}
								$weeklyReminderMsg .='	</table>';
							}
							$weeklyReminderMsg .= '<p>LocumKit Tip: To increase chance of booking you can always try to reduce your rate requirement from the dashboard calender</p></div>'.$footer;			
							//echo $weeklyReminderMsg;
							/* Mail Send to Freelancer */

 //sms content start
							$livejob = count($weekLiveJobArray);
							$privatejob = count($weekPrivateJobArray);
							if($livejob > 0){ $sms1 = $livejob." Live Job "; }else{ $sms1 = ''; }
							if($privatejob > 0){ $sms2 = $privatejob." Private Job "; }else{ $sms2 = ''; }							
						        $smsContent = "Hi ".$freName." upcoming bookings for Job in this week are : ".$sms1." ".$sms2.". ";
							//sms content end


				        	try {
					            $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
					            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
					            $mailFre->setFrom($adminEmail, 'Locumkit');
					            $mailFre->addTo($freEmail);
					            $mailFre->setSubject(' LocumKit week commencing '.$weekStartDate.' bookings');
					            $mailFre->send();
					            $this->flashMessenger()->addSuccessMessage('Message sent');


//send sms start
						 /*   $jobsmsController = new JobsmsController();
						    $jobsmsController->sendweeklyReminderToFreelancerSms($freId,$smsContent);*/
						   
 //send sms end

					        } catch (Exception $e) {
					            //$this->flashMessenger()->addErrorMessage($e->getMessage());
					        } 	
				        }else{
						if($uData->getUserAclRoleId() == 2 && $uData->getActive() == 1) {
						$freId = $uData->getId();
						$freEmail = $uData->getEmail();
						$freName 	= $uData->getFirstname().' '.$uData->getLastname();
						
						$weeklyReminderMsg = $header;
				        $weeklyReminderMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000; text-align: left;">';
						$weeklyReminderMsg .= '<p>Hi <b>'.$freName.'</b></p><p>In this week you dont have any booking . so please update your criteria to get more work .</p>';
						$weeklyReminderMsg .= '<p>&nbsp;</p><p>LocumKit Tip: To increase chance of booking you can always try to reduce your rate requirement from the dashboard calender</p></div>'.$footer;	
						try {
					            $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
					            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
					            $mailFre->setFrom($adminEmail, 'Locumkit');
					            $mailFre->addTo($freEmail);
					            $mailFre->setSubject(' LocumKit weekly reminder.');
					            $mailFre->send();
					            $this->flashMessenger()->addSuccessMessage('Message sent');

					        } catch (Exception $e) {
					            
					        }
						
						}
						
						}
        			}		
        		}
        	}

        	/* Get All Job Data */
        }

        /* Weekly Reminder Notification To Employer */
        public function sendweeklyReminderToEmployer($adapter,$weekStartDate,$weekEndDate)
        {
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');	
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $adminEmail = $configGet->get('mail_from');
        	/* Get All Customer Data */
        	$userObj = new User\Collection();
        	$userData = $userObj->getUsers();
        	foreach ($userData as $key => $uData) {
        		if ($uData->getUserAclRoleId() == 3) {
        			$empId = $uData->getId();
        			if ($empId) {
        				$weekLiveJobArray = array();        					
        				$sqlJob = "SELECT * from job_post WHERE  e_id = '$empId' AND job_status = '4'";
				        $jobArrayObj = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
				        $jobArray = $jobArrayObj->toArray();				        
				        $i = 0;
				        foreach ($jobArray as $key => $value) {				        	
				        	$jobDate = strtotime(str_replace('/', '-', $value['job_date']));
				        	if ($jobDate >= strtotime($weekStartDate) && $jobDate <= strtotime($weekEndDate)) {
				        		$i++;	
				        		$weekLiveJobArray[]= array(
				        				'job_date' 	=> $value['job_date'],
				        				'job_day'	=> date('l',$jobDate),
				        				'job_rate'	=> $this->getCurrencySymbol().''.number_format($value['job_rate'],2),
				        				'store'		=> $this->getStoreInfo($adapter,$value['store_id']),
				        				'location'	=> $value['job_address'].', '.$value['job_region'].', '.$value['job_zip'],
				        				'view'		=> $serverUrl().'/single-job?view='.$value['job_id']
				        			); 
				        	}
				        	
				        }
				        if ($i > 0) {				        	
				        	$empData 	= $this->getEmployerInfo($empId,$adapter);
					        if (!empty($empData)) {
					        	$empEmail 	= $empData['email'];
						    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
					        }
				        	$weeklyReminderMsg = $header;
				        	$weeklyReminderMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000; text-align: left;">';
				        	$weeklyReminderMsg .= 'Hi <b>'.$empName.'</b>';
				        	if (!empty($weekLiveJobArray)) {
					        	$weeklyReminderMsg .= '
					        		<h3 style="text-align:left;"> Live Job </h3>
									<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; margin-bottom:20px;">
										<tr style="background-color: #f2f2f2;">
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Day</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Rate</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
											<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>	
										</tr>';
										foreach ($weekLiveJobArray as $key => $value) {
											$weeklyReminderMsg .='
												<tr> 
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['store'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$value['location'].'</td>
													<td style=" border: 1px solid black;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
												</tr>';
										}
								$weeklyReminderMsg .='	</table>';
							}
							$weeklyReminderMsg .= '</div>'.$footer;			
							//echo $weeklyReminderMsg;
							/* Mail Send to Employer */
							
							 //sms content start
							$livejob = count($weekLiveJobArray);
							if($livejob > 0){ $sms1 = $livejob." Live Jobs "; }else{ $sms1 = ''; }						
						        $smsContent = "Hi ".$empName." Your ".$sms1." coming in this week.";
							//sms content end
							
							
							
				        	try {
					            $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
					            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
					            $mailFre->setFrom($adminEmail, 'Locumkit');
					            $mailFre->addTo($empEmail);
					            $mailFre->setSubject(' LocumKit week commencing '.$weekStartDate.' bookings');
					            $mailFre->send();
					            $this->flashMessenger()->addSuccessMessage('Message sent');
								
								//send sms start
							/*   $jobsmsController = new JobsmsController();
						    $jobsmsController->sendweeklyReminderToEmployerSms($empId,$smsContent);*/
						   
							//send sms end
								
								
					        } catch (Exception $e) {
					            //$this->flashMessenger()->addErrorMessage($e->getMessage());
					        } 
						}
					}
        		}
        		
        	}

        	/* Get All Job Data */
        }

        /* Reminder Notification mail */
        public function sendReminder($jobFid,$notifyDay,$jobId,$adapter)
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');	
        	$adminEmail = $configGet->get('mail_from');
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId' AND job_status = '4'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$eId 		= $value['e_id'];
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        	$storeName 	= $this->getStoreInfo($adapter,$value['store_id']);
 	        }

        	/* Get freelancer e-mail id*/
			$sqlFreEmail = "SELECT email,firstname,lastname from user WHERE id='$jobFid'";	
		    $freEmailData = $adapter->query($sqlFreEmail, $adapter::QUERY_MODE_EXECUTE);
		    $freEmails = $freEmailData->current();	
		    $freEmail = $freEmails['email'];
		    $freName = $freEmails['firstname'].' '.$freEmails['lastname'];
							
		    /* Get Employer e-mail id*/
			$sqlEmpEmail = "SELECT email,firstname,lastname from user WHERE id='$eId'";	
		    $empEmailData = $adapter->query($sqlEmpEmail, $adapter::QUERY_MODE_EXECUTE);
		    $empEmails = $empEmailData->current();	
		    $empEmail = $empEmails['email'];
		    $empName = $empEmails['firstname'].' '.$empEmails['lastname'];
	
		    $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $mail_css 	= $header;
			$job_info ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
						</tr>						
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Location</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>	
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$storeName.'</td>
						</tr>	
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
												
					</table>';
	            $massageFre = $mail_css.'
	        	<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;">
	        		<p>Hello <b>'.$freName.'</b>,</p>
	        		<p>This is just a courtesy reminder that you have a booking coming up.</p>
	        		<p>Please see a summary of the details below:</p>
    				'.$job_info.'
    				<br/>
    				<p>To view full details please <a href="'.$serverUrl().'/single-job?view='.$jobId.'">click here</a></p>
    				<p>If for any reason you can not attend this booking please <a href="'.$serverUrl().'/cancel-job?e='.$jobId.'">click here</a> to cancel.</p>
    				</div>'.$footer.'</body></html>';
    			if ($notifyDay > 1) {
    				$reminderSubject = 'Job reminder';
    			}else{
    				$reminderSubject = 'Job reminder - TOMORROW';
    			}
    		/* Mail Send to freelancer */
        	try {
	            if($freEmail){
	                 $mailFre = new \Gc\Mail('utf-8', $massageFre);
	                 $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	                 $mailFre->setFrom($adminEmail, 'Locumkit');
	                 $mailFre->addTo($freEmail);
	                 $mailFre->setSubject($reminderSubject);
	                 $mailFre->send();
	                 $this->flashMessenger()->addSuccessMessage('Message sent');
					 
				
		   $smsLinksArray =  array('detail' => $serverUrl().'/single-job?view='.$jobId , 'cancel' =>$serverUrl().'/cancel-job?e='.$jobId); ;
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->sendReminderSms($jobFid,$jobId,$smsLinksArray); 
					 
	            }
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
	        /* Mail Send to employer */
        	try {
	            if($empEmail){
	                 $mailFre = new \Gc\Mail('utf-8', $massageFre);
	                 $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	                 $mailFre->setFrom($adminEmail, 'Locumkit');
	                 $mailFre->addTo($empEmail);
	                 $mailFre->setSubject($reminderSubject);
	                 $mailFre->send();
	                 $this->flashMessenger()->addSuccessMessage('Message sent');

		   $smsLinksArray =  array('detail' => $serverUrl().'/single-job?view='.$jobId , 'cancel' =>$serverUrl().'/cancel-job?e='.$jobId); ;
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->sendReminderSms($eId,$jobId,$smsLinksArray); 
				
	            }
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* On Day Notification mail To freelancer */
        public function sendOnDayNotificationToFreelancer($jobId,$jobFid,$jobEid,$yesBtnLink,$adapter)
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');

        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 	= $value['job_id'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        	$storeId 	= $value['store_id'];
	        }

        	/* Get freelancer e-mail id*/
		    $sqlFreEmail = "SELECT email,firstname,lastname from user WHERE id='$jobFid'";	
		    $freEmailData = $adapter->query($sqlFreEmail, $adapter::QUERY_MODE_EXECUTE);
		    $freEmails = $freEmailData->current();	
		    $freEmail = $freEmails['email'];
		    $freName = $freEmails['firstname'].' '.$freEmails['lastname'];

		    $header 	= $this->mailHeader();
            $footer 	= $this->mailFooter();
            $mail_css 	= $header;
			$job_info ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$this->getStoreInfo($adapter, $storeId).'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
					</table>
				</div>'.$footer.'</body></html>';
	        $massageFre = $mail_css.'
	        	<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$freName.'</b>,</p>
    				<h3>Have you arrived at work today for the below stated booking?   <!--Please can you confirm your arrival to work today---></h3>
    				<p>'.$yesBtnLink.'</p>
    				<!----<p>The details of the work are as per below:</p>--->
    				'.$job_info;
    		/* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('LocumKit confirmation of arrival');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* On Day Notification mail To freelancer */
        public function sendOnDayNotificationToEmployer($jobId,$jobFid,$jobEid,$yesBtnLink,$adapter)
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');

        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        }

        	/* Get Employer e-mail id*/
		    $sqlEmpEmail = "SELECT email,firstname,lastname from user WHERE id='$jobEid'";	
		    $EmpEmailData = $adapter->query($sqlEmpEmail, $adapter::QUERY_MODE_EXECUTE);
		    $EmpEmails = $EmpEmailData->current();	
		    $EmpEmail = $EmpEmails['email'];
		    $EmpName = $EmpEmails['firstname'].' '.$EmpEmails['lastname'];

		    /* Get Freelancer e-mail id*/
		    $sqlFre = "SELECT firstname,lastname from user WHERE id='$jobFid'";	
		    $freData = $adapter->query($sqlFre, $adapter::QUERY_MODE_EXECUTE);
		    $freObj = $freData->current();
		    $freName = $freObj['firstname'].' '.$freObj['lastname'];

		    $header 	= $this->mailHeader();
            $footer 	= $this->mailFooter();
            $mail_css 	= $header;
			$job_info ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Locum</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
					</table>
				</div>'.$footer.'</body></html>';
	        $massageEmp = $mail_css.'
	        	<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$EmpName.'</b>,</p>
    				<h3>One of your freelancer just attend work today please take a note.</h3>
    				<p>The details of the work are as per below: </p>
    				'.$job_info;
    		/* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageEmp);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($EmpEmail);
	            $mailFre->setSubject('LocumKit confirmation of arrival');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
				
				
					//send sms start
					  $jobsmsController = new JobsmsController();
					  $jobsmsController->sendOnDayNotificationToEmployerSms($jobEid,$jobId);
					//send sms end
				
				
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }



        /* Feedback Notification mail */
        public function sendFeedbackNotification($job_id,$f_id,$e_id,$adapter,$feedback_link_fre,$feedback_link_emp){
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($f_id,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $empData 	= $this->getEmployerInfo($e_id,$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
	        }
	        $jobData 	= $this->getJobInfo($job_id,$adapter);
	        $massageFre = $header;
	        $massageFre .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$freName.'</b>,</p>';
	        $massageFre .= '<p>Hope you had a great day.</p>';
	        $massageFre .= '<p>We would now like you to leave feedback for the employer on your day there.</p>';
	        $massageFre .= '<p>This would help other Locums and also help improve clinical competition amongst users.</p>';
	        $massageFre .= '<p>Please click here on below button to submit your valuable feedback.</p><br/>';
	        $massageFre .= '<p>'.$feedback_link_fre.'</p>';
	      	/*  $massageFre .= '<p>Following is your job information.</p>';
	        $massageFre .= $jobData;*/
	        $massageFre .= '</div>';
	        $massageFre .= $footer;
	        //echo $massageFre;

	        $massageEmp = $header;
	        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$empName.'</b>,</p>';
	        $massageEmp .= '<p>Hope you had a great day.</p>';
	        $massageEmp .= '<p>We would now like you to leave feedback for the freelancer on your day there.</p>';
	        $massageEmp .= '<p>This would help other Employers and also help improve clinical competition amongst users.</p>';
	        $massageEmp .= '<p>Please click here on below button to submit your valuable feedback.</p>';
	        $massageEmp .= '<p>'.$feedback_link_emp.'</p>';
	        /*$massageEmp .= '<p>Following is your job information.</p>';
	        $massageEmp .= $jobData;*/
	        $massageEmp .= '</div>';
	        $massageEmp .= $footer;
	        //echo $massageEmp;
	        /* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Feedback');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
	
			/*Mail Send to employer*/
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject('Feedback');
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }

        /* Feedback submit notification to next party */
        public function recievedFeedbackFreelancerNotification($feedbackId,$feedbackArray,$adapter)
        {
        	$encypt = new Endecrypt();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        	$adminEmail = $configGet->get('mail_from');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($feedbackArray['fre_id'],$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $freId = $feedbackArray['fre_id'];
	      	$jobId = $feedbackArray['j_id'];
	      	$averageRate = $feedbackArray['rating'];
	      	$feedbackArray = unserialize($feedbackArray['feedback']);
	      	
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        	$storeId 	= $value['store_id'];
	        }
	        
	        $job_info ='
				<h3 style="text-align:left;"> Job Information </h3>
				<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
					</tr>
					<tr> 
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
					</tr>
					<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
					</tr>
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$this->getStoreInfo($adapter, $storeId).'</td>
					</tr>
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
					</tr>
				</table>';
			$feedbackQusAns = '';
			$i = 1;
			foreach ($feedbackArray as $key => $feedbackData) {
				$feedbackQusAns .= '
					<div style="border: 1px solid #cfcfcf; padding: 10px;background: #eee;border-radius: 3px;">
						<p style="font-style: italic;font-weight: bold;padding:0 0 10px;">Qus '.$i.') '.$feedbackData['qus'].'</p>
						<p style="font-weight: bold;padding:0 0 10px;">Ans : '.$feedbackData['qusRate'].' star(s).</p>
					</div>
					<div style="height:10px"></div>
				';
				$i++;
			}

	        $massageFre = $header;
	        $massageFre .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$freName.'</b>,</p>';
	        $massageFre .= '<p>You have received feedback for the following booking:</p>';
	        $massageFre .= $job_info;
	        $massageFre .= '<p>&nbsp;</p>';
	        $massageFre .= '<p>Please see below how the employer has left feedback for you</p>';
	        $massageFre .= $feedbackQusAns;
	        $massageFre .= '<p>&nbsp;</p>';
	        $massageFre .= '<p><b>Average star rating: '.$averageRate.'</b></p>';
	        $massageFre .= '<p>&nbsp;</p>';
	        $massageFre .= '<p>If you feel this feedback is not a true reflection of your performance then please <a href="'.$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($freId).'">click here</a>, so we at LocumKit can look into this. </p>';
	      	$massageFre .= '<p>If you are happy with this then this feedback shall automatically be posted against your profile within the next 48 hours.</p>';
	        $massageFre .= '</div>';
	        $massageFre .= $footer;
	        $currentDate = date('Y-m-d');
	        /* Mail Send to freelancer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Feedback recieved for '.date('d/m/Y', strtotime($currentDate .' -1 days')));
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');


		//send sms start
				$link =	$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($freId) ;
                $jobsmsController = new JobsmsController();
                $jobsmsController->recievedFeedbackFreelancerNotificationSms($freId,$jobId,$link);
                //send sms end


	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
	        
	        
        }
        public function recievedFeedbackEmployerNotification($feedbackId,$feedbackArray,$adapter)
        {
        	$encypt = new Endecrypt();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        	$adminEmail = $configGet->get('mail_from');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $empData 	= $this->getEmployerInfo($feedbackArray['emp_id'],$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
	        }
	        $freData 	= $this->getEmployerInfo($feedbackArray['fre_id'],$adapter);
	        if (!empty($freData)) {
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $empId = $feedbackArray['emp_id'];
	      	$jobId = $feedbackArray['j_id'];
	      	$averageRate = $feedbackArray['rating'];
	      	$feedbackArray = unserialize($feedbackArray['feedback']);
	      	
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];	        	
	        }
	        
	        $job_info ='
				<h3 style="text-align:left;"> Job Information </h3>
				<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
					</tr>
					<tr> 
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
					</tr>
					
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Locum</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
					</tr>
					<tr style="background-color: #f2f2f2;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
					</tr>
				</table>';
			$feedbackQusAns = '';
			$i = 1;
			foreach ($feedbackArray as $key => $feedbackData) {
				$feedbackQusAns .= '
					<div style="border: 1px solid #cfcfcf; padding: 10px;background: #eee;border-radius: 3px;">
						<p style="font-style: italic;font-weight: bold;padding:0 0 10px;">Qus '.$i.') '.$feedbackData['qus'].'</p>
						<p style="font-weight: bold;padding:0 0 10px;">Ans : '.$feedbackData['qusRate'].' star(s).</p>
					</div>
					<div style="height:10px"></div>
				';
				$i++;
			}

	        $massageEmp = $header;
	        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hi <b>'.$empName.'</b>,</p>';
	        $massageEmp .= '<p>You have received feedback for the following booking:</p>';
	        $massageEmp .= $job_info;
	        $massageEmp .= '<p>&nbsp;</p>';
	        $massageEmp .= '<p>Please see below how the freelancer has left feedback for you</p>';
	        $massageEmp .= $feedbackQusAns;
	        $massageEmp .= '<p>&nbsp;</p>';
	        $massageEmp .= '<p><b>Average star rating: '.$averageRate.'</b></p>';
	        $massageEmp .= '<p>&nbsp;</p>';
	        $massageEmp .= '<p>If you feel this feedback is not a true reflection of your performance then please <a href="'.$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($empId).'">click here</a>, so we at LocumKit can look into this. </p>';
	      	$massageEmp .= '<p>If you are happy with this then this feedback shall automatically be posted against your profile within the next 48 hours.</p>';
	        $massageEmp .= '</div>';
	        $massageEmp .= $footer;
	        $currentDate = date('Y-m-d');
	        /* Mail Send to freelancer */
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject('Feedback recieved for '.date('d/m/Y', strtotime($currentDate .' -1 days')));
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');


		//send sms start
				$link =	$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($empId) ;
                $jobsmsController = new JobsmsController();
                $jobsmsController->recievedFeedbackEmployerNotificationSms($empId,$jobId,$link);
                //send sms end


	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
        }

        /* Send alert of feedback after 1 week if user not submitted the feedback*/
        public function sendFeedbackNotificationOneWeekAlert($job_id,$u_id,$feedback_link,$user_type, $adapter)
        {
        	echo $user_type;
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        if ($user_type == 2) {
	        	$freData 	= $this->getFreelancerInfo($u_id,$adapter);
		        if (!empty($freData)) {
		        	$freEmail 	= $freData['email'];
			    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		        }

		        $massageFre = $header;
		        $massageFre .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
		        		<p>Hi <b>'.$freName.'</b>,</p>';
		        $massageFre .= '<p>This is a reminder mail to inform you that you left to submit feedback on job <b>#'.$job_id.'</b></p>';
		        $massageFre .= '<p>We would now like you to leave feedback for the employer on your day there.</p>';
		        $massageFre .= '<p>This would help other Locums and also help improve clinical competition amongst users.</p>';
		        $massageFre .= '<p>Please click here on below button to submit your valuable feedback.</p>';
		        $massageFre .= '<p>'.$feedback_link.'</p>';
		      	/*  $massageFre .= '<p>Following is your job information.</p>';
		        $massageFre .= $jobData;*/
		        $massageFre .= '</div>';
		        $massageFre .= $footer;

		        /* Mail Send to freelancer */
	        	try {
		            $mailFre = new \Gc\Mail('utf-8', $massageFre);
		            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mailFre->setFrom($adminEmail, 'Locumkit');
		            $mailFre->addTo($freEmail);
		            $mailFre->setSubject('Feedback Alert');
		            $mailFre->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        } 
	        }

	        if ($user_type == 3) {	        	
		        $empData 	= $this->getEmployerInfo($u_id,$adapter);
		        if (!empty($empData)) {
		        	$empEmail 	= $empData['email'];
			    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		        }
		        $massageEmp = $header;
		        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
		        		<p>Hi <b>'.$empName.'</b>,</p>';
		        $massageEmp .= '<p>This is a reminder mail to inform you that you left to submit feedback on job <b>#'.$job_id.'</b></p>';
		        $massageEmp .= '<p>We would now like you to leave feedback for the freelancer on your day there.</p>';
		        $massageEmp .= '<p>This would help other Employers and also help improve clinical competition amongst users.</p>';
		        $massageEmp .= '<p>Please click here on below button to submit your valuable feedback.</p>';
		        $massageEmp .= '<p>'.$feedback_link.'</p>';
		        /*$massageEmp .= '<p>Following is your job information.</p>';
		        $massageEmp .= $jobData;*/
		        $massageEmp .= '</div>';
		        $massageEmp .= $footer;

		        /*Mail Send to employer*/
	        	try {
		            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
		            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mailEmp->setFrom($adminEmail, 'Locumkit');
		            $mailEmp->addTo($empEmail);
		            $mailEmp->setSubject('Feedback Alert');
		            $mailEmp->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }
	        }
        }


        /* Send job apply email to Private user */
        public function sendApplyMailToPrivateUser($puid,$cjid,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $privateUserData 	= $this->getPrivateUserInfo($puid,$adapter);
	        if (!empty($privateUserData)) {
	        	$privateUserEmail 	= $privateUserData['p_email'];
		    	$privateUserName 	= $privateUserData['p_name'];
	        }
	        $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->current();
	        $empData 	= $this->getEmployerInfo($job['e_id'],$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
	        }
	        $jobData 	= $this->getJobInfo($cjid,$adapter);
	        $massagePrivateUser = $header;
	        $massagePrivateUser .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$privateUserName.'</b>,</p>';
	        $massagePrivateUser .= '<p>You have succesfully apply for the job. </p>';
	        $massagePrivateUser .= '<p>Following is your job information.</p>';
	        $massagePrivateUser .= $jobData;
	        $massagePrivateUser .= '</div>';
	        $massagePrivateUser .= $footer;
	        

	        $massageEmp = $header;
	        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$empName.'</b>,</p>';
	        $massageEmp .= '<p>One of private user apply for your job. </p>';
	        $massageEmp .= '<p>Following is your job information.</p>';
	        $massageEmp .= $jobData;
	        $massageEmp .= '</div>';
	        $massageEmp .= $footer;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        $adminEmail = $configGet->get('mail_from');
	        /* Mail Send to Private user */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($privateUserEmail);
	            $mailFre->setSubject('Job apply notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
	
			/* Mail Send to employer */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageEmp);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($empEmail);
	            $mailFre->setSubject('Job apply notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
        }

        /* Send job accept email to Private user */
        public function sendAcceptMailToPrivateUser($puid,$cjid,$adapter)
        {
			$functionsController = new FunctionsController();
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $privateUserData 	= $this->getPrivateUserInfo($puid,$adapter);

	        if (!empty($privateUserData)) {
	        	$privateUserEmail 	= $privateUserData['p_email'];
		    	$privateUserName 	= $privateUserData['p_name'];
				$privateUserID 	    = $privateUserData['p_uid'];
	        }
	        $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->current();
	        $empData 	= $this->getEmployerInfo($job['e_id'],$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
	        }
	        $jobData = $this->getJobInfo($cjid,$adapter);
			
			/* Get record of employer answer */
	        $sqlEmpUserQu = "SELECT ua.*,qu.equestion from user_answer ua,user_question qu WHERE qu.equestion!='' and ua.user_id = '".$job['e_id']."' and ua.question_id=qu.id";	
	        $EmpUserDetailsQu = $adapter->query($sqlEmpUserQu, $adapter::QUERY_MODE_EXECUTE);
	        $empUsersQu = $EmpUserDetailsQu->toArray();
	        foreach ($empUsersQu as $key => $value) {
	        	$emp_qu_ans.='
				        <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">'.$value['equestion'].'</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.str_replace(',',' / ',$value['type_value']).'</td>
						</tr>';
	        }
			
			/*Get Start time for employer*/
			$sqlEmpUserExtra = "SELECT store_unique_time,telephone,mobile from user_extra_info WHERE uid = '".$job['e_id']."'";	
	        $empUserExtraDetails = $adapter->query($sqlEmpUserExtra, $adapter::QUERY_MODE_EXECUTE);
	        $empUsersExtra = $empUserExtraDetails->toArray();
	        foreach ($empUsersExtra as $key => $value) {
				$store_telephone=$value['telephone'];
			    $store_mobile=$value['mobile'];
	        	$store_unique_time=unserialize($value['store_unique_time']);
				/*$store_start_time=$store_unique_time['start_time'].':00';
				$store_end_time=$store_unique_time['end_time'].':00';
				$store_lunch_time=$store_unique_time['lunch_time'].':00';*/
				if($store_telephone!=''){
				   $store_contact_details=$store_telephone;
				}elseif($store_mobile!=''){
				   $store_contact_details=$store_mobile;
				}
	        }
			
			/*Get store job details*/
			$sqlString_st00="select * from employer_store_list where emp_st_id='".$job['store_id']."'";	
            $results_st00 = $adapter->query($sqlString_st00, $adapter::QUERY_MODE_EXECUTE);
            $resultset_st00 = $results_st00->current();
			$emp_store_name=$resultset_st00['emp_store_name'];
			$emp_store_address=$resultset_st00['emp_store_address'].', '.$resultset_st00['emp_store_region'].', '.$resultset_st00['emp_store_zip'];
			$emp_store_region=$resultset_st00['emp_store_region'];
			$emp_store_zip=$resultset_st00['emp_store_zip']; 
			$startTime = unserialize( $resultset_st00['store_start_time']);
			$endTime = unserialize( $resultset_st00['store_end_time']);
			$lunchTime = unserialize( $resultset_st00['store_lunch_time']);
			$new_date=str_replace("/","-",$job['job_date']);
			$job_day =  date('l', strtotime($new_date));
			
			//Store timing for posted day 
			$store_start_time = $functionsController->getTimeOfDay($startTime,$job_day).':00';
			$store_end_time = $functionsController->getTimeOfDay($endTime,$job_day).':00';
			$store_lunch_time = $functionsController->getTimeOfDay($lunchTime,$job_day).':00 (Min)';
			
			$job_info_admin ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_date'].'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_rate'].'.00'.'</td>
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
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$job['job_post_desc'].'</td>
						</tr>	
					</table>
					 <br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking Confirmation – Details of Locum booked</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$privateUserName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> <b>Private Freelancer </b> </td>
						</tr>
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation (additional information)</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>';
			$freelancer_terms='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
					  <tr style="background-color: #92D000;">
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;"> Terms and Condition</th>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>DOCUMENTATION</strong></th>
					  </tr>
					  <tr style="background-color: #f2f2f2;">
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">Please ensure you have provided us the up to date/latest:
							<ul>
								<li> GOC registration details,</li>
								<li> Evidence of current PCT listing</li>
								<li> 2 Clinical references,</li>
								<li> AOP card of Professional Indemnity Insurance</li>
								<li> Passport photo page/visa page </li>
								<li> Recent CV (not compulsory but recommended)</li>
							</ul>
						  </td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>DRESS CODE</strong></td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;"><strong>CANCELLATIONS</strong></td>
					  </tr>
					  <tr>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">In the event that you are not able to attend a date, it is important that the store is given as much notice as possible to make alternate arrangements to reduce the impact on the store and more importantly their customers. Please try to avoid cancellations, as it would impact your future bookings if you have higher cancellation rates Any cancellations should be sent to xxxx as soon as you are aware you will not be able to attend giving the reason and dates. 
Once the cancellation has been received, we will contact you to advise that it has been dealt with. If the cancellation is short notice then please contact the store directly as well as emailing LocumKit.
</td>
					  </tr>
					</table>';
			$job_info_free ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_date'].'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_rate'].'.00'.'</td>
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
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$job['job_post_desc'].'</td>
						</tr>	
					</table>
					<br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking confirmation (additional information) </td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>
					<br>
					'.$freelancer_terms.' ';
			$job_info_emp ='
					<h3 style="text-align:left;"> Job Information </h3>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking confirmation (Key Details)</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_date'].'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_rate'].'.00'.'</td>
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
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional Booking Info:</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$job['job_post_desc'].'</td>
						</tr>	
					</table>
					 <br>
					<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%">
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking Confirmation – Details of Locum booked</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$privateUserName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> <b>Private Freelancer </b> </td>
						</tr>
						<tr style="background-color: #92D000;">
							<td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking confirmation (additional information)</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Start Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_start_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Finish Time</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_end_time.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Lunch Break (minutes)</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_lunch_time.'</td>
						</tr>
						'.$emp_qu_ans.'
					</table>';
				
	        $massagePrivateUser = $header;
	        $massagePrivateUser .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$privateUserName.'</b>,</p>';
	        $massagePrivateUser .= '<p>You have succesfully accept the job. </p>';
	        $massagePrivateUser .= '<p>Following is your job information.</p>';
	        $massagePrivateUser .= $job_info_free;
	        $massagePrivateUser .= '</div>';
	        $massagePrivateUser .= $footer;
	        //echo $massagePrivateUser;

	        $massageEmp = $header;
	        $massageEmp .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$empName.'</b>,</p>';
	        $massageEmp .= '<p>Your job has been accepted by the freelancer. </p>';
	        $massageEmp .= '<p>Following is your job information.</p>';
	        $massageEmp .= $job_info_emp;
	        $massageEmp .= '</div>';
	        $massageEmp .= $footer;
			
			//Admin EMail
			$mailAdmin = $header;
	        $mailAdmin .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>Admin</b>,</p>';
	        $mailAdmin .= '<p>Job has been accepted by the freelancer. </p>';
	        $mailAdmin .= '<p>Following is your job information.</p>';
	        $mailAdmin .= $job_info_admin;
	        $mailAdmin .= '</div>';
	        $mailAdmin .= $footer;
			
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
		    $adminEmail = $configGet->get('mail_from');
	        
	        /* Mail Send to Private user */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($privateUserEmail);
	            $mailFre->setSubject('Job Accept notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
	
			/* Mail Send to employer */
        	try {
	            $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($empEmail);
	            $mailEmp->setSubject('Job Accept notification');
	            $mailEmp->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
			
			/* Mail Send to employer */
        	try {
	            $mailAdmin = new \Gc\Mail('utf-8', $massageAdmin);
	            $mailAdmin->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailAdmin->setFrom($adminEmail, 'Locumkit');
	            $mailAdmin->addTo($adminEmail);
	            $mailAdmin->setSubject('Job Accept notification');
	            $mailAdmin->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* Email notification to Expired package user */
        public function sendPackageExpiredMail($userId,$packageId,$packageExpiryDate,$btnLink,$adapter){
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($userId,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        /* Get package name */
			$sqlPkgInfo = "SELECT * from user_acl_package WHERE id='$packageId'";	
		    $pkgInfoData = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
		    $pkgInfo = $pkgInfoData->current();
	        $pkgMessage = $header;
	        $pkgMessage .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hello <b>'.$freName.'</b>,</p>';
	        $pkgMessage .= '<p>Your freelancer account is going to be expired in <b>7 days</b>, please upgrade it and enjoy the freelancing at Locumkit. </p>';
	        $pkgMessage .= '<p>Your current package is : <b style="text-transform:uppercase">'.$pkgInfo['name'].' ( $'.$pkgInfo['price'].' ) </b>.</p>';
	        $pkgMessage .= '<p>Click below button to upgrade your account.</p>';
	        $pkgMessage .= $btnLink;
	        $pkgMessage .= '</div>';
	        $pkgMessage .= $footer;
	        //echo $pkgMessage;
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        /* Mail Send to employer */ 
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $pkgMessage);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('User Account Expired');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');

	         //send sms start
		$jobsmsController = new JobsmsController();
                $jobsmsController->sendPackageExpiredMailSms($userId);
                //send sms end	

	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }

        public function sendCloseJobNotification($jobId,$jobEid,$viewJobLink,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $empData 	= $this->getEmployerInfo($jobEid,$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		        $closeJobMsg = $header;
		        $closeJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hello <b>'.$empName.'</b>,</p>';
		        $closeJobMsg .= '<p>We have now closed job no #'.$jobId.' .</p>';
		        $closeJobMsg .= '<p>Unfortunatly we could not find you a successfull match for this job. </p>';
$closeJobMsg .= '<p>If this is a regular occurence for you then please feel free to contact us, as we at LocumKit can assist helping you obtain freelancers for your store.</p>';


		        $closeJobMsg .= '<p>Click below button to view your job.</p>';
		        $closeJobMsg .= '<p>&nbsp</p>';
		        $closeJobMsg .= '<p>&nbsp</p>';
		        $closeJobMsg .= $viewJobLink;
		        $closeJobMsg .= '</div>';
		        $closeJobMsg .= $footer;


		        $closeJobMsgAdmin = $header;
		        $closeJobMsgAdmin .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hello <b>Admin</b>,</p>';
		        $closeJobMsgAdmin .= '<p>The following employers job has just expired.</p>';
		        $closeJobMsgAdmin .= $this->getExpiredJobInfo($jobId,$adapter);		        
		        $closeJobMsgAdmin .= '</div>';
		        $closeJobMsgAdmin .= $footer;

		        //echo $closeJobMsgAdmin;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
		        /* Mail Send to employer */ 
	        	try {
		            $mailEmp = new \Gc\Mail('utf-8', $closeJobMsg);
		            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mailEmp->setFrom($adminEmail, 'Locumkit');
		            $mailEmp->addTo($empEmail);
		            $mailEmp->setSubject('Job Post Expired');
		            $mailEmp->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }

		        /* Mail Send to admin */ 
	        	try {
		            $mailAdm = new \Gc\Mail('utf-8', $closeJobMsgAdmin);
		            $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mailAdm->setFrom($adminEmail, 'Locumkit');
		            $mailAdm->addTo($adminEmail);
		            $mailAdm->setSubject('LocumKit job expired');
		            $mailAdm->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }
		    }
        }

        /* Private Job reminder notification mail */
        public function sendPrivateJobReminder($jobFid,$pEmpName,$pEmpEmail,$pJobTitle,$pJobRate,$pJobDate,$notifyDay,$pJobLocation,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($jobFid,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $pJobRate = $this->getCurrencySymbol().''.$pJobRate;
	        $privateJobMsg = $header;
	        $privateJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hello <b>'.$freName.'</b>,</p>';
	        $privateJobMsg .= '<p>This is just a courtesy reminder that you have a booking coming up.</p><p>Please see a summary of the details below:</p>';
    		$privateJobMsg .= '<p>Following is your job information.</p>';
    		$privateJobMsg .= '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobLocation.'</td>
						</tr>
					</table><br/>';
	        $privateJobMsg .= '</div>';
	        $privateJobMsg .= $footer;
	        
	        //echo $privateJobMsg;
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        /* Mail Send to employer */ 
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $privateJobMsg);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('Private Job reminder');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }

        /* Private Job On Day reminder notification mail */
        public function sendPrivateJobOnDayReminder($jobFid,$pEmpName,$pEmpEmail,$pJobTitle,$pJobRate,$pJobDate,$pJobLocation,$yesBtnLink,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($jobFid,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $pJobRate = $this->getCurrencySymbol().''.$pJobRate;
	        $privateJobMsg = $header;
	        $privateJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hello <b>'.$freName.'</b>,</p>';
	     //   $privateJobMsg .= '<h3>Please can you confirm your arrival to work today</h3>';
$privateJobMsg .= '<h3>Have you arrived at work today for the below stated booking?</h3>';
    		$privateJobMsg .= '<p>'.$yesBtnLink.'</p>';
    		//$privateJobMsg .= '<p>The details of the work are as per below:</p>';
    		$privateJobMsg .= '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$pJobLocation.'</td>
						</tr>
					</table>';
	        $privateJobMsg .= '</div>';
	        $privateJobMsg .= $footer;
	        
	        //echo $privateJobMsg;
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        /* Mail Send to employer */ 
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $privateJobMsg);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject('LocumKit private job confirmation of arrival');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }

        //  Send notification to guest user to update profile
        public function sendUpdateProfileNotificationToFreelancer($Fid,$firstname,$lastname,$email,$serverUrl)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $profileNoteMsg = $header;
	        $profileNoteMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hello <b>'.$firstname.' '.$lastname.'</b>,</p>';
	        $profileNoteMsg .= '<h3>Only One day left hurry up.</h3>';
	        $profileNoteMsg .= '<p> Your profile going to suspended tomorrow so hurry up to complete your profile with us to use full service of website.</p>';
	         $profileNoteMsg .= '<p> Please visit our website <a href="'.$serverUrl().'">click here</a>.';
	        $profileNoteMsg .= '</div>';
	        $profileNoteMsg .= $footer;
	        
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        /* Mail Send to employer */ 
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $profileNoteMsg);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($email);
	            $mailFre->setSubject('Profile Reminder Notification');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }

        //  Send Profile suspend notification
        public function sendProfileSuspendNotificationToFreelancer($Fid,$firstname,$lastname,$email,$serverUrl)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $profileNoteMsg = $header;
	        $profileNoteMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hello <b>'.$firstname.' '.$lastname.'</b>,</p>';
	        $profileNoteMsg .= '<h3 style="color:red">Profile suspened.</h3>';
	        $profileNoteMsg .= '<p> Your guest profile has beed suspended from Locumkit.</p>';	
	        $profileNoteMsg .= '</div>';
	        $profileNoteMsg .= $footer;
	        
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
	        /* Mail Send to employer */ 
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $profileNoteMsg);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($email);
	            $mailFre->setSubject('Profile suspended');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');


//send sms start
		$jobsmsController = new JobsmsController();
                $jobsmsController->sendProfileSuspendNotificationToFreelancerSms($Fid);
                //send sms end	


	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
        }
        /* Send email to freelance that 5 min left to reset freeze job */
        public function sendExpireFreezeNotification($job_id,$f_id,$expired_note_type,$adapter,$link)
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');

        	/* Get user e-mail id*/
		    $freData = $this->getFreelancerInfo($f_id,$adapter);
			if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
	        }
	        $job_info 	= $this->getJobInfo($job_id,$adapter);
		    $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $mail_css 	= $header;

	        /* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$job_id'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        }
	        if ($expired_note_type == 2) {
	        	$mail_sub = '5 mins left for job to unfreeze';
	        	$mail_title = '<p> The following job is locked just for another 5 minuted before it is available to all other applicable freelancer. Please apply now to confirm your booking for the following: </p>';
	        }else{
	        	$mail_sub =$jobDate.' / '.$jobAddress.' / '.$jobRate;
	        	$mail_title = '<p>The following job (ref no '.$job_id.') is no longer frozen and is available again for applicants. To view the job specifics please review the below. To accept this offer, please click on the accept button below .</p> ';
	        }
			$user_info ='
					<p style="text-align:left;"> '.$mail_title.' </p>
					'.$job_info.'<br/>
					'.$link.'
				</div>'.$footer.'</body></html>';
	        $massageFre = $mail_css.'
	        	<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		<p>Hello <b>'.$freName.'</b>,</p>
    				'.$user_info;
    		//echo $massageFre;
    		/* Mail Send to Acivated user */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($freEmail);
	            $mailFre->setSubject($mail_sub);
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
				
				//send sms start
				$jobsmsController = new JobsmsController();
                $jobsmsController->sendExpireFreezeNotificationSms($f_id,$job_id);
                //send sms end	
				
				
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        	
        }

        public function getFreelancerInfo($jobFid,$adapter){
        	/* Get freelancer e-mail id*/
			$sqlFreEmail = "SELECT email,firstname,lastname from user WHERE id='$jobFid'";	
		    $freEmailData = $adapter->query($sqlFreEmail, $adapter::QUERY_MODE_EXECUTE);
		    return $freEmails = $freEmailData->current();
        }
        public function getEmployerInfo($jobEmpId,$adapter){
        	/* Get record of employer */
	        $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'";	
	        $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
	        return $empUsers = $empUserDetails->current();
        }
        public function getPrivateUserInfo($puId,$adapter){
        	/* Get record of employer */

	        $sqlPriUser = "SELECT p_name,p_email from private_user
 WHERE p_uid = '$puId'";	
	        $privateUserDetails = $adapter->query($sqlPriUser, $adapter::QUERY_MODE_EXECUTE);
	        return $priUsers = $privateUserDetails->current();
        }
        public function getJobAddressInfo($jid,$adapter){
        	/* Get record of employer */
	        $sqlJob = "SELECT * from job_post WHERE job_id = '$jid'";	
	        $jobDetails = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        return $jobDetails = $jobDetails->current();
        }
        public function getJobInfo($jobId,$adapter){

        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	$jobDesc 	= $value['job_post_desc'];
	        	$jobEmpId 	= $value['e_id'];
	        }
	        $job_info = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Address</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Description</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDesc.'</td>
						</tr>	
					</table>';
			return $job_info;
        }

        /*Get expired job info*/
        public function getExpiredJobInfo($jobId,$adapter){

        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();

	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$jobEid 	= $value['e_id'];
	        	$countFreToNotify = $this->gettheCountOfEmailSend($jobId,$adapter);
	        	$empData 	= $this->getEmployerInfo($jobEid,$adapter);
		        if (!empty($empData)) {
		        	$empEmail 	= $empData['email'];
			    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
			    }
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        	$jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
	        	
	        }
	        $job_info = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
	        			<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobId.'</td>
						</tr>
						<tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Number of people sent to</th>
						<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$countFreToNotify.'</td>
					  </tr>
					  <tr>
						<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;"></th>
						<td style=" border: 1px solid black;  text-align:left;"> 
						 <table style="text-align:left;" width="100%">
						 <tr>
						 	<td width="50%" style="border-right:1px solid black;">SMS SEND : 0 </td>
							<td style="margin-left: 10px; display: block;">EMAIL SEND : '.$countFreToNotify.'</td>
						 </tr>
						</td>
					  </tr>
					</table>';
			return $job_info;
        }

        /* Send email to user after admin activation */
        public function sendActivationNotification($email,$firstname,$lastname,$login)
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');

        	/* Get user e-mail id*/
		    $userEmail = $email;
			$userLogin = $login;
		    $userName  = $firstname.' '.$lastname;

		    $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $mail_css 	= $header;
			$user_info ='
				<h3 style="text-align:left;">Hello Employer '.$userName.',</h3>
				<p>Thank you for joining LocumKit. We can confirm your account has been verified by our team and you have full unrestricted access to the site.</p>
				<p>Thank you for using Locumkit.</p>
				<p>Please do not respond to this email, if you have any queries please contact us here.</p>
					<!-- <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Login/username</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$userLogin.'</td>
						</tr>
					</table>-->
				</div>'.$footer.'</body></html>';
	        $massageFre = $mail_css.'
	        	<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left;">
	        		
    				'.$user_info;
    		/* Mail Send to Acivated user */
        	try {
	            $mailFre = new \Gc\Mail('utf-8', $massageFre);
	            $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailFre->setFrom($adminEmail, 'Locumkit');
	            $mailFre->addTo($userEmail);
	            $mailFre->setSubject('LocumKit account verified');
	            $mailFre->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* Cancel Emp Job notifiction to Freelancer */
        public function cancelJobByEmpNotificationToFreelancer($fid,$jid,$cancel_reason,$adapter)
        {
	        $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($fid,$adapter);
	        $jobDetails = $this->getCancelJobInfo($jid,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>'.$freName.'</b>,</p>';
		        $cancelJobMsg .= '<p>Employer has cancelled the following job</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
		        $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
		        $cancelJobMsg .= '</div>';
		        $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
		        $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
		        $cancelJobMsg .= '<p style="padding: 10px;">We have updated your calender as such that you are now available for jobs on that day. You shall receive emails now for that day. If you no longer are available to work on this day please login and make your self unavailable.</p>';
		        $cancelJobMsg .= '<p style="padding: 10px;"><b>We apologise for any inconvience this may have caused you.</b></p>';

		        $cancelJobMsg .= '</div></div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Freelancer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($freEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
										
					//send sms start
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->cancelJobByEmpNotificationToFreelancerSms($fid,$jid);
                    //send sms end
					
					
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
	        
        }

        /* Cancel Emp Job notifiction to Employer */
        public function cancelJobByEmpNotificationToEmployer($eid,$fid,$jid,$cancel_reason,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'%';
	        $freData 	= $this->getFreelancerInfo($fid,$adapter);
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    }
		    $empData 	= $this->getEmployerInfo($eid,$adapter);
	        $jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d-m-Y').'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Freelancer name </th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
						</tr>
					</table>';
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>'.$empName.'</b>,</p>';
		        $cancelJobMsg .= '<p>This email is sent to you as a confirmation that you have cancelled the following job:</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<h5>Your cancellation percentage now is : '.$cancelationPercent.' </h5>';
		        $cancelJobMsg .= '<p>We have notified the freelancer of the cancellation</p>';
		        $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
		        $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Your reason for cancellation was:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
		        $cancelJobMsg .= '</div>';
		        
		        $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
		        $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
		        $cancelJobMsg .= '<p style="padding: 10px;">This cancellations percentage is visible to potential freelancers. We want to promote an environment where minimum cancellation take place. They can also view your reason for cancellation as we accept that sometimes cancellation do need to take place. Your cancellation percentage is based on your last six months of results.  </p>';

		        $cancelJobMsg .= '</div></div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Freelancer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($empEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
					
					//send sms start
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->cancelJobByEmpNotificationToEmployerSms($eid,$jid);
                    //send sms end
					
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
        }

        /* Cancel Emp Job notifiction to Admin */
        public function cancelJobByEmpNotificationToAdmin($eid,$jid,$cancel_reason,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'%';
	        $empData 	= $this->getEmployerInfo($eid,$adapter);
	        if (!empty($empData)) {
	        	$empId 	= $eid;
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    	$jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">ID No.</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empId.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ref</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jid.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Reason
							</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancel_reason.'
							</td>
						</tr>
					</table>';
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>Admin</b>,</p>';
		        $cancelJobMsg .= '<p>The folowing employer has just cancelled a job</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<h5>Their cancellation rate now is: '.$cancelationPercent.' </h5>';

		        $cancelJobMsg .= '</div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Freelancer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($adminEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
        }




        
        /* Cancel Fre Job notifiction to Freelancer */
        public function cancelJobByFreNotificationToFreelancer($eid,$fid,$jid,$cancel_reason,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $cancelationPercent = $this->getFreCancellationRate($fid,$adapter).'%';
	        $freData 	= $this->getFreelancerInfo($fid,$adapter);
	        $empData = $this->getEmployerInfo($eid,$adapter);
	        $jobExtraData = $this->getJobAddressInfo($jid,$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    	$empAddress = $jobExtraData['job_address'].', '.$jobExtraData['job_region'].', '.$jobExtraData['job_zip'];
		    	
		    }

        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$jid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();

	        foreach ($job as $key => $value) {
	        	$jobDate 	= $value['job_date'];
	        }
		    
	        $jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer name </th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer Address
							</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empAddress.'</td>
						</tr>
					</table>';
	        if (!empty($freData)) {
	        	$freEmail 	= $freData['email'];
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>'.$freName.'</b>,</p>';
		        $cancelJobMsg .= '<p>This email is sent to you as a confirmation that you have cancelled the following job:</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<h5>Your cancellation percentage now is : '.$cancelationPercent.' </h5>';
		        $cancelJobMsg .= '<p>We have notified the employer of the cancellation</p>';
		        $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
		        $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Your reason for cancellation was:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
		        $cancelJobMsg .= '</div>';
		        
		        $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
		        $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
		        $cancelJobMsg .= '<p style="padding: 10px;">The cancellations percentage is visible to potential employers. We want to promote an environment where minimum cancellation take place. They can also view your reason for cancellation as we accept that sometimes cancellation do need to take place. Your cancellation percentage is based on your last six months of results.  </p>';

		        $cancelJobMsg .= '</div></div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Freelancer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($freEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
					
					 //send sms start
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->cancelJobByFreNotificationToFreelancerSms($fid,$jid);
                    //send sms end
					
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
        }

        /* Cancel Fre Job notifiction to Employer */
        public function cancelJobByFreNotificationToEmployer($fid,$eid,$jid,$cancel_reason,$is_relist,$adapter)
        {
	        $header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
	        $endecrypt = new Endecrypt();
	        $userEid = $endecrypt->encryptIt($eid);
	        $userFre = $endecrypt->encryptIt($fid);
	        $blockFreLink = $serverUrl().'/block-user?eid='.$userEid.'&fid='.$userFre;
	        $empData 	= $this->getEmployerInfo($eid,$adapter);
	        $jobDetails = $this->getCancelJobInfo($jid,$adapter);
	        if (!empty($empData)) {
	        	$empEmail 	= $empData['email'];
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>'.$empName.'</b>,</p>';
		        $cancelJobMsg .= '<p>Freelancer has cancelled the following job</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
		        $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
		        $cancelJobMsg .= '</div>';
		        $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
		        $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
		        $cancelJobMsg .= '<p style="padding: 10px;"><b>If you want to avoid using this freelancer in the future please <a href="'.$blockFreLink.'"> click here </a> to block.</b></p>';
		        if($is_relist == 1){
		        	
		        	$cancelJobMsg .= '<p style="padding: 10px;">As per your original posting this job has been reslisted for the original rate and details. Please go into the job if you want to edit anything. A seperate email shall be sent across confirming the relisting of this job with its details. Once someone has confirmed acceptance we shall notify you with an email.</p>';
		        	$cancelJobMsg .= '<p style="padding: 10px;">Please <a style="background: #00a9e0;color: #fff;text-decoration: none;padding: 5px;border-radius: 3px;text-transform: uppercase;" href="'.$serverUrl().'/managejob?e='.$jid.'">click here</a> to relist job.</p>';
		        }else{
		        	$cancelJobMsg .= '<p style="padding: 10px;">As per your original posting this job has not  been reslisted automatically. If you want please go into <b>Manage job</b> and copy the job to repost.</p>';
		        }
		        
		        $cancelJobMsg .= '<p style="padding: 10px;"><b>We apologise for any inconvience this may have caused you.</b></p>';

		        $cancelJobMsg .= '</div></div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Employer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($empEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
					
					//send sms start
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->cancelJobByFreNotificationToEmployerSms($eid,$jid);
                    //send sms end
					
					
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
	        
        }

        /* Cancel Emp Job notifiction to Admin */
        public function cancelJobByFreNotificationToAdmin($fid,$jid,$cancel_reason,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $cancelationPercent = $this->getFreCancellationRate($fid,$adapter).'%';
	        $freData 	= $this->getFreelancerInfo($fid,$adapter);
	        if (!empty($freData)) {
	        	$freId 	= $fid;
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    	$jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">ID No.</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freId.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ref</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jid.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Reason
							</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancel_reason.'
							</td>
						</tr>
					</table>';
		        $cancelJobMsg = $header;
		        $cancelJobMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b>Admin</b>,</p>';
		        $cancelJobMsg .= '<p>The following Freelancer has just cancelled a job</p>';
		        
		        $cancelJobMsg .= $jobDetails;
		        $cancelJobMsg .= '<h5>Their cancellation rate now is: '.$cancelationPercent.' </h5>';

		        $cancelJobMsg .= '</div>';
		        $cancelJobMsg .= $footer;
		        //echo $closeJobMsg;
		        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
	        	$adminEmail = $configGet->get('mail_from');
	        	//echo $cancelJobMsg;
		        /* Mail Send to Freelancer */ 
	        	try {
		            $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
		            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
		            $mail->setFrom($adminEmail, 'Locumkit');
		            $mail->addTo($adminEmail);
		            $mail->setSubject('Locumkit: Cancellation of job');
		            $mail->send();
		            //$this->flashMessenger()->addSuccessMessage('Message sent');
		        } catch (Exception $e) {
		            //$this->flashMessenger()->addErrorMessage($e->getMessage());
		        }  
		    }
        }

        /* Block user notifiction to Admin */
        public function sendBlockNotificationToAdmin($eid,$fid,$adapter)
        {
        	$header 	= $this->mailHeader();
	        $footer 	= $this->mailFooter();
	        $freData 	= $this->getFreelancerInfo($fid,$adapter);
	        $empData 	= $this->getEmployerInfo($eid,$adapter);
	        if (!empty($freData)) {
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    }

		    if (!empty($empData)) {
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    }
	    	
	        $blockMsg = $header;
	        $blockMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hi <b>Admin</b>,</p>';
	        $blockMsg .= '<p>The Employer <b>'.$empName.'</b> just block the freelancer <b>'.$freName.'</b></p>';
	        
	        $blockMsg .= '</div>';
	        $blockMsg .= $footer;
	        //echo $closeJobMsg;
	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
        	//echo $cancelJobMsg;
	        /* Mail Send to Freelancer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $blockMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($adminEmail);
	            $mail->setSubject('Locumkit: Block Freelancer');
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
		    
        }



        public function mailHeader(){
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$site_name 	= $configGet->get('site_name');
        	$header = '<div style="width: 700px;"><div class="mail-header" style="background: #00A9E0; padding: 20px 50px;  border: 2px solid #000; clear: both; ">';
        	$header .= '
        		<a href="'.$serverUrl().'"><img src="'.$serverUrl().'/public/frontend/locumkit-template/img/logo.png" alt="'.$site_name.'" width="100px"></a>
        		';	
        	$header .= '</div><div style="width: 100%;  margin-bottom: -5px;">';
        	return $header;
        }
        public function mailFooter(){
        	/* Get admin record */	        
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$site_name 	= $configGet->get('site_name');
			$site_phone     = $configGet->get('site_mobile');
			$site_addr 	= $configGet->get('site_addr');
			$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');			
	        $footer = '<div style=" padding: 0px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000; text-align: left; font-family: sans-serif; margin: -15px 0 0 0;">
	        	<p><b>Thank you for using Locumkit</b></p>
	        	<p><em>Please do not respond to this email, if you have any queries please contact us <a href="'.$serverUrl().'/contact">here</a>.</em></p>
	        	</div></div><div class="mail-footer" style="background: #252525; color: #fff; padding: 15px 50px; margin-top: 0px; ">
	        	<em>'.$site_phone.'</em>&nbsp; | &nbsp;<em>'.$site_addr.'</em>
                        <p>Sincerely,<br>LocumKit Team.</p>
	        	</div></div>';
	        return 	 $footer;      	
	        
        }

        public function getCurrencySymbol()
        {
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	return $currencySymbol = $configGet->get('site_currency');	
        }
        public function getCancelJobInfo($cjid,$adapter)
        {
        	/* Fetch record of job */
        	$sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";	
	        $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
	        $job = $jobView->toArray();
	        foreach ($job as $key => $value) {	
	        	$jobId 		= $value['job_id'];
	        	$jobTitle 	= $value['job_title'];
	        	$jobDate 	= $value['job_date'];
	        	$jobRate 	= $this->getCurrencySymbol().''.number_format($value['job_rate'],2);
	        }
	        $job_info = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
	        			<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ref no
							</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobId.'</td>
						</tr>
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job Title</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
						</tr>
						<tr> 
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
						</tr>
						<tr style="background-color: #f2f2f2;">
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Rate</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
						</tr>
					</table>';
			return $job_info;
        }

        /* Cancellation Rate Freelancer */
        public function getFreCancellationRate($uid,$adapter)
        {
        	$sqlContCancellation = "SELECT * FROM job_cancel WHERE c_uid = '$uid'";	
	        $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
	        $count = $contCancellation->count();
	        $finalCount = $count + 1; // adding current cancellation
	        $sqlAcceptedJob = "SELECT * FROM job_action WHERE ( action = '6' OR action = '3' ) AND f_id = '$uid'";	
	        $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
	        $countJobAccept = $acceptedJob->count();
	        $freCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
        	return $freCancellationRate;	
        }

        /*Cancellation Rate Employer */
        public function getEmpCancellationRate($uid,$adapter)
        {
        	$sqlContCancellation = "SELECT * FROM job_post WHERE e_id = '$uid' AND job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' )";	
	        $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
	        $count = $contCancellation->count();
	        $finalCount = $count + 1; // adding current cancellation
	        $sqlAcceptedJob = "SELECT * FROM job_post WHERE e_id = '$uid' AND ((job_status = 4) OR ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' )))";	
	        $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
	        $countJobAccept = $acceptedJob->count();
	        $empCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
        	return $empCancellationRate;	
        }

        /* Count the notification send */
        public function gettheCountOfEmailSend($jobId,$adapter)
        {
        	$sqlCountFreJobPost = "SELECT * FROM job_action WHERE job_id = '$jobId'";	
	        $countFreJobPost = $adapter->query($sqlCountFreJobPost, $adapter::QUERY_MODE_EXECUTE);
	        return $countFreNotify = $countFreJobPost->count();
        }

        /* Get Employer Store Information */
        public function getStoreInfo($adapter,$storeId)
        {
        	$sqlStoreInfo = "SELECT emp_store_name FROM employer_store_list WHERE emp_st_id = '$storeId'";
	        $storeInfoObj = $adapter->query($sqlStoreInfo, $adapter::QUERY_MODE_EXECUTE);
	        $storeInfo = $storeInfoObj->current();
	        return $storeInfo['emp_store_name'];
        }

        /* Send on day Expense Notification to freelancer */
        public function sendExpenseNotification($jobId,$fid,$link,$adapter)
        {
        	$header = $this->mailHeader();
        	$footer = $this->mailFooter();
        	$freData 	= $this->getFreelancerInfo($fid,$adapter);	        
	        if (!empty($freData)) {
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    	$ferEmail	= $freData['email'];
		    }

        	$expenseMsg = $header;
	        $expenseMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hi <b>'.$freName.'</b>,</p>';
	        $expenseMsg .= '<p>Please can you enter the amount you have spent on travel & lunch today.</p>';
	        $expenseMsg .= '<p>Please '.$link.'&nbsp; to add the expenses. </p>';
	        $expenseMsg .= '<p>Once you are happy please click submit</p>';
	        
	        $expenseMsg .= '</div>';
	        $expenseMsg .= $footer;

	        //echo $expenseMsg;



	        $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
        	//echo $cancelJobMsg;
	        /* Mail Send to Freelancer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $expenseMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($ferEmail);
	            $mail->setSubject('Locumkit: Job expenses');
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* Send job summary Notification to freelancer */
        public function sendFreJobSummaryNotification($freId,$jobId,$income,$expense,$freFeedback,$adapter)
        {
        	$header = $this->mailHeader();
        	$footer = $this->mailFooter();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$freData 	= $this->getFreelancerInfo($freId,$adapter);	        
	        if (!empty($freData)) {
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    	$ferEmail	= $freData['email'];
		    }
		    $expense = $expense != 0 ? $expense : 'Not submited yet.';
		    $freFeedback = $freFeedback != 0 ? $freFeedback : 'Not submitted yet.';
        	$summaryMsg = $header;
	        $summaryMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hi <b>'.$freName.'</b>,</p>';
	        $summaryMsg .= '<p>This below is your summary for the following job:</p>';
	        $summaryMsg .= '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
			        			<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> #'.$jobId.'</td>
								</tr>
								<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Income
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$this->getCurrencySymbol().number_format($income,2).'</td>
								</tr>
								<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Expenses
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$this->getCurrencySymbol().number_format($expense,2).'</td>
								</tr>
								<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Feedback
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$freFeedback .'</td>
								</tr>
							</table>';

	        $summaryMsg .= '<p>&nbsp;</p><p>To see details of these detials please login to your profile</p>';
	        $summaryMsg .= '<p>If there are entires with N/A then this is because we did not get information from you or the employer (feedback). You can always go into your profile to add information on your income and expense.</p>';
	        
	        $summaryMsg .= '</div>';
	        $summaryMsg .= $footer;

	        //echo $summaryMsg;
	        
        	$adminEmail = $configGet->get('mail_from');
        	//echo $cancelJobMsg;
        	$currentDate = date('Y-m-d');
	        /* Mail Send to Freelancer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $summaryMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($ferEmail);
	            $mail->setSubject('Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days')));
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }
        
        /* Send job summary Notification to employer */
        public function sendEmpJobSummaryNotification($empId,$freId,$jobId,$income,$expense,$empFeedback,$adapter)
        {
        	$header = $this->mailHeader();
        	$footer = $this->mailFooter();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
	        $endecrypt = new Endecrypt();
	        $userEid = $endecrypt->encryptIt($empId);
	        $userFre = $endecrypt->encryptIt($freId);
	        $blockFreLink = $serverUrl().'/block-user?eid='.$userEid.'&fid='.$userFre;
        	$empData 	= $this->getEmployerInfo($empId,$adapter);	        
	        if (!empty($empData)) {
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    	$empEmail	= $empData['email'];
		    }
		    $expense = $expense != 0 ? $expense : 'Not submited yet.';
		    $empFeedback = $empFeedback != 0 ? $empFeedback : 'Not submitted yet.';
        	$summaryMsg = $header;
	        $summaryMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
	        		<p>Hi <b>'.$empName.'</b>,</p>';
	        $summaryMsg .= '<p>This below is your summary for the following job:</p>';
	        $summaryMsg .= '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
			        			<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> #'.$jobId.'</td>
								</tr>
								<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Expense
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$this->getCurrencySymbol().number_format($income,2).'</td>
								</tr>
								
								<tr> 
									<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Feedback
									</th>
									<td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$empFeedback .'</td>
								</tr>
							</table>';

	        $summaryMsg .= '<p>&nbsp;</p><p>Want to block this freelancer, please <a href="'.$blockFreLink.'">click here.</a></p>';
	        $summaryMsg .= '<p>To see details of these detials please login to your profile</p>';
	        $summaryMsg .= '<p>If there are entires with N/A then this is because we did not get information from you or the employer (feedback). You can always go into your profile to add information on your income and expense.</p>';
	        
	        $summaryMsg .= '</div>';
	        $summaryMsg .= $footer;

	        //echo $summaryMsg;
	        
        	$adminEmail = $configGet->get('mail_from');
        	//echo $cancelJobMsg;
        	$currentDate = date('Y-m-d');
	        /* Mail Send to Freelancer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $summaryMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($empEmail);
	            $mail->setSubject('Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days')));
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }  
        }

        /* Send dispute notificatin to all users */
        public function sendDisputeSubmitNotification($id,$fre_id,$emp_id,$job_id,$user_type,$avg_rate,$adapter)
        {
        	$header = $this->mailHeader();
        	$footer = $this->mailFooter();
        	$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
	        $endecrypt = new Endecrypt();
	        $feedbackId = $endecrypt->encryptIt($id);
	        $userFre = $endecrypt->encryptIt($fre_id);
	        $userEmp = $endecrypt->encryptIt($fre_id);
	        $feedbackFreLink = $serverUrl().'/feedback-dispute?feedbackId='.$feedbackId.'&u='.$userFre;
	        $feedbackEmpLink = $serverUrl().'/feedback-dispute?feedbackId='.$feedbackId.'&u='.$userEmp;
	        $feedbackAdminLink = $serverUrl().'/admin/config/user/feedback/user-feedback/edit/'.$id;
        	$empData 	= $this->getEmployerInfo($emp_id,$adapter);	        
	        if (!empty($empData)) {
		    	$empName 	= $empData['firstname'].' '.$empData['lastname'];
		    	$empEmail	= $empData['email'];
		    }
		    $freData 	= $this->getFreelancerInfo($fre_id,$adapter);	        
	        if (!empty($freData)) {
		    	$freName 	= $freData['firstname'].' '.$freData['lastname'];
		    	$freEmail	= $freData['email'];
		    }
		    
		    if ($user_type == 2) {
		    	$disputeFreMsg = $header;
		        $disputeFreMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b style="text-transform: capitalize;">'.$freName.'</b>,</p>';
		        $disputeFreMsg .= '<p><b style="text-transform: capitalize;">'.$empName.'</b> has just disputed the feedback you have left for them in regards to the job <b>#'.$job_id.'</b>. </p>';
		        
		        $disputeFreMsg .= '<p>We at LocumKit shall look into this and hope to come to a resolution within the next 24-48 hrs.</p>';
		        
$disputeFreMsg .= '<p>We might contact you to help us in this process.</p>';

		        $disputeFreMsg .= '</div>';
		        $disputeFreMsg .= $footer;

		        $disputeEmpMsg = $header;
		        $disputeEmpMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b style="text-transform: capitalize;">'.$empName.'</b>,</p>';
		        $disputeEmpMsg .= '<p>We have recieved your dispute in regards to the feedback left for you by <b style="text-transform: capitalize;">'.$freName.'</b> on job <b>#'.$job_id.'</b>. </p>';
		        
		        $disputeEmpMsg .= '<p>Please allow admin 24-48 hours to look into this and then take appropriate action.</p>';
		        
		        $disputeEmpMsg .= '</div>';
		        $disputeEmpMsg .= $footer;

		        $disputeAdminMsg = $header;
		        $disputeAdminMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi Admin,</p>';
		        $disputeAdminMsg .= '<p><b style="text-transform: capitalize;">'.$empName.'</b> submit dispute on feedback that submitted by <b style="text-transform: capitalize;">'.$freName.'</b> on job <b>#'.$job_id.'</b>. </p>';
		        
		        $disputeAdminMsg .= '<p>Please <a href="'.$feedbackAdminLink.'">click here</a> to view the datails.</p>';
		        
		        $disputeAdminMsg .= '</div>';
		        $disputeAdminMsg .= $footer;
		    }elseif($user_type == 3){
		    	$disputeEmpMsg = $header;
		        $disputeEmpMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b style="text-transform: capitalize;">'.$empName.'</b>,</p>';
		        $disputeEmpMsg .= '<p><b style="text-transform: capitalize;">'.$freName.'</b> has just disputed the feedback you have left for them in regards to the job<b>#'.$job_id.'</b>. </p>';
		        
		        $disputeEmpMsg .= '<p>We at LocumKit shall look into this and hope to come to a resolution within the next 24-48 hrs.</p>';
$disputeEmpMsg .= '<p>We might contact you to help us in this process.</p>';
		        
		        $disputeEmpMsg .= '</div>';
		        $disputeEmpMsg .= $footer;

		        $disputeFreMsg = $header;
		        $disputeFreMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi <b style="text-transform: capitalize;">'.$freName.'</b>,</p>';
		        $disputeFreMsg .= '<p>We have recieved your dispute in regards to the feedback left for you by <b style="text-transform: capitalize;">'.$empName.'</b> on job <b>#'.$job_id.'</b>. </p>';
		        
		        $disputeFreMsg .= '<p>Please allow admin 24-48 hours to look into this and then take appropriate action .</p>';
		        
		        $disputeFreMsg .= '</div>';
		        $disputeFreMsg .= $footer;

		        $disputeAdminMsg = $header;
		        $disputeAdminMsg .= '<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; font-family: sans-serif;">
		        		<p>Hi Admin,</p>';
		        $disputeAdminMsg .= '<p><b style="text-transform: capitalize;">'.$freName.'</b> submit dispute on feedback that submitted by <b style="text-transform: capitalize;">'.$empName.'</b> on job <b>#'.$job_id.'</b>. </p>';
		        
		        $disputeAdminMsg .= '<p>Please <a href="'.$feedbackAdminLink.'">click here</a> to view the datails.</p>';
		        
		        $disputeAdminMsg .= '</div>';
		        $disputeAdminMsg .= $footer;
		    }
        	

	        /*echo $disputeFreMsg;
	        echo "<br/>";
	        echo $disputeEmpMsg;
	        echo "<br/>";
	        echo $disputeAdminMsg;*/
	        
        	$adminEmail = $configGet->get('mail_from');
        	//echo $cancelJobMsg;
	        /* Mail Send to Freelancer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $disputeFreMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($freEmail);
	            $mail->setSubject('Dispute Alert on job: #'.$job_id);
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');

                //send sms start
		$jobsmsController = new JobsmsController();
                $jobsmsController->sendDisputeSubmitNotificationSms($fre_id,$job_id,$freName,$empName);
                //send sms end


	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 

	        /* Mail Send to Employer */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $disputeEmpMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($empEmail);
	            $mail->setSubject('Dispute Alert on job: #'.$job_id);
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');

                //send sms start
		$jobsmsController = new JobsmsController();
                $jobsmsController->sendDisputeSubmitNotificationSms($emp_id,$job_id,$empName,$freName);
                //send sms end

	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 

	        /* Mail Send to Admin */ 
        	try {
	            $mail = new \Gc\Mail('utf-8', $disputeAdminMsg);
	            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mail->setFrom($adminEmail, 'Locumkit');
	            $mail->addTo($adminEmail);
	            $mail->setSubject('Dispute Alert on job: #'.$job_id);
	            $mail->send();
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        } 
        }


		public function invoiceMail($contant,$semail){

			/* Mail Send to employer */
         
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');

        
              	try {
	            $mailEmp = new \Gc\Mail('utf-8', $contant);
	            $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
	            $mailEmp->setFrom($adminEmail, 'Locumkit');
	            $mailEmp->addTo($semail);
	            $mailEmp->setSubject('Invoice');
	            $mailEmp->send();


return true ;
	            //$this->flashMessenger()->addSuccessMessage('Message sent');
	        } catch (Exception $e) {
return false ;
	            //$this->flashMessenger()->addErrorMessage($e->getMessage());
	        }
		}

}