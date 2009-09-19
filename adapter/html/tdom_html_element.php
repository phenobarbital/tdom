<?php
/**
 *
 * tdom_html_element
 * Tomates DOMElement (x)html elements extension class
 * @access public
 **/
include_once TDOM_BASE . 'adapter/xml/tdom_element.php';
class tdom_html_element extends tdom_element {

	# ---- some basic html specific functions  ----
	#agrega una regla (elemento HR) al body
	public function hr() {
		$this->create('hr');
		return $this;
	}

	#agrega un salto de linea (br) al objeto actual
	function br() {
		$this->create('br');
		return $this;
	}

	public function h($text = '', $level = 1) {
		$name = 'h' . $level;
		return $this->create($name)->text($text);
	}

	public function className($class) {
		$this->setAttribute('class', $class);
		return $this;
	}

	public function append($node, $before = false, $is_file = false) {
		//si el metodo build existe, deberia ejecutarlo
		if (method_exists($node, '_build') && is_callable(array($node, '_build'))) {
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
		$parent = get_parent_class($node);
		//var_dump($parent);
		switch($parent) {
			case 'helper_html_base':
			case 'tdom':
			case 'tdom_document_adapter':
				#se trata de un arbol tdom:
				return $this->_process_node($node, $before);
				break;
			case 'tdom_html_helper':
			case 'tdom_html_form_element':
			case 'tdom_html':
				return $this->_process_node($node, $before);
				break;
			case 'tdom_html_element':
			case 'tdom_element':
			case 'tdom_element_adapter':
				#TODO: adjuntar un elemento aislado:
				$nodo = $this->ownerDocument->importNode($node, true);
				if ($before == false) {
					$nodo = $this->appendChild($node);
				} else {
					$nodo = $this->insertBefore($node, $this->childNodes->item(0));
				}
				$this->ownerDocument->validate();
				return $nodo;
				#se trata de un elemento aislado
				break;
			case 'splib_view_adapter':
				#si es un objeto html, deberia tratar de adjuntar el dom:
				if (get_class($node) == 'splib_view_helper_html') {
					return $this->_process_node($node->dom(), $before);
				}
			default:
				if (is_subclass_of($node, 'tdom_html_helper') || is_subclass_of($node, 'tdom_html_form_element')) {
					return $this->_process_node($node, $before);
				} elseif ($node instanceOf DOMNode) {
					$nodo = $this->ownerDocument->importNode($node, true);
					if ($before == false) {
						$nodo = $this->appendChild($node);
					} else {
						$nodo = $this->insertBefore($node, $this->childNodes->item(0));
					}
					$this->ownerDocument->validate();
					return $nodo;
				}
				#si es string, verifico que sea un archivo, sino es texto HTML
				if (is_string($node)) {
					if ($is_file) {
						$this->file($node);
					} else {
						$this->html($node);
					}
				}
		}
	}

	#procesa el nodo de tipo helper:
	protected function _process_node($node, $before = false) {
		#si es un documento HTML, analizo si posee head o body:
		$head = $node->element('head')->item(0);
		if ($head) {
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
		}
		#si ademas, posee scripts y styles de sus hijos asociados
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
		#luego, debo procesar el body:
		$body = $node->element('body')->item(0);
		if ($body) {
			$nodos = $body->childNodes;
			foreach($nodos as $nodo) {
				$n = $this->ownerDocument->importNode($nodo, true);
				if ($before == false) {
					$n = $this->appendChild($n);
				} else {
					$n = $this->insertBefore($n, $this->childNodes->item(0));
				}
			}
		}
		$this->ownerDocument->validate();
		return $n;
	}
	// --- metodo generico add, permite adjuntar cualquier cosa (simplemente evalua y delega)
	public function add($data, $is_file = false) {
		$this->append($data, false, $is_file);
	}
	

	#procesa una cadena que contenga elementos html y entidades traducibles
	public function html($html, $replace = false) {
		if ($html) {
			$dom = $this->createDOM();
			#traducir los caracteres mas comunes a entidades html
			$data = strtr($html, $this->_entities);
			#cargo el HTML:
			$dom->loadHTML($data);
			#transformo para obtener un codigo limpio:
			if (!defined(LIBXML_NOXMLDECL)) {
				$buffer = $this->clean_data($dom->saveXML($dom->documentElement, LIBXML_NOXMLDECL+LIBXML_NOBLANKS+LIBXML_NOCDATA+LIBXML_NSCLEAN));
			} else {
				$buffer = $this->clean_data($dom->saveXML($dom->documentElement, LIBXML_NOBLANKS+LIBXML_NOCDATA+LIBXML_NSCLEAN));
			}
			#ahora si puedo cargarlo como XML valido:
			if ($dom->loadXML($buffer, LIBXML_NONET)) {
				if ($replace) {
					$this->clear();
				}
				//si el archivo tiene un body; solo obtengo el primer hijo de body en adelante
				$nl = $dom->getElementsByTagName('body')->item(0)->childNodes;
				if ($nl) {
					foreach($nl as $nodes) {
						$node_data = $this->ownerDocument->importNode($nodes, true);
						$node_data = $this->appendChild($node_data);
					}
					#si ademas tiene head:
					$head = $dom->getElementsByTagName('head')->item(0);
					if ($head) {
						if ($head->hasChildNodes()) {
							$h = $this->ownerDocument->getElementsByTagName('head')->item(0);
							foreach($head->childNodes as $node) {
								if(isset($node->tagName)) {
									if ($node->tagName == 'script' || $node->tagName == 'link' || $node->tagName == 'style') {
										$node_data = $this->ownerDocument->importNode($node, true);
										$node_data = $h->appendChild($node_data);
									}
								}
							}
						}
					}
				} else {
					//el primer nodo
					$nl = $dom->documentElement;
					#importo el nodo al objeto actual:
					$node_data = $this->ownerDocument->importNode($nl, true);
					$node_data = $this->appendChild($node_data);
				}
				$this->ownerDocument->validate();
			} else {
				return false;
			}
		}
	}

	#funciona igual que el innerHTML de javascript:
	public function innerHTML($html) {
		$this->html($html, true);
	}

	#abre un archivo que contenga HTML y lo adjunta la nodo actual
	public function openHTML($filename, $replace = false, $dynamic = false) {
		if ($dynamic) {
			$buffer = $this->buffer_file($filename);
		} else {
			$buffer = $this->open_file($filename);
		}
		#luego, carga el contenido como html
		$this->html($buffer, $replace);
	}	
}
?>