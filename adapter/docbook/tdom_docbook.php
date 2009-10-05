<?php
/**
 *
 * tdom_docbook extiende a tdom_xml para la construccion de documentos DocBook
 * Tomates DOM DOCBOOK extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_docbook extends tdom_xml {
	#dtd Docbook
	public $_dtd = 'http://www.oasis-open.org/docbook/xml/4.5/docbookx.dtd';

	#espacio xml
	public $_namespace = '';
	protected $_public = '-//OASIS//DTD DocBook XML V4.5//EN';

	#tipo de documento (xml)
	protected $_dom_type = 'xml';
	protected $_mime = 'application/docbook+xml';

	#nombre del nodo base
	protected $_base_node = '';

	#partes basicas de un documento tipo libro
	protected $_title = null;
	protected $_author = null;
	protected $_preface = null;
	#primer capitulo: introduccion:
	protected $_firstchap = null;

	/**
	 * objeto base svg
	 *
	 * @var tdom_element
	 */
	protected $_base = null;

	#permitir validacion via DTD
	protected $use_dtd = true;
	protected $validate_dtd = false;

	#personalizacion del DOM element class
	protected $_element_class = 'docbook_element';

	/**
	 * Permite crear un elemento book
	 *
	 */
	public function __construct($type = 'book') {
		#incluir el archivo de elementos de docbook
		include_once 'tdom_docbook_element.php';
		#defino el doctype
		$this->setDTD($this->_dtd, false);
		#defino el public:
		$this->setPublic($this->_public);
		#defino el tipo de documento:
		$this->_base_node = $type;
		parent::__construct($this->_base_node);
		$this->standalone = 'no';
		$this->_base = $this->element($type);
		$this->_base->setAttribute('lang', XML_LANG);
	}

	/**
	 * Retorna un tipo definido, libro o articulo
	 *
	 * @param string $type (book|article)
	 * @return tdom_docbook $this
	 */
	public function type($type = 'book') {
		if($type == 'book') {
			return new db_book();
		} else {
			return new db_article();
		}
	}


	/**
	 * Retorna la informacion del documento (articleinfo|bookinfo)
	 *
	 * @return tdom_docbook_element $info
	 */
	public function info() {
		if (!$info = $this->getElementsByTagName($this->info)->item(0)) {
			#como no existe, lo creamos y lo retornamos:
			$info = $this->base()->create($this->info);
			#creamos todas las partes basicas de info
			$info->title('Title here');
			#el segmento autor:
			$info->authorgroup()->author('Firstname', 'Surname', 'email@example.com');
			$info->releaseinfo();
			#abstract
			$info->resume('Abstract of document');			
		}
		return $info;
	}

}

class db_article extends tdom_docbook {
	#segmento info
	protected $info = 'articleinfo';

	public function __construct() {
		parent::__construct('article');
	}
}

class db_book extends tdom_docbook {
	/* -- Propiedades del libro */
	#segmento info
	protected $info = 'bookinfo';

	#segmento de partes
	protected $parts = null;

	#capitulos
	protected $chapters = null;

	public function __construct() {
		parent::__construct('book');
	}

	public function chapter($id, $title = '') {
		return $this->base()->chapter($id, $title);
	}

	public function preface($label, $title = '') {
		return $this->base()->preface($label, $title);
	}
	
	public function part($label, $title = '') {
		return $this->base()->part($label, $title);
	}

	public function bibliography($title = '') {
		return $this->base()->bibliography($title);
	}	
}
?>