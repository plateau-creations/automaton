# Laravel 4 Automaton Taks Scheduler

Automaton is a task Scheduler for Laravel 4 designed to run as a CronJob. It's designed to run resource intensive tasks with PHP CLI while providing a user friendly way to track down the execution of the tasks.

The planned tasks are stored in a database table, and are 'sandboxed' so any exception occuring will be logged into the database for easier debugging. 

## Installation

Add this into `require-dev` in your `composer.json` file:

```
"require-dev" : {
	...
	"plateau/automaton": "dev-master"
}
```

Run an update:

```
php composer.phar update
```

Register the console service provider in `app/config/app.php`:

```php
'providers' => array(
	...
	'Plateau\Automaton\AutomatonServiceProvider',
);
```

Run migrations 
```
php artisan migrate --package=plateau/automaton
```

## Usage

Configure your crontab to run Automaton at a regular interval :

```
* * * * * php /var/www/laravel-app/artisan automaton:run >/dev/null 2>&1
```

Create a task class that contains your logic : 

```
use Plateau\Automaton\AbstractTask;

class MyTask extends AbstractTask {
	
	public function fire()
	{
		// Task logic
	}
}
```

Schedule your task :

```
use Plateau\Automaton\Scheduler;

$scheduler = new Scheduler();

// Parameters are accessible from the task object as $this->parameters
$parameters = array('key' => 'value');

$myTask = new MyTask($parameters);

$scheduler->schedule($myTask, '2014-02-17 12:00:00');
```

