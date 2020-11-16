<?php
namespace pQn\datos\criterio;


use pQn\datos\criterio\Restricciones\AllEq;
use pQn\datos\criterio\Restricciones\Between;
use pQn\datos\criterio\Restricciones\Eq;
use pQn\datos\criterio\Restricciones\EqProperty;
use pQn\datos\criterio\Restricciones\Ge;
use pQn\datos\criterio\Restricciones\GeProperty;
use pQn\datos\criterio\Restricciones\GtProperty;
use pQn\datos\criterio\Restricciones\Gt;
use pQn\datos\criterio\Restricciones\In;
use pQn\datos\criterio\Restricciones\IsEmpty;
use pQn\datos\criterio\Restricciones\IsNotEmpty;
use pQn\datos\criterio\Restricciones\IsNull;
use pQn\datos\criterio\Restricciones\IsNotNull;
use pQn\datos\criterio\Restricciones\Le;
use pQn\datos\criterio\Restricciones\LeProperty;
use pQn\datos\criterio\Restricciones\Lt;
use pQn\datos\criterio\Restricciones\LtProperty;
use pQn\datos\criterio\Restricciones\Ne;
use pQn\datos\criterio\Restricciones\NeProperty;
use pQn\datos\criterio\Restricciones\Not;
use pQn\datos\criterio\Restricciones\Like;

class Restricciones {

    function Restricciones() {
    }
    
    public static function allEq($nombresValoresPropiedades)
    {
        return new AllEq($nombresValoresPropiedades);
    }
    
    public static function between($nombreProp,$valor1,$valor2)
    {
        return new Between($nombreProp,$valor1,$valor2);
    }
    
    public static function eq($nombreProp,$valor)
    {
        return new Eq($nombreProp,$valor);
    }
    
    public static function eqProperty($nombreProp1,$nombreProp2)
    {
        return new EqProperty($nombreProp1,$nombreProp2);
    }
    
    public static function ge($nombreProp,$valor)
    {
        return new Ge($nombreProp,$valor);
    }
    
    public static function geProperty($nombreProp1,$nombreProp2)
    {
        return new GeProperty($nombreProp1,$nombreProp2);
    }
    
    public static function gt($nombreProp,$valor)
    {
        return new Gt($nombreProp,$valor);
    }
    
    public static function gtProperty($nombreProp1,$nombreProp2)
    {
        return new GtProperty($nombreProp1,$nombreProp2);
    }
    
    public static function in($nombreProp,$valores)
    {
        return new In($nombreProp,$valores);
    }
    
    public static function isEmpty($nombreProp)
    {
        return new IsEmpty($nombreProp);
    }
    
    public static function isNotEmpty($nombreProp)
    {
        return new IsNotEmpty($nombreProp);
    }
    
    public static function isNull($nombreProp)
    {
        return new IsNull($nombreProp);
    }
    
    public static function isNotNull($nombreProp)
    {
        return new IsNotNull($nombreProp);
    }    
    
    public static function le($nombreProp,$valor)
    {
        return new Le($nombreProp,$valor);
    }
    
    public static function leProperty($nombreProp1,$nombreProp2)
    {
        return new LeProperty($nombreProp1,$nombreProp2);
    }
    
    public static function lt($nombreProp,$valor)
    {
        return new Lt($nombreProp,$valor);
    }
    
    public static function ltProperty($nombreProp1,$nombreProp2)
    {
        return new LtProperty($nombreProp1,$nombreProp2);
    }
    
    public static function ne($nombreProp,$valor)
    {
        return new Ne($nombreProp,$valor);
    }
    
    public static function neProperty($nombreProp1,$nombreProp2)
    {
        return new NeProperty($nombreProp1,$nombreProp2);
    }

    public static function not($restriccion)
    {
        return new Not($restriccion);
    }
    
    public static function like($nombreProp, $valor)
    {
        return new Like($nombreProp,$valor);
    }
    
    
}
