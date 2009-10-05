<?php
/**
 *
 * tdom_docbook_element permite extender para crear elementos DocBook
 * Tomates DOMElement docbook elements extension class
 * @access public
 **/
class docbook_element extends tdom_element {

	public function releaseinfo($date = null, $version = '1.0') {
		$name = $this->nodeName;
		if ($name == 'bookinfo' || $name == 'articleinfo') {
			$date = date('Y/m/d H:i:s', $date);
			if(!$this->exists('pubdate')) {
				$this->create('pubdate')->attribute('role', 'rcs')->value("\$Date: 2003/01/08 10:27:39 \$");
			}
			if(!$this->exists('releaseinfo')) {
				$info = "\$Id: {$version} TDOM Docbook Generator, {$date} \$";
				$this->create('releaseinfo')->value($info);
			}
		}
	}

	#genera un titulo solo en aquellos elementos que llevan titulo:
	public function title($title = 'Your title here') {
		if ($this->validParent($this->nodeName, array('book', 'bookinfo', 'articleinfo', 'chapter', 'section', 'sect1', 'sect2', 'figure'))) {
			if(!$this->exists('title')) {
				$title = $this->create('title', $title);
			} else {
				$title = $this->element('title')->value($title);
			}
			return $this;
		} else {
			return false;
		}
	}

	public function author($firstname = '', $lastname = '', $email = '') {
		if ($this->validParent($this->nodeName, array('bookinfo', 'articleinfo', 'authorgroup'))) {
			if($author = $this->element('author')) {
				$author->element('firstname')->value($firstname);
				$author->element('surname')->value($lastname);
				if ($email) {
					$author->element('email')->value($email);
				}
			} else {
				$author = $this->create('author');
				$author->create('firstname', $firstname);
				$author->create('surname', $lastname);
				if ($email) {
					$author->create('affiliation')->create('address')->create('email', $email);
				}
			}
			return $this;
		} else {
			return false;
		}
	}

	protected function validParent($name, array $parents = array()) {
		if (in_array($name, $parents)) {
			return true;
		} else {
			throw new exception("tdom docbook: los elementos {$name} no pueden contener el elemento solicitado");
			return false;
		}
	}

	/**
	 * Retorna el authorgroup del item actual
	 *
	 */
	public function authorgroup() {
		if ($this->validParent($this->nodeName, array('bookinfo', 'articleinfo', 'biblioentry'))) {
			if(!$a = $this->element('authorgroup')) {
				$a = $this->create('authorgroup');
			}
			return $a;
		} else {
			return false;
		}
	}

	public function bibliography($title = '') {
		if ($this->validParent($this->nodeName, array('book', 'article', 'appendix', 'glossary'))) {
			$b = $this->createinx('bibliography');
			$b->createinx('title', $title);
			return $b;
		} else {
			return false;
		}
	}

	public function copyright($year = '2009', $name = '') {
		if ($this->validParent($this->nodeName, array('bookinfo', 'articleinfo', 'biblioentry'))) {
			if($r = $this->element('copyright')) {
				$r->element('year')->value($year);
				$r->element('holder')->value($name);
			} else {
				$r = $this->create('copyright');
				$r->create('year', $year);
				$r->create('holder', $name);
			}
			return $r;
		}
	}

	public function resume($text = '') {
		if ($this->validParent($this->nodeName, array('bookinfo', 'articleinfo'))) {
			if(!$resume = $this->element('abstract')) {
				$resume = $this->create('abstract');
			}
			$resume->paragraph($text);
			return $this;
		} else {
			return false;
		}
	}

	public function paragraph($text) {
		if(!$p = $this->element('para')) {
			$p = $this->create('para');
		}
		$p->value($text);
		return $p;
	}
	public function p($text) {
		return $this->paragraph($text);
	}
	public function para($text) {
		return $this->paragraph($text);
	}

	public function keywords(array $keys = array()) {
		if ($this->validParent($this->nodeName, array('bookinfo', 'articleinfo'))) {
			if(!$set = $this->element('keywordset')) {
				$set = $this->create('keywordset');
			}
			foreach($keys as $k) {
				$set->create('keyword', $k);
			}
			return $this;
		} else {
			return false;
		}
	}

	/**
	 * Retorna todos los capitulos del item actual
	 *
	 */
	public function chapters() {
		$chapters = $this->getElementsByTagName('chapter');
		if ($chapters->length > 0) {
			return $chapters;
		} else {
			return false;
		}
	}

	public function chapter($id = '', $title = '') {
		$chapter = null;
		if ($this->validParent($this->nodeName, array('book', 'part'))) {
			if (!$chapter = $this->document()->getElementById($id)) {
				foreach($this->getElementsByTagName('chapter') as $node) {
					if ($node->getAttribute('id') == $id) {
						$chapter = $node;
						break;
					}
				}
				#si de cualquier manera, el capitulo no existe, se crea:
				if ($chapter == null) {
					$chapter = $this->create('chapter');
				}
			}
			$chapter->id($id);
			$chapter->create('title')->value($title);
			return $chapter;
		}
	}

	public function section($id = '', $title = '') {
		$chapter = null;
		if ($this->validParent($this->nodeName, array('article', 'chapter', 'section', 'appendix'))) {
			foreach($this->getElementsByTagName('section') as $node) {
				if ($node->getAttribute('id') == $id || $node->getAttribute('label')==$id) {
					$section = $node;
					break;
				}
			}
			#si de cualquier manera, la seccion no existe, se crea:
			if ($section == null) {
				$section = $this->create('section');
				$section->id($id);
				$section->label($id);
				$section->create('title')->value($title);
			}
			return $section;
		} else {
			return false;
		}
	}

	public function preface($label, $title = '') {
		if ($this->validParent($this->nodeName, array('book'))) {
			if ($preface = $this->getElementsByTagName('preface')->item(0)) {
				#si ya existe, definimos sus valores label y title
				$preface->setAttribute('label', $label);
				$preface->create('title', $title);
			} else {
				#no existe: pero el preface va despues de bookinfo:
				$info = $this->document()->info();
				$preface = new tdom_element('preface');
				if($info->nextSibling!=null) {
					#va insertado previo a cualquier capitulo
					$preface = $this->insertBefore($preface, $info->nextSibling);
				} else {
					#va simplemente despues del bookinfo
					$preface = $this->appendChild($preface);
				}
				$preface->setAttribute('label', $label);
				$preface->create('title', $title);
			}
			return $preface;
		}
	}

	public function part($label, $title = '') {
		$part = null;
		if ($this->validParent($this->nodeName, array('book'))) {
			foreach($this->document()->getElementsByTagName('part') as $node) {
				if ($node->getAttribute('label') == $label) {
					$part = $node;
					break;
				}
			}
			#si no hemos encontrado la parte
			if ($part == null) {
				$part = $this->document()->create('part');
				$part->setAttribute('label', $label);
				$part->create('title', $title);
			}
			return $part;
		} else {
			return false;
		}
	}

	public function partintro($text) {
		if ($this->nodeName() == 'part') {
			if (!$i = $this->element('partintro')) {
				$i = $this->create('partintro');
			}
			$i->paragraph($text);
			return $this;
		} else {
			return false;
		}
	}
}

class db_author extends tdom_xml {
	#nombre de la etiqueta
	protected $_tagName = 'author';

	public function __construct($name ='', $surname = '') {
		parent::__construct();
		$this->root($this->_tagName);
		$this->create('firstname', $name);
		$this->create('surname', $surname);
	}

	public function email($email = '') {
		if (!$e = $this->element('email')) {
			$e = $this->create('affiliation')->create('address')->create('email', $email);
		} else {
			$e->value($email);
		}
	}
}

class db_editor extends db_author {
	#nombre de la etiqueta
	protected $_tagName = 'editor';
}

class db_biblioentry extends tdom_xml {
	#nombre de la etiqueta
	protected $_tagName = 'biblientry';
	public function __construct($title ='', $publisher = '') {
		parent::__construct();
		$this->root($this->_tagName);
		$this->create('title', $title);
		$this->create('publisher')->create('publishername', $publisher);
	}

	public function isbn($isbn = '') {
		return $this->base()->createinx('isbn', $isbn);
	}

	/**
	 * Retorna el authorgroup del item actual
	 *
	 */
	public function authorgroup() {
		return $this->base()->createinx('authorgroup');
	}

	public function copyright($year = '2009', $name = '') {
		$r = $this->base()->createinx('copyright');
		$r->createinx('year', $year);
		$r->createinx('holder', $name);
		return $r;
	}

	public function createAuthor($name, $surname) {
		return $this->authorgroup()->add(new db_author($name, $surname));
	}
}
?>