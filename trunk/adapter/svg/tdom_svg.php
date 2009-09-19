<?php
/**
 *
 * tdom_svg extiende a tdom_xml para la construccion de graficos SVG
 * Tomates DOM SVG extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_svg extends tdom_xml {
	
	#dtd SVG
	public $_dtd = 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd';
	
	#espacio xml
	public $_namespace = 'http://www.w3.org/2000/svg';
	protected $_public = '-//W3C//DTD SVG 1.1//EN';
	
	#espacio de otras definiciones:
	protected $_ns_rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	protected $_ns_xlink = 'http://www.w3.org/1999/xlink';
	protected $_ns_sodipodi = 'http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd';
		
	#tipo de documento (xml)
	protected $_dom_type = 'xml';

	#nombre del nodo base
	protected $_base_node = 'svg';

	/**
	 * objeto base svg
	 *
	 * @var tdom_element
	 */
	protected $_base = null;	
	
	#permitir validacion via DTD
	protected $use_dtd = true;
	protected $validate_dtd = false;
		
	public function __construct($id = 'svg1', $width = '100px', $height = '100px') {
		#defino el doctype
		$this->setDTD($this->_dtd, false);
		#defino el public:
		$this->setPublic($this->_public);
		parent::__construct();
		$this->standalone = 'no';
		$this->_base = $this->svg();
		$this->_base->id($id)->viewBox('0 0 1 1');
		#namespace HTML
		$this->_base->setAttribute('xmlns:html', 'http://www.w3.org/1999/xhtml');
		#rdf
		$this->_base->setAttribute('xmlns:rdf', $this->_ns_rdf);
		#xlink
		$this->_base->setAttribute('xmlns:xlink', $this->_ns_xlink);
		#sodipodi
		$this->_base->setAttribute('xmlns:sodipodi', $this->_ns_sodipodi);
		#ingreso los atributos mas comunes de un SVG
		$this->_base->setAttribute('sodipodi:version', '0.32');
		#atributo de la version de SVG
		$this->_base->setAttribute('version', '1.1');
		#defino el ancho y alto del grafico
		$this->width($width)->height($height);
		#creo un titulo
		$this->_base->create('title', '');
		#creo una descripcion:
		$this->_base->create('desc', '');
		
	}
	
	/**
	 * Retorna el grafico base actual
	 *
	 * @return tdom_element svg
	 */
	public function svg() {
		return $this->element('svg');
	}
	
	/**
	 * ancho del grafico
	 *
	 * @param string $width
	 * @return self $this
	 */
	public function width($width = '100px') {
		$this->_base->setAttribute('width', $width);
		return $this;
	}
	
	/**
	 * alto del grafico
	 *
	 * @param string $height
	 * @return self $this
	 */
	public function height($height = '100px') {
		$this->_base->setAttribute('height', $height);
		return $this;
	}
	
	public function title($title = '') {
		$this->_base->element('title')->value($title);
		return $this;
	}
	
	/**
	 * Descripcion del grafico
	 */
	public function description($desc = '') {
		$this->element('desc')->value($desc);
		return $this;
	}
	
	public function viewBox($viewbox = '0 0 1 1') {
		$this->_base->setAttribute('viewBox', $viewbox);
		return $this;
	}

}
?>