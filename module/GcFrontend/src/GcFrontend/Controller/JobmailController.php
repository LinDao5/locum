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
  use GcFrontend\Controller\PdfController as PdfController;
  use Zend\Mail\Message;
  use Zend\Mime\Message as MimeMessage;
  use Zend\Mime\Part as MimePart;
  use Zend\Mime\Mime;
  use Zend\Mail\Transport\Sendmail;
  use FudugoApp\Controller\Notification\NotificationController as NotificationController;

  use Zend\Mail\Transport\Smtp as SmtpTransport;
  use Zend\Mail\Transport\SmtpOptions;
  
  class JobmailController extends Action
  {     
    public function sendAcceptMailToUser($uid,$cjid,$adapter){
      $functionsController = new FunctionsController();
      $notifyController = new NotificationController();
      /* Fetch record of job */
      $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";  
      $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
      $job = $jobView->toArray();
      foreach ($job as $key => $value) {  
        $jobTitle   = $value['job_title'];
        $jobDate  = $value['job_date'];
        $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
        $subject_jobRate  = $this->getCurrencyCode().number_format($value['job_rate'],2);
        $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
        $jobDesc  = $value['job_post_desc'];
        $jobEmpId   = $value['e_id'];
        $jobStoreId = $value['store_id'];
        $jobStarttime = $value['job_start_time'];
      }      
      $new_date=str_replace("/","-",$jobDate);
      /* Get record of employer */
      $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'"; 
      $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
      $empUsers = $empUserDetails->toArray();
      foreach ($empUsers as $key => $value) {
        $empName  = $value['firstname']." ".$value['lastname'];
        $empEmail   = $value['email'];
      }

      //Current EMP cancellation percentage
      $cancellationRate = $functionsController->getEmpCancellationRate($jobEmpId,$adapter);
      $cancellationRate = ($cancellationRate > 0) ? $cancellationRate.'%' : '0.00%';
      //Current EMP feedback percentage
      $currentFeedbackData =  $functionsController->getFeedbackByUserId($adapter, $jobEmpId, 2);
      $feedbackRate = round($functionsController->getOverallRating($currentFeedbackData),2);
      $feedbackRate = ($feedbackRate > 0) ? $feedbackRate.'%' : '0.00%';


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
      $store_start_time = $functionsController->getTimeOfDay($startTime,$job_day);
      $store_end_time = $functionsController->getTimeOfDay($endTime,$job_day);
      $store_lunch_time = $functionsController->getTimeOfDay($lunchTime,$job_day).' (Min)'; 

      /* Get record of freelancer */
      $sqlFreUser = "SELECT firstname,lastname,email,user_acl_profession_id,id from user WHERE id = '$uid'";  
      $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
      $freUsers = $freUserDetails->toArray();
      foreach ($freUsers as $key => $value) {
        $freName     = $value['firstname']." ".$value['lastname'];
        $freEmail      = $value['email'];
        $freID         = $value['id'];
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

      $emp_qu_ans.='
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store cancellation percentage</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$cancellationRate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store feedback percentage</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$feedbackRate.'</td>
            </tr>';
      
      /* Get record of freelancer */
      $sqlFreUserExtra = "SELECT * from user_extra_info WHERE uid = '$uid'";  
      $freUserExtraDetails = $adapter->query($sqlFreUserExtra, $adapter::QUERY_MODE_EXECUTE);
      $freUsersExtra = $freUserExtraDetails->toArray();
      foreach ($freUsersExtra as $key => $value) {
        $freGoc      = $value['goc'];
        $freaop      = $value['aop'];
        $freaoc_id     = $value['aoc_id'];
        $freinsurance  = $value['inshurance_company'];
        $freinsuranceno= $value['inshurance_no'];
        $freinsurance_date  = $value['inshurance_renewal_date'];
      }
      if($freprofession==3){
        $fre_addinfo=' 
        <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
           <tr style="background-color: #92D000;">
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit job invitation - information you provided us </th>
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
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Opthalmic number (OPL):</th>
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
        $fre_addinfo='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> LocumKit job invitation - information you provided us </td>
            </tr>
            <tr>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;" colspan="2">Please check the details below and advise us immediately if this information is incorrect</td>
          </tr>
            '.$free_qu_ans.'
          </table><br>';
          
      }

          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $configGet  = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
          $adminEmail = $configGet->get('mail_from');
          $mail_css   = '
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
          
          .mail-job-info {
              padding: 25px 5px 30px;
          }
        </style>'.$header;
        $freelancer_terms = $this->locum_email_terms('#92D000');
      $job_info_free ='
          <h3 style="text-align:left;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (Key Details)</td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (additional information) </td>
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
            <p>Should you need to cancel this job, please <a href="'.$serverUrl().'/cancel-job?e='.$cjid.'">click here</a>. </p>
            <p><br/></p>
          '.$freelancer_terms.'
        </div>'.$footer.'</body></html>';
        
        $job_info_emp ='
          <h3 style="text-align:left;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (Key Details)</td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation - details of locum booked</td>
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
            '.$free_qu_ans.'
          </table>
          <br>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (additional information)</td>
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

            <p>Should you need to cancel this job, please <a href="'.$serverUrl().'/cancel-job?e='.$cjid.'">click here</a>. </p>
        </div>'.$footer.'</body></html>';
        $job_info_admin ='
          <h3 style="text-align:left;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (Key Details) </td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation - details of locum booked</td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (additional information)</td>
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
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <p>Hi '.$freName.',</p>            
            <p>We would like to inform you that the following booking has been confirmed. </p>
            <p>Please review the details below:</p>
            '.$job_info_free;
        //echo "<br/>";

          $massageEmp = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$empName.',</p>
              <p> We would like to inform you that the following booking has been confirmed</p>
              '.$job_info_emp;
              //echo "<br/>";

          $massageAdm = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>The following booking has been confirmed </p>
              <p>A job has been posted by: <b>'.$empName.' ('.$jobEmpId.')</b> & accepted by : <b>'.$freName.' ('.$freID.')</b> </p>
                <p>Following is job information : </p>
              '.$job_info_admin;

        /* Mail Send to freelancer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Booking Confirmation');
              // $mailFre->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,'Booking Confirmation');
          
                //send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->bookingConfirmationfre($uid,$cjid,null);
                //send sms end
      
            } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  

          /* Mail Send to employer */
          try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Booking confirmation: '.$jobTitle);
              // $mailEmp->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              $sub = 'Booking confirmation: '.$jobTitle;
              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$sub);

         
                //send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->bookingConfirmationemp($jobEmpId,$cjid,null);
                //send sms end

            } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
            
            
            //Mobile APP Notification
            $mobile_invitation_send = $notifyController->notification($cjid,$message="Job Ref: ".$cjid.", Location: ".$emp_store_address.", Rate: ".$subject_jobRate.". Open this message to view full details.",$title='Booking confirmation',$uid,$types="");
             //Mobile APP Notification
            $mobile_invitation_send = $notifyController->notification($cjid,$message="Job Ref: ".$cjid.", Locum: ".$freName.", Rate: ".$subject_jobRate.". Open this message to view full details.",$title='Booking confirmation',$jobEmpId,$types="");
    
          /* Mail Send to Admin */
          try {
              // $mailAdm = new \Gc\Mail('utf-8', $massageAdm);
              // $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailAdm->setFrom($adminEmail, 'Locumkit');
              // $mailAdm->addTo($adminEmail);
              // $mailAdm->setSubject('Booking Confirmation: '.$jobTitle);
              // $mailAdm->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              $sub = 'Booking confirmation: '.$jobTitle;
              $this->sendSMTPMail($massageAdm,$adminEmail,$adminEmail,$to_name,$sub);

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
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
          }

          /* Get record of employer */
          $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'"; 
          $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
          $empUsers = $empUserDetails->toArray();
          foreach ($empUsers as $key => $value) {
            $empName  = $value['firstname']." ".$value['lastname'];
            $empEmail   = $value['email'];
          }

          /* Get record of freelancer */
          $sqlFreUser = "SELECT firstname,lastname,email from user WHERE id = '$uid'";  
          $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
          $freUsers = $freUserDetails->toArray();
          foreach ($freUsers as $key => $value) {
            $freName  = $value['firstname']." ".$value['lastname'];
            $freEmail   = $value['email'];
          }
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          $mail_css   = '
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
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job title</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job address</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job description</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDesc.'</td>
            </tr> 
          </table>
        </div>'.$footer.'</body></html>';

          $massageFre = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$freName.'</b>,</p>
            <br/>
            <p>You have successfully apply for the job, please wait next 24 hours for employer notification on the selected job.
            </p>
            <p>Following is your job information : </p>
            '.$job_info;
        //echo "<br/>";

          $massageEmp = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$empName.'</b>,</p>
            <br/>
            <p> someone is apply for your job..
            </p>
            <p>Following is your job information : </p>
            '.$job_info;
        //echo "<br/>";
        
          /*echo $massageAdm = $mail_css.'
            <div class="mail-job-info">
              <p>The job of employer <b>'.$empName.'</b> is accepted by the locum <b>'.$freName.'</b></p>
                <p>Folowwing is your job information : </p>
              '.$job_info;*/
          /* Mail Send to freelancer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Job apply notification');
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,'Job apply notification');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  

          /* Mail Send to employer */
          try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Job apply notification');
              // $mailEmp->send();

              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,'Job apply notification');
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
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
          }

          /* Get record of employer */
          $sqlEmpUser = "SELECT firstname,lastname,email from user WHERE id = '$jobEmpId'"; 
          $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
          $empUsers = $empUserDetails->toArray();
          foreach ($empUsers as $key => $value) {
            $empName  = $value['firstname']." ".$value['lastname'];
            $empEmail   = $value['email'];
          }

          /* Get record of freelancer */
          $sqlFreUser = "SELECT firstname,lastname,email from user WHERE id = '$uid'";  
          $freUserDetails = $adapter->query($sqlFreUser, $adapter::QUERY_MODE_EXECUTE);
          $freUsers = $freUserDetails->toArray();
          foreach ($freUsers as $key => $value) {
            $freName  = $value['firstname']." ".$value['lastname'];
            $freEmail   = $value['email'];
          }

                    $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          $mail_css   = '
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job title</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job address</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job description</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDesc.'</td>
            </tr> 
          </table>
          </div>'.$footer.'</body></html>';

          $massageFre = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$freName.'</b>,</p>
            <br/>
            <p>Congrats..! You have got the job.
            </p>
            <p>Following is your job information : </p>
            '.$job_info;
          //echo "<br/>";

          $massageEmp = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$empName.'</b>,</p>
            <br/>
            <p> You have assign job to the locum.
            </p>
            <p>Following is your job information : </p>
            '.$job_info;
          //echo "<br/>";

          $massageAdm = $mail_css.'
            <div class="mail-job-info" style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>The job of employer <b>'.$empName.'</b> is accepted by the locum <b>'.$freName.'</b></p>
                <p>Following is job information : </p>
              '.$job_info;

               /* Mail Send to freelancer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Job apply notification');
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,'Job apply notification');

              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  

          /* Mail Send to employer */
          try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Job apply notification');
              // $mailEmp->send();

              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,'Job apply notification');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }


          /* Mail Send to Admin */
          try {
              // $mailAdm = new \Gc\Mail('utf-8', $massageAdm);
              // $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailAdm->setFrom($adminEmail, 'Locumkit');
              // $mailAdm->addTo($adminEmail);
              // $mailAdm->setSubject('Job apply notification');
              // $mailAdm->send();

              $this->sendSMTPMail($massageAdm,$adminEmail,$adminEmail,$to_name,'Job apply notification');

              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
  
        }   


        //  Approval mail to private user

        public function sendAprrovalMailToPrivateUser($puid,$cjid,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $privateUserData  = $this->getPrivateUserInfo($puid,$adapter);
          if (!empty($privateUserData)) {
            $privateUserEmail   = $privateUserData['p_email'];
          $privateUserName  = $privateUserData['p_name'];
          }
          $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->current();
          $empData  = $this->getEmployerInfo($job['e_id'],$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          }
          $jobData  = $this->getJobInfo($cjid,$adapter);
          $massagePrivateUser = $header;
          $massagePrivateUser .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$privateUserName.'</b>,</p>';
          $massagePrivateUser .= '<p>You selected for the following job by employer. </p>';
          $massagePrivateUser .= '<p>Following is your job information.</p>';
          $massagePrivateUser .= $jobData;
          $massagePrivateUser .= '</div>';
          $massagePrivateUser .= $footer;
          

          $massageEmp = $header;
          $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello <b>'.$empName.'</b>,</p>';
          $massageEmp .= '<p>Job Successfully Assigned To Locum. </p>';
          $massageEmp .= '<p>Following is your job information.</p>';
          $massageEmp .= $jobData;
          $massageEmp .= '</div>';
          $massageEmp .= $footer;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          /* Mail Send to Private user */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($privateUserEmail);
              // $mailFre->setSubject('Job aprroved notification');
              // $mailFre->send();

              $this->sendSMTPMail($massagePrivateUser,$adminEmail,$privateUserEmail,$to_name,'Job aprroved notification');

              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
  
          /* Mail Send to employer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageEmp);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($empEmail);
              // $mailFre->setSubject('Job aprroved notification');
              // $mailFre->send();

              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,'Job aprroved notification');
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
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $adminEmail = $configGet->get('mail_from');
          /* Get All Customer Data */
          $userObj = new User\Collection();
          $userData = $userObj->getUsers();
          foreach ($userData as $key => $uData) {
            if ($uData->getUserAclRoleId() == 2 && $uData->getActive() == 1) {
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
                        'job_date'  => $value['job_date'],
                        'job_day' => date('l',$jobDate),
                        'job_rate'  => $this->getCurrencySymbol().number_format($value['job_rate'],2),
                        'store'   => $this->getStoreInfo($adapter,$value['store_id']),
                        'location'  => $value['job_address'].', '.$value['job_region'].', '.$value['job_zip'],
                        'view'    => $serverUrl().'/single-job?view='.$value['job_id']
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
                        'job_date'  => date('d-m-Y',strtotime($value['priv_job_start_date'])),
                        'job_day' => date('l',$jobDate),
                        'job_rate'  => $this->getCurrencySymbol().number_format($value['priv_job_rate'],2),
                        'name'    => $value['emp_name'],
                        'location'  => $value['priv_job_location'],
                        'view'    => $serverUrl().'/private-job'
                      ); 
                  }
                  
                }
                $freData  = $this->getFreelancerInfo($freId,$adapter);
                if (!empty($freData)) {
                  $freEmail   = $freData['email'];
                  $freName  = $freData['firstname'].' '.$freData['lastname'];
                }
                if ($i > 0) { 
                  $weeklyReminderMsg = $header;
                  $weeklyReminderMsg .= '<div style="padding: 25px 50px 5px; text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">';
                  if (empty($weekLiveJobArray) && empty($weekPrivateJobArray)) {
                    $weeklyReminderMsg .= '<p>Hi '.$freName.'</p><p>You currently have no bookings for this week.</p>';
                    $weeklyReminderMsg .= '<p>We have noticed increasing your distance or reducing your rate can help you get more job notifications and subsequently more job bookings. You can always contact us at Locumkit and we might be able to help you, if you still find yourself a bit light on job bookings. </p>';
                    $weeklyReminderMsg .= '<p>If you do have bookings, please update your calender so that you can benefit from automatic reminders and financial updates. </p>';
                  }else{
                    $weeklyReminderMsg .= '<p>Hi '.$freName.'</p><p>Please see below the upcoming booking(s) you have for the next week: </p>';
                  }
                 
                  if (!empty($weekLiveJobArray)) {
                    $weeklyReminderMsg .= '
                      <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> LocumKit job </h3>
                    <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; margin-bottom:20px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                    <tr style="background-color: #24a9e0;">
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Date</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Day</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Rate</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Store</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Location</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>  
                    </tr>';
                    foreach ($weekLiveJobArray as $key => $value) {
                      $weeklyReminderMsg .='
                        <tr> 
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['store'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['location'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
                        </tr>';
                    }
                    $weeklyReminderMsg .='  </table>';
                  }
                  if (!empty($weekPrivateJobArray)) {
                    $weeklyReminderMsg .= '
                      <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Private Job </h3>
                    <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; margin-bottom:20px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                    <tr style="background-color: #f2f2f2;">
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Date</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Day</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Rate</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Name</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Location</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>  
                    </tr>';
                    foreach ($weekPrivateJobArray as $key => $value) {
                      $weeklyReminderMsg .='
                        <tr> 
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['name'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['location'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
                        </tr>';
                    }
                    $weeklyReminderMsg .='  </table>';
                  }



                  $weeklyReminderMsg .= '<p>Locumkit Tip: You can always edit your requirements from the calendar (i.e. rate requirement, distance willing to travel), which can increase your chances of obtaining additional bookings.</p></div>'.$footer;      
                  //echo $weeklyReminderMsg;
                  /* Mail Send to Freelancer */

                  //sms content start
                  $livejob = count($weekLiveJobArray);
                  $privatejob = count($weekPrivateJobArray);
                  if($livejob > 0){ $sms1 = $livejob." Live Job "; }else{ $sms1 = ''; }
                  if($privatejob > 0){ $sms2 = $privatejob." Private Job "; }else{ $sms2 = ''; }              
                    $smsContent = "Hi ".$freName." upcoming bookings for Job in this week are : ".$sms1." ".$sms2.". ";
                  //sms content end
                  //echo $weeklyReminderMsg;

                  try {
                      // $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
                      // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                      // $mailFre->setFrom($adminEmail, 'Locumkit');
                      // $mailFre->addTo($freEmail);
                      // $mailFre->setSubject(' LocumKit week commencing '.$weekStartDate.' bookings');
                      // $mailFre->send();

                      $sub = 'LocumKit week commencing '.$weekStartDate.' bookings';
                      $this->sendSMTPMail($weeklyReminderMsg,$adminEmail,$freEmail,$to_name,$sub);
                      $this->flashMessenger()->addSuccessMessage('Message sent');


                      //send sms start
                       /*   $jobsmsController = new JobsmsController();
                          $jobsmsController->sendweeklyReminderToFreelancerSms($freId,$smsContent);*/
                         
                      //send sms end

                  } catch (Exception $e) {
                      //$this->flashMessenger()->addErrorMessage($e->getMessage());
                  }   
                }else{
                  $weeklyReminderMsg = $header;
                  $weeklyReminderMsg .= '<div style="padding: 25px 50px 5px; text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">';
                  $weeklyReminderMsg .= '<p>Hi '.$freName.'</p><p>You currently have no bookings for this week.</p>';
                  $weeklyReminderMsg .= '<p>We have noticed increasing your distance or reducing your rate can help you get more job notifications and subsequently more job bookings. You can always contact us at LocumKit and we might be able to help you, if you still find yourself a bit light on job bookings. </p>';
                  $weeklyReminderMsg .= '<p>If you do have bookings, please update your calender so that you can benefit from automatic reminders and financial updates. </p>';
                  $weeklyReminderMsg .= '</div>'.$footer;
                  
                  try {
                    // $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($freEmail);
                    // $mailFre->setSubject(' LocumKit week commencing '.$weekStartDate.' bookings');
                    // $mailFre->send();
                    

                    $sub = 'LocumKit week commencing '.$weekStartDate.' bookings';
                    $this->sendSMTPMail($weeklyReminderMsg,$adminEmail,$freEmail,$to_name,$sub);
                    $this->flashMessenger()->addSuccessMessage('Message sent');

                    //send sms start
                    /*   $jobsmsController = new JobsmsController();
                        $jobsmsController->sendweeklyReminderToFreelancerSms($freId,$smsContent);*/
                       
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

        /* Weekly Reminder Notification To Employer */
        public function sendweeklyReminderToEmployer($adapter,$weekStartDate,$weekEndDate)
        {
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl'); 
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
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
                        'job_id'  => $value['job_id'],
                        'job_date'  => $value['job_date'],
                        'job_day' => date('l',$jobDate),
                        'job_rate'  => $this->getCurrencySymbol().number_format($value['job_rate'],2),
                        'store'   => $this->getStoreInfo($adapter,$value['store_id']),
                        'location'  => $value['job_address'].', '.$value['job_region'].', '.$value['job_zip'],
                        'view'    => $serverUrl().'/single-job?view='.$value['job_id']
                      ); 
                  }
                  
                }
                $empData  = $this->getEmployerInfo($empId,$adapter);
                if (!empty($empData)) {
                  $empEmail   = $empData['email'];
                  $empName  = $empData['firstname'].' '.$empData['lastname'];
                }
                if ($i > 0) {
                  $weeklyReminderMsg = $header;
                  $weeklyReminderMsg .= '<div style="padding: 25px 50px 5px; text-align: left;">';
                  $weeklyReminderMsg .= 'Hi '.$empName.'';
                  if (!empty($weekLiveJobArray)) {
                    $weeklyReminderMsg .= '
                      <p>Please see below the upcoming booking(s) you have for the next week:</p><br/>
                      <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;padding:5px; margin-bottom:20px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                      <tr style="background-color: #24a9e0;">
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Date</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Day</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Rate</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Locum</th>
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Store</th>
                      <!--<th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px;">Location</th>-->
                      <th style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; width: 200px; text-align:center;">Action</th>  
                    </tr>';
                    foreach ($weekLiveJobArray as $key => $value) {
                      $freelancer_name="N/A";
                      $sql_fre        = "SELECT firstname,lastname,id FROM user WHERE id IN ( SELECT f_id FROM job_action WHERE job_id = '".$value['job_id']."' AND action = '3')";
                      $get_fre_info   = $adapter->query($sql_fre, $adapter::QUERY_MODE_EXECUTE);
                      $fre_info       = $get_fre_info->toArray();
                      $fre_info_count = $get_fre_info->count();
                      if($fre_info_count > 0){                          
                          $freelancer_name = $fre_info[0]['firstname'].' '.$fre_info[0]['lastname'];
                      }else{
                          $sql_fre = "SELECT p_name,p_email,p_uid FROM private_user WHERE p_uid IN ( SELECT puid FROM private_user_job_action WHERE j_id = '".$value['job_id']."' AND status = '3')";
                          $get_fre_info = $adapter->query($sql_fre, $adapter::QUERY_MODE_EXECUTE);
                          $fre_info = $get_fre_info->toArray();
                          $freelancer_name = $fre_info[0]['p_name'].' (private locum)';
                      }


                      $weeklyReminderMsg .='
                        <tr> 
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_date'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_day'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['job_rate'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$freelancer_name.'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px;">'.$value['store'].'</td>
                          <td style=" border: 1px solid #5dc1ea;  text-align:left;  padding:5px; text-align:center;"><a href="'.$value['view'].'">view</a></td>
                        </tr>';
                    }
                    $weeklyReminderMsg .='  </table>';
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
                    // $mailFre = new \Gc\Mail('utf-8', $weeklyReminderMsg);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($empEmail);
                    // $mailFre->setSubject(' LocumKit week commencing '.$weekStartDate.' bookings');
                    // $mailFre->send();

                    $sub = 'LocumKit week commencing '.$weekStartDate.' bookings';
                    $this->sendSMTPMail($weeklyReminderMsg,$adminEmail,$empEmail,$to_name,$sub);
                    $this->flashMessenger()->addSuccessMessage('Message sent');
                  
                    //send sms start
                    /*   $jobsmsController = new JobsmsController();
                      $jobsmsController->sendweeklyReminderToEmployerSms($empId,$smsContent);*/
                     
                    //send sms end
                  
                  
                  } catch (Exception $e) {
                      //$this->flashMessenger()->addErrorMessage($e->getMessage());
                  } 
                }else{

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
          $notifyController = new NotificationController();
          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId' AND job_status = '4'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobId    = $value['job_id'];
            $eId    = $value['e_id'];
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $subject_jobRate  = $this->getCurrencyCode().number_format($value['job_rate'],2);
            $jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
            $storeName  = $this->getStoreInfo($adapter,$value['store_id']);
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
      
            $header   = $this->mailHeader();
              $footer   = $this->mailFooter();
              $mail_css   = $header;
            $job_info ='<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
            </tr>           
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job location</th>
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

            $remider_on = ($notifyDay > 1) ? 'on job day.': 'tomorrow';
            $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello '.$freName.',</p>
              <p>This is a courteous reminder of the upcoming booking:</p>
              '.$job_info.'
              <br/>
              <p>To view full details of the booking, <a href="'.$serverUrl().'/single-job?view='.$jobId.'">click here </a></p>
              <p>If for whatever reason you can not make it, please cancel by <a href="'.$serverUrl().'/cancel-job?e='.$jobId.'">clicking here</a></p>
              <p>If you have signed up to our finance packages then all your income and expenses will be automatically recorded/triggered. '.$remider_on.'</p>
              </div>'.$footer.'</body></html>';


            $massageEmp = $mail_css.'
              <div style="padding: 25px 50px 5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hello '.$empName.',</p>
              <p>This is a courteous reminder of the upcoming booking:</p>
            '.$job_info.'
            <br/>
            <p>To view full details of the booking, <a href="'.$serverUrl().'/single-job?view='.$jobId.'">click here.</a></p>
            <p>If for whatever reason you can not make it, please cancel by <a href="'.$serverUrl().'/cancel-job?e='.$jobId.'">clicking here</a></p>
            </div>'.$footer.'</body></html>';

          if ($notifyDay > 1) {
            $reminderSubject = 'Job reminder';
            $appNotificationSub = $reminderSubject;
          }else{
            $reminderSubject = 'Job reminder for - TOMORROW';
            $appNotificationSub = 'Job reminder (next day)';
          }
          /* Mail Send to freelancer */
          try {
              if($freEmail){
                  // $mailFre = new \Gc\Mail('utf-8', $massageFre);
                  // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                  // $mailFre->setFrom($adminEmail, 'Locumkit');
                  // $mailFre->addTo($freEmail);
                  // $mailFre->setSubject($reminderSubject);
                  // $mailFre->send();

                  $mailController->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$reminderSubject);
                  $this->flashMessenger()->addSuccessMessage('Message sent');
           
        
                $smsLinksArray =  array('detail' => $serverUrl().'/single-job?view='.$jobId , 'cancel' =>$serverUrl().'/cancel-job?e='.$jobId); ;
                $jobsmsController = new JobsmsController();
                $jobsmsController->sendReminderSms($jobFid,$jobId,$smsLinksArray); 

                //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($jobId,$message="Job Ref: ".$jobId.", Location: ".$jobAddress.", Rate: ".$subject_jobRate.". Open this message to view full details.",$title=$appNotificationSub,$jobFid,$types="");
           
              }
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
          /* Mail Send to employer */
          try {
              if($empEmail){
                   // $mailFre = new \Gc\Mail('utf-8', $massageEmp);
                   // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                   // $mailFre->setFrom($adminEmail, 'Locumkit');
                   // $mailFre->addTo($empEmail);
                   // $mailFre->setSubject($reminderSubject);
                   // $mailFre->send();
                   $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$reminderSubject);
                   $this->flashMessenger()->addSuccessMessage('Message sent');

                  $smsLinksArray =  array('detail' => $serverUrl().'/single-job?view='.$jobId , 'cancel' =>$serverUrl().'/cancel-job?e='.$jobId); ;
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->sendReminderSms($eId,$jobId,$smsLinksArray); 

                    //Mobile APP Notification
                    $mobile_invitation_send = $notifyController->notification($jobId,$message="Job Ref: ".$jobId.", Locum: ".$freName.", Rate: ".$subject_jobRate.". Open this message to view full details.",$title=$appNotificationSub,$eId,$types="");
        
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
          $notifyController = new NotificationController();

          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'"; 
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobId  = $value['job_id'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
            $storeId  = $value['store_id'];
          }

          /* Get freelancer e-mail id*/
          $sqlFreEmail = "SELECT email,firstname,lastname from user WHERE id='$jobFid'";  
          $freEmailData = $adapter->query($sqlFreEmail, $adapter::QUERY_MODE_EXECUTE);
          $freEmails = $freEmailData->current();  
          $freEmail = $freEmails['email'];
          $freName = $freEmails['firstname'].' '.$freEmails['lastname'];

          $header   = $this->mailHeader();
              $footer   = $this->mailFooter();
              $mail_css   = $header;
          $job_info ='
            <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Job Information </h3>
            <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
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
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
            </tr>
          </table>
          </div>'.$footer.'</body></html>';
          $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$freName.',</p>
            <h3 style="font-weight: normal;">Please confirm arrival for the below booking:<!--Please can you confirm your arrival to work today---></h3>
            <p>'.$yesBtnLink.'</p>
            <!----<p>The details of the work are as per below:</p>--->
            '.$job_info;
          /* Mail Send to freelancer */
            try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('LocumKit confirmation of arrival');
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,'LocumKit confirmation of arrival');
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              //Mobile APP Notification
              $mobile_invitation_send = $notifyController->notification($jobId,$message="Please open this message to confirm your arival for the day.",$title='Arrival confirmation',$jobFid,$types="attendance");

          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
        }

        /* On Day Notification mail To freelancer */
        public function sendOnDayNotificationToEmployer($jobId,$jobFid,$jobEid,$yesBtnLink,$adapter)
        {
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          $notifyController = new NotificationController();

          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'"; 
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobId    = $value['job_id'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
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

          $header   = $this->mailHeader();
              $footer   = $this->mailFooter();
              $mail_css   = $header;
          $job_info ='
            <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Job Information </h3>
            <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
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
            <div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$EmpName.',</p>
            <h3 style="font-weight: normal;">This email is sent to you to confirm that your locum for today has just confirmed arrival for today. </h3>
            
            '.$job_info;
          /* Mail Send to freelancer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageEmp);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($EmpEmail);
              // $mailFre->setSubject('LocumKit confirmation of arrival');
              // $mailFre->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');  

              $this->sendSMTPMail($massageEmp,$adminEmail,$EmpEmail,$to_name,'LocumKit confirmation of arrival');     
        
          //send sms start
            $jobsmsController = new JobsmsController();
            $jobsmsController->sendOnDayNotificationToEmployerSms($jobEid,$jobId);
          //send sms end

          //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($jobId,$message="The locum for the day has just confirmed their attendance.",$title='LocumKit confirmation of arrival.',$jobEid,$types="");
        
        
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
        }



        /* Feedback Notification mail */
        public function sendFeedbackNotification($job_id,$f_id,$e_id,$adapter,$feedback_link_fre,$feedback_link_emp){
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
          $adminEmail = $configGet->get('mail_from');
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($f_id,$adapter);
          $notifyController = new NotificationController();
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $empData  = $this->getEmployerInfo($e_id,$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
            $empName  = $empData['firstname'].' '.$empData['lastname'];
          }
          $jobData  = $this->getJobInfo($job_id,$adapter);
          $massageFre = $header;
          $massageFre .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hi '.$freName.',</p>';
          $massageFre .= '<p>Hope you are well.</p>';
          $massageFre .= '<p>We are emailing you in regards to the following job.</p>';
          $massageFre .= $jobData;
          $massageFre .= '<p>We would like you to leave feedback for the employer about your day there.</p>';
          $massageFre .= '<p>This would help other Locums and also help improve clinical competition amongst users.</p>';
          $massageFre .= '<p>Please click here on below button to submit your valuable feedback.</p><br/>';
          $massageFre .= '<p>'.$feedback_link_fre.'</p>';                   
          $massageFre .= '</div>';
          $massageFre .= $footer;
          

          /* $massageEmp = $header;
          $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hi <b>'.$empName.'</b>,</p>';
          $massageEmp .= '<p>Hope you are well.</p>';
          $massageEmp .= '<p>We would now like you to leave feedback for the freelancer on your day there.</p>';
          $massageEmp .= '<p>This would help other Employers and also help improve clinical competition amongst users.</p>';
          $massageEmp .= '<p>Please click here on below button to submit your valuable feedback.</p>';
          $massageEmp .= '<p>'.$feedback_link_emp.'</p>';
          $massageEmp .= '</div>';
          $massageEmp .= $footer; */
          $encypt = new Endecrypt();
          $bke_id = $encypt->encryptIt($e_id);
          $bkf_id = $encypt->encryptIt($f_id);
           $massageEmp = $header;
            $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$empName.',</p>';
            $massageEmp .= '<p>Hope you are well.</p>';
            $massageEmp .= '<p>We are emailing you in regards to the following job.</p>';
            $massageEmp .= $jobData;
            $massageEmp .= '<p>Please could you leave feedback for the locum on their day with you.</p>';
            $massageEmp .= '<p>This would help other employers when looking to hire locums.</p>';
            $massageEmp .= '<p>Please click below to submit your feedback.</p>';
            $massageEmp .= '<p>'.$feedback_link_emp.'</p>';
            /*$massageEmp .= '<p>Following is your job information.</p>';
            $massageEmp .= $jobData;*/
            $massageEmp .= '<p>Want to block this locum, please <a href="'.$serverUrl().'/block-user?eid='.$bke_id.'&fid='.$bkf_id.'">click here.</a></p>';
            $massageEmp .= '</div>';
            $massageEmp .= $footer;

            /* Fetch record of job */
            $sqlJob = "SELECT job_date from job_post WHERE job_id = '$job_id'"; 
            $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
            $job_array = $jobView->toArray();
            foreach ($job_array as $key => $value) {
              $jobDate  = $value['job_date'];
            }

            //echo $massageEmp;
            /* Mail Send to freelancer */
            try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Feedback request for #'.$job_id);
              // $mailFre->send();

              $sub = 'Feedback request for #'.$job_id;
              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($job_id,$message="Please leave feedback for work carried out on Date :".$jobDate.'. Open this message to leave the feedback.',$title='Feedback request',$f_id,$types="feedbackRequest");
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            } 
  
            /*Mail Send to employer*/
            try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Feedback request for #'.$job_id);
              // $mailEmp->send();

              $sub = 'Feedback request for #'.$job_id;
              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              $mobile_invitation_send = $notifyController->notification($job_id,$message="Please leave feedback for work carried out on Date :".$jobDate.'. Open this message to leave the feedback.',$title='Feedback request',$e_id,$types="feedbackRequest");

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
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($feedbackArray['fre_id'],$adapter);
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
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
            $jobId    = $value['job_id'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
            $storeId  = $value['store_id'];
          }
          
          $job_info ='
          <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
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
            $displaystars = $this->calculatestars($feedbackData['qusRate']) ;
            $feedbackQusAns .= '
              <div style="border: 1px solid #cfcfcf; padding: 10px;background: #eee;border-radius: 3px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                <p style="font-style: italic;font-weight: bold;padding:0 0 10px;">Qus '.$i.') '.$feedbackData['qus'].'</p>
                <p style="font-weight: bold;padding:0 0 10px;">Ans : '.$displaystars.' '.$feedbackData['qusRate'].' star(s) </p>
              </div>
              <div style="height:10px"></div>
            ';
            $i++;
          }

          $massageFre = $header;
          $massageFre .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$freName.',</p>';
          $massageFre .= '<p>We would like to inform you that you have received feedback for the following booking:</p>';
          $massageFre .= $job_info;
          $massageFre .= '<p>&nbsp;</p>';
          $massageFre .= '<p>Please see below how the employer has left feedback for you</p>';
          $massageFre .= $feedbackQusAns;

          if(isset($feedbackArray['comments']) && $feedbackArray['comments'] != ''){
            $massageFre .= '<p>Feedback Comment : '.$feedbackArray['comments'].'</p>';
          }

          $massageFre .= '<p>&nbsp;</p>';
          $massageFre .= '<p style="float: left;padding: 0 10px 0 0;"><b>Average star rating: '.$this->calculatestarsaverage($averageRate).'</b></p>';
          $massageFre .= '<p>&nbsp;</p>';
          $massageFre .= '<p>If you feel this feedback is not a true reflection of your performance, please <a href="'.$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($freId).'">click here</a> and the LocumKit Team will look into the matter. If you are happy with the feedback, then it will be automatically posted on your profile in the next 48 hours </p>';

          $massageFre .= '<p>If you are happy with this then this feedback shall automatically be posted against your profile within the next 48 hours.</p>';
          $massageFre .= '</div>';
          $massageFre .= $footer;
          $currentDate = date('Y-m-d');
          /* Mail Send to freelancer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Feedback received for '.date('d/m/Y', strtotime($currentDate .' -1 days')));
              // $mailFre->send();

              $sub = 'Feedback received for '.date('d/m/Y', strtotime($currentDate .' -1 days'));
              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');


                //send sms start
                $link = $serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($freId) ;
                $jobsmsController = new JobsmsController();
                $jobsmsController->recievedFeedbackFreelancerNotificationSms($freId,$jobId,$link);
                //send sms end

                //Mobile APP Notification
                $notifyController = new NotificationController();
                $mobile_invitation_send = $notifyController->notification($feedbackId,$message="You have recieved feedback for work carried out on date:".$jobDate.'. Open this message to view the results.',$title='Feedback recieved',$freId,$types="feedbackRecieved");


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
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $empData  = $this->getEmployerInfo($feedbackArray['emp_id'],$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          }
          $freData  = $this->getFreelancerInfo($feedbackArray['fre_id'],$adapter);
          if (!empty($freData)) {
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $empId = $feedbackArray['emp_id'];
          $jobId = $feedbackArray['j_id'];
          $freId = $feedbackArray['fre_id'];
          $averageRate = $feedbackArray['rating'];
          $feedbackArray = unserialize($feedbackArray['feedback']);
          
          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId'"; 
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobId    = $value['job_id'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];           
          }
          
          $job_info ='
          <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
          <tr style="background-color: #f2f2f2;">
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job ID</th>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;">#'.$jobId.'</td>
          </tr>
          <tr> 
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
          </tr>
          
          <tr style="background-color: #f2f2f2;">
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Locum</th>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
          </tr>
          <tr style="background-color: #f2f2f2;">
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
          </tr>
          </table>';
          $feedbackQusAns = '';
          $i = 1;
          foreach ($feedbackArray as $key => $feedbackData) {
            $displaystars = $this->calculatestars($feedbackData['qusRate']) ;

            $feedbackQusAns .= '
              <div style="border: 1px solid #cfcfcf; padding: 10px;background: #eee;border-radius: 3px;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                <p style="font-style: italic;font-weight: bold;padding:0 0 10px;">Qus '.$i.') '.$feedbackData['qus'].'</p>
                <p style="font-weight: bold;padding:0 0 10px;">Ans : '.$displaystars.' '.$feedbackData['qusRate'].' star(s)  </p>
              </div>
              <div style="height:10px"></div>
            ';
            $i++;
          }

          $massageEmp = $header;
          $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
              <p>Hi '.$empName.',</p>';
          $massageEmp .= '<p>We would like to inform you that you have received feedback for the following booking:</p>';
          $massageEmp .= $job_info;
          $massageEmp .= '<p>&nbsp;</p>';
          $massageEmp .= '<p>Below you can see the details of the feedback:</p>';
          $massageEmp .= $feedbackQusAns;

                if(isset($feedbackArray['comments']) && $feedbackArray['comments'] != ''){
                 $massageFre .= '<p>Feedback Comment : '.$feedbackArray['comments'].'</p>';
                }

          $massageEmp .= '<p>&nbsp;</p>';
          $massageEmp .= '<p style="float: left;padding: 0 10px 0 0;"><b>Average star rating: '.$this->calculatestarsaverage($averageRate).'</b></p>';
          $massageEmp .= '<p>&nbsp;</p>';
          $massageEmp .= '<p>If you feel this feedback is not a true reflection of your performance then please <a href="'.$serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($empId).'">click here</a>, so we at LocumKit can look into this. </p>';
          //$massageEmp .= 'To avoid using this locum in the future, please <a href="'.$serverUrl().'/block-user?eid='.$encypt->encryptIt($empId).'&fid='.$encypt->encryptIt($freId).'">click here</a>';
          $massageEmp .= '<p>If you are happy with this then this feedback shall automatically be posted against your profile within the next 48 hours.</p>';
          $massageEmp .= '</div>';
          $massageEmp .= $footer;
          $currentDate = date('Y-m-d');
          /* Mail Send to freelancer */
          try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Feedback received for '.date('d/m/Y', strtotime($currentDate .' -1 days')));
              // $mailEmp->send();

              $sub = 'Feedback received for '.date('d/m/Y', strtotime($currentDate .' -1 days'));
              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$sub);

              //$this->flashMessenger()->addSuccessMessage('Message sent');

                //send sms start
                $link = $serverUrl().'/feedback-dispute?feedbackId='.$encypt->encryptIt($feedbackId).'&u='.$encypt->encryptIt($empId) ;
                $jobsmsController = new JobsmsController();
                $jobsmsController->recievedFeedbackEmployerNotificationSms($empId,$jobId,$link);
                //send sms end

                //Mobile APP Notification
                $notifyController = new NotificationController();
                $mobile_invitation_send = $notifyController->notification($feedbackId,$message="You have recieved feedback for work on date:".$jobDate.'. Open this message to view the results.',$title='Feedback recieved',$empId,$types="feedbackRecieved");


          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
        }

        /* Send alert of feedback after 1 week if user not submitted the feedback*/
        public function sendFeedbackNotificationOneWeekAlert($job_id,$u_id,$feedback_link,$user_type, $adapter)
        {         
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$job_id'"; 
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {
              $jobDate  = $value['job_date'];
          }
          if ($user_type == 2) {
            $freData  = $this->getFreelancerInfo($u_id,$adapter);
            if (!empty($freData)) {
              $freEmail   = $freData['email'];
              $freName  = $freData['firstname'].' '.$freData['lastname'];
            }

            $massageFre = $header;
            $massageFre .= '<div style="padding: 25px 50px 5px;text-align: left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                <p>Hi <b>'.$freName.'</b>,</p>';
            $massageFre .= '<p>This is a reminder request for you to leave feedback for the following </p>';
            $massageFre .= $jobData;
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
                // $mailFre = new \Gc\Mail('utf-8', $massageFre);
                // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mailFre->setFrom($adminEmail, 'Locumkit');
                // $mailFre->addTo($freEmail);
                // $mailFre->setSubject('Feedback reminder for#'.$job_id);
                // $mailFre->send();

                $sub = 'Feedback reminder for#'.$job_id;
                $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$sub);
                //$this->flashMessenger()->addSuccessMessage('Message sent');

                //Mobile APP Notification
                $notifyController = new NotificationController();
                  $mobile_invitation_send = $notifyController->notification($job_id,$message="Please leave feedback for work carried out on date:".$jobDate.'. Open this message to leave the feedback.',$title='Feedback reminder',$u_id,$types="feedbackRequest");

            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            } 
          }

          if ($user_type == 3) {            
            $empData  = $this->getEmployerInfo($u_id,$adapter);
            $jobData  = $this->getJobInfo($job_id,$adapter);
            if (!empty($empData)) {
              $empEmail   = $empData['email'];
              $empName  = $empData['firstname'].' '.$empData['lastname'];
            }
            $massageEmp = $header;
            $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;">
                <p>Hi '.$empName.',</p>';
            $massageEmp .= '<p>This is a reminder request for you to leave feedback for the following </p>';
            $massageEmp .= $jobData;
            $massageEmp .= '<p>Your feedback is would be highly valuable as it would allow for:</p>';
            $massageEmp .= '<p>1) Self reflection for the locum in question.</p>';
            $massageEmp .= '<p>2) Increase in clinical competition amongst locum.</p>';
            $massageEmp .= '<p>3) Allow your fellow employers to determine the locums competency.</p>';
            $massageEmp .= '<p>Please click below to submit your feedback</p>';
            $massageEmp .= '<p>'.$feedback_link.'</p>';
            /*$massageEmp .= '<p>Following is your job information.</p>';
            $massageEmp .= $jobData;*/
            $massageEmp .= '</div>';
            $massageEmp .= $footer;

            /*Mail Send to employer*/
            try {
                // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
                // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mailEmp->setFrom($adminEmail, 'Locumkit');
                // $mailEmp->addTo($empEmail);
                // $mailEmp->setSubject('Feedback reminder for#'.$job_id);
                // $mailEmp->send();

                $sub = 'Feedback reminder for#'.$job_id;
                $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$sub);

                //$this->flashMessenger()->addSuccessMessage('Message sent');

                //Mobile APP Notification
                $notifyController = new NotificationController();
                  $mobile_invitation_send = $notifyController->notification($job_id,$message="Please leave feedback for work carried out on date:".$jobDate.'. Open this message to leave the feedback.',$title='Feedback reminder',$u_id,$types="feedbackRequest");

            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
          }
        }


        /* Send job apply email to Private user */
        public function sendApplyMailToPrivateUser($puid,$cjid,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $privateUserData  = $this->getPrivateUserInfo($puid,$adapter);
          if (!empty($privateUserData)) {
            $privateUserEmail   = $privateUserData['p_email'];
          $privateUserName  = $privateUserData['p_name'];
          }
          $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->current();
          $empData  = $this->getEmployerInfo($job['e_id'],$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          }
          $jobData  = $this->getJobInfo($cjid,$adapter);
          $massagePrivateUser = $header;
          $massagePrivateUser .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello <b>'.$privateUserName.'</b>,</p>';
          $massagePrivateUser .= '<p>You have succesfully apply for the job. </p>';
          $massagePrivateUser .= '<p>Following is your job information.</p>';
          $massagePrivateUser .= $jobData;
          $massagePrivateUser .= '</div>';
          $massagePrivateUser .= $footer;
          

          $massageEmp = $header;
          $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;">
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
              // $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($privateUserEmail);
              // $mailFre->setSubject('Job apply notification');
              // $mailFre->send();

              $this->sendSMTPMail($massagePrivateUser,$adminEmail,$privateUserEmail,$to_name,'Job apply notification');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
  
          /* Mail Send to employer */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageEmp);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($empEmail);
              // $mailFre->setSubject('Job apply notification');
              // $mailFre->send();
              $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,'Job apply notification');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
        }

        /* Send job accept email to Private user */
        public function sendAcceptMailToPrivateUser($puid,$cjid,$adapter)
        {
          $functionsController = new FunctionsController();
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $privateUserData  = $this->getPrivateUserInfo($puid,$adapter);
          $notifyController = new NotificationController();

          if (!empty($privateUserData)) {
            $privateUserEmail   = $privateUserData['p_email'];
            $privateUserName  = $privateUserData['p_name'];
            $privateUserID      = $privateUserData['p_uid'];
          }
          $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->current();
          $empData  = $this->getEmployerInfo($job['e_id'],$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
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
          $emp_store_address=$resultset_st00['emp_store_address'].', '.$resultset_st00['emp_store_region'].',
         '.$resultset_st00['emp_store_zip'];
          $emp_store_region=$resultset_st00['emp_store_region'];
          $emp_store_zip=$resultset_st00['emp_store_zip']; 
          $startTime = unserialize( $resultset_st00['store_start_time']);
          $endTime = unserialize( $resultset_st00['store_end_time']);
          $lunchTime = unserialize( $resultset_st00['store_lunch_time']);
          $new_date=str_replace("/","-",$job['job_date']);
          $job_day =  date('l', strtotime($new_date));
          
          //Store timing for posted day 
          $store_start_time = $functionsController->getTimeOfDay($startTime,$job_day);
          $store_end_time = $functionsController->getTimeOfDay($endTime,$job_day);
          $store_lunch_time = $functionsController->getTimeOfDay($lunchTime,$job_day).':00 (Min)';
          
          $job_info_admin ='
          <h3 style="text-align:left;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;width:100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (Key Details)</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_date'].'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Daily rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$job['job_rate'].'.00'.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store contact details</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$store_contact_details.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Address</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$emp_store_address.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Additional booking info:</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; color:red; font-weight:bold;">'.$job['job_post_desc'].'</td>
            </tr> 
          </table>
           <br>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; width:100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Booking confirmation - details of locum booked</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$privateUserName.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;"> <b>Private Locum </b> </td>
            </tr>
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2"> Locumkit booking confirmation (additional information)</td>
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
          $freelancer_terms = $this->locum_email_terms('#92D000');
          $job_info_free =' 
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left; padding:5px;width:100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Locumkit booking confirmation (Key Details)</td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;width:100%;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Locumkit booking confirmation (additional information) </td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; width:100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Locumkit booking confirmation (Key Details)</td>
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
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px; width:100%;font-weight: initial;color: #000;">
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Booking confirmation - details of locum booked</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$privateUserName.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Id</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;"> <b>Private Locum</b> </td>
            </tr>
            <tr style="background-color: #92D000;">
              <td style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;" colspan="2">  Locumkit booking confirmation (additional information)</td>
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
          $massagePrivateUser .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello '.$privateUserName.',</p>';
          $massagePrivateUser .= '<p>We would like to inform you that the following booking has been confirmed.</p>';
          $massagePrivateUser .= '<p>Please review the details below:</p>';
          $massagePrivateUser .= $job_info_free;
          $massagePrivateUser .= '</div>';
          $massagePrivateUser .= $footer;
          //echo $massagePrivateUser;

          $massageEmp = $header;
          $massageEmp .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello '.$empName.',</p>';
          $massageEmp .= '<p>We would like to inform you that the following booking has been confirmed for you: </p>';          
          $massageEmp .= $job_info_emp;
          $massageEmp .= '</div>';
          $massageEmp .= $footer;
      
          //Admin EMail
          $mailAdmin = $header;
            $mailAdmin .= '<div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello <b>Admin</b>,</p>';
          $mailAdmin .= '<p>Job has been accepted by the Private Locum . </p>';
          $mailAdmin .= '<p>Following is your job information.</p>';
          $mailAdmin .= $job_info_admin;
          $mailAdmin .= '</div>';
          $mailAdmin .= $footer;
      
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
          
          /* Mail Send to Private user */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massagePrivateUser);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($privateUserEmail);
              // $mailFre->setSubject('Booking Confirmation');
              // $mailFre->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');
              $this->sendSMTPMail($massagePrivateUser,$adminEmail,$privateUserEmail,$to_name,'Booking Confirmation');

          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
  
          /* Mail Send to employer */
          try {
              // $mailEmp = new \Gc\Mail('utf-8', $massageEmp);
              // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailEmp->setFrom($adminEmail, 'Locumkit');
              // $mailEmp->addTo($empEmail);
              // $mailEmp->setSubject('Booking Confirmation');
              // $mailEmp->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');

            $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,'Booking Confirmation');

              //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($cjid,$message="We are pleased to inform you that a locum has been found. Open this message to view full details.",$title='Job accepted',$job['e_id'],$types="");

          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
      
          /* Mail Send to employer */
          try {
              // $mailAdmin = new \Gc\Mail('utf-8', $mailAdmin);
              // $mailAdmin->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailAdmin->setFrom($adminEmail, 'Locumkit');
              // $mailAdmin->addTo($adminEmail);
              // $mailAdmin->setSubject('Booking Confirmation');
              // $mailAdmin->send();
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              $this->sendSMTPMail($mailAdmin,$adminEmail,$adminEmail,$to_name,'Booking Confirmation');

          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
        }

        /* Email notification to Expired package user */ 
        public function sendPackageExpiredMail($userId,$packageId,$packageExpiryDate,$btnLink,$day,$adapter){
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($userId,$adapter);
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
            $freName  = $freData['firstname'].' '.$freData['lastname'];          
            /* Get package name */
            $sqlPkgInfo = "SELECT * from user_acl_package WHERE id='$packageId'"; 
            $pkgInfoData = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
            $pkgInfo = $pkgInfoData->current();
            $pkgMessage = $header;
            $pkgMessage .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
            <p>Hello '.$freName.',</p>';
            $pkgMessage .= '<p>Your current package: <b style="text-transform:uppercase">'.$pkgInfo['name'].' ( '.$this->getCurrencySymbol().$pkgInfo['price'].' ) </b>.</p>';
            $pkgMessage .= '<p>A friendly reminder that your membership at Locumkit is about to expire in <b>'.$day.' day(s)</b>. To renew or update your membership log in to you profile and follow the suggested actions. </p>'; 
            $pkgMessage .= '<p>Your current plan is: <b style="text-transform:uppercase">'.$pkgInfo['name'].' ( '.$this->getCurrencySymbol().$pkgInfo['price'].' ) </b><br>Last renewal date:<br>Expiry date: 
            </p>'; 
            $pkgMessage .= '<p>Please note if you do not renew your account in time then you will no longer be able to access your details (booking information and your financials) or receive any further job notifications and/or reminders. </p>'; 
            $pkgMessage .= '<p>Click below button to upgrade your account.</p>';
            $pkgMessage .= '<p>'.$btnLink.'</p>';
            $pkgMessage .= '<p>If you have any questions, please do not hesitate to contact us and one of our team members will look to address your concern at the earliest convenience. </p>';
            $pkgMessage .= '</div>';
            $pkgMessage .= $footer;
            //echo $pkgMessage;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            //echo $pkgMessage;
            /* Mail Send to employer */ 
            try {
              // $mailFre = new \Gc\Mail('utf-8', $pkgMessage);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Locumkit Membership');
              // $mailFre->send();

              $this->sendSMTPMail($pkgMessage,$adminEmail,$freEmail,$to_name,'Locumkit Membership');
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              //send sms start
              $jobsmsController = new JobsmsController();
              $jobsmsController->sendPackageExpiredMailSms($userId);
              //send sms end  

              // App notification 
              $notifyController = new NotificationController();
              $mobile_invitation_send = $notifyController->notification('',$message='Your membership going to expired in '.$day.' day please upgrade ASAP.',$title='Membership Upgrade',$fid,$types="packageUpgrade");
            } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
          }
        }

        /* Expired membership */
        public function sendMembershipExpired($userId,$packageId, $adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($userId,$adapter);
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          
          /* Get package name */
          $sqlPkgInfo = "SELECT * from user_acl_package WHERE id='$packageId'"; 
          $pkgInfoData = $adapter->query($sqlPkgInfo, $adapter::QUERY_MODE_EXECUTE);
          $pkgInfo = $pkgInfoData->current();
          $pkgMessage = $header;
          $pkgMessage .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hello '.$freName.',</p>';
          $pkgMessage .= '<p>Sorry to say you that, your Locum account membership is expired today. You can not access website any more. </p>';  
          $pkgMessage .= '<p>To resume the account please renew the membership by login to your account.</p>';  
          $pkgMessage .= '</div>';
          $pkgMessage .= $footer;
          //echo $pkgMessage;
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          /* Mail Send to employer */ 
          try {
              // $mailFre = new \Gc\Mail('utf-8', $pkgMessage);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('User Account Membership Expired');
              // $mailFre->send();

              $this->sendSMTPMail($pkgMessage,$adminEmail,$freEmail,$to_name,'User Account Membership Expired');
              //$this->flashMessenger()->addSuccessMessage('Message sent');

            
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
             }
        }

        public function sendCloseJobNotification($jobId,$jobEid,$viewJobLink,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $empData  = $this->getEmployerInfo($jobEid,$adapter);
          $notifyController = new NotificationController();
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
            $closeJobMsg = $header;
            $closeJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;"><p>Hello <b>'.$empName.'</b>,</p>';
            $closeJobMsg .= '<p>As no successful match was found, job no #'.$jobId.' is now closed.</p>';  
          $closeJobMsg .= '<p>If you find this is a regular occurrence, then please contact us and one of our assistance can look into why you could be struggling to obtain locums. </p>';
    
            $closeJobMsg .= '<p>Click below button to view your job.</p>';
            $closeJobMsg .= $viewJobLink;
            //$closeJobMsg .= '<p>&nbsp</p>';
            $closeJobMsg .= '</div>';
            $closeJobMsg .= $footer;


            $closeJobMsgAdmin = $header;
            $closeJobMsgAdmin .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;"><p>Hello <b>Admin</b>,</p>';
            $closeJobMsgAdmin .= '<p>The following employers job has just expired.</p>';
            $closeJobMsgAdmin .= $this->getExpiredJobInfo($jobId,$adapter);           
            $closeJobMsgAdmin .= '</div>';
            $closeJobMsgAdmin .= $footer;

            //echo $closeJobMsgAdmin;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            /* Mail Send to employer */ 
            try {
                // $mailEmp = new \Gc\Mail('utf-8', $closeJobMsg);
                // $mailEmp->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mailEmp->setFrom($adminEmail, 'Locumkit');
                // $mailEmp->addTo($empEmail);
                // $mailEmp->setSubject('Job Post Expired');
                // $mailEmp->send();

                $this->sendSMTPMail($closeJobMsg,$adminEmail,$empEmail,$to_name,'Job Post Expired');
                //$this->flashMessenger()->addSuccessMessage('Message sent');

                //Mobile APP Notification
                  $mobile_invitation_send = $notifyController->notification($jobId,$message="One of your job has just expired.",$title='Job Expired',$jobEid,$types="");


            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }

            /* Mail Send to admin */ 
            try {
                // $mailAdm = new \Gc\Mail('utf-8', $closeJobMsgAdmin);
                // $mailAdm->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mailAdm->setFrom($adminEmail, 'Locumkit');
                // $mailAdm->addTo($adminEmail);
                // $mailAdm->setSubject('LocumKit job expired');
                // $mailAdm->send();

                $this->sendSMTPMail($closeJobMsgAdmin,$adminEmail,$adminEmail,$to_name,'LocumKit job expired');
                //$this->flashMessenger()->addSuccessMessage('Message sent');
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        }

        /* Private Job reminder notification mail */
        public function sendPrivateJobReminder($jobPvid,$jobFid,$pEmpName,$pEmpEmail,$pJobTitle,$pJobRate,$pJobDate,$notifyDay,$pJobLocation,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($jobFid,$adapter);
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $pJobRate = $this->getCurrencySymbol().$pJobRate;
          $privateJobMsg = $header;
          $privateJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hello '.$freName.',</p>';
          $privateJobMsg .= '<p>Please see below details of your upcoming booking for tomorrow:</p>';
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
              // $mailFre = new \Gc\Mail('utf-8', $privateJobMsg);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('Private Job reminder');
              // $mailFre->send();

              $this->sendSMTPMail($privateJobMsg,$adminEmail,$freEmail,$to_name,'Private Job reminder');
              //$this->flashMessenger()->addSuccessMessage('Message sent');

              //Mobile APP Notification
              $notifyController = new NotificationController();
              $mobile_invitation_send = $notifyController->notification($jobPvid,$message="This is just a courtesy reminder that you have a booking coming up for private job.",$title='Job reminder',$jobFid,$types="privateJobReminder");
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
        }

        /* Private Job On Day reminder notification mail */
        public function sendPrivateJobOnDayReminder($jobPvid,$jobFid,$pEmpName,$pEmpEmail,$pJobTitle,$pJobRate,$pJobDate,$pJobLocation,$yesBtnLink,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($jobFid,$adapter);
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $pJobRate = $this->getCurrencySymbol().$pJobRate;
          $privateJobMsg = $header;
          $privateJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hello <b>'.$freName.'</b>,</p>';
         //   $privateJobMsg .= '<h3 style="font-weight: normal;">Please can you confirm your arrival to work today</h3>';
        $privateJobMsg .= '<h3 style="font-weight: normal;">Please confirm your arrival at work for the booking stated below:</h3>';
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
              // $mailFre = new \Gc\Mail('utf-8', $privateJobMsg);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject('LocumKit private job confirmation of arrival');
              // $mailFre->send();

              $this->sendSMTPMail($privateJobMsg,$adminEmail,$freEmail,$to_name,'LocumKit private job confirmation of arrival');

              //$this->flashMessenger()->addSuccessMessage('Message sent');

              //Mobile APP Notification
              $notifyController = new NotificationController();
              $mobile_invitation_send = $notifyController->notification($jobPvid,$message='Please open this message to confirm your arival for the day.', $title='Arrival confirmation', $jobFid,$types='privateJobAttendance');

          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
        }

        //  Send notification to guest user to update profile
        public function sendUpdateProfileNotificationToFreelancer($Fid,$firstname,$lastname,$email,$serverUrl)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $profileNoteMsg = $header;
          $profileNoteMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hello <b>'.$firstname.' '.$lastname.'</b>,</p>';
          $profileNoteMsg .= '<h3 style="font-weight: normal;">Only One day left hurry up.</h3>';
          $profileNoteMsg .= '<p> Your profile going to suspended tomorrow so hurry up to complete your profile with us to use full service of website.</p>';
           $profileNoteMsg .= '<p> Please visit our website <a href="'.$serverUrl().'">click here</a>.';
          $profileNoteMsg .= '</div>';
          $profileNoteMsg .= $footer;
          
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          /* Mail Send to employer */ 
          try {
              // $mailFre = new \Gc\Mail('utf-8', $profileNoteMsg);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($email);
              // $mailFre->setSubject('Profile Reminder Notification');
              // $mailFre->send();
              $this->sendSMTPMail($profileNoteMsg,$adminEmail,$email,$to_name,'Profile Reminder Notification');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
        }

        //  Send Profile suspend notification
        public function sendProfileSuspendNotificationToFreelancer($Fid,$firstname,$lastname,$email,$serverUrl)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $profileNoteMsg = $header;
          $profileNoteMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hello <b>'.$firstname.' '.$lastname.'</b>,</p>';
          $profileNoteMsg .= '<h3 style="color:red">Profile suspened.</h3>';
          $profileNoteMsg .= '<p> Your guest profile has beed suspended from Locumkit.</p>';  
          $profileNoteMsg .= '</div>';
          $profileNoteMsg .= $footer;
          
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          /* Mail Send to employer */ 
          try {
              // $mailFre = new \Gc\Mail('utf-8', $profileNoteMsg);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($email);
              // $mailFre->setSubject('Profile suspended');
              // $mailFre->send();

              $this->sendSMTPMail($profileNoteMsg,$adminEmail,$email,$to_name,'Profile suspended');
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
          $notifyController = new NotificationController();

          /* Get user e-mail id*/
        $freData = $this->getFreelancerInfo($f_id,$adapter);
        if (!empty($freData)) {
            $freEmail   = $freData['email'];
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $job_info   = $this->getJobInfo($job_id,$adapter);
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $mail_css   = $header;

          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$job_id'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
          }
          if ($expired_note_type == 2) {
            $notification_sub = 'Freeze confirmation expiring';
            $mail_sub = '5 mins left for job to unfreeze';
            $mail_title = '<p style="line-height: 20px;"> We would like to inform you that the following job will be frozen for just another 5 minutes before it opens to other locums. </p><p style="line-height: 20px;"> Please review the details and apply now to confirm your booking for the job: </p>';
            $notification_title = "Job Ref: ".$job_id.", Date: ".$jobDate.", Location: ".$jobAddress.", Rate: ".$jobRate.". Note: Job is frozen for 5 minutes only.";
          }else{
            $mail_sub = $jobDate.' / '.$jobAddress.' / '.$jobRate;
            $mail_title = '<p>The following job (ref no '.$job_id.') is open again for all applicants.<b style="font-weight: normal;display: block;margin-bottom: 5px;">Please review the job details below:</b></p> ';
            $notification_title = "Job Ref: ".$job_id.", Date: ".$jobDate.", Location: ".$jobAddress.", Rate: ".$jobRate.". Open this message to view full details.";
            $notification_sub = 'Job invitation - unfrozen';
          }
        $user_info ='
          <p style="text-align:left;"> '.$mail_title.' </p>
          '.$job_info.'<br/>
          '.$link.'
          </div>'.$footer.'</body></html>';
          $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello '.$freName.',</p>
            '.$user_info;
        //echo $massageFre;
        /* Mail Send to Acivated user */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($freEmail);
              // $mailFre->setSubject($mail_sub);
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$mail_sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');
        
              //send sms start
              $jobsmsController = new JobsmsController();
              $jobsmsController->sendExpireFreezeNotificationSms($f_id,$job_id);
              //send sms end  
        
              //Mobile APP Notification
              $mobile_invitation_send = $notifyController->notification($job_id,$message=$notification_title,$title=$notification_sub,$f_id,$types="interest");
        
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
          
        }

        /* Send email to private locum about job freeze time expired */
        public function sendExpireFreezeNotificationPrivateLocum($job_id,$f_id,$locum_name,$locum_email,$adapter,$link)
        {
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from'); 

          $job_info   = $this->getJobInfo($job_id,$adapter);
        $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $mail_css   = $header;

          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$job_id'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
          }
          
          $mail_sub =$jobDate.' / '.$jobAddress.' / '.$jobRate;
          $mail_title = '<p>The following job (ref no '.$job_id.') is no longer frozen and is open again to all applicable locums.</p> ';

          
      $user_info ='
          <p style="text-align:left;"> '.$mail_title.' </p>
          '.$job_info.'<br/>
          '.$link.'
        </div>'.$footer.'</body></html>';
          $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;text-align: left;">
              <p>Hello '.$locum_name.',</p>
            '.$user_info;
        //echo $massageFre;
        /* Mail Send to Acivated user */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($locum_email);
              // $mailFre->setSubject($mail_sub);
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$locum_email,$to_name,$mail_sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');
        
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
          
        }

        public function getFreelancerInfo($jobFid,$adapter){
          /* Get freelancer e-mail id*/
          $sqlFreEmail = "SELECT email,firstname,lastname,user_acl_profession_id from user WHERE id='$jobFid'"; 
          $freEmailData = $adapter->query($sqlFreEmail, $adapter::QUERY_MODE_EXECUTE);
          return $freEmails = $freEmailData->current();
        }
        public function getEmployerInfo($jobEmpId,$adapter){
          /* Get record of employer */
          $sqlEmpUser = "SELECT firstname,lastname,email,user_acl_profession_id from user WHERE id = '$jobEmpId'";  
          $empUserDetails = $adapter->query($sqlEmpUser, $adapter::QUERY_MODE_EXECUTE);
          return $empUsers = $empUserDetails->current();
        }


        public function getUserProfessional($proid,$adapter){
            /* Get user profession*/
            $sqlPro = "SELECT name , description from user_acl_professional WHERE id='$proid'";
            $sqlProData = $adapter->query($sqlPro, $adapter::QUERY_MODE_EXECUTE);
            return $sqlProval = $sqlProData->current();
        }



        public function getPrivateUserInfo($puId,$adapter){
          /* Get record of employer */

          $sqlPriUser = "SELECT p_name,p_email from private_user WHERE p_uid = '$puId'";  
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
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            $jobDesc  = $value['job_post_desc'];
            $jobEmpId   = $value['e_id'];
          }
          $job_info = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job title</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobTitle.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job address</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job description</th>
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
            $jobId    = $value['job_id'];
            $jobEid   = $value['e_id'];
            $countFreToNotify = $this->gettheCountOfEmailSend($jobId,$adapter);
            $empData  = $this->getEmployerInfo($jobEid,$adapter);
            if (!empty($empData)) {
              $empEmail   = $empData['email'];
            $empName  = $empData['firstname'].' '.$empData['lastname'];
          }
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
            $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
            
          }
          $job_info = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
                <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Employer</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$empName.'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobDate.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job rate</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobRate.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Location</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$jobAddress.'</td>
            </tr>
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Job title</th>
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

        /* Send EMPLOYER WELCOME email to user after admin activation */
        public function sendActivationNotification($email,$firstname,$lastname,$login)
        {
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');

          /* Get user e-mail id*/
          $userEmail = $email;
          $userLogin = $login;
          $userName  = $firstname.' '.$lastname;

          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $mail_css   = $header;
          $user_info ='
           <h3 style="text-align:left;text-transform: capitalize;">Dear '.$userName.',</h3>
          <p>Thank you for registering with Locumkit</p>
          <p>The service we endeavour to deliver is and will remain <strong style="color:rgb(255, 0, 0);">FREE</strong> for you. We want you to think of Locumkit as the go to platform for all your locum needs.</p>
          <p>We are in the process of bringing on board locums, and once we are at a point where we feel enough locums are on the platform, we will be sending another email inviting you to post jobs.</p>
          <p>In the meantime, we welcome any points you may like to raise. Our vision is simple - to empower the locum community. We will always strive to personally come back to you and make continuous improvements to this platform which is created solely for the people within the profession.</p>
          <p>Some key benefits you will receive from this platform:</p>
          <ul>
            <li><p>With only a few taps, you will be able to advertise a job - anytime and anywhere</p></li>
            <li><p>Not only set daily rates but also the option to set automatic incremental rate increases</p></li>
            <li><p>Beyond defining your standard requirements for jobs as part of the registration process - you will also have the ability to add specific requirements to each individual job</p></li>
            <li><p>Select locums who have low cancellation rates,</p></li>
            <li><p>Reports that can help you keep up to date with all your locum booking, expenses etc.</p></li>
          </ul>
          <p>Being a FREE to use service, with a real-time access, ability to tailor jobs, rates, and adding locums to your private list</p>
          <p>We thank you again and request to bear with us as we increase our database of locums.</p>
          <p>We will be reaching out soon again.</p>
          <p>For any queries please reply to this email</p>        
          <!-- <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Login/username</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$userLogin.'</td>
            </tr>
          </table>-->
        </div>'.$footer.'</body></html>';
          $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;text-align: left;">
              
            '.$user_info;
        /* Mail Send to Acivated user */
          try {
              // $mailFre = new \Gc\Mail('utf-8', $massageFre);
              // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mailFre->setFrom($adminEmail, 'Locumkit');
              // $mailFre->addTo($userEmail);
              // $mailFre->setSubject('LocumKit account verified');
              // $mailFre->send();

              $this->sendSMTPMail($massageFre,$adminEmail,$userEmail,$to_name,'LocumKit account verified');
              //$this->flashMessenger()->addSuccessMessage('Message sent');


          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
        }

        /* Cancel Emp Job notification to Freelancer */
        public function cancelJobByEmpNotificationToFreelancer($fid,$jid,$cancel_reason,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          $jobDetails = $this->getCancelJobInfo($jid,$adapter);
          $notifyController = new NotificationController();
          if (!empty($freData)) {
            $freEmail   = $freData['email'];
            $freName  = $freData['firstname'].' '.$freData['lastname'];
            $cancelJobMsg = $header;
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi '.$freName.',</p>';
            $cancelJobMsg .= '<p>We are sorry to inform you that the following booking has just been cancelled by the employer.</p>';
            
            $cancelJobMsg .= $jobDetails;
            $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
            $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
            $cancelJobMsg .= '</div>';
            $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
            $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Additional information </h3>';
            $cancelJobMsg .= '<p style="padding:0px 10px;">We have updated your calendar such that you are now available for the jobs on the designated day. You will now receive e-mails for that day. If you are no longer available to work on this day, please login and adjust your availability settings. We apologise for any inconvience this may have caused you. </p>';

            $cancelJobMsg .= '</div></div>';
            $cancelJobMsg .= $footer;
            //echo $closeJobMsg;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            //echo $cancelJobMsg;
            /* Mail Send to Freelancer */ 
            try {
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($freEmail);
                // $mail->setSubject('Locumkit: Cancellation of job');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$freEmail,$to_name,'Locumkit: Cancellation of job');
                //$this->flashMessenger()->addSuccessMessage('Message sent');
                    
                //send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->cancelJobByEmpNotificationToFreelancerSms($fid,$jid);
                //send sms end
                
                
                
                /* Fetch record of job */
                $sqlJob = "SELECT * from job_post WHERE job_id = '$jid'"; 
                $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
                $job = $jobView->toArray();
                foreach ($job as $key => $value) {  
                    $jobTitle   = $value['job_title'];
                    $jobDate  = $value['job_date'];
                    $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
                    $subject_jobRate  = $this->getCurrencyCode().number_format($value['job_rate'],2);
                    $jobAddress = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
                    $jobDesc  = $value['job_post_desc'];
                    $jobEmpId   = $value['e_id'];
                }

                //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($jid,$message='The following job has been cancelled by employer. Job Ref.: '.$jid.', Date: '.$jobDate.', Location: '.$jobAddress.', Rate: '.$subject_jobRate.', Reason : '.$cancel_reason.'. Open this message to view full details. Your calender has been updated accordingly.',$title='Job cancelled',$fid,$types="jobCancel");
          
          
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }  
        }
          
        }

        /* Cancel Emp Job notifiction to Employer */
        public function cancelJobByEmpNotificationToEmployer($eid,$fid,$jid,$cancel_reason,$adapter)
        {

          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'%';
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          if (!empty($freData)) {
              $freEmail   = $freData['email'];
            $freName  = $freData['firstname'].' '.$freData['lastname'];
          }
          $empData  = $this->getEmployerInfo($eid,$adapter);
          $jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d-m-Y').'</td>
            </tr>
            <tr> 
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Locum name </th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
            </tr>
          </table>';
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
            $empName  = $empData['firstname'].' '.$empData['lastname'];
            $cancelJobMsg = $header;
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi <b>'.$empName.'</b>,</p>';    
            $cancelJobMsg .= '<p>You have just cancelled the following booking:</p>';
            //$cancelJobMsg .= '<p>This email is a confirmation for the following booking being cancelled by you: </p>';
            $cancelJobMsg .= $jobDetails;
            $cancelJobMsg .= '<h5>Your cancellation rate now is : '.$cancelationPercent.' </h5>';
            $cancelJobMsg .= '<p>We have notified the Locum of the cancellation .</p>';
            $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
            $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Your reason for cancellation was:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
            $cancelJobMsg .= '</div>';
            
            $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
            $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
            $cancelJobMsg .= "<p style='padding: 0px 10px;margin: 10px 0px 0px;'>Your cancellation rate is based on the last six months of results. </p><p style='padding: 0px 10px;'>Your cancellation percentage is advertised to all locums that you invite for future potential bookings.</p><p style='padding: 0px 10px;margin: 0px 0px 10px;'>Our aim at Locumkit is to promote an environment where these cancellations are kept at a minimum and we hope to achieve this by having everyone's cancellation rates transparent. </p>";

            $cancelJobMsg .= '</div></div>';
            $cancelJobMsg .= $footer;
            //echo $closeJobMsg;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            //echo $cancelJobMsg;
            /* Mail Send to Freelancer */ 
            try {
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($empEmail);
                // $mail->setSubject('Locumkit: Cancellation of job');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$empEmail,$to_name,'Locumkit: Cancellation of job');
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
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'%';
          $empData  = $this->getEmployerInfo($eid,$adapter);
          if (!empty($empData)) {
            $empId  = $eid;
          $empName  = $empData['firstname'].' '.$empData['lastname'];
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
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
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
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($adminEmail);
                // $mail->setSubject('Locumkit: Cancellation of job');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$adminEmail,$to_name,'Locumkit: Cancellation of job');
                //$this->flashMessenger()->addSuccessMessage('Message sent');
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }  
        }
        }




        
        /* Cancel Fre Job notifiction to Freelancer */
        public function cancelJobByFreNotificationToFreelancer($eid,$fid,$jid,$cancel_reason,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $cancelationPercent = $this->getFreCancellationRate($fid,$adapter).'%';
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          $empData = $this->getEmployerInfo($eid,$adapter);
          $jobExtraData = $this->getJobAddressInfo($jid,$adapter);
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
            $empName  = $empData['firstname'].' '.$empData['lastname'];
            $empAddress = $jobExtraData['job_address'].', '.$jobExtraData['job_region'].', '.$jobExtraData['job_zip'];          
          }

          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$jid'"; 
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();

          foreach ($job as $key => $value) {
            $jobDate  = $value['job_date'];
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
            $freEmail   = $freData['email'];
            $freName  = $freData['firstname'].' '.$freData['lastname'];
            $cancelJobMsg = $header;
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi '.$freName.',</p>';
            $cancelJobMsg .= '<p>We would like to inform you that you have cancelled the following job:</p>';
            
            $cancelJobMsg .= $jobDetails;
            $cancelJobMsg .= '<h5>Your cancellation percentage is now: '.$cancelationPercent.' </h5>';
            $cancelJobMsg .= '<p>We have also notified the employer of this action.</p>';
            $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
            $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason provided for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
            $cancelJobMsg .= '</div>';
            
            $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
            $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Additional information </h3>';
            $cancelJobMsg .= '<p style="padding: 10px;">Please, bear in mind that your cancellation percentage is visible to other potential employers. That is one of the ways in which we try to promote supportive and pleasant working environment with minimum cancellation. We understand that sometimes this action is necessary; therefore, employers can see your reason for cancellation. Your cancellation percentage is based on your results in the past six months.</p>';

            $cancelJobMsg .= '</div></div>';
            $cancelJobMsg .= $footer;
            //echo $closeJobMsg;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            //echo $cancelJobMsg;
            /* Mail Send to Freelancer */ 
            try {
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($freEmail);
                // $mail->setSubject('Locumkit: Cancellation of job');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$freEmail,$to_name,'Locumkit: Cancellation of job');
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
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $notifyController = new NotificationController();
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
          $endecrypt = new Endecrypt();
          $userEid = $endecrypt->encryptIt($eid);
          $userFre = $endecrypt->encryptIt($fid);
          $blockFreLink = $serverUrl().'/block-user?eid='.$userEid.'&fid='.$userFre;
          $empData  = $this->getEmployerInfo($eid,$adapter);
          $jobDetails = $this->getCancelJobInfo($jid,$adapter);
          
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          if(!empty($freData)){
              $locum_name = $freData['firstname'].' '.$freData['lastname'];
          }
          
          
          if (!empty($empData)) {
            $empEmail   = $empData['email'];
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $cancelJobMsg = $header;
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi '.$empName.',</p>';
            $cancelJobMsg .= '<p>We are sorry to inform you that the following job has just been cancelled by the locum.</p>';
            
            $cancelJobMsg .= $jobDetails;
            $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
            $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
            $cancelJobMsg .= '</div>';
            $cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
            $cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Additional information</h3>';
            //$cancelJobMsg .= '<p style="padding:0px 10px;"><b>If you want to avoid using this locum in the future, please <a href="'.$blockFreLink.'"> click here </a> to block.</b></p><p style="padding: 0px 10px;">For the betterment of the profession, we would only advise you to do this, if you strongly feel this locum has a tendency of continuously cancelling last minute.</p>';
      $cancelJobMsg .= '<p style="padding:0px 10px;"><b>To avoid using this locum in the future, please <a href="'.$blockFreLink.'"> click here </a></b></p><p style="padding: 0px 10px;">For the betterment of the profession, we would only advise you to do this, if you strongly feel this locum has a tendency of continuously cancelling last minute.</p>';
            if($is_relist == 1){              
             
              $cancelJobMsg .= '<p style="padding: 0px 10px;">Please <a href="'.$serverUrl().'/managejob?e='.$jid.'">click here</a> to relist the job so that Locumkit can find you a matching locum.</p>';
            }else{
              $cancelJobMsg .= '<p style="padding: 0px 10px;">As per your original posting this job has not  been reslisted automatically. If you want please go into <b>Manage job</b> and copy the job to repost.</p>';
            }
            
            $cancelJobMsg .= '<p style="padding: 0px 10px;"><b>We apologise for any inconvenience this may have caused you.</b></p>';

            $cancelJobMsg .= '</div></div><br/>';
            $cancelJobMsg .= $footer;
            //echo $closeJobMsg;
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            //echo $cancelJobMsg;
            
            /* Fetch record of job */
            $sqlJob = "SELECT * from job_post WHERE job_id = '$jid'"; 
            $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
            $job = $jobView->toArray();
            foreach ($job as $key => $value) { 
                $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
                $subject_jobRate  = $this->getCurrencyCode().number_format($value['job_rate'],2);
            }
            
            /* Mail Send to Employer */ 
            try {
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($empEmail);
                // $mail->setSubject('Locumkit: Job cancelled');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$empEmail,$to_name,'Locumkit: Job cancelled');
                //$this->flashMessenger()->addSuccessMessage('Message sent');
          
                //send sms start
                $jobsmsController = new JobsmsController();
                $jobsmsController->cancelJobByFreNotificationToEmployerSms($eid,$jid);
                //send sms end

                //Mobile APP Notification
                $mobile_invitation_send = $notifyController->notification($jid,$message='The following job has been cancelled Job Ref:'.$jid.', Locum: '.$locum_name.', Rate: '.$subject_jobRate.'. Reason : '.$cancel_reason.'. Open this message to view full details. ',$title='Cancellation of job',$eid,$types="jobCancel");
          
          
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }  
        }
          
        }

        /* Cancel Emp Job notifiction to Admin */
        public function cancelJobByFreNotificationToAdmin($fid,$jid,$cancel_reason,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $cancelationPercent = $this->getFreCancellationRate($fid,$adapter).'%';
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          if (!empty($freData)) {
            $freId  = $fid;
          $freName  = $freData['firstname'].' '.$freData['lastname'];
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
            $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi Admin,</p>';
            $cancelJobMsg .= '<p>The following Locum has just cancelled a job</p>';
            
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
                // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($adminEmail);
                // $mail->setSubject('Locumkit: Cancellation of job');
                // $mail->send();

                $this->sendSMTPMail($cancelJobMsg,$adminEmail,$adminEmail,$to_name,'Locumkit: Cancellation of job');
                //$this->flashMessenger()->addSuccessMessage('Message sent');
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }  
        }
        }

        

        /* Block user notifiction to Admin */
        public function sendBlockNotificationToAdmin($eid,$fid,$adapter)
        {
          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($fid,$adapter);
          $empData  = $this->getEmployerInfo($eid,$adapter);
          if (!empty($freData)) {
          $freName  = $freData['firstname'].' '.$freData['lastname'];
        }

        if (!empty($empData)) {
          $empName  = $empData['firstname'].' '.$empData['lastname'];
        }
        
          $blockMsg = $header;
          $blockMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
              <p>Hi <b>Admin</b>,</p>';
          $blockMsg .= '<p>The Employer <b>'.$empName.'</b> just block the Locum <b>'.$freName.'</b></p>';
          
          $blockMsg .= '</div>';
          $blockMsg .= $footer;
          //echo $closeJobMsg;
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          //echo $cancelJobMsg;
          /* Mail Send to Freelancer */ 
          try {
              // $mail = new \Gc\Mail('utf-8', $blockMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($adminEmail);
              // $mail->setSubject('Locumkit: Block Freelancer');
              // $mail->send();

              $this->sendSMTPMail($blockMsg,$adminEmail,$adminEmail,$to_name,'Locumkit: Block Freelancer');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }  
        
        }



        public function mailHeader(){
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $site_name  = $configGet->get('site_name');
          $header = '<div style="width: 700px;"><div class="mail-header" style="background: #00A9E0; padding: 20px 50px;  border: 2px solid #000; clear: both; ">';
          $header .= '
            <a style="outline: none !important;" href="'.$serverUrl().'"><img src="'.$serverUrl().'/public/frontend/locumkit-template/img/logo.png" alt="'.$site_name.'" width="100px"></a>
            ';  
          $header .= '</div><div style="border-right: 2px solid #000; border-left: 2px solid #000;">';
          return $header;
        }
        public function mailFooter(){
          /* Get admin record */          
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $site_name  = $configGet->get('site_name');
          $site_phone     = $configGet->get('site_mobile');
          $site_addr  = $configGet->get('site_addr');
          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');    
          $footer = '<div style=" padding: 0px 50px 30px;  text-align: left; font-family: sans-serif; ">
            <p style="margin: 5px 0px;"><b>Thank you</b></p>
            <p style="margin: 5px 0px;"><b>The Locumkit Team</b></p>
            <p style="font-size: 13px;margin: 5px 0px;"></p><p style="margin: 5px 0px;"><em>For any queries please contact us <a href="'.$serverUrl().'/contact">here</a>.</em></p>
            </div>
            <div class="mail-footer" style="background: #252525; color: #fff; padding: 15px 50px; margin-top: 0px; border-top:2px solid #000;">
            <span style="width: 50%;line-height: 26px; color:#a3a3a3">Copyright &copy; '.date("Y").' Locumkit - All Rights Reserved</span>
                         <ul style="display:inline-block; padding: 0;margin: 0 auto; width: 40%;text-align: right;"><li style="list-style: none;margin-left: 5px;margin-right: 5px; display: inherit;"><a href="https://www.facebook.com/locumkit" target="_blank"><img src="'.$serverUrl().'/public/frontend/locumkit-template/new-design-assets/img/facebook-n.png" title="facebook" alt="Facebook"></a></li><li style="list-style: none;margin-left: 5px;margin-right: 5px; display: inherit;"><a href="https://www.linkedin.com/company/locumkit" target="_blank"><img src="'.$serverUrl().'/public/frontend/locumkit-template/new-design-assets/img/linkedin-n.png" title="Linkedin" alt="Linkedin"></a></li></ul>
            </div>';       
          return   $footer;       
          
        }

        public function getCurrencySymbol()
        {
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          return $currencySymbol = $configGet->get('email_currency');  
        }
        public function getCurrencyCode()
        {
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          return $currencySymbol = $configGet->get('email_currency');  
        }
        public function getCancelJobInfo($cjid,$adapter)
        {
          /* Fetch record of job */
          $sqlJob = "SELECT * from job_post WHERE job_id = '$cjid'";  
          $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
          $job = $jobView->toArray();
          foreach ($job as $key => $value) {  
            $jobId    = $value['job_id'];
            $jobTitle   = $value['job_title'];
            $jobDate  = $value['job_date'];
            $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
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
            $functionsController = new FunctionsController();
          /*$sqlContCancellation = "SELECT * FROM job_cancel WHERE c_uid = '$uid'"; 
          $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
          $count = $contCancellation->count();
          $finalCount = $count + 1; // adding current cancellation
          $sqlAcceptedJob = "SELECT * FROM job_action WHERE ( action = '6' OR action = '3' ) AND f_id = '$uid'";  
          $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
          $countJobAccept = $acceptedJob->count();
          $freCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
          return $freCancellationRate;*/
          return $cancellationRate = $functionsController->getFreCancellationRate($uid,$adapter);
  
        }

        /*Cancellation Rate Employer */
        public function getEmpCancellationRate($uid,$adapter)
        {
            $functionsController = new FunctionsController();
          /*$sqlContCancellation = "SELECT * FROM job_post WHERE e_id = '$uid' AND job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' )"; 
          $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
          $count = $contCancellation->count();
          $finalCount = $count + 1; // adding current cancellation
          $sqlAcceptedJob = "SELECT * FROM job_post WHERE e_id = '$uid' AND ((job_status = 4) OR ( job_status = '8' AND job_id IN ( SELECT job_id FROM  job_action WHERE action = '7' )))"; 
          $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
          $countJobAccept = $acceptedJob->count();
          $empCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
          return $empCancellationRate;*/  
          return $cancellationRate = $functionsController->getEmpCancellationRate($uid,$adapter);
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
        public function sendExpenseNotification($jobId,$fid,$link,$adapter,$type=null)
        {
          $header = $this->mailHeader();
          $footer = $this->mailFooter();
          $freData  = $this->getFreelancerInfo($fid,$adapter);  
          $notifyController = new NotificationController();        
          if (!empty($freData)) {
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          $ferEmail = $freData['email'];
        }

          $expenseMsg = $header;
          $expenseMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
              <p>Hi '.$freName.',</p>';
          $expenseMsg .= '<p>Please enter your expenses for the day (if any) by '.$link.'</p>';
      //$expenseMsg .= '<p>'.$link.'</p>';
          $expenseMsg .= '</div>';
          $expenseMsg .= $footer;

          //echo $expenseMsg;



          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');
          //echo $cancelJobMsg;
          /* Mail Send to Freelancer */ 
          try {
              // $mail = new \Gc\Mail('utf-8', $expenseMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($ferEmail);
              // $mail->setSubject('Locumkit: Job expenses');
              // $mail->send();

              $this->sendSMTPMail($expenseMsg,$adminEmail,$ferEmail,$to_name,'Locumkit: Job expenses');
              //$this->flashMessenger()->addSuccessMessage('Message sent');
              //Mobile APP Notification
              if ($type=='private') {
                $mobile_invitation_send = $notifyController->notification($jobId,$message='Please open this message to confirm your expenses for the day.',$title='Expense ',$fid,$types="privateJobExpense");
              }else{
                $mobile_invitation_send = $notifyController->notification($jobId,$message='Please open this message to confirm your expenses for the day.',$title='Expense',$fid,$types="jobExpense");
              }
                
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
          $freData  = $this->getFreelancerInfo($freId,$adapter);          
          if (!empty($freData)) {
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          $ferEmail = $freData['email'];
        }
        $expense = $expense != 0 ? $expense : 'Not submited yet.';
        $freFeedback = $freFeedback != 0 ? $this->calculatestarsaverage_summary($freFeedback) : 'Not submitted yet.';
          $summaryMsg = $header;
          $summaryMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
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
              // $mail = new \Gc\Mail('utf-8', $summaryMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($ferEmail);
              // $mail->setSubject('Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days')));
              // $mail->send();

              $sub = 'Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days'));
              $this->sendSMTPMail($summaryMsg,$adminEmail,$ferEmail,$to_name,$sub);
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
          $empData  = $this->getEmployerInfo($empId,$adapter);          
          if (!empty($empData)) {
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          $empEmail = $empData['email'];
        }
        $expense = $expense != 0 ? $expense : 'Not submited yet.';
        $empFeedback = $empFeedback != 0 ? $this->calculatestarsaverage_summary($empFeedback)  : 'Not submitted yet.';
          $summaryMsg = $header;
          $summaryMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
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
                  <td style=" border: 1px solid black;  text-align:left;  padding:5px;"> '.$empFeedback .' </td>
                </tr>
              </table>';

          $summaryMsg .= '<p>&nbsp;</p><p>Want to block this locum, please <a href="'.$blockFreLink.'">click here.</a></p>';
          $summaryMsg .= '<p>To see details of these detials please login to your profile</p>';
          $summaryMsg .= '<p>If there are entires with N/A then this is because we did not get information from you or the employer (feedback). You can always go into your profile to add information on your income and expense.</p>';
          
          $summaryMsg .= '</div>';
          $summaryMsg .= $footer;

          echo $summaryMsg;
          
          $adminEmail = $configGet->get('mail_from');
          //echo $cancelJobMsg;
          $currentDate = date('Y-m-d');
          /* Mail Send to Freelancer */ 
          try {
              // $mail = new \Gc\Mail('utf-8', $summaryMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($empEmail);
              // $mail->setSubject('Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days')));
              // $mail->send();

              $sub = 'Summary on job '.date('d/m/Y', strtotime($currentDate .' -2 days'));
              $this->sendSMTPMail($summaryMsg,$adminEmail,$empEmail,$to_name,$sub);

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
          $empData  = $this->getEmployerInfo($emp_id,$adapter);         
          if (!empty($empData)) {
          $empName  = $empData['firstname'].' '.$empData['lastname'];
          $empEmail = $empData['email'];
        }
        $freData  = $this->getFreelancerInfo($fre_id,$adapter);         
          if (!empty($freData)) {
          $freName  = $freData['firstname'].' '.$freData['lastname'];
          $freEmail = $freData['email'];
        }
        
        if ($user_type == 2) {
          $disputeFreMsg = $header;
            $disputeFreMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
                <p>Hi '.$freName.',</p>';
            $disputeFreMsg .= '<p>'.$empName.' has just disputed the feedback you have left for them in regards to the job <b>#'.$job_id.'</b>. </p>';
            
            $disputeFreMsg .= '<p>We at LocumKit shall look into this and hope to come to a resolution within the next 24-48 hrs. We might contact you to help us in this process.</p>';
            
            $disputeFreMsg .= '<p>Thank you for your co-operation. </p>';

            $disputeFreMsg .= '</div>';
            $disputeFreMsg .= $footer;

            $disputeEmpMsg = $header;
            $disputeEmpMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
                <p>Hi '.$empName.',</p>';
            $disputeEmpMsg .= '<p>We have received your application for dispute on the feedback submitted by '.$freName.' regarding the job <b>#'.$job_id.'</b>. </p>';
            
            $disputeEmpMsg .= '<p>We aim to resolve this within the next two days.</p>';
            $disputeEmpMsg .= '<p>Thank you for your co-operation. </p>';
            
            $disputeEmpMsg .= '</div>';
            $disputeEmpMsg .= $footer;

            $disputeAdminMsg = $header;
            $disputeAdminMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
                <p>Hi Admin,</p>';
            $disputeAdminMsg .= '<p><b style="text-transform: capitalize;">'.$empName.'</b> submit dispute on feedback that submitted by <b style="text-transform: capitalize;">'.$freName.'</b> on job <b>#'.$job_id.'</b>. </p>';
            
            $disputeAdminMsg .= '<p>Please <a href="'.$feedbackAdminLink.'">click here</a> to view the datails.</p>';
            
            $disputeAdminMsg .= '</div>';
            $disputeAdminMsg .= $footer;
        }elseif($user_type == 3){
          $disputeEmpMsg = $header;
            $disputeEmpMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
                <p>Hi '.$empName.',</p>';
            $disputeEmpMsg .= '<p>We would like to inform you that '.$freName.' has filed a dispute on the feedback you submitted regarding the job <b>#'.$job_id.'</b>. </p>';
            
            $disputeEmpMsg .= '<p>We at LocumKit shall look into this and hope to come to a resolution within the next 24-48 hrs. We might contact you to help us in this process.</p>';
            $disputeEmpMsg .= '<p>Thank you for your co-operation. </p>';
            
            $disputeEmpMsg .= '</div>';
            $disputeEmpMsg .= $footer;

            $disputeFreMsg = $header;
            $disputeFreMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
                <p>Hi '.$freName.',</p>';
            $disputeFreMsg .= '<p>We have received your application for dispute on the feedback submitted by '.$empName.' regarding the job <b>#'.$job_id.'</b>. </p>';
            
            $disputeFreMsg .= '<p>We aim to resolve this within the next two days.</p>';
            $disputeFreMsg .= '<p>Thank you for your co-operation.</p>';
            
            $disputeFreMsg .= '</div>';
            $disputeFreMsg .= $footer;

            $disputeAdminMsg = $header;
            $disputeAdminMsg .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">
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
              // $mail = new \Gc\Mail('utf-8', $disputeFreMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($freEmail);
              // $mail->setSubject('Dispute Alert on job: #'.$job_id);
              // $mail->send();

              $sub = 'Dispute Alert on job: #'.$job_id;
              $this->sendSMTPMail($disputeFreMsg,$adminEmail,$freEmail,$to_name,$sub);
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
              // $mail = new \Gc\Mail('utf-8', $disputeEmpMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($empEmail);
              // $mail->setSubject('Dispute Alert on job: #'.$job_id);
              // $mail->send();

              $sub = 'Dispute Alert on job: #'.$job_id;
              $this->sendSMTPMail($disputeEmpMsg,$adminEmail,$empEmail,$to_name,$sub);
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
              // $mail = new \Gc\Mail('utf-8', $disputeAdminMsg);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($adminEmail);
              // $mail->setSubject('Dispute Alert on job: #'.$job_id);
              // $mail->send();

              $sub = 'Dispute Alert on job: #'.$job_id;
              $this->sendSMTPMail($disputeAdminMsg,$adminEmail,$adminEmail,$to_name,$sub);
              //$this->flashMessenger()->addSuccessMessage('Message sent');
          } catch (Exception $e) {
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          } 
        }


      public function invoiceMail($contant,$semail,$sname,$invoice_id,$fre_id,$is_bank_details=false,$adapter){
      /* Mail Send to employer */     
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');

            $pdfController = new PdfController();

            /*Genertae Ivoice PDF */
            $pdf_invoice = $pdfController->generate_pdf_invoice($contant,$invoice_id, $is_bank_details);


            $freData  = $this->getFreelancerInfo($fre_id,$adapter);         
            if (!empty($freData)) {
              $freName  = $freData['firstname'].' '.$freData['lastname'];
              $freEmail = $freData['email'];
            }


            //$mail_contant = '<p>Hello,</p><p>Please find below/attached an invoice from '.$freName.' for their locum days.</p>';
            $mail_contant = $this->mailHeader();
           
            $mail_contant .= '<div style="padding:20px 50px 30px;text-align:left;font-family:sans-serif;"><div><p>To: '.$sname.'</p></div><div><p>Please find attached my invoice for my locum day(s) covered with yourselves.</p></div><div><p style="margin-bottom: 5px;margin-top: 30px;">Kind Regards</p></div><div><p style=" margin:0px">'.$freName.'</p></div><div><p style=" font-size:13px;margin: 0;">'.$freEmail.'</p></div> </div></div>';

             $mail_contant .= '<div style="display:none;">'.$this->mailFooter().'</div>';

            try {

              $message = new Message(); 
              $message->addTo($semail);
              $message->addFrom($freEmail, $freName);
              $message->setSubject('LocumKit Invoice - #'.$invoice_id);
           
              // HTML part
              $htmlPart           = new MimePart($mail_contant);
              $htmlPart->encoding = Mime::TYPE_HTML;
              $htmlPart->type     = "text/html; charset=UTF-8";

              $body = new MimeMessage();
              if ($pdf_invoice) {               
                  $content = new MimeMessage();             
                  $content->addPart($htmlPart);
           
                  $contentPart = new MimePart($content->generateMessage());
                  $contentPart->type = 'text/html; charset=UTF-8\n boundary=\'' .
                      $content->getMime()->boundary() . "'";
           
                  $body->addPart($contentPart);
                  $messageType = 'multipart/related';      
                  $pdf_content = file_get_contents($pdf_invoice);
                    $attachment = new MimePart($pdf_content);
                    $attachment->filename    = 'invoice-'.$invoice_id.'.pdf';
                    $attachment->type        = 'application/pdf';
                    $attachment->encoding    = Mime::ENCODING_BASE64;
                    $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                    $body->addPart($attachment);

                  $message->setBody($body);
                $message->getHeaders()->get('content-type')->setType($messageType);
                $message->setEncoding('UTF-8');
              }
              $mail = new Sendmail();
            $mail->send($message);
            unlink($pdf_invoice);
            return true;
          } catch (Exception $e) {
            return false ;
              //$this->flashMessenger()->addErrorMessage($e->getMessage());
          }
    }




      /* Send Email Verification mail to locum and admin */
      public function sendVerifyEmailtofreelancer($u_id,$package_final,$adapter)
      {

          $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
          $adminEmail = $configGet->get('mail_from');    
          $encypt = new Endecrypt();
          $host =$this->getRequest()->getUri()->getHost();
          $sub2 = 'New Locum registration';
          $infoLine = 'This is an email to confirm a new Locum has just registered';
          $package_price='<tr>
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Package</th>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$this->getCurrencySymbol().$package_final.'</td>
          </tr>';


          $freData = $this->getFreelancerInfo($u_id,$adapter);
          if (!empty($freData)) {
           $email =  $freEmail  = $freData['email'];
              $freName  = $freData['firstname'].' '.$freData['lastname'];
              $email      = $freData['email'];
              $frepro = $this->getUserProfessional($freData['user_acl_profession_id'],$adapter);
          }

          $date = date('Y-m-d');
          $activation_key = $encypt->encryptIt($email).'-'.sha1($date);

          $header   = $this->mailHeader();
          $footer   = $this->mailFooter();

          
         

          $message = $header.'
            <div style="padding: 25px 50px 5px; text-align: left;">
            <p>Hi '.$freName.',</p>
            <p>Thank you for registering.</p><p> You are now only one step away from full access to Locumkit.</p>
            <p>Please <a href="'.$serverUrl().'/email-activate?'.$activation_key.'">click here</a> to verify your email address and start recieveing notifications for jobs and reminders for your income and expenses. </p>
            <p></p>
            </div>'.$footer;

          $message2=$header.'
            <div style="padding: 25px 50px 5px;text-align: left; width:84.2%">
            <p>Hello <b>Admin</b>,</p>
            <h3 style="font-weight: normal;">'.$infoLine.'</h3>
            <table width="100%" style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
              <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">First Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freData['firstname'].'</td>
              </tr>
               <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Last Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freData['lastname'].'</td>
              </tr>
              <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">ID</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$u_id.'</td>
              </tr>
                                            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Profession</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$frepro['name'].'</td>
              </tr>
              '.$package_price.'
            </table><br/>
            <p><p>
            </div>'.$footer;

          /* Mail Send to freelancer */
          //send email to freelancer
          try{
              // $mail = new \Gc\Mail('utf-8', $message);
              // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
              // $mail->setSubject('Locumkit Account Verification');
              // $mail->setFrom($adminEmail, 'Locumkit');
              // $mail->addTo($email, $freName);
              // $mail->send();

              $this->sendSMTPMail($message,$adminEmail,$email,$freName,'Locumkit Account Verification');

        //       $mail2 = new \Gc\Mail('utf-8', $message2);
        //       $mail2->getHeaders()->addHeaderLine('Content-type','text/html');
        //       $mail2->setSubject($sub2);
        // $mail2->setFrom($email, $freData['firstname']);
        //       $mail2->addTo($adminEmail);
             // $mail2->addTo('ragineefwork@gmail.com', 'Admin');
             // $mail2->addTo($this->getServiceLocator()->get('CoreConfig')->getValue('mail_from'),$this->getServiceLocator()->get('CoreConfig')->getValue('mail_from_name'));
              // $mail2->send();

              $this->sendSMTPMail($message2,$email,$adminEmail,$to_name,$sub2);

          }
          catch (Exception $e) {
          }
      }


    /* RESend Email Verification mail to locum */
        public function reSendVerifyEmailtofreelancer($u_id,$adapter)
        {
            $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $adminEmail = $configGet->get('mail_from');
            $encypt = new Endecrypt();
            $freData = $this->getFreelancerInfo($u_id,$adapter);
            if (!empty($freData)) {
                $freName  = $freData['firstname'].' '.$freData['lastname'];
                $email = $freEmail  = $freData['email'];
            }
            $date = date('Y-m-d');
            $activation_key = $encypt->encryptIt($email).'-'.sha1($date);

            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();

            $message = $header.'
          <div style="padding: 25px 50px 5px;text-align: left;">
          <p>Hi '.$freName.',</p>          
          <p>Please <a href="'.$serverUrl().'/email-activate?'.$activation_key.'">click here</a> to verify your email address.</p>
          </div>'.$footer;

            //send email to freelancer
            try{
                // $mail = new \Gc\Mail('utf-8', $message);
                // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                // $mail->setSubject('Locumkit account verification');
                // $mail->setFrom($adminEmail, 'Locumkit');
                // $mail->addTo($email, $freName);
                // $mail->send();

                $this->sendSMTPMail($message,$adminEmail,$email,$to_name,'Locumkit account verification');
            }
            catch (Exception $e) {
            }
        }




        /* Cancel Emp Job notifiction to Private user */
        public function cancelJobByEmpNotificationToPrivateFreelancer($fid,$jid,$cancel_reason,$adapter)
        {
            $header     = $this->mailHeader();
            $footer     = $this->mailFooter();
            $freData    = $this->getPrivateUserInfo($fid,$adapter);
            $jobDetails = $this->getCancelJobInfo($jid,$adapter);
            if (!empty($freData)) {
                $freEmail   = $freData['p_email'];
                $freName    = $freData['p_name'];
                $cancelJobMsg = $header;
                $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi '.$freName.',</p>';
                $cancelJobMsg .= '<p>We are sorry to inform you that the following job has just been cancelled by the employer.</p>';

                $cancelJobMsg .= $jobDetails;
                $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
                $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Reason for cancellation:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
                $cancelJobMsg .= '</div>';
                //$cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
                //$cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
                //$cancelJobMsg .= '<p style="padding: 10px;">We have updated your calender as such that you are now available for jobs on that day. You shall receive emails now for that day. If you no longer are available to work on this day please login and make your self unavailable.</p>';
                //$cancelJobMsg .= '<p style="padding: 10px;"><b>We apologise for any inconvience this may have caused you.</b></p>';

                $cancelJobMsg .= '</div>';
                $cancelJobMsg .= $footer;
                //echo $closeJobMsg;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
                $adminEmail = $configGet->get('mail_from');
                //echo $cancelJobMsg;
                /* Mail Send to Freelancer */
                try {
                    // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                    // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mail->setFrom($adminEmail, 'Locumkit');
                    // $mail->addTo($freEmail);
                    // $mail->setSubject('Locumkit: Job cancelled');
                    // $mail->send();

                     $this->sendSMTPMail($cancelJobMsg,$adminEmail,$freEmail,$to_name,'Locumkit Job cancelled');
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



        /* Cancel Emp Job notifiction to Employer if job accepct by private locum*/
        public function cancelJobByEmpNotificationToEmployerIFPrivatefreelancer($eid,$fid,$jid,$cancel_reason,$adapter)
        {
            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();
            $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'';
            $freData  = $this->getPrivateUserInfo($fid,$adapter);
            if (!empty($freData)) {
                $freEmail   = $freData['p_email'];
                $freName  = $freData['p_name'];
            }
            $empData  = $this->getEmployerInfo($eid,$adapter);
            $jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d-m-Y').'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Private Locum Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Private Locum Email</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freEmail.'</td>
            </tr>
          </table>';
            if (!empty($empData)) {
                $empEmail   = $empData['email'];
                $empName  = $empData['firstname'].' '.$empData['lastname'];
                $cancelJobMsg = $header;
                $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi <b>'.$empName.'</b>,</p>';
                //$cancelJobMsg .= '<p>You have just cancelled the following booking:</p>';
        $cancelJobMsg .= '<p>This email is a confirmation for the following booking being cancelled by you:</p>';
                $cancelJobMsg .= $jobDetails;
                //$cancelJobMsg .= '<h5>Your cancellation percentage now is : '.$cancelationPercent.' </h5>';
                $cancelJobMsg .= '<p>We have notified the locum of the cancellation.</p>';
                $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
                $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Your reason for cancellation was:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
                $cancelJobMsg .= '</div>';

                //$cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
                //$cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
                //$cancelJobMsg .= '<p style="padding: 10px;">This cancellations percentage is visible to potential locums. We want to promote an environment where minimum cancellation take place. They can also view your reason for cancellation as we accept that sometimes cancellation do need to take place. Your cancellation percentage is based on your last six months of results.  </p>';

                $cancelJobMsg .= '</div>';
                $cancelJobMsg .= $footer;
                //echo $closeJobMsg;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
                $adminEmail = $configGet->get('mail_from');
                //echo $cancelJobMsg;
                /* Mail Send to Freelancer */
                try {
                    // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                    // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mail->setFrom($adminEmail, 'Locumkit');
                    // $mail->addTo($empEmail);
                    // $mail->setSubject('Locumkit: Cancellation of job');
                    // $mail->send();

                    $this->sendSMTPMail($cancelJobMsg,$adminEmail,$empEmail,$to_name,'Locumkit: Cancellation of job');
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

        /* Cancel Emp Job notifiction to Employer if job accepct by private locum*/
        public function cancelJobByEmpNotifyToEmployerIFPrivatefreelancer($eid,$fid,$jid,$cancel_reason,$adapter)
        {
            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();
            $cancelationPercent = $this->getEmpCancellationRate($eid,$adapter).'';
            $freData  = $this->getPrivateUserInfo($fid,$adapter);
            if (!empty($freData)) {
                $freEmail   = $freData['p_email'];
                $freName  = $freData['p_email'];
            }
            $empData  = $this->getEmployerInfo($eid,$adapter);
            $jobDetails = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
            <tr style="background-color: #f2f2f2;">
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Date</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.date('d-m-Y').'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Private Locum Name</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freName.'</td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Private Locum Email</th>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$freEmail.'</td>
            </tr>
          </table>';
            if (!empty($empData)) {
                $empEmail   = $empData['email'];
                $empName  = $empData['firstname'].' '.$empData['lastname'];
                $cancelJobMsg = $header;
                $cancelJobMsg .= '<div style="padding: 25px 50px 5px;text-align: left; font-family: sans-serif;">
                <p>Hi '.$empName.',</p>';
                $cancelJobMsg .= '<p>You have just cancelled the following booking:</p>';

                $cancelJobMsg .= $jobDetails;
                //$cancelJobMsg .= '<h5>Your cancellation percentage now is : '.$cancelationPercent.' </h5>';
                $cancelJobMsg .= '<p>We have notified the locum of the cancellation.</p>';
                $cancelJobMsg .= '<div style="margin: 0; margin-top: 20px;border: 1px solid #ff0000;">';
                $cancelJobMsg .= '<h3 style="background: #ff0000;margin: 0; padding: 5px 10px;  color: #fff;">Your reason for cancellation was:</h3><div style="padding: 10px;"> '.$cancel_reason.'</div>';
                $cancelJobMsg .= '</div>';

                //$cancelJobMsg .= '<div style="border: 1px solid #00a9e0; margin-top: 20px;">';
                //$cancelJobMsg .= '<h3 style="margin: 0;padding: 5px 10px;background: #00a9e0;color: #fff;">Important Information</h3>';
                //$cancelJobMsg .= '<p style="padding: 10px;">This cancellations percentage is visible to potential locums. We want to promote an environment where minimum cancellation take place. They can also view your reason for cancellation as we accept that sometimes cancellation do need to take place. Your cancellation percentage is based on your last six months of results.  </p>';

                $cancelJobMsg .= '</div>';
                $cancelJobMsg .= $footer;
                //echo $closeJobMsg;
                $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
                $adminEmail = $configGet->get('mail_from');
                //echo $cancelJobMsg;
                /* Mail Send to Freelancer */
                try {
                    // $mail = new \Gc\Mail('utf-8', $cancelJobMsg);
                    // $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mail->setFrom($adminEmail, 'Locumkit');
                    // $mail->addTo($empEmail);
                    // $mail->setSubject('Locumkit: Cancellation of job');
                    // $mail->send();

                    $this->sendSMTPMail($cancelJobMsg,$adminEmail,$empEmail,$to_name,'Locumkit: Cancellation of job');
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



        /* Reminder Notification mail to private locum */
        public function sendRemindertoprivateuser($jobpuid,$jobId,$jobEid,$adapter)
        {
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $adminEmail = $configGet->get('mail_from');
            /* Fetch record of job */
            $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId' AND job_status = '4'";
            $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
            $job = $jobView->toArray();
            foreach ($job as $key => $value) {
                $jobId    = $value['job_id'];
                $eId    = $value['e_id'];
                $jobTitle   = $value['job_title'];
                $jobDate  = $value['job_date'];
                $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
                $jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
                $jobDesc  = $value['job_post_desc'];
                $jobEmpId   = $value['e_id'];
                $storeName  = $this->getStoreInfo($adapter,$value['store_id']);
            }

            $freData  = $this->getPrivateUserInfo($jobpuid,$adapter);
            if (!empty($freData)) {
                $freEmail   = $freData['p_email'];
                $freName  = $freData['p_email'];
            }

            /* Get Employer e-mail id*/
            $sqlEmpEmail = "SELECT email,firstname,lastname from user WHERE id='$eId'";
            $empEmailData = $adapter->query($sqlEmpEmail, $adapter::QUERY_MODE_EXECUTE);
            $empEmails = $empEmailData->current();
            $empEmail = $empEmails['email'];
            $empName = $empEmails['firstname'].' '.$empEmails['lastname'];

            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();
            $mail_css   = $header;
            $job_info ='
          <h3 style="text-align:left;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
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
            <div style="padding: 25px 50px 5px;">
              <p>Hello '.$freName.'</b>,</p>
              <p>We would like to remind you that you have a booking coming up. Please, see the summary of details below:</p>
            '.$job_info.'
            <br/>
            </div>'.$footer.'</body></html>';
                    $reminderSubject = 'Job reminder';

          $massageEmp = $mail_css.'
            <div style="padding: 25px 50px 5px;">
              <p>Hello '.$empName.',</p>
              <p>This is just a courteous reminder for the below upcoming job:</p>              
            '.$job_info.'
            <br/>
            </div>'.$footer.'</body></html>';
            $reminderSubject = 'Job reminder';





            /* Mail Send to freelancer */
            try {
                if($freEmail){
                    // $mailFre = new \Gc\Mail('utf-8', $massageFre);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($freEmail);
                    // $mailFre->setSubject($reminderSubject);
                    // $mailFre->send();
                    

                    $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$reminderSubject);
                    $this->flashMessenger()->addSuccessMessage('Message sent');

                }
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
            /* Mail Send to employer */
            try {
                if($empEmail){
                    // $mailFre = new \Gc\Mail('utf-8', $massageEmp);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($empEmail);
                    // $mailFre->setSubject($reminderSubject);
                    // $mailFre->send();

                    $this->sendSMTPMail($massageEmp,$adminEmail,$empEmail,$to_name,$reminderSubject);
                    $this->flashMessenger()->addSuccessMessage('Message sent');
        /*
                    $smsLinksArray =  array('detail' => $serverUrl().'/single-job?view='.$jobId , 'cancel' =>$serverUrl().'/cancel-job?e='.$jobId); ;
                    $jobsmsController = new JobsmsController();
                    $jobsmsController->sendReminderSms($eId,$jobId,$smsLinksArray);*/

                }
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }

        public function sendOnDayRemindertoprivateuser($jobpuid,$jobId,$jobEid,$adapter)
        {
            $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $adminEmail = $configGet->get('mail_from');
            /* Fetch record of job */
            $sqlJob = "SELECT * from job_post WHERE job_id = '$jobId' AND job_status = '4'";
            $jobView = $adapter->query($sqlJob, $adapter::QUERY_MODE_EXECUTE);
            $job = $jobView->toArray();
            foreach ($job as $key => $value) {
                $jobId    = $value['job_id'];
                $eId    = $value['e_id'];
                $jobTitle   = $value['job_title'];
                $jobDate  = $value['job_date'];
                $jobRate  = $this->getCurrencySymbol().number_format($value['job_rate'],2);
                $jobAddress     = $value['job_address'].", ".$value['job_region']."-".$value['job_zip'];
                $jobDesc  = $value['job_post_desc'];
                $jobEmpId   = $value['e_id'];
                $storeName  = $this->getStoreInfo($adapter,$value['store_id']);
            }

            $freData  = $this->getPrivateUserInfo($jobpuid,$adapter);
            if (!empty($freData)) {
                $freEmail   = $freData['p_email'];
                $freName  = $freData['p_email'];
            }

            /* Get Employer e-mail id*/
            $sqlEmpEmail = "SELECT email,firstname,lastname from user WHERE id='$eId'";
            $empEmailData = $adapter->query($sqlEmpEmail, $adapter::QUERY_MODE_EXECUTE);
            $empEmails = $empEmailData->current();
            $empEmail = $empEmails['email'];
            $empName = $empEmails['firstname'].' '.$empEmails['lastname'];

            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();
            $mail_css   = $header;
            $job_info ='
          <h3 style="text-align:left;"> Job Information </h3>
          <table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
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
            <div style="padding: 25px 50px 5px;">
              <p>Hello <b>'.$freName.'</b>,</p>
              <p>We would like to remind you that you have a booking coming up. Please, see the summary of details below:</p>
            '.$job_info.'
            <br/>
            </div>'.$footer.'</body></html>';
            $reminderSubject = 'Job attendance reminder';


            /* Mail Send to freelancer */
            try {
                if($freEmail){
                    // $mailFre = new \Gc\Mail('utf-8', $massageFre);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($freEmail);
                    // $mailFre->setSubject($reminderSubject);
                    // $mailFre->send();

                    $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,$reminderSubject);
                    $this->flashMessenger()->addSuccessMessage('Message sent');

                }
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            } 
            
        }

        /* Send package renew success email to admin and freelancer */
        public function sendPackageRenewMail($fre_id,$amt,$adapter){
          $configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
            $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
            $adminEmail = $configGet->get('mail_from');
            $header   = $this->mailHeader();
            $footer   = $this->mailFooter();

            $freData  = $this->getFreelancerInfo($fre_id,$adapter);
            $mail_css   = $header;

            if (!empty($freData)) {
          $freEmail   = $freData['email'];
    $freName  = $freData['firstname'].' '.$freData['lastname'];
            }

            $massageFre = $mail_css.'
            <div style="padding: 25px 50px 5px;">
              <p>Hello <b>'.$freName.'</b>,</p>
              <p>Thank you...!</p><p> You successfully renewed your package and now you can enjoy all the facility of locumkit.</p>             
            </div>'.$footer.'</body></html>';

         $massageAdm = $mail_css.'
            <div style="padding: 25px 50px 5px;">
              <p>Hello Admin</p>
              <p> <b>'.$freName.' ('.$fre_id.')</b>, locum just renewed account please check the details in admin panel.</p> 
            </div>'.$footer.'</body></html>';

        /* Mail Send to freelancer */
            try {
                if($freEmail){
                    // $mailFre = new \Gc\Mail('utf-8', $massageFre);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($freEmail);
                    // $mailFre->setSubject('Locumkit account renew successfully.');
                    // $mailFre->send();

                    $this->sendSMTPMail($massageFre,$adminEmail,$freEmail,$to_name,'Locumkit account renew successfully.');
                    //$this->flashMessenger()->addSuccessMessage('Message sent');

                }
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            } 

            /* Mail Send to admin */
            try {
                if($freEmail){
                    // $mailFre = new \Gc\Mail('utf-8', $massageAdm);
                    // $mailFre->getHeaders()->addHeaderLine('Content-type','text/html');
                    // $mailFre->setFrom($adminEmail, 'Locumkit');
                    // $mailFre->addTo($adminEmail);
                    // $mailFre->setSubject('Locumkit account renewed.');
                    // $mailFre->send();

                    $this->sendSMTPMail($massageAdm,$adminEmail,$adminEmail,$to_name,'Locumkit account renewed.');
                    //$this->flashMessenger()->addSuccessMessage('Message sent');

                }
            } catch (Exception $e) {
                //$this->flashMessenger()->addErrorMessage($e->getMessage());
            } 

        }


    public function calculatestars($rating){
        $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        $totalStar = 5;
        $ratingStar = $rating;
        $currentStar = 1;
        $showstar = '';
        while($totalStar > 0){ 
            if($ratingStar >= $currentStar){
                $starurl = $serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite_fill.png';
            }else{                                                
                $starurl = $serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite_blank.png'; 
            }
            $showstar .= '<img src="'.$starurl.'" width="12px"/> ';
            //$showstar .= '<img src="'.$starurl.'" width="12px"/> ' ;
            $totalStar--; $currentStar++; 
        }
        return $showstar ;   
    }

    public function calculatestarsaverage($avgrating){
        $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        $pre = ($avgrating*2)*10 ;            
        $star =  '<div style="padding-top:8px"> <div style="background: url('.$serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite.png) repeat-x;
        font-size: 0;
        height: 21px;
        line-height: 0;
        overflow: hidden;
        text-indent: -999em;
        width: 110px;
        float: left;">
        <span style=" width:'.$pre.'% ;  background: url('.$serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite.png) repeat-x;
        background-position: 0 100%;
        float: left;
        height: 21px;
        display: block;"></span></div><div style="padding: 5px 0 0 0;"> &nbsp;&nbsp;'.$avgrating.' star(s)</div></div>' ;
        return $star; 
    }


    public function calculatestarsaverage_summary($avgrating){
      $serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
        $pre = ($avgrating*2)*10 ;            
        $star =  '<div style="width: 135px;"> <div style="background: url('.$serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite.png) repeat-x;
        font-size: 0;
        height: 15px;
        line-height: 0;
        overflow: hidden;
        text-indent: -999em;
        width: 70px;
                          background-size: 14px 30px;
        float: left;">
        <span style=" width:'.$pre.'% ;  background: url('.$serverUrl().'/public/frontend/locumkit-template/img/star-rating-sprite.png) repeat-x;
        background-position: 0 100%;
        float: left;
        height: 15px;
                          background-size: 14px 29px;
        display: block;"></span></div><div> &nbsp;&nbsp;'.$avgrating.' star(s)</div></div>' ;
        return $star; 
    }


    public function locum_email_terms($background_color = '#2dc9ff'){      
      $locum_email_terms = '<table style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;" width="100%;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
            <tr style="background-color: '.$background_color.';">
            <th style=" border: 1px solid black;  text-align:left;  padding:5px; font-weight:bold;"> Locumkit terms and condition</th>
            </tr>
            <tr>
            <th style=" border: 1px solid black;  text-align:left;  padding:5px;">DOCUMENTATION</th>
            </tr>
            <tr>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">Please ensure you have provided us the up to date/latest:
                <ul>
                  <li> GOC registration details</li>
                  <li> Evidence of current PCT listing</li>
                  <li> Two clinical references </li>                  
                  <li> Recent CV (not compulsory but recommended)</li>
                  <li> Proof of Professional indemnity Insurance</li>
                  <li> Copy of your personal ID </li>
                  <li>Up to date evidence of your CET record (we shall reqest this once a quarter to verify correct disclosure of CET points)</li>
                </ul>              
              </td>
            </tr>
            <tr>
              <th style=" border: 1px solid black;  text-align:left;  padding:5px;">DRESS CODE</th>
            </tr>
            <tr>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">
                <p>We expect all locums to dress appropriately in business attire. </p>
                <p>Employees are expected to demonstrate good judgment and professional taste. Courtesy of coworkers and your professional image to clients should be the factors that are used to assess that you are dressing in business attire that is appropriate.</p>
                <p>Please check for any additional comments above, where the employer might highlight any specific dress wear.</p>
              </td>
            </tr>
            <tr>
            <td style=" border: 1px solid black;  text-align:left;  padding:5px;font-weight: bold;">CANCELLATIONS</td>
            </tr>
            <tr>
              <td style=" border: 1px solid black;  text-align:left;  padding:5px;">
         
                <p>In the event that you are unable to fulfill your booking, it is vital that you cancel the job as soon as possible to give the store as much notice to make alternative arrangements. If the cancellation is at short notice (i.e. less than 24 hours before the booking date), please call the store directly as well as canceling through Locumkit.</p>
                <p>We advise to keep cancellations at a minimum - all cancellations go on your record and can impact your future bookings.</p>
              </td>
            </tr>
          </table>';
      return $locum_email_terms;
    }

    // Function for send mail using SMTP
    public function sendSMTPMail($msg,$from,$to,$to_name=null,$subject) {
      $transport = new SmtpTransport;
      $options= new SmtpOptions([
              'name'              => 'c44933.sgvps.net',
              'host'              => 'c44933.sgvps.net',
              'connection_class'  => 'login',
              'port'              => 465,
              'connection_config' => [
                  'username' => 'admin@localhost.com',
                  'password' => 'letmein@2018!',
                  'ssl'      => 'ssl',
              ]
          ]);
      $transport->setOptions($options);
      // Create HTML message 
      $html       = new MimePart($msg);
      $html->type = "text/html";
      $body       = new MimeMessage();
      $body->addPart($html);

      $message = new Message();
      $message->setBody($body);
      $message->setFrom($from,'Locumkit');
      $message->addTo($to,$to_name);
      $message->setSubject($subject);
      
      // Send message
      $transport->send($message);
  }

    // Function for send mail using SMTP
    public function sendSMTPMail2($msg,$from,$to,$to_name=null,$subject) {
      $transport = new SmtpTransport;
      $options= new SmtpOptions([
              'name'              => 'c44933.sgvps.net',
              'host'              => 'c44933.sgvps.net',
              'connection_class'  => 'login',
              'port'              => 465,
              'connection_config' => [
                  'username' => 'bookings@localhost.com',
                  'password' => 'Optometry10',
                  'ssl'      => 'ssl',
              ]
          ]);
      $transport->setOptions($options);
      // Create HTML message 
      $html       = new MimePart($msg);
      $html->type = "text/html";
      $body       = new MimeMessage();
      $body->addPart($html);

      $message = new Message();
      $message->setBody($body);
      $message->setFrom('bookings@localhost.com','Locumkit');
      $message->addTo($to,$to_name);
      $message->setSubject($subject);
      
      // Send message
      $transport->send($message);
  }


}