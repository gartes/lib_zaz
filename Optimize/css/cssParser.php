<?php
	namespace Optimize\css;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 4:19
	 *
	 * Orign Patch /var/www/smska.tk/html/plugins/system/jch_optimize/jchoptimize/cssparser.php
	 *
	 */
	
	
	use Core\platform\PlatformUri;
	use Core\platform\PlatformUtility;
	use DOMDocument;
	use DOMXPath;
	use Optimize\OptimizeEmbed;
	use Joomla\Registry\Registry;
	use Optimize\OptimizeHelper;
	use Optimize\OptimizeUrl;
	
	
	class cssParser extends OptimizeEmbed
	{
		protected $bBackend = false;
		public $e = '';
		public $u = '';
		
		/**
		 * cssOptimize constructor.
		 */
		public function __construct ( $params = null, $bBackend = false )
		{
			parent::__construct(   );
			
			
			$this->sLnEnd = is_null( $params ) ? "\n" : PlatformUtility::lnEnd();
			
			$params = \Core\extensions\zazExtensions::getParamsPlugin('system' , 'starcache' ) ;
			$this->params = $params;
			
			$this->bBackend = $bBackend;
			$e              = self::DOUBLE_QUOTE_STRING . '|' . self::SINGLE_QUOTE_STRING . '|' . self::BLOCK_COMMENTS . '|'
				. self::LINE_COMMENTS;
			$this->e        = "(?<!\\\\)(?:$e)|[\'\"/]";
			$this->u        = '(?<!\\\\)(?:' . self::URI . '|' . $e . ')|[\'\"/(]';
		}#END FN
		
		
		public function addRightBrace ( $sCss )
		{
			$sRCss = '';
			$r     = "#(?>[^{}'\"/(]*+(?:{$this->u})?)+?(?:(?<b>{(?>[^{}'\"/(]++|{$this->u}|(?&b))*+})|$)#";
			preg_replace_callback( "#(?>[^{}'\"/(]*+(?:{$this->u})?)+?(?:(?<b>{(?>[^{}'\"/(]++|{$this->u}|(?&b))*+})|(?=}}$))#",
				function ( $m ) use ( &$sRCss ) {
					$sRCss .= $m[ 0 ];
					
					return;
				}, rtrim( $sCss ) . '}}' );
			
			return $sRCss;
		}#END FN
		
		/**
		 * Converts url of background images in css files to absolute path
		 *  Преобразует URL-адрес фоновых изображений в css-файлах в абсолютный путь
		 *
		 * @param $sContent
		 * @param $aUrl
		 *
		 * @return string|string[]|null
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 * @throws \Exception
		 */
		public function correctUrl ( $sContent, $aUrl )
		{
			$obj = $this;
			
			$sCorrectedContent = preg_replace_callback(
				"#(?>[(]?[^('/\"]*+(?:{$this->e}|/)?)*?(?:(?<=url)\(\s*+\K['\"]?((?<!['\"])[^)]*+|(?<!')[^\"]*+|[^']*+)['\"]?|\K$)#i",
				function ( $aMatches ) use ( $aUrl, $obj ) {
					return $obj->_correctUrlCB( $aMatches, $aUrl );
				}, $sContent );
			
			if ( is_null( $sCorrectedContent ) )
			{
				throw new \Exception( 'The plugin failed to correct the url of the background images' );
			}
			
			$sContent = $sCorrectedContent;
			
			return $sContent;
		}#END FN
		
		
		/**
		 * Callback function to correct urls in aggregated css files
		 * Функция обратного вызова для исправления URL-адресов в агрегированных файлах css
		 *
		 * @param array $aMatches Array of all matches
		 *
		 * @return string         Correct url of images from aggregated css file
		 */
		public function _correctUrlCB ( $aMatches, $aUrl )
		{
			if ( empty( $aMatches[ 1 ] ) || $aMatches[ 1 ] == '/' || preg_match( '#^(?:\(|/\*)#', $aMatches[ 0 ] ) )
			{
				return $aMatches[ 0 ];
			}
			
			$sImageUrl   = $aMatches[ 1 ];
			$sCssFileUrl = empty( $aUrl[ 'url' ] ) ? '' : $aUrl[ 'url' ];
			
			
			
			if ( OptimizeUrl::isHttpScheme( $sImageUrl ) )
			{
				if ( ( OptimizeUrl::isInternal( $sCssFileUrl ) || $sCssFileUrl == '' ) && OptimizeUrl::isInternal( $sImageUrl ) )
				{
					$sImageUrl = OptimizeUrl::toRootRelative( $sImageUrl, $sCssFileUrl );
					
					$oImageUri = clone PlatformUri::getInstance( $sImageUrl );
					
					$aFontFiles = $this->fontFiles();
					$sFontFiles = implode( '|', $aFontFiles );
					
					$sImageUrl = OptimizeHelper::cookieLessDomain( $this->params, $oImageUri->toString( [ 'path' ] ), $sImageUrl );
					
					
					
					if ( $this->params->get( 'pro_cookielessdomain_enable', '0' )
						&& preg_match( '#\.(?>' . $sFontFiles . ')#', $oImageUri->getPath() ) )
					{
						
						$oUri = clone \Core\platform\PlatformUri::getInstance();
						
						$sImageUrl = '//' . $oUri->toString( [ 'host', 'port' ] ) .
							$oImageUri->toString( [ 'path', 'query', 'fragment' ] );
					}
				}
				else
				{
					if ( !OptimizeUrl::isAbsolute( $sImageUrl ) )
					{
						$sImageUrl = OptimizeUrl::toAbsolute( $sImageUrl, $sCssFileUrl );
					}
					else
					{
						return $aMatches[ 0 ];
					}
				}
				
				$sImageUrl = preg_match( '#(?<!\\\\)[\s\'"(),]#', $sImageUrl ) ? '"' . $sImageUrl . '"' : $sImageUrl;
				
				return $sImageUrl;
				
			}
			else
			{
				return $aMatches[ 0 ];
			}
			
		}#END FN
		
		public static function fontFiles ()
		{
			$arr = [
				'woff',
				'ttf',
				'otf',
				'eot',
			];
			
			return $arr;
		}
		
		/**
		 * Обработка <link rel="stylesheet" media="screen and (max-device-width: [XXXX]px)" .... />
		 *
		 * @param        $sContent
		 * @param string $sParentMedia
		 *
		 * @return string|string[]|null
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public function handleMediaQueries ( $sContent, $sParentMedia = '' )
		{
			if ( $this->bBackend )
			{
				return $sContent;
			}
			
			if ( isset( $sParentMedia ) && ( $sParentMedia != '' ) )
			{
				$obj = $this;
				
				$sContent = preg_replace_callback(
					"#(?>@?[^@'\"/(]*+(?:{$this->u})?)*?\K(?:@media ([^{]*+)|\K$)#i",
					function ( $aMatches ) use ( $sParentMedia, $obj ) {
						return $obj->_mediaFeaturesCB( $aMatches, $sParentMedia );
					}, $sContent
				);
				
				$a = $this->nestedAtRulesRegex();
				
				$sContent = preg_replace(
					"#(?>(?:\|\"[^|]++(?<=\")\||$a)\s*+)*\K"
					. "(?>(?:$this->u|/|\(|@(?![^{};]++(?1)))?(?:[^|@'\"/(]*+|$))*+#i",
					'@media ' . $sParentMedia . ' {' . $this->sLnEnd . '$0' . $this->sLnEnd . '}', trim( $sContent )
				);
				
				$sContent = preg_replace( "#(?>@?[^@'\"/(]*+(?:{$this->u})?)*?\K(?:@media[^{]*+{((?>\s*+|$this->e)++)}|$)#i", '$1', $sContent );
			}
			
			return $sContent;
		}#END FN
		
		
		public function _mediaFeaturesCB ( $aMatches, $sParentMedia )
		{
			if ( !isset( $aMatches[ 1 ] ) || $aMatches[ 1 ] == '' || preg_match( '#^(?>\(|/(?>/|\*))#', $aMatches[ 0 ] ) )
			{
				return $aMatches[ 0 ];
			}
			return '@media ' . $this->combineMediaQueries( $sParentMedia, trim( $aMatches[ 1 ] ) );
		}#END FN
		
		public static function nestedAtRulesRegex ()
		{
			return '@[^{};]++({(?>[^{}]++|(?1))*+})';
		}#END FN
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		public $hXhtml = '

<header class="header" role="banner">
	<div class="header-inner clearfix">
		<a class="brand pull-left" href="/">
			<span class="site-title" title="smska">smska</span>
		</a>
		<div class="header-search pull-right">
			<div class="mod-languages">
				<ul class="lang-inline">
					<li class="lang-active" dir="ltr">
						<a href="/magazin">
							<img src="/media/mod_languages/images/ru_ru.gif" alt="Russian (Russia)" title="Russian (Russia)">
						</a>
					</li>
					<li dir="ltr">
						<a href="/uk/magazin-3">
							<img src="/media/mod_languages/images/uk_ua.gif" alt="Українська (Україна)" title="Українська (Україна)">
						</a>
					</li>
					<li dir="ltr">
						<a href="/en/magazin-2">
							<img src="/media/mod_languages/images/en_gb.gif" alt="English (United Kingdom)" title="English (United Kingdom)">
						</a>
					</li>
					<li dir="ltr">
						<a href="/en/magazin-XXX">
							<img src="/media/mod_languages/images/en_gb.gif" alt="English (United Kingdom)" title="English (United Kingdom)">
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</header>';
		
		
		
		public static function cssRulesRegex ()
		{
			$c = self::BLOCK_COMMENTS . '|' . self::LINE_COMMENTS;
			
			$r = "(?:\s*+(?>$c)\s*+)*+\K"
				. "((?>[^{}@/]*+(?:/(?![*/])|(?<=\\\\)[{}@/])?)*?)(?>{[^{}]*+}|(@[^{};]*+)(?>({((?>[^{}]++|(?3))*+)})|;?)|$)";
			
			return $r;
		}
		
		/**
		 * Отбор критических стилей
		 *
		 *
		 * @param $sContents
		 * @param $sHtml
		 *
		 * @return array
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		public function optimizeCssDelivery ( $sContents, $sHtml )
		{
//
			// echo'<pre>';print_r( $sHtml  );echo'</pre>'.__FILE__.' '.__LINE__;
			/// TEMP
			// $sHtml = 	$this->hXhtml ;


//		echo'<pre>';print_r( $sHtml );echo'</pre>'.__FILE__.' '.__LINE__;
//			echo'<pre>';print_r( $sContents );echo'</pre>'.__FILE__.' '.__LINE__;
			
			//JCH_DEBUG ? JchPlatformProfiler::start( 'OptimizeCssDelivery' ) : null;
			
			//$this->_debug( '', '' );
			
			$sHtmlTruncated = '';
			
			
			
			# отобрать нужное количество тегов
			preg_replace_callback(
				'#<(?:[a-z0-9]++)(?:[^>]*+)>(?><?[^<]*+)*?(?=<[a-z0-9])#i',
				function ( $aM ) use ( &$sHtmlTruncated ) {
					
					$sHtmlTruncated .= $aM[ 0 ];
					return;
				},
				$sHtml,
				
				500
//				(int) $this->params->get( 'pro_optimizeCssDelivery', '200' )
			);
			
			
			
			
			
			
			# Добавить пробелы class=" container "
			$sHtmlTruncated = preg_replace( '#\s*=\s*["\']([^"\']++)["\']#i', '=" $1 "', $sHtmlTruncated );
			
			# Удалить текст в нутри тегов
			$sHtmlTruncated = preg_replace_callback(
				'#(<(?>[^<>]++|(?1))*+>)|((?<=>)(?=[^<>\S]*+[^<>\s])[^<>]++)#',
				function ( $m ) {
					if ( isset( $m[ 1 ] ) && $m[ 1 ] != '' )
					{
						return $m[ 0 ];
					}
					
					if ( isset( $m[ 2 ] ) && $m[ 2 ] != '' )
					{
						return ' ';
					}
				},
				$sHtmlTruncated );
			
			
			
			
			
			
			# create DOMDocument Object
			$oDom = new DOMDocument();
			
			libxml_use_internal_errors( true );
			$oDom->loadHtml( $sHtmlTruncated );
			libxml_clear_errors();
			
			$oXPath = new DOMXPath( $oDom );
			
			
			$sCriticalCss = '';
			
			$obj = $this;
			
			$sContents    = preg_replace_callback(
				'#' . self::cssRulesRegex() . '#' ,
				function ( $aMatches ) use ( $obj, $oXPath, $sHtmlTruncated, &$sCriticalCss ) {
					
					return $obj->extractCriticalCss( $aMatches, $oXPath, $sHtmlTruncated, $sCriticalCss );
				},
				$sContents
			);
			
			
			
			
			
			
			// $this->_debug( '', '', 'afterExtractCriticalCss' );


//			echo'<pre>';print_r( $sCriticalCss );echo'</pre>'.__FILE__.' '.__LINE__;
			
			# Удалить пустые CSS медиа запросы
			$sCriticalCss = preg_replace( '#@media[^{]*+{[^\S}]*+}#', '', $sCriticalCss );
			//$sCriticalCss = preg_replace('#@media[^{]*+{[^\S}]*+}#', '', $sCriticalCss);
			
			$sCriticalCss = preg_replace( '#/\*\*\*!+[^!]+!\*\*\*+/[^\S]*+(?=\/\*\*\*!|$)#', '', $sCriticalCss );
			# Удалить двойные переносы строк
			$sCriticalCss = preg_replace( '#\s*[\r\n]{2,}\s*#', "\n\n", $sCriticalCss );
			
			
			$aContents = [
				'font-face'   => trim( $sContents ),
				'criticalcss' => trim( $sCriticalCss ),
			];


//			echo'<pre>';print_r( $aContents );echo'</pre>'.__FILE__.' '.__LINE__;

//			die(__FILE__ .' Lines '. __LINE__ );
			//$this->_debug( self::cssRulesRegex(), '', 'afterCleanCriticalCss' );
			// JCH_DEBUG ? JchPlatformProfiler::stop( 'OptimizeCssDelivery', true ) : null;
			
			return $aContents;
		}#END FN
		
		
		
		public function extractCriticalCss ( $aMatches, $oXPath, $sHtml, &$sCriticalCss )
		{
			$matches0 = ltrim( $aMatches[ 0 ] );
			
			
			
			
			// echo'<pre>';print_r($matches0  );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
			#add all font at-rules to the critical css
			if ( preg_match( '#^(?>@(?:-[^-]+-)?(?:font-face|import))#i', $matches0 ) )
			{
				$sCriticalCss .= $aMatches[ 0 ];
				return $aMatches[ 0 ];
			}#END IF
			
			
			
			
			
			//recurse into each @media rule
			if ( preg_match( '#^@media#', $matches0 ) )
			{
				$sCriticalCss .= $aMatches[ 2 ] . '{';
				
				$obj = $this;
				
				$sMatch = preg_replace_callback(
					'#' . self::cssRulesRegex() . '#',
					function ( $aMatches ) use ( $obj, $oXPath, $sHtml, &$sCriticalCss ) {
						return $obj->extractCriticalCss( $aMatches, $oXPath, $sHtml, $sCriticalCss );
					}, $aMatches[ 4 ] );
				
				unset( $obj );
				
				$sCriticalCss .= $this->sLnEnd . '}' . $this->sLnEnd;
				
				return $sMatch;
			}
			
			//remove all other at-rules from critical css
			if ( preg_match( '#^\s*+@(?:-[^-]+-)?(?:page|keyframes|charset|namespace)#i', $matches0 ) )
			{
				return '';
			}
			
			
			//we're inside a @media rule or global css
			//remove pseudo-selectors
			$sSelectorGroup = preg_replace( '#:not\([^)]+\)|::?[a-zA-Z0-9(\[\])-]+#', '', $aMatches[ 1 ] );
			//Split selector groups into individual selector chains
			$aSelectorChains      = array_filter( explode( ',', $sSelectorGroup ) );
			$aFoundSelectorChains = [];
			
			//Iterate through each selector chain
			foreach ( $aSelectorChains as $sSelectorChain )
			{
				//If Selector chain is already in critical css just go ahead and add this group
				if ( strpos( $sCriticalCss, $sSelectorChain ) !== false )
				{
					$sCriticalCss .= $aMatches[ 0 ];
					
					return '';
				}
				
				//Split selector chain into simple selectors
				$aSimpleSelectors = preg_split( '#[ >+]#', trim( $sSelectorChain ), -1, PREG_SPLIT_NO_EMPTY );
				
				//We'll do a quick check first if all parts of each simple selector is found in the HTML
				//Iterate through each simple selector
				foreach ( $aSimpleSelectors as $sSimpleSelector )
				{
					//Match the simple selector into its components
					if ( preg_match( '#([a-z0-9]*)(?:([.\#]((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))|
					(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(?:[~|^$*]?=["\']?([^\]"\']+))?\]))*#i',
						$sSimpleSelector, $aS ) )
					{
						//Elements
						if ( isset( $aS[ 1 ] ) && $aS[ 1 ] != '' )
						{
							$sNeedle = '<' . $aS[ 1 ];
							
							if ( isset( $sNeedle ) && strpos( $sHtml, $sNeedle ) === false )
							{
								//Element part of selector not found,
								//abort and check next selector chain
								continue 2;
							}
						}
						
						//Attribute selectors
						if ( isset( $aS[ 4 ] ) && $aS[ 4 ] != '' )
						{
							//If the value of the attribute is set we'll look for that
							//otherwise just look for the attribute
							$sNeedle = isset( $aS[ 6 ] ) ? $aS[ 6 ] : $aS[ 5 ];// . '="';
							
							if ( isset( $sNeedle ) && strpos( $sHtml, str_replace( '\\', '', $sNeedle ) ) === false )
							{
								//Attribute part of selector not found,
								//abort and check next selector chain
								continue 2;
							}
						}
						
						//Ids or Classes
						if ( isset( $aS[ 2 ] ) && $aS[ 2 ] != '' )
						{
							$sNeedle = ' ' . $aS[ 3 ] . ' ';
							
							if ( isset( $sNeedle ) && strpos( $sHtml, str_replace( '\\', '', $sNeedle ) ) === false )
							{
								//Id or class part of selector not found,
								//abort and check next selector chain
								continue 2;
							}
						}
						
					}
					
				}#END FOREACH
				
				
				
				
				//If we get to this point then we've found a simple selector that has all parts in the
				//HTML. Let's save this selector chain and refine its search with Xpath.
				$aFoundSelectorChains[] = $sSelectorChain;
			}
			
			//If no valid selector chain was found in the group then we eliminate this selector group from the critical CSS
			if ( empty( $aFoundSelectorChains ) )
			{
				//$this->_debug( '', '', 'afterSelectorNotFound' );
				
				return '';
			}
			
			//Group the found selector chains
			$sFoundSelectorGroup = implode( ',', array_unique( $aFoundSelectorChains ) );
			//remove any backslash used for escaping
			//$sFoundSelectorGroup = str_replace('\\', '', $sFoundSelectorGroup);
			
			//$this->_debug( $sFoundSelectorGroup, '', 'afterSelectorFound' );
			
			//Convert the selector group to Xpath
			$sXPath = $this->convertCss2XPath( $sFoundSelectorGroup );
			
			//$this->_debug( $sXPath, '', 'afterConvertCss2XPath' );
			
			if ( $sXPath )
			{
				$aXPaths = array_unique( explode( ' | ', str_replace( '\\', '', $sXPath ) ) );
				
				foreach ( $aXPaths as $sXPathValue )
				{
					$oElement = $oXPath->query( $sXPathValue );

//                                if ($oElement === FALSE)
//                                {
//                                        echo $aMatches[1] . "\n";
//                                        echo $sXPath . "\n";
//                                        echo $sXPathValue . "\n";
//                                        echo "\n\n";
//                                }
					
					//Match found! Add to critical CSS
					if ( $oElement !== false && $oElement->length )
					{
						$sCriticalCss .= $aMatches[ 0 ];
						
						//$this->_debug( $sXPathValue, '', 'afterCriticalCssFound' );
						
						return '';
					}
					
					//$this->_debug( $sXPathValue, '', 'afterCriticalCssNotFound' );
				}
			}
			
			return '';
		}#END FN
		
		
		public function convertCss2XPath ( $sSelector )
		{
			$sSelector = preg_replace( '#\s*([>+~,])\s*#', '$1', $sSelector );
			$sSelector = trim( $sSelector );
			$sSelector = preg_replace( '#\s+#', ' ', $sSelector );
			
			
			if ( !$sSelector )
			{
				return false;
			}
			
			$sSelectorRegex = '#(?!$)'
				. '([>+~, ]?)' //separator
				. '([*a-z0-9]*)' //element
				. '(?:(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))(([.\#])((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+))?|'//class or id
				. '(\[((?:[_a-z0-9-]|\\\\[^\r\n\f0-9a-z])+)(([~|^$*]?=)["\']?([^\]"\']+)["\']?)?\]))*' //attribute
				. '#i';
			
			return preg_replace_callback( $sSelectorRegex, [ $this , '_tokenizer' ], $sSelector ) . '[1]';
		}
		
		protected function _tokenizer ( $aM )
		{
			$sXPath = '';
			
			switch ( $aM[ 1 ] )
			{
				case '>':
					$sXPath .= '/';
					
					break;
				case '+':
					$sXPath .= '/following-sibling::*';
					
					break;
				case '~':
					$sXPath .= '/following-sibling::';
					
					break;
				case ',':
					$sXPath .= '[1] | descendant-or-self::';
					
					break;
				case ' ':
					$sXPath .= '/descendant::';
					
					break;
				default:
					$sXPath .= 'descendant-or-self::';
					break;
			}
			
			if ( $aM[ 1 ] != '+' )
			{
				$sXPath .= $aM[ 2 ] == '' ? '*' : $aM[ 2 ];
			}
			
			if ( isset( $aM[ 3 ] ) || isset( $aM[ 9 ] ) )
			{
				$sXPath .= '[';
				
				$aPredicates = [];
				
				if ( isset( $aM[ 4 ] ) && $aM[ 4 ] == '.' )
				{
					$aPredicates[] = "contains(@class, ' " . $aM[ 5 ] . " ')";
				}
				
				if ( isset( $aM[ 7 ] ) && $aM[ 7 ] == '.' )
				{
					$aPredicates[] = "contains(@class, ' " . $aM[ 8 ] . " ')";
				}
				
				if ( isset( $aM[ 4 ] ) && $aM[ 4 ] == '#' )
				{
					$aPredicates[] = "@id = ' " . $aM[ 5 ] . " '";
				}
				
				if ( isset( $aM[ 7 ] ) && $aM[ 7 ] == '#' )
				{
					$aPredicates[] = "@id = ' " . $aM[ 8 ] . " '";
				}
				
				if ( isset( $aM[ 9 ] ) )
				{
					if ( !isset( $aM[ 11 ] ) )
					{
						$aPredicates[] = '@' . $aM[ 10 ];
					}
					else
					{
						switch ( $aM[ 12 ] )
						{
							case '=':
								$aPredicates[] = "@{$aM[10]} = ' {$aM[13]} '";
								
								break;
							case '|=':
								$aPredicates[] = "(@{$aM[10]} = ' {$aM[13]} ' or "
									. "starts-with(@{$aM[10]}, ' {$aM[13]}'))";
								break;
							case '^=':
								$aPredicates[] = "starts-with(@{$aM[10]}, ' {$aM[13]}')";
								break;
							case '$=':
								$aPredicates[] = "substring(@{$aM[10]}, string-length(@{$aM[10]})-"
									. strlen( $aM[ 13 ] ) . ") = '{$aM[13]} '";
								break;
							case '~=':
								$aPredicates[] = "contains(@{$aM[10]}, ' {$aM[13]} ')";
								break;
							case '*=':
								$aPredicates[] = "contains(@{$aM[10]}, '{$aM[13]}')";
								break;
							default:
								break;
						}
					}
				}
				
				if ( $aM[ 1 ] == '+' )
				{
					if ( $aM[ 2 ] != '' )
					{
						$aPredicates[] = "(name() = '" . $aM[ 2 ] . "')";
					}
					
					$aPredicates[] = '(position() = 1)';
				}
				
				$sXPath .= implode( ' and ', $aPredicates );
				$sXPath .= ']';
			}
			
			return $sXPath;
		}
		
	}#END CLASS