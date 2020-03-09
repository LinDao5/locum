<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Questionaire;

 // Add these import statements:
 use Questionaire\Model\Questionaire_1;
 use Questionaire\Model\Questionaire_1Table;
 use Zend\Db\ResultSet\ResultSet;
 use Zend\Db\TableGateway\TableGateway;

class Module
{
    const VERSION = '3.1.0';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig() {
      return array(
         'factories' => array(
             'Questionaire\Model\Questionaire_1Table' =>  function($sm) {
                 $tableGateway = $sm->get('Questionaire_1TableGateway');
                 $table = new Questionaire_1Table($tableGateway);
                 return $table;
             },
             'Questionaire_1TableGateway' => function ($sm) {
                 $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                 $resultSetPrototype = new ResultSet();
                 $resultSetPrototype->setArrayObjectPrototype(new Questionaire_1());
                 return new TableGateway('questionaire', $dbAdapter, null, $resultSetPrototype);
             },
         ),
     );
   }
}
