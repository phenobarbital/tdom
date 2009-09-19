<?php
/**
 * TDOM: Clase constructora de documentos XML usando Document Object Model
 *
 * @version 0.1.2
 * @author     Jesus Lara <jesuslarag@gmail.com>
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @copyright 2005-2009 Jesus Lara. All rights reserved.
 * @package tomates dom
 * @subpackage TDOM
 */

/**
 * TDOM: gestor de documentos XML usando el Document Object Model
 * @author Jesus Lara <jesuslarag@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 */
class tdom {

	#contenedor oficial del documento
	protected $_doc = null;

	/**
	 * inicializa el dom object
	 *
	 * @param string $config_file path
	 * @return boolean
	 */
	public static function init($config_file = '') {
		try {
			require($config_file);
			self::uri();
			#requiero los archivos básicos
			require_once('adapter/xml/tdom_xml.php');
			#y los elementos basicos:
			include_once 'adapter/xml/tdom_node.php';
			include_once 'adapter/xml/tdom_element.php';
			include_once 'adapter/xml/tdom_text.php';
			return true;
		} catch(exception $e) {
			echo $e->getMessage();
			return false;
		}
	}

	protected static function _create($doctype = 'xml') {
		#comprobamos inicialmente la existencia del adaptador
		$dir = TDOM_BASE . 'adapter' . DIRECTORY_SEPARATOR . $doctype . DIRECTORY_SEPARATOR;
		$classname = 'tdom_' . $doctype;
		$filename = $dir . $classname . '.php';
		if (is_dir($dir)) {
			if (is_file($filename)) {
				require_once($filename);
				if (class_exists($classname, false)) {
					return new $classname();
				} else {
					throw new exception("tdom error: la clase {$classname} para el tipo de documento {$doctype} no existe!");
				}
			} else {
				throw new exception("tdom error: el archivo {$filename} del adaptador {$doctype} no existe");
			}
		} else {
			throw new exception("tdom error: el directorio {$dir} no existe");
		}
	}
	/**
	 * Determina el tipo de documento que va a generar el objeto DOM
	 *
	 * @param string $doctype
	 * @return tdom_xml adapter
	 */
	public function type($doctype = 'xml') {
		$this->_doc = self::_create($doctype);
		return $this->_doc;
	}

	public static function document($doctype = 'xml') {
		return self::_create($doctype);
	}

	/**
	 * Descubrimiento del nombre del servidor y protocolo (http o https) para DOM
	 * @return ruta absoluta al objeto TDOM
	 */
	public static function uri() {
		$server = $_SERVER["SERVER_NAME"] . XML_HTTP_PORT;
		#HTTPS, HTTP u other protocol
		$protocol = XML_HTTP_PROTOCOL;
		#determinando rutas al objeto TDOM:
		define('TDOM_BASE', dirname(__FILE__) . DIRECTORY_SEPARATOR);
		# --- URL base de la aplicacion ----
		$base = (dirname($_SERVER['PHP_SELF']) . DIRECTORY_SEPARATOR);
		if (strpos($base, '//')!==false) {
			$base = str_replace('//', '/', $base);
		}
		#base URL de TDOM:
		$ruta = $protocol . '://' . $server . $base;
		define('TDOM_BASE_URI', $ruta);
		$ruta.= XML_INCLUDE . DIRECTORY_SEPARATOR;
		define('TDOM_URI', $ruta);
		return $ruta;
	}

	// -- metodos magicos para sobre-escribir las operaciones

	public function __call($method, $args) {
		return call_user_func_array(array($this->_doc, $method), $args);
	}

	public function version() {
		return TDOM_VERSION;
	}
}
?>