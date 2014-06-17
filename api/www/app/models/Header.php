<?php

class Header extends Eloquent implements HeaderRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	public function showHeaders() 
	{
		$headers = $this->get();

		return $headers;
	}

	public function requestEndpoints()
	{
		return $this->belongsToMany('Endpoint', 'request_headers_endpoints', 'header_id', 'endpoint_id');
	}

}