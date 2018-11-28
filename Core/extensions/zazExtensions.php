<?php
	/**
	 * @package     Core
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace Core\extensions;
	
	use JFactory;
	use Joomla\Registry\Registry;
	use Core\CoreEmbed;
	use JTable;
	
	class zazExtensions extends CoreEmbed
	{
		#Id of the joomla table where the plugins are registered
		protected $_jid = 0;
		
		
		/**
		 * zazExtensions constructor.
		 *
		 * @param Registry|null $options
		 */
		public function __construct ( Registry $options = null )
		{
			parent::__construct( $options );
			
			
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		/**
		 * Gets the id of the Joomla table where the plugin is registered
		 * Получает идентификатор таблицы Joomla, где зарегистрирован плагин
		 *
		 *
		 * @param $Plg - obj \JPlugin
		 *
		 * @return bool|int
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 28.11.18
		 */
		public function getJoomlaPluginId ( $Plg )
		{
			
			if ( !empty( $this->_jid ) )
			{
				return $this->_jid;
			}#END IF
			
			$db = JFactory::getDBO();
			
			$q = 'SELECT j.`extension_id` AS c FROM #__extensions AS j
					WHERE j.element = "' . $Plg->_name . '" AND j.`folder` = "' . $Plg->_type . '" and `enabled`= "1" and `state`="0" ';
			
			$db->setQuery( $q );
			$this->_jid = $db->loadResult();
			if ( !$this->_jid )
			{
				vmError( 'getJoomlaPluginId ' . $db->getErrorMsg() );
				
				return false;
			}
			else
			{
				return $this->_jid;
			}#END IF
			
		}#END FN
		
		
		
		
		
		
		
		
		
		
		
		
		
		/**
		 * Сохранить конфиг расшерения
		 *
		 * @param      $params
		 * @param  Bool extensionsId
		 *
		 * @throws \Exception
		 * @author    Gartes
		 * @since     3.8
		 * @copyright 28.11.18
		 */
		public function updateExtensionParams ( $params, $extensionsId = false )
		{
			
			if ( !$extensionsId ) $extensionsId = $this->_jid;
			
			# Save the new parameters extension
			$table = JTable::getInstance( 'extension' );
			
			$table->load( $extensionsId );
			$table->bind( [ 'params' => $params ] );
			
			// Store the changes
			if ( !$table->store() )
			{
				# If there is an error show it to the admin
				JFactory::getApplication()->enqueueMessage( $table->getError(), 'error' );
			}
		}#END FN
		
		
	}#END CLASS