<?php
/**
 * This source file is part of FUDUGO. *
 * 
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 * @author     Suraj Wasnik (suraj.wasnik0126@gmail.com)
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.fudugo.com
 */


namespace Gc\User\Feedback;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Feedback Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 */
class Collection extends AbstractTable
{
    /**
     * List of Feedbacks
     *
     * @var array
     */
    protected $feedbacks;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_feedback';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getFeedbacks(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getFeedbacks($forceReload = false)
    {
        //if (empty($this->feedbacks) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('feedback_id DESC');
                    }
                )
            );

            $feedbacks = array();
            foreach ($rows as $row) {
                $feedbacks[] = Model::fromArray((array) $row);
            }

            $this->feedbacks = $feedbacks;
       // }

        return $this->feedbacks;
    }

    /* Get feedback for employer by feedbackId and EmpId and JobId */
    public function getFeedbackEmployerById($empId, $jobId)
    {
        $query = "emp_id = '$empId' AND j_id = '$jobId' AND user_type = '2'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);

        $feedbackAverage = 0;
        foreach ($rows as $row) {
            $feedbackAverageObj = Model::fromArray((array) $row);
            $feedbackAverage = $feedbackAverageObj->getRating();

        } 
        return $feedbackAverage;
    }

    /* Get feedback for freelancer by feedbackId and FreId */
    public function getFeedbackFreelancerById($freId, $jobId)
    {
        $query = "fre_id = '$freId' AND j_id = '$jobId' AND user_type = '3'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
       
        $feedbackAverage = 0;
        foreach ($rows as $row) {
            $feedbackAverageObj = Model::fromArray((array) $row);
            $feedbackAverage = $feedbackAverageObj->getRating();
        } 
        return $feedbackAverage;
    }
}
