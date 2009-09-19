<?php
class tdom_text extends DOMText {
      	#atributos del nodo:
  	public function getText() {
  		return $this->textContent;
  	}

  	public function nodeName() {
  		return $this->nodeName;
  	}

  	public function nodeValue() {
  		return $this->nodeValue;
  	}

  	public function value($value = ''){
  		$this->nodeValue = $value;
  		return $this;
  	}

  	public function comment($comentario='') {
  		$this->appendChild(new DOMComment($comentario));
  		return $this;
  	}
        
    #obtiene todo el texto del nodo y sus descendientes:
    public function wholeText() {
        return $this->wholeText;
    }
    
    public function text() {
        return $this->wholeText;
    }
    
    #operacion con el texto
    
    /*
    @split: divide el texto de acuerdo al indice indicado
    */
    public function split($index) {
        return $this->splitText($index);
    }
    
    public function append($string) {
        $this->appendData($string);
    }
    
    public function insert($string) {
        $this->insertData($string);
    }
    
    public function replace($string, $offset = 1, $count = 1) {
        $this->deleteData($offset, $count, $string);
    }
    
    /*
     @delete: borra el numero de caracteres indicado por count, desde el offset indicado
    */
    public function delete($offset = 1, $count = 1) {
        $this->deleteData($offset, $count);
    }
    
    public function substring($offset = 1, $count = 1) {
        return $this->substringData($offset, $count);
    }
}
?>