<?php namespace Creolab\Alert;

use Illuminate\Foundation\Application;

/**
 * Single alert item
 * @author Boris Strahija <bstrahija@gmail.com>
 */
class Alert {

	/**
	 * Message for alert
	 * @var string
	 */
	protected $message;

	/**
	 * IoC container
	 * @var Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Initialize new alert
	 * @param Application $app
	 * @param string      $message
	 */
	public function __construct($app, $message)
	{
		$this->app     = $app;
		$this->message = $message;
	}

	/**
	 * Show the alert message
	 * @return string
	 */
	public function message()
	{
		return $this->message;
	}

	/**
	 * Render alert template
	 * @return string
	 */
	public function render()
	{
		// Prepare output
		$output  = $this->app['config']->get('alert::alert_open', '<p>');
		$output .= $this->message();
		$output .= $this->app['config']->get('alert::alert_close', '</p>');

		return $output;
	}

	/**
	 * Return array representation of alert
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'message'  => $this->message,
			'rendered' => $this->render(),
		);
	}

}
