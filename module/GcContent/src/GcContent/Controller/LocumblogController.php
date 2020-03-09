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

namespace GcContent\Controller;

use Gc\Mvc\Controller\Action;
use Gc\User\Professional;
use GcFrontend\Helper\FinanceHelper as FinanceHelper;
/**
 * Cms controller
 *
 * @category   Gc_Application
 * @package    GcConfig
 * @subpackage Controller
 */
class LocumblogController extends Action
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

        public function getProfession()
    {
        $professionCollections = new Professional\Collection();
        $professions = array();
        foreach ($professionCollections->getProfessionals() as $profession) {
            $professions[] = $profession;
        }
        return   $professions;
    }

    public function slugAction(){
        echo $this->getRouteMatch()->getParam('slug');     
die();   
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
        $professions = $this->getProfession();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();


            $data = array(
                'title' => str_replace("'" , "&#39", htmlentities(trim($post['title']))),
                'slug' => strtolower(htmlentities(str_replace(' ','-',$post['slug']))),
                'description' => str_replace("'" , "&#39", trim($post['description'])),
                'user_type' => implode(',',$post['user_type']) ,
                'category_id' => implode(',',$post['profession_type']) ,
                'status' =>  $post['status'],
                'metatitle' =>  $post['metatitle'],
                'metadescription' =>  $post['metadescription'],
                'metakeywords' =>  $post['metakeywords']
            );


            $slugArray = $financeHelper->checkBlogSlugExist($data['slug'], 'news');
            if($id != ''){
               if(!empty($slugArray[0]) && $slugArray[0]['id'] != $id ){
                   $this->flashMessenger()->addErrorMessage('Slug should be unique.');
                   $this->redirect()->toUrl('/admin/content/industrynews/edit/'.$id);
                   return array('listnews' => $listnews ,'professions' => $professions);
               }
            }else{
               if(!empty($slugArray[0])){
                   $this->flashMessenger()->addErrorMessage('Slug should be unique.');
                   $this->redirect()->toUrl('/admin/content/industrynews/add');                   
               }                
            }


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
            }else{
                $data['image_path'] = 'public/media/files/industry_news/logo.png';
            }


            $financeHelper->InsertIndustrynews($data);
            if($id != ''){
               $this->flashMessenger()->addSuccessMessage('News updated successfully.');
               $this->redirect()->toUrl('/admin/content/industrynews/edit/'.$id);            
            }else{
               $this->flashMessenger()->addSuccessMessage('News added successfully.');
               $this->redirect()->toUrl('/admin/content/industrynews');     
            }

        }

        return array('listnews' => $listnews ,'professions' => $professions);
    }
    
    
        public function blogpostAction()
    {
        $financeHelper = new FinanceHelper();
        $listnews =  $financeHelper->getpostblog(null, 0);
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
        $blogcategory =  $financeHelper->getblogcategory();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();


            $data = array(
                'title' => str_replace("'" , "&#39", htmlentities(trim($post['title']))),
                'slug' => strtolower(htmlentities(str_replace(' ','-',$post['slug']))),
                'description' => str_replace("'" , "&#39", trim($post['description'])) ,
                'user_type' => null ,
                'category_id' => implode(',',$post['category_id']) ,
                'status' =>  $post['status'],
                'metatitle' =>  $post['metatitle'],
                'metadescription' =>  $post['metadescription'],
                'metakeywords' =>  $post['metakeywords']
            );

            $slugArray = $financeHelper->checkBlogSlugExist($data['slug'], 'blog');
            if($id != ''){
               if(!empty($slugArray[0]) && $slugArray[0]['id'] != $id ){
                   $this->flashMessenger()->addErrorMessage('Slug should be unique.');
                   $this->redirect()->toUrl('/admin/content/blogpost/edit/'.$id);
                   return array('listnews' => $listnews, 'blogcategory' => $blogcategory );
               }
            }else{
               if(!empty($slugArray[0])){
                   $this->flashMessenger()->addErrorMessage('Slug should be unique.');
                   $this->redirect()->toUrl('/admin/content/blogpost/add/');                   
               }                
            }
           
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
            }else{
                $data['image_path'] = 'public/media/files/industry_news/logo.png';
            }

            // pdf upload section
            if($_FILES["pdf"] != '') {
                $target_dir = "public/media/files/industry_news/";
                $target_filenew = md5(uniqid() . time()) . "_" . basename($_FILES["pdf"]["name"]);
                $target_file = $target_dir . $target_filenew;
                if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
                    $pdf = $target_file;
                }
            }
            $data['pdf_path'] =  $pdf;
            if($id != '' && $post['new_id'] !='') {
                $data['id'] =  $post['new_id'];
                if($data['pdf_path'] == ''){
                    unset($data['pdf_path']);
                }
            }else {
                $data['pdf_path'] = '';
            }

            $financeHelper->Insertblogpost($data);
            
            if($id != ''){
               $this->flashMessenger()->addSuccessMessage('Post updated successfully.');
               $this->redirect()->toUrl('/admin/content/blogpost/edit/'.$id);            
            }else{
               $this->flashMessenger()->addSuccessMessage('Post added successfully.');
               $this->redirect()->toUrl('/admin/content/blogpost');
            }

        }

        return array('listnews' => $listnews, 'blogcategory' => $blogcategory );
    }

}
