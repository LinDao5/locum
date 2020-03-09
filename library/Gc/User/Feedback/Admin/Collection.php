<?php
/**
 * This source file is part of FUDUGO. *
 * 
 *
 * PHP Version >=5.3
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 * @author     Suraj Wasnik (suraj.wasnik0126@gmail.com)
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.fudugo.com
 */

namespace Gc\User\Feedback\Admin;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;

/**
 * Collection of Feedback Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Feedback
 */
class Collection extends AbstractTable
{
    /**
     * List of Feedbacks
     *
     * @var array
     */
    protected $feedbacks;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'feedback_qus';

    /**
     * Initiliaze role collection
     *
     * @return void
     */
    public function init()
    {
        $this->getFeedbacks(true);
    }

    /**
     * Get Roles
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Role\Model
     */
    public function getFeedbackQus($forceReload = false)
    {
        if (empty($this->feedbacks) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('fd_qus_sort_order ASC');
                    }
                )
            );

            $feedbacks = array();
            foreach ($rows as $row) {
                $feedbacks[] = Model::fromArray((array) $row);
            }

            $this->feedbacks = $feedbacks;
        }

        return $this->feedbacks;
    }
}
