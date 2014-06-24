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
		$projects['data'] = $this->project->getProjects();
		return Response::json($projects);
	}

	public function getProjectById($id)
	{
		$project['data'] = $this->project->getProjectByID($id);
		return Response::json($project);
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

		} catch (ObjectDeleteException $e) {
			
			$message = $e->getMessage();
			$endpoints = $e->getEndpoints();
			$error = array('message'=>$message, 'endpoints'=>$endpoints);
			return $this->error($error);

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($error);
			
		}

		return Response::json($project);
	}
	
}