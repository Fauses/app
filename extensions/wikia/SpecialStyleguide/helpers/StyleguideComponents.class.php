<?php

class StyleguideComponents {

	/**
	 * @desc Array key name where message key for component's name could be found in documentation array
	 */
	const COMPONENT_NAME_MSG_KEY = 'name';

	/**
	 * @desc Array key name where message key for component's description could be found in documentation array
	 */
	const COMPONENT_DESC_MSG_KEY = 'description';

	/**
	 * @desc Component's documentation file suffix
	 * Example: button component documentation file should be named button_doc.json
	 *
	 * @var String
	 */
	const DOCUMENTATION_FILE_SUFFIX = '_doc.json';

	/**
	 * @desc Component's messages/i18n file suffix
	 * Example: button i18n file should be named button.i18n.php
	 *
	 * @var String
	 */
	const MESSAGES_FILE_SUFFIX = 'i18n.php';

	/**
	 * @var Array|null
	 */
	public static $componentsNames = null;

	/**
	 * @var Wikia\UI\Factory
	 */
	private $uiFactory;

	public function __construct() {
		$this->uiFactory = Wikia\UI\Factory::getInstance();

		static::$componentsNames = WikiaDataAccess::cache(
			wfSharedMemcKey( __CLASS__, 'components_names_list' ),
			\Wikia\UI\Factory::MEMCACHE_EXPIRATION,
			['StyleguideComponents', 'loadComponentsFromFileSystem']
		);
	}

	/**
	 * @desc Returns an array with all available components configuration
	 *
	 * @return array
	 */
	public function getAllComponents() {
		$components = WikiaDataAccess::cache(
			wfSharedMemcKey( __CLASS__, 'all_components_list_with_details' ),
			Wikia\UI\Factory::MEMCACHE_EXPIRATION,
			[ $this, 'getAllComponentsFromDirectories' ]
		);

		return $components;
	}

	/**
	 * @desc Iterates the components directory and returns list of it subdirectories
	 *
	 * @return array
	 */
	public static function loadComponentsFromFileSystem() {
		$componentsNames = [];
		$directory = new DirectoryIterator( \Wikia\UI\Factory::getComponentsDir() );

		while( $directory->valid() ) {
			if( !$directory->isDot() && $directory->isDir() ) {
				$componentName = $directory->getFilename();
				$componentsNames[] = $componentName;
			}

			$directory->next();
		}

		return $componentsNames;
	}

	/**
	 * @desc Returns full documentation file's path
	 *
	 * @param string $name component's name
	 *
	 * @return string full file path
	 */
	public function getComponentDocumentationFileFullPath( $name ) {
		return $this->uiFactory->getComponentsDir() .
			$name .
			DIRECTORY_SEPARATOR .
			$name .
			self::DOCUMENTATION_FILE_SUFFIX;
	}

	/**
	 * @desc
	 *
	 * @param String $componentName
	 * @return Array
	 */
	private function loadComponentDocumentationAsArray( $componentName ) {
		wfProfileIn( __METHOD__ );

		$docFile = $this->getComponentDocumentationFileFullPath( $componentName );
		$docFileContent = $this->uiFactory->loadFileContent( $docFile );
		$docInArray = $this->uiFactory->loadFromJSON( $docFileContent );

		wfProfileOut( __METHOD__ );
		return $docInArray;
	}

	/**
	 * @desc Returns all components by iterating on components directory
	 *
	 * @return array
	 */
	public function getAllComponentsFromDirectories() {
		$components = [];

		foreach( static::$componentsNames as $componentName ) {
			$component = [];
			$componentDocumentation = $this->loadComponentDocumentationAsArray( $componentName );
			// todo: load component samples
			$component = $this->prepareMainMessages( $component, $componentDocumentation );

			// add unique id so after clicking on the components list's link page jumps to the right anchor
			$componentConfig['id'] = Sanitizer::escapeId( $componentName, ['noninitial'] );

			if ( isset( $componentDocumentation['types'] ) ) {
				$component['types'] = $this->prepareTypesDocumentation( $componentDocumentation['types'] );
			}

			// todo: similiar logic to types for samples
			$components[] = $component;
		}

		return $components;
	}

	/**
	 * @desc Return component with i18n name and description
	 *
	 * @param Array $component
	 * @param Array $messages
	 * @return mixed
	 */
	private function prepareMainMessages( $component, $messages ) {
		$component[ 'name' ] = $this->prepareMessage( $messages[ self::COMPONENT_NAME_MSG_KEY ] );
		$component[ 'description' ] = $this->prepareMessage( $messages[ self::COMPONENT_DESC_MSG_KEY] );

		return $component;
	}

	/**
	 * @desc Creates an stdObject instance for mustache template for each component's type
	 *
	 * @param Array $typesFromFile
	 *
	 * @return array of stdObject instances
	 */
	private function prepareTypesDocumentation( $typesFromFile ) {
		$result = [];

		foreach( $typesFromFile as $typeName => $typeFromFile ) {
			$type = new stdClass();
			$type->typeName = $typeName;
			$type->typeParams = $this->prepareParamDocumentation( $typeFromFile[ 'params' ] );

			$result[] = $type;
		}

		return $result;
	}

	/**
	 * @desc Creates rows for each parameter for each component's type parameter
	 *
	 * @param Array $paramsFromFile
	 *
	 * @return Array of stdObject instances
	 */
	private function prepareParamDocumentation( $paramsFromFile ) {
		$result = [];

		foreach( $paramsFromFile as $param ) {
			$params = new stdClass();
			$params->templateVar = $param[ 'templateVar' ];
			$params->type = $param[ 'type' ];
			$params->description = $this->prepareMessage( $param[ 'description' ] );

			if( !empty( $param['objectVar'] ) ) {
				$params->object = $this->prepareObjectData( $param['objectVar'] );
			}

			if( !empty( $param['value'] ) ) {
				$params->value = $this->prepareValueData( $param['value'] );
			}

			$result[] = $params;
		}

		return $result;
	}

	/**
	 * @desc Creates {key: '', type: ''} structure for each object type parameter
	 *
	 * @param Array $objectVar
	 * @return array
	 */
	private function prepareObjectData( $objectVar ) {
		$result = [];

		foreach( $objectVar as $key => $type ) {
			$objectRow = new stdClass();
			$objectRow->key = $key;
			$objectRow->type = $type;
			$result[] = $objectRow;
		}

		return $result;
	}

	/**
	 * @desc Creates {option: ''} structure for each value/enum type parameter
	 *
	 * @param Array $value
	 * @return Array
	 */
	private function prepareValueData( $value ) {
		$result = [];

		foreach( $value as $option ) {
			$valueRow = new stdClass();
			$valueRow->option = $option;
			$result[] = $valueRow;
		}

		return $result;
	}

	/**
	 * @desc Returns i18n message by key
	 *
	 * @param $key
	 * @return String
	 */
	private function prepareMessage($key) {
		return wfMessage($key)->plain();
	}

	/**
	 * @desc Returns full documentation file's path
	 *
	 * @param string $name component's name
	 *
	 * @return string full file path
	 */
	public static function getComponentMessagesFileFullPath( $name ) {
		return \Wikia\UI\Factory::getComponentsDir() .
			$name .
			DIRECTORY_SEPARATOR .
			$name .
			'.' .
			self::MESSAGES_FILE_SUFFIX;
	}

	/**
	 * Include components messages files when recompiling the language cache
	 *
	 * @param $extensionMessagesFiles - array with extensions' messages files
	 *
	 * @return bool hook run status
	 */
	public static function onBeforeExtensionMessagesRecache( &$extensionMessagesFiles ) {
		// we don't want to use componentsNames from the cache when rebuilding the cache, as it won't speedup the process
		// anyway and we risk some of the messages won't get cached
		foreach( StyleguideComponents::loadComponentsFromFileSystem() as $componentName ) {
			$componentMessageFile = self::getComponentMessagesFileFullPath( $componentName );
			$extensionMessagesFiles['StyleguideComponents' . ucfirst($componentName) ] = $componentMessageFile;
		}

		return true;
	}

}
