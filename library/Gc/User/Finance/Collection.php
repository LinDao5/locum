<?php
/**
* Dvelope by SURAJ WASNIK (suraj.wasnik0126@gmail.com) at FUDUGO
*/

namespace Gc\User\Finance;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Finance Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Job
 */
class Collection extends AbstractTable
{
    /**
     * List of Jobs
     *
     * @var array
     */
    protected $finances;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_finance';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getFinances(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getFinances($forceReload = false)
    {
        if (empty($this->finances) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('id');
                    }
                )
            );

            $finances = array();
            foreach ($rows as $row) {
                $finances[] = Model::fromArray((array) $row);
            }

            $this->finances = $finances;
        }

        return $this->finances;
    }

    /* Get Finance info by job Id  */
    public function getFinanceInfoByJobId($jid)
    {
        $query = "job_id = '$jid'";
        $select = $this->select($query,
                function (Select $select, $query) {
                    $select->where($query);
                }
            );
        $rows = $this->fetchAll($select);
        
        $financeInfo = array();
        foreach ($rows as $row) {
            $financeInfo[] = Model::fromArray((array) $row);
        }
        return $financeInfo;
    }
    
}
