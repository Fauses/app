/*!
 * VisualEditor DataModel Model class.
 *
 * @copyright 2011-2014 VisualEditor Team and others; see AUTHORS.txt
 * @license The MIT License (MIT); see LICENSE.txt
 */

/**
 * Base class for DM models.
 *
 * @class
 * @abstract
 *
 * @constructor
 * @param {Object} element Reference to plain object in linear model
 */
ve.dm.Model = function VeDmModel( element ) {
	// Properties
	this.element = element || { 'type': this.constructor.static.name };
};

/* Static Properties */

ve.dm.Model.static = {};

/**
 * Symbolic name for this model class. Must be set to a unique string by every subclass.
 * @static
 * @property {string}
 * @inheritable
 */
ve.dm.Model.static.name = null;

/**
 * Array of HTML tag names that this model should be a match candidate for.
 * Empty array means none, null means any.
 * For more information about element matching, see ve.dm.ModelRegistry.
 * @static
 * @property {string[]}
 * @inheritable
 */
ve.dm.Model.static.matchTagNames = null;

/**
 * Array of RDFa types that this model should be a match candidate for.
 * Empty array means none, null means any.
 * For more information about element matching, see ve.dm.ModelRegistry.
 * @static
 * @property {Array}
 * @inheritable
 */
ve.dm.Model.static.matchRdfaTypes = null;

/**
 * Optional function to determine whether this model should match a given element.
 * Takes a Node and returns true or false.
 * This function is only called if this model has a chance of "winning"; see
 * ve.dm.ModelRegistry for more information about element matching.
 * If set to null, this property is ignored. Setting this to null is not the same as unconditionally
 * returning true, because the presence or absence of a matchFunction affects the model's
 * specificity.
 *
 * NOTE: This function is NOT a method, within this function "this" will not refer to an instance
 * of this class (or to anything reasonable, for that matter).
 * @static
 * @property {Function}
 * @inheritable
 */
ve.dm.Model.static.matchFunction = null;

/**
 * Static function to convert a DOM element or set of sibling DOM elements to a linear model element
 * for this model type.
 *
 * This function is only called if this model "won" the matching for the first DOM element, so
 * domElements[0] will match this model's matching rule. There is usually only one DOM node in
 * domElements[]. Multiple elements will only be passed if this model supports about groups.
 * If there are multiple nodes, the nodes are all adjacent siblings in the same about group
 * (i.e. they are grouped together because they have the same value for the about attribute).
 *
 * The converter has some state variables that can be obtained by this function:
 * - if converter.isExpectingContent() returns true, the converter expects a content element
 * - if converter.isInWrapper() returns true, the returned element will be put in a wrapper
 *   paragraph generated by the converter (this is only relevant if isExpectingContent() is true)
 * - converter.canCloseWrapper() returns true if the current wrapper paragraph can be closed,
 *   and false if it can't be closed or if there is no active wrapper
 *
 * This function is allowed to return a content element when context indicates that a non-content
 * element is expected or vice versa. If that happens, the converter deals with it in the following way:
 *
 * - if a non-content element is expected but a content element is returned:
 *     - open a wrapper paragraph
 *     - put the returned element in the wrapper
 * - if a content element is expected but a non-content element is returned:
 *     - if we are in a wrapper paragraph:
 *         - if we can close the wrapper:
 *             - close the wrapper
 *             - insert the returned element right after the end of the wrapper
 *         - if we can't close the wrapper:
 *             - alienate the element
 *     - if we aren't in a wrapper paragraph:
 *         - alienate the element
 *
 * For these purposes, annotations are considered content. Meta-items can occur anywhere, so if
 * a meta-element is returned no special action is taken. Note that "alienate" always means an alien
 * *node* (ve.dm.AlienNode) will be generated, never an alien meta-item (ve.dm.AlienMetaItem),
 * regardless of whether the subclass attempting the conversion is a node or a meta-item.
 *
 * The returned linear model element must have a type property set to a registered model name
 * (usually the model's own .static.name, but that's not required). It may optionally have an attributes
 * property set to an object with key-value pairs. Any other properties are not allowed.
 *
 * This function may return a single linear model element, or an array of balanced linear model
 * data. If this function needs to recursively convert a DOM node (e.g. a child of one of the
 * DOM elements passed in), it can call converter.getDataFromDomSubtree( domElement ). Note that
 * if an array is returned, the converter will not descend into the DOM node's children; the model
 * will be assumed to have handled those children.
 *
 * @static
 * @inheritable
 * @method
 * @param {Node[]} domElements DOM elements to convert. Usually only one element
 * @param {ve.dm.Converter} converter Converter object
 * @returns {Object|Array|null} Linear model element, or array with linear model data, or null to alienate
 */
ve.dm.Model.static.toDataElement = function () {
	return { 'type': this.name };
};

/**
 * Static function to convert a linear model data element for this model type back to one or more
 * DOM elements.
 *
 * If this model is a node with handlesOwnChildren set to true, dataElement will be an array of
 * the linear model data of this node and all of its children, rather than a single element.
 * In this case, this function way want to recursively convert linear model data to DOM, which can
 * be done with ve.dm.Converter#getDomSubtreeFromData.
 *
 * NOTE: If this function returns multiple DOM elements, the DOM elements produced by the children
 * of this model (if it's a node and has children) will be attached to the first DOM element in the array.
 * For annotations, only the first element is used, and any additional elements are ignored.
 *
 * @static
 * @inheritable
 * @method
 * @param {Object|Array} dataElement Linear model element or array of linear model data
 * @param {HTMLDocument} doc HTML document for creating elements
 * @param {ve.dm.Converter} converter Converter object to optionally call `getDomSubtreeFromData` on
 * @returns {Node[]} DOM elements
 */
ve.dm.Model.static.toDomElements = function ( dataElement, doc ) {
	if ( this.matchTagNames && this.matchTagNames.length === 1 ) {
		return [ doc.createElement( this.matchTagNames[0] ) ];
	}
	throw new Error( 've.dm.Model subclass must match a single tag name or implement toDomElements' );
};

/**
 * Whether this model supports about grouping. When a DOM element matches a model type that has
 * about grouping enabled, the converter will look for adjacent siblings with the same value for
 * the about attribute, and ask #toDataElement to produce a single data element for all of those
 * DOM nodes combined.
 *
 * The converter doesn't descend into about groups, i.e. it doesn't convert the children of the
 * DOM elements that make up the about group. This means the resulting linear model element will
 * be childless.
 *
 * @static
 * @property {boolean}
 * @inheritable
 */
ve.dm.Model.static.enableAboutGrouping = false;

/**
 * Which HTML attributes should be preserved for this model type. HTML attributes on the DOM
 * elements that match this specification will be stored as attributes in the linear model. The
 * attributes will be stored in the .htmlAttributes property of the linear model element.
 *
 * When converting back to DOM, these HTML attributes will be restored except for attributes that
 * were already set by #toDomElements.
 *
 * The value of this property can be one of the following:
 *
 * - true, to preserve all attributes (default)
 * - false, to preserve none
 * - a string, to preserve only that attribute
 * - a regular expression matching attributes that should be preserved
 * - an array of strings or regular expressions
 * - an object with the following keys:
 *   - 'blacklist': specification of attributes not to preserve (boolean|string|RegExp|Array)
 *   - 'whitelist': specification of attributes to preserve
 *
 * If only a blacklist is specified, all attributes will be preserved except the ones matching
 * the blacklist. If only a whitelist is specified, only those attributes matching the whitelist
 * will be preserved. If both are specified, only attributes that both match the whitelist and
 * do not match the blacklist will be preserved.
 *
 * @static
 * @property {boolean|string|RegExp|Array|Object}
 * @inheritable
 */
ve.dm.Model.static.storeHtmlAttributes = true;

/* Static methods */

/**
 * Determine whether an attribute name matches an attribute specification.
 *
 * @param {string} attribute Attribute name
 * @param {boolean|string|RegExp|Array|Object} spec Attribute specification, see #storeHtmlAttributes
 * @returns {boolean} Attribute matches spec
 */
ve.dm.Model.matchesAttributeSpec = function ( attribute, spec ) {
	function matches( subspec ) {
		if ( subspec instanceof RegExp ) {
			return !!subspec.exec( attribute );
		}
		if ( typeof subspec === 'boolean' ) {
			return subspec;
		}
		return attribute === subspec;
	}

	function matchesArray( specArray ) {
		var i, len;
		if ( !ve.isArray( specArray ) ) {
			specArray = [ specArray ];
		}
		for ( i = 0, len = specArray.length; i < len; i++ ) {
			if ( matches( specArray[i] ) ) {
				return true;
			}
		}
		return false;
	}

	if ( spec.whitelist === undefined && spec.blacklist === undefined ) {
		// Not an object, treat spec as a whitelist
		return matchesArray( spec );
	}
	return matchesArray( spec.whitelist || true ) && !matchesArray( spec.blacklist || false );
};

/**
 * Get hash object of a linear model data element.
 *
 * @static
 * @param {Object} dataElement Data element
 * @returns {Object} Hash object
 */
ve.dm.Model.static.getHashObject = function ( dataElement ) {
	return {
		type: dataElement.type,
		attributes: dataElement.attributes,
		htmlAttributes: dataElement.htmlAttributes
	};
};

/**
 * Array of RDFa types that this model should be a match candidate for.
 *
 * @static
 * @returns {Array} Array of strings or regular expressions
 */
ve.dm.Model.static.getMatchRdfaTypes = function () {
	return this.matchRdfaTypes;
};

/**
 * Remove a specified HTML attribute from all DOM elements in the model.
 *
 * @static
 * @param {Object} dataElement Data element
 * @param {string} attribute Attribute name
 */
ve.dm.Model.static.removeHtmlAttribute = function ( dataElement, attribute ) {
	function removeAttributeRecursive( children ) {
		var i;
		for ( i = 0; i < children.length; i++ ) {
			if ( children[i].values ) {
				delete children[i].values[attribute];
				if ( ve.isEmptyObject( children[i].values ) ) {
					delete children[i].values;
				}
			}
			if ( children[i].children ) {
				removeAttributeRecursive( children[i].children );
				if ( !children[i].children.length ) {
					delete children[i].children;
				}
			}
			if ( ve.isEmptyObject( children[i] ) ) {
				children.splice( i, 1 );
				i--;
			}
		}
	}

	if ( dataElement.htmlAttributes ) {
		removeAttributeRecursive( dataElement.htmlAttributes );
		if ( !dataElement.htmlAttributes.length ) {
			delete dataElement.htmlAttributes;
		}
	}
};

/* Methods */

/**
 * Get a reference to the linear model element.
 *
 * @method
 * @returns {Object} Linear model element passed to the constructor, by reference
 */
ve.dm.Model.prototype.getElement = function () {
	return this.element;
};

/**
 * Get the symbolic name of this model's type.
 *
 * @method
 * @returns {string} Type name
 */
ve.dm.Model.prototype.getType = function () {
	return this.constructor.static.name;
};

/**
 * Get the value of an attribute.
 *
 * Return value is by reference if array or object.
 *
 * @method
 * @param {string} key Name of attribute to get
 * @returns {Mixed} Value of attribute, or undefined if no such attribute exists
 */
ve.dm.Model.prototype.getAttribute = function ( key ) {
	return this.element && this.element.attributes ? this.element.attributes[key] : undefined;
};

/**
 * Get a copy of all attributes.
 *
 * Values are by reference if array or object, similar to using the getAttribute method.
 *
 * @method
 * @param {string} [prefix] Only return attributes with this prefix, and remove the prefix from them
 * @returns {Object} Attributes
 */
ve.dm.Model.prototype.getAttributes = function ( prefix ) {
	var key, filtered,
		attributes = this.element && this.element.attributes ? this.element.attributes : {};
	if ( prefix ) {
		filtered = {};
		for ( key in attributes ) {
			if ( key.indexOf( prefix ) === 0 ) {
				filtered[key.substr( prefix.length )] = attributes[key];
			}
		}
		return filtered;
	}
	return ve.extendObject( {}, attributes );
};

/**
 * Get the preserved HTML attributes.
 * @returns {Object[]} HTML attribute list, or empty array
 */
ve.dm.Model.prototype.getHtmlAttributes = function () {
	return ( this.element && this.element.htmlAttributes ) || [];
};

/**
 * Check if the model has certain attributes.
 *
 * If an array of keys is provided only the presence of the attributes will be checked. If an object
 * with keys and values is provided both the presence of the attributes and their values will be
 * checked. Comparison of values is done by casting to strings unless the strict argument is used.
 *
 * @method
 * @param {string[]|Object} attributes Array of keys or object of keys and values
 * @param {boolean} strict Use strict comparison when checking if values match
 * @returns {boolean} Model has attributes
 */
ve.dm.Model.prototype.hasAttributes = function ( attributes, strict ) {
	var key, i, len,
		ourAttributes = this.getAttributes() || {};
	if ( ve.isPlainObject( attributes ) ) {
		// Node must have all the required attributes
		for ( key in attributes ) {
			if (
				!( key in ourAttributes ) ||
				( strict ?
					attributes[key] !== ourAttributes[key] :
					String( attributes[key] ) !== String( ourAttributes[key] )
				)
			) {
				return false;
			}
		}
	} else if ( ve.isArray( attributes ) ) {
		for ( i = 0, len = attributes.length; i < len; i++ ) {
			if ( !( attributes[i] in ourAttributes ) ) {
				return false;
			}
		}
	}
	return true;
};

/**
 * Get a clone of the model's linear model element.
 *
 * The attributes object will be deep-copied.
 *
 * @returns {Object} Cloned element object
 */
ve.dm.Model.prototype.getClonedElement = function () {
	return ve.copy( this.element );
};

/**
 * Get the hash object of the linear model element.
 *
 * The actual logic is in a static function as this needs
 * to be accessible from ve.dm.Converter
 *
 * This is a custom hash function for OO#getHash.
 *
 * @method
 * @returns {Object} Hash object
 */
ve.dm.Model.prototype.getHashObject = function () {
	return this.constructor.static.getHashObject( this.element );
};
