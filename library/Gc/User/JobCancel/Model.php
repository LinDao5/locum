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
 * @subpackage User\JobCancel
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\JobCancel;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

/**
 * JobCancel Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobCancel
 */
class Model extends AbstractTable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_cancel';

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
    public function addCancelRecord($jid,$uid,$role,$reason,$status)
    {
        try {
            $insertArray = array(
                'c_job_id'  => $jid,
                'c_uid'     => $uid,
                'c_urole'   => $role,
                'c_reason'  => $reason,          
                'c_job_status'  => $status,          
                );
            $this->insert($insertArray);
            return 1;
        } catch (\Exception $e) {
            
            throw new \Gc\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
