<?php
/*
	File: signup.common.php

	Example which demonstrates a xajax implementation of a sign-up page.
	
	Title: Sign-up Example
	
	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	Section: Files
	
	- <signup.php>
	- <signup.common.php>
	- <signup.server.php>
*/

/*
	@package xajax
	@version $Id: signup.common.php,v 1.1 2008-08-26 20:53:19 martinezdiaz Exp $
	@copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

require_once ("../../xajax_core/xajax.inc.php");

session_start();

$xajax = new xajax("signup.server.php");
$xajax->registerFunction("processForm");
?>