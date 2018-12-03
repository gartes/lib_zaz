<?php namespace Optimize;

	use Core\platform\PlatformUtility;
	use Optimize\css\cssParser;
	use Optimize\OptimizeHelper;
	
	
	
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 6:16
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/jchoptimize/combiner.php
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class combiner
	{
		public $params            = NULL;
		protected $oParser;
		public $bBackend          = FALSE;
		public static $bLogErrors = FALSE;
		
		public $oCssParser;
		public $css               = '';
		public $js                = '';
		
		
		/**
		 * combiner constructor.
		 *
		 * @param bool $params
		 * @param      $oParser
		 * @param bool $bBackend
		 */
		public function __construct( $params = FALSE , $oParser= FALSE  , $bBackend = FALSE)
		{
			$this->params   = $params;
			$this->oParser  = $oParser;
			$this->bBackend = $bBackend;
			
			
			
			$this->sLnEnd = PlatformUtility::lnEnd();
			$this->sTab   = PlatformUtility::tab();
			$params = \Core\extensions\zazExtensions::getParamsPlugin('system' , 'starcache');
			
			echo'<pre>';print_r( $params );echo'</pre>'.__FILE__.' '.__LINE__;
			
			$this->oCssParser = new cssParser($params, $bBackend);
			
			//self::$bLogErrors = $this->params->get('jsmin_log', 0) ? TRUE : FALSE;
		}
		
		
		/**
		 * Слить данные файлов
		 *
		 * @param $aUrlArray
		 * @param $sType
		 * @param $oCssParser
		 *
		 * @return string
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public function combineFiles($aUrlArray, $sType /*, $oCssParser*/)
		{
			
			$sContents = '';
			$this->bAsync    = FALSE;
			
			$oFileRetriever =   \Optimize\FileRetriever::getInstance();
			
			
			
			
			
			
			//Iterate through each file/script to optimize and combine
			foreach ($aUrlArray as $aUrl => $opt )
			{
				
				$sContent = $this->cacheContent($aUrl, $sType  , $oFileRetriever /* , $oCssParser, FALSE*/);
				
				
				//Truncate url to less than 40 characters
//				$sUrl = $this->prepareFileUrl($aUrl, $sType);
//				JCH_DEBUG ? JchPlatformProfiler::start('CombineFile - ' . $sUrl) : null;
				
				
				echo'<pre>';print_r( $aUrl );echo'</pre>'.__FILE__.' '.__LINE__;
				
				
				//If a cache id is present then cache this individual file to avoid
				//optimizing it again if it's present on another page
				/*if (isset($aUrl['id']) && $aUrl['id'] != '')
				{
					if(isset($aUrl['url']))
					{
						$this->current_file = $aUrl['url'];
					}
					
					$function = array($this, 'cacheContent');
					$args     = array($aUrl, $sType, $oFileRetriever, $oCssParser, TRUE);
					
					//Optimize and cache file/script returning the optimized content
					$sCachedContent = JchPlatformCache::getCallbackCache($aUrl['id'], $function, $args);
					
					$this->$sType .= $sCachedContent;
					
					//Append to combined contents
					$sContents .= $this->addCommentedUrl($sType, $aUrl) . $sCachedContent .
						$this->sLnEnd . 'DELIMITER';
				}
				else
				{
					//If we're not caching just get the optimized content
					$sContent = $this->cacheContent($aUrl, $sType, $oFileRetriever, $oCssParser, FALSE);
					$sContents .= $this->addCommentedUrl($sType, $aUrl) . $sContent . '|"LINE_END"|';
				}*/
				
				// JCH_DEBUG ? JchPlatformProfiler::stop('CombineFile - ' . $sUrl, TRUE) : null;
			}
			die(__FILE__ .' Lines '. __LINE__ );
			if ($this->bAsync)
			{
				$sContents = $this->getLoadScript() . $sContents;
			}
			
			return $sContents;
		}#END FN
		
		
		function cacheContent( $aUrl, $sType , $oFileRetriever /*, $oCssParser, $bPrepare */ ){
			
			
			
			$oCssParser = new cssParser();
			
			$sContent= '';
			
			
			
			
			# Convert local urls to file path
			$sPath    = OptimizeHelper::getFilePath( $aUrl );
			
			$sContent .= $oFileRetriever->getFileContents( $sPath );
			
			
			if ( $sType == 'css' )
			{
				if ( function_exists( 'mb_convert_encoding' ) )
				{
					$sEncoding = mb_detect_encoding( $sContent );
					
					if ( $sEncoding === false )
					{
						$sEncoding = mb_internal_encoding();
					}#END IF
					
					$sContent = mb_convert_encoding( $sContent, 'utf-8', $sEncoding );
				}#END IF
				
				$sImportContent = preg_replace( '#@import\s(?:url\()?[\'"]([^\'"]+)[\'"](?:\))?#', '@import url($1)', $sContent );
				
				if ( is_null( $sImportContent ) )
				{
					// JchOptimizeLogger::log( sprintf( 'There was an error when trying to find "@imports" in the CSS file: %s', $aUrl[ 'url' ] ), $this->params );
					
					$sImportContent = $sContent;
				}#END IF
				
				$sContent = $sImportContent;
				unset( $sImportContent );
				
				$sContent = $oCssParser->addRightBrace( $sContent );
				
				$oCssParser->aUrl = $aUrl;
				
				$sContent = $oCssParser->correctUrl( $sContent, $aUrl );
				$sContent = $this->replaceImports( $sContent, $aUrl );
				$sContent = $oCssParser->handleMediaQueries( $sContent, $aUrl[ 'media' ] );
			}#END IF
			
			
			
			
			echo'<pre>';print_r( $sContent );echo'</pre>'.__FILE__.' '.__LINE__;
			
		}#END FN
		
		
	}#END CLASS