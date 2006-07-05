<?php
/** @package tests */
/** @package tests */
class bug_489398
{
	/**
	* Checking the single quote var case
	*/
	var $test_01 = '$Id: bug-489398.php,v 1.1.1.1 2006-07-05 13:54:06 vidaguren Exp $';

	/**
	* checking the double quote var case
	*/
	var $test_02 = "Double quoted value";

	/**
	* Checking the no quote cause
	*/
	var $test_03 = false;

	/**
	* Checking the empty array case
	*/
	var $test_04 = array();

	/**
	* Checking the array with data case
	*/
	var $test_05 = array("test1","test2" => "value");
}
?>
