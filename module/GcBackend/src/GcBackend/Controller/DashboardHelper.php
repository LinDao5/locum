<?php

namespace GcBackend\Controller;
use Zend\Db\Sql\Sql;
use Gc\Mvc\Controller\Action;
use GcFrontend\Controller\DbController as DbController;

class DashboardHelper extends Action
{
	public function getAdapter()
    {
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        return $adapter;
    }
    public function dashbordUserRegisterData()
    {
    	$mons = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
    	$month = date('m');
    	$leastmonth = 1;
    	$userRecord = '';

    	while ( $month >= $leastmonth) {
    		$userLocumRecord[]=$this->getSelectedmonthRegisterUser($leastmonth,2);
    		$userEmployerRecord[$leastmonth]=$this->getSelectedmonthRegisterUser($leastmonth,3);
    		$monthArray[]= '"'.$mons[$leastmonth].'"';
    		$leastmonth++;
    	}
    	$userCountinString['employer'] = implode(', ', $userEmployerRecord) ;
    	$userCountinString['locum'] = implode(', ', $userLocumRecord) ;
    	$userCountinString['month'] = implode(', ', $monthArray) ;
    	$userCountinString['AllUser'] = $this->getSelectedYearRegisterUser(null);
    	return $userCountinString;
    }

    public function getSelectedmonthRegisterUser($month = null,$userRole = null)
    {
    	if (!$month) {
    		$month = date('m');
    	} 
    	
    	$adapter=$this->getAdapter();
    	if (!$month) {
    		$month = date('m');
    	}     	
    	$countUserSql = "SELECT COUNT(*) AS numberofuser FROM user WHERE user_acl_role_id = '$userRole' AND MONTH(created_at) = '$month'";
    	
    	$countUserObj = $adapter->query($countUserSql, $adapter::QUERY_MODE_EXECUTE); 
    	$countUser = $countUserObj->current(); 
    	return $countUser->numberofuser;   	
    }

    public function getSelectedYearRegisterUser($year=null)
    {
    	$adapter=$this->getAdapter();
    	if (!$year) {
    		$year = date('Y');
    	} 
    	$countUserSql = "SELECT COUNT(*) AS numberofuser FROM user WHERE ( user_acl_role_id = 2 OR user_acl_role_id = 3) AND YEAR(created_at) = '$year'";    	
    	$countUserObj = $adapter->query($countUserSql, $adapter::QUERY_MODE_EXECUTE); 
    	$countUser = $countUserObj->current(); 
    	return $countUser->numberofuser;   	
    }

    public function getTotalJobs($year = null)
    {
    	if (!$year) {
    		$year = date('Y');
    	} 
    	$adapter=$this->getAdapter();
    	$countJobSql = "SELECT COUNT(*) AS numberofjob FROM job_post WHERE YEAR(job_create_date) = '$year'";
    	$countJobObj = $adapter->query($countJobSql, $adapter::QUERY_MODE_EXECUTE); 
    	$countJob = $countJobObj->current(); 
    	return $countJob->numberofjob; 
    }

    public function getTotalTrunOver($year = null)
    {
    	if (!$year) {
    		$year = date('Y');
    	} 
    	$adapter=$this->getAdapter();
    	$totalTrunOverSql = "SELECT sum(price) AS turnover FROM user_payment_info WHERE payment_status = '1' AND YEAR(created_date) = '$year'";
    	$totalTrunOverObj = $adapter->query($totalTrunOverSql, $adapter::QUERY_MODE_EXECUTE); 
    	$totalTrunOver = $totalTrunOverObj->current(); 
    	return $totalTrunOver->turnover;
    }

}