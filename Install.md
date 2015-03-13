# Install TDOM #

  * Download and unpack (or download via SVN)
  * Call base file of TDOM:

```
require_once '/path/to/tdom.php';
```
  * initialize TDOM object:
```
tdom::init('path/to/config/file');
```
  * create a new instance of tdom (optional)
```
$dom = new tdom();
```

And ready to use TDOM.

## Creating a XML-type Document ##

tdom have a 2 methods for creating TDOM objects based on file type:

### Using type() method ###
```
$xml = $dom->type('xml');
```

### Using static call ###

```
$svg = tdom::document('svg');
```

both returns a new instance of TDOM

# Instances of TDOM #

TDOM can create many types of XML-based files:

  * RSS
  * ATOM
  * KML (Google Maps, Google Earth)
  * SVG (Graphic)
  * DocBook (documentation creation, parsing and transformation)
  * XUL (XML User interface of Mozilla)
  * HTML (XHTML Strict)

And new types is coming!:
  * HTML5
  * XMPP
  * ODF
  * RDF
  * XML-RPC