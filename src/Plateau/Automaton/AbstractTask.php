<?php
namespace Plateau\Automaton;
use Plateau\Automaton\Contracts\TaskInterface;
/*
 * Purpose of this class is to provide a
 * framework to all tasks run by the scheduler
 */

abstract class AbstractTask implements TaskInterface{
	
	protected $parameters;

	public function __construct(array $parameters = array() )
	{
		$this->init($parameters);
	}

	public function init(array $parameters)
	{
		$this->parameters = $parameters;
	}
	
	abstract public function fire();

}