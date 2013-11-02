<?php namespace Creolab\Alert;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;

/**
 * Collection of alert items
 * @author Boris Strahija <bstrahija@gmail.com>
 */
class AlertCollection extends Collection {

	/**
	 * Type of the collection
	 * @var string
	 */
	protected $type = 'info';

	/**
	 * IoC container
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Initialize a new collection
	 * @param Application $app
	 * @param string      $type
	 */
	public function __construct(Application $app, $type = null)
	{
		$this->app = $app;

		if ($type) $this->type = $type;
	}

	public function add($message)
	{
		return $this->items[] = new Alert($this->app, $message);
	}

	/**
	 * Render all alerts in collection
	 * @return string
	 */
	public function render()
	{
		if ($this->count())
		{
			$messages = array();

			foreach ($this->items as $item)
			{
				$messages[] = $item->render();
			}

			// Class for opening tag
			$class = ($this->type == 'error') ? 'danger' : $this->type;

			// Prepare output
			$output  = $this->app['config']->get('alert::collection_open', '<div class="alert alert-' . $class . '">') . PHP_EOL;
			$output .= implode(PHP_EOL, $messages) . PHP_EOL;
			$output .= $this->app['config']->get('alert::collection_close', '</div>') . PHP_EOL;

			return $output;
		}
	}

	/**
	 * Return array representation of collection
	 * @return array
	 */
	public function toArray()
	{
		$alerts = array();

		foreach ($this->items as $item)
		{
			$alerts[] = $item->toArray();
		}

		return $alerts;
	}

}
