<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Expense;

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
    protected $expenses;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_expense_table';

    /**
     * Initiliaze expenses collection
     *
     * @return void
     */
    public function init()
    {
        $this->getExpenses(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getExpenses($forceReload = false)
    {
        if (empty($this->expenses) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $expenses = array();
            foreach ($rows as $row) {
                $expenses[] = Model::fromArray((array) $row);
            }

            $this->expenses = $expenses;
        }

        return $this->expenses;
    }

    /* Get Expence Information by expense Id */
    public function getExpenseInfoById($expId)
    {
        $query = "id = '$expId'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $expenseInfo = array();
        foreach ($rows as $row) {
            $expenseInfo[] = Model::fromArray((array) $row);
        }        
        return $expenseInfo;
    }

    /* Get Expence cost Information by expense Id */
    public function getExpenseCostInfoById($expId)
    {
        return 1;
    }
    
}
