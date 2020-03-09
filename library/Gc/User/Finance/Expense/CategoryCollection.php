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
class CategoryCollection extends AbstractTable
{
    /**
     * List of expenses
     *
     * @var array
     */
    protected $expenseCategory;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'expense_type';

    /**
     * Initiliaze expenses collection
     *
     * @return void
     */
    public function init()
    {
        $this->getExpenseCategory(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getExpenseCategory($forceReload = false)
    {
        if (empty($this->expenseCategory) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $expenseCategory = array();
            foreach ($rows as $row) {
                $expenseCategory[] = Model::fromArray((array) $row);
            }

            $this->expenseCategory = $expenseCategory;
        }

        return $this->expenseCategory;
    }

    /* Get Cat Information by Cat Id */
    public function getExpenseCatInfoById($expCatId)
    {
        $query = "id = '$expCatId'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);        
        $expenseCatInfo = '';
        foreach ($rows as $row) {
            $expenseCatInfo = $row['expense'];
        }        
        return $expenseCatInfo;
    }

   
    
}
