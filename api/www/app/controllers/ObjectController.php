<?php

class ObjectController extends BaseController
{
	public function __construct(ObjectRepository $object)
	{
		$this->object = $object;
	}

	//GET Methods

	public function getAllObjects()
	{
		$objects['data'] = $this->object->getObjects();
		return Response::json($objects);
	}

	public function getObjectById($id)
	{
		$object['data'] = $this->object->getObjectByID($id);
		return Response::json($object);
	}


	//POST Methods

	public function createObject()
	{
		$json = Input::get();

		try {
			$object['data'] = $this->object->createObject($json);
		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error(json_encode($error));
			
		}
		
		return Response::json($object);	
	}


	//PUT Methods

	public function editObject($id)
	{
		$json = Input::get();

		try {

			$object['data'] = $this->object->editObject($id, $json);

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error(json_encode($error));

		}	

		return Response::json($object);
	}

	//DELETE Methods

	public function removeObject($id)
	{
		try {

			$object['data'] = $this->object->removeObject($id);

		} catch (ObjectDeleteException $e) {
			
			$message = $e->getMessage();
			$endpoints = $e->getEndpoints();
			$error = array('message'=>$message, 'endpoints'=>$endpoints);
			return $this->error(json_encode($error));

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error($e);

		}

		return Response::json($object);
	}
	
}