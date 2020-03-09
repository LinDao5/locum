<?php
/**
 * Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
 */

namespace FudugoApp\Controller;

use Gc\Mvc\Controller\Action;
use FudugoApp\Controller\User\UserLoginController as UserLoginController ;
use FudugoApp\Controller\User\RegisterController as RegisterController ;
use FudugoApp\Controller\User\UserController as UserController ;
use FudugoApp\Controller\User\Finance\FinanceController as FinanceController ;
use FudugoApp\Controller\User\FeedbackController as FeedbackController ;
use FudugoApp\Controller\User\BlockedUserController as BlockedUserController ;
use FudugoApp\Controller\Store\StoreController as StoreController ;
use FudugoApp\Controller\Store\ManageStoreController as ManageStoreController ;
use FudugoApp\Controller\Job\JobController as JobController ;
use FudugoApp\Controller\Job\SearchController as SearchController ;
use FudugoApp\Controller\Job\PrivateJobController as PrivateJobController ;
use FudugoApp\Controller\Finance\FinanceAppController as FinanceAppController ;
use FudugoApp\Controller\Finance\IncomeAppController as IncomeAppController ;
use FudugoApp\Controller\Job\JobActionController as JobActionController;
use FudugoApp\Controller\Feedback\FeedbackController as UserFeedbackController;
use FudugoApp\Controller\SubscriptionPackage\PackageController  as PackageController;
/**
 * Index controller for module Application
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */

class IndexController extends Action
{
    protected $web_service_key = null;
    protected $web_service_pass = null;    
    protected $app_page_type = null;    
    public function index()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
     
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
     
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
     
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
     
            exit(0);
        }
        
        $this->web_service_key = isset($_GET['fudugo_key']) ? $_GET['fudugo_key'] : '';
        $this->web_service_pass = isset($_GET['fudugo_password']) ? $_GET['fudugo_password'] : '';
        $this->app_page_type = isset($_GET['page']) ? $_GET['page'] : '';

        if ($this->web_service_key == 'TG9jdW1raXQtQXBwLUZ1ZHVnby1EZXZlbG9wZXItU3VyYWotV2FzbmlrLTIwMTctYXdlc29tZQ==' && $this->web_service_pass == 'TG9jdW1raXRAbGV0bWVpbkZ1ZHVnb19BcHBAMjAxNyE=') {

            $json = file_get_contents('php://input');
            $json_data = json_decode($json, true); 
            $userLogin = new UserLoginController();
            $registerController = new RegisterController();
            $userController = new UserController();            
            $blockeduserController = new BlockedUserController();
            $financeController = new FinanceController();            
            $feedbackController = new FeedbackController(); 
            $storeController = new StoreController(); 
            $jobController = new JobController(); 
            $privatejobController = new PrivateJobController();
            $searchController = new SearchController();         
            $storemanageController = new ManageStoreController();
            $manageIncomeController = new IncomeAppController();
            $jobActionController = new JobActionController(); 
            $userFeedbackController = new UserFeedbackController(); 
            $packageController = new PackageController();
            $financeAppController = new FinanceAppController();         
           
                switch ($this->app_page_type) {
                    case 'login':                    
                        $response = $userLogin->app_user_login($json_data);
                        break;
                    case 'update-session':                    
                        $response = $userLogin->app_user_session_update($json_data);
                        break;
                    case 'register-form':                    
                        $response = $registerController->app_register_form_field($json_data);
                        break;
                    case 'is-profile-completed':                    
                        $response = $userController->is_profile_completed($json_data);
                        break;
                    case 'block-date':                    
                        $response = $userController->get_block_date($json_data);
                        break;
                    case 'check-user-availability':                    
                        $response = $userController->check_user_availability_by_date($json_data);
                        break;
                    case 'current-month-booking':                    
                        $response = $userController->get_current_month_bookings($json_data);
                        break;
                    case 'manage-calendar':                    
                        $response = $userController->set_fre_calendar($json_data);
                        break;
                    case 'get-min-rate-date':                    
                        $response = $userController->get_fre_min_rate($json_data);
                        break;
                    case 'finance-summary':                    
                        $response = $financeController->get_finance_summary($json_data);
                        break;
                    case 'finance-summary-chart':                    
                        $response = $financeController->get_finance_summary_chart($json_data);
                        break;
                    case 'feedback-summary':                    
                        $response = $feedbackController->feedback_summary_data($json_data);
                        break;
                    case 'edit-profile':                    
                        $response = $userController->get_user_personal_info($json_data);
                        break;
                    case 'update-profile':                    
                        $response = $userController->update_user_personal_info($json_data);
                        break;
                    case 'edit-questions':              
                        $response = $userController->get_user_questions_info($json_data);
                        break;
                    case 'update-questions':                    
                        $response = $userController->update_user_answers_info($json_data);
                        break;
                    case 'store-list':                    
                        $response = $userController->get_user_store_list($json_data);
                        break;
                    case 'manage-blocked-user':                    
                        $response = $blockeduserController->get_blocked_user($json_data);
                        break;
                    case 'manage-package':                    
                        $response = $packageController->manage_package($json_data);
                        break;
                    case 'search-town':                    
                        $response = $userController->town_search($json_data);
                        break;
                    /* Employer Section */
                    case 'multi-store':                    
                        $response = $storeController->get_stores($json_data);
                        break; 
                     /* Manage Store */
                    case 'manage-stores':                    
                        $response = $storemanageController->get_stores_list($json_data);
                        break;   
                        /* Manage Incomes */
                    case 'manage-incomes':               
                        $response = $manageIncomeController->manage_income($json_data);
                        break;   
                     /*** Job Management**/             
                    case 'post-job':
                        $response = $jobController->post_job($json_data);
                        break; 
                    case 'search-freelancer':                    
                        $response = $searchController->search_match_freelancer($json_data);
                        break;  
                    case 'send-job-invitation':                    
                        $response = $jobController->send_job_invitation($json_data);
                        break;
                    case 'add-private-freelancer':                    
                        $response = $jobController->add_private_freelancer($json_data);
                        break; 
                    case 'delete-private-freelancer':                    
                        $response = $jobController->delete_private_freelancer($json_data);
                        break;
                    case 'job-list':                    
                        $response = $jobController->get_job_list($json_data);
                        break;
                    case 'job-view':                    
                        $response = $jobController->get_job_detail_info($json_data);
                        break;
                    case 'job-action':                    
                        $response = $jobController->job_action($json_data);
                        break; 
                    case 'private-job':                    
                        $response = $privatejobController->manage_private_job($json_data);
                        break;
                    case 'private-job-view':                    
                        $response = $privatejobController->view_private_job($json_data);
                        break;
                    case 'private-job-attend':                    
                        $response = $privatejobController->attend_private_job($json_data);
                        break;

                    case 'finance':                    
                        $response = $financeAppController->get_finance($json_data);
                        break;
                    case 'logout':                    
                        $response = $userLogin->logoutMobile($json_data);
                        break;
                    case 'user-job-action':                    
                        $response = $jobActionController->jobAction($json_data);
                        break;
                    case 'user-feedback-action':                    
                        $response = $userFeedbackController->feedback_action($json_data);
                        break;
                    case 'get-feedback-by-id':            
                        $response = $userFeedbackController->get_feedback_by_id($json_data);
                        break;
                    case 'user-cancellation-rate':                    
                        $response = $userController->getCancellationRate($json_data);
                        break;
                    case 'user-permission':                    
                        $response = $userController->getUserPermission($json_data);
                        break;
                    case 'update-paasword':                    
                        $response = $userController->updateUserPassword($json_data);
                        break;
                    default:
                        $response = "Invalid request";
                        break;
                }          
           
            
            echo $response;  

        }else{
            echo " OOOPPSSSSSS.....RISTRICTED..... ";
        }        
        die();
    }
    
}