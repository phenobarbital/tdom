<?php
/**
 *
 * tdom_xml extiende a DOMDocument para incluirle mas capacidades
 * Tomates DOM XML extension class
 * @access public
 **/
class tdom_xml extends DOMDocument  implements Iterator, Countable {

	#procesador xslt
	protected $_proc = null;
	#hoja de estilo XSLT
	protected $_xsl = null;

	#xpath del documento
	protected $_xpath = null;

	#tabla de traduccion de entidades html:
	protected $_entities = array('á'=>'&aacute;', 'Á'=>'&Aacute;', 'é'=>'&#233;', 'É'=>'&Eacute;', 'í'=>'&iacute;', 'Í'=>'&Iacute;', 'ó'=>'&oacute;', 'Ó'=>'&Oacute;', 'ú'=>'&uacute;', 'Ú'=>'&Uacute;', 'ñ'=>'&ntilde;', '©'=>'&copy;', '®'=>'&reg;', 'Ñ'=>'&Ntilde;',  '¿'=>'&iquest;', '¡'=>'&iexcl;', '@'=>'&#64;');	

	//atributos:
	protected $_dom_type = 'xml';
	protected $_mime = 'application/xml';

	#nombre del nodo base
	protected $_base_node = '';
	//nodo raiz del elemento
	protected $_root = '';

	protected $_dom_version = '1.0';
	protected $_encoding = 'UTF-8';
	protected $_dtd = '';
	#espacio xml
	public $_namespace = null;
	protected $_public = null;
	protected $_qname = null;

	//nodo para iterar
	protected $_node = null;

	#para la extension de las clases DOM:
	protected $_node_class = 'tdom_node';
	protected $_element_class = 'tdom_element';
	protected $_text_class = 'tdom_text';

	#permitir validacion via DTD
	protected $use_dtd = false;
	protected $validate_dtd = false;

	/**
	 * construye un documento xml
	 *
	 */
	public function __construct() {
		#version por defecto, 1.0
		$this->_dom_version = XML_VERSION;
		#encoding por defecto, utf-8
		$this->_encoding = XML_ENCODING;
		#convoco al constructor de DOMDocument
		parent::__construct($this->_dom_version, $this->_encoding);
		libxml_use_internal_errors(true);
		$this->_register_classes();
		#defino las propiedades del elemento DOM
		$this->_setProperties();
		#gestor de busquedas xpath
		$this->_xpath = new DOMXPath($this);
		if (class_exists('XSLTProcessor', false)) {
			$this->_proc = new XSLTProcessor();
		}
		#ahora, creo el nodo base si fue definido asi:
		if ($this->_base_node != '') {
			$this->createBase();
			#registro el espacio de nombres para las busquedas XPATH
			$this->_xpath->registerNamespace($this->_base_node, $this->_namespace);
		}
	}

	public function createBase() {
		if ($this->use_dtd == true) {
			// Creates an instance of the DOMImplementation class
			$imp = new DOMImplementation();
			/*
			TODO: Debe existir una mejor forma de cargar un DTD a un DOMDocument
			*/
			$dtd = $imp->createDocumentType($this->_base_node, $this->_public, $this->_dtd);
			$dom = $imp->createDocument($this->_namespace, $this->_qname, $dtd);
			$dom->encoding = XML_ENCODING;
			$dom->appendChild(new DOMElement($this->_base_node, null, $this->_namespace));
			#creo el nodo raiz
			$this->root($this->_base_node, $this->_namespace);
			$this->validateOnParse = false;
			$this->loadXML($dom->saveXML(), LIBXML_NONET|LIBXML_DTDLOAD|LIBXML_DTDVALID);
			$this->update(); //Normalizo y valido el documento (de ser posible)
			//no necesito gastar memoria en este objeto dom
			unset($dom);
			return true;
		} else {
			#creo el nodo raiz
			$this->root($this->_base_node, $this->_namespace);
			return true;
		}
	}

	public function setDTD($dtd = '', $local = false) {
		if ($local) {
			#esto causa que la validacion sea local
			$this->_dtd = TDOM_URI . $dtd;
		} else {
			$this->_dtd = $dtd;
		}
		return $this;
	}

	public function useDTD($val = true) {
		$this->use_dtd = $val;
		return $this;
	}

	public function setPublic($public = '') {
		$this->_public =$public;
		return $this;
	}

	public function setType($type = '') {
		if ($type) {
			$this->_base_node = $type;
		}
		return $this;
	}

	public function setNamespace($name = '') {
		$this->_namespace = $name;
		return $this;
	}

	#para actualizar los nodos principales del html
	protected function update() {
		if ($this->validate_dtd == true) {
			//es requerido validar para poder usar getElementById
			$this->validate();
		}
	}

	/**
	 * me permite personalizar y extender la clase DOM de PHP
	 * @version 0.1.2 para PHP > 5.2.3
	 */
	protected function _register_classes() {
		$version = explode('.', phpversion());
		if (($version[1]>=2) && ($version[2] > 3)) {
			#registro la clase base (document)
			parent::RegisterNodeClass('DOMDocument', null);
			parent::RegisterNodeClass('DOMDocument', get_class($this));
			#registro la clase NODO base
			parent::registerNodeClass('DOMNode', $this->_node_class);
			#registro la clase que gestionara los elementos xml:
			parent::registerNodeClass('DOMElement', $this->_element_class);
			#registro la clase que gestionará los DOMText:
			parent::registerNodeClass('DOMText', $this->_text_class);
		} else {
			#Tomates DOM requiere php 5.2.1 o superior
			throw new exception('Error: Tomates DOM requiere 5.2.1 o superior');
		}
	}

	/**
	 * define las propiedades basicas de un documento XML
	 */
	protected function _setProperties() {
		#analizar el documento al cargar
		$this->validateOnParse = true;
		#formatea la salida, colocando los nodos de manera tabulada
		$this->formatOutput = XML_FORMAT;
		#convierte todo caracter html a entidad xhtml (ej. ñ => &ntilde;)
		$this->substituteEntities = true;
		#limpia cualquier espacio en blanco innecesario
		$this->preserveWhiteSpace = false;
		#intenta reparar etiquetas mal formadas o ausentes
		$this->normalizeDocument = true;
		#hace que el parser sea estricto en el chequeo del archivo
		$this->strictErrorChecking = true;
		#define el encoding del documento
		$this->encoding = XML_ENCODING;
	}

	/**
	 * retorna no define la raiz del documento
	 *
	 * @param string $name
	 * @param string $namespace uri namespace
	 * @return tdom_element node root
	 */
	public function root($name='', $namespace = '') {
		if ($name=='') {
			$name = $this->_root;
		}
		if ($this->documentElement) {
			$node = $this->appendChild(new $this->_element_class($name, null, $namespace));
			$node->copyNodes($this->documentElement);
			#ahora elimino el actual elemento raiz:
			$this->replaceChild($node , $this->documentElement);
			return $this->documentElement;
		} else {
			return $this->appendChild(new $this->_element_class($name, null, $namespace));
		}
	}

	/**
	 * devuelve la base del documento
	 *
	 * @return tdom_xml_element element
	 */
	public function base() {
		return $this->documentElement;
	}

	/**
	 * crea un nodo en el elemento raiz actual
	 *
	 * @param string $node_name
	 * @param string $value optional
	 * @param string $uri namespace
	 * @return unknown
	 */
	public function createNode($node_name, $value = null, $uri = null) {
		//var_dump($this->_element_class);
		$element = new $this->_element_class($node_name, $value, $uri);
		return $this->documentElement->appendNode($element);
	}

	/**
	 * Permite crear instrucciones de procesado, como xsl-stylesheet
	 *
	 * @param string $target
	 * @param string $data
	 * @return DOMNode node
	 */
	public function createInstruction($target, $data = null) {
		$element = $this->createProcessingInstruction($target, $data);
		return $this->appendChild($element);
	}

	/**
	 * Permite crear secciones CDATA
	 *
	 * @param string $data
	 * @return DOMNode node
	 */
	public function createDATA($data) {
		$element = $this->createCDATASection($data);
		return $this->appendChild($element);
	}

	/**
	 * crea un nodo en el documento:
	 *
	 * @param string $nodename
	 * @param string $value optional
	 * @param string $uri namespace
	 * @return tdom_element node
	 */
	public function create($nodename, $value = null, $uri = null) {
		return $this->createNode($nodename, $value, $uri);
	}

	// --- visualizacion del documento
	
	/**
	 * Retorna el tipo MIME declarado para el documento
	 *
	 * @return string $mime
	 */
	public function mime() {
		return $this->_mime;
	}

	/**
	 * valida el documento actual
	 *
	 * @return boolean validez del documento
	 */
	public function validate() {
		if ($this->validate_dtd == true) {
			return @parent::validate();
		} else {
			return true;
		}
	}

	// --- ubicacion de objetos:

	/**
	 * retorna un nodo especificado como nodo dom:
	 *
	 * @param string $name
	 * @return tdom_element
	 * @uses No se debe usar get cuando el documento NO es valido
	 */
	public function get($name) {
		if ($name) {
			$node = $this->getElementById($name);
			if ($node) {
				return $node;
			} else {
				throw new exception("TDOM_XML: Debe especificar un nombre de nodo valido: {$name} no existe");
				return false;
			}
		} else {
			throw new exception("TDOM_XML: debe especificar un nombre de nodo valido");
		}
	}

	/**
	 * un get basado en xpath (es mas lento que getElementById pero se puede usar en cualquier lado)
	 *
	 * @param string $name
	 * @return tdom_element node
	 */
	public function getXpath($name) {
		if ($name) {
			$node = $this->_xpath->query("//*[@id='{$name}']");
			if ($node) {
				return $node;
			} else {
				throw new exception("TDOM XML: Debe especificar un nombre de nodo valido: {$name} no existe");
				return false;
			}
		} else {
			throw new exception("TDOM XML: debe especificar un nombre de nodo valido");
		}
	}
	public function getID($name) {
		return $this->getXpath($name);
	}

	/**
	 * hace una consulta xpath sobre el arbol actual
	 *
	 * @param string $q consulta
	 * @param string $item
	 * @return tdom_element node
	 */
	public function query($q, $item = null) {
		if ($q) {
			$node = $this->_xpath->query($q);
		}
		if ($node){
			if ($item) {
				return $node->item($item);
			} elseif ($node->length == 1) {
				#como no hay 2 items, devuelvo el unico que existe
				return $node->item(0);
			} else {
				#retorno una serie de items, como elementos dom_element
				return $node;
			}
		} else {
			return false;
		}
	}

	/**
	 * hace una busqueda basada en tokens XPATH
	 *
	 * @param string $query
	 * @param string $item
	 * @return tdom_element @node
	 */
	public function find($query, $item = null) {
		#primero, separar en tokens el query:
		$k = explode(' ', $query);
		if (!count($k)) {
			trigger_error('Consulta mal formada, Error de consulta XPATH');
			return false;
		}
		$str = $k[0];
		$items = array();
		if (preg_match("/^[A-Za-z0-9]+$/", $str, $items)) { //se trata de un tag element:
			$q = '//';
			$q.= $items[0];
		} elseif(preg_match("/^[#]+[A-Za-z0-9]+$/", $str, $items)) { //se trata de un id:
			$name = str_replace('#', '', $items[0]);
			$q = "//*[@id='{$name}']";
		}
		echo $q;
		if ($q) {
			$node = $this->_xpath->query($q);
		}
		if ($node){
			if ($item) {
				return $node->item($item);
			} elseif ($node->length == 1) {
				#como no hay 2 items, devuelvo el unico que existe
				return $node->item(0);
			} else {
				#retorno una serie de items, como elementos dom_element
				return $node;
			}
		} else {
			return false;
		}
	}

	/**
	 * retorna uno o una coleccion de elementos xml en forma de nodos DOM
	 *
	 * @param string $name
	 * @param string $item
	 * @return tdom_element node
	 */
	public function element($name, $item = null) {
		$n = null;
		if ($name) {
			$node = $this->getElementsByTagName($name);
			if (!empty($node) && $node->length > 0){
				if (is_null($item)) {
					if ($node->length == 1) {
						#como no hay 2 items, devuelvo el unico que existe
						$n = $node->item(0);
					} else {
						#hay muchos nodos, devuelvo el nodelist
						$n = $node;
					}
				} else {
					switch($item) {
						case 0:
						case '':
							$n = $node->item(0);
							break;
						default:
							$n = $node->item($item);
					}
				}
				return $n;
			} else {
				return false;
			}
		} else {
			throw new exception('TDOM XML: debe especificar un nombre de elemento');
		}
	}

	/**
	 * Permite hacer busquedas de elementos definiendo el namespace
	 *
	 * @param string $name
	 * @param string $uri namespace
	 * @param integer $item
	 * @return tdom_node DOMNodelist
	 */
	public function elementNS($name, $uri = '', $item = null) {
		$n = null;
		$this->validate();
		if ($name) {
			$node = $this->getElementsByTagNameNS('*', $name);
			if (!empty($node) && $node->length > 0) {
				if (is_null($item)) {
					if ($node->length == 1) {
						#como no hay 2 items, devuelvo el unico que existe
						$n = $node->item(0);
					} else {
						#hay muchos nodos, devuelvo el nodelist
						$n = $node;
					}
				} else {
					switch($item) {
						case 0:
						case '':
							$n = $node->item(0);
							break;
						default:
							$n = $node->item($item);
					}
				}
			}
			return $n;
		} else {
			return false;
		}
	}

	#manipulacion de nodos:

	/**
	 * remueve un nodo existente en el documento actual
	 *
	 * @param DOMNode $node
	 * @return DOMNode nodo eliminado
	 */
	public function removeNode($node) {
		$parent = $node->parentNode;
		return $parent->removeChild($node);
	}

	/**
	 * remueve un nodo a partir de su nombre
	 *
	 * @param string $name
	 * @param string $item
	 * @return DOMNode nodo eliminado
	 */
	public function delete($name, $item = 0) {
		$deleted = $this->getElementById($name);
		if (!$deleted) {
			$deleted = $this->getElementsByTagName($name)->item($number);
		}
		if ($deleted) {
			$parent = $deleted->parentNode;
			return $parent->removeChild($deleted);
		} else {
			return false;
		}
	}

	/**
	 * reemplaza un nodo por otro
	 *
	 * @param unknown_type $newnode
	 * @param unknown_type $oldnode
	 * @return unknown
	 */
	public function replaceNode($newnode, $oldnode) {
		return $this->replaceChild($newnode, $oldnode);
	}

	/**
	 * permite obtener un elemento (o nodelist) a partir de su nombre
	 *
	 * @param unknown_type $element
	 * @return unknown
	 */
	public function __get($element) {
		return $this->element($element);
	}

	/**
	 * permite obtener un item de un nodelist de elementos usando notacion de funcion
	 *
	 * @param unknown_type $method
	 * @param unknown_type $args
	 * @return unknown
	 */
	public function __call($method, $args) {
		if ($args) {
			#retorna el elemento indicado por args
			$val = $args[0];
		} else {
			$val = null;
		}
		return $this->element($method, $val);
	}

	/**
	 * --- operaciones de visualizacion del documento
	 *
	 * @param boolean $show_dtd
	 * @return string XML
	 */
	protected function _generate($show_dtd = true) {
		if ($show_dtd==true) {
			return $this->saveXML();
		} else {
			//LIBXML_NOCDATA
			return $this->saveXML($this->documentElement, LIBXML_NOXMLDECL+LIBXML_NOCDATA+LIBXML_NOBLANKS+LIBXML_NSCLEAN|LIBXML_NONET|LIBXML_DTDLOAD|LIBXML_DTDVALID);
		}
	}

	/**
	 * muestra el documento en pantalla
	 *
	 * @param boolean $show_dtd
	 */
	public function show($show_dtd = true) {
		echo $this->_generate($show_dtd);
	}

	/**
	 * retorna el documento
	 *
	 * @param boolean $show_dtd
	 * @return string XML
	 */
	public function getDocument($show_dtd = true) {
		return $this->_generate($show_dtd);
	}

	/**
	 * da salida por el buffer de php (mas rapido que echo saveXML)
	 * @return salida del documento por la salida standard
	 */
	public function render() {
		$this->save('php://output');
	}

	/**
	 * si mandamos a hacer echo al documento, se devuelve el texto
	 *
	 * @return string document
	 */
	public function __toString() {
		return $this->_generate(true);
	}


	// --- Funciones del iterador

	// --- Un elemento es iterable a traves de todos sus hijos:
	public function count() {
		if ($this->documentElement->hasChildNodes()) {
			return $this->documentElement->childNodes->length;
		} else {
			$this->_node = null;
			return 0;
		}
	}

	#retorna el primer hijo del elemento actual
	public function first() {
		if ($this->documentElement->hasChildNodes()) {
			$this->_node = $this->documentElement->firstChild;
			return $this->_node;
		} else {
			$this->_node = null;
			return false;
		}
	}

	#retorna el ultimo hijo del elemento actual
	public function last() {
		if ($this->documentElement->hasChildNodes()) {
			$this->_node = $this->documentElement->lastChild;
			return $this->_node;
		} else {
			$this->_node = null;
			return false;
		}
	}

	#rebobina el array:
	public function rewind() {
		return $this->first();
	}

	#retorna el anterior hijo desde el actual
	public function previous(){
		if ($this->_node) {
			$this->_node = $this->_node->previousSibling;
			if ($this->_node) {
				return $this->_node;
			} else {
				return null;
			}
		}
	}

	#retorna el siguiente hijo desde el actual nodo
	public function next() {
		if ($this->_node) {
			$this->_node = $this->_node->nextSibling;
			if ($this->_node) {
				return $this->_node;
			} else {
				return null;
			}
		}
	}

	#retorna el nodo actual de la iteracion
	public function current() {
		if ($this->_node) {
			return $this->_node;
		} else {
			$this->_node = null;
			return false;
		}
	}

	#retorna el valor del nodo actual
	public function value(){
		if ($this->_node) {
			return $this->_node->nodeValue;
		} else {
			$this->_node = null;
			return false;
		}
	}

	#retorna el nombre del nodo actual como una clave:
	public function key() {
		if ($this->_item) {
			return $this->_node->nodeName;
		} else {
			$this->_node = null;
			return false;
		}
	}

	public function valid(){
		if ($this->_node) {
			return true;
		} else {
			$this->_node = null;
			return false;
		}
	}

	# --- end of iterator functions


	// --- operaciones de carga:

	/**
	 * operacion para cargar desde un archivo o recurso URL
	 *
	 * @param string $filename
	 * @param boolean $xinclude procesa las directivas xinclude
	 * @return boolean
	 */
	public function load($filename, $xinclude = false) {
		if (is_file($filename)) {
			#creo el parser DOM local
			$dom = $this->createDOM();
			#limpia cualquier espacio en blanco innecesario
			$dom->preserveWhiteSpace = false;
			#intenta reparar etiquetas mal formadas o ausentes
			$dom->normalizeDocument = false;
			#hace que el parser sea estricto en el chequeo del archivo
			$dom->strictErrorChecking = false;
			#indico la base de cualquier posible xinclude
			$dom->documentURI = $filename;
			#lo cargo como xml (puede dar error si el archivo esta mal parseado):
			$dom->load($filename, LIBXML_NOERROR);
			//LIBXML_NOBLANKS+LIBXML_NOENT+LIBXML_NONET+LIBXML_NOCDATA
			if ($xinclude) {
				$dom->xinclude();
			}
			#si ha cargado, entonces lo adjunto al elemento actual
			#ahora si puedo cargarlo como XML valido:
			foreach($dom->childNodes as $node) {
				$doc = $this->importNode($node, true);
				if ($doc) {
					$node = $this->appendChild($doc);
				}
			}
			return true;
		} else {
			return false;
		}
	}

	#gestion de carga de archivos

	#creacion de elementos:
	/**
	* crea una definicion basica de DOM para elementos (x)html
	*
	* @param string $html
	* @return tdom_xml object
	*/
	public function createDOM($html = false) {
		#se crea un DOM implementation
		$dom_imp = new DOMImplementation();
		if ($html) {
			$public = '-//W3C//DTD XHTML 1.0 Strict//EN';
			$dtd = TDOM_BASE . 'include/xhtml1-strict.dtd';
			$namespace = 'http://www.w3.org/1999/xhtml';
			$dom_dtd = $dom_imp->createDocumentType('html', $public, $dtd);
			$dom = $dom_imp->createDocument($namespace, '', $dom_dtd);
			$dom->validateOnParse = false;
		} else {
			$dom_dtd = $dom_imp->createDocumentType('xml');
			#objeto dom
			$dom = $dom_imp->createDocument('', '', $dom_dtd);
			#analizar el documento al cargar
			$dom->validateOnParse = true;
		}
		#formatea la salida, colocando los nodos de manera tabulada
		$dom->formatOutput = true;
		#convierte todo caracter html a entidad xhtml (ej. ñ => &ntilde;)
		$dom->substituteEntities = true;
		#limpia cualquier espacio en blanco innecesario
		$dom->preserveWhiteSpace = false;
		#intenta reparar etiquetas mal formadas o ausentes
		$dom->normalizeDocument = true;
		#hace que el parser sea estricto en el chequeo del archivo
		$dom->strictErrorChecking = true;
		#define el encoding del documento
		$dom->encoding = XML_ENCODING;
		return $dom;
	}

	/**
	 * _loadHTML
	 * Carga, un archivo (x)html solicitado por
	 * las funciones load_html y layout, load_body, load_file, html_helper
	 * @params: $filename ruta al archivo
	 * @return: un objeto DOM conteniendo el archivo
	 */
	public function _loadHTML($filename) {
		if(is_file($filename)) {
			#creo el parser DOM local
			$dom = $this->createDOM(true);
			$buffer = file_get_contents($filename);
			if (is_empty($buffer)) {
				throw new exception("tdom xml: el archivo {$filename} esta vacio");
				return false;
			}
			#traducir los caracteres mas comunes a entidades html
			$buffer = strtr($buffer, $this->_entities);
			#lo cargo como html:
			$dom->loadHTML($buffer);
			#transformo para obtener un codigo limpio:
			$buffer = $dom->saveXML($dom->documentElement, LIBXML_COMPACT+LIBXML_NOBLANKS+LIBXML_NOENT+LIBXML_NOCDATA+LIBXML_NSCLEAN+LIBXML_NOERROR);
			//que gran peo con las CDATA
			$buffer = str_replace("<![CDATA[", "", $buffer);
			$buffer = str_replace("]]>", "", $buffer);
			//y con los retorno de carro de windows:
			$buffer = str_replace('&#13;', '', $buffer);
			$buffer = str_replace('&#xD;', '', $buffer);
			#ahora si puedo cargarlo como XML valido:
			//print_r($buffer);
			if ($dom->loadXML($buffer, LIBXML_COMPACT+LIBXML_NONET)) {
				return $dom;
			} else {
				$archivo = basename($filename);
				throw new exception("tdom xml: El archivo {$archivo} no contiene HTML o ha sido mal formado");
				return false;
			}
		} else {
			$archivo = basename($filename);
			throw new exception("tdom xml: El archivo {$archivo} no existe");
			return false;
		}
	}

	/**
	 * loadXSLT: carga una hoja de estilo XSL para procesar el documento
	 * @author Jesus Lara
	 * @version 0.1
	 * @param string $filename
	 */
	public function loadXSLT($filename) {
		$this->_xsl = new DOMDocument();
		$this->_xsl->load($filename);
		$this->_proc->importStylesheet($this->_xsl);
	}

	// -- funciones de conversion:

	/**
	 * Convierte el documento XML en un arreglo de items
	 *
	 */
	public function toArray() {
	}

	/**
	 * Convierte el documento XML en una estructura JSON
	 *
	 */
	public function toJSON() {
	}

	/**
	 * Emite la salida a documento de la transformacion XSL
	 *
	 * @return mixed doc
	 */
	public function toDOC() {
		if ($this->_xsl) {
			return $this->_proc->transformToDoc($this);
		} else {
			return false;
		}
	}

	#-- xpath related functions

	/**
	 * registra un Namespace para XPATH
	 *
	 * @param unknown_type $prefix
	 * @param unknown_type $space
	 */
	public function xpathNS($prefix, $space) {
		if ($this->_xpath) {
			$this->_xpath->registerNamespace($prefix, $space);
		}
	}

	/**
	 * Consulta XPATH: XQUERY
	 *
	 * @param unknown_type $query
	 * @return unknown
	 */
	public function xquery($query) {
		if ($this->_xpath) {
			$result = $this->_xpath->query($query);
			switch ($result->length) {
				case 0:
					return false;
					break;
				case 1:
					return $result->item(0);
					break;
				default:
					return $result;
			}
		}
	}

	public function xevaluate($eval, $node = null) {
		if ($this->_xpath) {
			$result = $this->_xpath->evaluate($eval, $node);
			return $result;
		}
	}


	// -- operaciones con nodos (array-type)
	// -- indicar el nodo padre del mismo
	public function shift($node) {
	}

	public function unshift($node) {
	}

	public function push($node) {
	}

	public function pop($node) {}
}
?>