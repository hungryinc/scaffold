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
		$endpoint = $this->endpoint->where('id', $id)->get();

		if ($endpoint != null) {
			return $endpoint;
		} else {
			return "Sorry, ID Not Found";
		}
	}

	public function test()
	{
		$endpt = $this->endpoint->requestHeaders;

		echo $endpt;
	}

	//Default Case

	public function missingMethod($parameters = array())
	{
		echo "Sorry, that API call does not exist.";
	}
}