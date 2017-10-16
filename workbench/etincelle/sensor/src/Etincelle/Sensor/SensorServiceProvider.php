<?php namespace Etincelle\Sensor;

use Illuminate\Support\ServiceProvider;

class SensorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;
    public function boot()
    {
        $this->package('etincelle/sensor');

        include __DIR__.'/../../routes.php';
    }
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
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
