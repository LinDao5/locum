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
class ReminderModel extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_reminder';

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
    public function updateJobReminder($jid,$uid,$action,$f_notification)
    {
        $updateArray = array(
                'action' => $action,
                'f_notification' => $f_notification,
                'update_date' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'job_id' => $jid,
                'f_id'   => $uid,
            );
        $this->update($updateArray, $conditionArray);
    }
    public function insertJobReminder($job_id,$uid,$fuid,$jobStartDate,$jobReminderDate)
    {
        $jobStartDate = str_replace('/','-',$jobStartDate);
        $saveArray = array(
                'j_id'=>$job_id,
                'e_id'=>$uid,
                'f_id'=>$fuid,
                'job_start_date'=>$jobStartDate,
                'job_reminder_date'=>$jobReminderDate
            ); 
        $this->insert($saveArray);
    }

    /*Calculate Reminder date 1. Before 3 days 2. Before 1 day */
    public function dateJobReminder($jobStartDate)
    {
        $r_date = array();
        if ($jobStartDate) {
            $jobStartDate = str_replace('/','-',$jobStartDate);
            //$r_date[0] = date('Y-m-d', strtotime('-7 days', strtotime($jobStartDate)));
            $r_date[0] = date('Y-m-d', strtotime('-1 days', strtotime($jobStartDate)));
            $reminderDates = serialize($r_date);
            /*echo $reminderDates;
            exit();*/
        }
        return $reminderDates;
    }

    
}
