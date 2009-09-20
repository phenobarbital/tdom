<?php
/**
 *
 * tdom_planner_element
 * Tomates DOMElement Gnome Planner elements extension class
 * @access public
 **/
class tdom_planner_element extends tdom_element {
	public function createPhase($name = '') {
		$phase = $this->getElementsByTagName($name);
		if ($phase->length == 0) { //si no lo encuentro
			$this->create('phase')->attribute('name', $name);
		}
		return $phase;
	}

	/**
	 * Permite crear y retornar calendarios
	 *
	 * @param string $id
	 * @param string $name
	 * @param array $week
	 * @return tdom_element calendar
	 */
	public function calendar($id = '1', $name = 'default', $week = array()) {
		$calendar = null;
		$calendars = $this->getElementsByTagName('calendar');
		foreach($calendars as $cal) {
			if ($cal->getAttribute('id') == $id) {
				$calendar = $cal;
				break;
			}
		}
		if (is_null($calendar)) {
			#si llego aca, el calendario no existe:
			$calendar = $this->create('calendar');
			$calendar->id($id);
		}
		#y ahora defino los atributos
		$calendar->attribute('name', $name);
		$dw = $calendar->create('default-week');
		if (is_array($week) && !empty($week)) {
			$i = 0;
			for ($i=0;$i<7;$i++) {
				$w = $week[$i];
				switch($i) {
					case 0:
						$dw->mon($w);
						break;
					case 1:
						$dw->tue($w);
						break;
					case 2:
						$dw->wed($w);
						break;
					case 3:
						$dw->thu($w);
						break;
					case 5:
						$dw->fri($w);
						break;
					case 6:
						$dw->sat($w);
						break;
					case 7:
						$dw->sun($w);
						break;
				}
			}
		} else {
			#creo un calendario predeterminado
			$dw->mon('0')->tue('0')->wed('0')->thu('0')->fri('0')->sat('1')->sun('1');
		}
		#creo el sobre-escritor de atributos de inicio y fin de calendario
		$ot = $calendar->create('overridden-day-types');
		$odt = $ot->create('overridden-day-type')->id("0");
		#creo el intervalo que define las horas laborales
		$odt->create('interval')->attribute('start', '0800')->attribute('end', '1200');
		$odt->create('interval')->attribute('start', '1400')->attribute('end', '1800');
		#y el contenedor de dias
		$calendar->create('days');
		return $calendar;
	}

	#creacion de una propiedad especifica.
	public function property($name = '', $value = '', $type = 'text', $description = '') {
		#donde planner guarda las propiedades y sus valores:
		$prop = $this->getElementsByTagName('properties')->item(0);
		$val = $this->getElementsByTagName('properties')->item(1);
		$property = null;
		$props = $prop->getElementsByTagName('property');
		foreach($props as $p) {
			if ($p->getAttribute('name') == $name) {
				#si la encuentro, defino quien es:
				$property = $p;
				#luego, cambio su valor
				foreach($val->getElementsByTagName('property') as $v) {
					if ($v->getAttribute('name') == $name) {
						$v->setAttribute('value', $value);
					}
				}
				#luego, salgo del ciclo
				break;
			}
		}
		if (is_null($property)) {
			#creo una propiedad
			$property = $prop->create('property');
			$property->attribute('name', $name);
			#valor de la propiedad:
			$val->create('property')->attribute('name', $name)->attribute('value', $value);
		}
		#ahora, defino las propiedades:
		$property->setAttribute('label', $name);
		#descripcion:
		$property->setAttribute('description', $description);
		#tipo (text, integer or float)
		$property->setAttribute('type', $type);
		#propietario
		$property->setAttribute('owner', 'project');
		#retornamos esta propiedad
		return $property;
	}

	/**
	 * Permite crear y retornar un recurso definido por id
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $email
	 * @param string $cost
	 * @return tdom_element resource
	 */
	public function resource($id = '1', $name = '', $email = '', $cost = '') {
		$res = null;
		$resources = $this->getElementsByTagName('resources');
		foreach($resources as $r) {
			if ($r->getAttribute('id') == $id) {
				$res = $r;
				break;
			}
		}
		if (is_null($res)) {
			#si llego aca, el calendario no existe:
			$res = $this->create('resource');
			$res->id($id);
		}
		#nombre del atributo
		$res->setAttribute('name', $name);
		#nombre corto:
		$res->setAttribute('short-name', strtolower(str_replace(' ', '_', $name)));
		#tipo (1=trabajo, 2=material)
		$res->setAttribute('type', 1);
		#email
		$res->setAttribute('email', $email);
		#unidades, notas, etc
		$res->attribute('note', '')->attribute('units', 0);
		#valor del recurso
		$res->setAttribute('std-rate', $cost);
		return $res;
	}

}

/**
 * Es un nodo completo donde se define una tarea:
 *
 */
class planner_task extends tdom_element {

	public function __construct() {
		#creo una tarea:
		parent::__construct('task', null);
	}

	public function defaultProperties() {
		$this->work(0)->notes('')->complete(0)->priority(0);
		$this->type('normal')->schedule('fixed-work');
		#inicio, fin, estado
		$this->start()->end()->workstart();
		#creo el nodo que contiene los predecesores
		$this->appendChild(new tdom_element('predecessors'));
	}

	public function notes($notes = '') {
		$this->setAttribute('note', $notes);
		return $this;
	}
	public function work($work = '1') {
		$this->setAttribute('work', $work);
		return $this;
	}
	public function complete($complete = '0') {
		$this->setAttribute('percent-complete', $complete);
		return $this;
	}
	public function priority($p = '0') {
		$this->setAttribute('priority', $p);
		return $this;
	}
	public function type($type = 'normal') {
		$this->setAttribute('type', $type);
		return $this;
	}
	public function schedule($schedule = 'fixed-work') {
		$this->setAttribute('scheduling', $schedule);
		return $this;
	}

	// --- inicio, fin y estado de la tarea
	public function start($start = '') {
		$this->setAttribute('start', $this->date($start));
		return $this;
	}

	public function end($end = '') {
		$this->setAttribute('end', $this->date($end));
		return $this;
	}

	public function workstart($start = '') {
		$this->setAttribute('work-start', $this->date($start));
		return $this;
	}

	public function date($timestamp = '') {
		//format: 20090921T000000Z
		$format = 'omd\THis\Z';
		if ($timestamp == '') {
			return date($format);
		} else {
			return date($format, $timestamp);
		}
		#darle formato a la fecha para que sea DATE_ISO860
	}
}
?>