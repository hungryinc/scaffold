<?php

class Object extends Eloquent implements ObjectRepository 
{

	public function getObjects() 
	{
		$objects = $this->get();

		if ($objects != null) {
			foreach ($objects as $object) {
				$object = $object->formatted();
			}
		} else {
			throw new Exception("There was a problem retrieving the objects from the database."); die();
		}

		return $objects->toArray();
	}

	public function getObjectByID($id) 
	{
		$object = $this->find($id);

		if ($object != null) {
			return $object->formatted();
		} else {
			throw new Exception("Sorry, Object ID Not Found"); die();
		}
	}

	public function createObject($jsonobject)
	{
		$newObject = new Object();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json'])) {
					$newObject->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("'JSON' field is not valid");
				}

			} else {
				throw new Exception("JSON field is missing");
			}

			if (isset($jsonobject['project_id']) && $project_id = $jsonobject['project_id']) {
				$newObject->project_id = $project_id;

				if (!Project::find($project_id)) {
					throw new Exception("That project ID does not exist in the database"); die();
				}

			} else {
				throw new Exception('"project_id" field is missing'); die();
			}

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newObject->name = $name;

				if ($this->checkName($name)) {
					throw new Exception('Sorry, that name is being used by another object!'); die();
				}

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

				if ($this->checkName($name)) {
					throw new Exception('Sorry, that name is being used by another object!'); die();
				}
				
			}

			if (isset($jsonobject['description']) && $description = $jsonobject['description']) {
				$object->description = $description;
			}

			if (isset($jsonobject['json'])) {

				if (is_array($jsonobject['json'])) {
					$object->json = json_encode($jsonobject['json']);
				} else {
					throw new Exception("the field 'JSON' is not valid");
				}
			}

			if (isset($jsonobject['project_id']) && $project_id = $jsonobject['project_id']) {
				$object->project_id = $project_id;

				if (!Project::find($project_id)) {
					throw new Exception("That project ID does not exist in the database"); die();
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

			$endpoints = Endpoint::get();

			if ($endpoints != null) {
				$array = array();
				$id_modified = '<%'.$id.'%>';

				foreach ($endpoints as $endpoint) {
					$jsonobject = json_decode($endpoint->json);	

					foreach ($jsonobject as $key => $value) {
						if ($value == $id_modified) {
							unset($jsonobject->$key);
							$array[] = $endpoint->id;
							$endpoint->json = json_encode($jsonobject);
							$endpoint->save();
							break;
						}
					}
				}


				if (count($array)) {

					if (count($array) == 1) {
						$warning = "The object was deleted and is no longer being referenced by endpoint: " . $array[0];
					} else {
						$warning = "The object was deleted and is no longer being referenced by the following endpoints: ";

						foreach ($array as $value) {
							$warning .= $value . ", ";
						}
					}			

					$object->warning = $warning;
				} 
			}		

			$object->delete();

		} else {
			throw new Exception("Sorry, that endpoint ID does not exist"); die();
		}

		return $object->formatted();
	}

	//DUPLICATE CHECK METHOD

	public function checkName($name)
	{
		$objects = $this->getObjects();
		foreach ($objects as $object) {
			if ($object['name'] == $name) {
				return true;
			}
		}
		return false;
	}

	public function formatted()
	{

		$this->json = json_decode($this->json);
		
		return $this->toArray();
	}

	public function project()
	{
		return $this->belongsTo('Project');
	}

}