<?php
namespace Plateau\Automaton;

use App;
use File;
use Carbon\Carbon;
use Config;
use Cron\CronExpression;
use Plateau\Automaton\Contracts\TaskInterface;
use Plateau\Automaton\Repositories\Eloquent\ScheduledTask;
use Plateau\Automaton\Repositories\Eloquent\CronLog;

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
	public function schedule(TaskInterface $task, $date, $label='')
	{
		$newTask = $this->createTask($task);
		
		$newTask->label = $label;

		if (get_class($date) == 'Carbon\Carbon')
		{
			$newTask->scheduled_at = $date->toDateTimeString();	
		}
		else
		{
			$newTask->scheduled_at = $date;	
		}
				
		$newTask->save();
		return $newTask;
	}

	/**
	 * Create the basic task object
	 * @param  TaskInterface $task [description]
	 * @return [type]              [description]
	 */
	protected function createTask(TaskInterface $task)
	{
		$newTask = new ScheduledTask();
		$newTask->type = get_class($task);
		$newTask->parameters = serialize($task->parameters);
		return $newTask;
	}

	/**
	 * Schedule a Recurring CronJob
	 * @param  TaskInterface $task           [description]
	 * @param  [type]        $cronExpression [description]
	 * @param  string        $label          [description]
	 * @return [type]                        [description]
	 */
	public function cron(TaskInterface $task, $cronExpression, $label='')
	{
		if ($this->isValidCronExpression($cronExpression) )
		{
			$newTask = $this->createTask($task);

			$newTask->label = $label;

			$newTask->is_cron = true;

			$newTask->cron_expression = $cronExpression;

			$newTask->save();

			return $newTask;
		}
		else
		{
			throw new \InvalidArgumentException($cronExpression.' is not a valid Cron expression.');
		}

	}


	protected function isValidCronExpression($expression)
	{
		// Check if the given expression is set and is correct
        if (!isset($expression) || count(explode(' ', $expression)) < 5 || count(explode(' ', $expression)) > 6) {
            return false;
        }
        else return true;
	}

	/**
	 * Run next task
	 * @return [type]
	 */
	public function run()
	{
		// Update Lock File
		$this->updateRunningStatus();

		// Run Crons
		if ($crons = $this->getDueCrons() )
		{
			foreach($crons as $cron) $this->runTask($cron);
		}

		// Run Scheduled Jobs
		if ($tasks = $this->getNextTasks() )
		{
			foreach($tasks as $task) $this->runTask($task);
		}
		else return null;
	}

	
	protected function getDueCrons()
	{
		// Get All crons wherever running or not
		if(Config::get('automaton::allow_task_overlap') == true)
		{
			$crons = ScheduledTask::where('is_cron', '=', 1)->get();

		}
		else
		{
			$crons = ScheduledTask::where('is_cron', '=', 1)->where('running', '=', 0)->get();	
		}

		foreach($crons as $cronTask)
		{
			$cron = CronExpression::factory($cronTask->cron_expression);
			
			if (! $cron->isDue() )
			{
				$crons->forget($cron->id);
			}
		}
		
		return $crons;

	}

	/** 
	 * Get next scheduled task
	 * 
	 * @return TaskInterface $object
	 * 
	 */
	protected function getNextTasks()
	{
		$now = Carbon::now()->toDateTimeString();

		// Get next undone task 
		$nextTask = ScheduledTask::whereDone(false)
			->whereRunning(false)
			->where('is_cron', '=', 0)
			->where('scheduled_at', '<' , $now)
			->orderBy('scheduled_at', 'asc')
			->get();

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

		//
		if($task->is_cron)
		{
			$cronLog = $this->getCronLogger($task);
			$cronLog->timer = $taskRunner->run();
		}
		else
		{
			$task->timer = $taskRunner->run();
		}

		if (! $taskRunner->hasErrors() )
		{
			if ($task->is_cron)
			{
				$cronLog->success = true;
				$cronLog->save();
			}
			else 
			{
				$task->success = true;	
			}
		}
		else
		{
			if($task->is_cron)
			{
				$cronLog->errors = $taskRunner->getErrors();
				$cronLog->success = false;
				$cronLog->save();
			}
			else
			{
				$task->errors = $taskRunner->getErrors();
				$task->success = false;
			}
		}

		$task->running = false;
		$task->done = true;
		$task->save();
		return $task->success;
	}


	protected function getCronLogger(ScheduledTask $task)
	{
		$cronLog = new CronLog();
		$cronLog->type = $task->type;
		$cronLog->label = $task->label;
		$cronLog->parameters = $task->parameters;
		return $cronLog;
	}

	protected function updateRunningStatus()
	{
		File::put($this->getLockFile() , time());
	}

	/**
	 * Check the running status to check if cron is running
	 * @return boolean [description]
	 */
	public function isRunning()
	{
		if (! File::exists($this->getLockFile() ))
		{
			return false;
		}

		$lastModified = Carbon::createFromTimestamp(File::get($this->getLockFile() ));

		$now = Carbon::now();

		if($lastModified->diffInMinutes($now) > 2)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	protected function getLockFile()
	{
		return storage_path().'/meta/automaton.lock';
	}
}