<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class OrderCount extends Controller
{	
	
	public $shopify;

	public function __construct()
    {
        $this->shopify = \App::make('ShopifyAPI', [ 
	        'API_KEY'       => '2a4be46c3f02a1e32bbef4a3e09d2db3', 
			'API_SECRET'    => '069ea560a3ca8d5f2728e22c5562ad26', 
			'SHOP_DOMAIN'   => 'plug-chemx.myshopify.com', 
			'ACCESS_TOKEN'  => 'e67e64de9397adf2942cd4fd363c9e76' 
	    ]);
    }

    public function getCountry($ip)
    {	
    	$query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));

		if($query && $query['status'] == 'success') {
		  return $query['country'];
		} else {
		  return false;
		}
    }

    public function getTotalOrderCounts()
    {
    	// Gets total order count.
	    $result = $this->shopify->call([ 
	        'METHOD'    => 'GET', 
	        'URL'       => '/admin/orders/count.json' 
	    ]);
	    return $result->count;
    }

    public function getOrderCounts($ip ='141.105.64.66')
    {
    	$country = $this->getCountry($ip);

    	$country = 'United States';
    	
    	// Gets a list of orders.
	    $results = $this->shopify->call([ 
	        'METHOD'    => 'GET', 
	        'URL'       => '/admin/orders.json'
	    ]);

	    $order = $this->shopify->call([
	    	'METHOD'    => 'GET', 
	        'URL'       => '/admin/orders/717764624426.json'
	    ]);

	    // Order Count based on this country.
	    $count = 0;
	    foreach($results->orders as $result) {
	    	if ($country == $result->customer->default_address->country) {
	    		$count++;
	    	}
	    }

	    return $count;
    }
	
}
