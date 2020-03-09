<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobAction
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\JobAction;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * JobAction Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobAction
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_action';

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
        $jobActionTable = new Model();
        $jobActionTable->setData($array);
        $jobActionTable->setOrigData();

        return $jobActionTable;
    }
    public function updateJobaction($jid,$uid,$action,$f_notification)
    {

        $updateArray = array(
                'action' => $action,
                /*'f_notification' => $f_notification,*/
                'update_date' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'job_id' => $jid,
                'f_id'   => $uid,
            );
        $this->update($updateArray, $conditionArray);
    }

    /* Employer cancel job status update in Job action table */
    public function updateEmpCancelJobaction($jid,$action,$job_current_status = null)
    {
        $updateArray = array(
                'action' => $action,
                /*'f_notification' => $f_notification,*/
                'update_date' => date('Y-m-d H:i:as'),
            );
        if($job_current_status == 1){
            $conditionArray = array(
                'job_id' => $jid,
                'action' => 3,
            ); 
        }else{
            $conditionArray = array(
                'job_id' => $jid                
            ); 
        }

        
        $this->update($updateArray, $conditionArray);
    }
    public function updateFreezeJobaction($jid,$uid,$action,$f_notification)
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
    public function updateWaitingUnFreezeJobaction($jid,$uid,$action,$f_notification)
    {
        $updateArray = array(
                'action' => $action,                
                'update_date' => date('Y-m-d H:i:as'),
            );
        $conditionArray = array(
                'job_id' => $jid,
            );
        $data = $this->update($updateArray, $conditionArray);
    }
    public function updateJobactionCron($action)
    {
        $updateArray = array(
                'action' => $action,
            );
        $conditionArray = array(
                'action' => 1, 
            );
        $this->update($updateArray, $conditionArray);
    }
    public function updateJobactionCronReFreeze($action)
    {
        $updateArray = array(
                'action' => $action,
            );
        $conditionArray = array(
                'action' => 5              
            );
        $this->update($updateArray, $conditionArray);
    }
    
}
