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

	public function displayEndpoint($uri)
	{
		$endpoints = Endpoint::with('requestHeaders', 'responseHeaders')->get();

		foreach ($endpoints as $endpoint) {
			if ($endpoint['uri'] == $uri) {

				$request_headers = getallheaders();

				$endpoint_request_headers = $endpoint['request_headers'];
				$missingHeaders = array();
				
				foreach ($endpoint_request_headers as $header) {
					if (array_key_exists($header['key'], $request_headers)) {
						if ($header['value'] != $request_headers[$header['key']]) {
							$missingHeaders[] = array($header['key'] => $header['value']);
						}
					}
				}

				if (count($missingHeaders)) {
					$response = array();
					$response['message'] = "You are missing some endpoints";
					$response['endpoints'] = $missingHeaders;
					return $response;
				}

				$data = json_decode($endpoint['json']);
				$code = $endpoint['response_code'];
				
				$response = Response::json($data, $code);

				// $headers = $endpoint['response_headers'];


				// foreach ($headers as $header) {
				// 	$response->header($header['key'], $header['value']);
				// }

				return $response;
				return "GOOD URI";
			}
		}

		return "BAD URI";


		//return $endpoints[0]['uri'];
	}

	public function createProject($jsonobject)
	{
		$newProject = new Project();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newProject->name = $name;
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