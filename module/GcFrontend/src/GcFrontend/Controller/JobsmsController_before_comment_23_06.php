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
use Twilio\Rest\Client;
use GcFrontend\Controller\ShorturlController as ShorturlController;

use GcFrontend\Controller\DbController as DbController;

class JobsmsController extends Action
{

    public function getmobileno($usrid){
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $sqlMobile = "SELECT mobile from user_extra_info WHERE uid='$usrid'";
        $freMobileData = $adapter->query($sqlMobile, $adapter::QUERY_MODE_EXECUTE);
        $freMobile = $freMobileData->current();
        return	$mobile = $freMobile['mobile'];
    }

    public function insertSms($data){
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $sqlSms = "INSERT INTO manage_sms(userid, contactno, sendfor) VALUES ('".$data['uid']."','".$data['mobile']."','".$data['sendfor']."')";		
        $freSmsData = $adapter->query($sqlSms, $adapter::QUERY_MODE_EXECUTE);

    }

    public function sendAcceptSmsToUser($userno,$content)
    {
        $from = '+12293514378';
        require_once 'twilio-php-master/Twilio/autoload.php';
        $sid = 'ACe0d7a2419951524b083b439a772e43a7';
        $token = 'c4b03f43b476c5c47ad084536d81f5f5';
        $client = new Client($sid, $token);
       try{ $client->account->messages->create(
            $userno,
            array(
                'from' => $from,
                'body' => $content
            )
        ); }catch (Exception $e) {
       echo "error";
    }



    }


    public function sendReminderSms($uid,$jobId,$smsLinksArray)
    {
        $shorturlController = new ShorturlController();
        $mobile =  $this->getmobileno($uid);
        $content = 'This is reminder that you have a booking coming up of Jobno.'.$jobId.' click here for detail '.$shorturlController->strurl($smsLinksArray['detail']);
        if($mobile != ''){  
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'JobReminderSms');
		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );	 
			// save sms in table.
			$this->insertSms($smsData);			
	        } catch (Exception $e) {
	           
	        }        
        }

    }

	
	// use in template/script  cron-on-day.phtml
    public function sendOnDayNotificationToFreelancerSms($jobFid,$jobId,$smsLinksArray)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($jobFid);
        $content = 'Plz confirm your arrival to work today for Job no.'.$jobId.' click here for Yes '.$shorturlController->strurl($smsLinksArray['yes']).' & for No '.$shorturlController->strurl($smsLinksArray['no']). ' LocumKit';
        if($mobile != ''){ 
		$smsData = array('uid' => $jobFid, 'mobile' => $mobile, 'sendfor' => 'OndayNotificationFreelancer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
	}    
	

	// use in template/script  job-search-progress.phtml
	public function jobInvitationFreeSms($freid,$jobId,$smsLinks)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($freid);

    //  $content = 'A new job has been posted. Job no.'.$jobId.' Login here for Accept '.$shorturlController->strurl($smsLinks). ' LocumKit';
        $content = 'A new job has been posted which matches your requirement. Job no.'.$jobId.' Login From here for Accept Job https://goo.gl/VeUcSz ';

        if($mobile != ''){
		$smsData = array('uid' => $freid, 'mobile' => $mobile, 'sendfor' => 'jobInvitationFreelancer');
        try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }
	
	
    // use in template/script  job-search-progress.phtml
    public function jobInvitationemployerSms($eid,$jobId,$smsLinks)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($eid);
        $content = 'Your job posting has been confirmed and is now live. Jobno.'.$jobId;

        if($mobile != ''){
		$smsData = array('uid' => $eid, 'mobile' => $mobile, 'sendfor' => 'jobInvitationEmployer');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }
	
    
	public function bookingConfirmationfre($uid,$jobId,$smsLinks)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
        $content = 'Booking has been confirmed for you. Job no. '.$jobId.' For more detail click here https://goo.gl/VeUcSz';

        if($mobile != ''){
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'bookingConfirmFreelancer');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }
    
       
	public function bookingConfirmationemp($uid,$jobId,$smsLinks)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
        $content = 'Someone applies for your job. Job no. '.$jobId.' For more detail click here https://goo.gl/VeUcSz';

        if($mobile != ''){
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'bookingConfirmEmployer');
           try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }
	
	    public function afterRegisterdEmpSms($mobile,$fname)
    {
        $shorturlController = new ShorturlController();
        $mobile = $mobile;
        $content = 'Hello '.$fname.', Welcome to LocumKit.  Click here https://goo.gl/sYbmQS';
        if($mobile != ''){
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
            } catch (Exception $e) {

            }
        }
    }
	
	
    public function cancelJobByEmpNotificationToFreelancerSms($fid,$jid)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($fid);

        $content = 'Employer has cancelled a job. Job no. '.$jid.'  Click here to login https://goo.gl/sYbmQS';

        if($mobile != ''){
		$smsData = array('uid' => $fid, 'mobile' => $mobile, 'sendfor' => 'cancelJob');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
            // save sms in table.
			$this->insertSms($smsData);
			} catch (Exception $e) {

            }
        }
    }

    public function cancelJobByEmpNotificationToEmployerSms($eid,$jid)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($eid);
        $content = 'You have cancelled a job. Job no. '.$jid.'  Click here to login https://goo.gl/sYbmQS';

        if($mobile != ''){
		$smsData = array('uid' => $eid, 'mobile' => $mobile, 'sendfor' => 'cancelJob');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }

    public function cancelJobByFreNotificationToFreelancerSms($fid,$jid)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($fid);

        $content = 'You have cancelled a job. Job no. '.$jid.'  Click here to login https://goo.gl/sYbmQS';

        if($mobile != ''){
		$smsData = array('uid' => $fid, 'mobile' => $mobile, 'sendfor' => 'cancelJob');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }

    public function cancelJobByFreNotificationToEmployerSms($eid,$jid)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($eid);
        $content = 'Freelancer has cancelled a job. Job no. '.$jid.'  Click here to login https://goo.gl/sYbmQS';
        if($mobile != ''){
		$smsData = array('uid' => $eid, 'mobile' => $mobile, 'sendfor' => 'cancelJob');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }

 
   public function sendweeklyReminderToFreelancerSms($uid,$smsContent)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
        $content = $smsContent.'  Click here to login https://goo.gl/sYbmQS';
       
 if($mobile != ''){
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'weeklyReminderToFreelancer');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    } 
   public function sendweeklyReminderToEmployerSms($uid,$smsContent)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
        $content = $smsContent.'  Click here to login https://goo.gl/sYbmQS';
       
 if($mobile != ''){
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'weeklyReminderToEmployer');
            try {
                $from = '+12293514378';
                require_once 'twilio-php-master/Twilio/autoload.php';
                $sid = 'ACe0d7a2419951524b083b439a772e43a7';
                $token = 'c4b03f43b476c5c47ad084536d81f5f5';
                $client = new Client($sid, $token);
                $client->messages->create(
                    $mobile,
                    array(
                        'from' => $from,
                        'body' => $content
                    )
                );
			// save sms in table.
			$this->insertSms($smsData);
            } catch (Exception $e) {

            }
        }
    }
	
		
	public function sendOnDayNotificationToEmployerSms($eid,$jobId)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($eid);
        $content = 'One of your freelancer just attend work today for Job no. '.$jobId.".";
        if($mobile != ''){ 
		$smsData = array('uid' => $eid, 'mobile' => $mobile, 'sendfor' => 'OndayNotificationEmployer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
	}
    
		// use in template/script  cron-feedback.phtml
	public function sendFeedbackNotificationFreSms($f_id,$jobId,$smsfeedback_link_fre)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($f_id);
        $content = 'Would now like you to leave feedback to the employer for Job no. '.$jobId.". click here ".$shorturlController->strurl($smsfeedback_link_fre);
        if($mobile != ''){ 
		$smsData = array('uid' => $f_id, 'mobile' => $mobile, 'sendfor' => 'FeedbackNotificationEmployer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
	}
    
			// use in template/script  cron-feedback.phtml
	public function sendFeedbackNotificationEmpSms($e_id,$jobId,$smsfeedback_link_emp)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($e_id);
        $content = 'Would now like you to leave feedback to the freelancer for Job no. '.$jobId.". click here ".$shorturlController->strurl($smsfeedback_link_emp);
        if($mobile != ''){ 
		$smsData = array('uid' => $e_id, 'mobile' => $mobile, 'sendfor' => 'FeedbackNotificationFreelancer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
    
    			// use in template/script  cron-feedback.phtml
	public function sendFeedbackNotificationOneWeekAlertSms($uid,$jobId,$smsfeedback_link,$role)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
		if($role == 2){
		$content = 'This is a reminder mail to inform you that you left to submit feedback on Job no. '.$jobId.". click here ".$shorturlController->strurl($smsfeedback_link);
		}
		if($role == 3){
		$content = 'This is a reminder mail to inform you that you left to submit feedback on Job no. '.$jobId.". click here ".$shorturlController->strurl($smsfeedback_link);
		}
        
        if($mobile != ''){ 
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'FeedbackNotificationFreelancer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
   }
    
    

	public function recievedFeedbackFreelancerNotificationSms($freId,$jobId,$link)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($freId);
        $content = 'You have received feedback from employer on Job no. '.$jobId.". Feedback will publish against your profile in the next 48 hours.";
        if($mobile != ''){ 
		$smsData = array('uid' => $freId, 'mobile' => $mobile, 'sendfor' => 'recievedFeedbackFreelancer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
	

	public function recievedFeedbackEmployerNotificationSms($empId,$jobId,$link)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($empId);
        $content = 'You have received feedback from Freelancer on Job no. '.$jobId.". Feedback will publish against your profile in the next 48 hours.";
        if($mobile != ''){ 
		$smsData = array('uid' => $empId, 'mobile' => $mobile, 'sendfor' => 'recievedFeedbackEmployer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
	
	public function sendPackageExpiredMailSms($userId)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($userId);
        $content = 'Your freelancer account is going to be expired in 7 days, please upgrade it and enjoy the freelancing at Locumkit.';
        if($mobile != ''){ 
		$smsData = array('uid' => $userId, 'mobile' => $mobile, 'sendfor' => 'PackageExpired');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
	
    	
	public function sendProfileSuspendNotificationToFreelancerSms($userId)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($userId);
        $content = 'Your guest profile has beed suspended from Locumkit.';
        if($mobile != ''){ 
		$smsData = array('uid' => $userId, 'mobile' => $mobile, 'sendfor' => 'SuspendNotificationToFreelancer');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
	
    	
	public function sendExpireFreezeNotificationSms($f_id,$job_id)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($f_id);
        $content = 'Job no.'.$job_id.' is locked just for another 5 minuted before it is available to all other applicable freelancer. Please apply now to confirm your booking from click here https://goo.gl/VeUcSz .';
        if($mobile != ''){ 
		$smsData = array('uid' => $f_id, 'mobile' => $mobile, 'sendfor' => 'ExpireFreeze');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }

	// use in template/script  cron-onday-expense.phtml
    public function sendExpenseNotificationSms($jobFid,$jobId,$smsLinks)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($jobFid);
        $content = 'Please can you enter the amount you have spent today . Click here to Add'. $shorturlController->strurl($smsLinks) .' LocumKit';
        if($mobile != ''){ 
		$smsData = array('uid' => $jobFid, 'mobile' => $mobile, 'sendfor' => 'addExpense');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
	}

		public function sendDisputeSubmitNotificationSms($uid,$jobId,$to,$from)
    {
        $shorturlController = new ShorturlController();
        $mobile = $this->getmobileno($uid);
        $content = 'Hi '.$to.' , '.$from.' submit dispute on feedback you submitted on job '.$jobId.' Please contact admin as soon as possible. LocumKit';
        if($mobile != ''){ 
		$smsData = array('uid' => $uid, 'mobile' => $mobile, 'sendfor' => 'DisputeSubmit');		
        try {        
            $from = '+12293514378';
            require_once 'twilio-php-master/Twilio/autoload.php';
            $sid = 'ACe0d7a2419951524b083b439a772e43a7';
            $token = 'c4b03f43b476c5c47ad084536d81f5f5';
            $client = new Client($sid, $token);
            $client->messages->create(
                $mobile,
                array(
                    'from' => $from,
                    'body' => $content
                )
            );
			// save sms in table.
			$this->insertSms($smsData);
	        } catch (Exception $e) {
	        }
        }
    }
	
	    
}