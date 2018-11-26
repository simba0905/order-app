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
	        'API_KEY'       => env('SHOPIFY_API_KEY'),
			'API_SECRET'    => env('SHOPIFY_API_SECRET'),
			'SHOP_DOMAIN'   => env('SHOPIFY_SHOP_DOMAIN'),
			'ACCESS_TOKEN'  => env('SHOPIFY_ACCESS_TOKEN')
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

    public function getOrderCounts(Request $request)
    {
    	$ip = $request->ip;
    	$country = $this->getCountry($ip);

    	if (!$country) return false;

    	// Gets a list of orders.
	    $results = $this->shopify->call([ 
	        'METHOD'    => 'GET', 
	        'URL'       => '/admin/orders.json'
	    ]);

	    // Order Count based on this country.
	    $order_count = 0;
	    foreach($results->orders as $result) {
	    	if ($country == $result->customer->default_address->country) {
	    		$order_count++;
	    	}
	    }

	    $data = [
	    	'country'		=> $country,
	    	'ip'	 		=> $ip,
	    	'order_count'	=> $order_count,
	    	'total_order'	=> count($results->orders)
	    ];

	    return $data;
    }
	
}
