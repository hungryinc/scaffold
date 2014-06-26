<?php

class Project extends Eloquent implements ProjectRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	public function getProjects() 
	{
		$projects = $this->with('endpoints', 'objects')->get();

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

		return null;
	}

	public function getAllEndpoints($name) {	
		
		if ($project = $this->getProjectByName($name)) {		
			$endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->where('project_id', '=', $project['id'])->get();

			foreach ($endpoints as $endpoint) {
				$endpoint->json = json_decode($endpoint->json);
			}

			return $endpoints->toArray();	
		} else {	
			throw new Exception("The project name does not exist");	
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
			throw new Exception("The project name does not exist");	
		}
	
	}


	public function displayEndpoint($name, $uri)
	{
		if ($project = $this->getProjectByName($name)) {
			if (count($endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->where('uri', '=', $uri)->get())) {

				$endpoint = $endpoints[0];

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
					throw new Exception(json_encode($response));
				}


				//DEAL WITH ENDPOINT JSON DATA AND ANY OBJECT JSON DATA
				$data = json_decode($endpoint['json']);

				foreach ($data as $key => $value) {

					if ($object = $this->getObject($value)) {
						$data->$key = json_decode($object->json);
					}

				}

				//GET RESPONSE CODE FROM ENDPOINT
				$code = $endpoint['response_code'];
				
				//ASSIGN HEADERS FROM ENDPOINT AND RETURN RESPONSE
				$response_headers = array();
				$endpoint_response_headers = $endpoint['response_headers'];

				foreach ($endpoint_response_headers as $header) {
					$response_headers[$header['key']] = $header['value'];
				}


				return Response::json($data, $code, $response_headers);
			} else {
				throw new Exception("That URI does not exist for any of the endpoints in the project");
			}	

		} else {
			throw new Exception("The project name does not exist");
		}
	}

	public function createProject($jsonobject)
	{
		$newProject = new Project();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newProject->name = $name;

				if ($id = $this->getProjectByName($name)->id) {
					throw new Exception('That name is already being used by project id #' . $id); die();
				}

			} else {
				throw new Exception('Name field is missing');
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$newProject->description = $description;
			} else {
				throw new Exception('Description field is missing');
			}

			$newProject->save();

			return $newProject;
		} else {
			throw new Exception('Invalid JSON');
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
			throw new Exception("Sorry, tht project ID does not exist.");
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
		if (preg_match('/<%(\d+)%>/is', $str, $matches)){
			if (($object = Object::find($matches[1])) != null) {

				return $object;

			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	public function formatted()
	{

		$endpoints = $this->endpoints;

		foreach ($endpoints as $endpoint) {
			$endpoint = Endpoint::with('requestHeaders', 'responseHeaders')->find($endpoint->id);
			$endpoint->json = json_decode($endpoint->json);
			// echo $endpoint; die();
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