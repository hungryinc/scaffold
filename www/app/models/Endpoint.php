<?php

class Endpoint extends Eloquent implements EndpointRepository 
{

	public function showEndpoints() 
	{
		$endpoints = $this->get();

		return $endpoints;
	}

	public function requestHeaders()
	{
		return $this->belongsToMany('Header', 'request_headers_endpoints');
	}

}