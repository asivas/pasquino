<?php
/*
	File: multiply.server.php

	Example which demonstrates a multiplication using xajax.
	
	Title: Multiplication Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <multiply.php>
	- <multiply.common.php>
	- <multiply.server.php>
*/

/*
	@package xajax
	@version $Id: multiply.server.php,v 1.1 2008-08-26 20:53:19 martinezdiaz Exp $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

function multiply($x, $y)
{
	$objResponse = new xajaxResponse();
	$objResponse->assign("z", "value", $x*$y);
	return $objResponse;
}

require("multiply.common.php");
$xajax->processRequest();
?>