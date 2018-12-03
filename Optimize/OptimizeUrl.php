<?php
	
	namespace Optimize;
	
	use Core\platform\PlatformUri;
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 10:27
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class OptimizeUrl
	{
		/**
		 * Determines if file is internal
		 * Определяет, является ли файл внутренним
		 * @param string $sUrl  Url of file
		 * @return boolean
		 */
		public static function isInternal($sUrl)
		{
			
			$oUrl = clone PlatformUri::getInstance($sUrl);
			
			if (self::isProtocolRelative($sUrl))
			{
				$sUrl = self::toAbsolute($sUrl);
			}
			
			$sUrlBase = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));
			$sUrlHost = $oUrl->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			
			$sBase = PlatformUri::base();
			
			if (stripos($sUrlBase, $sBase) !== 0 && !empty($sUrlHost))
			{
				return FALSE;
			}
			
			return TRUE;
		}
		public static function isProtocolRelative($sUrl)
		{
			return preg_match('#^//#', $sUrl);
		}
		public static function isHttpScheme($sUrl)
		{
			return !preg_match('#^(?!https?)[^:/]+:#i', $sUrl);
		}
		public static function toRootRelative($sUrl, $sCurFile = '')
		{
			if(self::isPathRelative($sUrl))
			{
				$sUrl = (empty($sCurFile) ? '' : dirname($sCurFile) . '/' ) . $sUrl;
			}
			
			$sUrl = PlatformUri::getInstance($sUrl)->toString(array('path', 'query', 'fragment'));
			
			if(self::isPathRelative($sUrl))
			{
				$sUrl = rtrim(PlatformUri::base(TRUE), '\\/') . '/' . $sUrl;
			}
			
			return $sUrl;
		}
		public static function isPathRelative($sUrl)
		{
			return self::isHttpScheme($sUrl)
				&& !self::isAbsolute($sUrl)
				&& !self::isProtocolRelative($sUrl)
				&& !self::isRootRelative($sUrl);
		}
		public static function isAbsolute($sUrl)
		{
			return preg_match('#^http#i', $sUrl);
		}
		public static function isRootRelative($sUrl)
		{
			return preg_match('#^/[^/]#', $sUrl);
		}
	}#END CLASS