<?php

namespace GcFrontend\Controller;
use GcFrontend\Controller\GoogleUrlApi as GoogleUrlApi  ;
use Gc\Mvc\Controller\Action;

    class ShorturlController extends Action
	{
    public function strurl($url)
        {        
        $googer1 = new GoogleUrlApi();        	
	$key = 'AIzaSyCKGM1uU3bDUX667VHM-MJG7JwxK2AI18U';
	$googer = $googer1->GoogleURLAPI($key);

	// Test: Shorten a URL
	$shortDWName = $googer1->shorten($url);
	return $shortDWName ; 

	// Test: Expand a URL
	//$longDWName = $googer1->expand($shortDWName);
	//$longDWName; 		  
        } 
    }
