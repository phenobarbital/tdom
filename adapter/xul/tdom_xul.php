<?php
/**
 *
 * tdom_xul extiende a tdom_xml para la construccion de interfaces XUL
 * Tomates DOM XUL extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_xul extends tdom_xml {
	
	public $_namespace = 'http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul';
	public $_stylesheet = 'chrome://global/skin/xul.css';
	
	protected $_dom_type = 'xml';
	
	/**
	 * objeto base window
	 *
	 * @var tdom_element
	 */
	protected $_base = null;
	
	#indicando el tipo de documento
	protected $_base_node = '';

	#contenedor de los arrays y scripts del documento
	public $_styles = array();
	public $_scripts = array();
	
	public function __construct($id = 'xulWindow') {
		parent::__construct();
		#defino el stylesheet basico para una ventana XUL
		$this->createInstruction('xml-stylesheet', 'type="text/css" href="chrome://global/skin/"');
		$this->_base = $this->root('window', 'http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul');	
		$this->_base->id($id)->persist('screenX screenY width height sizemode');	
		#atributo para poder contener HTML
		$this->_base->setAttribute('xmlns:html', 'http://www.w3.org/1999/xhtml');
	}
	
	/**
	 * Retorna la ventana actual
	 *
	 * @return tdom_element window
	 */
	public function window() {
		return $this->element('window');
	}
	
	public function title($title = '') {
		$this->_base->attribute('title', $title);
	}
	
	/**
	 * Apertura de un archivo xul
	 */
	public function open($filename) {
		//TODO: abrir archivo XUL
	}
}
?>