<?php

class HomeController extends BaseController 
{

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	 */

	public function __construct(TaskRepository $task)
	{
		$this->task = $task;
		$this->showArchives = Input::get('showArchives');
	}

	public function index()
	{

		if (Input::has('newtask')) {
			$input = Input::get('newtask');
			$this->task->addTask($input);
		}

		if (Input::has('statuschange')) {
			$id = Input::get('statuschange');
			$this->task->toggleStatus($id);
			return Redirect::to('/?showArchives=' . $this->showArchives);
		}

		if (Input::has('archive')) {
			$id = Input::get('archive');
			$this->task->toggleArchived($id);
			return Redirect::to('/?showArchives=' . $this->showArchives);
		}

		$viewData['entries'] = $this->task->showTasks()->toArray();
		$viewData['showArchives'] = $this->showArchives;

		return View::make('hello', $viewData);
	}

}
