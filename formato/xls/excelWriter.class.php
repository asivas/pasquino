<?php
namespace pQn\formato\xls;
/**
* Define la clase excelWriter que 
* Permite escribir archivos en formato XML que interpreta el Excel XP+
*
* @author	    Harish Chauhan
* @copyright	Harish Chauhan
* Date   : 31 Dec,2004
*
* @package      fichado
* @subpackage   reportes
* @version      0.7
*/
	
     /*
     ###############################################
     ####                                       ####
     ####    Author : Harish Chauhan            ####
     ####    Date   : 31 Dec,2004               ####
     ####    Updated:                           ####
     ####                                       ####
     ###############################################

     */

	 
	 /**
	 * Class is used for save the data into microsoft excel format.
	 * It takes data into array or you can write data column vise.
	 * @package reportes
	 * @deprecated use PHPExcel instead
	 */
	Class ExcelWriter
	{
			
		var $fp;
		var $error;
		var $state;
		var $bold;
		var $newRow;
		var $newSheet;
		
		var $worksheet;
		
		/*
		* @Params : $file  : file name of excel file to be created.
		* @Return : On Success Valid File Pointer to file
		* 			On Failure return false	 
		*/
		 
		function ExcelWriter($file="",$workSheet="econtrol")
		{
		    $this->fp=null;
		    $this->state ="CLOSED"; 
		    $this->newRow=false;
		    $this->newSheet=false;
		    $this->worksheet = $workSheet;
			
			return $this->open($file);
		}
		
		/*
		* @Params : $file  : file name of excel file to be created.
		* 			if you are using file name with directory i.e. test/myFile.xls
		* 			then the directory must be existed on the system and have permissioned properly
		* 			to write the file.
		* @Return : On Success Valid File Pointer to file
		* 			On Failure return false	 
		*/
		function open($file)
		{
			if($this->state!="CLOSED")
			{
				$this->error="Error : Ya hay otro archivo abierto. Cierrelo para guardar el archivo";
				return false;
			}	
			
			if(!empty($file))
			{
				$this->fp=@fopen($file,"w+");
			}
			else
			{
				$this->error="Modo de uso : New ExcelWriter('fileName')";
				return false;
			}	
			if($this->fp==false)
			{
				$this->error="Error: No se puede abrir/crear el archivo. Puede no tener permisos para escribir el archivo.";
				return false;
			}
			$this->state="OPENED";
			fwrite($this->fp,$this->GetHeader());
			return $this->fp;
		}
		
		function close()
		{
			if($this->state!="OPENED")
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if($this->newRow)
			{
				fwrite($this->fp," </Row>");
				$this->newRow=false;
			}
			if($this->newSheet)
			{
				fwrite($this->fp," </Table>\n</Worksheet>");
				$this->newSheet=false;
			}
			
			fwrite($this->fp,$this->GetFooter());
			fclose($this->fp);
			$this->state="CLOSED";
			return ;
		}
		/* @Params : Void
		*  @return : Void
		* This function write the header of Excel file.
		*/
		 							
		function GetHeader()
		{
		    $ctime = date("Y-m-d\Th:i:s\Z");
			$header = <<<EOH
<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>{$_SESSION['DNI']}</Author>
  <LastAuthor>{$GLOBALS['usuario']->apellido} {$GLOBALS['usuario']->nombre}</LastAuthor>
  <Created>$ctime</Created>
  <LastSaved>$ctime</LastSaved>
  <Version>11.5606</Version>
 </DocumentProperties>
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <DownloadComponents/>
  <LocationOfComponents HRef="file:///G:\"/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>10005</WindowHeight>
  <WindowWidth>10005</WindowWidth>
  <WindowTopX>120</WindowTopX>
  <WindowTopY>135</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="comun">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="comunbold">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:Bold="1"/>
  </Style>
  <Style ss:ID="green">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Font ss:Color="#008000"/>
  </Style>
  <Style ss:ID="red">
   <Font ss:Color="#FF0000"/>
  </Style>
  <Style ss:ID="greenbold">
    <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
    <Font x:Family="Swiss" ss:Color="#008000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="redbold">
    <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
    <Font x:Family="Swiss" ss:Color="#FF0000" ss:Bold="1"/>
  </Style>
 </Styles>
				
EOH;

/***
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<head>
				<meta http-equiv=Content-Type content="text/html; charset=us-ascii">
				<meta name=ProgId content=Excel.Sheet>
				<!--[if gte mso 9]><xml>
				 <o:DocumentProperties>
				  <o:Author>{$_SESSION['DNI']}</o:Author>
				  <o:LastAuthor>Econtrol</o:LastAuthor>
				  <o:Version>10.2625</o:Version>
				 </o:DocumentProperties>
				 <o:OfficeDocumentSettings>
				  <o:DownloadComponents/>
				 </o:OfficeDocumentSettings>
				</xml><![endif]-->
				<style>
				<!--table
					{mso-displayed-decimal-separator:"\.";
					mso-displayed-thousand-separator:"\,";}
				@page
					{margin:1.0in .75in 1.0in .75in;
					mso-header-margin:.5in;
					mso-footer-margin:.5in;}
				tr
					{mso-height-source:auto;}
				col
					{mso-width-source:auto;}
				br
					{mso-data-placement:same-cell;}
				.style0
					{mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					white-space:nowrap;
					mso-rotate:0;
					mso-background-source:auto;
					mso-pattern:auto;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:comun;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					border:none;
					mso-protection:locked visible;
					mso-style-name:comun;
					mso-style-id:0;}
				td
					{mso-style-parent:style0;
					padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:comun;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					border:none;
					mso-background-source:auto;
					mso-pattern:auto;
					mso-protection:locked visible;
					white-space:nowrap;
					mso-rotate:0;}
				.xl24
					{mso-style-parent:style0;
					white-space:comun;}
				-->
				</style>
				<!--[if gte mso 9]><xml>
				 <x:ExcelWorkbook>
				  <x:ExcelWorksheets>
				   <x:ExcelWorksheet>
					<x:Name>{$this->worksheet}</x:Name>
					<x:WorksheetOptions>
					 <x:Selected/>
					 <x:ProtectContents>False</x:ProtectContents>
					 <x:ProtectObjects>False</x:ProtectObjects>
					 <x:ProtectScenarios>False</x:ProtectScenarios>
					</x:WorksheetOptions>
				   </x:ExcelWorksheet>
				  </x:ExcelWorksheets>
				  <x:WindowHeight>10005</x:WindowHeight>
				  <x:WindowWidth>10005</x:WindowWidth>
				  <x:WindowTopX>120</x:WindowTopX>
				  <x:WindowTopY>135</x:WindowTopY>
				  <x:ProtectStructure>False</x:ProtectStructure>
				  <x:ProtectWindows>False</x:ProtectWindows>
				 </x:ExcelWorkbook>
				</xml><![endif]-->
				</head>

				<body link=blue vlink=purple>
				<table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse: collapse;'>
***/
			return $header;
		}

		function GetFooter()
		{
			//return "</table></body></html>";
			if($this->newRow)
			{
				$ret = " </Row>";
				$this->newRow=false;
			}
			if($this->newSheet)
			{
			    $ret .= " </Table>\n</Worksheet>";
			    $this->newSheet = false;
			}
			$ret .= "\n</Workbook>";
			return $ret;
		}
		
		/*
		* @Params : $line_arr: An valid array 
		* @Return : Void
		*/
		 
		function writeLine($line_arr)
		{
			if($this->state!="OPENED")
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if(!is_array($line_arr))
			{
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			fwrite($this->fp,"<Row>");
			foreach($line_arr as $col)
			    $this->writeCell($col);
				//fwrite($this->fp,"<td class=xl24 width=64 >$col</td>");
			fwrite($this->fp,"</Row>");
		}

		/*
		* @Params : Void
		* @Return : Void
		*/
		function writeRow()
		{
			if($this->state!="OPENED")
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if($this->newRow==false)
				fwrite($this->fp," <Row>\n");
			else
				fwrite($this->fp,"</Row>\n <Row>");
			$this->newRow=true;	
		}

		/*
		* @Params : $value : Coloumn Value
		* @Return : Void
		*/
		function writeCell($value,$link=NULL,$color = NULL,$colspan=NULL)
		{
		    //$props = "class=xl24";
		    $props = "ss:StyleID=\"comun{$this->bold}\"";
		    $value = str_replace('&nbsp;',' ',$value);
		    $data = htmlentities($value);
		    		
			if(isset($color))
		    {
		        if($color=='red')
		            $props = "ss:StyleID=\"red{$this->bold}\"";
		        elseif($color=='green')
		            $props = "ss:StyleID=\"green{$this->bold}\"";
		        else
		            $data = "<font color=\"$color\">$value</font>";
			}			
			
			if( isset($link) && strpos($link,"http://")!==FALSE )
			   $props .= " ss:HRef=\"$link\"";
			if(isset($colspan))  
			    $props .= " ss:MergeAcross=\"".($colspan-1)."\"";

		    if(is_numeric($value))
			    $data .= "<Data ss:Type=\"Number\">$data</Data>";
			else
			    $data = "<Data ss:Type=\"String\">$data</Data>";

			if($this->state!="OPENED")
			{
				$this->error="Error : Please open the file.";
				return false;
			}
			//<Cell ><Data ss:Type="String">05:00</Data></Cell>
			fwrite($this->fp,"\t\t<Cell $props>$data</Cell>\n");
		}
		
		/*
		* @Params : $value : Coloumn Value
		* @Return : Void
		*/
		function writeBoldCell($value,$link=NULL,$color = NULL,$colspan=NULL)
		{
		    $this->bold = 'bold';
		    $this->writeCell($value,$link,$color,$colspan);
		    $this->bold = '';
		}
		
		function endSheet()
		{
		    if($this->newRow)
			{
				fwrite($this->fp," </Row>");
				$this->newRow=false;
			}
			if($this->newSheet)
			{
    		    fwrite($this->fp," </Table>\n</Worksheet>");
    		    $this->newSheet=false;
    		}
		}
		
		function writeSheet($name)
		{
		    if($this->state!="OPENED")
			{
				$this->error="Error : Please open the file.";
				return false;
			}
			
			if($this->newRow)
			{
				fwrite($this->fp," </Row>");
				$this->newRow = false;
			}
			
			if($this->newSheet==false)
				fwrite($this->fp,"<Worksheet ss:Name=\"$name\">");
			else
				fwrite($this->fp," </Table>\n</Worksheet><Worksheet ss:Name=\"$name\">");
			
			fwrite($this->fp,"   <Table x:FullColumns=\"1\" x:FullRows=\"1\" ss:DefaultColumnWidth=\"90\">");
			$this->newSheet=true;
			
			return true;		    
		}
	}
?>