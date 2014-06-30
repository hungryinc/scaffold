<?php

class ProjectController extends BaseController
{
	public function __construct(ProjectRepository $project)
	{
		$this->project = $project;
	}

	//GET Methods
	public function getAllProjects()
	{
		try {
			$projects['data'] = $this->project->getAllProjects();
		} catch (Exception $e) {
			
			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);

		}

		return Response::json($projects);
	}

	public function getProjectById($id)
	{
		$project['data'] = $this->project->getProjectByID($id);
		return Response::json($project);
	}

	public function getProjectByName($projectName)
	{
		try {
			$project['data'] = $this->project->getProjectByName($projectName);
		} catch (Exception $e) {
			
			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);

		}

		return Response::json($project);
	}

	public function getAllEndpoints($projectName)
	{
		$endpoints['data'] = $this->project->getAllEndpoints($projectName);
		return Response::json($endpoints);
	}

	public function getAllObjects($projectName)
	{
		$objects['data'] = $this->project->getAllObjects($projectName);
		return Response::json($objects);
	}

	public function displayEndpoint($projectName, $uri)
	{

		try {
			$result = $this->project->displayEndpoint($projectName, '/'.$uri);
		} catch (Exception $e) {

			throw $e; die();

			$message = $e->getMessage();
			$error = json_decode($message);

			if (!(json_last_error() == JSON_ERROR_NONE)) {
				$message = $e->getMessage();
				$error = array('message'=>$message);
			}

			return $this->error($error);

		}
		return $result;
	}


	//POST Methods

	public function createProject()
	{
		$json = Input::get();

		try {
			$project['data'] = $this->project->createProject($json);
		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);

		}
		
		return Response::json($project);	
	}

	//PUT Methods

	public function editProject($id)
	{
		$json = Input::get();

		try {
			$project['data'] = $this->project->editProject($id, $json);
		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);			
		}	

		return Response::json($project);
	}

	//DELETE Methods

	public function removeProject($id)
	{
		try {

			$project['data'] = $this->project->removeProject($id);

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);
			
		}

		return Response::json($project);
	}
	
}