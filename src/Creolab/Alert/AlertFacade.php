<?php namespace Creolab\Alert;

use Illuminate\Support\Facades\Facade;

class AlertFacade extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'alert.environment';
	}

}
