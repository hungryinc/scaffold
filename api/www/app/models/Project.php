<?php

class Project extends Eloquent implements ProjectRepository 
{

	protected $hidden = array('pivot');

	public function getAllProjects() 
	{
		$projects = $this->get();

		foreach ($projects as $project) {
			$project = $project->formatted();
		}

		return $projects->toArray();
	}

	public function getProjectByID($id) 
	{
		$project = $this->with('endpoints', 'objects')->find($id);

		if ($project != null) {
			return $project->formatted();
		} else {
			throw new Exception("Sorry, Project ID Not Found"); die();
		}
	}

	public function getProjectByName($name) {
		$projects = $this->with('endpoints', 'objects')->get();

		foreach ($projects as $project) {
			if (Str::slug($name) == Str::slug($project->name)) {
				return $project->formatted();
			}
		}

		throw new Exception("Sorry, Project does not exist"); die();
	}

	public function getAllEndpoints($name) {	
		
		if ($project = $this->getProjectByName($name)) {		
			$endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->where('project_id', '=', $project['id'])->get();

			foreach ($endpoints as $endpoint) {
				$endpoint->json = json_decode($endpoint->json);
			}

			return $endpoints->toArray();	
		} else {	
			throw new Exception("The project name does not exist");	die();
		}

	}

	public function getAllObjects($name) {	
		
		if ($project = $this->getProjectByName($name)) {		
			$objects = Object::where('project_id', '=', $project['id'])->get();

			foreach ($objects as $object) {
				$object->json = json_decode($object->json);
			}

			return $objects->toArray();	
		} else {	
			throw new Exception("The project name does not exist");	die();
		}

	}


	public function displayEndpoint($name, $uri, $method, $input)
	{
		if ($project = $this->getProjectByName($name)) {

			if ($endpoint = $this->getEndpointFromURI($uri)) {


			// if (count($endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->where('uri', '=', $uri)->get())) {

				//CHECK IF HTTP METHOD IS ALLOWED
				$endpointMethod = $endpoint['method'];
				if ($endpointMethod != $method) {
					throw new Exception("This endpoint's HTTP method is '" . $endpointMethod . "' but you are using '" . $method . "."); die();
				}

				//DEAL WITH REQUEST HEADERS
				$request_headers = getallheaders();

				$endpoint_request_headers = $endpoint['request_headers'];

				$missingHeaders = array();
				
				foreach ($endpoint_request_headers as $header) {

					if (array_key_exists($header['key'], $request_headers)) {

						if ($header['value'] != $request_headers[$header['key']]) {
							$missingHeaders[] = array($header['key'] => $header['value']);
						}

					} else {

						$missingHeaders[] = array($header['key'] => $header['value']);

					}

				}

				if (count($missingHeaders)) {
					$response = array();
					$response['message'] = "You are missing some request headers";
					$response['request headers'] = $missingHeaders;
					throw new Exception(json_encode($response)); die();
				}

				//DEAL WITH ENDPOINT JSON DATA AND ANY OBJECT JSON DATA

				if (in_array(strtoupper($endpointMethod), ['POST', 'PUT'])) {
					if ($input != null) {
						$array = unserialize($endpoint['input']);
						$this->verifyInputData($input, $array);
					} else {
						throw new Exception('Invalid JSON'); die();
					}
				}

				$data['data'] = json_decode($endpoint['json']);
				

				if ($data['data'] != null) {
					foreach ($data['data'] as $key => $value) {
						if ($object = $this->getObject($value)) {
							$data['data']->$key = json_decode($object->json);
						}
					}
				}
				
				

				//GET RESPONSE CODE FROM ENDPOINT
				$code = $endpoint['response_code'];
				
				//ASSIGN HEADERS FROM ENDPOINT AND RETURN RESPONSE
				$response_headers = array();
				$endpoint_response_headers = $endpoint['response_headers'];

				if ($endpoint_response_headers != null) {
					foreach ($endpoint_response_headers as $header) {
						$headerString = "" . $header['key'] . ": " . $header['value'];
						header($headerString, true, $code);
					}
				}

				return Response::json($data, $code);
			} else {
				throw new Exception("That URI does not exist for any of the endpoints in the project"); die();
			}	

		} else {
			throw new Exception("The project name does not exist"); die();
		}
	}

	public function createProject($jsonobject)
	{
		$newProject = new Project();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newProject->name = $name;

				try {
					if ($id = $this->getProjectByName($name)->id) {
						throw new Exception('That name is already being used by project id #' . $id); die();
					}
				} catch (Exception $e) {}

			} else {
				throw new Exception('Name field is missing'); die();
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$newProject->description = $description;
			} else {
				throw new Exception('Description field is missing'); die();
			}

			$newProject->save();

			return $newProject->formatted();
		} else {
			throw new Exception('Invalid JSON'); die();
		}
	}

	public function editProject($id, $jsonobject)
	{
		$project = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$project->name = $name;

				if ($id = $this->getProjectByName($name)->id) {
					throw new Exception('That name is already being used by project id #' . $id); die();
				}

			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$project->description = $description;
			}

			$project->save();

			return $project;
		} else {
			throw new Exception('Invalid JSON');
		}
	}

	public function removeProject($id)
	{
		$project = $this->find($id);

		if ($project != null) {

			$endpoints = $project->endpoints;
			foreach ($endpoints as $endpoint) {
				$endpoint->removeEndpoint($endpoint->id);
			}

			$objects = $project->objects;
			foreach ($objects as $object) {
				$object->removeObject($object->id);
			}

			$project->delete();

		} else {
			throw new Exception("Sorry, tht project ID does not exist."); die();
		}

		return $project->formatted();
	}

	public function endpoints()
	{
		return $this->hasMany('Endpoint');
	}

	public function objects()
	{
		return $this->hasMany('Object');
	}
	
	public function getObject($str) {

		try {
			if (preg_match('/<%(\d+)%>/is', $str, $matches)){
				if (($object = Object::find($matches[1])) != null) {

					return $object;

				} else {
					return null;
				}
			} else {
				return null;
			}
		} catch (Exception $e) {
			return false;
		}
		
	}

	public function getEndpointFromURI($uri)
	{

		$endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->get();

		foreach ($endpoints as $endpoint) {
			$endpointURI = $endpoint['uri'];

			if ($endpointURI == $uri) {
				return $endpoint;
			}

			if (preg_match_all("/\/:([a-zA-Z]+){([a-zA-Z]+)}/is", $endpointURI, $matches)) {


				$endpointURI = str_replace("/", "\/", $endpointURI);

				for ($i=0; $i < count($matches[0]); $i++) { 
					$name = $matches[1][$i];
					$type = $matches[2][$i];

					if (strtoupper($type) == 'STRING') {
						$stringToReplace = "/:" . $name . "{" . $type . "}";

						$endpointURI = str_replace($stringToReplace, "/[a-zA-Z]+", $endpointURI);

					} else if (strtoupper($type) == 'NUMBER') {
						$stringToReplace = "/:" . $name . "{" . $type . "}";

						$endpointURI = str_replace($stringToReplace, "/[0-9]+", $endpointURI);
					}
					
				}

				$endpointURI = "/" . $endpointURI . "/is";

				if (preg_match($endpointURI, $uri)) {
					return $endpoint;
				}
			}
			
		}
		return null;
	}

	public function verifyInputData($inputData, $array)
	{

		$array = json_decode(json_encode($array));

		foreach ($array as $name => $element) {

			if (is_object($element)) {

				try {
					$value = $inputData[$name];
				} catch (Exception $e) {
					throw new Exception("The '" . $name . "' JSON field in your input data is missing!!!"); die();
				}
				$this->verifyInputData($value, $element);

			} else if (is_array($element) && count($element) == 2){
				$this->verifyInputDataArray($name, $element, $inputData);

			} else {
				throw new Exception("The value of '" . $name . "' in the database was not a valid array or JSON object."); die();
			}

		}


		return true;
	}

	public function verifyInputDataArray($name, $element, $inputData)
	{
		if (is_array($element) && count($element) == 2) {
			$type = $element[0];
			$required = $element[1];

			try {
				$value = $inputData[$name];
			} catch (Exception $e) {

				if ($required) {
					throw new Exception("The '" . $name . "' field in your input data is missing!!!"); die();
				} else {
					return null;
				}

			}

			$declaredType = $type;
			$inputType = strtoupper(gettype($value));

			if ($declaredType == 'NUMBER') {
				if ($inputType == 'DOUBLE' || $inputType == 'INTEGER') {
					return true;
				} else {
					throw new Exception("Input value '" . $name . "' has to be a number!!!"); die();
				}
			} else if ($declaredType == 'BOOLEAN') {
				if ($inputType == 'BOOLEAN') {
					return true;
				} else {
					throw new Exception("Input value '" . $name . "' has to be a boolean!!!"); die();
				}
			} else if ($declaredType == 'STRING') {
				if ($inputType == 'STRING') {
					return true;
				} else {
					throw new Exception("Input value '" . $name . "' has to be a string!!!"); die();
				}
			} else if ($declaredType == 'MIXED') {
				return true;
			}

		} else {
			throw new Exception('An invalid array was given to verifyInputDataArray()'); die();
		}
	}

	public function contains($str, $value) {
	    return (strpos($str, $value) !== FALSE);
	}

	public function formatted()
	{

		$endpoints = $this->endpoints;

		foreach ($endpoints as $endpoint) {
			$endpoint->json = json_decode($endpoint->json);
		}
		$this->endpoints = $endpoints;


		$objects = $this->objects;
		foreach ($objects as $object) {
			$object->json = json_decode($object->json);
		}
		$this->objects = $objects;

		return $this->toArray();
	}

}