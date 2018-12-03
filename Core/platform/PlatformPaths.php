<?php
	
	namespace Core\platform;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 7:43
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/platform/paths.php
	 
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class PlatformPaths
	{
		public static function absolutePath($url)
		{
			return JPATH_ROOT . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $url), '\\/');
		}
		
	}#END CLASS