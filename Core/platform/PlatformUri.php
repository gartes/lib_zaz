<?php
	
	namespace Core\platform;


	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 6:55
	 *
	 * Origin Patch  /var/www/smska.tk/html/plugins/system/jch_optimize/jchoptimize/url.php
	 *
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class PlatformUri
	{
		
		private $oUri;
		
		/**
		 * PlatformUri constructor.
		 *
		 * @param $oUri
		 */
		public function __construct ( $uri )
		{
			$this->oUri = clone \JUri::getInstance($uri);
		}
		
		
		/**
		 *
		 * @param type $uri
		 * @return \JchPlatformUri
		 */
		public static function getInstance($uri = 'SERVER')
		{
			static $instances = array();
			
			if(!isset($instances[$uri]))
			{
				$instances[$uri] = new PlatformUri($uri);
			}
			
			return $instances[$uri];
		}
		
		
		
		
		
		/**
		 * Получить Base PACH
		 * @param type $pathonly
		 * @return type
		 */
		public static function base($pathonly = FALSE)
		{
			if ($pathonly)
			{
				return str_replace('/administrator', '', \JUri::base(TRUE));
			}
			
			return str_replace('/administrator/', '', \JUri::base());
		}#END FN
		
		
		/**
		 * Determines if file is internal
		 *
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
		
		
		public function toString(array $parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
		{
			return $this->oUri->toString($parts);
		}
		
		public static function requiresHttpProtocol($sUrl)
		{
			return preg_match('#\.php|^(?![^?\#]*\.(?:css|js|png|jpe?g|gif|bmp)(?:[?\#]|$)).++#i', $sUrl);
		}
		
		public function getPath()
		{
			return $this->oUri->getPath();
		}
		
		public function getScheme()
		{
			return $this->oUri->getScheme();
		}
		public function getHost()
		{
			return $this->oUri->getHost();
		}
		public function getQuery()
		{
			return $this->oUri->getQuery();
		}
		
	}#END CLASS