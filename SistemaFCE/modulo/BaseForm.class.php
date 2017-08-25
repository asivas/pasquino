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

    /**
     *
     * Arreglo de los textos con las informaciones de los elementos que necesitan una descripción
     * @var array
     */
	protected $_elementsInfo;

	function __construct($nombre=NULL, $metodo='POST', $accion='',$target='',$attributos='') {

    	if(!isset($nombre))
    		$nombre = str_replace("Form", "", get_class($this));

        $path = Configuracion::getGessedAppRelpath();

    	if(empty($accion)) $accion = $path;
    	//me aseguro por si envian explicitamente por error null
    	if(!isset($metodo))	$metodo = 'POST';
    	parent::__construct($nombre,$metodo,$accion,$target,$attributos);

        $this->registerRule('fecha','callback','esFecha');

        $this->addElements();
        $this->addRules();
    }

    /**
     *
     * Agrega los elementos por defecto del formulario
     *
     * A este metodo debe hacersele override para no tener que definir la __construct en las clases que extiendan esta
     * en caso de agregar elementos por defecto.
     * Tener en cuenta que es aquí donde se agrega el hidden accion, por lo tanto en el override se recomienda llamar a
     * parent::addElements();
     *
     */
    protected function addElements()  {
    	$this->addElement('hidden','accion',$_REQUEST['accion']);
    }

	/**
     *
     * Agrega las reglas por defecto del formulario
     *
     * A este metodo debe hacersele override para no tener que definir la __construct en las clases que extiendan esta
     * si se quieren agregar reglas
     */
    protected function addRules()  {    }

    /**
     * Debe generar el arreglo de defaults a partir del objeto entidad $elem
     *
     * A este metodo debe hacersele override para poder usar correctamente el setDefaults implementado en BaseForm
     *
     * @param object $elem el objeto de la entidad principal del form
     *
     */
    protected  function getDefaultsArray($elem) {   return array(); }

    /**
     * Extiende el setDefaults de Quickform permitiendo pasar como parametro un objeto elemento o un array
     *
     * @param mixed $objOArr puede ser un objeto de elemento conocido en getDefaultsArray o un arreglo
     */
    function setDefaults($objOArr) {
    	$defaultValues = array();
		if(is_array($objOArr)){
			$defaultValues = $objOArr;
		}
		else{
			$defaultValues = $this->getDefaultsArray($objOArr);
		}
		parent::setDefaults($defaultValues);
    }

    /**
     * Genera el arreglo renderizado para smarty del form
     * @return array arreglo renderizado con el renderer de array de smarty
     */
    function renderSmarty($smarty)
    {
        $renderer= new HTML_QuickForm_Renderer_ArraySmarty($smarty);// creacion del renderer para smarty

        $this->accept($renderer);// inclusion en el form del renderer
        $rendered = $renderer->toArray();// pasaje a arreglo del renderer
        if(is_array($this->_elementsInfo))
        foreach($this->_elementsInfo as $elemName => $info)
        {
        	$rendered[$elemName]['info'] = $info;
        }
        return $rendered;
    }

    /**
     * Crea el input con el calendario selector de fecha
     * @return String con el html listo para insertar en el template
     * @deprecated usar jQuery para inputs de fecha http://jqueryui.com/demos/datepicker/
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
     * @param integer $otro si se debe crear una opcion de "Otro", si est� definido el nro ser� el id
     * @return array arreglo asociativo id => nombre
     */
    static function getArregloSelect($listaElementos,$vacio=true,$otro=null,$otroLabel='Otra',$getNombreFunc='getNombre',$getIdFunc='getId')
    {
        $arregloOpciones = array();

        if($vacio)
        {
            if($vacio===true)
            	$vacio = '';
        	$arregloOpciones[0] = $vacio;
        }


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
        $tr = array('�'=>'&aacute;','�'=>'&eacute;','�'=>'&iacute;','�'=>'&oacute;','�'=>'&uacute;',
                    '�'=>'&Aacute;','�'=>'&eacute;','�'=>'&iacute;','�'=>'&oacute;','�'=>'&uacute;',
                    '�'=>'&ntilde;','�'=>'&Ntilde;','�'=>'&uuml;','�'=>'&Uuml;',
                    'á'=>'&aacute;','é'=>'&eacute;','í'=>'&iacute;','ó'=>'&oacute;','ú'=>'&uacute;',
                    'Á'=>'&Aacute;','É'=>'&Eacute;','Í'=>'&Iacute;','Ó'=>'&Oacute;','Ú'=>'&Uacute;',
                    'ñ'=>'&ntilde;','Ñ'=>'&Ntilde;','ü'=>'&uuml;','Ü'=>'&Uuml;');
        return strtr($str,$tr);
    }

    /**
     *
     *  Agregar un texto o html descriptivo de información sobre un elemento
     *  @param string $elementName nombre del elemento del cual será la información
     *  @param string $elementInfo información para el elemento
     */
    function addElementInfo($elementName,$elementInfo)
    {
    	//veo si existe el elemento al que le agrego info
    	$e = $this->getElement($elementName);
    	if(!PEAR::isError($e))
    		$this->_elementsInfo[$elementName] = $elementInfo;
    }

    /**
     * Removes an element
     *
     * The method "unlinks" an element from the form, returning the reference
     * to the element object. If several elements named $elementName exist,
     * it removes the first one, leaving the others intact.
     *
     * @param string    $elementName The element name
     * @param boolean   $removeRules True if rules for this element are to be removed too
     * @access public
     * @since 2.0
     * @return HTML_QuickForm_element    a reference to the removed element
     * @throws HTML_QuickForm_Error
     */
    function &removeElement($elementName, $removeRules = true)
    {
    	if(isset($this->_elementsInfo))
    		unset($this->_elementsInfo[$elementName]);
    	return parent::removeElement($elementName,$removeRules);
    }
}