<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Income;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of expenses Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\expenses
 */
class Collection extends AbstractTable
{
    /**
     * List of expenses
     *
     * @var array
     */
    protected $income;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_income_table';

    /**
     * Initiliaze expenses collection
     *
     * @return void
     */
    public function init()
    {
       $this->getIncome(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getIncome($forceReload = false)
    {
        if (empty($this->income) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $income = array();
            foreach ($rows as $row) {
                $income[] = Model::fromArray((array) $row);
            }

            $this->income = $income;
        }
return $rows;
       // return $this->income;
    }

    /* Get Expence Information by expense Id */
    public function getincomeInfoById($inId)
    {
        $query = "id = '$inId'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $incomeInfo = array();
        foreach ($rows as $row) {
            $incomeInfo[] = Model::fromArray((array) $row);
        }        
        return $incomeInfo;
    }

    /* Get Expence cost Information by expense Id */
    public function getExpenseCostInfoById($expId)
    {
        $query = "id = '$expId'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $expenseCostInfo = 0;
        $totalExpenseCost = 0;
        foreach ($rows as $row) {
            $expenseInfo = Model::fromArray((array) $row);
            $expenseCostInfo = $expenseInfo->getExpenseCost();
        } 
        if ($expenseCostInfo) {
           $expenseCostInfoArray = unserialize($expenseCostInfo);
           $totalExpenseCost = $expenseCostInfoArray['travel-cost'] + $expenseCostInfoArray['lunch-cost'];
        }       
        return $totalExpenseCost;
    }
    
}
