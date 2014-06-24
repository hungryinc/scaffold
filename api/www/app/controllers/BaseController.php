<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function error($error)
	{
		return Response::json($error, 400);
	}

}
