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
 * @package  Library
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc\User;

use Gc\Registry;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 20:40:08.
 *
 * @group Gc
 * @category Gc_Tests
 * @package  Library
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Model
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
        $collection = new Collection();
        foreach ($collection->getUsers() as $user) {
            $user->delete();
        }

        unset($collection);

        $this->object = Model::fromArray(
            array(
                'lastname' => 'Test',
                'firstname' => 'Test',
                'email' => 'pierre.rambaud86@gmail.com',
                'login' => 'test-user-model',
                'user_acl_role_id' => 1,
                'active' => true
            )
        );

        $this->object->setPassword('test-user-model-password');
        $this->object->save();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->object->delete();
        unset($this->object);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $this->assertTrue($this->object->authenticate('test-user-model', 'test-user-model-password'));
    }


    /**
     * Test
     *
     * @return void
     */
    public function testFakeAuthenticate()
    {
        $this->assertFalse($this->object->authenticate('test', 'wrong password'));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetEmail()
    {
        $this->assertTrue($this->object->setEmail('test-user-model@test.com'));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetFakeEmail()
    {
        $this->assertFalse($this->object->setEmail('wrong email'));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetEncryptedPassword()
    {
        $password = sha1('test');
        $this->object->setPassword('test');
        $this->assertEquals($password, $this->object->getPassword());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSave()
    {
        $this->assertInternalType('integer', (int) $this->object->save());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSaveWithWrongValues()
    {
        $configuration = Registry::get('Application')->getConfig();
        if ($configuration['db']['driver'] == 'pdo_mysql') {
            $this->markTestSkipped('Mysql does not thrown exception.');
        }

        $this->setExpectedException('\Gc\Exception');
        $model = new Model();
        $model->setId('undefined');
        $this->assertFalse($model->save());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testDeleteWithoutId()
    {
        $model = new Model();
        $this->assertFalse($model->delete());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testDelete()
    {
        $this->assertTrue($this->object->delete());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testDeleteWithWrongId()
    {
        $configuration = Registry::get('Application')->getConfig();
        if ($configuration['db']['driver'] == 'pdo_mysql') {
            $this->markTestSkipped('Mysql does not thrown exception.');
        }

        $this->setExpectedException('\Gc\Exception');
        $model = new Model();
        $model->setId('undefined');
        $this->assertFalse($model->delete());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testFromArray()
    {
        $this->object->delete();
        $this->object = Model::fromArray(
            array(
                'lastname' => 'Test',
                'firstname' => 'Test',
                'email' => 'pierre.rambaud86@gmail.com',
                'login' => 'test',
                'user_acl_role_id' => 1,
            )
        );

        $this->assertEquals('pierre.rambaud86@gmail.com', $this->object->getEmail());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testFromId()
    {
        $currentId = $this->object->getId();
        $model     = Model::fromId($currentId);

        $this->assertEquals($this->object->getName(), $model->getName());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testFromFakeId()
    {
        $this->assertFalse(Model::fromId(10000));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetRole()
    {
        $this->assertInstanceOf('Gc\User\Role\Model', $this->object->getRole());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSendForgotPasswordWithWrongEmail()
    {
        $this->assertFalse($this->object->sendForgotPasswordEmail('wrong@email.test'));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSendForgotPasswordWithEmail()
    {
        $this->assertTrue($this->object->sendForgotPasswordEmail('pierre.rambaud86@gmail.com'));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals('Test Test', $this->object->getName());
    }
}
