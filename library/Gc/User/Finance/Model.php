<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * Finance Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Finance
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    /**
     * Save Finance
     *
     * @return integer
     */
    public function save($arraySave)
    {
        $this->events()->trigger(__CLASS__, 'before.save', $this);       

        try {
            //print_r($arraySave);
            $transId = $this->getTransId();    
            if (!$this->isJobFinanceAdded($arraySave['trans_type_id'] , $arraySave['trans_type'])) {
                $this->insert($arraySave);
                $this->setId($this->getLastInsertId());
            } else {
                $this->update($arraySave, array('trans_id' => $this->getTransId()));
            }
            $this->events()->trigger(__CLASS__, 'after.save', $this);
            return $this->getId();
        } catch (\Exception $e) {
            $this->events()->trigger(__CLASS__, 'after.save.failed', $this);
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\Finance\Model
     */
    public static function fromArray(array $array)
    {
        $FinanceTable = new Model();
        $FinanceTable->setData($array);
        $FinanceTable->setOrigData();

        return $FinanceTable;
    }

    /**
     * Initiliaze from id
     *
     * @param integer $userqusId User Finance id
     *
     * @return \Gc\User\Finance\Model
     */
    public static function fromId($userFinanceId)
    {
        $financeTable = new Model();
        $row       = $financeTable->fetchRow($financeTable->select(array('trans_id' => (int) $userFinanceId)));
        $financeTable->events()->trigger(__CLASS__, 'before.load', $financeTable);
        if (!empty($row)) {
            $financeTable->setData((array) $row);
            $financeTable->setOrigData();
            $financeTable->events()->trigger(__CLASS__, 'after.load', $financeTable);
            return $financeTable;
        } else {
            $financeTable->events()->trigger(__CLASS__, 'after.load.failed', $financeTable);
            return false;
        }
    }
    

    /* Update Status Freeze/Accept/Delete/Enable/Desable from frontend */

    public function FinanceStatusUpdate($Finance_id,$status)
    {
        $updateArray = array(
                'Fstatus' => $status,
                'update_at' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'id' => $Finance_id,                
            );
        $this->update($updateArray, $conditionArray);
    }

    public function updateFinanceCron($Finance_id,$action)
    {
        $updateArray = array(
                'status' => $action,
            );
        $conditionArray = array(
                'status' => 6,              
            );
        $this->update($updateArray, $conditionArray);
    }

    /* Update finance with expense cost */
    public function updateFinanceWithExpense($checkFinanceModel,$updateFinanceModel)
    {
        $this->update($updateFinanceModel, $checkFinanceModel);
    }

    /* Check if job finance already exist */
    private function isJobFinanceAdded($transTypeId , $transType)
    {
        $query = "trans_type_id = '$transTypeId' AND trans_type = '$transType' ";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        if (!empty($rows)) {
            return true;
        }else{
            return false;
        }
    }

}
