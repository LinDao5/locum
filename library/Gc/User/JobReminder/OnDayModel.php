<?php
/**
 * This source file is part of FUDUGO.
 *
 * FUDUGO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * FUDUGO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with FUDUGO. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobReminder
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\JobReminder;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * JobReminder Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobReminder
 */
class OnDayModel extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_on_day';

    /**
     * Protected Professional name
     *
     * @var string $protectedname
     */
    const PROTECTED_NAME = 'Administrator';

    

    /**
     * Initiliaze from array
     *
     * @param array $array Data
     *
     * @return \Gc\User\Job\Model
     */
    public static function fromArray(array $array)
    {
        $JobReminderTable = new Model();
        $JobReminderTable->setData($array);
        $JobReminderTable->setOrigData();

        return $JobReminderTable;
    }
    public function updateJobOnDayStatus($job_id,$status)
    {
        $updateArray = array(
                'status' => $status,
            );
        $conditionArray = array(
                'j_id' => $job_id,
            );
        $this->update($updateArray, $conditionArray);
    }
    public function insertJobOnDayReminder($job_id,$uid,$fuid,$jobStartDate)
    {
        $saveArray = array(
                'j_id'=>$job_id,
                'e_id'=>$uid,
                'f_id'=>$fuid,
                'job_date'=>$jobStartDate,                
            );  
        $this->insert($saveArray);
    }

    /*Calculate Reminder date 1. Before 3 days 2. Before 1 day */
    public function dateJobReminder($jobStartDate)
    {
        $r_date = array();
        if ($jobStartDate) {
            $r_date[0] = date('Y-m-d', strtotime('-3 days', strtotime($jobStartDate)));
            $r_date[1] = date('Y-m-d', strtotime('-1 days', strtotime($jobStartDate)));
            $reminderDates = serialize($r_date);
            /*echo $reminderDates;
            exit();*/
        }
        return $reminderDates;
    }
    
   
    public function notificationStatusUpdate($onDayId)
    {
        $updateArray = array(
                'notify' => 1,
            );
        $conditionArray = array(
                'on_day_id' => $onDayId,
            );
        $this->update($updateArray, $conditionArray);
    }
    
}
