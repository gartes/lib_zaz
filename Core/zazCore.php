<?php namespace Core ;
	/**
	 *
	 *
	 *  require
	 *
	 *  if ( !class_exists( 'Core\Core' ) )  require JPATH_LIBRARIES . '/zaz/Core/Core.php';
	 *
	 *
	 * @package     zaz\Core
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	use Core\extensions\zazExtensions;
	use Joomla\Registry\Registry;
	
	 include_once __DIR__ .'/vendor/autoload.php' ;
	
	class zazCore
	{
		/**
		 * @var    Registry  Options for the Core object.
		 * @since  12.3
		 */
		protected $options;
		
		public function __construct(Registry $options = null, JGoogleAuth $auth = null)
		{
			$this->options = isset($options) ? $options : new Registry;
			
			
			echo'<pre>';print_r( $this );echo'</pre>'.__FILE__.' '.__LINE__;
			
			//$this->auth  = isset($auth) ? $auth : new JGoogleAuthOauth2($this->options);
		}
		
		/**
		 * Method to create CoreEmbed objects
		 *
		 * $Core = new \Core\zazCore();
		 * $extensions = $Core->embed('extensions');
		 *
		 *
		 * @param   string    $name     Name of property to retrieve
		 * @param   Registry  $options  Google options object.
		 *
		 * @return zazCore  zazCore embed API object.
		 *
		 * @since   12.3
		 */
		public function embed($name, $options = null)
		{
			if ($this->options && !$options)
			{
				$options = $this->options;
			}
			
			
			
			
			switch ($name)
			{
				case 'extensions':
					return new zazExtensions($options);
				 
				default:
					return;
			}
		}
		
		
		
		
	} 