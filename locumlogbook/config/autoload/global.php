<?php
return array(
   'db' => array(
      'driver'         => 'Pdo',
      'dsn'            => 'mysql:dbname=zf2tutorial;host=localhost',
   ),
   'service_manager' => array(
      'factories' => array(
         'Zend\Db\Adapter\Adapter' => function ($serviceManager) {
            $adapterFactory = new Zend\Db\Adapter\AdapterServiceFactory();
               $adapter = $adapterFactory->createService($serviceManager);

               \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);

               return $adapter;
         }
      ),
   ),
);