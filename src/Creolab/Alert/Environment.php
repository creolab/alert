<?php namespace Creolab\Alert;

use Illuminate\Support\MessageBag;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository as Config;

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
	 * Alert bag
	 * @var Illuminate\Support\MessageBag
	 */
	protected $bag;

	/**
	 * Session object
	 * @var Illuminate\Session\SessionManager
	 */
	protected $session;

	/**
	 * Config object
	 * @var Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Initialize alert environment
	 * @param Application $app
	 */
	public function __construct(SessionManager $session, Config $config)
	{
		$this->session = $session;
		$this->config = $config;

		// Get all notifications from the flash input
		$this->getExisting();
	}

	/**
	 * Return all collections
	 * @return array
	 */
	public function bag()
	{
		return $this->bag;
	}

	/**
	 * Add new alert to specific collection
	 * @param string $message
	 * @param string $type
	 */
	public function add($message, $type = 'info', $flash = true)
	{
		$this->bag->add($type, $message);

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
	 * Add new success alert
	 * @param string $message
	 */
	public function addSuccess($message)
	{
		return $this->add($message, 'success');
	}

	/**
	 * Add new warning alert
	 * @param string $message
	 */
	public function addWarning($message)
	{
		return $this->add($message, 'warning');
	}

	/**
	 * Show specific alert type
	 * @param  string $type
	 * @return string
	 */
	public function render($type = null)
	{
		if ($this->bag->any())
		{
			$output = '';

			foreach ($this->bag->getMessages() as $type => $messages)
			{
				// Class for opening tag
				$class = ($type == 'error') ? 'danger' : $type;

				// Start collection
				$output  .= $this->config->get('alert::collection_open', '<div class="alert alert-' . $class . '">') . PHP_EOL;

				foreach ($messages as $message)
				{
					// Prepare output
					$output .= $this->config->get('alert::alert_open', '<p>');
					$output .= $message;
					$output .= $this->config->get('alert::alert_close', '</p>');
				}

				// Close it
				$output .= $this->config->get('alert::collection_close', '</div>') . PHP_EOL;
			}

			return $output;
		}
	}

	/**
	 * Get all alert from session flash
	 * @return void
	 */
	public function getExisting()
	{
		// Create message bag
		if ( ! $this->bag) $this->bag = new MessageBag();

		// Get messges from flash
		$flash = $this->session->get(self::SESSION_KEY);

		if ($flash)
		{
			foreach ($flash as $type => $alerts)
			{
				if (is_array($alerts) and count($alerts))
				{
					foreach ($alerts as $alert)
					{
						$this->bag->add($type, $alert);
					}
				}
			}
		}

		foreach (array('alert', 'alert_success', 'alert_error', 'alert_warning') as $key)
		{
			if ($message = $this->session->get($key))
			{
				// Get type
				$type = trim(str_replace('alert', '', $key), '_');
				if ( ! $type) $type = 'info';

				// Add the message
				$this->bag->add($type, $message);

				// And remove from flash
				$this->session->forget($key);
			}
		}
	}

	/**
	 * Write all alerts to session flash
	 */
	public function setFlash()
	{
		$flash = array();

		foreach ($this->bag->getMessages() as $type => $messages)
		{
			foreach ($messages as $message)
			{
				$flash[$type][] = $message;
			}
		}

		$this->session->flash(self::SESSION_KEY, $flash);
	}

}
