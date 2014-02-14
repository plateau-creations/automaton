<?php
namespace Plateau\Automaton\Contracts;

interface TaskInterface {

	public function init(array $parameters);

	public function fire();

}