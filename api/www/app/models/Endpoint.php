<?php

class Endpoint extends Eloquent implements EndpointRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	public function getEndpoints() 
	{
		$endpoints = $this->with('requestHeaders', 'responseHeaders')->get();

		foreach ($endpoints as $endpoint) {
			$endpoint = $endpoint->formatted();
		}

		return $endpoints->toArray();
	}

	public function getEndpointByID($id) 
	{
		$endpoint = $this->with('requestHeaders', 'responseHeaders')->find($id);

		if ($endpoint != null) {
			return $endpoint->formatted();
		} else {
			throw new Exception("Sorry, Endpoint ID Not Found"); die();
		}
	}

	public function requestHeaders()
	{
		return $this->belongsToMany('Header', 'request_headers_endpoints', 'endpoint_id', 'header_id');
	}

	public function responseHeaders()
	{
		return $this->belongsToMany('Header', 'response_headers_endpoints', 'endpoint_id', 'header_id');
	}

	public function createEndpoint($jsonobject)
	{
		$newEndpoint = new Endpoint();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newEndpoint->name = $name;
			} else {
				throw new Exception('Name field is missing'); die();
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$newEndpoint->uri = $uri;
			} else {
				throw new Exception('URI field is missing'); die();
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$newEndpoint->method = $method;
			} else {
				throw new Exception('Method field is missing'); die();
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$newEndpoint->response_code = $response_code;
			} else {
				throw new Exception('"Response Code" field is missing'); die();
			}

			if (isset($jsonobject['object'])) {
				if(preg_match('/%(\d+)%/is', $jsonobject['object'], $matches)){
					if (Object::find($matches[1] == null)) {
						throw new Exception("Object ID does not exist"); die();
					} else {
						$object = $matches[0];
						$entry->object = $object;
					}


				} else {
					throw new Exception("'Object' field is not valid"); die();
				}
			}

			$newEndpoint->save();

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				foreach ($request_headers as $array) {
					$id_array = array();
					foreach ($array as $key => $value) {
						$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

						if ($header == null) {
							$header = new Header();
							$header->key = $key;
							$header->value = $value;
							$header->save();
						}

						$id_array[] = $header->id;
					}
					$newEndpoint->requestHeaders()->sync($id_array);
				}		
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				foreach ($response_headers as $array) {
					$id_array = array();
					foreach ($array as $key => $value) {
						$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

						if ($header == null) {
							$header = new Header();
							$header->key = $key;
							$header->value = $value;
							$header->save();
						}

						$id_array[] = $header->id;
					}
					$newEndpoint->responseHeaders()->sync($id_array);
				}	
			}

			return $newEndpoint->formatted();
		} else {
			throw new Exception('Invalid JSON'); die();
		}

	}

	public function editEndpoint($id, $jsonobject) 
	{
		$endpoint = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$endpoint->name = $name;
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$endpoint->uri = $uri;
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$endpoint->method = $method;
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$endpoint->response_code = $response_code;
			}

			if (isset($jsonobject['object'])) {
				if(preg_match('/%(\d+)%/is', $jsonobject['object'], $matches)){
					if (Object::find($matches[1]) != null) {
						$object = $matches[0];
						$endpoint->object = $object;
					} else {
						throw new Exception("Object ID does not exist"); die();
					}


				} else {
					throw new Exception("'Object' field is not valid"); die();
				}
			}

			$endpoint->save();

			if (isset($jsonobject['request_headers']) && $request_headers = $jsonobject['request_headers']) {
				foreach ($request_headers as $array) {
					$id_array = array();
					foreach ($array as $key => $value) {
						$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

						if ($header == null) {
							$header = new Header();
							$header->key = $key;
							$header->value = $value;
							$header->save();
						}

						$id_array[] = $header->id;
					}
					$endpoint->requestHeaders()->sync($id_array);
				}			
			}

			if (isset($jsonobject['response_headers']) && $response_headers = $jsonobject['response_headers']) {
				foreach ($response_headers as $array) {
					$id_array = array();
					foreach ($array as $key => $value) {
						$header = Header::where('key', '=', $key)->where('value', '=', $value)->first();

						if ($header == null) {
							$header = new Header();
							$header->key = $key;
							$header->value = $value;
							$header->save();
						}

						$id_array[] = $header->id;
					}
					$endpoint->responseHeaders()->sync($id_array);
				}	
			}

			return $endpoint->formatted();

		} else {
			throw new Exception('Invalid JSON'); die();
		}
	}


	public function removeEndpoint($id)
	{
		$endpoint = $this->find($id);

		if ($endpoint != null) {

			//Remove all headers by passing empty array to sync()
			$endpoint->requestHeaders()->sync(array());
			$endpoint->responseHeaders()->sync(array());

			$endpoint->delete();

		} else {
			throw new Exception("Sorry, that endpoint ID does not exist"); die();
		}

		return $endpoint->formatted();
	}

	public function formatted()
	{
		$id = $this->object;

		if ($id != null) {
			if (preg_match('/%(\d+)%/is', $id, $matches)) {
				$id = $matches[1];

				$object = Object::find($id);

				if ($object != null) {
					$object->json = json_decode($object->json);				
					$this->object = $object;
				} else {
					throw new Exception('The ID in the database points to an object that does not exist.'); die();
				}
			} else {
				throw new Exception('The ID in the database is not in %number% format'); die();
			}

		}

		return $this->toArray();
	}

}