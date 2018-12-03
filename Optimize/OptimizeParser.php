<?php
	
	namespace Optimize;
	/**
	 * Created by PhpStorm.
	 * User: oleg
	 * Date: 03.12.18
	 * Time: 16:29
	 *
	 * Origin Patch /var/www/smska.tk/html/plugins/system/jch_optimize/jchoptimize/parser.php
	 */
	
	
	
	
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	class OptimizeParser
	{
		/** @var string   Html of page */
		public $sHtml = '';
		
		public $params = null;
		protected $oFileRetriever;
		public $sLnEnd = '';
		public $sTab = '';
		
		/**
		 * OptimizeParser constructor.
		 *
		 * @param $oParams
		 * @param $sHtml
		 * @param $oFileRetriever
		 *
		 */
		public function __construct ( $oParams, $sHtml, $oFileRetriever)
		{
			$this->params = $oParams;
			$this->sHtml  = $sHtml;
			
			$this->oFileRetriever = $oFileRetriever;
			
			$this->sLnEnd = \Core\platform\PlatformUtility::lnEnd();
			$this->sTab   = \Core\platform\PlatformUtility::tab();
			
			
		}#END FN
		
		
	}#END CLASS