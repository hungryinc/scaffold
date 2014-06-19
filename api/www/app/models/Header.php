<?php

class Header extends Eloquent implements HeaderRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	public function getHeaders() 
	{
		$headers = $this->get();

		return $headers;
	}

}