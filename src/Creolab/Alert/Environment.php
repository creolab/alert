<?php namespace Creolab\Alert;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;

/**
 * The environment for the alerts
 * @author Boris Strahija <bstrahija@gmail.com>
 */
class Environment {

	/**
	 * Key that is used to store the flash data
	 */
	const SESSION_KEY = 'creolab_alerts';

	/**
	 * All collections
	 * @var array
	 */
	protected $collections = array();

	/**
	 * IoC container
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Initialize alert environment
	 * @param Application $app [description]
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;

		// Get all notifications from the flash input
		$this->getFlash();
	}

	/**
	 * Get a collection of alerts
	 * @param  string $name
	 * @return AlertCollection
	 */
	public function collection($name)
	{
		if ($this->collectionExists($name))
		{
			return $this->collections[$name];
		}
	}

	/**
	 * Return all collections
	 * @return array
	 */
	public function collections()
	{
		return $this->collections;
	}

	/**
	 * Create a new alert collections
	 * @param  string $name
	 * @return AlertCollections
	 */
	public function createCollection($name)
	{
		return $this->collections[$name] = new AlertCollection($this->app, $name);
	}

	/**
	 * Check if a collection exists
	 * @param  string $name
	 * @return boolean
	 */
	public function collectionExists($name)
	{
		return isset($this->collections[$name]);
	}

	/**
	 * Add new alert to specific collection
	 * @param string $message
	 * @param string $type
	 */
	public function add($message, $type = 'info', $flash = true)
	{
		// Create collection if needed
		if ( ! $this->collection($type))
		{
			$this->createCollection($type);
		}

		// And now add new alert item
		$this->collection($type)->add($message);

		// And write to flash
		if ($flash) $this->setFlash();
	}

	/**
	 * Add new error alert
	 * @param string $message
	 */
	public function addError($message)
	{
		return $this->add($message, 'error');
	}

	/**
	 * Show specific alert type
	 * @param  string $type
	 * @return string
	 */
	function render($type = null)
	{
		if ($type and $this->collectionExists($type))
		{
			return $this->collection($type)->render();
		}
		elseif ( ! $type)
		{
			$all = '';

			foreach ($this->collections as $collection)
			{
				$all .= $collection->render();
			}

			return $all;
		}

		// Cleanup
		$this->collections = array();
		$this->app['session']->forget(self::SESSION_KEY);
	}

	/**
	 * Get all alert from session flash
	 * @return void
	 */
	public function getFlash()
	{
		$flash = $this->app['session']->get(self::SESSION_KEY);

		if ($flash)
		{
			foreach ($flash as $type => $alerts)
			{
				if (count($alerts))
				{
					foreach ($alerts as $alert)
					{
						$this->add($alert['message'], $type, false);
					}
				}
			}
		}
	}

	/**
	 * Write all alerts to session flash
	 */
	public function setFlash()
	{
		$flash = array();

		foreach ($this->collections as $type => $collection)
		{
			$flash[$type] = $collection->toArray();
		}

		$this->app['session']->flash(self::SESSION_KEY, $flash);
	}

}
