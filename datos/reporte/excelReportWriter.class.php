<?
/**
* Define la clase excelReportWriter que 
* 'Imprime' un reporte en un archivo de excel (XML para verion XP+)
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      datos
* @subpackage   reporte
* @version      0.7
*/

/**
 * clase de la que se extinde
 */
require_once("datos/reporte/reportWriter.class.php");
/**
 * La interfaz para escribir en excel
 */
require_once("formato/xls/excelWriter.class.php");

/**
* 'Imprime' un reporte en un archivo de excel (XML para verion XP+)
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      datos
* @subpackage   reporte
* @version      0.7
*/
class excelReportWriter extends reportWriter
{
    var $excelFileName;
    var $excelFilePath;
    var $excellWriter;
    var $fileNamesList;
    
    /**
     * Crea un nuevo objeto excelReportWriter y seteando las variables e inicializando
     * el excelWriter
     */
    function excelReportWriter($excelFilePath,$excelFileName)
    {
        $this->excelFilePath = $excelFilePath;
        chmod($this->ecxelFilePath,0777);
        if(empty($excelFileName))
            $this->excelFileName = "(" . date("Y-m-d H;i;s").").xls";
        else
            $this->excelFileName .= $excelFileName
            
        $this->excelWriter = new ExcelWriter("{$this->excelFilePath}/{$this->excelFileName}",$this->excelFileName);
        $this->fileNamesList[] = "{$this->excelFileName}";
    }
    
    /**
     * Cambia el nombre del archivo que se generará
     * @param string $fileName el nuevo nombre de archivo
     */
    function setFileName($fileName)
    {
        $this->excelFileName = $fileName;
    }
    
    /**
     * Recupera el nombre de archivo que generará
     * @return string el nombre de archivo que será generado por esta clase
     */
    function getFileName($fileName)
    {
        return $this->excelFileName;
    }
    
    /**
     * Cambia la ruta donde se generará el archivo xls
     * @param string $filePath la nueva ruta
     */
    function setFilePath($filePath)
    {
        $this->excelFilePath = $filePath;
    }
    
    /**
     * Recupera la ruta en que se generará el archivo xls
     * @return string la ruta donde será generado el xls por esta clase
     */
    function getFilePath($filePath)
    {
        return $this->excelFilePath;
    }
    
    /**
	 * comienza la tabla de reporte
	 * @param integer $cellspacing el espaciado entre celdas del reporte
	 * @param integer $cellpadding la superposición entre celdas
	 * @param string $reportName El nombre base del reporte que se genera
	 */
	function startReport($cellspacing=3,$cellpadding=0,$reportName="Econtrol")
	{
	    $this->excelWriter->writeSheet($reportName)
	    /*if(!$this->excelWriter->writeSheet($reportName))
	        printError($this->excelWriter->error."<br>");*/
    }
    
    /**
     * Finaliza el reporte
     * @param boolean $close si se cierra el archivo
     */
    function endReport($close=true)
    {
        if($close)
            $this->excelWriter->close();
        else
            $this->excelWriter->endSheet();
    }
    
    /**
	 * comienza una linea nueva (renglón) de reporte
	 * @param string $style estilo css de la fila
	 */	
	function writeRow($style=NULL)
	{
	    $this->excelWriter->writeRow();
	}
	
	/**
	 * Genera una celda de horas cumplidas (color verde)
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */
	function writeGreenCell($value,$colspan=NULL,$link=NULL)
	{
        $this->excelWriter->writeCell($value,$link,"green",$colspan);
	}

	/**
	 * Genera una celda de horas no cumplidas (color rojo)
	 *
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header	 
	 */	
	function writeRedCell($value,$colspan=NULL,$link=NULL)
	{
	    $this->excelWriter->writeCell($value,$link,"red",$colspan);
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
	    $this->excelWriter->writeCell($value,$link,NULL,$colspan);
	}
	
	/**
	 * Genera una celda de encabezado
	 *
	 * @param $value el contenido del header
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */	
	function writeHeader($value,$link = NULL,$colspan=NULL)
	{
	    $this->excelWriter->writeBoldCell($value,$link,NULL,$colspan);
	}
}
?>