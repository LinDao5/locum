<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\User;
	use GcFrontend\Controller\DbController as DbController;
	use GcFrontend\Controller\FunctionsController;
 
	Class FeedbackController
	{
		public function feedback_summary_data($user_data){
			$dbController = new DbController();
			$adapter = $dbController->locumkitDbConfig();
			$functionsController = new FunctionsController();
			$uid = $user_data['user_id'];
			$user_role = $user_data['user_role'];
			$feedbackSummaryData = '';
			$feedbackSummary = '';
			if ($user_role == 2) {
				$currentFeedbackData =  $functionsController->getFeedbackByUserId($adapter, $uid, 3);
			}else{
				$currentFeedbackData =  $functionsController->getFeedbackByUserId($adapter, $uid, 2);
			}

			if (empty($currentFeedbackData)) {
				$currentFeedbackData = ''; 
			}else{
				$qusdata = $qus = $quscount =  array();
                foreach($currentFeedbackData as $currentFeedback){
                    foreach(unserialize($currentFeedback['feedback']) as $feedback){
                        $queid = $feedback['qusId'];
                        $qusdata[$queid] = isset($qusdata[$queid]) ? $qusdata[$queid] + $feedback['qusRate'] : $feedback['qusRate'];
                        $quscount[$queid] = isset($quscount[$queid]) ? $quscount[$queid] + 1 : 1;
                        $qus[$queid]= $feedback['qus'];
                    }  
                } 
                $i = 1 ;
        	    $c = count($qusdata);
        	    if($c >=4){  $j = 4 ;}
                elseif($c == 2){  $j = 1 ;  }
                else{ $j = $c ;   }
                $qus_ave_rate = array();
                foreach($qusdata as $key => $qusdata){
                	$qus_ave[] =  "Q".$i;
                	$qus_ave_rate[] =  round(($qusdata/($quscount[$key]*5))*100,2);
                	$qus_ave_rate_background[] =  'rgba(8, 169, 226, 0.9)';
                	$qus_ave_rate_border[] = 'rgb(8, 169, 226, 0.9)' ;
                	$feedbackSummary[] = array(
                			'qus' => "Q".$i." : ".$qus[$key],
                			'qusRate' => round(($qusdata/($quscount[$key]*5))*100,2),
                			'dataX' => "Q".$i,
                			'dataY' => round(($qusdata/($quscount[$key]*5))*100,2),	
                			'j' => $j
                		);
                	$i++;
                }
                $feedbackSummaryData['feedback'] = $feedbackSummary;
                $feedbackSummaryData['graph_chart']['qus_label'] = $qus_ave;
                $feedbackSummaryData['graph_chart']['qus_ave_rate'] = $qus_ave_rate;
                $feedbackSummaryData['graph_chart']['qus_ave_rate_background'] = $qus_ave_rate_background;
                $feedbackSummaryData['graph_chart']['qus_ave_rate_border'] = $qus_ave_rate_border;
			}
			return json_encode($feedbackSummaryData);
		}

		
	}