<?php
namespace Plateau\Automaton;

use App;
use Carbon\Carbon;
use Plateau\Automaton\Contracts\TaskInterface;
use Plateau\Automaton\Repositories\Eloquent\ScheduledTask;

/**
 * Scheduler Class 
 */
class Scheduler {
	
	/**
	 * Schedule a new task 
	 * 
	 * @param  TaskInterface $task
	 * @param  [type]        $date
	 * @param  string        $label
	 * @param  array         $parameters
	 * @return [type]
	 */
	public function schedule(TaskInterface $task, $date, $label='', $parameters = array() )
	{
		$newTask = new ScheduledTask();
		$newTask->type = get_class($task);
		$newTask->parameters = serialize($parameters);
		$newTask->label = $label;
		$newTask->scheduled_at = $date;
		$newTask->save();
		return $newTask;
	}

	/**
	 * Run next task
	 * @return [type]
	 */
	public function run()
	{
		if ($task = $this->getNextTask() )
		{
			$this->runTask($task);
		}
		else return null;
	}

	/** 
	 * Get next scheduled task
	 * 
	 * @return TaskInterface $object
	 * 
	 */
	protected function getNextTask()
	{
		$now = Carbon::now()->toDateTimeString();

		// Get next undone task 
		$nextTask = ScheduledTask::whereDone(false)
			->whereRunning(false)
			->where('scheduled_at', '<' , $now)
			->orderBy('scheduled_at', 'asc')
			->first();

		if ($nextTask)
		{
			return $nextTask;			
		}
		else return null;	
	}

	/** 
	 * Run a task
	 * @param  TaskInterface $task
	 * @return boolean
	 */
	protected function runTask(ScheduledTask $task)
	{
		$task->running = true;
		$task->save();

		// Task type to bind by the ioc container
		$type = $task->type;

		$parameters = unserialize($task->parameters);
		
		$taskRunner = new TaskRunner(App::make($type));
		
		$taskRunner->setParameters($parameters);

		$task->timer = $taskRunner->run();

		if (! $taskRunner->hasErrors() )
		{
			$task->success = true;
		}
		else
		{
			$task->errors = $taskRunner->getErrors();
			$task->success = false;
		}

		$task->running = false;
		$task->done = true;
		$task->save();
		return $task->success;
	}


}