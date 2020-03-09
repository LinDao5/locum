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
 * @category Gc_Tests
 * @package  ZfModules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace GcFrontend;

use Gc\Registry;
use Gc\Core\Config as CoreConfig;
use Gc\Layout\Model as LayoutModel;
use Gc\View\Stream;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Mvc\Router\RouteMatch;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-03-15 at 23:51:32.
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Install
     */
    protected $object;

    /**
     * @var Zend\Uri\Http
     */
    protected $uri;

    /**
     * @var Zend\Mvc\MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var CoreConfig
     */
    protected $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object   = new Module;
        $this->uri      = Registry::get('Application')->getRequest()->getUri();
        $this->mvcEvent = Registry::get('Application')->getMvcEvent();
        $this->config   = Registry::get('Application')->getServiceManager()->get('CoreConfig');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->config->setValue('force_frontend_ssl', 0);
        $this->config->setValue('force_backend_ssl', 0);
        unset($this->object);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testOnBootstrap()
    {
        $oldAdapter = GlobalAdapterFeature::getStaticAdapter();
        $this->config->setValue('debug_is_active', 1);
        $this->config->setValue('session_lifetime', 3600);
        $this->config->setValue('cookie_domain', 'got-cms.com');
        $this->config->setValue('session_handler', CoreConfig::SESSION_DATABASE);

        $this->assertNull($this->object->onBootstrap(Registry::get('Application')->getMvcEvent()));

        GlobalAdapterFeature::setStaticAdapter($oldAdapter);
    }
}
