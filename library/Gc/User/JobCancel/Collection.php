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
 * @subpackage User\JobAction
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\JobCancel;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of JobCancel Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobCancel
 */
class Collection extends AbstractTable
{
    /**
     * List of JobCancel
     *
     * @var array
     */
    protected $jobCancel;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_cancel';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getJobCancels(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getJobCancels($forceReload = false)
    {
        if (empty($this->jobCancel) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('c_id');
                    }
                )
            );

            $jobCancels = array();
            foreach ($rows as $row) {
                $jobCancels[] = Model::fromArray((array) $row);
            }

            $this->jobCancels = $jobCancels;
        }

        return $this->jobCancels;
    }

}

