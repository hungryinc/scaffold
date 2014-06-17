<?php

class Header extends Eloquent implements HeaderRepository 
{

	public function showHeaders() 
	{
		$headers = $this->get();

		return $headers;
	}

}