<?php
	
	namespace Core\js;
	
	use JFactory;
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 21:21
	 */
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	
	class CoreJs
	{
		
		public static $instance;
		
		public static function instance ( $options = [] )
		{
			if ( self::$instance === null )
			{
				self::$instance = new self( $options );
				
				
			}
			
			return self::$instance;
		}#END FN
		
		/**
		 * CoreJs constructor.
		 *
		 * @param $opt
		 */
		private function __construct ( $options )
		{
			return $this ;
		}#END FN
		
		public static function addCoreJs ()
		{
			$doc = JFactory::getDocument();
			$doc->addScript( 'libraries/zaz/Core/js/assets/zazCore.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
		}#END FN
		
		
		public static function addBtnYesNo (){
			$doc = JFactory::getDocument();
			$doc->addScript( \JURI::root().'libraries/zaz/Core/js/assets/zazCore_BtnYesNo.js', [ 'version' => 'auto' ], [ 'defer' => 1 ] );
		}#END FN
		
		
	}#END CLASS






