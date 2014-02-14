<?php namespace Plateau\Automaton;

use Illuminate\Support\ServiceProvider;

class AutomatonServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// Registering commands
		$this->app['command.automaton.run'] = $this->app->share(function($app)
		{
			return new \AutomatonRunCommand();
		});
		$this->commands('command.automaton.run');	
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}