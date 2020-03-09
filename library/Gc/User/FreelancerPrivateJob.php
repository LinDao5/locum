<?php
/**
* Model to update the user package on admin action, dvelope by SURAJ 
* WASNIK at FUDUGO
*/
namespace Gc\User;

use Gc\Db\AbstractTable;
use Gc\Mail;
use Gc\Registry;
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Select;
use Zend\Validator\EmailAddress;

class FreelancerPrivateJob extends AbstractTable
{
	const BACKEND_AUTH_NAMESPACE = 'Zend_Auth_Backend';
	protected $name = 'freelancer_private_job';

	public function getFreelancerPrivateJobCount($uid)
	{
		$query = "f_id = '$uid'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $pJobs = array();
        foreach ($rows as $row) {
            $pJobs[] = Model::fromArray((array) $row);
        }
        
        $noOfpJobs = count($pJobs);
        return $noOfpJobs;
	}

    /* Get Freelancer private jobs by selected date range  */
    public function getFreelancerPrivateJobCountByDate($uid,$startdate,$enddate)
    {
        /*$sdate = date('d/m/Y' , strtotime($startdate));
        $edate = date('d/m/Y' , strtotime($enddate));*/
        $query = "f_id = '$uid' AND (priv_job_start_date BETWEEN '$startdate' AND '$enddate')";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $pJobs = array();
        foreach ($rows as $row) {
            $pJobs[] = Model::fromArray((array) $row);
        }
        /*echo "<pre>";
        print_r($pJobs);
        echo "</pre>";
        exit();*/
        $noOfpJobs = count($pJobs);
        return $noOfpJobs;
    }

    public function getFreelancerPrivateJob($start_date = null, $end_date = null)
    {
        if ($start_date != null && $end_date != null) {
            $select = "SELECT * FROM freelancer_private_job WHERE priv_create_date BETWEEN '$start_date' AND '$end_date' ORDER BY pv_id DESC";
        }else{
            $select = "SELECT * FROM freelancer_private_job ORDER BY pv_id DESC";
        }
        $rows = $this->fetchAll($select);
        
        $pJobs = array();
        foreach ($rows as $row) {
            $pJobs[] = Model::fromArray((array) $row);
        }
        return $pJobs;
    }
}