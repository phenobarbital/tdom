# Using TDOM base factory class #

Creating a simple XML file try first calling DOM factory class:

```
require_once 'tdom/tdom.php';
tdom::init('path/to/config/file');
$tdom = new tdom();
```

Config file containts define constants require for TDOM.

create a DOM-XML object:

```
$xml = $tdom->type('xml');
```

## Creating a simple XML file ##

Code:
```
		$xml->useDTD(true);
		$xml->setDTD('example.dtd',true)->setType('body');
		$xml->createBase();
		$div = $xml->create('div', 'char');
		$div->create('p', 'algun texto');
		$xml->createDATA('<test>');
		$dom->render();
```

Output:
```
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE body SYSTEM "example.dtd">
<body>
  <div>char<p>algun texto</p></div>
</body>
<![CDATA[<test>]]>
```

## Creating an (x)HTML strict ##

Code:
```
		$html = $dom->type('html');
		$html->createHead();
		$html->setTitle('.: Titulo de la Pagina Web :.');
		$dom->render();
```

Output:
```
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://localhost/proyectos/tomates/tomates/include/tdom/tomates/tdom/include/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>.: Titulo de la Pagina Web :.</title>
    <base href="http://localhost/proyectos/tomates/" />
<!--Aqui comienzan las etiquetas meta-->
    <meta name="author" content="" />
    <meta name="generator" content="Tomates Framework" />
    <meta name="description" content="" />
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="robots" content="all" />
    <meta http-equiv="Content-Language" content="en_US" />
    <meta name="keywords" content="" />
    <meta name="Revised" content=": Sep 18 2009" />
<!--Aqui terminan las etiquetas meta-->
    <link rel="shortcut icon" href="http://localhost/proyectos/tomates/application/assets/images/favicon.ico" type="image/x-icon" />
    <link rel="start" href="http://localhost/proyectos/tomates/" />
    <link rel="index" href="http://localhost/proyectos/tomates/" />
  </head>
  <body></body>
</html>
```

## Creating a SVG Graphic ##

Code:
```
$svg = tomates::dom()->type('svg');
$svg->description('Example circle01 - circle filled with red and stroked with blue')->title('SVG Circle');
$svg->ViewBox('0 0 1200 400');
$svg->width('12cm')->height('4cm');
$svg->create('rect')->x('1')->y('1')->width("1198")->height("398")->fill("none")->stroke('blue')->attribute('stroke-width', '2');
$svg->create('circle')->cx('600')->cy('200')->r('100')->fill('red')->stroke('blue')->attribute('stroke-width', '10');
$svg->render();
```

Output:
```
<?xml version="1.0" encoding="utf-8" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg1" viewBox="0 0 1200 400" xmlns:html="http://www.w3.org/1999/xhtml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" sodipodi:version="0.32" version="1.1" width="12cm" height="4cm">
  <title>SVG Circle</title>
  <desc>Example circle01 - circle filled with red and stroked with blue</desc>
  <rect x="1" y="1" width="1198" height="398" fill="none" stroke="blue" stroke-width="2"/>
  <circle cx="600" cy="200" r="100" fill="red" stroke="blue" stroke-width="10"/>
</svg>
```

## Creating a XUL interface ##

Code:
```
		$xml = $dom->type('xul');
		#un tabbox
		$tabbox = $xml->create('tabbox');
		$tabbox->orient('vertical')->flex('1');
		$tabs = $tabbox->create('tabs');
		$tabpanel = $tabbox->create('tabpanels');
		$tabpanel->flex('1');
		#adjunto las pestañas:
		$tabs->create('tab')->label('Google');
		$tabs->create('tab')->label('PHP.net');
		$tabs->create('tab')->label('DEVEL');
		#y el contenido de las pestañas
		$tabpanel->create('browser')->src('http://www.google.co.ve/');
		$tabpanel->create('browser')->src('http://www.php.net/');
		$tabpanel->create('browser')->src('http://www.devel.com.ve/');
		$xml->title('Prueba de XUL');
		$dom->render();	
```


Output:
```
<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type="text/css" href="chrome://global/skin/"?>
<window xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul" id="xulWindow" persist="screenX screenY width height sizemode" xmlns:html="http://www.w3.org/1999/xhtml" title="Prueba de XUL">
  <tabbox orient="vertical" flex="1">
    <tabs>
      <tab label="Google"/>
      <tab label="PHP.net"/>
      <tab label="DEVEL"/>
    </tabs>
    <tabpanels flex="1">
      <browser src="http://www.google.co.ve/"/>
      <browser src="http://www.php.net/"/>
      <browser src="http://www.devel.com.ve/"/>
    </tabpanels>
  </tabbox>
</window>
```