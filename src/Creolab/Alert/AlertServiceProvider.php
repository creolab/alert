<?php namespace Creolab\Alert;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Alert package
 * @author Boris Strahija <boris@creolab.hr>
 */
class AlertServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 * @return void
	 */
	public function boot()
	{
		// Register the package
		$this->package('creolab/alert', 'alert', __DIR__.'/../../');

		// Register IoC bindings
		$this->registerBindings();

		// Shortcut so developers don't need to add an Alias in app/config/app.php
		if ($alias = $this->app['config']->get('alert::alias', 'Alert'))
		{
			$this->app->booting(function() use ($alias)
			{
				$loader = \Illuminate\Foundation\AliasLoader::getInstance();

				$loader->alias($alias, '\Creolab\Alert\AlertFacade');
			});
		}

		// Include various files
		require __DIR__ . '/../../helpers.php';
	}

	/**
	 * Register the service provider.
	 * @return void
	 */
	public function register()
	{
	}

	/**
	 * Register IoC bindings
	 * @return void
	 */
	public function registerBindings()
	{
		$this->app->singleton('alert.environment', function($app)
		{
			return new Environment($app['session'], $app['config']);
		});
	}

	/**
	 * Some helpers to use in our Blade templates
	 * @return void
	 */
	public function registerBladeExtensions()
	{
		// Get instance of Blade compiler
		$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();

		// Display alert
		$blade->extend(function($value, $compiler)
		{
			$matcher = $compiler->createMatcher('alert');

			return preg_replace($matcher, '$1<?php echo alert$2; ?>', $value);
		});

		// Dump error alert
		$blade->extend(function($value, $compiler)
		{
			$matcher = $compiler->createMatcher('alert_error');

			return preg_replace($matcher, '$1<?php echo alert_error$2; ?>', $value);
		});
	}

}
