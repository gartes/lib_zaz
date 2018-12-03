<?php
	
	namespace Core\platform;
	
	use JHttpFactory;
	use JRegistry;
	use JUri;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 8:40
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/platform/http.php
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class PlatformHttp
	{
		public function __construct($aDrivers)
		{
			jimport('joomla.http.factory');
			
			if (!class_exists('JHttpFactory'))
			{
				throw new BadFunctionCallException(
					PlatformUtility::translate('JHttpFactory not present. Please upgrade your version of Joomla. Exiting plugin...')
				);
			}
			
			$aOptions = array();
			
			if(empty(ini_get('open_basedir')))
			{
				$aOptions['follow_location'] = true;
			}
			
			$oOptions = new JRegistry($aOptions);
			
			$this->oHttpAdapter = JHttpFactory::getAvailableDriver($oOptions, $aDrivers);
		}#END FN
		
		
		public function request($sPath, $aPost = null, $aHeaders = null, $sUserAgent='', $timeout=5)
		{
			if (!$this->oHttpAdapter)
			{
				throw new BadFunctionCallException(JchPlatformUtility::translate('No Http Adapter present'));
			}
			
			$oUri = JUri::getInstance($sPath);
			
			$method = !isset($aPost) ? 'GET' : 'POST';
			
			$oResponse = $this->oHttpAdapter->request($method, $oUri, $aPost, $aHeaders, $timeout, $sUserAgent);
			
			
			$return = array('body' => $oResponse->body, 'code' => $oResponse->code);
			
			return $return;
		}#END FN
	}#END CLASS