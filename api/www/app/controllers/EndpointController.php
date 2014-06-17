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
		$endpoints['data'] = $this->endpoint->showEndpoints()->toArray();

		return Response::json($endpoints);
	}

	public function getEndpointById($id)
	{
		$endpoint = $this->endpoint->with('requestHeaders', 'responseHeaders')->find($id);

		if ($endpoint != null) {
			return Response::json($endpoint);
		} else {
			return "Sorry, ID Not Found";
		}
	}



	//POST Methods

	public function createEndpoint()
	{
		$json = Input::get();

		try {
			$result['data'] = $this->endpoint->createEndpoint($json);
			return Response::json($result);	
		} catch (Exception $e) {
			$message = $e->getMessage();
			$error = array('message'=>$message);

			return $this->error(json_encode($error));
		}
	}



	//PUT Methods

	public function changeEndpoint($id)
	{
		$json = Input::get();

		try {
			$result['data'] = $this->endpoint->changeEndpoint($id, $json);
			return Response::json($result);	
		} catch (Exception $e) {
			$message = $e->getMessage();
			$error = array('message'=>$message);

			return $this->error($e);
		}	
	}

	//DELETE Methods

	public function removeEndpoint($id)
	{
		$result['data'] = $this->endpoint->remove($id);
		return Response::json($result);
	}



	//Default Case

	public function missingMethod($parameters = array())
	{
		echo "Sorry, that API call does not exist.";
	}
}