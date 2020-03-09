<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonquestionaire for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Questionaire;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'questionaire' => [
                'type'    => Segment::class, 
                'options' => [ 
                'route' => '/questionaire[/:user_id/:action[/:id]]', 
                  'constraints' => [ 
                     'action' => '[a-zA-Z][a-zA-Z0-9_-]*', 
                     'id'     => '[0-9]+', 
                  ], 
                  'defaults' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'index', 
                  ],

                  //questionaire1

                  'add1post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add1post', 
                  ],
                  'edit1post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit1post', 
                  ],
                  'delete1post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete1post', 
                  ],

                  'add1' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add1', 
                  ],
                  'edit1' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit1', 
                  ],
                  'list1' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list1', 
                  ],
                  

                  //questionaire2 part

                  'add2' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add2', 
                  ],
                  'list2' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list2', 
                  ],
                  'edit2' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit2', 
                  ],
                  'add2post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add2post', 
                  ],
                  'edit2post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit2post', 
                  ],
                  'delete2post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete2post', 
                  ],



                  'add3' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add3', 
                  ],
                  'list3' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list3', 
                  ],
                  'edit3' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit3', 
                  ],
                  'add3post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add3post', 
                  ],
                  'edit3post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit3post', 
                  ],
                  'delete3post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete3post', 
                  ],



                  'add4' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add4', 
                  ],
                  'list4' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list4', 
                  ],
                  'edit4' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit4', 
                  ],
                  'add4post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add4post', 
                  ],
                  'edit4post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit4post', 
                  ],
                  'delete4post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete4post', 
                  ],

                  

                  'add5post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add5post', 
                  ],
                  'edit5post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit5post', 
                  ],
                  'delete5post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete5post', 
                  ],

                  'add5' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add5', 
                  ],
                  'edit5' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit5', 
                  ],
                  'list5' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list5', 
                  ],


                  //quetionaire 6 part

                  'add6' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add6', 
                  ],
                  'list6' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'list6', 
                  ],
                  'edit6' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit6', 
                  ],
                  'add6post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'add6post', 
                  ],
                  'edit6post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'edit6post', 
                  ],
                  'delete6post' => [
                     'controller' => Controller\QuestionaireController::class, 
                     'action'     => 'delete6post',
                  ],
                  
               ], 
             
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\QuestionaireController::class => InvokableFactory::class,
        ],
    ],
    // 'view_manager' => [
    //     'display_not_found_reason' => true,
    //     'display_exceptions'       => true,
    //     'doctype'                  => 'HTML5',
    //     'not_found_template'       => 'error/404',
    //     'exception_template'       => 'error/index',
    //     'template_map' => [
    //         'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
    //         'questionaire/index/index' => __DIR__ . '/../view/questionaire/index/index.phtml',
    //         'error/404'               => __DIR__ . '/../view/error/404.phtml',
    //         'error/index'             => __DIR__ . '/../view/error/index.phtml',
    //     ],
    //     'template_path_stack' => [
    //         __DIR__ . '/../view',
    //     ],
    // ],
    'view_manager' => [
         'template_map' => [
          'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
          'error/404'               => __DIR__ . '/../view/error/404.phtml',
          'error/index'             => __DIR__ . '/../view/error/index.phtml',
          
      ],
      'template_path_stack' => ['questionaire' => __DIR__ . '/../view',],
      'strategies' => array(
              'ViewJsonStrategy'
        )
      ]
];
