<?php
/**
 * PHP Version >=5.3
 *
 * @category   Gc_Application
 * @package    GcFrontend
 * @subpackage Controller
 */

 namespace GcFrontend\Controller;
 use Gc\Mvc\Controller\Action;

 class DistancecalculateController extends Action
 {
   public function getLnt($zip){
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address=
		".urlencode($zip)."&sensor=false";
		$result_string = file_get_contents($url);
		$result = json_decode($result_string, true);
		$result1[]=$result['results'][0];
		$result2[]=$result1[0]['geometry'];
		$result3[]=$result2[0]['location'];
		return $result3[0];
	}
	public function getDistanceLat($zip,$addr){
		$zip_lng = $this->getLnt($zip);
		if (!empty($zip_lng)) {
			return $lng1 = $zip_lng['lat'];
		}else{
			$addr_lng = $this->getLnt($addr);
			return $lng1 = $addr_lng['lat'];
		}
	}
	public function getDistanceLng($zip,$addr){
		$zip_lng = $this->getLnt($zip);
		if (!empty($zip_lng)) {
			return $lng1 = $zip_lng['lng'];
		}else{
			$addr_lng = $this->getLnt($addr);
			return $lng1 = $addr_lng['lng'];
		}
		
	}	
   public function getDistance($zip1, $zip2, $unit){
		$first_lat = $this->getLnt($zip1);
		$next_lat = $this->getLnt($zip2);
		$lat1 = $first_lat['lat'];
		$lon1 = $first_lat['lng'];
		$lat2 = $next_lat['lat'];
		$lon2 = $next_lat['lng']; 
		$theta=$lon1-$lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
		cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
		cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		
		if ($unit == "K"){
		//return ($miles * 1.609344)."-".$unit;
		return ($miles * 1.609344);
		}
		else if ($unit =="N"){
		//return ($miles * 0.8684)."-".$unit;
		return ($miles * 0.8684);
		}
		else{
		//return $miles."-".$unit;
		return $miles;
		}
		
	}
 }
