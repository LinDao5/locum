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
 * @package    GcBackend
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcBackend\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Document\Collection;
use Gc\User\Visitor;
use Gc\Version;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;

/**
 * Index controller for admin module
 *
 * @category   Gc_Application
 * @package    GcBackend
 * @subpackage Controller
 */
class IndexController extends Action
{
    /**
     * Display dashboard
     *
     * @return array
     */
    public function indexAction()
    {
        $data                    = array();
        $data['version']         = Version::VERSION;
        $data['versionIsLatest'] = Version::isLatest();
        $data['versionLatest']   = Version::getLatest();

        $documents                        = new Collection();
        $contentStats                     = array();
        $contentStats['online_documents'] = array(
            'count' => count($documents->getAvailableDocuments()),
            'label' => 'Online documents',
            'route' => 'content',
        );

        $contentStats['total_documents'] = array(
            'count' => count($documents->select()->toArray()),
            'label' => 'Total documents',
            'route' => 'content',
        );

        $data['contentStats'] = $contentStats;

        $visitorModel      = new Visitor();
        $data['userStats'] = array(
            'total_visitors' => array(
                'count' => $visitorModel->getTotalVisitors(),
                'label' => 'Total visitors',
                'route' => 'statistics',
            ),
            'total_visits' => array(
                'count' => $visitorModel->getTotalPageViews(),
                'label' => 'Total page views',
                'route' => 'statistics',
            ),
        );

        $coreConfig                = $this->getServiceLocator()->get('CoreConfig');
        $widgets                   = @unserialize($coreConfig->getValue('dashboard_widgets'));
        $data['dashboardSortable'] = !empty($widgets['sortable']) ? Json::encode($widgets['sortable']) : '{}';
        $data['dashboardWelcome']  = !empty($widgets['welcome']);

        $data['customeWidgets'] = array();
        $this->events()->trigger(__CLASS__, 'dashboard', $this, array('widgets' => &$data['customeWidgets']));

        return $data;
    }

    /**
     * Save dashboard
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function saveDashboardAction()
    {
        $coreConfig = $this->getServiceLocator()->get('CoreConfig');
        $params     = $this->getRequest()->getPost()->toArray();

        $config = @unserialize($coreConfig->getValue('dashboard_widgets'));

        if (empty($config)) {
            $config = array();
        }

        if (!empty($params['dashboard'])) {
            $config['welcome'] = false;
        } else {
            $config['sortable'] = $params;
        }

        $coreConfig->setValue('dashboard_widgets', serialize($config));

        return $this->returnJson(array('success' => true));
    }

    /**
     * Translator js action
     *
     * @return ViewModel
     */
    public function translatorAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }


    /**
     * Keep alive connection action
     *
     * @return ViewModel
     */
    public function keepAliveAction()
    {
        return $this->returnJson(array('success' => true));
    }

    public function fsUploadAction()
    {
        
        $basePath = "/home/umairc65/public_html/files/";        
        $baseUrl = 'https://'.$_SERVER['SERVER_NAME']."/public/media/ckeditor-file/";       
        $CKEditor = $_GET['CKEditor'] ;        
        $funcNum = $_GET['CKEditorFuncNum'] ;        
        $langCode = $_GET['langCode'] ;        
        $url = '' ;        
        $message = '';        
        if (isset($_FILES['upload'])) {           
           /*$file_img = $_FILES['upload']['name'];
           if (!file_exists($basePath)) {
               mkdir($basePath . $name, 0777, true);
           }            
              
            if (move_uploaded_file($_FILES["upload"]["tmp_name"], $basePath . $name)) {
               $message = "The file ". basename( $_FILES["upload"]["name"]). " has been uploaded.";
            } else {
               $message = "Sorry, there was an error uploading your file.";
            }         
            $url = $baseUrl . $name ;    */
            $url = '';
            $file_img = $_FILES['upload']['name'];
            $target_dir = $_SERVER['DOCUMENT_ROOT'].'/public/media/ckeditor-file/';
            $img_url ='https://'.$_SERVER['SERVER_NAME'];     
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }   
            $target_file = $target_dir . basename($_FILES['upload']['name']);
            if (file_exists($target_file)) {
                $rawBaseName = pathinfo($_FILES['upload']['name'], PATHINFO_FILENAME );
                $extension = pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION );
                $file_img = $rawBaseName .'-'. rand(0,999) . '.' . $extension;
                $target_file = $target_dir . $file_img;
            }
            if(move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file)){
                $url = $baseUrl . $file_img ;
            }else{
                $message = "Sorry, there was an error uploading your file.";
            }


        }
        else
        {
            $message = 'No file has been sent';
        }        
        echo "<script type='text/javascript'> window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message')</script>";

    }
}
