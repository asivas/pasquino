<?php
require_once("HTML/QuickForm.php");
require_once("HTML/QuickForm/Renderer/ArraySmarty.php");

if(!function_exists('esFecha'))
{
    /**
     * Funcion de regla para validar fechas en quickform
     */
    function esFecha($fecha,$puedeSerVacia = false)
    {
        $vacio = empty($fecha);
        if($puedeSerVacia) $vacio = false; 
        
        return !$vacio && dateTimeFmt::fechaArgtotime($fecha)!==FALSE;
    }
}

/**
 * Base para los formularios de modulo de pasquino
 */
class BaseForm extends HTML_QuickForm {
    function __construct($nombre, $metodo='POST', $accion='',$target='',$attributos='') {
    	
    	if(empty($accion)) $accion = $_SERVER['PHP_SELF'];
    	parent::__construct($nombre,$metodo,$accion,$target,$attributos);
        
        $this->registerRule('fecha','callback','esFecha');
        
        $this->addElement('hidden','accion',$_REQUEST['accion']);
    }
    
    /**
     * Genera el arreglo renderizado para smarty del form
     * @return array arreglo renderizado con el renderer de array de smarty
     */
    function renderSmarty($smarty)
    {
        $renderer= new HTML_QuickForm_Renderer_ArraySmarty($smarty);// creacion del renderer para smarty
        
        $this->accept($renderer);// inclusion en el form del renderer
        
        return $renderer->toArray();// pasaje a arreglo del renderer   	
    }
    
    /**
     * Crea el input con el calendario selector de fecha
     * @return String con el html listo para insertar en el template
     */
    function getCalendarInput($objCal ,$name, $value = "", $format = null, $baseID = null)
    {
        if(empty($format)) $format = Configuracion::getDateFormat();
        
        if(empty($format)) $format = "%Y-%m-%d";
        
        $cal = $objCal->get_input_field(
        // calendar options go here; see the documentation and/or calendar-setup.js
        array('firstDay'       => 1, // show Monday first
              'showsTime'      => false,
              'singleClick'    => true,
              'showOthers'     => true,
              'ifFormat'       => $format
             ),
        // field attributes go here
        array('name'        => $name,
              'value'       => $value),
        $baseID);
        return $cal;
    }
    
    /**
     * Genera un arreglo con opciones para un select
     * @param array $listaElementos Lista de elementos que deben tener getId y getNombre definidos
     * @param integer $vacio si se debe crear una opcion vacia
     * @param integer $otro si se debe crear una opcion de "Otro", si está definido el nro será el id
     * @return array arreglo asociativo id => nombre 
     */
    function getArregloSelect($listaElementos,$vacio=true,$otro=null,$otroLabel='Otra',$getNombreFunc='getNombre',$getIdFunc='getId')
    {
        $arregloOpciones = array();
        
        if($vacio)
            $arregloOpciones[0] = '';
        
        
        if(is_array($listaElementos))     
            foreach($listaElementos as $elem)
            {
                if(method_exists($elem,$getIdFunc) && method_exists($elem,$getNombreFunc))
                    $arregloOpciones[$elem->$getIdFunc()] = $elem->$getNombreFunc();
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
    function getSelectInput($smarty,$name,$options,$attributes,$selected=null)
    {
        $element = $this->addElement('select',$name,'label:',$options,$attributes);
        
        if(isset($selected))
        {
            $element->setSelected($selected); 
        }   
        
        $rendered = $this->renderSmarty($smarty);
       
        return $rendered[$name]['html'];
    }
    
    /**
     * Asigna un valor a un hidden, si este no existe lo crea
     */
    function setHidden($nombre,$valor)
    {
    	$elem = $this->getElement($nombre);
         
        if(isset($elem) && !$this->isError($elem))
           $elem->setValue($valor);
        else
           $this->addElement('hidden',$nombre,$valor);
    }
    
    function getSelectValue($nombreCampo)
    {
    	$arrVal = $this->getElementValue($nombreCampo);
    	return current($arrVal);
    }
    
    function caracteres_html($str)
    {
        $tr = array('á'=>'&aacute;','é'=>'&eacute;','í'=>'&iacute;','ó'=>'&oacute;','ú'=>'&uacute;',
                    'Á'=>'&Aacute;','É'=>'&eacute;','Í'=>'&iacute;','Ó'=>'&oacute;','Ú'=>'&uacute;',
                    'ñ'=>'&ntilde;','Ñ'=>'&Ntilde;','ü'=>'&uuml;','Ü'=>'&Uuml;');
        return strtr($str,$tr);	
    }
}