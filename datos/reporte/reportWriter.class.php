<?php
namespace pQn\datos\repote;
/**
* Se define la clase reportWriter
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      fichado
* @subpackage   reportes
* @version      0.7
*/


/**
* 'Imprime' un reporte en pantalla
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      fichado
* @subpackage   reportes
* @version      0.7
* @deprecated use pQn\SistemaFCE\BaseMod::list to reports
*/
class reportWriter
{
    /**
	 * @var boolean
	 */
	var $_newRow;
	
	/**
	 * @var integer
	 */
	var $rowStyle;
	
    /**
	 * comienza la tabla de reporte
	 * @param integer $cellspacing el espaciado entre celdas del reporte
	 * @param integer $cellpadding la superposici�n entre celdas
	 * @param string $reportName El nombre base del reporte que se genera
	 */
	function startReport($cellspacing=3,$cellpadding=0,$reportName="")
	{
	    print "
	    <style>
	    .redReportCell {color:red;}
	    .greenReportCell {color:green;}
	    </style>
	    ";
	    print "<table align=\"left\" width=\"100%\" cellspacing=$cellspacing cellpadding=$cellpadding>";
    }
    
    /**
     * finaliza el reporte
     * @param boolean $finalize si se debe cerrar la �ltima tabla
     */
    function endReport($finalize = false)
    {
        if(!$finalize)
        {
            if($this->_newRow!=false) print "</tr>";
            print "</table>";
        }
        
    }
    
    /**
	 * comienza una linea nueva (rengl�n) de reporte
	 * @param string $style estilo css de la fila
	 */	
	function writeRow($style=NULL)
	{
	    $this->rowStyle = ($this->rowStyle % 2) + 1 ;
	    if($style==NULL)
		{		    		  
			if($this->_newRow==false)
				print "<tr class=\"reportRow$this->rowStyle\">";
			else
				print "</tr>\n<tr class=\"reportRow$this->rowStyle\">";
		}
		else
		{
			if($this->_newRow==false)
				print "<tr class=\"$style{$this->rowStyle}\">";
			else
				print "</tr>\n<tr class=\"$style{$this->rowStyle}\">";
		}
		$this->_newRow=true;	
	}
	
	/**
	 * Genera una celda de horas cumplidas (color verde)
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */
	function writeGreenCell($value,$colspan=NULL,$link=NULL)
	{
        //print "<td class=\"horasCumplidas\">$value</td>\n";
        $this->writeCell($value,$link,"greenReportCell",$colspan);
	}

	/**
	 * Genera una celda de horas no cumplidas (color rojo)
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header	 
	 */	
	function writeRedCell($value,$colspan=NULL,$link=NULL)
	{
        $this->writeCell($value,$link,"redReportCell",$colspan);
	}
	
	/**
	 * Genera una celda en la tabla de reporte
 	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 * @param string $style la clase CSS que define el estilo de la celda
	 */
	function writeCell($value,$link=NULL,$style=NULL,$colspan=NULL)
	{
	    if(isset($link))  $value = "<a href=\"$link\">$value</a>";
	    if(isset($colspan))$colspan = " colspan=\"$colspan\"";
	    if(isset($style))
	        print "<td{$colspan} class=\"$style\">$value</td>\n";
	    else
	        print "<td{$colspan}>$value</td>\n";
	}
	
	/**
	 * Genera una celda de encabezado
	 * @param $value el contenido del header
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */	
	function writeHeader($value,$link=NULL,$colspan=NULL,$rowspan=NULL)
	{
	    if(isset($colspan)) $extras = " colspan=\"$colspan\"";
	    if(isset($rowspan)) $extras .= " rowspan=\"$rowspan\"";
	    if(isset($link)) $value = "<a href=\"$link\">$value</a>";
	    print "<th $extras class=\"reporte\">$value</th>\n";
	}
}
?>