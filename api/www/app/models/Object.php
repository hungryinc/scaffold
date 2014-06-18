<?php

class Object extends Eloquent implements ObjectRepository 
{

	public function getObjects() 
	{
		$objects = $this->get();

		foreach ($objects as $object) {
			$object = $object->formatted();
		}

		return $objects->toArray();
	}

	public function getObjectByID($id) 
	{
		$endpoint = $this->find($id);

		if ($endpoint != null) {
			return $endpoint->formatted();
		} else {
			throw new Exception("Sorry, Object ID Not Found"); die();
		}
	}

	public function createObject($jsonobject)
	{
		$newObject = new Object();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json']) AND count($jsonobject['json'])) {
					$newObject->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("'JSON' field is not valid");
				}
			} else {
				throw new Exception("JSON field is missing");
			}

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newObject->name = $name;
			} else {
				throw new Exception('Name field is missing');
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$newObject->description = $description;
			} else {
				throw new Exception('Description field is missing');
			}

			$newObject->save();

			return $newObject->formatted();
		} else {
			throw new Exception('Invalid JSON');
		}
	}

	public function editObject($id, $jsonobject) 
	{
		$object = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {
		
			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$object->name = $name;
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$object->description = $description;
			}

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json']) AND count($jsonobject['json'])) {
					$object->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("the field 'JSON' is not valid");
				}
			}

			$object->save();

			return $object->formatted();
		} else {
			throw new Exception('Invalid JSON');
		}
	}

	public function removeObject($id)
	{
		$object = $this->find($id);

		if ($object != null) {

			//Remove all headers by passing empty array to sync()
			$object->requestHeaders()->sync(array());
			$object->responseHeaders()->sync(array());

			$object->delete();
			
		} else {
			throw new Exception("Sorry, that endpoint ID does not exist"); die();
		}

		return $object->formatted();
	}

	public function formatted()
	{
		$this->json = json_decode($this->json);
		
		return $this->toArray();
	}

}