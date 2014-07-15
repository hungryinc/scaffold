<?php

class Endpoint extends Eloquent implements EndpointRepository 
{

	protected $hidden = array('pivot');

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
				throw new Exception("'project_id' field is missing"); die();
			}

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newEndpoint->name = $name;

				if ($this->checkName($name)) {
					throw new Exception('Sorry, that name is being used by another endpoint!'); die();
				}

			} else {
				throw new Exception('Name field is missing'); die();
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				if ($method = $this->isValidHTTPMethod($method)) {
					$newEndpoint->method = $method;
				} else {
					throw new Exception('That is not a valid HTTP method'); die();
				}
			} else {
				throw new Exception('Method field is missing'); die();
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$newEndpoint->uri = $uri;

				$this->checkURI($uri);

			} else {
				throw new Exception('URI field is missing'); die();
			}

			if (isset($jsonobject['input']) && $input = $jsonobject['input']) {

				if ($this->isValidInputMethod($newEndpoint->method)) {
					if (json_encode($input)) {

						$this->inputCheck($input);

						$newEndpoint->input = serialize($input);

					} else {
						throw new Exception("The field 'input' is not valid"); die();
					}
				} else {
					throw new Exception("The method you chose does not accept input");
				}
				

			} else {
				if ($this->isValidInputMethod($newEndpoint->method)) {
					throw new Exception("Input field is required for PUT or POST methods"); die();
				}
			}

			

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$newEndpoint->response_code = $response_code;
			} else {
				throw new Exception('"Response Code" field is missing'); die();
			}

			if (isset($jsonobject['json']) && $json = $jsonobject['json']) {

				if (json_encode($json)) {

					foreach ($json as $key => $value) {
						$this->objectCheck($value);
						
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

				if ($this->checkName($name)) {
					throw new Exception('Sorry, that name is being used by another endpoint!'); die();
				}
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				if ($method = $this->isValidHTTPMethod($method)) {
					$endpoint->method = $method;
				} else {
					throw new Exception('That is not a valid HTTP method'); die();
				}
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$endpoint->uri = $uri;
				$this->checkURI($uri);
			} 

			if (isset($jsonobject['input']) && $input = $jsonobject['input']) {

				if ($this->isValidInputMethod($endpoint->method)) {
					if (json_encode($input)) {

						$this->inputCheck($value);

						$endpoint->input = serialize($input);

					} else {
						throw new Exception("The field 'input' is not valid"); die();
					}
				} else {
					throw new Exception("The method you chose does not support input");
				}
				

			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$endpoint->response_code = $response_code;
			}

			if (isset($jsonobject['json']) && $json = $jsonobject['json']) {

				if (json_encode($json)) {
					
					foreach ($json as $key => $value) {
						$this->objectCheck($value);
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
		$exposeHeaderString = "";

		if (is_array($data)) {
			foreach ($data as $key => $value) {

				$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

				if ($header == null) {
					$header = new Header();
					$header->key = $key;
					$header->value = $value;
					$header->save();
				}

				$exposeHeaderString = $exposeHeaderString . $header->key . ", ";

				$id_array[] = $header->id;
			}	
		} else {
			$id_array[] = $data;
		}

		if ($type == 'request') {
			$endpoint->requestHeaders()->sync($id_array);
		} else if ($type == 'response') {

			$exposeHeader = Header::where('key', '=', 'Access-Control-Expose-Headers')->where('value', '=', $exposeHeaderString)->first();

			if ($exposeHeader == null) {
				$exposeHeader = new Header();
				$exposeHeader->key = 'Access-Control-Expose-Headers';
				$exposeHeader->value = $exposeHeaderString;
				$exposeHeader->save();
			}

			$id_array[] = $exposeHeader->id;
			

			$endpoint->responseHeaders()->sync($id_array);
		} else if ($type == 'project') {
			$endpoint->project();
		}
	}

	public function objectCheck($value)
	{
		if (is_string($value)) {
			if (preg_match('/<%(\d+)%>/is', $value, $matches)) {
				if ( ! Object::find($matches[1])) {
					throw new Exception("Object ID '" . $matches[1] . "' does not exist");
				}
			}
		}
	}

	//DUPLICATE CHECK METHODS

	public function checkName($name)
	{
		$endpoints = $this->getEndpoints();
		foreach ($endpoints as $endpoint) {
			if ($endpoint['name'] == $name) {
				return true;
			}
		}
		return false;
	}

	public function checkURI($uri)
	{

		if (preg_match('#[0-9]#',$uri)){ 
			throw new Exception("URI can't have numbers!!!");
		}

		if (preg_match_all("/\/:([a-zA-Z]+){([a-zA-Z]+)}/is", $uri, $matches)) {

			foreach ($matches[2] as $type) {
				if (!(in_array(strtoupper($type), ['STRING', 'NUMBER']))) {
					throw new Exception("The URI wildcard dataType has to be either {string} or {number}"); die();
				}
			}
		}

		$endpoints = $this->getEndpoints();
		foreach ($endpoints as $endpoint) {
			$endpointURI = $endpoint['uri'];

			if ($endpointURI == $uri) {
				throw new Exception("That URI is being used by another endpoint!"); die();
			}

			if (preg_match_all("/\/:([a-zA-Z]+){([a-zA-Z]+)}/is", $endpointURI, $matches)) {


				$endpointURI = str_replace("/", "\/", $endpointURI);

				for ($i=0; $i < count($matches[0]); $i++) { 
					$name = $matches[1][$i];
					$type = $matches[2][$i];

					if (strtoupper($type) == 'STRING') {
						$stringToReplace = "/:" . $name . "{" . $type . "}";

						$endpointURI = str_replace($stringToReplace, "/[a-zA-Z]+", $endpointURI);

					}
					
				}

				$endpointURI = "/" . $endpointURI . "/is";

				if (preg_match($endpointURI, $uri)) {
					throw new Exception("That URI matches the wildcard of another endpoint's URI"); die();
				}
			}
			
		}
	}

	public function isValidHTTPMethod($method)
	{
		$methods = ['GET', 'POST', 'PUT', 'DELETE'];

		if (in_array(strtoupper($method), $methods)) {
			return strtoupper($method);
		} else {
			return false;
		}
	}

	public function isValidInputMethod($method)
	{
		$methods = ['POST', 'PUT'];

		if (in_array(strtoupper($method), $methods)) {
			return strtoupper($method);
		} else {
			return false;
		}
	}

	public function methodNeedsID($method)
	{
		$methods = ['PUT', 'DELETE'];

		if (in_array(strtoupper($method), $methods)) {
			return strtoupper($method);
		} else {
			return false;
		}
	}

	public function inputCheck($input)
	{		
		$input = json_decode(json_encode($input));
		$types = ['STRING', 'NUMBER', 'BOOLEAN', 'JSON', 'MIXED'];

		foreach ($input as $key => $element) {		

			if (is_array($element)) {
				$this->isValidInputArray($key, $element);
			} else if (is_object($element)) {
				$this->inputCheck($element);
			} else {
				throw new Exception("'" . $key . "' has to be either JSON or an array with format [string TYPE, boolean REQUIRED]");
			}
			
		}
		
	}

	public function isValidInputArray($key, $array)
	{
		$types = ['STRING', 'NUMBER', 'BOOLEAN', 'JSON', 'MIXED'];

		if (count($array) == 2) {
			$type = $array[0];
			$required = $array[1];
			if (in_array($type, $types)) {
				if (is_bool($required)) {
					return true;
				} else {
					throw new Exception("The 'required' field for '" . $key . "' is not a boolean!");
				}
			} else {
				throw new Exception("The input type for '" . $key . "' is not valid!");
			}
		}
	}

	public function contains($str, $value) {
		return (strpos($str, $value) !== FALSE);
	}




	public function formatted()
	{

		if ($this->json != null) {
			$this->json = json_decode($this->json);
		}

		if ($this->input != null) {
			$this->input = unserialize($this->input);
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

	public function project()
	{
		return $this->belongsTo('Project');
	}

}