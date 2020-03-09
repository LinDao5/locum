<?php
/**
 * Collection controller to get all subscribe member in mailchimp, develope by SURAJ WASNIK at FUDUGO
 */

namespace Gc\User\Mailchimp;

use Gc\Db\AbstractTable;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Authentication\Adapter;
/**
 * Collection of Subscriber Model
 *
 * @category   Gc
 * @package    Library
 * @subpackage User\Subscriber
 */
class Collection extends AbstractTable
{
    /**
     * List of Subscribers
     *
     * @var array
     */
    protected $subscribers;

    /**
     * Table name
     *
     * @var string
     */
    protected $name = 'subscribe_user';

    /**
     * Initiliaze Subscriber collection
     *
     * @return void
     */
    public function init()
    {
        $this->getSubscribeUsers(true);
    }

    /**
     * Get Subscribers
     *
     * @param boolean $forceReload Force reload
     *
     * @return array \Gc\User\Mailchimp\Model
     */
    public function getSubscribeUsers($forceReload = false)
    {
        if (empty($this->subscribers) or $forceReload === true) {
            $rows = $this->fetchAll(
                $this->select(
                    function (Select $select) {
                        $select->order('s_id DESC');
                    }
                )
            );

            $subscribers = array();
            foreach ($rows as $row) {
                $subscribers[] = Model::fromArray((array) $row);
            }

            $this->subscribers = $subscribers;
        }

        return $this->subscribers;
    }

    /* Check if the user is already subscribe or not */
    public function checkSubscriber($email)
    {
        $is_exist = 0; // 0 Not exist 1 exist
        $whereArray = "email = '$email'";       
        $select = $this->select($whereArray,
            function (Select $select, $whereArray) {
                $select->where($whereArray);                
            }
        );        
        $rows  = $this->fetchAll($select);
        if (!empty($rows)) {
            $is_exist = 1;
        }
        return $is_exist;
    }
}
