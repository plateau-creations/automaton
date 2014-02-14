<?php
namespace Plateau\Automaton\Utilities;

/**
 * Very Simple Timer class for Profiling
 */
class Timer
{
	protected $timerStart;

	protected $stopped = false;

	protected $runtime;

	public function start()
	{
		$this->timerStart = microtime(true);
	}

	public function stop()
	{
		$this->runtime = $this->calculateRuntime();
		$this->stopped = true;
		return $this->getTime();
	}

	public function getTime()
	{
		if($this->stopped)
		{
			return $this->runtime;
		}
		else return $this->calculateRuntime();
	}

	public function getTimeMilliseconds()
	{
		return $this->getTime() * 1000;
	}

	public function getTimeSeconds()
	{
		return $this->getTime();
	}

	private function calculateRuntime()
	{
		return microtime(true) - $this->timerStart;
	}

}