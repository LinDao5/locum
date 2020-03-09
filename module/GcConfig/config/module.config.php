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
 * @category Gc
 * @package  Config
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

return array(
    'display_exceptions'    => true,
    'controllers' => array(
        'invokables' => array(
            'ConfigController'          => 'GcConfig\Controller\IndexController',
            'UserController'            => 'GcConfig\Controller\UserController',
            'RoleController'            => 'GcConfig\Controller\RoleController',
            'ProfessionalController'    => 'GcConfig\Controller\ProfessionalController',
            'PackageController'         => 'GcConfig\Controller\PackageController',
            'PackageResourceController' => 'GcConfig\Controller\PackageresourceController',
            'RuleController'            => 'GcConfig\Controller\RuleController',
            'PaymenthistoryController'  => 'GcConfig\Controller\PaymenthistoryController',
            'QuestionController'        => 'GcConfig\Controller\QuestionController',
            'CmsController'             => 'GcConfig\Controller\CmsController',
            'ReportController'          => 'GcConfig\Controller\ReportController',
            'JobController'             => 'GcConfig\Controller\JobController',
            'FeedbackController'        => 'GcConfig\Controller\FeedbackController',
            'FinanceController'         => 'GcConfig\Controller\FinanceController',
            'FinanceTaxController'      => 'GcConfig\Controller\FinanceTaxController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'config' => __DIR__ . '/../views',
        ),
    ),
    'router' => array(
        'routes' => array(
            'config' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/config',
                    'defaults' =>
                    array (
                        'module'     => 'gcconfig',
                        'controller' => 'ConfigController',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'user' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/user',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'UserController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'forbidden' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/forbidden-access',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forbidden',
                                    ),
                                ),
                            ),
                            'login' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/login[/:redirect]',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'login',
                                    ),
                                ),
                            ),
                            'logout' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/logout',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                            'forgot-password' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/forgot-password',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'forgot-password-key' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/forgot-password/:id/:key',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'forgot-password',
                                    ),
                                ),
                            ),
                            'create' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/create',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'create',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/edit/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'edit',
                                    ),
                                ),
                            ),
                            'export-info' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/export-info',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'userInfoExport',
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/delete/:id',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'UserController',
                                        'action'     => 'delete',
                                    ),
                                ),
                            ),
                           
                            'role' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/role',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'RoleController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'RoleController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'finance' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/finance',
                                    'defaults' =>
                                        array (
                                            'module'     => 'gcconfig',
                                            'controller' => 'FinanceController',
                                            'action'     => 'index',
                                        ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'profitloss' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/profitloss/:id/:year',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceController',
                                                    'action'     => 'profitloss',
                                                ),
                                        ),
                                    ),
                                    'balancesheet' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/balancesheet/:id/:year',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceController',
                                                    'action'     => 'balancesheet',
                                                ),
                                        ),
                                    ),
                                    'all-transactions' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/all-transactions/:id/:year',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceController',
                                                    'action'     => 'alltransactions',
                                                ),
                                        ),
                                    ),
                                    'supplier-list' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/supplier-list/:id/:year',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceController',
                                                    'action'     => 'supplierlist',
                                                ),
                                        ),
                                    ),
                                    'tax' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/tax',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceTaxController',
                                                    'action'     => 'taxList',
                                                ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                            'create' => array(
                                                'type'    => 'Literal',
                                                'options' => array(
                                                    'route'    => '/create',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'taxAdd',
                                                    ),
                                                ),
                                            ),
                                            'edit' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/edit/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'taxEdit',
                                                    ),
                                                ),
                                            ),
                                            'delete' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/delete/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'taxDelete',
                                                    ),
                                                ),
                                            ),
                                        )
                                    ),
                                    'ni-tax' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/ni-tax',
                                            'defaults' =>
                                                array (
                                                    'module'     => 'gcconfig',
                                                    'controller' => 'FinanceTaxController',
                                                    'action'     => 'niTaxList',
                                                ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                            'create' => array(
                                                'type'    => 'Literal',
                                                'options' => array(
                                                    'route'    => '/create',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'niTaxAdd',
                                                    ),
                                                ),
                                            ),
                                            'edit' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/edit/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'niTaxEdit',
                                                    ),
                                                ),
                                            ),
                                            'delete' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/delete/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FinanceTaxController',
                                                        'action'     => 'niTaxDelete',
                                                    ),
                                                ),
                                            ),
                                        )
                                    ),
                                )
                            ),

                            
                            'professional' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/professional',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ProfessionalController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'ProfessionalController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'ProfessionalController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'ProfessionalController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'question' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/question',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'QuestionController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'QuestionController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'QuestionController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'QuestionController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'job' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/job',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'JobController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(              
                                    'view' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/view/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'JobController',
                                                'action'     => 'view',
                                            ),
                                        ),
                                    ),
                                    'search' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/search',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'JobController',
                                                'action'     => 'search',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'JobController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'package' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/package',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'PackageController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),

                            'feedback' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/feedback',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'FeedbackController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'FeedbackController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'FeedbackController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'FeedbackController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                    'user-feedback' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/user-feedback',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'FeedbackController',
                                                'action'     => 'feedbackList',
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                            'edit' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/edit/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FeedbackController',
                                                        'action'     => 'feedbackEdit',
                                                    ),
                                                ),
                                            ),
                                            'delete' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/delete/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FeedbackController',
                                                        'action'     => 'feedbackDelete',
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                    'dispute-feedback' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/dispute-feedback',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'FeedbackController',
                                                'action'     => 'feedbackDispute',
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes'  => array(
                                            'edit' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/edit/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FeedbackController',
                                                        'action'     => 'feedbackEdit',
                                                    ),
                                                ),
                                            ),
                                            'delete' => array(
                                                'type'    => 'Segment',
                                                'options' => array(
                                                    'route'    => '/delete/:id',
                                                    'defaults' =>
                                                    array (
                                                        'module'     => 'gcconfig',
                                                        'controller' => 'FeedbackController',
                                                        'action'     => 'feedbackDelete',
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                )
                            ),

                            'packageresource' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/packageresource',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'PackageResourceController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'create' => array(
                                        'type'    => 'Literal',
                                        'options' => array(
                                            'route'    => '/create',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageResourceController',
                                                'action'     => 'create',
                                            ),
                                        ),
                                    ),
                                    'edit' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/edit/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageResourceController',
                                                'action'     => 'edit',
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PackageResourceController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'paymenthistory' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/paymenthistory',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'PaymenthistoryController',
                                        'action'     => 'index',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(                                    
                                    'delete' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/delete/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PaymenthistoryController',
                                                'action'     => 'delete',
                                            ),
                                        ),
                                    ),
                                    'updatepaid' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/updatepaid/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PaymenthistoryController',
                                                'action'     => 'updatePaid',
                                            ),
                                        ),
                                    ),
                                    'updatepending' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/updatepending/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'PaymenthistoryController',
                                                'action'     => 'updatePending',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                        )
                    ),
                    'report' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/report',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'ReportController',
                                'action'     => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'package-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/package-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'packageReport',
                                    ),
                                ),
                            ),
                            'new-user-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/new-user-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'newUser',
                                    ),
                                ),
                            ),
                            'leave-user' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/leave-user',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'leaveUser',
                                    ),
                                ),
                            ),
                            'last-login-user' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/last-login-user',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'lastLoginUsers',
                                    ),
                                ),
                            ),
                            'emp-job-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/emp-job-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'empJobReport',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'single-emp' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/single-emp/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'ReportController',
                                                'action'     => 'singleEmp',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'fre-job-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/fre-job-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'freJobReport',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes'  => array(
                                    'single-fre' => array(
                                        'type'    => 'Segment',
                                        'options' => array(
                                            'route'    => '/single-fre/:id',
                                            'defaults' =>
                                            array (
                                                'module'     => 'gcconfig',
                                                'controller' => 'ReportController',
                                                'action'     => 'singleFre',
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'package-income-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/package-income-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'pkgIncomeReport',
                                    ),
                                ),
                            ),
                            'private-freelancer-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/private-freelancer-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'privateFreelancerReport',
                                    ),
                                ),
                            ),
                            'private-store-report' => array(
                                'type'    => 'Literal',
                                'options' => array(
                                    'route'    => '/private-store-report',
                                    'defaults' =>
                                    array (
                                        'module'     => 'gcconfig',
                                        'controller' => 'ReportController',
                                        'action'     => 'privateStoreReport',
                                    ),
                                ),
                            ),

                        ),
                    ),
                    'general' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/general',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editGeneral',
                            ),
                        ),
                    ),
                    'system' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/system',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editSystem',
                            ),
                        ),
                    ),
                    'email' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/email',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editEmail',
                            ),
                        ),
                    ),
                    'email-filter' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/email-filter',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'emailFilter',
                            ),
                        ),
                    ),
                    'notification' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/notification',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'notification',
                            ),
                        ),
                    ),
                    'server' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/server',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'editServer',
                            ),
                        ),
                    ),
                    'cms-update' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/update',
                            'defaults' =>
                            array (
                                'module'     => 'gcconfig',
                                'controller' => 'CmsController',
                                'action'     => 'update',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )
);