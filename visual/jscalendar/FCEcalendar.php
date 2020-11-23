<?php
namespace pQn\visual\jscalendar;
require_once("calendar.php");

class FCEcalendar extends \DHTML_Calendar {

    function get_input_field($cal_options = array(), $field_attributes = array(),$baseID = null) {
        $id = $this->_gen_id();
        $attrstr = $this->_make_html_attr(array_merge($field_attributes,
                                                      array('id'   => $this->_field_id($id,$baseID),
                                                            'type' => 'text')));
        $html = '<input ' . $attrstr .'/>';
        $html.= '<a href="#" id="'. $this->_trigger_id($id,$baseID) . '">' .
            '<img align="middle" border="0" src="' . $this->calendar_lib_path . 'b_calendar.png" alt="" /></a>';

        $options = array_merge($cal_options,
                               array('inputField' => $this->_field_id($id,$baseID),
                                     'button'     => $this->_trigger_id($id,$baseID)));
		$respuesta['html'] = $html;
		$respuesta['js'] = $this->_make_js_calendar($options);
		return $respuesta;
   }

     function _make_js_calendar($other_options = array()) {
        $js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
        $code  = ( 'Calendar.setup({'.$js_options.'})');
        return $code;
    }
    
    function _trigger_id($id,$base=null) { 
    	if($base!=null)
    		return $base . "-trigger-" . $id; 
    	else
    		return 'f-calendar-trigger-' . $id; 
    }
    
    function _field_id($id,$base=null) {
    	 if($base!=null)
    		return $base . "-field-" . $id; 
    	else
    		return 'f-calendar-field-' . $id; 
   	}
}
