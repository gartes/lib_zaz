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
			$params = \Core\extensions\zazExtensions::getParamsPlugin('system' , 'starcache');
			$this->params   = $params;
			$this->oParser  = $oParser;
			$this->bBackend = $bBackend;
			
			
			
			$this->sLnEnd = PlatformUtility::lnEnd();
			$this->sTab   = PlatformUtility::tab();
			
			
			
			
			$this->oCssParser = new cssParser($params, $bBackend);
			
			//self::$bLogErrors = $this->params->get('jsmin_log', 0) ? TRUE : FALSE;
		}
		
		/**
		 * @param $Stylesheet
		 *
		 * @return array
		 * @throws \Exception
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public function getContents ( $Stylesheet )
		{
			$oCssParser     = $this->oCssParser;
			$aSpriteCss     = [];
			$aFontFace      = [];
			$aContentsArray = [];
			
			
			 $sContents = $this->combineFiles( $Stylesheet, 'css', $oCssParser );
			 $sContents = $this->prepareContents( $sContents );
			
			
			$aContents = [
				'filemtime' => PlatformUtility::unixCurrentDate(),
				'etag'      => md5( 'css' ),
				'file'      => $sContents ,
				'spritecss' => $aSpriteCss,
			];
			
			
			return $aContents;
		}#END FN
		
		/**
		 * Remove placeholders from aggregated file for caching
		 * Удаление заполнителей из агрегированного файла для кэширования
		 *
		 *
		 * @param string $sContents       Aggregated file contents
		 * @param string $sType           js or css
		 * @return string
		 */
		public function prepareContents($sContents, $test = FALSE)
		{
			$sContents = str_replace(
				array(
					'|"COMMENT_START',
					'|"COMMENT_IMPORT_START',
					'COMMENT_END"|',
					'DELIMITER',
					'|"LINE_END"|'
				),
				array(
					$this->sLnEnd . '/***! ',
					$this->sLnEnd . $this->sLnEnd . '/***! @import url',
					' !***/' . $this->sLnEnd . $this->sLnEnd,
					($test) ? 'DELIMITER' : '',
					$this->sLnEnd
				), trim($sContents));
			
			
			return $sContents;
		}#END FN
		
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
		 * @throws \Exception
		 */
		public function combineFiles($aUrlArray, $sType  , $oCssParser )
		{
			
			$sContents = '';
			$this->bAsync    = FALSE;
			
			$oFileRetriever =   \Optimize\FileRetriever::getInstance();
			
			# Iterate through each file/script to optimize and combine
			# Итерировать через каждый файл / скрипт для оптимизации и объединения
			foreach ($aUrlArray as $aUrl => $opt )
			{
				$opt['url'] = $aUrl ;
				
				
				//Truncate url to less than 40 characters
//				$sUrl = $this->prepareFileUrl($aUrl, $sType);
//				JCH_DEBUG ? JchPlatformProfiler::start('CombineFile - ' . $sUrl) : null;
				
				# Если присутствует идентификатор кеша, кешируйте этот отдельный файл, чтобы избежать
				# оптимизируя его снова, если он присутствует на другой странице
				if (isset($aUrl['id']) && $aUrl['id'] != '')
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
					# If we're not caching just get the optimized content
					# Если мы не кэшируем, просто получаем оптимизированный контент
					
					$sContent  = $this->cacheContent( $opt , $sType  , $oFileRetriever , $oCssParser  , FALSE );
					$sContents .= $this->addCommentedUrl($sType, $aUrl) . $sContent . '|"LINE_END"|';
				}
				// JCH_DEBUG ? JchPlatformProfiler::stop('CombineFile - ' . $sUrl, TRUE) : null;
			}
			
			if ($this->bAsync)
			{
				$sContents = $this->getLoadScript() . $sContents;
			}
			
			
			
			return $sContents;
		}#END FN
		
		/**
		 * @param $aUrl
		 * @param $sType
		 * @param $oFileRetriever
		 * @param $oCssParser \Optimize\css\cssParser
		 *
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 * @return string|string[]|null
		 * @throws \Exception
		 */
		function cacheContent( $aUrl, $sType , $oFileRetriever , $oCssParser  , $bPrepare   ){
			
			 
			
			$sContent= '';
			# Convert local urls to file path
			$sPath    = OptimizeHelper::getFilePath( $aUrl['url']  );
			
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
				$sContent = $this->replaceImports( $sContent  );
				
				
				
				
				if ( isset( $aUrl['media'] ) ) {
					$sContent = $oCssParser->handleMediaQueries( $sContent, $aUrl[ 'media' ] );
				}#END IF
				
				
				
				
			}#END IF
			
			return $sContent;
			
			
			// echo'<pre>';print_r( $sContent );echo'</pre>'.__FILE__.' '.__LINE__;
			
		}#END FN
		
		/**
		 * Resolves @imports in css files, fetching contents of these files and adding them to the aggregated file
		 * Решает @imports в css-файлах, извлекает содержимое этих файлов и добавляет их в агрегированный файл
		 *
		 * @param $sContent
		 *
		 * @return string|string[]|null
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		protected function replaceImports($sContent)
		{
			if ($this->params->get('pro_replaceImports', '1'))
			{
				$oCssParser = $this->oCssParser;
				
				$u = $oCssParser->u;
				
				$sImportFileContents = preg_replace_callback(
					"#(?>@?[^@'\"/]*+(?:{$u}|/|\()?)*?\K(?:@import\s*+(?:url\()?['\"]?([^\)'\"]+)['\"]?(?:\))?\s*+([^;]*);|\K$)#",
					array($this, 'getImportFileContents'), $sContent
				);
				
				if (is_null($sImportFileContents))
				{
					OptimizeLogger::log(
						'The plugin failed to get the contents of the file that was imported into the document by the "@import" rule',
						$this->params
					);
					return $sContent;
				}
				
				$sContent = $sImportFileContents;
			}
			else
			{
				$sContent = parent::replaceImports($sContent);
			}
			
			return $sContent;
		}#END FN
		
		/**
		 * Fetches the contents of files declared with @import
		 * Выбирает содержимое файлов, объявленных с помощью @import
		 *
		 * @param array $aMatches Array of regex matches
		 *
		 * @return string               file contents
		 * @throws \Exception
		 */
		protected function getImportFileContents($aMatches)
		{
			if (empty($aMatches[1])
				|| preg_match('#^(?>\(|/\*)#', $aMatches[0])
				|| !$this->oParser->isHttpAdapterAvailable($aMatches[1])
				|| (JchOptimizeUrl::isSSL($aMatches[1]) && !extension_loaded('openssl'))
				|| (!JchOptimizeUrl::isHttpScheme($aMatches[1]))
			)
			{
				return $aMatches[0];
			}
			
			if($this->oParser->isDuplicated($aMatches[1]))
			{
				return '';
			}
			
			//Need to handle file specially if it imports google font
			if (strpos($aMatches[1], 'fonts.googleapis.com') !== FALSE)
			{
				//Get array of files from cache that imports Google font files
				$containsgf = JchPlatformCache::getCache('jch_hidden_containsgf');
				
				//If not cache found initialize to empty array
				if($containsgf === false)
				{
					$containsgf = array();
				}
				
				//If not in array, add to array
				if (!in_array($this->current_file, $containsgf))
				{
					$containsgf[] = $this->current_file;
					
					//Store array of filenames that imports google font files to cache
					JchPlatformCache::saveCache($containsgf, 'jch_hidden_containsgf');
				}
			}
			
			$aUrlArray = array();
			
			$aUrlArray[0]['url']   = $aMatches[1];
			$aUrlArray[0]['media'] = $aMatches[2];
			//$aUrlArray[0]['id']    = md5($aUrlArray[0]['url'] . $this->oParser->sFileHash);
			
			$oCssParser    = $this->oCssParser;
			$sFileContents = $this->combineFiles($aUrlArray, 'css', $oCssParser);
			
			if ($sFileContents === FALSE)
			{
				return $aMatches[0];
			}
			
			return $sFileContents;
		}#END FN
		
		
		protected function addCommentedUrl($sType, $sUrl)
		{
			$sComment = '';
			
			if ($this->params->get('debug', '1'))
			{
				if (is_array($sUrl))
				{
					$sUrl = isset($sUrl['url']) ? $sUrl['url'] : (($sType == 'js' ? 'script' : 'style') . ' declaration');
				}
				
				$sComment = '|"COMMENT_START ' . $sUrl . ' COMMENT_END"|';
			}
			
			return $sComment;
		}
		
	}#END CLASS