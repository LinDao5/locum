<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance\Employertrans;

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
    protected $financetrans;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'finance_employer';

    /**
     * Initiliaze expenses collection
     *
     * @return void
     */
    public function init()
    {
       $this->getemployeeTrans(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\expenses\Model
     */
    public function getemployeeTrans($forceReload = false)
    {
        if (empty($this->financetrans) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );
            $financetrans = array();
            foreach ($rows as $row) {
                $financetrans[] = Model::fromArray((array) $row);
            }

            $this->financetrans = $financetrans;
        }
		return $rows;
       // return $this->income;
    }

    /* Get Expence Information by expense Id */
    public function getemployeeTransById($inId)
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
    
}
