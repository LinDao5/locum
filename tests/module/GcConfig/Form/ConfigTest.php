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

namespace GcConfig\Form;

use Gc\Registry;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-03-15 at 23:51:16.
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new Config;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testInit()
    {
        $this->assertNull($this->object->init());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testInitGeneral()
    {
        $this->assertInstanceOf('GcConfig\Form\Config', $this->object->initGeneral());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testInitSystem()
    {
        $this->assertInstanceOf('GcConfig\Form\Config', $this->object->initSystem());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testInitServer()
    {
        $this->assertInstanceOf(
            'GcConfig\Form\Config',
            $this->object->initServer(Registry::get('Application')->getConfig())
        );
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetValues()
    {
        $this->object->initServer(Registry::get('Application')->getConfig());
        $this->assertNull(
            $this->object->setValues(
                array(
                    array(
                        'identifier' => 'mail_from_name',
                        'value' => 'Pierre Rambaud'
                    )
                )
            )
        );
    }
}
