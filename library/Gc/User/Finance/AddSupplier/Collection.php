<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\AddSupplier;

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
    protected $supplier;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'add_supplier';

    /**
     * Initiliaze supplier collection
     *
     * @return void
     */
    public function init()
    {
        $this->getSupplier(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getSupplier($forceReload = false)
    {
        if (empty($this->supplier) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('supplier_id');
                    }
                )
            );

            $this->supplier = $rows;
        }
        return $this->supplier;

    }

    public function getLocumSupplier($user_id, $forceReload = false)
    {

        $query = "created_by = '$user_id'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        return $rows = $this->fetchAll($select);  

    }

    public function searchLocumSupplier($user_id, $store_name = '')
    {

        $query = "created_by = '$user_id' AND store_name like '%$store_name%'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        return $rows = $this->fetchAll($select);  

    }




    /* Get Expence Information by expense Id */
    public function getSupplierInfoById($Id)
    {
        $query = "supplier_id = '$Id'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);            
        return $rows[0];
    }
    

    public function getSupplierNameByStore($Id = null)
    {
    

    }

      
}
