<?php
/**
 *
 * tdom_html
 * Tomates DOM (x)html extension
 *
 **/

include_once TDOM_BASE . 'adapter/xml/tdom_xml.php';
class tdom_html extends tdom_xml {

	protected $_strict = true;
	protected $_local_validation = true;
	protected $_standalone = false;
	 
	#espacio xml
	public $_namespace = 'http://www.w3.org/1999/xhtml';
	protected $_public = '-//W3C//DTD XHTML 1.0 Strict//EN';

	protected $_dom_type = 'html';

	#nombre del nodo base
	protected $_base_node = 'html';

	//especificas de un documento html
	protected $_base = null;
	protected $_title = null;
	protected $_link = null;
	protected $_meta = null;
	 
	#sectores de un documento html
	protected $_html = null;
	protected $_head = null;
	protected $_body = null;

	#contenedor de los arrays y scripts del documento
	public $_styles = array();
	public $_scripts = array();

	//guarda los archivos de elementos html
	private static $dir = null;

	#personalizacion del element_adapter
	protected $_element_class = 'tdom_html_element';

	#permitir validacion via DTD
	protected $use_dtd = true;
	protected $validate_dtd = true; //HTML necesita validar para DTD

	public function __construct() {
		include_once 'tdom_html_element.php';
		$this->_strict = XML_STRICT;
		$this->_local_validation = XML_LOCAL_VALIDATION;
		#defino el tipo de documeto y el public namespace del HTML
		$this->define_public();
		$this->define_dtd();
		parent::__construct();
	}

	/**
	 * define el DTD de un documento HTML
	 *
	 */
	protected function define_dtd() {
		if (!$this->_strict) {
			if (!$this->_local_validation) {
				$dtd = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';
			} else {
				$dtd = 'tomates/tdom/include/xhtml1-transitional.dtd';
			}
		} else {
			if (!$this->_local_validation) {
				$dtd = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';
			} else {
				$dtd = 'tomates/tdom/include/xhtml1-strict.dtd';
			}
		}
		$this->setDTD($dtd, $this->_local_validation);
	}

	/**
	 * define el public del documento (x)html
	 *
	 */
	protected function define_public() {
		if (!$this->_strict) {
			$this->setPublic('-//W3C//DTD XHTML 1.0 Transitional//EN');
		} else {
			$this->setPublic('-//W3C//DTD XHTML 1.0 Strict//EN');
		}
	}
	 
	/*
	 * Elementos html basicos
	 */
	/**
	 * elemento html
	 *
	 * @return tdom_element html
	 */
	public function html() {
		//si no existe html lo creo
		if ($this->_html == null) {
			$this->_html = $this->getElementsByTagName('html')->item(0);
			if ($this->_html==null) {
				#en el caso que el nodo raiz no exista
				$this->_html = $this->createNode('html', null, $this->_namespace);
			}
		}
		return $this->_html;
	}

	/**
	 * elemento head
	 *
	 * @return tdom_element head
	 */
	public function head() {
		//si no existe head lo creo
		if ($this->_html->getElementsByTagName('head')->item(0)==null) {
			$this->_head = $this->_html->create('head');
		} elseif (($this->_head== null)) {
			$this->_head = $this->_html->getElementsByTagName('head')->item(0);
		}
		return $this->_head;
	}

	/**
	 * elemento body
	 *
	 * @return tdom_element body
	 */
	public function body() {
		if ($this->_html->getElementsByTagName('body')->item(0)==null) {
			$this->_body = $this->_html->create('body');
		} elseif ($this->_body == null) {
			$this->body = $this->_html->getElementsByTagName('body')->item(0);
		}
		return $this->_body;
	}

	/**
	 * retorna y define el elemento title
	 *
	 * @param string $title
	 * @return DOMNode node
	 */
	public function title($title = '') {
		//si no existe title lo creo
		if ($this->_html->getElementsByTagName('title')->item(0)==null) {
			$this->_title = $this->_head->create('title');
		} elseif ($this->_title==null) {
			$this->_title = $this->getElementsByTagName('title')->item(0);
		}
		$this->_title->text($title, true);
		return $this->_title;
	}

	/**
	 * forma corta de definir el titulo de la pagina web
	 *
	 * @param string $title
	 * @return DOMNode node
	 */
	public function setTitle($title) {
		return $this->title($title);
	}
	 
	public function update() {
		$this->html();
		$this->head();
		$this->title();
		$this->body();
		//Normalizo y valido el documento
		$this->normalizeDocument();
		parent::update();
	}
	 
	/**
	 * cuerpo head completo
	 *
	 */
	public function createHead() {
		//direccion base
		$this->link_base();
		//etiquetas meta
		$this->head()->comment('Aqui comienzan las etiquetas meta');
		$this->meta_author();
		if (defined('APP_GENERATOR')) {
			$this->meta_generator(APP_GENERATOR);
		} else {
			$this->meta_generator('TDOM - Tomates DOM model');
		}
		//otras etiquetas meta:
		$this->meta_description()->meta_content()->meta_lang()->meta_keywords()->meta_revised();
		$this->head()->comment('Aqui terminan las etiquetas meta');
		/*
		 * Algunas etiquetas link
		 */
		$this->favicon()->link_start();
	}


	//crea etiquetas META:
	public function meta($name, $value) {
		if ($this->_head instanceOf tdom_element) {
			$this->_head->create('meta')->name(htmlentities($name, null, $this->_encoding))->content(htmlentities($value, null, $this->_encoding));
		}
		return $this;
	}

	public function meta_author($author = '') {
		return $this->meta('author', $author);
	}

	public function meta_generator($generator = '') {
		return $this->meta('generator', $generator);
	}

	public function meta_description($descrip = '') {
		return $this->meta('description', $descrip);
	}

	public function meta_content() {
		$this->_head->create('meta')->attribute('http-equiv', 'Content-Type')->content("application/xhtml+xml; charset={$this->_encoding}");
		$this->_head->create('meta')->attribute('http-equiv', 'Content-Script-Type')->content('text/javascript');
		$this->_head->create('meta')->attribute('http-equiv', 'Content-Style-Type')->content('text/css');
		$this->meta('robots', 'all');
		return $this;
	}

	public function meta_lang($lang = 'en_US') {
		$this->_head->create('meta')->attribute('http-equiv', 'Content-Language')->content($lang);
		return $this;
	}

	public function meta_keywords($keys= '') {
		return $this->meta('keywords', $keys);
	}

	public function meta_revised($autor = '') {
		return $this->meta('Revised', $autor . ': ' . date ('M d Y'));
	}

	public function meta_abstract($value = '') {
		return $this->meta('Abstract', $value);
	}

	public function meta_refresh($seconds = 5, $uri = '') {
		$meta = $this->_head->create('meta')->attribute('http-equiv', 'refresh')->content($seconds);
		if ($uri) {
			$meta->URL($uri);
		}
		return $this;
	}
	
	// --- definicion de otros elementos de head

	public function link_base($uri = TDOM_BASE_URI) {
		$this->_head->create('base')->href($uri);
		return $this;
	}

	public function html_lang($lang = 'es') {
		return $this->html()->attribute('xml:lang', $lang);
	}

	public function XML_base($base = TDOM_BASE_URI) {
		return $this->html()->attribute('xml:base', $base);
	}

	//permite generar un link de tipo generico:
	public function link($rel='start', $file = '', $reverse = false, $type='', $lang = '') {
		if ($reverse) {
			$link = $this->_head->create('link')->rev($rel);
		} else {
			$link = $this->_head->create('link')->rel($rel);
		}
		if ($file) {
			$link->href($file);
		}
		if ($lang) {
			$link->hreflang($lang);
		}
		if ($type) {
			$link->type($type);
		}
		return $this;
	}

	/*
	 * --- Algunos tipos de LINK
	 */
	//favicon define el icono a mostrar la aplicacion
	public function favicon($image='') {
		if ($image=='') {
			$image = TDOM_BASE_URI . FAVICON;
		}
		$this->link('shortcut icon', $image, false, 'image/x-icon');
		return $this;
	}

	public function link_start($uri = '') {
		if ($uri == '') {
			$uri = TDOM_BASE_URI;
		}
		$this->link('start', $uri);
		$index = TDOM_BASE_URI;
		$this->link('index', $index);
		return $this;
	}

	public function link_glossary($uri) {
		$this->link('glossary', $uri);
	}

	public function link_toc($uri) {
		$this->link('contents', $uri);
	}

  	  // -- gestion de documentos html

  	  #actualiza las rutas de las imagenes asociadas a la template actual
  	  public function update_img_paths($dir = '', $is_layout = true) {
  	  	#normalizo las imagenes
  	  	$imgs = $this->getElementsByTagName('img');
  	  	foreach($imgs as $img) {
  	  		$file = $img->getAttribute('src');
  	  		if ($file) {
  	  			$filename = basename($file);
  	  			$dirname = dirname($file);
                if ($is_layout) {
                    $img->setAttribute('src', LAYOUTS_URI . $dir . $dirname . '/' . $filename);
                } else {
                    $img->setAttribute('src', IMAGES_URI . $dir . $dirname . '/' . $filename);
                }
  	  		}
  	  	}
  	  }

  	  #carga un archivo HTML pero descarta su HEAD y el resto de cosas:
  	  public function load_body($filename, $preservestyles=false) {
  	  	#cargo el archivo inicialmente
  	  	$layout_dir = str_replace(LAYOUTS_DIR, '', dirname($filename)) . DS;
  	  	$dom = $this->_loadHTML($filename);
  	  	if ($dom) {
  	  		if ($preservestyles) {
  	  			#puedo preservar los css, es necesario?
  	  			$styles = $dom->getElementsByTagName('link');
                #preservar tambien los estilos
                $scripts = $dom->getElementsByTagName('script');
  	  		}
  	  		#tomo el body:
  	  		$node = $dom->getElementsByTagName('body')->item(0);
  	  		#parseo el documento:
  	  		$node_data = $this->importNode($node, true);
  	  		#debo buscar el body y adjuntarlo a mi estructura actual:
  	  		$old_data = $this->getElementsByTagName('body')->item(0);
  	  		#reemplazar:
  	  		$old_data = $this->_html->replaceChild($node_data, $old_data);
  	  		$this->_body = $node_data;
  	  		#adjuntar al body actual todos los estilos encontrados
  	  		if ($preservestyles) {
  	  			foreach ($styles as $style) {
  	  				$rel = $style->getAttribute('rel');
  	  				if ($rel=='stylesheet') {
  	  					$file = $style->getAttribute('href');
  	  					$filename = basename($file);
  	  					$dirname = dirname($file);
  	  					$style->setAttribute('href', LAYOUTS_URI . $layout_dir . $dirname . DS . $filename);
  	  					$s = $this->importNode($style, true);
  	  					$s = $this->_head->appendChild($s);
  	  				}
  	  			}
  	  			foreach ($scripts as $script) {
  	  				$file = $script->getAttribute('src');
  	  				$filename = basename($file);
  	  				$dirname = dirname($file);
  	  				$script->setAttribute('src', LAYOUTS_URI . $layout_dir . $dirname . DS . $filename);
  	  				$s = $this->importNode($script, true);
  	  				$s = $this->_head->appendChild($s);
  	  			}
  	  		}
  	  		#actualizo los paths de las hojas de estilo y las imagenes
  	  		$this->update_img_paths($layout_dir);
  	  		#si todo ocurrio perfectamente, valido el documento:
  	  		$this->update_references();
  	  		return true;
  	  	} else {
  	  		return false;
  	  	}
  	  }
  	  
  	  //salida del documento por pantalla
  	  public function show($show_dtd = true) {
  	  	if($this->_onload) {
  	  		$this->_define_onload_script();
  	  	}
  	  	echo parent::render();
  	  }  	  

  	  //permite cargar un 'front' o layout para la pagina actual
  	  //TODO: pasar a la controladora app_controller
  	  public function layout($filename) {
  	  	$file = LAYOUTS_DIR . $filename;
  	  	return $this->load_body($file, true);
  	  }

  	  protected function _define_onload_script() {
  	  	#create a SCRIPT element
  	  	if ($this->_onload) {
  	  		#creo las lineas iniciales del define_script:
  	  		$script = "\r";
  	  		$script.= "window.onload=function() {\r";
  	  		$script.= $this->_onload;
  	  		$script.= "}\r";
  	  		return $this->content_javascript($script);
  	  	}
  	  }
      
  	 //gestion de hojas de estilo y javascript
  	 # ----- funciones de hoja de estilo:

  	 #crea una hoja de estilos generica
  	 public function create_stylesheet($content = '', $media='screen') {
  	 	#creo una seccion style:
  	 	$style = $this->_head->create('style');
  	 	$style->attribute('type', 'text/css')->attribute('media', $media);
  	 	#adjunto el contenido como texto comentado:
  	 	$style->append(new DOMComment("\n" . $content .  "\n"));
  	 	return $this;
  	 }

  	 //crea un link stylesheet generico
  	 public function link_stylesheet($filename, $media='screen') {
  	 	#no necesito agregar una hoja de estilo que ya está:
  	 	if (!in_array($filename, $this->_styles)) {
  	 		$style = $this->_head->create('link')->rel('stylesheet')->type('text/css')->media($media);
  	 		$style->setAttribute('href', $filename);
  	 		$this->_styles[] = $filename;
  	 	}
  	 	return $this;
  	 }

  	 #define una hoja de estilo de usuario:
  	 public function stylesheet($stylesheet, $media="screen") {
  	 	$filename = STYLES_URI . $stylesheet;
  	 	return $this->link_stylesheet($filename, $media);
  	 }
  	 
  	 public function user_stylesheet($stylesheet, $media = 'screen') {
  	 	$filename = RELATIVE_URI . $stylesheet;
  	 	return $this->link_stylesheet($filename, $media);
  	 }
  	 
  	 public function user_css($stylesheet, $media = 'screen') {
  	 	return $this->user_stylesheet($stylesheet, $media);
  	 }

  	 #referencia a un hoja de estilos del framework
  	 public function define_stylesheet($stylesheet, $media="screen") {
  	 	$filename = TOMATES_CSS . $stylesheet;
  	 	return $this->link_stylesheet($filename , $media);
  	 }
  	 
  	 public function source_stylesheet($stylesheet, $media='screen') {
  	 	return $this->link_stylesheet($stylesheet , $media);
  	 }

  	 // --- Funciones de javascript -----------

  	 protected function create_javascript($node=null) {
  	 	if (!$node) {
  	 		$node = &$this->_head;
  	 	}
  	 	#create a js reference
  	 	$this->_js = $node->create('script');
  	 	#set type
  	 	$this->_js->type('text/javascript');
  	 	return $this;
  	 }

  	 //un javascript source generico
  	 public function source_javascript($filename) {
  	 	$this->create_javascript();
  	 	$this->_js->src($filename);
  	 	return $this;
  	 }

  	 //funciones javascript del framework
  	 public function define_javascript($js) {
  	 	$filename = TOMATES_JS . $js;
  	 	//si ya lo adjunte, para que volverlo a adjuntar?
  	 	if (!in_array($filename, $this->_scripts)) {
  	 		$this->source_javascript($filename);
  	 		$this->_scripts[] = $filename;
  	 	}
  	 	return $this;
  	 }

  	 //vinculo a un javascript externo
  	 public function external_javascript($uri) {
  	 	$filename = $uri;
  	 	//si ya lo adjunte, para que volverlo a adjuntar?
  	 	if (!in_array($filename, $this->_scripts)) {
  	 		$this->source_javascript($filename);
  	 		$this->_scripts[] = $filename;
  	 	}
  	 	return $this;
  	 }

  	 //javascript generico con contenido infile
  	 public function content_javascript($js_content, &$node=null) {
  	 	$this->create_javascript($node);
  	 	if ($this->_js) {
  	 		#adjunto el contenido como texto:
  	 		$content = new DOMComment("\n" . $js_content .  "\n");
  	 		$this->_js->append($content);
  	 	}
  	 	return $this;
  	 }

  	 #agrego un javascript de usuario (helper, etc)
  	 public function user_javascript($filename) {
  	 	$uri = RELATIVE_URI . $filename;
  	 	return $this->external_javascript($uri);
  	 }
  	 
  	 #agrega una referencia a un archivo de script js de usuario
  	 public function &javascript($js, $isFile=true, &$node=null) {
  	 	#si es un archivo:
  	 	if ($isFile) {
  	 		$filename = SCRIPTS_URI . $js;
  	 		if (!in_array($filename, $this->_scripts)) {
  	 			$this->source_javascript($filename);
  	 			$this->_scripts[] = $filename;
  	 		}
  	 	} else {
  	 		$this->content_javascript($js, $node);
  	 	}
  	 	return $this;
  	 }

  	 #agrega una funcion javascript usando addEventListener:
  	 public function OnloadEvent($control, $js, $event='click') {
  	 	#creo el script:
  	 	$script = "var {$control} = document.getElementById('{$control}');\n";
  	 	$script.= "{$control}.addEventListener('{$event}', function (event) {\n";
  	 	$script.= "	{$js};\n";
  	 	$script.= "},false);\n";
  	 	$this->_onload.= $script;
  	 }

  	 #agrega codigo javascript para ser insertado en el onload:
  	 public function onloadFunction($js) {
  	 	$script= "{$js}\n";
  	 	$this->_onload.= $script;
  	 }

  	 #codigo a ser insertado en un bloque "onReady" de jquery:
  	 public function onReady($js) {
  	 	if ($js!= '') {
  	 		$script = "$(document).ready(function(){\r";
  	 		$script.= $js . "\r";
  	 		$script.= "});\r";
  	 		$this->content_javascript($script);
  	 	}
  	 }
}
?>