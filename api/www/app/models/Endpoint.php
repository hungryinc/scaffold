<?php

class Endpoint extends Eloquent implements EndpointRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	//GET Methods

	public function getEndpoints() 
	{
		$endpoints = $this->with('requestHeaders', 'responseHeaders')->get();

		if ($endpoints != null) {

			foreach ($endpoints as $endpoint) {
				$endpoint = $endpoint->formatted();
			}

		} else {
			throw new Exception("The model is having trouble accessing the MySQL database."); die();
		}

		return $endpoints->toArray();
	}

	public function getEndpointByID($id) 
	{
		$endpoint = $this->with('requestHeaders', 'responseHeaders')->find($id);

		if ($endpoint != null) {
			return $endpoint->formatted();
		} else {
			throw new Exception("Sorry, Endpoint ID Not Found"); die();
		}
	}

	//POST Methods

	public function createEndpoint($jsonobject)
	{
		$newEndpoint = new Endpoint();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newEndpoint->name = $name;
			} else {
				throw new Exception('Name field is missing'); die();
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$newEndpoint->uri = $uri;
			} else {
				throw new Exception('URI field is missing'); die();
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$newEndpoint->method = $method;
			} else {
				throw new Exception('Method field is missing'); die();
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$newEndpoint->response_code = $response_code;
			} else {
				throw new Exception('"Response Code" field is missing'); die();
			}

			if (isset($jsonobject['object']) && $id = $jsonobject['object']) {
				try {

					$object = $this->parseObject($id);

					if ($object != null) {
						$newEndpoint->object = $id;
					}

				} catch (Exception $e) {
					throw $e; die();
				}
			}

			$newEndpoint->save();

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				$this->syncHeaders($newEndpoint, $request_headers, 'request');
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				$this->syncHeaders($newEndpoint, $response_headers, 'response');	
			}

			return $newEndpoint->formatted();
			
		} else {
			throw new Exception('Invalid JSON'); die();
		}

	}

	//PUT Methods

	public function editEndpoint($id, $jsonobject) 
	{
		$endpoint = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$endpoint->name = $name;
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$endpoint->uri = $uri;
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$endpoint->method = $method;
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$endpoint->response_code = $response_code;
			}

			if (isset($jsonobject['object']) && $id = $jsonobject['object']) {
				try {

					$object = $this->parseObject($id);

					if ($object != null) {
						$endpoint->object = $id;
					}

				} catch (Exception $e) {
					throw $e; die();
				}
			}

			$endpoint->save();

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				$this->syncHeaders($endpoint, $request_headers, 'request');		
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				$this->syncHeaders($endpoint, $response_headers, 'response');
			}

			return $endpoint->formatted();

		} else {
			throw new Exception('Invalid JSON'); die();
		}
	}

	//DELETE Methods

	public function removeEndpoint($id)
	{
		$endpoint = $this->find($id);

		if ($endpoint != null) {

			//Remove all headers by passing empty array to sync()
			$this->syncHeaders($endpoint, array(), 'request');
			$this->syncHeaders($endpoint, array(), 'response');

			$endpoint->delete();

		} else {
			throw new Exception("Sorry, that endpoint ID does not exist"); die();
		}

		return $endpoint->formatted();
	}



	//Helper Methods

	/*
	This method is used to determine whether a given string matches up with an object in the objects table of the database
	@param string $str String to check against database in %number% format
	*/
	public function parseObject($str) {
		if(preg_match('/%(\d+)%/is', $str, $matches)){
			if (($object = Object::find($matches[1])) != null) {

				return $object;

			} else {
				throw new Exception("Object ID does not exist"); die();
			}
		} else {
			throw new Exception("'Object' is not in %number% format"); die();
		}
	}

	/*
	Method used to sync headers and endpoints
	@param Endpoint $endpoint The endpoint to sync request headers to
	@param Array $headers Array retrieved from JSON of headers to put in database
	@param String $type Type of headers to sync. Either 'request' 'response'
	*/
	public function syncHeaders($endpoint, $headers, $type)
	{
		$id_array = array();
		foreach ($headers as $key => $value) {
			$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

			if ($header == null) {
				$header = new Header();
				$header->key = $key;
				$header->value = $value;
				$header->save();
			}

			$id_array[] = $header->id;
		}	

		if ($type == 'request') {
			$endpoint->requestHeaders()->sync($id_array);
		} else if ($type == 'response') {
			$endpoint->responseHeaders()->sync($id_array);
		}
	}

	public function formatted()
	{
		$id = $this->object;

		if ($id != null) {

			try {
				$object = $this->parseObject($id);			
				$object->json = json_decode($object->json);				
				$this->object = $object;
			} catch (Exception $e) {
				throw $e; die();
			}						
		}

		return $this->toArray();
	}

	public function requestHeaders()
	{
		return $this->belongsToMany('Header', 'request_headers_endpoints', 'endpoint_id', 'header_id');
	}

	public function responseHeaders()
	{
		return $this->belongsToMany('Header', 'response_headers_endpoints', 'endpoint_id', 'header_id');
	}

}