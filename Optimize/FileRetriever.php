<?php
	
	namespace Optimize;
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 8:35
	 */
	
	
	
	
	 
	
	class FileRetriever
	{
		protected static $instances = array();
		protected $oHttpAdapter = Null;
		
		/**
		 * @param        $sPath
		 * @param null   $aPost
		 * @param array  $aHeader
		 * @param string $sOrigPath
		 * @param int    $timeout
		 *
		 * @return false|string
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public function getFileContents($sPath, $aPost = null, $aHeader = array(), $sOrigPath = '', $timeout=7)
		{
			 
			
			
			//We need to use an http adapter if it's a remote or dynamic file
			if (strpos($sPath, 'http') === 0)
			{
				//Initialize response code
				$this->response_code = 0;
				
				try
				{
					# TODO Вызывает ошибку если сервер со шрфотм не доступен
					$sUserAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
					$aHeader = array_merge($aHeader, array('Accept-Encoding' => 'identity, deflate, *;q=0'));
					$response = $this->oHttpAdapter->request($sPath, $aPost, $aHeader, $sUserAgent, $timeout);
					$this->response_code = $response['code'];
					
					
					
					
					if (!isset($response) || $response === FALSE)
					{
						
						  throw new RuntimeException(sprintf('Failed getting file contents from %s', $sPath));
					}
				}
				catch (RuntimeException $ex)
				{
					
					 
					//Record error message
					$this->response_error = $ex->getMessage();
				}
				catch (Exception $ex)
				{
					throw new Exception($sPath . ': ' . $ex->getMessage());
				}
				
				if ($this->response_code != 200 && !$this->allow_400)
				{
					//Most likely a RuntimeException has occurred here in that case we want the error message
					if($this->response_code === 0 && $this->response_error !== '')
					{
						$sContents = '|"COMMENT_START ' . $this->response_error . ' COMMENT_END"|';
					}
					else
					{
						$sPath     = $sOrigPath == '' ? $sPath : $sOrigPath;
						$sContents = $this->notFound($sPath);
					}
				}
				else
				{
					$sContents = $response['body'];
				}
			}
			else
			{
				if (file_exists($sPath))
				{
					$sContents = @file_get_contents($sPath);
				}
				elseif ($this->oHttpAdapter->available())
				{
					$sUriPath = JchPlatformPaths::path2Url($sPath);
					
					$sContents = $this->getFileContents($sUriPath, null, array(), $sPath);
				}
				else
				{
					$sContents = $this->notFound($sPath);
				}
			}
			
			return $sContents;
		}
		
		
		/**
		 * @param array $aDrivers
		 *
		 * @return mixed
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public static function getInstance($aDrivers = array('curl', 'stream', 'socket'))
		{
			$hash = serialize($aDrivers);
			
			if (empty(static::$instances[$hash]))
			{
				static::$instances[$hash] = new FileRetriever($aDrivers);
			}
			
			return static::$instances[$hash];
		}#END FN
		
		/**
		 * FileRetriever constructor.
		 *
		 * @param $aDrivers
		 */
		private function __construct($aDrivers)
		{
			$this->oHttpAdapter = new \Core\platform\PlatformHttp($aDrivers);
		}#END FN
		
	}#END CLASS