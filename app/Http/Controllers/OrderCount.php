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

    	$total_order = $this->getTotalOrderCounts();
    	$limit = 250;
    	$page = 1;
    	
	    // Order Count based on this country.
	    $order_count = 0;

	    if ($total_order > $limit) {
	    	$page_count = intval($total_order / $limit) + 1;

	    	for($i = 1; $i <= $page_count; $i++) {
	    		$url  = '/admin/orders.json?fields=customer&fulfillment_status=shipped&limit=' . $limit . '&page=' . $i;
	    		$results = $this->shopify->call([ 
			        'METHOD'    => 'GET', 
			        'URL'       => $url
			    ]);

			    foreach($results->orders as $result) {
			    	if ($country == $result->customer->default_address->country) {
			    		$order_count++;
			    	}
			    }
	    	}

	    } else {
	    	$url  = '/admin/orders.json?fields=customer&fulfillment_status=shipped&limit=' . $limit . '&page=' . $page;

	    	// Gets a list of orders.
		    $results = $this->shopify->call([ 
		        'METHOD'    => 'GET', 
		        'URL'       => $url
		    ]);

		    foreach($results->orders as $result) {
		    	if ($country == $result->customer->default_address->country) {
		    		$order_count++;
		    	}
		    }
	    }

	    $data = [
	    	'country'		=> $country,
	    	'ip'	 		=> $ip,
	    	'order_count'	=> $order_count,
	    	'total_order'	=> $total_order
	    ];

	    return $data;
    }
	
}
