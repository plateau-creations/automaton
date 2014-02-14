<?php
namespace Plateau\Automaton;

use Plateau\Automaton\Utiliies\Timer;
use Plateau\Automaton\Contracts\TaskInterface;


class TaskRunner {

	protected $task;
	protected $errors;

	protected $timer;

	public function __construct(TaskInterface $task)
	{
		$this->task = $task;
		$this->timer = new Timer();
	}

	public function setParameters(array $parameters)
	{
		return $this->task->init($parameters);
	}

	public function run()
	{
		$this->timer->start();
		// * Sandbox This *
		try { 
			$this->task->fire();
		}
		catch (\Exception $e)
		{
			$this->errors = $e;
		}
		$this->timer->stop();
		return $this->timer->getTime();
	}


	public function hasErrors()
	{
		return (isset($this->errors) ? true : false);
	}

	public function getErrors()
	{
		return (isset($this->errors) ? $this->errors : null);
	}


}