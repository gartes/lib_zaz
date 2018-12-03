<?php
	namespace Optimize;
	
	use Core\platform\PlatformUtility;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 11:29
	 */
	
	
	
	
	 
	
	class OptimizeLogger
	{
		/**
		 *
		 * @param type $sMessage
		 * @param JchPlatformSettings $params
		 * @param type $sCategory
		 */
		public static function log($sMessage, JchPlatformSettings $params)
		{
			
			JCH_DEBUG ? PlatformUtility::log($sMessage, 'ERROR', 'plg_jch_optimize.errors.php') : null;
		}
		
		/**
		 *
		 * @param type $variable
		 * @param type $name
		 */
		public static function debug($variable, $name='')
		{
			$sMessage = $name != '' ? "$name = '" . $variable . "'" : $variable;
			
			PlatformUtility::log($sMessage, 'DEBUG', 'plg_jch_optimize.debug.php');
		}
		
		/**
		 *
		 * @param type $sMessage
		 */
		public static function logInfo($sMessage)
		{
			PlatformUtility::log($sMessage, 'INFO', 'plg_jch_optimize.logs.php');
		}
		
	}#END CLASS