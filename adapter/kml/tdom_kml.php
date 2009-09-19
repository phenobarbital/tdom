<?php
//
/*
 example:
 <?xml version="1.0" encoding="UTF-8"?>
 <kml xmlns="http://www.opengis.net/kml/2.2">
 <Placemark>
 <name>New York City</name>
 <description>New York City</description>
 <Point>
 <coordinates>-74.006393,40.714172,0</coordinates>
 </Point>
 </Placemark>
 </kml>

 */

/**
 *
 * tdom_kml extiende a tdom_xml para la construccion de bloques de definicion geografica KeyHole Markup Language
 * Tomates DOM KML extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_kml extends tdom_xml {

	#espacio xml
	public $_namespace = 'http://www.opengis.net/kml/2.2';

	protected $_public = '';

	#tipo de documento (xml)
	protected $_dom_type = 'xml';
	protected $_mime = 'application/vnd.google-earth.kml+xml';

	#nombre del nodo base
	protected $_base_node = 'kml';

	/**
	 * objeto base kml
	 *
	 * @var tdom_element
	 */
	protected $_base = null;
	
	#personalizacion del DOM element class
	protected $_element_class = 'tdom_kml_element';	

	public function __construct() {
		include_once 'tdom_kml_element.php';
		parent::__construct();
		$this->_base = $this->root('kml', $this->_namespace);
	}
}
?>