<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Mails;
	use Gc\Mail;
	use Gc\Registry;
	use GcFrontend\Controller\DbController as DbController;
	use GcFrontend\Controller\JobmailController as MailController;	
	use Gc\User\Job\Model as JobModel;
	

	Class MailsController
	{
		public function updateProfileMails($job_data)
		{
			$mailController = new MailController();
			$header = $mailController->mailHeader();
			$footer = $mailController->mailFooter();
			$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
        	$adminEmail = $configGet->get('mail_from');
			if($job_data)
			{
				
				$lastname = $job_data['lastname'];
				$firstname =$job_data['firstname'];
				$email =$job_data['email'];
				/******Send Email to user on Profile update ******/
				$message=$header.'
							<div style="padding: 25px 50px 30px; text-align: left;">
							<p>Hello '.$firstname.',</p>
							<p style="font-weight: normal;">Your account details have just been changed .</p>
		<p style="font-weight: normal;">If this was you then you can safely ignore this email .</p>
		<p style="font-weight: normal;">If this was not you, your account has been compromised. Please follow these steps.</p>
							
		<p>1- Reset your password</p>
		<p>2- Check your account details</p>
							<p><p>
							</div>'.$footer;
				$mail = new \Gc\Mail('utf-8', $message);
				$mail->getHeaders()->addHeaderLine('Content-type','text/html');
				$mail->setSubject('Profile update notification');
				$mail->setFrom($adminEmail,'Locumkit');
				$mail->addTo($email, $firstname);
				$mail->send();
						
				/***********SEND email end*************/
		        return TRUE;
			    
			}
		}
	}