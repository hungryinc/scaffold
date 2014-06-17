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
		$objects['data'] = $this->object->showObjects()->toArray();

		return Response::json($objects);
	}

	public function getObjectById($id)
	{
		$object = $this->object->where('id', $id)->get();

		if ($object != null) {
			return $object;
		} else {
			return "Sorry, ID Not Found";
		}
	}


	//POST Methods

	public function createObject()
	{
		$json = Input::get();

		try {
			$result['data'] = $this->object->createObject($json);
			return Response::json($result);	
		} catch (Exception $e) {
			$message = $e->getMessage();
			$error = array('message'=>$message);

			return $this->error(json_encode($error));
		}
	}


	//PUT Methods

	public function changeObject($id)
	{
		$json = Input::get();

		try {
			$result['data'] = $this->object->changeObject($id, $json);

			return Response::json($result);	
		} catch (Exception $e) {
			$message = $e->getMessage();
			$error = array('message'=>$message);

			return $this->error(json_encode($error));
		}	
	}

	//DELETE Methods

	public function removeObject($id)
	{
		$object['data'] = $this->object->remove($id);
		return Response::json($object);
	}


	//Default Case

	public function missingMethod($parameters = array())
	{
		echo "Sorry, that API call does not exist.";
	}
}