<?php
    /**
    * Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
    */
    namespace FudugoApp\Controller\Feedback;
    use Gc\Registry;
    use GcFrontend\Controller\DbController as DbController;
    use Gc\User\Feedback\Model as FeedbackModel;
    use GcFrontend\Controller\JobmailController as MailController;
    use Gc\User\JobReminder\OnDayModel as OnDayModule;
    use Gc\User\Collection as UserCollection;
    use Gc\User\Feedback\Frontend\Collection as FeedbackQuestion;
    use GcFrontend\Helper\FinanceHelper as FinanceHelper;
    use FudugoApp\Controller\Job\JobController as JobController;
    use FudugoApp\Controller\Helper\HelperController as HelperController; 
    use Gc\User\Role\Model; 
    use GcFrontend\Controller\FunctionsController;

    Class FeedbackController
    {
        public function feedback_action($user_data)
        {
            $dbController   = new DbController();           
            $adapter        = $dbController->locumkitDbConfig();
            $user_id        = $user_data['user_id'];
            $user_role      = $user_data['user_role'];
            $page_id        = $user_data['page_id'];
            $job_id         = $user_data['job_id'];
            $user_profession= $user_data['user_profession'];
            $feedback_response = '';
            switch ($page_id) {
                case 'form-info':
                    $feedback_response = $this->get_feedback_form($user_id,$user_role,$user_profession,$job_id,$adapter);
                    break;
                case 'save-feedback':                   
                    $feedback_data = isset($user_data['data']) ? $user_data['data'] : null;
                    $feedback_response = $this->save_feedback($user_id,$user_role,$user_profession,$job_id,$feedback_data,$adapter);
                    break;
                case 'feedback-list':
                    $feedback_response = $this->feedback_list($user_id,$user_role,$user_profession,$adapter);
                    break;
            }
            return $feedback_response;
        }

        public function get_feedback_form($user_id,$user_role,$user_profession,$job_id,$adapter)
        {
            
            $financeHelper      = new FinanceHelper();
            $feedbackQuestion   = new FeedbackQuestion();
            $userCollection     = new UserCollection();
            $jobController      = new JobController();
            $helpController     = new HelperController();

            if ($user_role == 2) {
                $job_info           = $jobController->get_job_info_by_id($job_id, $adapter);
                $emp_id             = $job_info['e_id'];
                $empDataObj         = $userCollection->getUserById($emp_id);
                $empName = '';
                $empEmail = '';
                if (!empty($empDataObj)) {
                    foreach ($empDataObj as $key => $empData) {
                        $empName    = $empData->getFirstname() . ' ' . $empData->getLastname();
                        $empEmail   = $empData->getEmail();
                    }
                }
                
                $job_info['job_rate'] = $helpController->formate_price($job_info['job_rate']);
                $allFeedbackQusArray  = $feedbackQuestion->getFreelancerQus($user_profession); 
                $feedbackQusArray     = array(); 
                foreach ($allFeedbackQusArray as $key => $feedbackQus) {                    
                    $feedbackQusArray[] = array(
                            'qus_id' => $feedbackQus->getFdQusId(),
                            'qus'    => $feedbackQus->getFdQusFre()
                        );
                }
                
                $feedback_form_info = array(
                        'u_id'      => $emp_id,
                        'u_name'    => $empName,
                        'u_email'   => $empEmail,
                        'job_date'  => $job_info['job_date'],
                        'job_rate'  => $job_info['job_rate'],
                        'feed_qus'  => $feedbackQusArray
                    );

                return json_encode($feedback_form_info);
            }
            if ($user_role == 3) {
                $job_info           = $jobController->get_job_info_by_id($job_id, $adapter);
                $fre_id             = $this->get_fre_id_by_job_id($job_id,$adapter);
                $freDataObj         = $userCollection->getUserById($fre_id);
                $freName = '';
                $freEmail = '';
                if (!empty($freDataObj)) {
                    foreach ($freDataObj as $key => $freData) {
                        $freName = $freData->getFirstname() . ' ' . $freData->getLastname();
                        $freEmail = $freData->getEmail();
                    }
                }
                
                $job_info['job_rate'] = $helpController->formate_price($job_info['job_rate']);
                $allFeedbackQusArray  = $feedbackQuestion->getEmployerQus($user_profession); 
                $feedbackQusArray     = array(); 
                foreach ($allFeedbackQusArray as $key => $feedbackQus) {                    
                    $feedbackQusArray[] = array(
                            'qus_id' => $feedbackQus->getFdQusId(),
                            'qus'    => $feedbackQus->getFdQusEmp()
                        );
                }
                
                $feedback_form_info = array(
                        'u_id'      => $fre_id,
                        'u_name'    => $freName,
                        'u_email'   => $freEmail,
                        'job_date'  => $job_info['job_date'],
                        'job_rate'  => $job_info['job_rate'],
                        'feed_qus'  => $feedbackQusArray
                    );              
                return json_encode($feedback_form_info);
            }           
        }

        public function save_feedback($user_id,$user_role,$user_profession,$job_id,$feedback_data,$adapter)
        {   
            $feedbackModel      = new FeedbackModel();
            $mailController     = new MailController();
            $feedback_status = '<div class="notification error">Something is wrong, please restart app and try again.</div>';   
                
            if(!empty($feedback_data['feedback'])){
                /* feedback form */
                $feedback_name      = $feedback_data['feedback_name'];
                $feedback_email     = $feedback_data['feedback_email'];
                $feedback           = $feedback_data['feedback'];
                $rating             = 0;
                $feedbackQusId      = array();
                $feedbackAns        = array();
                $$feedbackQusArray  = array();
                $feedbackQus        = $feedback_data['feedbackQus'];                
                $i = 0;
                foreach ($feedback as $key => $feedback_rate) {
                    $rating             += $feedback_rate;
                    $feedbackQusId[]    = $key;
                    $feedbackQusArray[] = $feedbackQus[$i]['qus'];   
                    $feedbackAns[]      = $feedback_rate;   
                    $i++; 
                }               
                $rating = $rating/$i;
                $feedback_user_id   = $user_id;
                $feedback_job_id    = $job_id;
                $feedback_comment   = "";
                $user_role          = $feedback_data['user_role'];
                $user_cat           = $feedback_data['user_cat'];
                
                $feedback_to_user_id= $feedback_data['feedback_to_user_id'];

                /* merge rate vale with qus ids */  
                $feedbackArray = array();
                foreach ($feedbackQus as $key => $feedbackQus) {
                    $feedbackArray[] = array(
                            'qusId'     => $feedbackQusId[$key],
                            'qus'       => $feedbackQusArray[$key],
                            'qusRate'   => $feedbackAns[$key]
                        );
                }
                

                $feedback = serialize($feedbackArray);
                $checkFeedbackData = $feedbackModel->checkFeedback($feedback_job_id,$feedback_user_id,$user_role);
                
                if ($checkFeedbackData && $checkFeedbackData == 1) {
                    $feedback_status = '<div class="notification error">You have already submitted feedback.</div>';
                }else{
                    if ($user_role == 2) {
                        $isertArray = array(
                                'emp_id'    => $feedback_to_user_id,
                                'fre_id'    => $feedback_user_id,
                                'j_id'      => $feedback_job_id,
                                'rating'    => $rating,
                                'feedback'  => $feedback,
                                'comments'  => $feedback_comment,
                                'user_type' => $user_role,
                                'cat_id'    => $user_cat,
                            ); 
                        $feedbackId = $feedbackModel->save($isertArray);
                        $mailController->recievedFeedbackEmployerNotification($feedbackId,$isertArray,$adapter);
                    }elseif($user_role == 3){
                        #Employer feedback
                        $isertArray = array(
                                'emp_id'    => $feedback_user_id,
                                'fre_id'    => $feedback_to_user_id,
                                'j_id'      => $feedback_job_id,
                                'rating'    => $rating,
                                'feedback'  => $feedback,
                                'comments'  => $feedback_comment,
                                'user_type' => $user_role,
                                'cat_id'    => $user_cat,
                            ); 
                        $feedbackId = $feedbackModel->save($isertArray);
                        $mailController->recievedFeedbackFreelancerNotification($feedbackId,$isertArray,$adapter);
                    }
                    $feedback_status = '';
                }
            }
            return $feedback_status;
        }

        public function feedback_list($user_id,$user_role,$user_profession,$adapter)
        {           
            $functionsController    = new FunctionsController();
            $userCollection         = new UserCollection();
            $jobController          = new JobController();  
            $helperController       = new HelperController(); 
            $userId                 = $user_id;
            $userRoleId             = $user_role;
            $userInfo               = $userCollection->getUserById($userId);
            if ($userRoleId == 2) {
                $currentFeedbackData =  $functionsController->getFeedbackByUserId($adapter, $userId, 3);
                $userType = 'Employer(s)';
            }elseif($userRoleId == 3){
                $currentFeedbackData =  $functionsController->getFeedbackByUserId($adapter, $userId, 2);
                $userType = 'Locum(s)';
            }
            $totalFeedback  = count($currentFeedbackData);          
            $perRating_new  = round($functionsController->getOverallRating($currentFeedbackData)) ;
            $perRating_star = round(($functionsController->getOverallRating($currentFeedbackData)/100)*5,1) ;

            foreach ($currentFeedbackData as $key => $feedbackData) {
                $qus_data = unserialize($feedbackData['feedback']);
                $job_info = $jobController->get_job_info_by_id($feedbackData['j_id'], $adapter);
                if ($user_role == 2) {
                    $userDataObj        = $userCollection->getUserById($feedbackData['emp_id']);
                }elseif ($user_role == 3){
                    $userDataObj        = $userCollection->getUserById($feedbackData['fre_id']);
                }
                
                $userName = '';
                $userEmail = '';
                if (!empty($userDataObj)) {
                    foreach ($userDataObj as $k => $userData) {
                        $userName   = $userData->getFirstname() . ' ' . $userData->getLastname();
                        $userEmail  = $userData->getEmail();
                    }
                }else{
                    if ($user_role == 2) {
                        $deleteduser    = "SELECT * from user_leavers_table WHERE uid = ".$feedbackData['emp_id']."";
                    }else{
                        $deleteduser    = "SELECT * from user_leavers_table WHERE uid = ".$feedbackData['fre_id']."";
                    }
                    $deleteduserView = $adapter->query($deleteduser, $adapter::QUERY_MODE_EXECUTE);
                    $del_user   = $deleteduserView ->current();  
                    if($del_user) {
                        $userName   = $del_user['user_name'];
                        $userEmail  = $del_user['user_email'];
                    }    
                }

                $currentFeedbackData[$key]['feedback']  = $qus_data;
                $currentFeedbackData[$key]['j_rate']    = $helperController->formate_price($job_info['job_rate']);
                $currentFeedbackData[$key]['j_date']    = $job_info['job_date'];
                $currentFeedbackData[$key]['user_name'] = $userName;

            }

            if(!empty($currentFeedbackData)){
                foreach($currentFeedbackData as $k=>$currentFeedback){
                    $currentFeedbackData[$k]['created_date'] = date('d/m/Y', strtotime($currentFeedback['created_date']));
                }
            }
        
            
            $feedback_list_array = array(
                    'data' => $currentFeedbackData,
                    'total_count'   => $totalFeedback,
                    'average_rating' => $perRating_new,
                    'average_rating_star' => $perRating_star
                );
            return json_encode($feedback_list_array);
        }

        public function get_fre_id_by_job_id($job_id,$adapter)
        {
            $sql_job_action = "SELECT f_id FROM  job_action WHERE job_id='$job_id' AND action = '4'";
            $job_action     = $adapter->query($sql_job_action, $adapter::QUERY_MODE_EXECUTE);
            $fre_id = $job_action->current();
            return $fre_id->f_id;
        }

        public function get_feedback_by_id($data){
            $feedback_id    = $data['feedback_id'];            
            $user_id        = $data['user_id'];            
            $role_id        = $data['role_id'];      
            $dbController   = new DbController(); 
            $userCollection = new UserCollection();
            $jobController  = new JobController(); 
            $helperController = new HelperController(); 

            $adapter        = $dbController->locumkitDbConfig();
            $sql_feedback   = "SELECT * FROM  job_feedback WHERE feedback_id = '$feedback_id'";
            $feedback_data  = $adapter->query($sql_feedback, $adapter::QUERY_MODE_EXECUTE);
            $feedback_obj   = $feedback_data->toArray();
            $feedback_obj[0]['feedback'] = unserialize($feedback_obj[0]['feedback']);

            if ($role_id == 2) {
                $userDataObj   = $userCollection->getUserById($feedback_obj[0]['emp_id']);
            }elseif ($role_id == 3){
                $userDataObj   = $userCollection->getUserById($feedback_obj[0]['fre_id']);
            }   
            
            
            $userName = '';
            $userEmail = '';
            if (!empty($userDataObj)) {
                foreach ($userDataObj as $k => $userData) {
                    $userName   = $userData->getFirstname() . ' ' . $userData->getLastname();
                    $userEmail  = $userData->getEmail();
                }
            }

            $feedback_obj[0]['job_info'] = $jobController->get_job_info_by_id($feedback_obj[0]['j_id'], $adapter);
            $feedback_obj[0]['job_info']['job_rate'] = $helperController->formate_price($feedback_obj[0]['job_info']['job_rate']);
            $feedback_obj[0]['created_date'] = date('d/m/Y', strtotime($feedback_obj[0]['created_date']));
            $feedback_obj[0]['opposition_name']     = $userName;
            $feedback_obj[0]['opposition_email']    = $userEmail;
            return json_encode($feedback_obj[0]);
        }

    }