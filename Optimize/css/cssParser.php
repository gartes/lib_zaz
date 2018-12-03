<?php
	namespace Optimize\css;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 4:19
	 */
	
	
	use Core\platform\PlatformUri;
	use Core\platform\PlatformUtility;
	use Optimize\OptimizeEmbed;
	use Joomla\Registry\Registry;
	use Optimize\OptimizeHelper;
	use Optimize\OptimizeUrl;
	
	
	class cssParser extends OptimizeEmbed
	{
		
		public $e = '';
		public $u = '';
		
		/**
		 * cssOptimize constructor.
		 */
		public function __construct ( $params = null, $bBackend = false )
		{
			parent::__construct(   );
			
			
			$this->sLnEnd = is_null( $params ) ? "\n" : PlatformUtility::lnEnd();
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
						$oUri = clone JchPlatformUri::getInstance();
						
						$sImageUrl = '//' . $oUri->toString( [ 'host', 'port' ] ) .
							$oImageUri->toString( [ 'path', 'query', 'fragment' ] );
					}
				}
				else
				{
					if ( !JchOptimizeUrl::isAbsolute( $sImageUrl ) )
					{
						$sImageUrl = JchOptimizeUrl::toAbsolute( $sImageUrl, $sCssFileUrl );
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
		
		
		###################################################################################
		public function getCriticalCss(){
			die(__FILE__ .' Lines '. __LINE__ );
			echo'<pre>';print_r( $this );echo'</pre>'.__FILE__.' '.__LINE__;
		}#END FN
		
		
	}#END CLASS