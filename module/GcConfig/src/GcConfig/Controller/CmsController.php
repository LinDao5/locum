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
 * @package    GcConfig
 * @subpackage Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace GcConfig\Controller;

use Gc\Mvc\Controller\Action;
use Gc\Core\Updater;
use Gc\Media\Info;
use Gc\Version;
use Gc\User\Collection as UserCollection;
use Gc\User\Model as UserModel;
use GcConfig\Form\Config as configForm;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Exception;
use GcFrontend\Controller\JobmailController as JobmailController;
use Gc\User\Professional;
use GcFrontend\Helper\FinanceHelper as FinanceHelper;
/**
 * Cms controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class CmsController extends Action
{
    /**
     * Config form
     *
     * @var configForm $form
     */
    protected $form;

    /**
     * Contains information about acl
     *
     * @var array
     */
    //protected $aclPage = array('resource' => 'settings', 'permission' => 'config');

    /**
     * Generate general configuration form
     *
     * @return void
     */
    public function editGeneralAction()
    {
        $this->form = new configForm();
        $this->form->initGeneral();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate system configuration form
     *
     * @return void
     */
    public function editSystemAction()
    {
        $this->form = new configForm();
        $this->form->initSystem();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate server configuration form
     *
     * @return void
     */
    public function editServerAction()
    {
        $this->form = new configForm();
        $this->form->initServer($this->getServiceLocator()->get('Config'));
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Notification time manager form devlop by Suraj Wasnik
     *
     * @return void
     */

    public function notificationAction()
    {
        $this->form = new configForm();
        $this->form->initNotificationConfig();
        return $this->forward()->dispatch('CmsController', array('action' => 'edit'));
    }

    /**
     * Generate form and display
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function editAction()
    {
        $coreConfig = $this->getServiceLocator()->get('CoreConfig');
        $values     = $coreConfig->getValues();
        $this->form->setAttribute(
            'action',
            $this->url()->fromRoute($this->getRouteMatch()->getMatchedRouteName())
        );
        $this->form->setValues($values);

        if ($this->getRequest()->isPost()) {
            $this->form->setData($this->getRequest()->getPost()->toArray());

            if (!$this->form->isValid()) {
                $this->flashMessenger()->addErrorMessage('Can not save configuration');
                $this->useFlashMessenger();
            } else {
                $inputs = $this->form->getInputFilter()->getValidInput();
                foreach ($inputs as $input) {
                    
                    if (method_exists($input, 'getName')) {

                        $coreConfig->setValue($input->getName(), $input->getValue());
                    }
                }

                $this->flashMessenger()->addSuccessMessage('Configuration saved');
                return $this->redirect()->toRoute($this->getRouteMatch()->getMatchedRouteName());
            }
        }

        return array('form' => $this->form);
    }

    /**
     * Update cms
     *
     * @return \Zend\View\Model\ViewModel|array
     */
    public function updateAction()
    {
        $versionIsLatest = Version::isLatest();
        $latestVersion   = Version::getLatest();
        $session         = $this->getSession();

        if ($this->getRequest()->isPost()) {
            $updater         = new Updater();
            $versionIsLatest = false;
            if (!$updater->load($this->getRequest()->getPost()->get('adapter')) or $versionIsLatest) {
                $this->flashMessenger()->addErrorMessage('Can\'t set adapter');
                return $this->redirect()->toRoute('config/cms-update');
            }

            $currentVersion = Version::VERSION;

            //Fetch content
            if ($updater->update()) {
                //Upgrade cms
                if ($updater->upgrade()) {
                    //Update modules
                    $modules = $this->getServiceLocator()->get('CustomModules')->getLoadedModules();
                    foreach ($modules as $module) {
                        if (method_exists($module, 'update')) {
                            try {
                                $module->update($latestVersion);
                            } catch (Exception $e) {
                                //don't care
                            }
                        }
                    }

                    //Update database
                    $configuration = $this->getServiceLocator()->get('Config');
                    $dbAdapter     = GlobalAdapterFeature::getStaticAdapter();
                    if (!$updater->updateDatabase($configuration, $dbAdapter)) {
                        //Rollback cms
                        $updater->rollback($currentVersion);
                    } else {
                        $updater->executeScripts();
                        $session['updateOutput'] = $updater->getMessages();

                        $this->flashMessenger()->addSuccessMessage(sprintf('Cms update to %s', $latestVersion));
                        return $this->redirect()->toRoute('config/cms-update');
                    }
                }
            }

            foreach ($updater->getMessages() as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }

            return $this->redirect()->toRoute('config/cms-update');
        }

        if (!empty($session['updateOutput'])) {
            $updateOutput = $session['updateOutput'];
            unset($session['updateOutput']);
        }

        //Check modules and datatypes
        $datatypesErrors = array();
        $this->checkVersion($this->getServiceLocator()->get('DatatypesList'), 'datatype', $datatypesErrors);
        $modulesErrors = array();
        $this->checkVersion($this->getServiceLocator()->get('ModulesList'), 'module', $modulesErrors);

        return array(
            'gitProject'      => file_exists(GC_APPLICATION_PATH . '/.git'),
            'isLatest'        => $versionIsLatest,
            'latestVersion'   => $latestVersion,
            'datatypesErrors' => $datatypesErrors,
            'modulesErrors'   => $modulesErrors,
            'updateOutput'    => empty($updateOutput) ? '' : $updateOutput,
        );
    }

    /**
     * Check version in info file
     * from $type directory
     *
     * @param array  $directories List of directories
     * @param string $type        Type of directory
     * @param array  &$errors     Insert in this all errors
     *
     * @return void
     */
    protected function checkVersion(array $directories, $type, array &$errors)
    {
        $latestVersion = Version::getLatest();
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                $filename = $directory . '/' . $type . '.info';
                $info     = new Info();

                if ($info->fromFile($filename) === true) {
                    $infos = $info->getInfos();
                    if (!empty($infos['cms_version'])) {
                        preg_match('~(?<operator>[>=]*)(?<version>.+)~', $infos['cms_version'], $matches);
                        if (empty($matches['operator'])) {
                            if (version_compare($latestVersion, $matches['version']) === 1) {
                                $errors[] = basename($directory);
                            }
                        } else {
                            if (!version_compare($latestVersion, $matches['version'], $matches['operator'])) {
                                $errors[] = $directory;
                            }
                        }
                    }
                }
            }
        }
    }

    public function editEmailAction()
    {
        $this->form = new configForm();
        $this->form->initEmailConfig();
        return $this->forward()->dispatch('CmsController', array('action' => 'sendEmail'));
    }
    

    

    public function emailFilterAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $filter_id = $this->getRequest()->getPost()->get('filter_id');
            $val = json_decode($filter_id, TRUE);
            if($val != ''){
                $user_email = $this->getUserFilterList($val['pro'],$val['role']);
            }else{
                $user_email = $this->getUserFilterList(0,0);
            }
       /*     switch ($filter_id) {
                case '1':
                        $user_email = $this->getUserFilterList(3,0);
                    break;
                case '2':
                        $user_email = $this->getUserFilterList(4,0);
                    break;
                case '3':
                        $user_email = $this->getUserFilterList(1,0);
                    break;
                case '4':
                        $user_email = $this->getUserFilterList(3,3);
                    break;
                case '5':
                        $user_email = $this->getUserFilterList(3,2);
                    break;
                case '6':
                        $user_email = $this->getUserFilterList(1,3);
                    break;
                case '7':
                        $user_email = $this->getUserFilterList(1,2);
                    break;
                case '8':
                        $user_email = $this->getUserFilterList(4,3);
                    break;
                case '9':
                        $user_email = $this->getUserFilterList(4,2);
                    break;                               
                default:
                        $user_email = $this->getUserFilterList(0,0);
                    break;
            }*/
            
            return array('user_email' => $user_email);
        }
    }
    
    public function sendEmailAction()
    {

        $request = $this->getRequest();
        if($request->isPost())
        {
            $post = $request->getPost();
            if(!empty($post->get('email'))) {
                $email = $post->get('email');
            }elseif(!empty($post->get('email_user'))){            
                $email = $post->get('email_user');
            }
            if(!empty($post->get('mail_message'))) {
                $msg1 = $post->get('mail_message');
            }elseif(!empty($post->get('user_mail_message'))){            
                $msg1 = $post->get('user_mail_message');
            }
            if(!empty($post->get('mail_subject'))) {
                $mail_subject = $post->get('mail_subject');
            }elseif(!empty($post->get('user_mail_subject'))){            
                $mail_subject = $post->get('user_mail_subject');
            }
            /*print_r($email);
            exit();*/

            /*echo $userName = $this->userName('suraj.work0126@gmail.com');
            exit();*/

            if (!empty($post->get('user_marketing_mail_ids'))) {
                $email = explode(';', $post->get('user_marketing_mail_ids'));
            }
            if (!empty($post->get('user_marketing_mail_subject'))) {
                $mail_subject = $post->get('user_marketing_mail_subject');
            }
            if (!empty($post->get('user_marketing_mail_message'))) {
                $msg1 = $post->get('user_marketing_mail_message');
            }

            $jobmailController = new JobmailController();
            

            if(empty($email) or empty($msg1))
            {
                return array('email' => $email, 'message' => $msg1, 'error_message' => 'Please fill all fields');
            }
            else
            {
                $mailSend = 0;
                foreach ($email as $key => $value) {
                    if(!empty($value)){
                        try {
                            $arr = explode("@", $value);
                            $userName = $arr[0];
                            //$msg2 = '<p>Hello '.$userName.', </p>';
                            $msg2 = '';
                            //$message = $jobmailController->mailHeader();
                            //$message .= '<div style="padding: 25px 50px 5px; text-align: left; font-family: sans-serif;">';
                            //$message .= '<p>'.$msg2.'</p><p>'.$msg1.'</p>';
                            //$message .= '</div>';
                            //$message .= $jobmailController->mailFooter(); 
                            $message = $msg1;
                            /*$mail = new \Gc\Mail('utf8', $message);
                            $mail->getHeaders()->addHeaderLine('Content-type','text/html');
                            $mail->setFrom($this->getServiceLocator()->get('CoreConfig')->getValue('site_email'),$this->getServiceLocator()->get('CoreConfig')->getValue('mail_from_name'));
                            $mail->addTo($value);
                            $mail->setSubject($mail_subject);
                            $mail->send();*/
                            if (!empty($post->get('user_marketing_mail_message'))) {
                                  $jobmailController->sendSMTPMail2($message, $this->getServiceLocator()->get('CoreConfig')->getValue('site_email'), $value, '', $mail_subject );
                            }else{
                                  $jobmailController->sendSMTPMail($message, $this->getServiceLocator()->get('CoreConfig')->getValue('site_email'), $value, '', $mail_subject );
                            }

                        } catch (Exception $e) {
                            //$this->flashMessenger()->addErrorMessage($e->getMessage());
                        }
                    }

                }
                $this->flashMessenger()->addSuccessMessage('Message sent');
                $this->redirect()->toUrl('/admin/config/email');
                
            }
            
            
        }

        return array('form' => $this->form , 'professions' => $this->getProfession());
    }

    public function userName($email){
        $returnUserName = '';
        $userCollection = new UserCollection();
        $userList = $userCollection->getUsers();
        foreach ($userList as $userModel) {
            if ($email == $userModel->getEmail()) {
               $returnUserName = $userModel->getFirstname();
            }
        }
        return $returnUserName;

    }

    public function getUserFilterList($cat_id,$u_type)
    {
        $userTable = new UserModel();
        if ($cat_id == 0 && $u_type == 0) {
            $rows = $userTable->fetchAll($userTable->select(array('active'=>1)));
        }elseif($cat_id != 0 && $u_type == 0){
            $rows = $userTable->fetchAll($userTable->select(array('user_acl_profession_id'=>$cat_id, 'active'=>1 )));
        }else{
            $rows = $userTable->fetchAll($userTable->select(array('user_acl_role_id' =>$u_type , 'user_acl_profession_id'=>$cat_id, 'active'=>1 ))); 
        }         
        return $rows;
    }
    
        public function getProfession()
    {
        $professionCollections = new Professional\Collection();
        $professions = array();
        foreach ($professionCollections->getProfessionals() as $profession) {
            $professions[] = $profession;
        }
        return   $professions;
    }

    public function industrynewsAction()
    {
        $financeHelper = new FinanceHelper();
        $professions = $this->getProfession();
        
        if($_GET['q'] == 'fre'){
            //$listnews =  $financeHelper->getIndustrynews(null,2,$_GET['c']);
        }elseif($_GET['q'] == 'emp'){
            //$listnews =  $financeHelper->getIndustrynews(null,3,$_GET['c']);
        }else{
            //$listnews =  $financeHelper->getIndustrynews(null,2,1);
        }


        $listnews =  $financeHelper->getIndustrynews();
        return array('newsdata' => $listnews ,'professions' => $professions);
    }

    public function addindustrynewsAction()
    {
        $financeHelper = new FinanceHelper();

        $id    = $this->getRouteMatch()->getParam('id');
        if($id != ''){
            $listnews =  $financeHelper->getIndustrynews($id);
            $listnews =  $listnews['0'] ;
        }else{

            $listnews = null ;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();


            $data = array(
                'title' => str_replace("'" , "&#39", htmlentities(trim($post['title']))),
                'description' => str_replace("'" , "&#39", trim($post['description'])),
                'user_type' => implode(',',$post['user_type']) ,
                'category_id' => implode(',',$post['profession_type']) ,
                'status' =>  $post['status'],
            );
            //fileupload section
            if($_FILES["image"] != '') {
                $target_dir = "public/media/files/industry_news/";
                $target_filenew = md5(uniqid() . time()) . "_" . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $target_filenew;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                }
            }
            $data['image_path'] =  $image ;
            if($id != '' && $post['new_id'] !=''){
                $data['id'] =  $post['new_id'] ;
                if($data['image_path'] == ''){
                    unset($data['image_path']);
                }
            }

            $financeHelper->InsertIndustrynews($data);
            $this->flashMessenger()->addSuccessMessage('ADD');
            $this->redirect()->toUrl('/admin/config/industry-news');
        }
        $professions = $this->getProfession();
        return array('listnews' => $listnews ,'professions' => $professions);
    }
    
    
        public function blogpostAction()
    {
        $financeHelper = new FinanceHelper();
        $listnews =  $financeHelper->getpostblog();
        $blogcategory =  $financeHelper->getblogcategory();
        return array('newsdata' => $listnews , 'blogcategory' => $blogcategory );
    }

    public function addblogpostAction()
    {
        $financeHelper = new FinanceHelper();

        $id    = $this->getRouteMatch()->getParam('id');
        if($id != ''){
            $listnews =  $financeHelper->getpostblog($id);
            $listnews =  $listnews['0'] ;
        }else{
            $listnews = null ;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();


            $data = array(
                'title' => str_replace("'" , "&#39", htmlentities(trim($post['title']))),
                'description' => str_replace("'" , "&#39", trim($post['description'])) ,
                'user_type' => null ,
                'category_id' => implode(',',$post['category_id']) ,
                'status' =>  $post['status'],
            );
           
            //fileupload section
            if($_FILES["image"] != '') {
                $target_dir = "public/media/files/industry_news/";
                $target_filenew = md5(uniqid() . time()) . "_" . basename($_FILES["image"]["name"]);
                $target_file = $target_dir . $target_filenew;
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                }
            }
            $data['image_path'] =  $image ;
            if($id != '' && $post['new_id'] !=''){
                $data['id'] =  $post['new_id'] ;
                if($data['image_path'] == ''){
                    unset($data['image_path']);
                }
            }

            $financeHelper->Insertblogpost($data);
            $this->flashMessenger()->addSuccessMessage('ADD');
            $this->redirect()->toUrl('/admin/config/blog-post');
        }
         $blogcategory =  $financeHelper->getblogcategory();
        return array('listnews' => $listnews, 'blogcategory' => $blogcategory );
    }
}
