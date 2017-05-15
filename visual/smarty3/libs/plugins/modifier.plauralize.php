<?php 
/** 
 * Smarty plugin 
 * @package Smarty 
 * @subpackage plugins 
 */ 


/** 
 * Smarty plugin 
 * 
 * Type:     modifier<br> 
 * Name:    plauralizer<br> 
 * Date:     Nov 23 2010 
 * Purpose:  Add an "s" at the end of a word if the number isn't 1 
 * Example:  {$number|plauralize} 
 * @version  1.0 
 * @author   Sean Boyer <sean@boyercentral.net> 
 * @param int 
 * @return string 
 */ 
function smarty_modifier_plauralize($int) 
{ 
    if(intval($int) != 1) 
        return "s"; 
    return ""; 
} 

/* vim: set expandtab: */ 

?> 