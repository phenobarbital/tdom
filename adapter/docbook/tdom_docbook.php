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
	
	public function __construct($type = 'book') {
		#defino el doctype
		$this->setDTD($this->_dtd, false);
		#defino el public:
		$this->setPublic($this->_public);
		#defino el tipo de documento:
		$this->_base_node = $type;
		parent::__construct();
		$this->standalone = 'no';
		$this->_base = $this->element($type);
		$this->_base->setAttribute('lang', XML_LANG);
		#crear las partes básicas del docbook:
		$this->_title = $this->_base->create('title', '-');
		$this->_author = $this->_base->create('author', 'TDOMv ' . TDOM_VERSION);
		#creo el prefacio
		$this->_preface = $this->_base->create('preface');
		$this->_preface->id('preface');
		#el titulo del prefacio:
		$this->_preface->create('title', 'preface');
		#creo un capitulo:	
		$this->_firstchap = $this->createChapter('intro', '');
	}
	
	public function createChapter($id = '', $title = '') {
		$chapter = $this->_base->create('chapter');
		$chapter->id($id);
		$chapter->create('title')->value($title);
		return $chapter;
	}
	
	public function title($title = '') {
		$this->_title->value($title);
		return $this;
	}
	
	public function author($author = 'TDOM') {
		$this->_author->value($author);
		return $this;
	}

}
?>