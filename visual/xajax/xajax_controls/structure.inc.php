<?php
/*
	File: structure.inc.php

	HTML Control Library - Structure Tags

	Title: xajax HTML control class library

	Please see <copyright.inc.php> for a detailed description, copyright
	and license information.
*/

/*
	@package xajax
	@version $Id: structure.inc.php,v 1.2 2009-06-12 22:09:49 vidaguren Exp $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2009 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

/*
	Section: Description
	
	This file contains the class declarations for the following HTML Controls:
	
	- div, span
*/

class clsDiv extends xajaxControlContainer
{
	function clsDiv($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('div', $aConfiguration);

		$this->sClass = '%block';
	}
}

class clsSpan extends xajaxControlContainer
{
	function clsSpan($aConfiguration=array())
	{
		xajaxControlContainer::xajaxControlContainer('span', $aConfiguration);

		$this->sClass = '%inline';
	}
}
