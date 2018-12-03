<?php
	/**
	 * @package     Core
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace Optimize;
	
	
	
	use Joomla\Registry\Registry;
	use JUri;
	
	
	include_once JPATH_LIBRARIES . '/zaz/Core/vendor/autoload.php' ;
	
	abstract class OptimizeEmbed
	{
		
		
		
		//regex for double quoted strings
		const DOUBLE_QUOTE_STRING = '"(?>(?:\\\\.)?[^\\\\"]*+)+?(?:"|(?=$))';
		//regex for single quoted string
		const SINGLE_QUOTE_STRING = "'(?>(?:\\\\.)?[^\\\\']*+)+?(?:'|(?=$))";
		//regex for block comments
		const BLOCK_COMMENTS = '/\*(?>[^/\*]++|//|\*(?!/)|(?<!\*)/)*+\*/';
		//regex for line comments
		const LINE_COMMENTS = '//[^\r\n]*+';
		//regex for HTML comments
		const HTML_COMMENTS = '(?:(?:<!--|(?<=[\s/^])-->)[^\r\n]*+)';
		
		const URI = '(?<=url)\(\s*+(?:"[^"]*+"|\'[^\']*+\'|[^)]*+)\s*+\)';
		
		
		/**
		 * @var    Registry  Options for the zaz\Core data object.
		 * @since  12.3
		 */
		protected $options;
		
		/**
		 * @var    JUri  URI of the page being rendered.
		 * @since  12.3
		 */
		protected $uri;
		
		/**
		 * CoreEmbed constructor.
		 *
		 * @param Registry|null $options
		 * @param JUri|null     $uri
		 */
		public function __construct(Registry $options = null, JUri $uri = null)
		{
			$this->options = $options ? $options : new Registry;
			$this->uri = $uri ? $uri : JUri::getInstance();
		}#END FN
		
		/**
		 * Method to retrieve the javascript header for the embed API
		 *
		 * @return  string  The header
		 *
		 * @since   12.3
		 */
		public function isSecure()
		{
			return $this->uri->getScheme() == 'https';
		}#END FN
		
		/**
		 * Get an option from the CoreEmbed instance.
		 *
		 * @param   string  $key  The name of the option to get.
		 *
		 * @return  mixed  The option value.
		 *
		 * @since   12.3
		 */
		public function getOption($key)
		{
			return $this->options->get($key);
		}#END FN
		
		/**
		 *
		 * * Set an option for the CoreEmbed instance.
		 *
		 * @param   string  $key    The name of the option to set.
		 * @param   mixed   $value  The option value to set.
		 *
		 *
		 * @param $key
		 * @param $value
		 *
		 * @return OptimizeEmbed This object for method chaining.
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 28.11.18
		 */
		public function setOption($key, $value)
		{
			$this->options->set($key, $value);
			
			return $this;
		}#END FN
		
		
	} #END CLASS