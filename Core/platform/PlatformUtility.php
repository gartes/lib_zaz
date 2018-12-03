<?php
	namespace Core\platform;
	use JFactory;
	
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 8:44
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/platform/utility.php
	 *
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class PlatformUtility
	{
		public static function translate($text)
		{
			if(strlen($text) > 20)
			{
				$text = substr($text, 0, strpos(wordwrap($text, 20), "\n"));
			}
			
			$text = 'JCH_' . strtoupper(str_replace(' ', '_', $text));
			
			return JText::_($text);
		}#END FN
		
		public static function tab()
		{
			$Doc = JFactory::getDocument();
			
			return $Doc->_getTab();
		}#END FN
		
		public static function lnEnd()
		{
			$Doc = JFactory::getDocument();
			
			return $Doc->_getLineEnd();
		}#END FN
		
		
	}#END CLASS