<?php
/**
 * Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
 */
namespace FudugoApp;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication ()->getEventManager ();
        $moduleRouteListener = new ModuleRouteListener ();
        $moduleRouteListener->attach ( $eventManager );
        $eventManager->attach('dispatch', array($this, 'checkLoginChangeLayout'));

    }
    public function checkLoginChangeLayout(MvcEvent $e) {
	    if (! $e->getApplication ()->getServiceManager ()->get('Auth')->hasIdentity()) {
	        return true;
        }
    }
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
