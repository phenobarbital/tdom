<?php
class tdom_planner extends tdom_xml {
	#espacio xml
	public $_namespace = '';

	protected $_public = '';

	#tipo de documento (xml)
	protected $_dom_type = 'xml';
	protected $_mime = 'application/xml';

	#nombre del nodo base
	protected $_base_node = 'project';

	/**
	 * objeto base kml
	 *
	 * @var tdom_element
	 */
	protected $_base = null;

	#personalizacion del DOM element class
	protected $_element_class = 'tdom_planner_element';

	#objetos bases del documento:
	protected $_phases = null;
	protected $_calendars = null;
	protected $_tasks = null;
	protected $_groups = null;
	protected $_resources = null;
	protected $_allocations = null;

	#sector de las propiedades
	protected $_props = null;
	protected $_propval = null;

	public function __construct() {
		include_once 'tdom_planner_element.php';
		parent::__construct();
		#luego de construido, agrego las secciones obligatorias
		$this->_base = $this->element($this->_base_node);
		#nombre del proyecto
		$this->_base->attribute('name', '');
		#company of project
		$this->_base->company('')->manager('')->phase('')->attribute('project-start', $this->date());
		$this->_base->attribute('mrproject-version', '2');
		$this->_base->attribute('calendar', '1');
		#propiedades del proyecto
		$this->_prop = $this->_base->create('properties');
		$this->_propval = $this->_base->create('properties');
		#creo el contenedor de fases:
		$this->phases();
		#creo el contenedor de calendarios:
		$this->calendars();
		#creo el calendario por defecto
		$this->defaultCalendar();
		#contenedor de tareas:
		$this->tasks();
		#contenedor de grupos de recursos
		$this->groups();
		#contenedor de recursos
		$this->resources();
		#contenedor de ubicaciones
		$this->allocations();
	}

	// -- Propiedades del proyecto
	public function name($name = '') {
		$this->_base->attribute('name', $name);
		return $this;
	}

	public function manager($name = '') {
		$this->_base->attribute('manager', $name);
		return $this;
	}

	public function company($name = '') {
		$this->_base->attribute('company', $name);
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
	
	public function project() {
		return $this->_base;
	}

	#retorna el sector de las fases del documento
	public function phases() {
		return $this->_getbase('phases');
	}

	public function calendars() {
		return $this->_getbase('calendars');
	}

	public function tasks() {
		return $this->_getbase('tasks');
	}

	public function resources() {
		return $this->_getbase('resources');
	}

	public function allocations() {
		return $this->_getbase('allocations');
	}

	public function groups() {
		return $this->_getbase('resource-groups', '_groups');
	}

	protected function _getbase($name = 'project', $varname = '') {
		if ($varname == '') {
			$varname = '_' . $name;
		}
		if ($this->_base->getElementsByTagName($name)->item(0)== null) {
			$this->$varname = $this->_base->create($name);
		} elseif (($this->$varname == null)) {
			$this->$varname = $this->_base->getElementsByTagName($name)->item(0);
		}
		return $this->$varname;
	}

	public function defaultCalendar() {
		$calendar = $this->element('calendars');
		#creo los tipos de dias:
		if (!$dt = $calendar->element('day-types')) {
			$dt = $calendar->create('day-types');
		}
		#tipos de dias:
		$dt->create('day-type')->id('0')->attribute('name', 'Work')->attribute('description', 'Default Works Days');
		$dt->create('day-type')->id('1')->attribute('name', 'Rest')->attribute('description', 'Rest days');
		$dt->create('day-type')->id('2')->attribute('name', 'Base')->attribute('description', 'Use Base');
		#creo el calendario por defecto
		$calendar->calendar('1', 'default');
	}
	
	/**
	 * crea una tarea
	 * @return planner_task $task
	 */
	public function createTask($id = '1', $name = '') {
		$task = new planner_task();
		$this->_tasks->appendChild($task);
		$task->id($id);
		$task->setAttribute('name', $name);
		#atributos basicos de la tarea, vacios o con valores por defecto:
		$task->defaultProperties();
		return $task;
	}
}
?>