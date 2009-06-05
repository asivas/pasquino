<?php
require_once("HTML/QuickForm.php");
require_once("HTML/QuickForm/Renderer/ArraySmarty.php");

class BaseForm extends HTML_QuickForm {
    
    function __construct($nombre, $metodo='POST', $accion='',$target='',$attributos='') {
    	
    	if(empty($accion)) $accion = $_SERVER['PHP_SELF'];
    	parent::__construct($nombre,$metodo,$accion,$target,$attributos);
    }
    
    /**
     * Genera el arreglo renderizado para smarty del form
     * @return array arreglo renderizado con el renderer de array de smarty
     */
    protected function renderSmarty($smarty)
    {
        $renderer= new HTML_QuickForm_Renderer_ArraySmarty($smarty);// creacion del renderer para smarty
        
        $this->accept($renderer);// inclusion en el form del renderer
        
        return $renderer->toArray();// pasaje a arreglo del renderer   	
    }
    
    /**
     * Crea el input con el calendario selector de fecha
     * @return String con el html listo para insertar en el template
     */
    function getCalendarInput($name, $value = "", $format = null)
    {
        if(is_null($format)) $format = $this->_dateFormat;
        ob_start();
        $this->_calendar->make_input_field(
        // calendar options go here; see the documentation and/or calendar-setup.js
        array('firstDay'       => 1, // show Monday first
              'showsTime'      => false,
              'singleClick'    => true,
              'showOthers'     => true,
              'ifFormat'       => $format
             ),
        // field attributes go here
        array('name'        => $name,
              'value'       => $value));
        return ob_get_clean();
    }
    
    /**
     * Genera un arreglo con opciones para un select
     * @param array $listaElementos Lista de elementos que deben tener getId y getNombre definidos
     * @param integre $vacio si se debe crear una opcion vacia
     * @param integre $otro si se debe crear una opcion de "Otro", si está definido el nro será el id
     * @return array arreglo asociativo id => nombre 
     */
    function getArregloSelect($listaElementos,$vacio=true,$otro=null,$otroLabel='Otra')
    {
        $arregloOpciones = array();
        
        if($vacio)
            $arregloOpciones[0] = '';
        
        
        if(is_array($listaElementos))     
            foreach($listaElementos as $elem)
            {
                if(method_exists($elem,'getId') && method_exists($elem,'getNombre'))
                    $arregloOpciones[$elem->getId()] = $elem->getNombre();
            }
        
        if(isset($otro))
            $arregloOpciones[$otro] = $otroLabel;
        
        return $arregloOpciones;
    }
    
    /**
     * Obtiene el código HTML de un input select
     * @param string $name
     * @param array $options opciones compatibles con las opciones de HTML_QuickForm_select
     * @param mixed $attributes atributos compatibles con los atributos de HTML_QuickForm_select
     */
    function getSelectInput($name,$options,$attributes,$selected=null)
    {
        $this->_form->addElement('select',$name,'label:',$options,$attributes);
        
        if(isset($selected))
        {
            $this->_form->setDefaults(array($name=>$selected)); 
        }   
        
        $rendered = $this->getRenderedForm();
       
        return $rendered[$name]['html'];
    }
}