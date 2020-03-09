<?php
/**
 * Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
 */

use Gc\Core\Config as CoreConfig;
use Gc\User\Model as UserModel;
use Gc\View\Helper;
use Gc\Mvc\Resolver\AssetAliasPathStack;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;
use Zend\ModuleManager\Listener;
use AssetManager\Cache\ZendCacheAdapter;

return array( 
    'controllers' => array(
        'invokables' => array(
            'FudugoAppController'   => 'FudugoApp\Controller\IndexController',            
        ),
    ),    
    
    'view_manager' => array(            
        'template_path_stack' => array(
            'FudugoApp' => __DIR__ . '/../views',
        ) 
    ),    
);
