<?php
/**
 *Design and develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO SOLUTIONS PVT. LTD.
 */

namespace GcConfig\Controller;

use Gc\Mvc\Controller\Action;
use Gc\User\Job;
use GcConfig\Form\Job as JobForm;
use GcFrontend\Controller\DbController as DbController;
use GcFrontend\Controller\FunctionsController as FunctionsController;
/**
 * Job controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class JobController extends Action
{
    /**
     * Contains information about acl
     *
     * @var array
     */
    protected $aclPage = array('resource' => 'settings', 'permission' => 'job');

    /**
     * List all Jobs
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function indexAction()
    {
        $jobCollection = new Job\Collection();
        $jobs          = array();
        $dbConfig = new DbController();
        $adapter = $dbConfig->locumkitDbConfig();
        foreach ($jobCollection->getJobs() as $job) {
            if ($job->getName() !== Job\Model::PROTECTED_NAME) {
                if ($job->getJobStatus() != 7) {
                    $freInfo = $this->getFreInfo($job->getJobId(),$adapter);
                    $job->freName = $freInfo['name'];
                    $job->freId = $freInfo['id'];
                    $jobs[] = $job;
                }                
            }
        }

        return array('jobs' => $jobs);
    }


    /**
     * Delete job
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function deleteAction()
    {
        $jobModel = Job\Model::fromId($this->getRouteMatch()->getParam('id'));
        if (!empty($jobModel) and $jobModel->getName() !== Job\Model::PROTECTED_NAME and $jobModel->delete()) {
            return $this->returnJson(array('success' => true, 'message' => 'Job has been deleted'));
        }

        return $this->returnJson(array('success' => false, 'message' => 'Job does not exists'));
    }

    /**
     * View Job
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function viewAction()
    {
        $jobId = $this->getRouteMatch()->getParam('id');

        $jobModel = Job\Model::fromId($jobId);
        if (empty($jobModel) or $jobModel->getName() === Job\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't view this job");
            return $this->redirect()->toRoute('config/user/job');
        }
        /*echo "<pre>";
        print_r($jobModel);
        echo "</pre>";
        foreach ($jobModel as $key => $value) {
            echo "<pre>";
            print_r($value);
            echo "</pre>";
        }
        exit();*/
        $form = new JobForm();        
        $form->setAttribute('action', $this->url()->fromRoute('config/user/job/view', array('id' => $jobId)));
        /*print_r($jobModel);
        exit();*/
        $form->loadValues($jobModel);
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost()->toArray();
            /*print_r($post);
            exit();*/
            $form->setData($post);
            if ($form->isValid()) {
                $jobModel->addData($form->getInputFilter()->getValues());
                $jobModel->save();

                $this->flashMessenger()->addSuccessMessage('Job saved!');
                return $this->redirect()->toRoute('config/user/job/view', array('id' => $jobId));
            }

            $this->flashMessenger()->addErrorMessage('job can not saved!');
            $this->useFlashMessenger();
        }
        $jobCollection = new Job\Collection();
        $jobs          = array();
        foreach ($jobCollection->getJobs() as $job) {
            if ($job->getName() !== Job\Model::PROTECTED_NAME && $job->getJobId() == $jobId ) {
                $jobs[] = $job;
            }
        }
        $jobData = $jobs;
        return array('form' => $form, 'jobData'=> $jobData);
    }

    /**
     * Search Job
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function searchAction()
    {
        $jobId = 0;
        $catId = 0;
        $userId = 0;
        if (isset($_GET['jobId'])) {
            $jobId = $_GET['jobId'];
        }
        if (isset($_GET['catId'])) {
            $catId = $_GET['catId'];
        }
        if (isset($_GET['uId'])) {
            $userId = $_GET['uId'];
        }
        
        
        $jobModel = Job\Model::serach($jobId,$userId,$catId);
        if (empty($jobModel) or $jobModel->getName() === Job\Model::PROTECTED_NAME) {
            $this->flashMessenger()->addErrorMessage("Can't search this job");
            //return $this->redirect()->toRoute('config/user/job');
        }
        /*echo "<pre>";
        $jobModel = $jobModel->getData();
        print_r($jobModel);
        echo "</pre>";
        exit();
        echo "===============";
        foreach ($jobModel['origData'] as $key => $value) {
            echo "<pre>";
            
            print_r($value);
            echo "</pre>";
        }
        echo "=====123==========";
        $jobModel = (array)$jobModel;*/
        $jobSearchArray = array();
        if (!empty($jobModel)) {
            $jobModel = $jobModel->getData();
            foreach ($jobModel as $key => $job) {
                if($job['job_status'] != 7 ){
                    $jobSearchArray[] = array(
                        'job_id' => $job['job_id'],
                        'job_title'=>$job['job_title'],
                        'cat_id'=>$job['cat_id'],
                        'job_rate'=>$job['job_rate'],
                        'e_id'=>$job['e_id'],
                        'job_status'=>$job['job_status'],

                    );
                }
            }
        }else{
            $jobSearchArray = array();
        }
        
       /* echo "<pre>";
        print_r($jobSearchArray);
        echo "</pre>";
        exit();*/
        $jobCollection = new Job\Collection();
        $jobs          = array();
        foreach ($jobCollection->getJobs() as $job) {
            if ($job->getName() !== Job\Model::PROTECTED_NAME) {
                $jobs[] = $job;
            }
        }
        return array('jobData'=> $jobSearchArray, 'jobs' => $jobs);
    }

    /*Get freelancer info who accept job */
    public function getFreInfo($jobId,$adapter)
    {
        $functionsController = new FunctionsController();
        $freInfo = $functionsController->getFreelancerInfoFromAcceptedJob($jobId, $adapter);
        if(!empty($freInfo)){
            if (isset($freInfo->firstname) && isset($freInfo->lastname)) {
                $freName = $freInfo->firstname.' '.$freInfo->lastname;
                $freId = $freInfo->id;
            }elseif($freInfo->user_name){
                $freName = $freInfo->user_name;
                $freId = $freInfo->uid;
            }else{
                $freName = 'N/A';
                $freId = 'N/A';
            }            
        }else{
            $freName = 'N/A';
            $freId = 'N/A';
        }
        $freData = array(
                'name' => $freName,
                'id' => $freId
            );
        return $freData;        
    }
}
