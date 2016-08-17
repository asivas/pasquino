<?php
namespace pQn\datos\repote;
/**
* Define la clase PDFReportWriter que 
* 'Genera' un reporte en un archivo PDF
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      fichado
* @subpackage   reportes
* @version      0.7
*/

/**
 * directorio de definici�n de fuentes para PDF
 */
define('FPDF_FONTPATH','datos/reporte/font/');

/**
* 'Imprime' un reporte en un archivo de excel (XML para verion XP+)
*
* @author	    Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
* @copyright	Lucas Vidaguren <vidaguren@econ.unicen.edu.ar>
*
* @package      fichado
* @subpackage   reportes
* @version      0.7
*/

define(cRowHeight,7);

class PDFReportWriter extends reportWriter
{
    var $pdfFileName;
    var $pdfFilePath;
    //var $excellWriter;
    var $pdf;
    var $fileNamesList;
    var $cellWidth;
    var $rowHeight;
    var $defRowHeight;
    var $reportName;
    var $pageOrientation;
    
    var $fontHeightNormal;
    var $fontHeightBig;
    
    /**
     * Crea un nuevo objeto excelReportWriter y seteando las variables e inicializando
     * el excelWriter
     */
    function __construct($filePath="",$fileName="")
    {
        $this->pdfFilePath = $filePath;
        if(is_dir($this->pdfFilePath))
            chmod($this->pdfFilePath,0777);
        
        /*if(empty($fileName))
            $fileName = "(" . date("Y-m-d H;i;s").").pdf";
        */

        $this->setFileName($fileName);
        
        $this->pdf = new \FPDF();       
        $this->pdf->AliasNbPages();
        
        $this->cellWidth = 35;
        $this->defRowHeight = cRowHeight;
        $this->rowHeight = cRowHeight;
        
        $this->pageOrientation = 'L';

        $this->fontHeightNormal = 12;
        $this->fontHeightBig = 20;
        
    }
    
    /**
     * Cambia el objeto que escribe en formato PDF (para lo que se extiende)
     * @param object $pdf un objeto fpdf o instancia de una extensi�n de esa clase
     */
    function setPdfWriter($pdf)
    {
        $this->pdf = $pdf;
        $this->pdf->AliasNbPages();
    }
    
     /**
     * Cambia el nombre del archivo que se generar�
     * @param string $fileName el nuevo nombre de archivo
     */
    function setFileName($fileName)
    {
        $this->pdfFileName = $fileName;
        $this->fileNamesList[0] = "{$this->pdfFileName}";
    }
    
    /**
     * Recupera el nombre de archivo que generar�
     * @return string el nombre de archivo que ser� generado por esta clase
     */
    function getFileName($fileName)
    {
        return $this->pdfFileName;
    }
    
    /**
     * Cambia la ruta donde se generar� el archivo pdf
     * @param string $filePath la nueva ruta
     */
    function setFilePath($filePath)
    {
        $this->pdfFilePath = $filePath;
    }
    
    /**
     * Recupera la ruta en que se generar� el archivo pdf
     * @return string la ruta donde ser� generado el pdf por esta clase
     */
    function getFilePath($filePath)
    {
        return $this->pdfFilePath;
    }

    
    /**
	 * comienza la tabla de reporte
	 * @param integer $cellspacing el espaciado entre celdas del reporte
	 * @param integer $cellpadding la superposici�n entre celdas
	 * @param string $reportName El nombre base del reporte que se genera
	 */
	function startReport($cellspacing=3,$cellpadding=0,$reportName="Econtrol",$link="")
	{
	    //print "Nueva Secci�n ". $reportName."<br>";
	    $this->pdf->AddPage($this->pageOrientation);
	    $this->pdf->SetFont('Arial','',$this->fontHeightBig);	    
	    $this->pdf->Cell(0,10,$reportName,0,1,'L',0,$link);
	    $this->pdf->SetFont('Arial','',$this->fontHeightNormal);	    
	    $this->reportName = $reportName;

	    //print "$this->reportName <br>";
	    /*if(!$this->excelWriter->writeSheet($reportName))
	        printError($this->excelWriter->error."<br>");*/
    }
    
    /**
     * Finaliza el reporte
     * @param boolean $close si se cierra el archivo
     */
    function endReport($close=true)
    {
        if($close && !empty($this->fileNamesList[0]) )
        {
            //print "guardando en: ".$this->fileNamesList[0];
            $this->pdf->Output($this->pdfFilePath.$this->fileNamesList[0],'F');
        }
        elseif(empty($this->fileNamesList[0]))
        {
            $this->pdf->Output();
        }
        /*else
        {
            //$this->pdf->AddPage();
        }*/
    }
    
   
    /**
	 * comienza una linea nueva (rengl�n) de reporte
	 * @param string $style estilo css de la fila
	 */	
	function writeRow($style=NULL)
	{
	    $this->pdf->Ln($this->rowHeight);
	    $this->rowHeight = $this->defRowHeight;
	}
	
	/**
	 * Genera una celda de horas cumplidas (color verde)
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */
	function writeGreenCell($value,$colspan=NULL,$link=NULL)
	{
        $this->pdf->SetTextColor(0,128,0);
        $this->writeCell($value,$link,NULL,$colspan);
        $this->pdf->SetTextColor(0);
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
	    $this->pdf->SetTextColor(255,0,0);
	    
        $this->writeCell($value,$link,NULL,$colspan);
        $this->pdf->SetTextColor(0);
	}
	
	/**
	 * Genera una celda en la tabla de reporte
	 * @param $value el contenido de la celda
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 * @param string $style la clase CSS que define el estilo de la celda
	 */
	function writeCell($value,$link=NULL,$style=NULL,$colspan=NULL,$border="T")
	{
        $value = str_replace('&nbsp;',' ',$value);
        $width = $this->cellWidth;
        if(!empty($colspan)) $width *= $colspan; 
        
        $this->pdf->SetFont('');
        
        $nStrWidth = $this->pdf->GetStringWidth($value);
        
        if($this->reportName == 'Fuera Por Trabajo' && round($this->pdf->GetX()) >= $this->cellWidth * 2 && round($this->pdf->GetX()) <= $this->cellWidth * 3)
            $width *= 4;
        
        if($nStrWidth > $width)
        {
            $nuevoX = $this->pdf->GetX() + $width;
            $nuevoY = $this->pdf->GetY();
            $this->pdf->MultiCell($width,$this->defRowHeight,$value,$border,'L');
            $yResultante = $this->pdf->GetY();
            if(($yResultante - $nuevoY) >= $this->rowHeight)
                $this->rowHeight = $yResultante - $nuevoY;
            $this->pdf->SetXY($nuevoX,$nuevoY);
        }            
        else
        {        
            $this->pdf->Cell($width,$this->rowHeight,$value,$border,0,'L',0,$link);
        }
        //$this->pdf->MultiCell(25,7,$value,1,'L');
	}
	
	/**
	 * Genera una celda de encabezado
	 *
	 * @param $value el contenido del header
	 * @param string $link la URL a donde apunta si el header es un link 
	 * @param integer $colspan la cantidad de columnas que ocupa el header
	 */	
	function writeHeader($value,$link = NULL,$colspan=NULL,$border=1)
	{
	    $this->pdf->tag = 1;
	    $this->pdf->SetFont('','B');
	    $width = $this->cellWidth;
	    //print round($this->pdf->GetX()) . " " . ($this->cellWidth * 2) .  " $value <br>";
	    if($this->reportName == 'Fuera Por Trabajo' && round($this->pdf->GetX()) >= $this->cellWidth * 2 && round($this->pdf->GetX()) <= $this->cellWidth * 3)
            $width *= 4;
        $this->pdf->Cell($width,$this->rowHeight,$value,$border,0,'L',0,$link);
        //$this->pdf->MultiCell(25,7,$value,1,'L');
        $this->pdf->tag = -1;
        $this->pdf->SetFont('','B');
	}
}
?>