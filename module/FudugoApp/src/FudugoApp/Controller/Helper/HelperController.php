<?php
	/**
	* Develop by SURAJ WASNIK (suraj.wasnik0126@gmail.com)
	*/
	namespace FudugoApp\Controller\Helper;
	use GcFrontend\Controller\DbController as DbController;
	use Gc\Registry;
	Class HelperController
	{
		/*Formate the price */
		public function formate_price($price)
		{
			$config = Registry::get('Application')->getServiceManager()->get('ViewHelperManager')->get('Config');
			$currency = $config->get('site_currency');
			return $currency.number_format($price,2);
		}
	}