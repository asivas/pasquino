<?php 
function smarty_modifier_resaltar($str,$filtro)
{	
	$str = htmlentities($str);
	$filtro = htmlentities($filtro);
	$res = $str;
	//var_dump($filtro);
	if(isset($filtro) && trim($filtro)!="")
	{	
		$filtros = explode(" ",$filtro);						
		foreach($filtros as $f)
		{	
			$offset=0;
			$lengthf = strlen($f);
			$lengthStr = strlen($str);
			//var_dump($str);
			while(($pos = stripos($str,$f,$offset))!==FALSE && $offset<strlen($str))
			{	
				//var_dump($pos);
				$res = substr($str,0,$pos).chr(254).
				substr($str,$pos,$lengthf).chr(255).substr($str,$pos+$lengthf);
				$str = $res;
				$offset = $pos + $lengthf + 2 ;
			}
		}
		$res = str_replace(chr(254),"<span style='background-color:#FFFFBF;'>",$res);
		$res = str_replace(chr(255),"</span>",$res);
	}
	return $res;
}
