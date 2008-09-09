<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty printpre modifier plugin
 *
 * Type:     modifier<br>
 * Name:     printpre<br>
 * Purpose:  Shows the var_dump of a var formated with pre html tag
 * Example:  {$var|printpre}
 * Date:     September 09th, 2008
 * @author   Lucas Vidaguren
 * @version  1.0
 * @param mixed
 * @return string
 */
function smarty_block_printpre($params,$content,&$smarty)
{
    ob_start();
    var_dump($params['var']);
    $dato = ob_get_contents();
    ob_end_clean();
    return "<pre>{$dato}</pre>";    
}

/* vim: set expandtab: */

?>
