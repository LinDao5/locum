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
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcFrontend\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Document;
use Gc\Layout;
use Gc\Property;
use Gc\View;
use Zend\View\Model\ViewModel;
use Exception;
use Zend\Db\Adapter\Adapter as Adapter;

/**
 * Index controller for module Application
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */
class DbController extends Action
{
    /**
     * View filename
     *
     * @var string
     */
    const VIEW_PATH = 'application/index/view-content';

    /**
     * View filename
     *
     * @var string
     */
    const LAYOUT_PATH = 'application/index/layout-content';

    

    public function locumkitDbConfig()
    {
        $adapter = new Adapter(array(
		'driver' => 'pdo_mysql',
		'username' => 'root',
		'password' => '',
		'database' => 'umairc65_locumkit',
		'hostname' => 'localhost'
        ));
        return $adapter;
    }
}
