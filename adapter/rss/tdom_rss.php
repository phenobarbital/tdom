<?php
/**
 *
 * tdom_rss extiende a tdom_xml para la construccion de archivos de sindicacion rss
 * Tomates DOM RSS extension class
 * @access public
 * @author Jesús Lara <jesuslarag@gmail.com>
 **/
class tdom_rss extends tdom_xml {

	#espacio xml
	public $_namespace = '';

	#namespaces opcionales
	protected $_ns_rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
	protected $_ns_atom = 'http://www.w3.org/2005/Atom';
	protected $_ns_sy = 'http://purl.org/rss/1.0/modules/syndication/';
	protected $_ns_dc = 'http://purl.org/dc/elements/1.1/';
	protected $_ns_content = 'http://purl.org/rss/1.0/modules/content/';

	protected $_public = '';

	#tipo de documento (xml)
	protected $_dom_type = 'xml';

	#nombre del nodo base
	protected $_base_node = 'rss';

	/**
	 * objeto base svg
	 *
	 * @var tdom_element
	 */
	protected $_base = null;

	/**
	 * Canal basico del RSS
	 *
	 * @var tdom_element channel
	 */
	protected $_channel = null;

	public function __construct($uri = '') {
		parent::__construct();
		$this->_base = $this->element('rss');
		$this->_base->setAttribute('version', '2.0');
		#defino los namespaces opcionales:
		#content
		$this->_base->setAttribute('xmlns:content', $this->_ns_content);
		#rdf
		$this->_base->setAttribute('xmlns:rdf', $this->_ns_rdf);
		#dc
		$this->_base->setAttribute('xmlns:dc', $this->_ns_dc);
		#syndicalization
		$this->_base->setAttribute('xmlns:sy', $this->_ns_sy);
		#atom
		$this->_base->setAttribute('xmlns:atom', $this->_ns_atom);
		#creo el canal basico
		$this->_channel = $this->createChannel('channel');
		#validar luego de creado el canal y adjuntado los namespaces
		$this->validate();
	}

	public function createChannel($id = '', $uri = '') {
		$channel = $this->_base->create('channel');
		$channel->id($id);
		$channel->create('title', '');
		$channel->create('link', $uri);
		#atom feed
		$channel->create('atom:link', $uri, $this->_ns_atom)->value($uri);
		#descripcion
		$channel->create('description', '');
		$channel->create('language', XML_LANG);
		$channel->create('pubDate', date(DATE_RFC822));
		$channel->create('lastBuildDate', date(DATE_RFC822));
		$channel->create('generator', 'TDOM RSS Generator');
		$channel->create('webmaster', '<>');
		$channel->create('ttl', '5');
		$channel->create('sy:updatePeriod', 'hourly', $this->_ns_sy);
		$channel->create('sy:updateFrequency', '1', $this->_ns_sy);
		#autor:
		$channel->create('dc:creator', '', $this->_ns_dc);
		return $channel;
	}

	/**
	 * retorna el canal del RSS actual
	 *
	 * @return tdom_element channel
	 */
	public function channel() {
		return $this->_channel;
	}

	public function title($title = '') {
		$this->channel()->element('title')->value($title);
		return $this;
	}

	/**
	 * Definicion del atom feed URI
	 *
	 * @param string $uri
	 */
	public function feed($uri = '') {
		$link = $this->elementNS('link', $this->_ns_atom);
		if ($link) {
			$link->value($uri);
		} else {
			#sino existe, lo creo:
			$this->channel()->create('atom:link', $uri, $this->_ns_atom);
			$this->validate();
		}
		return $this;
	}

	public function link($uri = '') {
		$this->channel()->element('link')->item(0)->value($uri);
		return $this;
	}

	public function author($name, $email) {
		$this->channel()->element('webmaster')->value($email);
		$creator = $this->elementNS('creator', $this->_ns_dc);
		if ($creator) {
			$creator->value($name);
		} else {
			#sino existe, lo creo:
			$this->channel()->create('dc:creator', $name, $this->_ns_dc);
			$this->validate();
		}
		return $this;
	}

	// -- gestion de items
	public function createItem($title = '', $uri = '', $desc = '') {
		$item = $this->channel()->create('item');
		$item->create('title', $title);
		$item->create('link', $uri);
		$item->create('description')->value($desc);
		$item->create('guid', $uri);
		$item->create('pubDate', date(DATE_RFC822));
		#autor:
		$item->create('dc:creator', '', $this->_ns_dc);
		return $item;
	}


}
?>