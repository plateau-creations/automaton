<?php
namespace Plateau\Automaton;
use Plateau\Automaton\Contracts\TaskInterface;
/*
 * Purpose of this class is to provide a
 * framework to all tasks run by the scheduler
 */

abstract class AbstractTask implements TaskInterface{
	
	public $parameters = array();

	public function init(array $parameters)
	{
		$this->parameters = $parameters;
	}
	
	abstract public function fire();

}