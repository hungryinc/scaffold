<?php

class EndpointController extends BaseController
{
	public function __construct(EndpointRepository $endpoint)
	{
		$this->endpoint = $endpoint;
	}

	//GET Methods

	public function getAllEndpoints()
	{
		$endpoints['data'] = $this->endpoint->getEndpoints();
		return Response::json($endpoints);
	}

	public function getEndpointById($id)
	{
		$endpoint['data'] = $this->endpoint->getEndpointByID($id);
		return Response::json($endpoint);
	}

	//POST Methods

	public function createEndpoint()
	{
		$json = Input::get();

		try {

			$endpoint['data'] = $this->endpoint->createEndpoint($json);

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error(json_encode($error));

		}
		
		return Response::json($endpoint);
	}

	//PUT Methods

	public function editEndpoint($id)
	{
		$json = Input::get();

		try {

			$endpoint['data'] = $this->endpoint->editEndpoint($id, $json);

		} catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error(json_encode($error));

		}	

		return Response::json($endpoint);	
	}

	//DELETE Methods

	public function removeEndpoint($id)
	{
		try {

			$endpoint['data'] = $this->endpoint->removeEndpoint($id);

	    } catch (Exception $e) {

			$message = $e->getMessage();
			$error = array('message'=>$message);
			return $this->error(json_encode($error));

		}

		return Response::json($endpoint);

	}

}