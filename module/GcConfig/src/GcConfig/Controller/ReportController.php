<?php
namespace GcConfig\Controller;
use Gc\Mvc\Controller\Action;
use Gc\User\Role;
use Gc\User\Leaveuser\Collection as LeaveuserCollection;
use Gc\User\Collection as UserCollection; 
use Gc\User\PrivateUserJob as PrivateUserJobCollection;
use Gc\User\Job\Collection as JobCollection;
use Gc\User\JobAction\Collection as JobActionCollection;
use Gc\User\Package\Collection as PackageCollection;
use Gc\User\FreelancerPrivateJob as FreelancerPrivateJobCollection;
use Gc\User\PrivateFreelancerInfo as PrivateFreelancerInfo;
use GcFrontend\Controller\FunctionsController as FunctionController;
use GcFrontend\Controller\DbController as DbController;
use Gc\User\PackageRateReport;

/**
 * Role controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class ReportController extends Action
{
    protected $aclPage = array('resource' => 'settings', 'permission' => 'report');

    public function indexAction()
    {
        return ;
    }
    public function packageReportAction()
    {
    	return ;
    }
    public function newUserAction()
    {
    	return ;
    }
    public function lastLoginUsersAction()
    {
    	return ;
    }
    public function leaveUserAction()
    {        
        $startdate  = isset($_GET['startdate']) ? $_GET['startdate'] : null;
        $enddate    = isset($_GET['enddate']) ? $_GET['enddate'] : null;        
        $leaveuserCollection = new LeaveuserCollection();
        $leaveUsers = $leaveuserCollection->getLeaveusers(false,$startdate,$enddate);
        return array('leaveUsers' => $leaveUsers);
    }
    /* Job Report Employer*/
    public function empJobReportAction($startdate = null,$enddate = null)
    {
        if ($startdate == null) {
            $startdate  = isset($_GET['startdate']) ? $_GET['startdate'] : null;
        }
        if ($enddate == null) {
            $enddate    = (isset($_GET['enddate']) && $_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d'); 
        } 
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUsers = $userCollection->getEmployerUsers(); 

        $allRecord = array();
        foreach ($empUsers as $key => $user) {
            $userName = $user->getName();
            $postJobCount = $this->countJobsByUid($user->getId(),$startdate,$enddate);
            $acceptedJobCount = $this->countAcceptedJobsByUid($user->getId(),$startdate,$enddate);

            
            $successRate = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $empCancellationRate = $functionController->getEmpCancellationRate($user->getId(),$adapter,$startdate,$enddate);
            $privateUserReqSendCount = $this->countPrivateUserRequestSendByUid($user->getId(),$startdate,$enddate);
            
            $allRecord[] = array(
                    'emp_id'            => $user->getId(),
                    'name'              => $userName,
                    'job_list'          => $postJobCount,
                    'job_accepted'      => $acceptedJobCount,
                    'success_rate'      => $successRate,
                    'cancellation_rate' => $empCancellationRate,
                    'private_user_req'  => $privateUserReqSendCount,
                );
        }
        
        return array('allRecord' => $allRecord);
    }
    /* Get total job post count */
    public function countJobsByUid($uid,$startdate=null,$enddate=null){        
        $jobCollection = new JobCollection();
        $jobCount = $jobCollection->getPostJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted */
    public function countAcceptedJobsByUid($uid,$startdate=null,$enddate=null){
        $jobCollection = new JobCollection();
        $jobCount = $jobCollection->getAcceptedJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total private user request send */
    public function countPrivateUserRequestSendByUid($uid,$startdate=null,$enddate=null){
        $privateUserCollection = new PrivateUserJobCollection();        
        $privateUserCount = $privateUserCollection->getPrivateUserRequestSendCount($uid,$startdate,$enddate); 
        return $privateUserCount;
    }

    /* Job Report Per Month*/
    public function empMonthJobReport($startdate,$enddate)
    {
        $userCollection = new UserCollection();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUsers = $userCollection->getEmployerUsers($startdate,$enddate); 
        
        foreach ($empUsers as $key => $user) {
            $userName = $user->getName();
            $postJobCount = $this->countMonthJobs($user->getId(),$startdate,$enddate);
            $acceptedJobCount = $this->countMonthAcceptedJobs($user->getId(),$startdate,$enddate);
            $successRate = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $cancellationRate = $this->getEmpCancellationRate($user->getId(),$startdate,$enddate,$adapter);
            $privateUserReqSendCount = $this->countMonthPrivateUserRequestSend($user->getId(),$startdate,$enddate);
            
            $allRecord[] = array(
                    'emp_id' => $user->getId(),
                    'name' => $userName,
                    'job_list' => $postJobCount,
                    'job_accepted' => $acceptedJobCount,
                    'success_rate' => $successRate,
                    'cancellation_rate' => $cancellationRate,
                    'private_user_req' => $privateUserReqSendCount,
                );
        }
       
        return array('allRecord' => $allRecord);
    }
    /* Get all employer who post job for a  month */
    public function getMonthEmployerUsers($startdate,$enddate)
    {
        $jobCollection = new JobCollection();
        $employer = $jobCollection->getMonthEmployer($startdate,$enddate);
        return $employer;
    }
    /* Get total job post count per months*/
    public function countMonthJobs($uid,$startdate,$enddate){
        $jobCollection = new JobCollection();
        $jobCount = $jobCollection->getMonthPostJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted per months*/
    public function countMonthAcceptedJobs($uid,$startdate,$enddate){
        $jobCollection = new JobCollection();
        $jobCount = $jobCollection->getMonthAcceptedJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }

    /* Cancellation Rate Freelancer */
    public function getFreCancellationRate($uid,$startDate,$endDate,$adapter)
    {
        $sqlContCancellation = "SELECT * FROM job_cancel WHERE c_uid = '$uid' AND c_date BETWEEN '$startDate' AND '$endDate'"; 
        $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
        $finalCount = $contCancellation->count();
        
        $sqlAcceptedJob = "SELECT * FROM job_action WHERE ( action = '6' OR action = '3' ) AND f_id = '$uid'";  
        $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
        $countJobAccept = $acceptedJob->count();
        $freCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
        return $freCancellationRate;    
    }

    /*Cancellation Rate Employer */
    public function getEmpCancellationRate($uid,$startDate,$endDate,$adapter)
    {
        $sqlContCancellation = "SELECT * FROM job_post WHERE e_id = '$uid' AND job_status = '8' AND job_update_date BETWEEN '$startDate' AND '$endDate'";   
        $contCancellation = $adapter->query($sqlContCancellation, $adapter::QUERY_MODE_EXECUTE);
        $finalCount = $contCancellation->count();
        
        $sqlAcceptedJob = "SELECT * FROM job_post WHERE e_id = '$uid'"; 
        $acceptedJob = $adapter->query($sqlAcceptedJob, $adapter::QUERY_MODE_EXECUTE);
        $countJobAccept = $acceptedJob->count();
        $empCancellationRate = number_format(($finalCount/$countJobAccept)*100,2);
        return $empCancellationRate;    
    }




    /* Get total private user request send per months*/
    public function countMonthPrivateUserRequestSend($uid,$startdate,$enddate){
        $privateUserCollection = new PrivateUserJobCollection();
        $privateUserCount = $privateUserCollection->getMonthPrivateUserRequestSendCount($uid,$startdate,$enddate); 
        return $privateUserCount;
    }



    /* Individual employer report section of current year */
    public function singleEmpAction()
    {
        
        $userId = $this->getRouteMatch()->getParam('id');
        $_SESSION['report_emp_id'] = $userId;
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUser = $userCollection->getUserById($userId);
        foreach ($empUser as $key => $value) {
            $empName = $value->getName();
            $createdDate = $value->getCreatedAt(); 
        }
        $createdTime = strtotime($createdDate);
        $createdMonthName = date("F",$createdTime);
        $createdMonthNum = date("m",$createdTime);
        $createdYear = date("Y",$createdTime);
        for ($i = $createdYear; $i <= date("Y") ; $i++) {
            if ($i == $createdYear) {
                $startMonth = $createdMonthNum;
                if ($i == date("Y")) {
                    $totalMonth = date("m");
                }else{
                    $totalMonth = 12;
                }
            }elseif($i == date("Y")){
                $startMonth = 1;
                $totalMonth = date("m");
            }else{
                $startMonth = 1;
                $totalMonth = 12;
            }
            
            $allRecord['allyear'][]=$i;
            while ( $totalMonth >= $startMonth) {
                $monthNum  = $startMonth;
                $month = date('F', mktime(0, 0, 0, $monthNum, 10)); 
                $startdate = date('Y-m-01', strtotime($month));
                $lastDate = date('Y-m-t', strtotime($month));
                $date = strtotime("+1 day", strtotime($lastDate));
                $enddate = date('Y-m-d', $date);
                $postJobCount = $this->countMonthJobs($userId,$startdate,$enddate);
                $acceptedJobCount = $this->countMonthAcceptedJobs($userId,$startdate,$enddate);
                $successRate = 0;
                if ($postJobCount > 0) {
                    $successRate = round(($acceptedJobCount/$postJobCount)*100);
                }
                $empCancellationRate = $this->getEmpCancellationRate($userId,$startdate,$enddate,$adapter);
                $privateUserReqSendCount = $this->countMonthPrivateUserRequestSend($userId,$startdate,$enddate);
                
                $allRecord['record'][] = array(
                    'emp_id' => $userId,
                    'month' => $month,
                    'year' => $i,
                    'job_list' => $postJobCount,
                    'job_accepted' => $acceptedJobCount,
                    'success_rate' => $successRate,
                    'cancellation_rate' => $empCancellationRate,
                    'private_user_req' => $privateUserReqSendCount,
                );
                $startMonth++;
            }
        }
        
        if (isset($_GET['emp_year'])) {
            $yealy_record = $this->singleEmpJobRecordByYear($_GET['emp_year']);
            $yealy_record['allRecord']['allyear'] = $allRecord['allyear'];
            return $yealy_record;
        }else{            
            return array('allRecord' => $allRecord, 'empName' => $empName, 'empId' => $userId);
        }
    }

    /* Get record of individual employer by selected year */
    public function singleEmpJobRecordByYear($year)
    {
        $userId = $_SESSION['report_emp_id'];
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUser = $userCollection->getUserById($userId);
        foreach ($empUser as $key => $value) {
            $empName = $value->getFirstname();
            $createdDate = $value->getCreatedAt(); 
        }

        $createdTime = strtotime($createdDate);
        $createdMonthName = date("F",$createdTime);
        $createdMonthNum = date("m",$createdTime);
        $createdYear = date("Y",$createdTime);
        if ($year == $createdYear) {
            $startMonth = $createdMonthNum;
            if ($year == date("Y")) {
                $totalMonth = date("m");
            }else{
                $totalMonth = 12;
            }
        }elseif($year == date("Y")){
            $startMonth = 1;
            $totalMonth = date("m");
        }else{
            $startMonth = 1;
            $totalMonth = 12;
        }

        while ( $totalMonth >= $startMonth) {
            $monthNum  = $startMonth;
            $month = date('F', mktime(0, 0, 0, $monthNum, 10)); 
            $startdate = date('Y-m-01', strtotime($month));
            $lastDate = date('Y-m-t', strtotime($month));
            $date = strtotime("+1 day", strtotime($lastDate));
            $enddate = date('Y-m-d', $date);
            $postJobCount = $this->countMonthJobs($userId,$startdate,$enddate);
            $acceptedJobCount = $this->countMonthAcceptedJobs($userId,$startdate,$enddate);
            $successRate = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $empCancellationRate = $this->getEmpCancellationRate($userId,$startdate,$enddate,$adapter);
            $privateUserReqSendCount = $this->countMonthPrivateUserRequestSend($userId,$startdate,$enddate);
            
            $allRecord['record'][] = array(
                'month' => $month,
                'year'  => $year,
                'job_list' => $postJobCount,
                'job_accepted' => $acceptedJobCount,
                'success_rate' => $successRate,
                'cancellation_rate' => $empCancellationRate,
                'private_user_req' => $privateUserReqSendCount,
            );
            $startMonth++;
        }
        return array('allRecord' => $allRecord, 'empName' => $empName, 'empId' => $userId);
    }

    /* Job Report Freelancer*/
    public function freJobReportAction($startdate = null , $enddate = null)
    {
        $startdate  = isset($_GET['startdate']) ? $_GET['startdate'] : null;
        $enddate    = (isset($_GET['enddate']) && $_GET['enddate']) ? $_GET['enddate'] : date('Y-m-d'); 
        
        $userCollection     = new UserCollection();
        $functionController = new FunctionController();
        $dbConfig           = new DbController();
        $adapter            = $dbConfig->locumkitDbConfig();
        $freUsers           = $userCollection->getFreelancerUsers(); 
        $allRecord          = array();
        foreach ($freUsers as $key => $user) {
            $userName               = $user->getName();
            $postJobCount           = $this->countApplyJobsByFuid($user->getId(),$startdate,$enddate);
            $acceptedJobCount       = $this->countAcceptedJobsByFuid($user->getId(),$startdate,$enddate);
            $acceptedFreezeJobCount = $this->countAcceptedFreezeJobsByFuid($user->getId(),$startdate,$enddate);
            $postFreezeJobCount     = $this->countFreezeJobsByFuid($user->getId(),$startdate,$enddate);
            $private_job            = $this->countPrivateJobsByFuid($user->getId(),$startdate,$enddate);
            $successRate            = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $freCancellationRate = $functionController->getFreCancellationRate($user->getId(),$adapter);
            $freezeSuccessRate = 0;
            if ($postFreezeJobCount > 0) {
                $freezeSuccessRate = round(($acceptedFreezeJobCount/$postFreezeJobCount)*100);
            }
            $allRecord[] = array(
                    'fre_id' => $user->getId(),
                    'name' => $userName,
                    'job_applied' => $postJobCount,
                    'job_accepted' => $acceptedJobCount,
                    'job_success_rate' => $successRate,
                    'job_cancellation_rate' => $freCancellationRate,
                    'job_freeze' => $postFreezeJobCount,
                    'job_freeze_accepted' => $acceptedFreezeJobCount,
                    'job_freeze_success_rate' => $freezeSuccessRate,
                    'private_job' => $private_job,
                );
        }
        
        /*echo "<pre>";
        print_r($allRecord);
        echo "</pre>";
        exit();*/
        return array('allRecord' => $allRecord);
    }

    /* Get total job apply count */
    public function countApplyJobsByFuid($uid, $startdate = null, $enddate = null){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getAppliedJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted from applied*/
    public function countAcceptedJobsByFuid($uid, $startdate = null, $enddate = null){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getAcceptedJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job freeze count */
    public function countFreezeJobsByFuid($uid, $startdate = null, $enddate = null){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getFreezeJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted from freezed*/
    public function countAcceptedFreezeJobsByFuid($uid, $startdate = null, $enddate = null){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getAcceptedFreezeJobCount($uid,$startdate,$enddate); 
        return $jobCount;
    }

    /* Individual Freelancer report section of all year */
    public function singleFreAction()
    {
       
        $userId = $this->getRouteMatch()->getParam('id');
        $_SESSION['report_fre_id'] = $userId;
        
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUser = $userCollection->getUserById($userId);
        foreach ($empUser as $key => $value) {
            $freName = $value->getName();
            $createdDate = $value->getCreatedAt(); 
        }
        $createdTime = strtotime($createdDate);
        $createdMonthName = date("F",$createdTime);
        $createdMonthNum = date("m",$createdTime);
        $createdYear = date("Y",$createdTime);
        for ($i = $createdYear; $i <= date("Y") ; $i++) {
            if ($i == $createdYear) {
                $startMonth = $createdMonthNum;
                if ($i == date("Y")) {
                    $totalMonth = date("m");
                }else{
                    $totalMonth = 12;
                }
            }elseif($i == date("Y")){
                $startMonth = 1;
                $totalMonth = date("m");
            }else{
                $startMonth = 1;
                $totalMonth = 12;
            }
            
            $allRecord['allyear'][]=$i;
            while ( $totalMonth >= $startMonth) {
                $monthNum  = $startMonth;
                $month = date('F', mktime(0, 0, 0, $monthNum, 10)); 
                $startdate = date('Y-m-01', strtotime($month.'-'.$i));
                $lastDate = date('Y-m-t', strtotime($month.'-'.$i));
                $date = strtotime("+1 day", strtotime($lastDate));
                $enddate = date('Y-m-d', $date);
                $postJobCount = $this->countFreMonthJobs($userId,$startdate,$enddate);
                $acceptedJobCount = $this->countFreMonthAcceptedJobs($userId,$startdate,$enddate);
                $acceptedFreezeJobCount = $this->countAcceptedFreezeJobsByMonth($userId,$startdate,$enddate);
                $postFreezeJobCount = $this->countFreezeJobsByMonth($userId,$startdate,$enddate);
                $private_job = $this->countPrivateJobsByFuidOfDate($userId,$startdate,$enddate);
                $successRate = 0;
                if ($postJobCount > 0) {
                    $successRate = round(($acceptedJobCount/$postJobCount)*100);
                }
                $freCancellationRate = $this->getFreCancellationRate($userId,$startdate,$enddate,$adapter);
                $freezeSuccessRate = 0;
                if ($postFreezeJobCount > 0) {
                    $freezeSuccessRate = round(($acceptedFreezeJobCount/$postFreezeJobCount)*100);
                }
                
                $allRecord['record'][] = array(
                    'fre_id' => $userId,
                    'month' => $month,
                    'year' => $i,
                    'job_applied' => $postJobCount,
                    'job_accepted' => $acceptedJobCount,
                    'success_rate' => $successRate,
                    'cancellation_rate' => $freCancellationRate,
                    'job_freeze' => $postFreezeJobCount,
                    'job_freeze_accepted' => $acceptedFreezeJobCount,
                    'job_freeze_success_rate' => $freezeSuccessRate,
                    'private_job' => $private_job,
                );
                $startMonth++;
            }
        }

        
        if (isset($_GET['fre_year'])) {
            $yearly_record = $this->singleFreJobRecordByYear($_GET['fre_year']);
            $yearly_record['allRecord']['record'] = $yearly_record['allRecord'];
            $yearly_record['allRecord']['allyear'] = $allRecord['allyear'];
            return $yearly_record;
        }else{
            return array('allRecord' => $allRecord, 'freName' => $freName, 'freId' => $userId);
        }
        
    }

    /* Get record of individual employer by selected year */
    public function singleFreJobRecordByYear($year)
    {
        $userId = $_SESSION['report_fre_id'];
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $freUser = $userCollection->getUserById($userId);
        foreach ($freUser as $key => $value) {
            $freName = $value->getFirstname();
            $createdDate = $value->getCreatedAt(); 
        }

        $createdTime = strtotime($createdDate);
        $createdMonthName = date("F",$createdTime);
        $createdMonthNum = date("m",$createdTime);
        $createdYear = date("Y",$createdTime);
        if ($year == $createdYear) {
            $startMonth = $createdMonthNum;
            if ($year == date("Y")) {
                $totalMonth = date("m");
            }else{
                $totalMonth = 12;
            }
        }elseif($year == date("Y")){
            $startMonth = 1;
            $totalMonth = date("m");
        }else{
            $startMonth = 1;
            $totalMonth = 12;
        }

        while ( $totalMonth >= $startMonth) {
            $monthNum  = $startMonth;
            $month = date('F', mktime(0, 0, 0, $monthNum, 10)); 
            $startdate = date('Y-m-01', strtotime($month.'-'.$year));
            $lastDate = date('Y-m-t', strtotime($month.'-'.$year));
            $date = strtotime("+1 day", strtotime($lastDate));
            $enddate = date('Y-m-d', $date);
            $postJobCount = $this->countFreMonthJobs($userId,$startdate,$enddate);
            $acceptedJobCount = $this->countFreMonthAcceptedJobs($userId,$startdate,$enddate);
            $acceptedFreezeJobCount = $this->countAcceptedFreezeJobsByMonth($userId,$startdate,$enddate);
            $postFreezeJobCount = $this->countFreezeJobsByMonth($userId,$startdate,$enddate);
            $private_job = $this->countPrivateJobsByFuidOfDate($userId,$startdate,$enddate);
            $successRate = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $freCancellationRate = $this->getFreCancellationRate($userId,$startdate,$enddate,$adapter);
            $freezeSuccessRate = 0;
            if ($postFreezeJobCount > 0) {
                $freezeSuccessRate = round(($acceptedFreezeJobCount/$postFreezeJobCount)*100);
            }
            
            $allRecord[] = array(
                'month' => $month,
                'year'  => $year,
                'job_applied' => $postJobCount,
                'job_accepted' => $acceptedJobCount,
                'success_rate' => $successRate,
                'cancellation_rate' => $freCancellationRate,
                'job_freeze' => $postFreezeJobCount,
                'job_freeze_accepted' => $acceptedFreezeJobCount,
                'job_freeze_success_rate' => $freezeSuccessRate,
                'private_job' => $private_job,
            );
            $startMonth++;
        }
        return array('allRecord' => $allRecord, 'freName' => $freName, 'freId' => $userId);
    }
    /* Job Report Per Month*/
    public function freMonthJobReport($startdate,$enddate)
    {
        $userCollection = new UserCollection();
        $functionController= new FunctionController();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        $empUsers = $userCollection->getFreelancerUsers($startdate,$enddate); 
        
        foreach ($empUsers as $key => $user) {
            $userName = $user->getName();
            $postJobCount = $this->countFreMonthJobs($user->getId(),$startdate,$enddate);
            $acceptedJobCount = $this->countFreMonthAcceptedJobs($user->getId(),$startdate,$enddate);
             $acceptedFreezeJobCount = $this->countAcceptedFreezeJobsByMonth($user->getId(),$startdate,$enddate);
            $postFreezeJobCount = $this->countFreezeJobsByMonth($user->getId(),$startdate,$enddate);
            $private_job = $this->countPrivateJobsByFuidOfDate($user->getId(),$startdate,$enddate);
            $successRate = 0;
            if ($postJobCount > 0) {
                $successRate = round(($acceptedJobCount/$postJobCount)*100);
            }
            $freCancellationRate = $this->getFreCancellationRate($user->getId(),$startdate,$enddate,$adapter);
            $freezeSuccessRate = 0;
            if ($postFreezeJobCount > 0) {
                $freezeSuccessRate = round(($acceptedFreezeJobCount/$postFreezeJobCount)*100);
            }
            
            $allRecord[] = array(
                'fre_id' => $user->getId(),
                'name' => $userName,
                'job_applied' => $postJobCount,
                'job_accepted' => $acceptedJobCount,
                'job_success_rate' => $successRate,
                'job_cancellation_rate' => $freCancellationRate,
                'job_freeze' => $postFreezeJobCount,
                'job_freeze_accepted' => $acceptedFreezeJobCount,
                'job_freeze_success_rate' => $freezeSuccessRate,
                'private_job' => $private_job,
            );
               
        }
        
        return array('allRecord' => $allRecord);
    }
    /* Get all employer who post job for a  month */
    public function getMonthJobFreelancerUsers($startdate,$enddate)
    {
        $jobCollection = new JobActionCollection();
        $employer = $jobCollection->getMonthFreelancer($startdate,$enddate);
        return $employer;
    }
    /* Get total job post count per months*/
    public function countFreMonthJobs($uid,$startdate,$enddate){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getAppliedJobCountByMonth($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted per months*/
    public function countFreMonthAcceptedJobs($uid,$startdate,$enddate){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getAcceptedJobCountByMonth($uid,$startdate,$enddate); 
        return $jobCount;
    }

    /* Get total job post count per months*/
    public function countFreezeJobsByMonth($uid,$startdate,$enddate){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getCountFreezeJobsByMonth($uid,$startdate,$enddate); 
        return $jobCount;
    }
    /* Get total job accepted per months*/
    public function countAcceptedFreezeJobsByMonth($uid,$startdate,$enddate){
        $jobCollection = new JobActionCollection();
        $jobCount = $jobCollection->getcountAcceptedFreezeJobsByMonth($uid,$startdate,$enddate); 
        return $jobCount;
    }

    /* Get count of all private job of freelancer */
    public function countPrivateJobsByFuid($uid)
    {
        $jobCollection = new FreelancerPrivateJobCollection();
        $jobCount = $jobCollection->getFreelancerPrivateJobCount($uid); 
        return $jobCount;
    }

    /* Get count of selected dates private job of freelancer */
    public function countPrivateJobsByFuidOfDate($uid,$startdate,$enddate)
    {
        $jobCollection = new FreelancerPrivateJobCollection();
        $jobCount = $jobCollection->getFreelancerPrivateJobCountByDate($uid,$startdate,$enddate); 
        return $jobCount;
    }

    /* Package income report */

    public function pkgIncomeReportAction()
    {
        $pkg_year = $pkg_month = '';
        if (isset($_GET['pkg_year'])) {
            $pkg_year = $_GET['pkg_year'];
        }
        if (isset($_GET['pkg_month'])) {
            $pkg_month = $_GET['pkg_month'];
        }
        $packageRateReport = new PackageRateReport();

        if ($pkg_month == '' && $pkg_year != '') {
            $start_date = $pkg_year.'-01-01';
            $end_date   = $pkg_year.'-12-31';
            $package_rate_report = $packageRateReport->getPackageRates($start_date,$end_date);
        }elseif($pkg_month != '' && $pkg_year == ''){
            $start_date = date('Y-m-01', strtotime($pkg_month));
            $end_date   = date('Y-m-t', strtotime($pkg_month));
            $package_rate_report = $packageRateReport->getPackageRates($start_date,$end_date);
        }elseif($pkg_month != '' && $pkg_year != ''){
            $start_date = date('Y-m-01', strtotime($pkg_month.'-'.$pkg_year));
            $end_date   = date('Y-m-t', strtotime($pkg_month.'-'.$pkg_year));
            $package_rate_report = $packageRateReport->getPackageRates($start_date,$end_date);
        }else{
            $package_rate_report = $packageRateReport->getPackageRates();
        }

        
        
        $packageCollection = new PackageCollection();
        $packageData = $packageCollection->getPackages();
        foreach ($packageData as $key => $pkgValue) {
            $pkgId      = $pkgValue->getId();
            $pkgName    = $pkgValue->getName();
            $pkgPrice   = $pkgValue->getPrice();
            $conutUser  = $this->getCountFreelancerOfPkg($pkgId);
            $incomeExpected = $pkgPrice * $conutUser;
            $allRecord[] = array(
                    'pkg_id' => $pkgId,
                    'pkg_name' => $pkgName,
                    'pkg_rate' => $pkgPrice,
                    'pkg_user_count' => $conutUser,
                    'pkg_income' => $incomeExpected
                );
        }
        
        return array('allRecord' => $allRecord);
    }

    /* Get pack report by selected month of selected year*/
    public function pkgIncomeReportByFilter($startdate,$enddate)
    {
        $packageCollection = new PackageCollection();
        $packageData = $packageCollection->getPackages();
        foreach ($packageData as $key => $pkgValue) {
            $pkgId = $pkgValue->getId();
            $pkgName = $pkgValue->getName();
            $pkgPrice = $pkgValue->getPrice();
            $conutUser = $this->getCountFreelancerOfPkgOfSelectedDate($pkgId,$startdate,$enddate);
            $incomeExpected = $pkgPrice * $conutUser;
            $allRecord[] = array(
                    'pkg_id' => $pkgId,
                    'pkg_name' => $pkgName,
                    'pkg_rate' => $pkgPrice,
                    'pkg_user_count' => $conutUser,
                    'pkg_income' => $incomeExpected
                );
        }
        
        return array('allRecord' => $allRecord);
    }

    /* Get total Freelancer of current package  */
    public function getCountFreelancerOfPkg($pkgId)
    {
        $userCollection = new UserCollection();
        $pkgUsers = $userCollection->getFreelancerOfCurrentPkg($pkgId); 
        return $pkgUsers;  
    }
    public function getCountFreelancerOfPkgOfSelectedDate($pkgId,$startdate,$enddate)
    {
        $userCollection = new UserCollection();
        $pkgUsers = $userCollection->getFreelancerOfCurrentPkgOfSelectedDate($pkgId,$startdate,$enddate); 
        return $pkgUsers;  
    }

    /* Get the acivation date of freelancer */
    public function getPkgActivationDateFre($uid,$adapter)
    {        
        $sqlPkgActivationDate = "SELECT * FROM user_package_details WHERE user_id='$uid' AND package_status = '1'";  
        $pkgActivationDateObj = $adapter->query($sqlPkgActivationDate, $adapter::QUERY_MODE_EXECUTE);
        return $pkgActivationDate = $pkgActivationDateObj->current();
    }

    /* Private Freelancer Report */
    public function privateFreelancerReportAction()
    {
        $startdate  = isset($_GET['startdate']) ? $_GET['startdate'] : null;
        $enddate    = (isset($_GET['enddate']) && $_GET['enddate'] != '' )  ? $_GET['enddate'] : date('Y-m-d'); 
        $privateUserCollection = new PrivateFreelancerInfo();
        $privateUserObj = $privateUserCollection->getPrivateUserInfo($startdate,$enddate); 
        return array('privateUserRecords' => $privateUserObj);
        
    }

    /* Private Store Report */
    public function privateStoreReportAction()
    {
        $startdate  = isset($_GET['startdate']) ? $_GET['startdate'] : null;
        $enddate    = isset($_GET['enddate']) ? $_GET['enddate'] : null; 
        $storeCollection = new FreelancerPrivateJobCollection();
        $storeObj = $storeCollection->getFreelancerPrivateJob($startdate,$enddate); 
        return array('privateStoreRecords' => $storeObj);
    }
}