<?php
	namespace Optimize\css;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 4:19
	 */
	
	
	
	use Optimize\OptimizeEmbed;
	use Joomla\Registry\Registry;
	
	
	class cssOptimize extends OptimizeEmbed
	{
		
		
		/**
		 * cssOptimize constructor.
		 */
		public function __construct ( Registry $options = null )
		{
			parent::__construct( $options );
			
		
		}#END FN
		
		public function getCriticalCss(){
			
			
			$stylesheetList = $this->getStylesheetData();
			
			echo'<pre>';print_r( $this );echo'</pre>'.__FILE__.' '.__LINE__;
		}#END FN
		
		/**
		 * Получиь данные всех стилей в хадаре
		 * @author    Gartes
		 *
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
			$combineData = $combiner->combineFiles( $Stylesheet['file'] , 'css'   );
			
			
			echo'<pre>';print_r( $combineData );echo'</pre>'.__FILE__.' '.__LINE__;
			
			
			die(__FILE__ .' Lines '. __LINE__ );
			
		}#END FN
		
		
	}#END CLASS