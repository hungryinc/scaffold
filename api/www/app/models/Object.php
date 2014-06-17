<?php

class Object extends Eloquent implements ObjectRepository 
{

	public function showObjects() 
	{
		$objects = $this->get();

		return $objects;
	}

	public function createObject($jsonobject)
	{
		$newEntry = new Object();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json']) AND count($jsonobject['json'])) {
					$newEntry->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("'JSON' field is not valid");
				}
			} else {
				throw new Exception("JSON field is missing");
			}

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newEntry->name = $name;
			} else {
				throw new Exception('Name field is missing');
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$newEntry->description = $description;
			} else {
				throw new Exception('Description field is missing');
			}

			$newEntry->save();

			return $newEntry->formatted();
		} else {
			throw new Exception('Invalid JSON');
		}
	}

	public function changeObject($id, $jsonobject) 
	{
		$entry = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {
		
			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$entry->name = $name;
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$entry->description = $description;
			}

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json']) AND count($jsonobject['json'])) {
					$entry->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("the field 'JSON' is not valid");
				}
			}

			$entry->save();

			return $entry->formatted();
		} else {
			throw new Exception('Invalid JSON');
		}
	}

	public function formatted()
	{
		$this->json = json_decode($this->json);
		
		return $this->toArray();
	}

	public function remove($id)
	{
		$entry = $this->find($id);
		$entry->delete();

		return $entry->toArray();
	}

}