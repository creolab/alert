<?php

if ( ! function_exists('alerts'))
{
	function alerts($type = 'info')
	{
		return app('alert.environment')->render($type);
	}
}

if ( ! function_exists('all_alerts'))
{
	function all_alerts()
	{
		return app('alert.environment')->render();
	}
}
