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

namespace Gc\Component;

use Gc\Document\Collection as DocumentCollection;
use Gc\Document\Model as DocumentModel;
use Gc\DocumentType\Model as DocumentTypeModel;
use Gc\Layout\Model as LayoutModel;
use Gc\User\Model as UserModel;
use Gc\View\Model as ViewModel;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 20:40:09.
 *
 * @group Gc
 * @category Gc_Tests
 * @package  Library
 */
class TabsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tabs
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
        $this->object = new Tabs(array());
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
    public function testRenderWithIterableInterface()
    {
        $view = ViewModel::fromArray(
            array(
                'name' => 'View Name',
                'identifier' => 'View identifier',
                'description' => 'View Description',
                'content' => 'View Content'
            )
        );
        $view->save();

        $layout = LayoutModel::fromArray(
            array(
                'name' => 'Layout Name',
                'identifier' => 'Layout identifier',
                'description' => 'Layout Description',
                'content' => 'Layout Content'
            )
        );
        $layout->save();

        $user = UserModel::fromArray(
            array(
                'lastname' => 'User test',
                'firstname' => 'User test',
                'email' => 'pierre.rambaud86@gmail.com',
                'login' => 'test',
                'user_acl_role_id' => 1,
            )
        );

        $user->setPassword('test');
        $user->save();

        $documentType = DocumentTypeModel::fromArray(
            array(
                'name' => 'Document Type Name',
                'description' => 'Document Type description',
                'icon_id' => 1,
                'defaultview_id' => $view->getId(),
                'user_id' => $user->getId(),
            )
        );

        $documentType->save();

        $document = DocumentModel::fromArray(
            array(
                'name' => 'Document name',
                'url_key' => 'url-key',
                'status' => DocumentModel::STATUS_ENABLE,
                'show_in_nav' => true,
                'user_id' => $user->getId(),
                'document_type_id' => $documentType->getId(),
                'view_id' => $view->getId(),
                'layout_id' => $layout->getId(),
                'parent_id' => 0
            )
        );

        $document->save();
        $collection = new DocumentCollection();
        $collection->load(0);
        $this->assertEquals(
            sprintf(
                '<ul><li><a href="#tabs-%d">Document name</a></li></ul>',
                $document->getId()
            ),
            $this->object->render($collection->getChildren())
        );

        $document->delete();
        $documentType->delete();
        $layout->delete();
        $view->delete();
        $user->delete();
    }

    /**
     * Test
     *
     * @return void
     */
    public function testRenderWithParams()
    {
        $this->assertEquals('<ul><li><a href="#tabs-1">string</a></li></ul>', $this->object->render(array('string')));
    }

    /**
     * Test
     *
     * @return void
     */
    public function testRenderWithoutParams()
    {
        $this->object->setData(array('string'));
        $this->assertEquals('<ul><li><a href="#tabs-1">string</a></li></ul>', $this->object->render());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testToStringWithEmptyData()
    {
        $this->object->setData(array());
        $this->assertFalse($this->object->__toString());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testToStringWithoutEmptyData()
    {
        $this->object->setData(array('string'));
        $this->assertEquals('<ul><li><a href="#tabs-1">string</a></li></ul>', $this->object->__toString());
    }

    /**
     * Test
     *
     * @return void
     */
    public function testSetData()
    {
        $this->object->setData(array('string'));
        $this->assertEquals('<ul><li><a href="#tabs-1">string</a></li></ul>', $this->object->__toString());
    }
}
