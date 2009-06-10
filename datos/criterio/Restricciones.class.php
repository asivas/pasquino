<?php
require_once('datos/criterio/Restriccion.class.php');

class Restricciones {

    function Restricciones() {
    }
    
    public static function allEq($nombresValoresPropiedades)
    {
    	require_once('datos/criterio/Restricciones/AllEq.class.php');
        return new AllEq($nombresValoresPropiedades);
    }
    
    public static function between($nombreProp,$valor1,$valor2)
    {
        require_once('datos/criterio/Restricciones/Between.class.php');
        return new Between($nombreProp,$valor1,$valor2);
    }
    
    public static function eq($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Eq.class.php');
        return new Eq($nombreProp,$valor);
    }
    
    public static function eqProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/EqProperty.class.php');
        return new EqProperty($nombreProp1,$nombreProp2);
    }
    
    public static function ge($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Ge.class.php');
        return new Ge($nombreProp,$valor);
    }
    
    public static function geProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/GeProperty.class.php');
        return new GeProperty($nombreProp1,$nombreProp2);
    }
    
    public static function gt($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Gt.class.php');
        return new Gt($nombreProp,$valor);
    }
    
    public static function gtProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/GtProperty.class.php');
        return new GtProperty($nombreProp1,$nombreProp2);
    }
    
    public static function in($nombreProp,$valores)
    {
        require_once('datos/criterio/Restricciones/In.class.php');
        return new In($nombreProp,$valores);
    }
    
    public static function isEmpty($nombreProp)
    {
        require_once('datos/criterio/Restricciones/IsEmpty.class.php');
        return new IsEmpty($nombreProp);
    }
    
    public static function isNotEmpty($nombreProp)
    {
        require_once('datos/criterio/Restricciones/IsNotEmpty.class.php');
        return new IsNotEmpty($nombreProp);
    }
    
    public static function isNull($nombreProp)
    {
        require_once('datos/criterio/Restricciones/IsNull.class.php');
        return new IsNull($nombreProp);
    }
    
    public static function isNotNull($nombreProp)
    {
        require_once('datos/criterio/Restricciones/IsNotNull.class.php');
        return new IsNotNull($nombreProp);
    }    
    
    public static function le($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Le.class.php');
        return new Le($nombreProp,$valor);
    }
    
    public static function leProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/LeProperty.class.php');
        return new LeProperty($nombreProp1,$nombreProp2);
    }
    
    public static function lt($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Lt.class.php');
        return new Lt($nombreProp,$valor);
    }
    
    public static function ltProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/LtProperty.class.php');
        return new LtProperty($nombreProp1,$nombreProp2);
    }
    
    public static function ne($nombreProp,$valor)
    {
        require_once('datos/criterio/Restricciones/Ne.class.php');
        return new Ne($nombreProp,$valor);
    }
    
    public static function neProperty($nombreProp1,$nombreProp2)
    {
        require_once('datos/criterio/Restricciones/NeProperty.class.php');
        return new NeProperty($nombreProp1,$nombreProp2);
    }

    public static function not($restriccion)
    {
        require_once('datos/criterio/Restricciones/Not.class.php');
        return new Not($restriccion);
    }
    
    
}
