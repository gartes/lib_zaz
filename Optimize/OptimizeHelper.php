<?php
	
	namespace Optimize;
	
	use Core\platform\PlatformPaths;
	use Core\platform\PlatformUri;
	
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 7:14
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/jchoptimize/helper.php
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	
	
	
	class OptimizeHelper
	{
		/**
		 * Get local path of file from the url if internal
		 * If external or php file, the url is returned
		 *
		 * @param $sUrl
		 *
		 * @return mixed|string|\type
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public static function getFilePath($sUrl)
		{
			
			$sUriPath = PlatformUri::base(TRUE);
			
			
			
			$oUri = clone PlatformUri::getInstance();
			$oUrl = clone PlatformUri::getInstance(html_entity_decode($sUrl));
			
			
			
			//Use absolute file path if file is internal and a static file
			if (PlatformUri::isInternal($sUrl) && !PlatformUri::requiresHttpProtocol($sUrl))
			{
				return PlatformPaths::absolutePath(preg_replace('#^' . preg_quote($sUriPath, '#') . '#', '', $oUrl->getPath()));
			}
			else
			{
				$scheme = $oUrl->getScheme();
				
				if (empty($scheme))
				{
					$oUrl->setScheme($oUri->getScheme());
				}
				
				$host = $oUrl->getHost();
				
				if (empty($host))
				{
					$oUrl->setHost($oUri->getHost());
				}
				
				$path = $oUrl->getPath();
				
				if (!empty($path))
				{
					if (substr($path, 0, 1) != '/')
					{
						$oUrl->setPath($sUriPath . '/' . $path);
					}
				}
				
				$sUrl = $oUrl->toString();
				
				$query = $oUrl->getQuery();
				
				if (!empty($query))
				{
					parse_str($query, $args);
					
					$sUrl = str_replace($query, http_build_query($args, '', '&'), $sUrl);
				}
				
				return $sUrl;
			}
		}#END FN
		
		/**
		 * CDN/домен без куки
		 *
		 *
		 * @param      $params
		 * @param      $path
		 * @param      $orig_path
		 * @param bool $domains_only
		 * @param bool $reset
		 *
		 * @return array|bool|mixed
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public static function cookieLessDomain ( $params, $path, $orig_path, $domains_only = false, $reset = false )
		{
			
			$params = \Core\extensions\zazExtensions::getParamsPlugin('system' , 'starcache' );
			
			
			
			
			//If feature disabled just return the path if present
			if ( !$params->get( 'pro_cookielessdomain_enable', '0' ) && !$domains_only )
			{
				return $domains_only ? [] : $orig_path;
			}
			
			//Cache processed files to ensure the same file isn't placed on a different domain
			//if it occurs on the page twice
			static $aDomain = [];
			static $aFilePaths = [];
			
			//reset $aFilePaths for unit testing
			if ( $reset )
			{
				foreach ( $aFilePaths as $key => $value )
				{
					unset( $aFilePaths[ $key ] );
				}
				
				foreach ( $aDomain as $key => $value )
				{
					unset( $aDomain[ $key ] );
				}
				
				return false;
			}
			
			if ( empty( $aDomain ) )
			{
				switch ( $params->get( 'pro_cdn_scheme', '0' ) )
				{
					case '1':
						$scheme = 'http:';
						break;
					case '2':
						$scheme = 'https:';
						break;
					case '0':
					default:
						$scheme = '';
						break;
				}
				
				$aDefaultFiles = self::getStaticFiles();
				
				if ( trim( $params->get( 'pro_cookielessdomain', '' ) ) != '' )
				{
					$domain1      = $params->get( 'pro_cookielessdomain' );
					$staticfiles1 = implode( '|', $params->get( 'pro_staticfiles', $aDefaultFiles ) );
					
					$aDomain[ $scheme . self::prepareDomain( $domain1 ) ] = $staticfiles1;
				}
				
				if ( trim( $params->get( 'pro_cookielessdomain_2', '' ) ) != '' )
				{
					$domain2      = $params->get( 'pro_cookielessdomain_2' );
					$staticfiles2 = implode( '|', $params->get( 'pro_staticfiles_2', $aDefaultFiles ) );
					
					$aDomain[ $scheme . self::prepareDomain( $domain2 ) ] = $staticfiles2;
				}
				
				if ( trim( $params->get( 'pro_cookielessdomain_3', '' ) ) != '' )
				{
					$domain3      = $params->get( 'pro_cookielessdomain_3' );
					$staticfiles3 = implode( '|', $params->get( 'pro_staticfiles_3', $aDefaultFiles ) );
					
					$aDomain[ $scheme . self::prepareDomain( $domain3 ) ] = $staticfiles3;
				}
			}
			
			//Sprite Generator needs this to remove CDN domains from images to create sprite
			if ( $domains_only )
			{
				return $aDomain;
			}
			
			//if no domain is configured abort
			if ( empty( $aDomain ) )
			{
				return parent::cookieLessDomain( $params, $path, $orig_path );
			}
			
			//If we haven't matched a cdn domain to this file yet then find one.
			if ( !isset( $aFilePaths[ $path ] ) )
			{
				$aFilePaths[ $path ] = self::selectDomain( $aDomain, $path );
			}
			
			if ( $aFilePaths[ $path ] === false )
			{
				return $orig_path;
			}
			
			return $aFilePaths[ $path ];
		}
		
		
	}#END CLASS