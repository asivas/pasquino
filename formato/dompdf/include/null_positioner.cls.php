<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: null_positioner.cls.php,v 1.2 2013-05-15 20:28:51 martinezdiaz Exp $
 */

/**
 * Dummy positioner
 *
 * @access private
 * @package dompdf
 */
class Null_Positioner extends Positioner {

  function __construct(Frame_Decorator $frame) {
    parent::__construct($frame);
  }

  function position() { return; }
  
}
