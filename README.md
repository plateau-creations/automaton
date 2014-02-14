# Laravel 4 Automaton Taks Scheduler

Automaton is a task Scheduler for Laravel 4 designed to work as a CronJob.

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

Soon.
