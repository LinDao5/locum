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

namespace Gc\User\Feedback\Frontend;

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
    protected $name = 'feedback_qus';

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
    public function save()
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this); 
        $arraySave = array(
            'fd_qus_fre' => $this->getFdQusFre(),
            'fd_qus_emp' => $this->getFdQusEmp(),
            'fd_qus_cat_id' => $this->getFdQusCatId(),
            'fd_qus_sort_order' => $this->getFdQusSortOrder(),
            'fd_qus_status' => $this->getFdQusStatus()          
        );


        try {
            $id = $this->getFdQusId();            
            if (empty($id)) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
                $this->events()->trigger(__CLASS__, 'after.save', $this);
            } else {
                $this->update($arraySave, array('fd_qus_id' => $this->getFdQusId()));
            }
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    
    /**
     * Delete Question
     *
     * @return boolean
     */
    public function delete()
    {
        $this->events()->trigger(__CLASS__, 'before.delete', $this);

        $id = $this->getFdQusId();
        if (!empty($id)) {            
            $arraySave = array(                
                'fd_qus_status' => 2          
            );
            parent::update($arraySave, array('fd_qus_id' => $id));
            //parent::delete(array('fd_qus_id' => $id));
            $this->events()->trigger(__CLASS__, 'after.delete', $this);
            unset($this);

            return true;
        }

        $this->events()->trigger(__CLASS__, 'after.delete.failed', $this);

        return false;
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
        $row       = $feedbacTable->fetchRow($feedbacTable->select(array('fd_qus_id' => (int) $feedbackId)));
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

    
}
