<?php
	namespace Optimize\js;
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 4:19
	 */
	
	
	use Core\extensions\zazExtensions;
	use Core\js\CoreJs;
	use Optimize\OptimizeEmbed;
	use Joomla\Registry\Registry;
	
	
	class jsOptimize extends OptimizeEmbed
	{
		
		public  $params ;
		
		/**
		 * cssOptimize constructor.
		 */
		public function __construct ( Registry $options = null )
		{
			parent::__construct( $options );
			$this->params =  zazExtensions::getParamsPlugin('system' , 'starcache' ) ;
		
		}#END FN
		
		
		/**
		 * Создание ленивой загрузки для Js файлов
		 *
		 * @param $styleSheets
		 *
		 * @author    Gartes
		 *
		 * @since     3.8
		 * @copyright 03.12.18
		 *
		 * @return
		 */
		public function addLazyLoadingJs ($scriptTime ,  $return = false ){
			
			CoreJs::addCoreJs();
			$timeArr = [];
			$newScriptTime = [];
			foreach ($scriptTime as $url => $script ){
				if ($script->delayed_loading){
					$timeArr[$script->delayed_loading_time][$url] = $script ;
					
				}else{
					$newScriptTime[$url] = $script ;
				}#END IF
			}#END FOREACH
			
			if (!count($timeArr)) return $newScriptTime ;
			
			$scriptAdd = 'document.addEventListener("DOMContentLoaded", function () {';
			foreach ($timeArr as $time => $scripts ){
				$scriptAdd .= 'setTimeout(()=>{';
				$scriptAdd .= 'Promise.all([';
					
					foreach ($scripts as $url => $option  ) {
						$scriptAdd .= "zazCoreLoadAssets.js('".$url."'),";
					}#END FOREACH
				
				$scriptAdd .= '])';
				$scriptAdd .= '},'.$time.')';
			}#END FOREACH
			
			$scriptAdd .= '});';
			
			$doc = \JFactory::getDocument();
			$doc->addScriptDeclaration($scriptAdd);
			return $newScriptTime ;
			
			
		}
		
		
		
		
		
	}#END CLASS