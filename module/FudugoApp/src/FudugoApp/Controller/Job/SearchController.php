<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Job; 
	use Gc\Registry;
	use GcFrontend\Controller\DbController as DbController;
	use FudugoApp\Controller\Helper\HelperController as HelperController;
	use Gc\User\Role\Model;
	use Gc\User\Professional\Model as Model2;
	use Gc\User\Package\Model as Model3;
	use GcFrontend\Controller\FunctionsController as FunctionController;
	use GcFrontend\Controller\JobmailController as MailController;
	use GcFrontend\Controller\DistancecalculateController as Distancecal;
	use FudugoApp\Controller\Job\JobController as JobController;
	use FudugoApp\Controller\Store\StoreController as StoreController;
	use GcFrontend\Controller\PackagePrivilegesController;

	Class SearchController
	{
		public function search_match_freelancer($data)
		{



			$dbConfig 			= new DbController();
			$adapter 			= $dbConfig->locumkitDbConfig();
			$distancecal 		= new Distancecal();
			$jobController 		= new JobController();
			$storeController 	= new StoreController();
			$functionController = new FunctionController();
			//this for Package Privileges resources
			$packagePrivilegesController = new PackagePrivilegesController();
			$user_id = $uid		= $data['user_id'];
			$profession_id 		= $data['cat_id'];
			$job_id 			= $data['job_id'];
			$configGet 			= Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');	
			$match_freelancer = array();

			if ($job_id) {
				$matching_answers 	= $this->get_matching_criteria($user_id,$adapter);
				$job_info 			= $jobController->get_job_info_by_id($job_id, $adapter);
				$store_info 		= $storeController->get_store_by_id($job_info['store_id'], $adapter);
				$private_user_list	= $this->get_private_users($user_id,$adapter);
				$freelancer_list 	= $this->get_freelancer_for_search($profession_id,$adapter);

				$emp_store_name		= $store_info['emp_store_name'];
				$emp_store_address	= $store_info['emp_store_address'];
				$emp_store_region	= $store_info['emp_store_region'];
				$emp_store_zip		= $store_info['emp_store_zip'];  
				
				$job_title			= $job_info['job_title'];
				$job_id				= $job_info['job_id'];
				$job_rate			= $job_info['job_rate'];
				$job_date			= $job_info['job_date'];
				$job_address		= $job_info['job_address'];
				$job_type			= $job_info['job_type'];
				$job_post_desc		= $job_info['job_post_desc'];
				$job_zip			= str_replace(' ', '',$job_info['job_zip']);
				$job_region			= $job_info['job_region'];
				$job_create_date	= $job_info['job_create_date'];
				$job_start_time		= $job_info['job_start_time'];

				$cat_id				= $profession_id;
				$user_role_id		= 3;

				$count_pulist		= count($private_user_list);

				$count_freelancer	= count($freelancer_list);
				$distance_match		= "";
				$rate_match			= "";
				$question_match		= "";
				$answer_match		= "";
				$block_date_match	= "";
				$get_arr_list		= array();
				$emp_ans_array		= $matching_answers;
				$emp_ans 			= count(array_filter($emp_ans_array));
			    $qusmatch 			= $configGet->get('qusMatch'); 
			    $qusmatch_per 		= $qusmatch/100;

			    	
			    $no_qus_need_to_match = $this->get_number_of_qus_match($cat_id,$adapter);
			    $no_ans_need_to_match = $this->get_number_of_ans_match($user_id,$adapter);
			    if ($no_ans_need_to_match < $no_qus_need_to_match) {
					$no_qus_need_to_match = $no_ans_need_to_match ;
				}
				$emp_ans_per		=	round($qusmatch_per*$no_qus_need_to_match);

				$allow_job_count		= "";
				$notification_count		= "";	
				$result_usedList_count	= "";
				$cancellation_rate		= 0;

				$match_freelancer_list 	= array();

				$start 	= 0; 
				$limit 	= 10;
				$i 		= 0;
				$j 		= 0;
				$k 		= 0;

				foreach($freelancer_list as $resultset){
					$search_userid = $resultset['uid'];

					/* Check user privileges */
					$is_user_pkg_allow_job_invitation = $packagePrivilegesController->getCurrentPackagePrivilegesResources('job_invitation',$search_userid,$adapter);


					// get user membership expire date
					$check_membership = $functionController->checkUserMembershipPlan($search_userid,$adapter);
					$is_expired = 0;
					if ($check_membership == 0) {
						$is_expired = 1;
					}	

					if($is_expired == 0 && $is_user_pkg_allow_job_invitation == 1){

						if ($resultset['max_distance'] == 'Over 50') {
							$max_distance = 6371;
						}else{
							$max_distance = $resultset['max_distance'];
						}
						$search_pack_id 	= $resultset['user_acl_package_id'];
						$search_user_zip 	= $resultset['zip'];
						$search_user_region = $resultset['city'];
						$search_fname 		= $resultset['firstname'];
						$search_lname 		= $resultset['lastname'];
						$search_email 		= $resultset['email'];
						$search_category_id = $resultset['user_acl_profession_id']; 
						$gender 			= ucfirst($resultset['gender']);
						$last_worked 		= "N/A";
						$year_qualified 	= "N/A";
						$feedback_avg 		= "0";				
						$used_list 			= "";

						
						$result_fedbk_data	= $this->get_feedback_by_id($search_userid, $adapter);
						$result_fedbk_count = $result_fedbk_data->count(); 

						if($result_fedbk_count>0){
							$sum_total		= $result_fedbk_data['sum_total'];
							$total_count	= $result_fedbk_data['total_count'];
							$feedback_avgold= $total_count > 0 ? ($sum_total/($total_count*5))*100 : 0;
							$feedback_avg 	= $functionController->getOverallRating($functionController->getFeedbackByUserId($adapter, $search_userid, 3)).'%';
						}

						//Cancellation Rate
						$result_cnrate_data = $this->get_cancellation_rate_by_id($search_userid, $adapter);
						$result_cnrate_count = $result_cnrate_data->count(); 
						if($result_cnrate_count > 0){
							$sum_total 			= $result_cnrate_data['sum_total'];
							$total_count 		= $result_cnrate_data['total_count'];
							$sum_accept 		= $result_cnrate_data['sum_accept'];
							$cancel_rate 		= ($sum_total-$sum_accept);
							$cancellation_rate 	= $functionController->getFreCancellationRate($search_userid,$adapter);
						}


						// last worked
						$sql_lastWorked = "SELECT ja.f_id,jp.e_id,u.firstname FROM job_post jp,job_action ja,user u where ja.f_id='".$search_userid."' and ja.action=4 and ja.job_id=jp.job_id and jp.e_id=u.id order by ja.a_id DESC";
					    $results_lastWorked = $adapter->query($sql_lastWorked, $adapter::QUERY_MODE_EXECUTE);
						$result_lastWorked_data = $results_lastWorked->current();
						$result_lastWorked_count = $results_lastWorked->count(); 
						if($result_lastWorked_count>0){
							$last_worked=$result_lastWorked_data['firstname'];
						}
						
						// used list
						$sql_usedList="SELECT ja.f_id,jp.e_id FROM job_post jp,job_action ja where ja.f_id='".$search_userid."' and ja.action=4 and jp.e_id='".$uid."'";
					    $results_usedList = $adapter->query($sql_usedList, $adapter::QUERY_MODE_EXECUTE);
						$result_usedList_count = $results_usedList->count();
						$result_usedList_data = $results_usedList->current();  
						
						/*******************Freelancer private job date************************/
						$pvj_date=str_replace('/','-',$job_date);
						$pvj_date_new=date('Y-m-d',strtotime($pvj_date));
						$sql_pvj="select priv_job_start_date from freelancer_private_job where priv_job_start_date like '%$pvj_date_new%' and f_id='".$search_userid."' ";
						$get_pvj = $adapter->query($sql_pvj, $adapter::QUERY_MODE_EXECUTE);
						$count_pvj = $get_pvj->count(); // count==0 means freelancer is available
						
						// get freelancer qualified year from answer table
						$sql_qu_year="select type_value as qualified from user_answer where question_id IN (select id from user_question where fquestion like '%qualified%' and cat_id='$cat_id') and user_id='$search_userid'";
						$get_qu_year = $adapter->query($sql_qu_year, $adapter::QUERY_MODE_EXECUTE);
						$result_qu_year=$get_qu_year->current();
						$count_qu_year=$get_qu_year->count();
						if($count_qu_year>0){ $year_qualified=$result_qu_year['qualified']; }
						
						/* Get min rate of freelancer at job date */
						$sqlGetRecord="SELECT available_date FROM user_work_calender  WHERE uid = '$search_userid'"; 
					  	$getRecord = $adapter->query($sqlGetRecord, $adapter::QUERY_MODE_EXECUTE);
					  	$allRecord = $getRecord->toArray();
					  	$isSetupRate = 0;
					  	if (!empty($recordArray)) {
					  		$recordArray = unserialize($allRecord[0]['available_date']);
					  		$infoDate = date("Y-m-d", strtotime(str_replace('/','-',$job_date)));
					  		foreach ($recordArray as $key => $value) {
						  		if (date("Y-m-d", strtotime($value['date'])) == $infoDate) {
						  			$search_rate = $value['min_rate'];
						  			$isSetupRate = 1;
						  			break;
						  		}else{
						  			$isSetupRate = 0;
						  		}
						  		//print_r($value);
						  	}
					  	}
					  	/*echo "<br/>";
					  	echo '=='.$isSetupRate;
					  	echo "<br/>";*/
						if ($isSetupRate == 0) {						
							$currentDay = date('l');
							$fre_rate = @unserialize( $resultset['minimum_rate'] );
							$search_rate = $fre_rate[$currentDay];
						}
						//echo $search_rate;
						// answer match for user
						foreach ($emp_ans_array as $key => $value) {
							//$condition_arr=array("1"=>"Greater than","2"=>"Greater than OR equal to","3"=>"Less than","4"=>"Less than OR equal","5"=>"Equal to");
							$condition_arr=array("1"=>">","2"=>">=","3"=>"<","4"=>"<=","5"=>"=");
							$this_qus_match = 0;
							// get question range option for search criteria
							$sqlString_qu2="select * from user_question where id='".$key."' and range_type_condition!=0 ";
							$results_qu2 = $adapter->query($sqlString_qu2, $adapter::QUERY_MODE_EXECUTE);
							$results_qu22 = $results_qu2->current();
							$result_qu2_count = $results_qu2->count();
							if($result_qu2_count>0 && $value!=''){
								$condition=$results_qu22['range_type_condition'];
								$qu_value=unserialize($results_qu22['type_value']);
								$replace_str=str_replace('minutes','',$value);
								//$con_ser = $condition_arr[$condition].$replace_str;implode(',',$qu_value)
								//$con_ser = $value;
								$new_val=str_replace(",","','",$value);
								$con_ser = "'".$new_val."'";
								//$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and type_value LIKE '%$value%' ";
								$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and type_value IN($con_ser) ";
							}else{
								if($value!='' && $key!=''){
								    $myArray = explode(',', $value); //print_r($myArray);
									$myarr_count=count($myArray);
									if($myarr_count>1){
									   $new_val=str_replace(",","','",$value);
								       $con_ser = "'".$new_val."'";
									   $inarr=in_array('All', $myArray);
									   if($inarr==1){
									       $sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and type_value !='' ";     
									    }else{
										    foreach ($myArray as $ans_value) {
									   			//echo "====$ans_value<br/>";
									   			$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and  FIND_IN_SET('".$ans_value."',type_value) "; 
									   			$multiselect_match = $adapter->query($sqlString_ans2, $adapter::QUERY_MODE_EXECUTE)->toArray();
									   			if (!empty($multiselect_match)){
									   				/*echo "<pre>"; 
									   				print_r($multiselect_match);
									   				echo "</pre>"; */
									   				$this_qus_match=1;
									   				//break;
									   			}else{
									   				$this_qus_match=0;
									   				break;
									   			}
									   		}
									    }
									}
									if($myarr_count==0 || $myarr_count==1){
									   //$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and type_value LIKE '%$value%' ";            
									   if($value=="All"){
										   $sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and type_value !='' ";
									   }else{
										   $sqlchecktype6="select * from user_question where id=".$key;
											$checktype6 = $adapter->query($sqlchecktype6, $adapter::QUERY_MODE_EXECUTE);									
											$result_checktype6 = $checktype6->current();
											if($result_checktype6['type_key'] == 6 ){
												if ($value == 'Yes') {
													$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and LOCATE( '$value', type_value )"; 
												}elseif($value == 'No'){
													$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' "; 
												}										
											}else{
												$sqlString_ans2="select * from user_answer where question_id='".$key."' and user_id='".$search_userid."' and LOCATE( '$value', type_value )"; 
											}
									   }
									  
									}      
								
								}    
							}
							if($value!=''){
								$results_ans2 = $adapter->query($sqlString_ans2, $adapter::QUERY_MODE_EXECUTE); //die();
								$result_ans2_count = $results_ans2->count(); //echo $result_ans2_count;print_r($results_ans2);
								$result_ans2_data = $results_ans2->current();
								if($result_ans2_count>0){								
								   $get_arr_list[$search_userid][]=$result_ans2_count;
								}elseif($this_qus_match == 1){
									$get_arr_list[$search_userid][]=1;
								}
							}
						}

						$ans2_count = count( @$get_arr_list[$search_userid] ); 
						
						
						// get user job done and there count
						$count_accept=$functionController->jobAcceptCount($search_userid,$adapter);

						// get block user from table
						$count_date_bkl=$functionController->blockByEmp($search_userid,$uid,$adapter);					
					
						
						// get block dates
						$count_block_date=$functionController->userBlockDate($search_userid,$job_date,$adapter);

						// check booked dates
						$count_book_date=$functionController->getBookDate($search_userid,$job_date,$adapter);

						//get lat and lon of town for distance calculate
						$town_lat1 = '';
						$town_lon1 = '';
						$town_lat2 = '';
						$town_lon2 = '';
						$distance22 = '';
						$word_region_count= str_word_count($job_region);
						$user_region_count= str_word_count($search_user_region);
						if($word_region_count==1){
							$job_region_new=ucfirst($job_region);
							$sql_jtown = "SELECT * from site_town_table WHERE Town like '%$job_region_new%' AND Lat != '' AND Lon !='' "; 
						}else{
							$job_region_new=ucfirst($job_region);
							$sql_jtown = "SELECT * from site_town_table WHERE Town like '%$job_region_new%' AND Lat != '' AND Lon !='' ";
						}
						if($user_region_count==1){
							$user_region_new=ucfirst($search_user_region);
							$sql_utown = "SELECT * from site_town_table WHERE Town ='$user_region_new' AND Lat != '' AND Lon !='' ";
						}else{
							$user_region_new=ucfirst($search_user_region);
							$sql_utown = "SELECT * from site_town_table WHERE Town like '%$user_region_new%' AND Lat != '' AND Lon !='' ";
						}
						   
		                $res_jtown = $adapter->query($sql_jtown, $adapter::QUERY_MODE_EXECUTE);
						$res_get11 = $res_jtown->current(); //print_r($res_jtown);
						$town_lat1 = $res_get11['Lat']; $tw_id1 = $res_get11['tw_id']; $town_lon1= $res_get11['Lon'];
		                $res_utown = $adapter->query($sql_utown, $adapter::QUERY_MODE_EXECUTE);
						$res_get22 = $res_utown->current(); 
						$town_lat2 = $res_get22['Lat']; $town_lon2= $res_get22['Lon'];
						
						//search calculate criteria
						if($town_lat1!="" && $town_lat2!=""){
							//$distance2222 = round($distancecal->getDistanceTown($town_lat1,$town_lon1, $town_lat2,$town_lon2,'N'));
							$new_diff = $distancecal->distance($town_lat1,$town_lon1, $town_lat2,$town_lon2,"M");							
							//$dis_diff=$distancecal->distanceCalculation($town_lat1,$town_lon1, $town_lat2,$town_lon2,'mi',2);
							//if($new_diff == ""){$distance22 = $max_distance;}
							if($new_diff == 0){$distance22 = "zero";}
							if($new_diff != 0){ 
							   $distance22=round($new_diff);
							}
							if($town_lat1=="" && $new_diff==""){
								$distance22='Undefined';
							}
						}else{
							$lat_long_user = $distancecal->getLnt($user_region_new);
							$lat_long_job  = $distancecal->getLnt($job_region);
							$town_lat1 = $lat_long_job['lat']; 
							$town_lon1 = $lat_long_job['lng']; 
							$town_lat2 = $lat_long_user['lat']; 
							$town_lon2 = $lat_long_user['lng'];
							$new_diff  = $distancecal->distance($town_lat1,$town_lon1, $town_lat2,$town_lon2,"M");
							if($new_diff == 0){$distance22 = "zero";}
							if($new_diff != 0){ 
							   $distance22 = round($new_diff);
							}
							if($town_lat1 == "" && $new_diff == "" ){
								$distance22 = 'Undefined';
							}
						}
						
						//get user categoryid
						$professionalTable = new Model2();
						$row2              = $professionalTable->fetchRow($professionalTable->select(array('id' => (int) $search_category_id)));
						$category_name=$row2['name'];

						//get min rate of all freelancer for job date

						//check all parameter for search
						if(isset($search_userid))
						{					
							/*echo 'DIFFENENCE--'.$distance22.'-REQUIRED-'.$max_distance.'<br>';
							echo "=================================";*/

							if($distance22 != ""){ // check for distance
								if($distance22 == $new_diff || $distance22 == "zero"){  
									$distance_match=1;
								}								
								if($distance22 > 0){
									if($distance22 > $max_distance){
										$distance_match=0;
									}
									if($distance22 < $max_distance && $distance22!=0){
										$distance_match=1;
									}
									if($distance22 == $max_distance){
										$distance_match=1;
									}
									if($max_distance=="Over 50"){
										$distance_match=1;
									}
								}
							}
							//echo $job_rate.'Job rate'.round($search_rate);
							//Check for rates 
							if ($search_rate <= $job_rate) {
								$rate_match = 1;
							}else{
								$rate_match = 0;
							}
							//echo $ans2_count.'-'.$emp_ans_per;
							//echo "$job_rate == $search_rate === $rate_match";	
							//pagination div
							$div_show=$i+$k;
							if($div_show>=$limit){
								$visible='style="display:none;"';
							}
							
							if($div_show % $limit == 0 ){
								if($i<=$limit){
									$div_show_class='show_list'.$div_show;
								}else{
									$div_show_class='show_list'.$div_show;
								}
							}

							//check user is eligible or not to get job invitation start
							$is_user_pkg_allow_job_invitation = $packagePrivilegesController->getCurrentPackagePrivilegesResources('job_invitation',$search_userid,$adapter);

							//check user is eligible or not to get job invitation end

							$user_list_check[$search_userid] = "($distance_match==1) && ($ans2_count>=$emp_ans_per) && ($count_block_date==0) && ($count_book_date==0) && ($count_date_bkl==0)  && ($rate_match == 1) && ($count_pvj==0)";
							

							if(($distance_match==1) && ($ans2_count>=$emp_ans_per) && ($count_block_date==0) && ($count_book_date==0) && ($count_date_bkl==0)  && ($rate_match == 1) && ($count_pvj==0) ){
				  
							 	$match_freelancer_list[] = array(
							 			'user_id' => $search_userid,
							 			'cancellation_rate' => $cancellation_rate,
							 			'feedback_avg' => round($feedback_avg, 2),
							 			'cet' => $functionController->getCetRatesByUid($search_userid,$adapter),
							 			'cetriz' => $is_user_pkg_allow_add_private
							 		); 
							   $i++;
							} // 
						}
					} // End package expire & privileges
				} // End for

				//Test why not users come through hahaha just uncomment below code dear...
				/*print_r($user_list_check);
				die();*/

				$match_freelancer = array(
						'website_locum' => $match_freelancer_list,
						'private_locum' => $private_user_list
					);		
							
			}

			return json_encode($match_freelancer);
		}

		/* Get matching answer of current employer */
		public function get_matching_criteria($user_id,$adapter)
		{
			$questions = $answers = '';
			$sql_user_ans	= "SELECT * FROM user_answer WHERE user_id='$user_id'";  
			$user_ans_obj 	= $adapter->query($sql_user_ans, $adapter::QUERY_MODE_EXECUTE);
			$user_ans 		= $user_ans_obj->toArray(); 
			foreach($user_ans as $res_qu){				
				$answers[$res_qu['question_id']] = $res_qu['type_value'];
			}			
			return $answers;
		}

		/* Get Maching Freelancer */
		public function get_freelancer_for_search($cat_id,$adapter)
		{		
			$sql_freelancer = "SELECT u.* ,ux.* FROM user u , user_extra_info ux  WHERE u.user_acl_role_id != '3' AND u.user_acl_role_id !=1 AND  u.user_acl_profession_id = '$cat_id' AND u.id = ux.uid AND u.active = 1 AND ux.max_distance != '' AND ux.minimum_rate != '' ORDER BY u.firstname ASC";
			$freelancer_obj = $adapter->query($sql_freelancer, $adapter::QUERY_MODE_EXECUTE); 
			$freelancer_list = $freelancer_obj->toArray();
			return $freelancer_list;
		}

		/* Get Private User List */
		public function get_private_users($user_id,$adapter)
		{
			$sql_pulist = "SELECT * FROM private_user where emp_id='$user_id' AND status != '2'";
			$rows_pulist = $adapter->query($sql_pulist, $adapter::QUERY_MODE_EXECUTE);
			$pulist = $rows_pulist->toArray();
			return $pulist;
		}

		/* Get number of qus need to match */
		public function get_number_of_qus_match($cat_id,$adapter)
		{
			$no_qus_need_to_match = 0;
			$sqlQueMatch="SELECT equestion FROM user_question WHERE cat_id ='$cat_id' AND fquestion != '' AND equestion != ''";
			$queMatchData = $adapter->query($sqlQueMatch, $adapter::QUERY_MODE_EXECUTE);
			if (!empty($queMatchData)) {
				$no_qus_need_to_match = $queMatchData->count();
			}
			return $no_qus_need_to_match ;
		}

		/* Get no of ans need to match */
		public function get_number_of_ans_match($uid,$adapter)
		{			
			$no_ans_need_to_match = 0;
			$sqlAnsMatch="SELECT * FROM user_answer WHERE user_id ='$uid' AND type_value != ''";
			$ansMatchData = $adapter->query($sqlAnsMatch, $adapter::QUERY_MODE_EXECUTE);
			if (!empty($ansMatchData)) {
				$no_ans_need_to_match = $ansMatchData->count();
			}
			return $no_ans_need_to_match;
		}

		/* Get Freelancer Feedback by Id*/
		public function get_feedback_by_id($user_id, $adapter)
		{
			$sql_fedbk = "SELECT SUM(rating) as sum_total,COUNT(*)as total_count FROM job_feedback where fre_id='".$user_id."' and status=1  AND  created_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) ";
		    $results_fedbk = $adapter->query($sql_fedbk, $adapter::QUERY_MODE_EXECUTE);
			$result_fedbk_data = $results_fedbk->current();
			return $result_fedbk_data;
		}

		/* Get Freelancer Cancellation Rate */
		public function get_cancellation_rate_by_id($user_id, $adapter)
		{			
			$sql_cancelrate="SELECT SUM(f_notification) as sum_total,(SELECT f_notification FROM job_action WHERE f_notification =3) as sum_accept,COUNT(*)as total_count FROM job_action where f_id='".$user_id."' and action!='' ";			
		    $results_cnrate = $adapter->query($sql_cancelrate, $adapter::QUERY_MODE_EXECUTE);
			$result_cnrate_data = $results_cnrate->current();
			return $result_cnrate_data;
		}

		/* Get Freelancer Last Work info */
		public function get_last_work_info($user_id, $adapter)
		{
			# code...
		}
	}