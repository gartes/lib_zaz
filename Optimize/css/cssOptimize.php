<?php
	namespace Optimize\css;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 4:19
	 */
	
	
	use Core\js\CoreJs;
	use Optimize\OptimizeEmbed;
	use Joomla\Registry\Registry;
	
	
	class cssOptimize extends OptimizeEmbed
	{
		
		public  $params ;
		
		/**
		 * cssOptimize constructor.
		 */
		public function __construct ( Registry $options = null )
		{
			parent::__construct( $options );
			$this->params =  \Core\extensions\zazExtensions::getParamsPlugin('system' , 'starcache' ) ;
		
		}#END FN
		
		/**
		 * Получение ВСЕХ стилей CSS на странице
		 * Точка входа  \Optimize\css
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 *
		 */
		public function getAllCss(){
			$cssContents  = $this->getStylesheetData();
			return $cssContents ;
		}#END FN
		
		
		
		// $ArrData
		public function sendPost ( $ArrData )
		{
			$myCurl = curl_init();
			curl_setopt_array( $myCurl, [
				CURLOPT_URL            => 'https://nobd.ml/node',
				CURLOPT_TIMEOUT        => 400,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => http_build_query($ArrData),
			] );
			$response = curl_exec( $myCurl );
			curl_close( $myCurl );
			$data = json_decode($response  )  ;
			
			if ( !isset($data->success) ) throw new \Exception('nobd.ml Сервис недоступен');
			
			// if ( !$response )
				
				return $data;
			
			
		}#END FN
		
		
		/**
		 * Получение критических стилей
		 *
		 * @param $bodyHrml
		 * @param $allCss
		 *
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 * @return array
		 */
		public function getCriticalCss ($bodyHtml , $allCss){
			
			$cssParser = new \Optimize\css\cssParser();
			$CriticalCss = $cssParser->optimizeCssDelivery ( $allCss['file'] , $bodyHtml );
			
			return $CriticalCss ;
		}#END FN
		
		/**
		 * Создание ленивой загрузки для CSS Links
		 *
		 * @param $styleSheets
		 *
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 *
		 * @return bool
		 */
		public function addLazyLoadingCss ($styleSheets){
			
			
			
			
			CoreJs::addCoreJs();
			
			$scipt  = 'document.addEventListener("DOMContentLoaded",()=>{setTimeout(()=>{';
				$scipt .= 'Promise.all([';
				foreach($styleSheets as $url => $opt ){
					$scipt .= "zazCoreLoadAssets.css('".$url."'),";
				}#END FOREACH
			
			$scipt .= "])";
			$scipt .= ".then(function() {console.log('LazyLoadingCss has loaded!');})";
			$scipt .= ".catch(function(){console.log('LazyLoadingCss , epic failure!');";
			$scipt .= "});";
			$scipt .= "},2000)";
			$scipt .= "});";
			$doc = \JFactory::getDocument();
			$doc->addScriptDeclaration($scipt);
			return true ;
		}#END FN
		
		
		
		/**
		 * Получиь данные всех стилей в хедаре
		 *
		 *
		 *
		 * @author    Gartes
		 *
		 * @throws
		 * @since     3.8
		 * @copyright 03.12.18
		 */
		private function getStylesheetData(){
			$doc = \JFactory::getDocument();
			
			$Stylesheet = [
				'file' =>  $doc->_styleSheets ,
				'style' => $doc->_style ,
			];
			
			
			$combiner = new \Optimize\combiner();
			
			# Слить все файлы CSS
			$combineData = $combiner->getContents( $Stylesheet['file']   );
			
			return $combineData ;
		}#END FN
		
		
	}#END CLASS