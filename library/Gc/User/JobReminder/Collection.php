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

/**
 * Collection of JobReminder Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\JobReminder
 */
class Collection extends AbstractTable
{
    /**
     * List of JobReminders
     *
     * @var array
     */
    protected $JobReminders;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'job_reminder';

    /**
     * Initiliaze reminder collection
     *
     * @return void
     */
    public function init()
    {
        $this->getJobReminders(true);
    }

    /**
     * Get reminders
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\reminder\Model
     */
    public function getJobReminders($forceReload = false)
    {
        if (empty($this->JobReminders) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('r_id');
                    }
                )
            );

            $JobReminders = array();
            foreach ($rows as $row) {
                $JobReminders[] = Model::fromArray((array) $row);
            }

            $this->JobReminders = $JobReminders;
        }

        return $this->JobReminders;
    }
}
