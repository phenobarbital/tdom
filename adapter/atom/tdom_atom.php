<?php
/**
 *
 * tdom_atom extiende a tdom_xml para la construccion de archivos de sindicacion atom
 * Tomates DOM ATOM extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_atom extends tdom_xml {

	#espacio xml
	public $_namespace = 'http://www.w3.org/2005/Atom';

	#namespaces opcionales
	protected $_ns_rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	protected $_ns_sy = 'http://purl.org/rss/1.0/modules/syndication/';

	protected $_public = '';

	#tipo de documento (xml)
	protected $_dom_type = 'xml';

	#nombre del nodo base
	protected $_base_node = 'feed';

	/**
	 * objeto base svg
	 *
	 * @var tdom_element
	 */
	protected $_base = null;

	public function __construct() {
		parent::__construct();
		$this->_base = $this->element('feed');
		$this->_base->setAttribute('version', '1.0');
		#defino los namespaces opcionales:
		#rdf
		$this->_base->setAttribute('xmlns:rdf', $this->_ns_rdf);
		#syndicalization
		$this->_base->setAttribute('xmlns:sy', $this->_ns_sy);
		#creo los elementos basicos de un feed Atom
		$this->properties();
	}

	protected function properties() {
		$this->_base->create('title', '');
		$this->_base->create('subtitle', '');
		$this->_base->create('link', '');
		#atom feed
		$this->_base->create('link')->value('')->attribute('rel', 'self');
		#id
		$this->_base->create('id');
		#descripcion
		$this->_base->create('description', '');
		$this->_base->create('language', XML_LANG);
		$this->_base->create('updated', date(DATE_ATOM));
		$this->_base->create('generator', 'TDOM Atom Generator');
		$this->_base->create('sy:updatePeriod', 'hourly', $this->_ns_sy);
		$this->_base->create('sy:updateFrequency', '1', $this->_ns_sy);
		#autor:
		$autor = $this->_base->create('author');
		$autor->create('name');
		$autor->create('email');
		$this->validate();
	}

	public function title($title = '') {
		$this->_base->getElementsByTagName('title')->item(0)->value($title);
		return $this;
	}

	/**
	 * Definicion del atom feed URI
	 *
	 * @param string $uri
	 */
	public function feed($uri = '') {
		$links = $this->_base->getElementsByTagName('link');
		foreach($links as $link) {
			/*
			if ($link->hasAttribute('rel')) {
				$link->attribute('href', $uri);
			}
			*/
		}
		return $this;
	}

	public function link($uri = '') {
		$this->_base->getElementsByTagName('link')->item(0)->attribute('href', $uri);
		return $this;
	}

	public function author($name, $email) {
		$autor = $this->_base->element('author');
		$autor->element('name')->value($name);
		$autor->element('email')->value($email);
		return $this;
	}

	// -- gestion de items
	public function createEntry($title = '', $uri = '', $desc = '') {
		$item = $this->_base->create('entry');
		$item->create('title', $title);
		$item->create('link', $uri);
		$item->create('summary')->value($desc);
		$item->create('id', $uri);
		$item->create('updated', date(DATE_ATOM));
		return $item;
	}


}
?>