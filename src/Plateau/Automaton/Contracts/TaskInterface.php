<?php
namespace Plateau\Automaton;

interface TaskInterface {

	public function init(array $parameters);

	public function fire();

}