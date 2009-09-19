<?php
#la version actual de tomato TDOM
define('TDOM_VERSION', '0.1.2');
#relativas al encoding y version del XML
define('XML_VERSION', '1.0');
define('XML_LOCAL_VALIDATION', true); #causa una validacion local del XML
define('XML_ENCODING', 'utf-8');
define('STANDALONE', false); #required for some applications
define('XML_STRICT', true); #xhtml 1.0 strict or xhtml transitional?
define('XML_LANG', 'es'); #lenguaje para el constructor xhtml
define('XML_LOCALE', 'es_VE');
define('XML_FORMAT', true); #permite formatear la salida del DOM
#propiedades relativas al URI
define('XML_HTTP_PORT', ''); #puerto, cambiar si es distinto de 80 (ej. :8080)
define('XML_HTTP_PROTOCOL', 'http'); #por defecto http (https|rss|file)
#ruta de inclusion de DTD y otros archivos (relativa a la ruta absoluta de la aplicacion)
define('XML_INCLUDE', 'tomates/include/tdom');
?>