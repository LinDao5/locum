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
 * @subpackage User\Question
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\User\Question;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Question Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Question
 */
class Collection extends AbstractTable
{
    /**
     * List of Questions
     *
     * @var array
     */
    protected $questions;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'user_question';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getQuestions(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getQuestions($forceReload = false)
    {
        if (empty($this->questions) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('sort_order ASC');
                    }
                )
            );

            $questions = array();
            foreach ($rows as $row) {
                $questions[] = Model::fromArray((array) $row);
            }

            $this->questions = $questions;
        }

        return $this->questions;
    }
}
