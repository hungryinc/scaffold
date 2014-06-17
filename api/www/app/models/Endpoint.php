<?php

class Endpoint extends Eloquent implements EndpointRepository 
{

	protected $hidden = array('pivot', 'created_at', 'updated_at');

	public function showEndpoints() 
	{
		$endpoints = $this->with('requestHeaders', 'responseHeaders')->get();

		return $endpoints;
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
		$newEntry = new Endpoint();

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$newEntry->name = $name;
			} else {
				throw new Exception('Name field is missing'); die();
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$newEntry->uri = $uri;
			} else {
				throw new Exception('URI field is missing'); die();
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$newEntry->method = $method;
			} else {
				throw new Exception('Method field is missing'); die();
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$newEntry->response_code = $response_code;
			} else {
				throw new Exception('"Response Code" field is missing'); die();
			}

			if (isset($jsonobject['object'])) {
				if(preg_match('/%(\d+)%/is', $jsonobject['object'], $matches)){
					$object = Object::find($matches[1]);

					if ($object == null) {
						throw new Exception("Object ID does not exist"); die();
					}

					$newEntry->object = json_encode($object);
				} else {
					throw new Exception("'Object' field is not valid"); die();
				}
			}

			$newEntry->save();

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
					$newEntry->requestHeaders()->sync($id_array);
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
					$newEntry->responseHeaders()->sync($id_array);
				}	
			}

			return $newEntry->formatted();
		} else {
			throw new Exception('Invalid JSON'); die();
		}

	}

	public function changeEndpoint($id, $jsonobject) 
	{
		$entry = $this->find($id);

		if (is_array($jsonobject) AND count($jsonobject)) {

			if (isset($jsonobject['name']) && $name = $jsonobject['name']) {
				$entry->name = $name;
			}

			if (isset($jsonobject['uri']) && $uri = $jsonobject['uri']) {
				$entry->uri = $uri;
			}

			if (isset($jsonobject['method']) && $method = $jsonobject['method']) {
				$entry->method = $method;
			}

			if (isset($jsonobject['response_code']) && $response_code = $jsonobject['response_code']) {
				$entry->response_code = $response_code;
			}

			if (isset($jsonobject['object'])) {
				if(preg_match('/%(\d+)%/is', $jsonobject['object'], $matches)){
					$object = $matches[0];

					if ($object == null) {
						throw new Exception("Object ID does not exist"); die();
					}

					$entry->object = $object;
				} else {
					throw new Exception("'Object' field is not valid"); die();
				}
			}

			$entry->save();

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
					$entry->requestHeaders()->sync($id_array);
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
					$entry->responseHeaders()->sync($id_array);
				}	
			}

			return $entry->formatted();

		} else {
			throw new Exception('Invalid JSON'); die();
		}
	}


	public function remove($id)
	{
		$entry = $this->find($id);

		//Remove all headers by passing empty array to sync()
		$entry->requestHeaders()->sync(array());
		$entry->responseHeaders()->sync(array());

		$entry->delete();

		return $entry->formatted();
	}

	public function formatted()
	{
		$id = $this->object;

		if(preg_match('/%(\d+)%/is', $id, $matches)){
			$id = $matches[1];
		}

		$object = Object::find($id);

		$object->json = json_decode($object->json);
		
		$this->object = $object;

		return $this->toArray();
	}

}