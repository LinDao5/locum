<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\User; 
	use Gc\Registry;
	use Gc\Mvc\Controller\Action;
	use GcFrontend\Controller\DbController as DbController;
	use GcFrontend\Controller\JobmailController as MailController;	
	use FudugoApp\Controller\SubscriptionPackage\PackageController  as PackageController;

	Class RegisterController extends Action 
	{
		public function app_register_form_field($form_step){
			$dbController = new DbController();
			$adapter = $dbController->locumkitDbConfig();			
			switch ($form_step['step']) {
				case '1':
					$response = $this->get_form_field_setp_1($adapter);
					break;
				case '3':
					$response = $this->get_form_field_setp_3($form_step,$adapter);
					break;
				case '4':
					$response = $this->checkUserRecordExist($form_step,$adapter);
					break;
				case '5':
					$response = $this->saveUserRecords($form_step,$adapter);
					break;				
				default:
					$response = 0;
					//$response = $this->get_form_field_setp_3($form_step,$adapter);
					break;
			}
			return $response;
					
		}
		
		public function get_form_field_setp_1($adapter){	
			$packageController = new PackageController();
		    $step_1_field= $packageController->get_package_list($adapter);		
            $step_1_field['role'] = $this->get_user_role($adapter);
            $step_1_field['profession'] = $this->get_user_profession($adapter);            
            $step_1_field['payapal_api'] = $packageController->get_package_list_api($adapter);            
            return json_encode($step_1_field);
		}

		public function get_form_field_setp_3($form_step,$adapter){
			$role = isset($form_step['role']) ? $form_step['role'] : '';
			$profession = isset($form_step['profession']) ? $form_step['profession'] : '';
			if ($role != '' && $profession != '') {
				if($role == 3){
					$sqlFreQues = "SELECT id,equestion AS q,type_key,type_value,required_status,range_type_unit,range_type_condition from user_question WHERE (equestion != '' OR equestion != null) AND cat_id = '$profession' ORDER BY sort_order ASC";	
				    $quesDataObj = $adapter->query($sqlFreQues, $adapter::QUERY_MODE_EXECUTE);
				    $quesData['questions'] = $quesDataObj->toArray();
				    foreach ($quesData['questions'] as $key => $que) {
				    	if ($que['type_value'] != '') {
				    		$quesData['questions'][$key]['type_value'] = unserialize($que['type_value']);
				    	}
				    	if ($que['type_key'] == 5) {
				    		$quesData['questions'][$key]['type_key'] = 3;
				    	}
				    }
			    }elseif($role == 2){
			    	$sqlFreQues = "SELECT id,fquestion AS q,type_key,type_value,required_status,range_type_unit,range_type_condition from user_question WHERE (fquestion != '' OR fquestion != null) AND cat_id = '$profession' ORDER BY sort_order ASC";	
				    $quesDataObj = $adapter->query($sqlFreQues, $adapter::QUERY_MODE_EXECUTE);
				    $quesData['questions'] = $quesDataObj->toArray();
				    foreach ($quesData['questions'] as $key => $que) {
				    	if ($que['type_value'] != '') {
				    		$quesData['questions'][$key]['type_value'] = unserialize($que['type_value']);
				    	}
				    }
			    }			    
			    return json_encode($quesData);
			}else{
				return 0;
			}
		}


		public function get_user_role($adapter){
			$sql_role="select id,name from user_acl_role where id!='1' and (id = 2 OR id = 3 )";
            $role_obj = $adapter->query($sql_role, $adapter::QUERY_MODE_EXECUTE);
            $role_array = $role_obj->toArray();
            return $role_array;
		}

		public function get_user_profession($adapter){
			$sql_profession="select id,name from user_acl_professional";
            $profession_obj = $adapter->query($sql_profession, $adapter::QUERY_MODE_EXECUTE);
            $profession_data = $profession_obj->toArray();  
            $profession_array[0] = array('id' =>  3, 'name'=>'Optometry'); 
$profession_array = [];
if(!empty($profession_data)){
foreach($profession_data as $profession){
if($profession['id'] == 3 ){
$profession_array[0] = array('id' =>  3, 'name'=>$profession['name']); 
}
if($profession['id'] == 10 ){
//$profession_array[1] = array('id' =>  10, 'name'=>$profession['name']); 
}
}
}



         
            return $profession_array;
		}
		
		public function checkUserRecordExist($form_step,$adapter){
			$sql_check = '';
			if (isset($form_step['email']) && $form_step['email'] != '') {
				$email = $form_step['email'];
				$sql_check = "SELECT email FROM user WHERE email = '$email'";	
			}
			if (isset($form_step['username']) && $form_step['username'] != '') {
				$username = $form_step['username'];
				$sql_check = "SELECT login FROM user WHERE login = '$username'";
			}
			if ($sql_check != '') {
				$check_obj = $adapter->query($sql_check, $adapter::QUERY_MODE_EXECUTE);
	            $check_array = $check_obj->toArray();
	            if (empty($check_array)) {
	            	return 0;
	            }else{
	            	return 1;
	            }
			}
		}

		public function saveUserRecords($form_step,$adapter){

			
			$role_id = isset($form_step['user_type']['role']) ? $form_step['user_type']['role'] : ''; //role id			
			$profession_id = $profession = isset($form_step['user_type']['profession']) ? $form_step['user_type']['profession'] : ''; 
			$fname = isset($form_step['personal_info']['firstname']) ? $form_step['personal_info']['firstname'] : ''; 
			$lname = isset($form_step['personal_info']['lastname']) ? $form_step['personal_info']['lastname'] : ''; 
			$login = isset($form_step['personal_info']['username']) ? $form_step['personal_info']['username'] : ''; 	
		    $email = isset($form_step['personal_info']['email']) ? $form_step['personal_info']['email'] : '';		    
		    $company = isset($form_step['personal_info']['company_name']) ? $form_step['personal_info']['company_name'] : '';
		    $store = isset($form_step['personal_info']['store_name']) ? $form_step['personal_info']['store_name'] : '';
			$address = isset($form_step['personal_info']['address']) ? $form_step['personal_info']['address'] : '';
			$city = isset($form_step['personal_info']['town']) ? str_replace("'", "", $form_step['personal_info']['town']) : '';
			$zip = isset($form_step['personal_info']['postcode']) ? $form_step['personal_info']['postcode'] : '';
			$password = sha1(isset($form_step['personal_info']['password']) ? $form_step['personal_info']['password'] : '');			
			$telephone = isset($form_step['personal_info']['telephone']) ? $form_step['personal_info']['telephone'] : '';
			$mobile = isset($form_step['personal_info']['mobile_number']) ? $form_step['personal_info']['mobile_number'] : '';
			$aoc_id = isset($form_step['personal_info']['opl']) ? $form_step['personal_info']['opl'] : '';
			$minimum_rate = isset($form_step['personal_info']['min_rate']) ? serialize($form_step['personal_info']['min_rate']) : '';
			$max_distance = isset($form_step['personal_info']['max_distance']) ? $form_step['personal_info']['max_distance'] : '';
			$cet = isset($form_step['personal_info']['cet']) ? $form_step['personal_info']['cet'] : '';
			$goc = isset($form_step['personal_info']['goc']) ? $form_step['personal_info']['goc'] : '';
			$aop = isset($form_step['personal_info']['aop']) ? $form_step['personal_info']['aop'] : '';
			$inshurance_company = isset($form_step['personal_info']['inshurance_company']) ? $form_step['personal_info']['inshurance_company'] : '';
			$inshurance_no = isset($form_step['personal_info']['inshurance_no']) ? $form_step['personal_info']['inshurance_no'] : '';
			$inshurance_renewal_date = isset($form_step['personal_info']['inshurance_renewal_date']) ? $form_step['personal_info']['inshurance_renewal_date'] : '';
			$ans_val = isset($form_step['ans_val']) ? $form_step['ans_val'] : '';
			$store_info = isset($form_step['store_info']) ? $form_step['store_info'] : '';
			$package_id = isset($form_step['payment_info']['intent']) ? $form_step['payment_info']['intent'] : '';
			$price = isset($form_step['payment_info']['amount']) ? number_format($form_step['payment_info']['amount'],2) : '0';
			$payment_data = isset($form_step['payment_info']['authorization_id']) ? $form_step['payment_info']['authorization_id'] : '';
			$payment_type= isset($form_step['payment_info']['payment_type']) ? $form_step['payment_info']['payment_type'] : 'paypal';
			$store_id = isset($form_step['personal_info']['store_info_ques']) ? $form_step['personal_info']['store_info_ques'] : '';
			$store_data = isset($form_step['personal_info']['store_data_ques']) ? $form_step['personal_info']['store_data_ques'] : '';
			$store_value='';
			$store_info_ques = array();
			$store_info_data_ques= array();
	        //If Role is locum then add store id in array 
			$user_payment_data['payment_info'] = array(
					'authorization_id' 	=> $payment_data,
					'amount' 			=> $price,
					'payment_type' 		=> $payment_type,
					'intent' 			=> $package_id
				);		


	        if($role_id==2){

				if(!empty($store_data)){
					foreach ($store_data as $key => $value) {
						if($key!=""){
						    $store_info_data_ques[] = $value;
						}			
						
					}
					$store_info_data_ques=implode(",", $store_info_data_ques);
			    }

			    if(!empty($store_id)){
					foreach ($store_id as $key => $value) {
						if($value==1){
							$value_replaced=str_replace("_"," ",$key);
						    $store_info_ques[] = $value_replaced;
						}
					}
					$store_info_ques=implode(",", $store_info_ques);
			    }
		    }else{
		    	 $company = isset($form_step['personal_info']['store_name']) ? $form_step['personal_info']['store_name'] : '';
		    	$store_info_ques=$store_id;
		    	$store_info_ques=implode(",", $store_info_ques);
		    	$store_info_data_ques='';
		    }
		  

			//Register new user 
			$sql_user = "INSERT INTO user (firstname,lastname,email,login,password,user_acl_role_id,user_acl_profession_id,user_acl_package_id,active) VALUES ('$fname','$lname','$email','$login','$password','$role_id','$profession','$package_id','0')";
			$insert_user_data = $adapter->query($sql_user, $adapter::QUERY_MODE_EXECUTE);
			$uid = $insert_user_data->getGeneratedValue();			
			if ($uid != '') {
				// Extra information
				$sql_user_extra_info = "INSERT INTO user_extra_info (uid,aoc_id,mobile,address,city,zip,telephone,company,minimum_rate,max_distance,cet,goc,aop,inshurance_company,inshurance_no,inshurance_renewal_date,store_id,store_data) values('$uid','$aoc_id','$mobile','$address','$city','$zip','$telephone','$company','$minimum_rate','$max_distance','$cet','$goc','$aop','$inshurance_company','$inshurance_no','$inshurance_renewal_date','$store_info_ques','$store_info_data_ques')";
				$insert_extra_data = $adapter->query($sql_user_extra_info, $adapter::QUERY_MODE_EXECUTE);

				//Questions answers
				// check for user ans stored or not
				$sql_user_ans ="SELECT user_id FROM user_answer WHERE user_id='$uid'";
				$result_user_ans = $adapter->query($sql_user_ans, $adapter::QUERY_MODE_EXECUTE);				
				$count_useransdata=$result_user_ans->count();
				if(!empty($ans_val)){
					foreach ($ans_val as $key => $value) {
						if (is_array($value)) {
							$value = implode(',', $value);
						}
						if($count_useransdata>0){
							$sqlString_ansup="UPDATE user_answer SET type_value='$value' WHERE user_id='$uid' AND question_id='$key'";
							$results_ansup = $adapter->query($sqlString_ansup, $adapter::QUERY_MODE_EXECUTE);
						}else{
							$sqlString_insert_ans="INSERT INTO user_answer (user_id,question_id,type_value) VALUES('$uid','$key','$value')";
							$results_ans = $adapter->query($sqlString_insert_ans, $adapter::QUERY_MODE_EXECUTE);
						}
					}
				}
				if($role_id==3){
				//Save store info
				$store_name = isset($store_info['name'])?$store_info['name']:'';
				$store_address = isset($store_info['address'])?$store_info['address']:'';
				$store_town = isset($store_info['town'])? str_replace("'", "", $store_info['town']):'';
				$store_postcode = isset($store_info['postcode'])?$store_info['postcode']:'';
				$store_start_time = '';
				$store_end_time = '';
				$store_lunch_time = '';
				foreach ($store_info as $key => $store_time) {
					if (strpos($key, '_start_time') !== false) {
						$day = ucfirst(str_replace('_start_time', '', $key));
						$store_start_time[] = array($day => $store_time);
					}
					if (strpos($key, '_end_time') !== false) {
						$day = ucfirst(str_replace('_end_time', '', $key));
						$store_end_time[] = array($day => $store_time);
					}
					if (strpos($key, '_lunch_time') !== false) {
						$day = ucfirst(str_replace('_lunch_time', '', $key));
						$store_lunch_time[] =array($day => str_replace('00:','',$store_time));
					}
				}
				if (!empty($store_start_time)) {
					$store_start_time = serialize($store_start_time);
				}
				if (!empty($store_end_time)) {
					$store_end_time = serialize($store_end_time);
				}
				if (!empty($store_lunch_time)) {
					$store_lunch_time = serialize($store_lunch_time);
				}

				$sql_emp_store="INSERT INTO employer_store_list (emp_id,emp_store_name,emp_store_address,emp_store_region,emp_store_zip,store_start_time,store_end_time,store_lunch_time) VALUES ('$uid','$store_name','$store_address','$store_town','$store_postcode','$store_start_time','$store_end_time','$store_lunch_time')";
				$resultsemp = $adapter->query($sql_emp_store, $adapter::QUERY_MODE_EXECUTE);
				}
				
				//send activation mail to users
				$mailController = new MailController();
				$header = $mailController->mailHeader();
				$footer = $mailController->mailFooter();
				$host = $this->getRequest()->getUri()->getHost();
				$serverUrl = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('ServerUrl');
				$configGet = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
				$adminEmail = $configGet->get('mail_from');

				if($profession_id != ''){
				    $sqlPro = "SELECT name , description from user_acl_professional WHERE id='$profession_id'";
				    $sqlProData = $adapter->query($sqlPro, $adapter::QUERY_MODE_EXECUTE);
				    $sqlProval = $sqlProData->current();
				}
				if ($role_id == 2) {
					// insert payment  details 
					$packageController = new PackageController();
					$insert_payment_details = $packageController->insert_user_package($user_payment_data,$uid,$adapter,'register');		     	
				}
				if ($role_id == 3) {
					$message = $header.'
							<div style="padding: 25px 50px 5px; text-align: left;">
							<p>Hello Employer '.$fname.' '.$lname.',</p>
							<p>Thank you for joining Locumkit.</p><p> We have received your account application and your details are currently being verified by our team. Verification can take up to 48 hours and once this process is complete we will notify you.</p><p> Please note that during this process you will be unable to access any of the features of the site</p>
							<!--<p>Thank you for using Locumkit.</p>
							<p>Please do not respond to this email, if you have any queries please contact us <a href="http://'.$host.'/contact">here</a>.</p>-->
							<p><p>
							</div>'.$footer;

					if($profession_id != ''){
						$Professiontr = '<tr><th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Profession</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$sqlProval['name'].'</td></tr>';
					}else{
						$Professiontr = '';
					}


					$message2=$header.'
						<div style="padding: 25px 50px 30px; border-right: 2px solid #000; border-left: 2px solid #000;text-align: left; width:84.2%">
						<p>Hello <b>Admin</b>,</p>
						<p>A new employer account has been created which needs to be verified. The employer details are listed below:</p>
						<table width="100%" style="border-collapse: collapse;  border: 1px solid black;  text-align:left;  padding:5px;">
						<tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Email</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$email.'</td>
						  </tr>
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">First Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$fname.'</td>
						  </tr>
						   <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Last Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$lname.'</td>
						  </tr>                                         
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">ID</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$uid.'</td>
						  </tr>'.$Professiontr.'
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Store Name</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$company.'</td>
						  </tr>
						  <tr>
							<th style=" border: 1px solid black;  text-align:left;  padding:5px; width: 200px;">Telephone number</th>
							<td style=" border: 1px solid black;  text-align:left;  padding:5px;">'.$telephone.'</td>
						  </tr>					  
						  					  
						</table><br/>
						<p><p>
						</div>'.$footer;
					try{	
						$mail = new \Gc\Mail('utf-8', $message);
				        $mail->getHeaders()->addHeaderLine('Content-type','text/html');
				        $mail->setSubject('Welcome to LocumKit');
				        $mail->setFrom($adminEmail);
				        $mail->addTo($email, $fname);
							
				        $mail->send();
							
					    $mail2 = new \Gc\Mail('utf-8', $message2);
				        $mail2->getHeaders()->addHeaderLine('Content-type','text/html');
				        $mail2->setSubject('New employer registration - Needs to be verified');
				        $mail2->setFrom($email, $fname);
						//$mail->addTo('pallavi.fwork@gmail.com', 'Admin');
						$mail2->addTo($adminEmail);
						$mail2->send();	
					}
					catch (Exception $e) {
					}
					
					// send sms start 
		          	// $jobsmsController->afterRegisterdEmpSms($mobile,$fname);
		            // send sms end
				}
				//success
				$user_return_data = array(
					'id' => $uid , 
					'firstname' => $fname, 
					'lastname' => $lname,
					'email' => $email,
					'role' => $role_id,
					'profession' => $profession 
				);	
			}

				
			$user_return_data = array(
					'id' => $uid , 
					'firstname' => $fname, 
					'lastname' => $lname,
					'email' => $email,
					'role' => $role_id,
					'profession' => $profession 
				);	
			return json_encode($user_return_data);
			
		}
	}