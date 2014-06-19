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
			throw new Exception("There was a problem retrieving the endpoints from the database."); die();
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

			if (isset($jsonobject['project_id']) && $project_id = $jsonobject['project_id']) {
				$newEndpoint->project_id = $project_id;

				if (!Project::find($project_id)) {
					throw new Exception("That project ID does not exist in the database"); die();
				}

			} else {
				throw new Exception('"project_id" field is missing'); die();
			}

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

			if (isset($jsonobject['json']) && $json = $jsonobject['json']) {

				if (json_encode($json)) {
				
					foreach ($json as $key => $value) {
						if (is_string($value)) {
							if (preg_match('/<%(\d+)%>/is', $value, $matches)) {
								if ( ! Object::find($matches[1])) {
									throw new Exception("Object ID '" . $matches[1] . "' does not exist");
								}
							}
						}
					}

					$newEndpoint->json = json_encode($json);
				
				} else {
					throw new Exception("The field 'json' is not valid"); die();
				}

			}


			$newEndpoint->save();

			if (isset($jsonobject['project_id']) && $project = $jsonobject['project_id']) {
				$this->syncData($newEndpoint, $project, 'project');		
			}

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				$this->syncData($newEndpoint, $request_headers, 'request');
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				$this->syncData($newEndpoint, $response_headers, 'response');	
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

			if (isset($jsonobject['project_id']) && $project_id = $jsonobject['project_id']) {
				$endpoint->project_id = $project_id;

				if (!Project::find($project_id)) {
					throw new Exception("That project ID does not exist in the database"); die();
				}
			}

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

			if (isset($jsonobject['json']) && $json = $jsonobject['json']) {

				if (json_encode($json)) {
					
					foreach ($json as $key => $value) {
						if (is_string($value)) {
							if (preg_match('/<%(\d+)%>/is', $value, $matches)) {
								if ( ! Object::find($matches[1])) {
									throw new Exception("Object ID does not exist");
								}
							}
						}
					}
					
					$endpoint->json = json_encode($json);
				
				} else {
					throw new Exception("The field 'json' is not valid"); die();
				}

			} 

			$endpoint->save();

			if (isset($jsonobject['project_id']) && $project = $jsonobject['project_id']) {
				$this->syncData($endpoint, $project, 'project');		
			}

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				$this->syncData($endpoint, $request_headers, 'request');		
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				$this->syncData($endpoint, $response_headers, 'response');
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
			$this->syncData($endpoint, array(), 'request');
			$this->syncData($endpoint, array(), 'response');
			$this->syncData($endpoint, array(), 'project');

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
		if (preg_match('/<%(\d+)%>/is', $str, $matches)){
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
	Method used to sync data between different mysql tables
	@param Endpoint $endpoint The endpoint to sync request headers to
	@param Array $headers Array retrieved from JSON of headers to put in database
	@param String $type Type of data to sync. Either 'request' 'response' or 'project'
	*/
	public function syncData($endpoint, $data, $type)
	{
		$id_array = array();
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

				if ($header == null) {
					$header = new Header();
					$header->key = $key;
					$header->value = $value;
					$header->save();
				}

				$id_array[] = $header->id;
			}	
		} else {
			$id_array[] = $data;
		}

		if ($type == 'request') {
			$endpoint->requestHeaders()->sync($id_array);
		} else if ($type == 'response') {
			$endpoint->responseHeaders()->sync($id_array);
		} else if ($type == 'project') {
			$endpoint->project();
		}
	}

	public function formatted()
	{

		$this->json = json_decode($this->json);

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

	public function project()
	{
		return $this->belongsTo('Project');
	}

}