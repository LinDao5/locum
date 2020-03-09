<?php
	/**
	*  Package privileges controller develope by SURAJ WASNIK at FUDUGO
	*/
	namespace GcFrontend\Controller;
	use GcFrontend\Controller\DbController as DbController;
	
	class PackagePrivilegesController
	{
		/* Get current user package ID */
		public function getCurrentPackagePrivilegesResources($privilege_key = null, $user_id = null,$adapter = null)
		{
			if ($adapter == null) {
				$dbConfig   = new DbController();
    			$adapter    = $dbConfig->locumkitDbConfig();
			}
					

			$package_id = $this->getUserPackageId($user_id,$adapter);
			$sql_pkg_resources = "SELECT user_acl_package_resources_id FROM user_acl_package WHERE id= '$package_id'";
			$pkg_resources 	= $adapter->query($sql_pkg_resources, $adapter::QUERY_MODE_EXECUTE);
			$pkg_resources_obj = $pkg_resources->current();

			$pkg_resources = unserialize($pkg_resources_obj->user_acl_package_resources_id);
			$key_resources = $this->getAllOrivilegesResources($privilege_key,$adapter);

			
			if(in_array($key_resources['id'], $pkg_resources)){
				return 1;
			}else{
				return 0;
			}
		}

		public function getAllOrivilegesResources($privilege_key,$adapter)
		{
			
    		$sql_resources = "SELECT * FROM user_acl_package_resources WHERE resource_key = '$privilege_key'";
			$all_resources 	= $adapter->query($sql_resources, $adapter::QUERY_MODE_EXECUTE);
			$all_resources_array = $all_resources->toArray();
			return $all_resources_array[0];
		}

		public function getUserPackageId($user_id,$adapter)
		{
			$sql_pkg_id = "SELECT user_acl_package_id FROM user WHERE id = '$user_id'";
			$pkg_id 	= $adapter->query($sql_pkg_id, $adapter::QUERY_MODE_EXECUTE);
			$pkg_id_obj	= $pkg_id->current();
			return $pkg_id_obj->user_acl_package_id;
		}
	}