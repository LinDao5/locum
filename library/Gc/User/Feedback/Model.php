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
use Zend\Db\TableGateway\TableGateway;

/**
 * Feedback Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_feedback';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Question
     *
     * @return integer
     */
   public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this); 
        try {
            $id = $this->getFeedbackId(); 
            if (empty($id)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
                $this->events()->trigger(__CLASS__, 'after.save', $this);  
                return $this->getLastInsertId();              
            }else{               
                $this->update($arraySave, array('feedback_id' => $this->getFeedbackId()));
            }

        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /* Check for feedback already given or not */
    public function checkFeedback($job_id,$u_id,$u_type)
    {
        $alreadyExsit = 0;
        if ($u_type == 2) {
            $rows = $this->fetchAll($this->select(array('j_id' =>$job_id,'fre_id'=>$u_id, 'user_type'=> 2)));
            $count = count($rows);
            if ($count && $count > 0) {
                $alreadyExsit = 1;
            }
        }elseif ($u_type == 3) {
            $rows = $this->fetchAll($this->select(array('j_id' =>$job_id,'emp_id'=>$u_id, 'user_type'=> 3)));
            $count = count($rows);
            if ($count && $count > 0) {
                $alreadyExsit = 1;
            }
        }
        return $alreadyExsit;
        
    }
    /**
     * Delete Question
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);
        $id = $this->getFeedbackId();
        if (!empty($id)) {
            parent::delete(array('feedback_id' => $id));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
    }

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\Question\Model
     */
    public static function fromArray(array $array)
    {
        $feedbackTable = new Model();
        $feedbackTable->setData($array);
        $feedbackTable->setOrigData();

        return $feedbackTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userId User id
     *
     * @return \Gc\feedback\Model
     */
    public static function fromId($feedbackId)
    {
        $feedbacTable = new Model();
        $row       = $feedbacTable->fetchRow($feedbacTable->select(array('feedback_id' => (int) $feedbackId)));
        $feedbacTable->events()->trigger(__CLASS__, 'before.load', $feedbacTable);
        if (!empty($row)) {
            $feedbacTable->setData((array) $row);
            $feedbacTable->unsetData('password');
            $feedbacTable->setOrigData();
            $feedbacTable->events()->trigger(__CLASS__, 'after.load', $feedbacTable);
            return $feedbacTable;
        } else {
            $feedbacTable->events()->trigger(__CLASS__, 'after.load.failed', $feedbacTable);
            return false;
        }
    }

    /* If user fire dispute the feedback status change to dispute pending */
    public function updateDispute($arraySave,$feedback_id){
        if (!empty($arraySave)) {
            $this->update($arraySave, array('feedback_id' => $feedback_id));
        }
    }

    /* Publish feedback By feedback Id as argument   */
    public function setPublishFeedbackById($feddbackId){
        if (!empty($feddbackId)) {
            $arraySave  = array('status' => 1, );
            $this->update($arraySave, array('feedback_id' => $feddbackId));
        }
    }


    /* Publish feedback By feedback Id as argument   */
    public function updateFeedbackById($feddbackId,$arraySave){
        if (!empty($feddbackId)) {            
            $this->update($arraySave, array('feedback_id' => $feddbackId));
        }
    }
    
}
