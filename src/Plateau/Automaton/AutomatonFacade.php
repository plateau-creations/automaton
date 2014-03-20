<?php
namespace Plateau\Automaton;

use Illuminate\Support\Facades\Facade;

class AutomatonFacade extends Facade {
	
	 /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'automaton'; }
}