<?php

class ObjectDeleteException extends Exception {
	
	public $endpoints = array();

	public function __construct($message, $endpoints, $code = 0, Exception $previous = null) {



        $this->endpoints = $endpoints;

        foreach ($this->endpoints as $endpoint) {
        	$endpoint->json = json_decode($endpoint->json);
        }

        parent::__construct($message, $code, $previous);

    }

	public function getEndpoints() {
		return $this->endpoints;
	}
}