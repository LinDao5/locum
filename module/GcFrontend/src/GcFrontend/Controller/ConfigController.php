<?php
/**
 * PHP Version >=5.3
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */

 namespace GcFrontend\Controller;
 use Gc\Mvc\Controller\Action;
 use Gc\view\Helper\Config as ConfigModule;
 use Gc\Core\Config as CoreConfig;
 use Gc\Registry;

 class ConfigController extends Action
 {
   // connectoin string
   public function connectionDB(){
		$adapter = new Zend\Db\Adapter\Adapter(array(
        'driver' => 'pdo_mysql',
        'username' => 'fudugoso_locum',
        'password' => 'locumkit123.*',
        'database' => 'fudugoso_locumkit',
        'hostname' => 'localhost'
	    ));
		return $adapter;
	}
 }