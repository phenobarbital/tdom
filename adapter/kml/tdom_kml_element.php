<?php
/**
 *
 * tdom_kml_element
 * Tomates DOMElement KML elements extension class
 * @access public
 **/
include_once TDOM_BASE . 'adapter/xml/tdom_element.php';
class tdom_kml_element extends tdom_element {

	/**
	 * Crea un nuevo placemark en el kml element
	 *
	 * @param string $name
	 * @param array $coordinates (lat,long,alt)
	 * @param string $description
	 * @return tdom_element $place
	 */
	public function placemark($name, $coordinates = array(0, 0, 0), $description = '', $is_html = true) {
		$this->validate();
		$place = $this->element('Placemark');
		if (!$place) { #si no existe place, entonces lo creamos
			$place = $this->create('Placemark');
		}
		$place->create('name')->value($name);
		$desc = $place->create('description');
		if ($is_html == true) {
			$comment = $this->document()->createCDATASection($description);
		} else {
			$comment = $this->document()->createTextNode($description);
		}
		$desc->appendChild($comment);
		if (is_array($coordinates) && count($coordinates) == 3) {
			$place->create('Point')->create('coordinates')->value(implode(',', $coordinates));
		}
		return $place;
	}

	/**
	 * Crea un item linestring
	 *
	 * @param mixed $coordinates
	 */
	public function linestring($coordinates = array()) {
		$line = $this->element('LineString');
		if(!$line) {
			$line = $this->create('LineString');
		}
		$line->replace('extrude', '1');
		$line->replace('tessellate', '1');
		$line->replace('altitudeMode', 'absolute');
		if (is_array($coordinates)) {
			$coord = implode(',', $coordinates);
		} else {
			$coord = $coordinates;
		}
		#valor directo del nodo:
		$line->create('coordinates')->nodeValue = $coord;
	}

	public function createDocument($name, $description = '') {
		$doc = $this->element('Document');
		if (!$doc) {
			$doc = $this->create('Document');
			$doc->create('name')->value($name);
			$doc->create('description', $description);
			return $doc;
		} else {
			$doc->element('name')->value($name);
			$doc->element('description')->value($description);
			return $doc;
		}

	}

	public function linearRing($coordinates = array()) {
		$line = $this->element('LinearString');
		if(!$line) {
			$line = $this->create('LinearString');
		}
		if (is_array($coordinates)) {
			$coord = implode(',', $coordinates);
		} else {
			$coord = $coordinates;
		}
		#valor directo del nodo:
		$line->create('coordinates')->nodeValue = $coord;
	}

}
?>