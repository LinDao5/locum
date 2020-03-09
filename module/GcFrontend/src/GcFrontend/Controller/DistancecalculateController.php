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
		$url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBItia4ov4j9jIcIRIxaUjr_zatky5kC-Q&address=
		".urlencode($zip);
		$result_string = file_get_contents($url);
		$result = json_decode($result_string, true);
		$result1[]=$result['results'][0];
		$result2[]=$result1[0]['geometry'];
		$result3=$result2[0]['location'];
		return $result3;
	}
	public function getDistanceLat($zip=null,$addr=null){
		$zip_lng = $this->getLnt(str_replace(' ', '',$zip));
		if (!empty($zip_lng)) {
			return $lng1 = $zip_lng['lat'];
		}else{
			$addr_lng = $this->getLnt($addr);
			return $lng1 = $addr_lng['lat'];
		}
	}
	public function getDistanceLng($zip=null,$addr=null){
		$zip_lng = $this->getLnt($zip);
		if (!empty($zip_lng)) {
			return $lng1 = $zip_lng['lng'];
		}else{
			$addr_lng = $this->getLnt($addr);
			return $lng1 = $addr_lng['lng'];
		}
		
	}
	public function getDistanceLatLng($zip=null,$addr=null){
		$zip_lng_lat_record = $this->getLnt($zip);
		$zip_lat_lng = '';
		if (!empty($zip_lng_lat_record)) {
			$zip_lat_lng['lng'] = $zip_lng_lat_record['lng'];
			$zip_lat_lng['lat'] = $zip_lng_lat_record['lat'];
		}else{
			$addr_lng = $this->getLnt($addr);
			$zip_lat_lng['lng'] = $addr_lng['lng'];
			$zip_lat_lng['lat'] = $addr_lat_lng['lat'];
		}
		return $zip_lat_lng;
		
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
     //calculate distance  using town
     public function getDistanceTown($town_lat1,$town_lon1, $town_lat2,$town_lon2, $unit){
		$lat1 = $town_lat1;
		$lon1 = $town_lon1;
		$lat2 = $town_lat2;
		$lon2 = $town_lon2; 
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
      function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'mi', $decimals = 2) {
	// Calculate the distance in degrees
	$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
 
	// Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
	switch($unit) {
		case 'km':
			$distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
			break;
		case 'mi':
			$distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
			break;
		case 'nmi':
			$distance =  $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
	}
	return round($distance, $decimals);
    }
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

     $theta = $lon1 - $lon2;
     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
     $dist = acos($dist);
     $dist = rad2deg($dist);
     $miles = number_format($dist * 60 * 1.1515, 2);
     $unit = strtoupper($unit);

     if ($unit == "K") {
      return ($miles * 1.609344);
     } else if ($unit == "N") {
      return ($miles * 0.8684);
     } else {
        return $miles;
     }
   }
 }
