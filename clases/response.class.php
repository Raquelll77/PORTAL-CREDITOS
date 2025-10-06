<?php

class HTTPResponse
{
    public $Code = 200;
    public $Message;
    public $Success;
    public $Data;

    function __construct($Code = 200, $Message = "/HTTPResponse Instanciado correctamente", $Success = true, $Data = null)
    {
        $this->Code = $Code;
        $this->Message = self::convert_utf8($Message);
        $this->Success = $Success;
        $this->Data = $Data;
    }

    function Ok($data, $Message = "Operacion completada con exito")
    {
        $this->Code = 200;
        $this->Message = self::convert_utf8($Message);
        $this->Success = true;
        $this->Data = $data;
    }

    function Error($Code = 500, $Message = "Error al realizar la operacion", $data = null)
    {
        $this->Code = $Code;
        $this->Message = self::convert_utf8($Message);
        $this->Success = false;
        $this->Data = $data;
    }

    function NotFound($Code = 404, $Message = "La direccion requerida no existe - 404 not found")
    {
        $this->Code = $Code;
        $this->Message = self::convert_utf8($Message);
        $this->Success = false;
        $this->Data = null;
    }

    function MethodNotAllowed($Message = "Metodo No Autorizado - 405 Method Not Allowed")
    {
        $this->Code = 405;
        $this->Message = self::convert_utf8($Message);
        $this->Success = false;
        $this->Data = null;
    }

    function Forbidden($Message = "Accion no autorizada - 401 Forbidden")
    {
        $this->Code = 401;
        $this->Message = self::convert_utf8($Message);
        $this->Success = false;
        $this->Data = null;
    }

    public static function convert_utf8($str)
    {
        return utf8_encode($str);
        //return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
    }
}
