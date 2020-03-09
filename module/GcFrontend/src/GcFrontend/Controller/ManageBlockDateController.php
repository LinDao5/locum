<?php
	/**
	* To perform action on date managment of the freelancer
	*/
	namespace GcFrontend\Controller;
	use Gc\Mvc\Controller\Action;
	use Gc\view\Helper\Config as ConfigModule;
	use Gc\Core\Config as CoreConfig;
	use Gc\Registry;
	class ManageBlockDateController extends Action
	{
		
		public function updateBlockDate($uid,$blockDate,$adapter){
			//$blockDate = $blockDate.',';
			$sqlGetBlockDate="SELECT block_date FROM  user_block_date WHERE uid = '$uid'"; 
  			$resultsGetBlockDate = $adapter->query($sqlGetBlockDate, $adapter::QUERY_MODE_EXECUTE);
  			$blockDatesObject = $resultsGetBlockDate->current();
  			if (!empty($blockDatesObject)) {
  				$blockDates = (array)$blockDatesObject;
  				$arrayBlockDates = explode(',', $blockDates['block_date']);
  				if(!in_array($blockDate,$arrayBlockDates)){
	  				array_push($arrayBlockDates,$blockDate);
	  				//print_r($arrayBlockDates);
	  				$allBlockDates = implode(',', $arrayBlockDates);
	  				$sqlString_data="UPDATE user_block_date SET block_date = '$allBlockDates' WHERE uid = '$uid'"; 
	  				$results_get = $adapter->query($sqlString_data, $adapter::QUERY_MODE_EXECUTE);
	  			}
  			}else{
  				$sqlInsertBlockDate="INSERT INTO user_block_date (uid,block_date) VALUES ('$uid','$blockDate')"; 
  				$resultsInsert = $adapter->query($sqlInsertBlockDate, $adapter::QUERY_MODE_EXECUTE);
  			}
			
		}
	}