<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Bank;

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
     * List of supplier
     *
     * @var array
     */
    protected $bank_detail;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_bank_detail';

    /**
     * Initiliaze supplier collection
     *
     * @return void
     */
    public function init()
    {
        $this->getBankDetails(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getBankDetails($forceReload = false)
    {
        if (empty($this->bank_detail) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('bank_id');
                    }
                )
            );

            $this->bank_detail = $rows;
        }
        return $this->bank_detail;

    }  

    /* Get Bank detail by user id */
    public function getBankInfoByUserId($user_id)
    {
        $query = "user_id = '$user_id'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);            
        return $rows[0];
    }
    
      
}
