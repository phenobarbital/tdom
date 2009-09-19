<?php
 /**
  *
  * tdom_element
  * Tomates DOMElement extension class
  * @access public
  **/
class tdom_element extends DOMElement implements Iterator, Countable {
    
    #nombre de la clase elemento:
    protected $_classname = '';
    #usable para iterar sobre todos los hijos
  	protected $_item = null;
  	#tabla de traduccion de entidades html:
  	protected $_entities = array('á'=>'&aacute;', 'Á'=>'&Aacute;', 'é'=>'&#233;', 'É'=>'&Eacute;', 'í'=>'&iacute;', 'Í'=>'&Iacute;', 'ó'=>'&oacute;', 'Ó'=>'&Oacute;', 'ú'=>'&uacute;', 'Ú'=>'&Uacute;', 'ñ'=>'&ntilde;', '©'=>'&copy;', '®'=>'&reg;', 'Ñ'=>'&Ntilde;',  '¿'=>'&iquest;', '¡'=>'&iexcl;', '@'=>'&#64;');

  	#contiene el xpath del nodo actual
  	protected $_nodepath = null;
    
  	public function __construct($name, $text=null, $namespace = null){
        $this->_classname = get_class($this);
  		$value = htmlentities($text, null, XML_ENCODING);
  		parent::__construct($name, $value, $namespace);
  		$this->_nodepath = $this->getNodePath();
  	}
    
  	/**
  	 * crea una definicion basica de DOM para elementos xml
  	 *
  	 * @return DOMDocument
  	 */
  	public function createDOM() {
  		#se crea un DOM implementation
  		$dom_imp = new DOMImplementation();
  		$dom_dtd = $dom_imp->createDocumentType('xml');
  		#objeto dom
  		$dom = $dom_imp->createDocument('', '', $dom_dtd);
  		#analizar el documento al cargar
  		$dom->validateOnParse = true;
  		#formatea la salida, colocando los nodos de manera tabulada
  		$dom->formatOutput = true;
  		#convierte todo caracter html a entidad xhtml (ej. ñ => &ntilde;)
  		$dom->substituteEntities = true;
  		#limpia cualquier espacio en blanco innecesario
  		$dom->preserveWhiteSpace = false;
  		#intenta reparar etiquetas mal formadas o ausentes
  		$dom->normalizeDocument = true;
  		#trata de recuperar los fallos del archivo actual
  		$dom->recover = true;
  		#hace que el parser sea estricto en el chequeo del archivo
  		$dom->strictErrorChecking = true;
  		#define el encoding del documento
  		$dom->encoding = XML_ENCODING;
  		return $dom;
  	}
    
    /**
     * retorna el documento propietario del elemento actual
     *
     * @return DOMDocument
     */
    public function document() {
        return $this->ownerDocument;
    }
    
    // -- insercion y creacion de objetos:
    
    /**
     * crea un elemento dentro del tdom_element actual
     *
     * @param string $node
     * @param string $text
     * @param string $uri namespace
     * @param boolean $before
     * @return tdom_element node
     */
  	public function create($node, $text = null, $uri = null, $before = false) {
  		$value = htmlentities($text, null, XML_ENCODING);
        if ($this->_classname == '') {
            $this->_classname = get_class($this);
        }
  		if ($before) {
  			return $this->insertBefore(new $this->_classname($node, $value, $uri), $this->childNodes->item(0));
  		} else {
  			return $this->appendChild(new $this->_classname($node, $value, $uri));
  		}
  	}
    
  	/**
  	 * adjunta un nodo ya existente
  	 *
  	 * @param DOMNode $node
  	 * @param unknown_type $before
  	 * @return unknown
  	 */
  	public function appendNode(DOMNode $node, $before = false) {
  		if ($before) {
  			return $this->insertBefore($node);
  		} else {
  			return $this->appendChild($node);
  		}
  	}
    
  	/*
  	TODO: hacer que la funcion permita:
  	1. adjuntar nodos en cualquier parte (no solo al final)
  	2. que se pueda seguir trabajando el nodo luego de agregado
  	3. Mejorar el append (simplificar el proceso)
  	*/
  	
  	/**
  	 * adjunta un nodo existente (y todos sus hijos) al nodo actual
  	 *
  	 * @param unknown_type $node
  	 * @param unknown_type $before
  	 * @return unknown
  	 */
  	public function append($node, $before = false) {
  		//si el metodo build existe, deberia ejecutarlo
  		if (method_exists($node, '_build')) {
  			$node->_build();
  		}
  		#si tiene alguna definicion javascript que ejecutar al arranque:
  		if (isset($node->_onload)) {
  			#si la cadena ya esta, para que seguir buscando?
  			if ($node->_onload) {
  				if (strpos($this->ownerDocument->_onload, $node->_onload)===false) {
  					$this->ownerDocument->_onload.= $node->_onload;
  				}
  			}
  		}
  		if ($node instanceOf DOMDocument) {
  			#procesar cualquier head encontrado
  			if ($node->element('head')) {
  				$head = $node->element('head')->item(0);
  				#procesar lo que contenga head
  				if ($head->hasChildNodes()) {
  					$nodos = $head->childNodes;
  					foreach($nodos as $nodo) {
  						#son los unicos tipos de tags que voy a importar:
  						if ($nodo->tagName=='script') {
  							$ruta = $nodo->getAttribute('src');
  							if(!in_array($ruta, $this->ownerDocument->_scripts)) {
  								$element = $this->ownerDocument->importNode($nodo, true);
  								$element = $this->ownerDocument->getElementsByTagName('head')->item(0)->appendChild($element);
  							}
  						} elseif ($nodo->tagName=='link') {
  							$ruta = $nodo->getAttribute('href');
  							if(!in_array($ruta, $this->ownerDocument->_styles)) {
  								$element = $this->ownerDocument->importNode($nodo, true);
  								$element = $this->ownerDocument->getElementsByTagName('head')->item(0)->appendChild($element);
  							}
  						} elseif ($nodo->tagName=='style') {
  							$element = $this->ownerDocument->importNode($nodo, true);
  							$element = $this->ownerDocument->getElementsByTagName('head')->item(0)->appendChild($element);
  						}
  					}
  				}
  				if (isset($node->_scripts)) {
  					if (is_array($node->_scripts) && count($node->_scripts) > 0) {
  						$a = array_merge($this->ownerDocument->_scripts, $node->_scripts);
  						$this->ownerDocument->_scripts = array_unique($a);
  					}
  				}
  				if (isset($node->_styles)) {
  					if (is_array($node->_styles) && count($node->_styles) > 0) {
  						$a = array_merge($this->ownerDocument->_styles, $node->_styles);
  						$this->ownerDocument->_styles = array_unique($a);
  					}
  				}
  				#remover head
  				$parent = $head->parentNode;
  				$parent->removeChild($head);
  			}
  			#procesar cualquier body encontrado:
  			$body = $node->element('body');
  			if (is_object($body)) {
  				$body = $body->item(0);
  				$nodos = $body->childNodes;
  				foreach($nodos as $nodo) {
  					$n = $this->ownerDocument->importNode($nodo, true);
  					if ($before == false) {
  						$n = $this->appendChild($n);
  					} else {
  						$n = $this->insertBefore($n, $this->childNodes->item(0));
  					}
  				}
  				#remover head
  				$parent = $body->parentNode;
  				$node->documentElement->removeChild($body);
  			}
  			#ahora, todos aquellos que estan en documentElement
  			$nodos = $node->documentElement->childNodes;
  			foreach($nodos as $nodo) {
  				$n = $this->ownerDocument->importNode($nodo, true);
  				if ($before == false) {
  					$n = $this->appendChild($n);
  				} else {
  					$n = $this->insertBefore($n, $this->childNodes->item(0));
  				}
  			}
  			$this->ownerDocument->validate();
  			return $n;
  		} elseif ($node instanceOf DOMNode) {
  			$nodo = $this->ownerDocument->importNode($node, true);
  			if ($before == false) {
  				$nodo = $this->appendChild($node);
  			} else {
  				$nodo = $this->insertBefore($node, $this->childNodes->item(0));
  			}
  			$this->ownerDocument->validate();
  			return $nodo;
  		} else {
  			throw new exception('tdom element: No puedo adjuntar dicho objeto, nodo DOM invalido');
  			return false;
  		}
  	}    
    
  	// --- metodo generico add, permite adjuntar cualquier cosa (simplemente evalua y delega)
  	public function add($data, $is_file = false) {

  	}
  	
  	// --- metodo attach, permite adjuntar y elemento especial html (div, img, tables, forms, form elements, widgets, etc)
  	public function attach($data = null, $before = false) {

  	}

  	#crea y adjunta un nodo ya existente
  	public function appendElement($name) {
  		return $this->appendChild(new $this->_classname($name));
  	}
    
  	/**
  	 * importar un nodo en el objeto actual
  	 *
  	 * @param unknown_type $node
  	 * @param unknown_type $deep
  	 * @return unknown
  	 */
  	public function import($node, $deep = true) {
  		$class = get_class($node);
  		if ($class=='tdom_element' || get_parent_class($node)=='tdom_element') {
  			#es un elemento, simplemente le hago append al elemento actual
  			$n = $this->appendChild($node);
  		} else {
  			$n = $this->ownerDocument->importNode($node, $deep);
  			$n = $this->appendChild($n);
  		}
  		return $n;
  	}

  	#copia todos los nodos de un elemento al actual
  	public function copyNodes($node) {
  		if ($node) {
  			//Now copy the child nodes
  			foreach ($node->childNodes as $item) {
  				$n = $item->cloneNode(true);
  				$n = $this->appendChild($n);
  			}
  		} else {
  			throw new exception('Error de copia de nodos, nodo invalido');
  		}
  	}
  	
    
  	#parent retorna el nodo padre actual, si se especifica un nuevo parent, se mueve este elemento a dicho padre
  	public function parentNode($new_parent = null) {
  		if ($new_parent) {
  			//TODO: Implementar
  		} else {
  			return $this->parentNode;
  		}
  	}
    
    public function parent() {
        return $this->parentNode;
    }

  	//TODO: implementar
  	public function moveNodes($origin) {
  	}

  	#remueve el nodo que se pasa como referencia
  	public function delete(DOMNode $node) {
  		$parent = $node->parentNode;
  		return $parent->removeChild($node);
  	}

  	#retira este nodo del documento actual
  	public function detach() {
  		$parent = $this->parentNode;
  		return $parent->removeChild($this);
  	}
    
  	#clear
  	public function clear() {
  		return $this->clean();
  	}
  	public function clean() {
  		$this->nodeValue = '';
  		return $this;
  	}

  	#valida el documento del nodo actual
  	public function validate() {
  		$this->ownerDocument->validate();
  	}
    
    //ubicar otros items
  	/**
  	 * retorna uno o una coleccion de elementos xml en forma de nodos DOM
  	 *
  	 * @param unknown_type $name
  	 * @param unknown_type $item
  	 * @return unknown
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
  			throw new splib_exception('debe especificar un nombre de elemento');
  		}
  	}
    
  	public function get($name) {
  		if ($name) {
  			$node = $this->ownerDocument->getElementById($name);
  			if ($node) {
  				return $node;
  			} else {
  				throw new exception("Debe especificar un nombre de nodo valido: {$name} no existe");
  				return false;
  			}
  		} else {
  			throw new exception("debe especificar un nombre de nodo valido");
  		}
  	}    
    
    #retorna una busqueda basada en nodos XPATH o aproximaciones
    public function find($expression, $item = null) {
        
    }

  	#crea un nodo texto dentro de un nodo existente
  	public function text($node_text='', $replace = false) {
  		if ($replace) {
  			$this->clear();
  		}
  		$this->appendChild(new tdom_text($node_text));
  		return $this;
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

  	#atributos del nodo:
  	public function getText() {
  		return $this->textContent;
  	}

  	public function nodeName() {
  		return $this->nodeName;
  	}

  	public function nodeValue() {
  		return $this->nodeValue;
  	}

  	public function value($value = ''){
  		$this->nodeValue = $value;
  		return $this;
  	}

  	public function comment($comentario='') {
  		$this->appendChild(new DOMComment($comentario));
  		return $this;
  	}
    
  	#define el nombre del elemento:
  	public function name($name) {
  		$this->setAttribute('name', $name);
  		return $this;
  	}
    
    // -- gestion de atributos
  	#algunos atributos importantes:
  	#crea un atributo dentro de un nodo existente, permite un array de valores
  	public function attribute($name='id', $value='') {
  		if ($name) {
  			if (is_array($name)) {
  				foreach ($name as $n=>$v) {
  					$this->setAttribute($n, $v);
  				}
  			} else {
  				$this->setAttribute($name, $value);
  			}
  			return $this;
  		} else {
  			return false;
  		}
  	}
  	
  	public function attributeNS($name = 'id', $value = '', $uri = '') {
  		if ($name && $uri) {
  			$this->setAttributeNS($uri, $name, $value);
  		} elseif($name) {
  			return $this->attribute($name, $value);
  		} else {
  			return false;
  		}
  	}

  	#elimina un atributo de un nodo
  	public function remove_attribute($name) {
  		if ($this->hasAttribute($name)) {
  			$this->removeAttribute($name);
  		}
  	}

  	public function getAttribute($name) {
  		return parent::getAttribute($name);
  	}

  	#identifica el id del documento
  	public function id($id) {
  		$this->setAttribute('id', $id);
  		$this->setIdAttribute('id', true);
  		$this->validate();
  		return $this;
    }

    public function className($class) {
    	$this->setAttribute('class', $class);
    	return $this;
    }

  	 // --- Un elemento es iterable a traves de todos sus hijos:
  	 public function count() {
  	 	if ($this->hasChildNodes()) {
  	 		return $this->childNodes->length;
  	 	} else {
  	 		$this->_item = null;
  	 		return 0;
  	 	}
  	 }

  	 #retorna el primer hijo del elemento actual
  	 public function first() {
  	 	if ($this->hasChildNodes()) {
  	 		$this->_item = $this->firstChild;
  	 		return $this->_item;
  	 	} else {
  	 		$this->_item = null;
  	 		return false;
  	 	}
  	 }

  	 #retorna el ultimo hijo del elemento actual
  	 public function last() {
  	 	if ($this->hasChildNodes()) {
  	 		$this->_item = $this->lastChild;
  	 		return $this->_item;
  	 	} else {
  	 		$this->_item = null;
  	 		return false;
  	 	}
  	 }

  	 #rebobina el array:
  	 public function rewind() {
  	 	return $this->first();
  	 }
  	 #retorna el anterior hijo desde el actual
  	 public function previous(){
  	 	if ($this->_item) {
  	 		$this->_item = $this->_item->previousSibling;
  	 		if ($this->_item) {
  	 			return $this->_item;
  	 		} else {
  	 			return null;
  	 		}
  	 	}
  	 }

  	 #retorna el siguiente hijo desde el actual nodo
  	 public function next() {
  	 	if ($this->_item) {
  	 		$this->_item = $this->_item->nextSibling;
  	 		if ($this->_item) {
  	 			return $this->_item;
  	 		} else {
  	 			return null;
  	 		}
  	 	}
  	 }

  	 #retorna el nodo actual de la iteracion
  	 public function current() {
  	 	if ($this->_item) {
  	 		return $this->_item;
  	 	} else {
  	 		$this->_item = null;
  	 		return false;
  	 	}
  	 }

  	 #retorna el nombre del nodo actual como una clave:
  	 public function key() {
  	 	if ($this->_item) {
  	 		return $this->_item->nodeName;
  	 	} else {
  	 		$this->_item = null;
  	 		return false;
  	 	}
  	 }

  	 public function valid(){
  	 	if ($this->_item) {
  	 		return true;
  	 	} else {
  	 		$this->_item = null;
  	 		return false;
  	 	}
  	 }

  	 # --- end of iteration functions

  	 //Implementacion de modificadores tipo array:

  	 /**
  	  * inserta un nodo hijo al final del arreglo de nodos
  	  *
  	  * @param tdom_element $node
  	  * @return tdom_element node
  	  */
  	 public function push($node) {
  	 	if ($node instanceOf DOMNode) {
  	 		//verifico primero si tengo hijos, sino, lo agrego normalmente
  	 		if ($this->hasChildNodes) {
  	 			$n = $this->childNodes->length;
  	 			return $this->insertBefore($node, $this->childNodes->item($n));
  	 		} else {
  	 			return $this->append($node);
  	 		}
  	 	}
  	 }
  	 
  	 /**
  	  * inserta un nodo hijo al principio de todos los nodos
  	  *
  	  * @param tdom_element $node
  	  * @return tdom_element
  	  */
  	 public function unshift($node) {
  	 	if ($node instanceOf DOMNode) {
  	 		//verifico primero si tengo hijos, sino, lo agrego normalmente
  	 		if ($this->hasChildNodes) {
  	 			return $this->insertBefore($node, $this->childNodes->item(0));
  	 		} else {
  	 			return $this->append($node);
  	 		}
  	 	}
  	 }
  	 public function insert($node, $index = 0) {
  	 	if ($node instanceOf DOMNode) {
  	 		//verifico primero si tengo hijos, sino, lo agrego normalmente
  	 		if ($this->hasChildNodes) {
  	 			$n = $this->childNodes->length;
  	 			#si $index es mayor que el numero de hijos, va de ultimo
  	 			if ($index > $n) {
  	 				$index = $n;
  	 			} elseif(($index < 0) || (!is_integer($index))) {
  	 				//si es menor que cero, o no es numero, va de ultimo
  	 				$index = 0;
  	 			}
  	 			return $this->insertBefore($node, $this->childNodes->item($index));
  	 		} else {
  	 			return $this->append($node);
  	 		}
  	 	}
  	 }

  	 /**
  	  * retira un nodo de acuerdo a un indice
  	  *
  	  * @param integer $index
  	  * @return DOMNode node
  	  */
  	 public function remove($index = 0) {
  	 	#verifico que tengo hijos, sino no puedo remover nada
  	 	if ($this->hasChildNodes) {
  	 		$n = $this->childNodes->length;
  	 		if (($index > $n) || ($index < 0) || (!is_integer($index))) {
  	 			return false;
  	 		} else {
  	 			$node = $this->childNodes->item($index);
  	 			return $this->removeChild($node);
  	 		}
  	 	} else {
  	 		return false;
  	 	}
  	 }
  	 
  	 /**
  	  * retira y retorna el primer nodo del array de nodos
  	  *
  	  * @return DOMNode nodo
  	  */
  	 public function shift() {
  	 	return $this->remove(0);
  	 }
  	 
  	 /**
  	  * retira y retorna el ultimo nodo hijo
  	  *
  	  * @return DOMNode nodo
  	  */
  	 public function pop() {
  	 	#verifico que tengo hijos, sino no puedo remover nada
  	 	if ($this->hasChildNodes) {
  	 		$n = $this->childNodes->length - 1;
  	 		$this->remove($n);
  	 	} else {
  	 		return false;
  	 	}
     }
     
     
  	// --- metodos magicos para sobre-escribir la creacion de elementos hijos en el elemento actual:

  	/**
  	 * permite sobre-escribir la creacion de atributos
  	 *
  	 * @param string $name
  	 * @param string $value
  	 * @return tdom_element this
  	 */
  	public function __set($name, $value) {
  	 	try {
  	 		$this->setAttribute($name, $value);
  	 	} catch (exception $e) {
  	 		echo $e->getMessage();
  	 	}
  	 	return $this;
  	}

  	/**
  	 * obtiene un atributo
  	 *
  	 * @param string $name
  	 * @return DOMAttribute attribute
  	 */
  	public function __get($name) {
  	 	if ($this->hasAttribute($name)) {
  	 		return $this->getAttribute($name);
  	 	} else {
  	 		return false;
  	 	}
  	}

  	/**
  	 * si pido el objeto, devuelvo su valor interno
  	 *
  	 * @return string element
  	 */
  	public function __toString() {
  	 	return $this->textContent;
  	}

  	// --- metodo magico para sobre-escribir la creacion de atributos en el elemento actual:
  	
  	/**
  	 * generar atributos de la forma $element->attribute('value')
  	 *
  	 * @param string $method nombre de atributo
  	 * @param string $value valor de atributo
  	 * @return tdom_element object
  	 */
  	public function __call($method, $value) {
  	 	if (isset($value[0])) {
  	 		try {
  	 			$this->setAttribute($method, $value[0]);
  	 		} catch (splib_exception $e) {
  	 			$e->exception_error();
  	 		}
  	 	}
  		return $this;
  	}

  	/**
  	 * indica si un elemento posee ese atributo
  	 *
  	 * @param unknown_type $name
  	 * @return unknown
  	 */
  	public function __isset($name) {
  	 	return $this->hasAttribute($name);
  	}

  	/**
  	 * remueve un atributo a traves de unset
  	 */
  	public function __unset($name) {
  	 	$this->removeAttribute($name);
  	}
    
    
  	 //funciones utilitarias con dom element
  	 
  	 /**
  	  * Retorna el elemento actual como etiquetas HTML
  	  *
  	  * @return string HTML
  	  */
  	 public function asHTML() {
		return $this->ownerDocument->saveXML($this, LIBXML_NONET|LIBXML_NOXMLDECL+LIBXML_NOCDATA+LIBXML_NOBLANKS+LIBXML_NSCLEAN);
  	 }
  	 
  	 /**
  	  * Retorna todo el contenido del elemento actual pero sin las etiquetas HTML
  	  *
  	  * @return string text
  	  */
  	 public function asText() {
  	 	return strip_tags($this->asHTML());
  	 }
	 
  	 //TODO: implementar el extract y el move de elementos:
  	 
  	 /**
  	  * retorna una coleccion de nodos basados en un patron
  	  *
  	  * @param string $collection
  	  */
  	 public function extract($collection = '') {

  	 }
     
     
     // -- operaciones de apertura de archivos
     
  	 /**
  	  * load: carga a partir de un archivo xml o un socket
  	  *
  	  * @param unknown_type $filename
  	  * @param unknown_type $xinclude
  	  */
  	 public function load($filename, $xinclude = false) {
  	 	#creo el parser DOM local
  	 	$dom = $this->createDOM();
  	 	#indico la base de cualquier posible xinclude
  	 	$dom->documentURI = $filename;
  	 	#lo cargo como xml (puede dar error si el archivo esta mal parseado):
  	 	$dom->load($filename);
  	 	if ($xinclude) {
  	 		$dom->xinclude();
  	 	}
  	 	#si ha cargado, entonces lo adjunto al elemento actual
  	 	#ahora si puedo cargarlo como XML valido:
  	 	foreach($dom->childNodes as $node) {
  	 		$doc = $this->ownerDocument->importNode($node, true);
  	 		if ($doc) {
  	 			$node = $this->appendChild($doc);
  	 		}
  	 	}
  	 }

  	 /**
  	  * abrir un archivo, lo limpia (mas no convierte entidades) y lo adjunta al nodo actual
  	  *
  	  * @param unknown_type $filename
  	  * @param unknown_type $xinclude
  	  * @return unknown
  	  */
  	 public function open($filename, $xinclude = false) {
  	 	$buffer = $this->open_file($filename);
  	 	if ($buffer) {
  	 		#creo el parser DOM local
  	 		$dom = $this->createDOM();
  	 		#indico la base de cualquier posible xinclude
  	 		$dom->documentURI = $filename;
  	 		#lo cargo como xml (puede dar error si el archivo esta mal parseado:
  	 		$dom->loadXML($buffer, LIBXML_NONET);
  	 		if ($xinclude) {
  	 			$dom->xinclude();
  	 		}
  	 		#transformo para obtener un codigo limpio:
  	 		$buffer = $dom->saveXML($dom->documentElement, LIBXML_NOXMLDECL+LIBXML_NOBLANKS+LIBXML_NOCDATA+LIBXML_NSCLEAN);
  	 		#ahora si puedo cargarlo como XML valido:
  	 		$doc = $this->ownerDocument->createDocumentFragment();
  	 		if ($doc->appendXML($this->clean_data($buffer))) {
  	 			$node = $this->appendChild($doc);
  	 		} else {
  	 			//throw new splib_exception("El archivo {$filename} no contiene XML o ha sido mal formado", SP_WARNING, 'Error al cargar HTML');
  	 			return false;
  	 		}
			#valida el documento, para poder hacer busquedas de nodos luego de cargado:
			$this->ownerDocument->validate();
  	 	}
  	 }
     
     
    #abre un archivo cualquiera y lo adjunta al nodo actual (primero lo ejecuta)
  	 public function file($filename, $replace = false) {
  	 	$this->html($this->buffer_file($filename), $replace);
  	 }
     
  	 /**
  	  * gestiona la apertura del archivo
  	  *
  	  * @param unknown_type $filename
  	  * @return unknown
  	  */
  	 protected function open_file($filename) {
  	 	if(is_file($filename)) {
  	 		$buffer = file_get_contents($filename);
  	 		#traducir los caracteres mas comunes a entidades html
  	 		$buffer = strtr($buffer, $this->_entities);
  	 		return $buffer;
  	 	} else {
            return false;
  	 	}
  	 }

  	 /**
  	  * limpia los documentos mal formados
  	  *
  	  * @param unknown_type $data
  	  * @return unknown
  	  */
  	 protected function clean_data($data) {
  	 	//voy a remover todas las CDATA sections
  	 	$buffer = str_replace("<![CDATA[", "", $data);
  	 	$buffer = str_replace("]]>", "", $buffer);
  	 	//y con los retorno de carro de windows y espacios no reconocidos:
  	 	$buffer = str_replace('&#13;', '', $buffer);
  	 	$buffer = str_replace('&#xD;', '', $buffer);
  	 	return $buffer;
  	 }

  	 /**
  	  * abre un archivo a partir de un buffer de entrada
  	  *
  	  * @param string $filename
  	  * @return string $data
  	  */
  	 protected function buffer_file($filename) {
  	 	if(is_file($filename)) {
  	 		#inicio el buffer de carga:
  	 		ob_start();
  	 		require $filename;
  	 		#obtengo el buffer
  	 		$data = $this->clean_data(ob_get_contents());
  	 		#limpio el buffer y lo retorno:
  	 		ob_end_clean();
  	 		return $data ? $data : null;
  	 	} else {
  	 		throw new exception("El archivo {$filename} no existe");
            return false;
  	 	}
  	 }
}
?>