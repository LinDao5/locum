<?php


namespace Gc\User\Leaveuser;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Leaveuser Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Leaveuser
 */
class Collection extends AbstractTable
{
    /**
     * List of Leaveusers
     *
     * @var array
     */
    protected $leaveusers;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_leavers_table';

    /**
     * Initiliaze Leaveuser collection
     *
     * @return void
     */
    public function init()
    {
        //$this->getLeaveusers(true, $start_date = null, $end_date = null);
    }

    /**
     * Get Leaveusers
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Leaveuser\Model
     */
    public function getLeaveusers($forceReload = false, $start_date = null, $end_date = null)
    {
if($end_date == null || $end_date == ''){ $end_date = date('Y-m-d'); }
        if (empty($this->leaveusers) or $forceReload === true) {
            if ($start_date != null && $end_date != null) {
                $rows = $this->fetchAll("SELECT * FROM user_leavers_table WHERE created_at BETWEEN '$start_date' AND '$end_date'");
            }else{
                $rows = $this->fetchAll(
                    $this->select(
                        function (Select $select) {
                            $select->order('lid DESC');
                        }
                    )
                );
            }
            

            $leaveusers = array();
            foreach ($rows as $row) {
                $leaveusers[] = Model::fromArray((array) $row);
            }

            $this->leaveusers = $leaveusers;
        }

        return $this->leaveusers;
    }
}
