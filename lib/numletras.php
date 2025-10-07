<?PHP

function Centenas($VCentena)
{
    $Numeros[0] = "cero";
    $Numeros[1] = "uno";
    $Numeros[2] = "dos";
    $Numeros[3] = "tres";
    $Numeros[4] = "cuatro";
    $Numeros[5] = "cinco";
    $Numeros[6] = "seis";
    $Numeros[7] = "siete";
    $Numeros[8] = "ocho";
    $Numeros[9] = "nueve";
    $Numeros[10] = "diez";
    $Numeros[11] = "once";
    $Numeros[12] = "doce";
    $Numeros[13] = "trece";
    $Numeros[14] = "catorce";
    $Numeros[15] = "quince";
    $Numeros[20] = "veinte";
    $Numeros[30] = "treinta";
    $Numeros[40] = "cuarenta";
    $Numeros[50] = "cincuenta";
    $Numeros[60] = "sesenta";
    $Numeros[70] = "setenta";
    $Numeros[80] = "ochenta";
    $Numeros[90] = "noventa";
    $Numeros[100] = "ciento";
    $Numeros[101] = "quinientos";
    $Numeros[102] = "setecientos";
    $Numeros[103] = "novecientos";
    if ($VCentena == 1) {
        return $Numeros[100];
    } else if ($VCentena == 5) {
        return $Numeros[101];
    } else if ($VCentena == 7) {
        return ($Numeros[102]);
    } else if ($VCentena == 9) {
        return ($Numeros[103]);
    } else {
        return $Numeros[$VCentena];
    }
}



function Unidades($VUnidad)
{
    $Numeros[0] = "cero";
    $Numeros[1] = "un";
    $Numeros[2] = "dos";
    $Numeros[3] = "tres";
    $Numeros[4] = "cuatro";
    $Numeros[5] = "cinco";
    $Numeros[6] = "seis";
    $Numeros[7] = "siete";
    $Numeros[8] = "ocho";
    $Numeros[9] = "nueve";
    $Numeros[10] = "diez";
    $Numeros[11] = "once";
    $Numeros[12] = "doce";
    $Numeros[13] = "trece";
    $Numeros[14] = "catorce";
    $Numeros[15] = "quince";
    $Numeros[20] = "veinte";
    $Numeros[30] = "treinta";
    $Numeros[40] = "cuarenta";
    $Numeros[50] = "cincuenta";
    $Numeros[60] = "sesenta";
    $Numeros[70] = "setenta";
    $Numeros[80] = "ochenta";
    $Numeros[90] = "noventa";
    $Numeros[100] = "ciento";
    $Numeros[101] = "quinientos";
    $Numeros[102] = "setecientos";
    $Numeros[103] = "novecientos";

    $tempo = $Numeros[$VUnidad];
    return $tempo;
}

function Decenas($VDecena)
{
    $Numeros[0] = "cero";
    $Numeros[1] = "uno";
    $Numeros[2] = "dos";
    $Numeros[3] = "tres";
    $Numeros[4] = "cuatro";
    $Numeros[5] = "cinco";
    $Numeros[6] = "seis";
    $Numeros[7] = "siete";
    $Numeros[8] = "ocho";
    $Numeros[9] = "nueve";
    $Numeros[10] = "diez";
    $Numeros[11] = "once";
    $Numeros[12] = "doce";
    $Numeros[13] = "trece";
    $Numeros[14] = "catorce";
    $Numeros[15] = "quince";
    $Numeros[20] = "veinte";
    $Numeros[30] = "treinta";
    $Numeros[40] = "cuarenta";
    $Numeros[50] = "cincuenta";
    $Numeros[60] = "sesenta";
    $Numeros[70] = "setenta";
    $Numeros[80] = "ochenta";
    $Numeros[90] = "noventa";
    $Numeros[100] = "ciento";
    $Numeros[101] = "quinientos";
    $Numeros[102] = "setecientos";
    $Numeros[103] = "novecientos";
    $tempo =    ($Numeros[$VDecena]);
    return $tempo;
}





function NumerosALetras($Numero)
{


    $Decimales = 0;
    //$Numero = intval($Numero);
    $letras = "";

    while ($Numero != 0) {

        // \'*---> Validaci�n si se pasa de 100 millones

        if ($Numero >= 1000000000) {
            $letras = "Error en Conversion a Letras";
            $Numero = 0;
            $Decimales = 0;
        }

        // \'*---> Centenas de Mill�n
        if (($Numero < 1000000000) and ($Numero >= 100000000)) {
            if ((Intval($Numero / 100000000) == 1) and (($Numero - (Intval($Numero / 100000000) * 100000000)) < 1000000)) {
                $letras .= (string) "cien millones ";
            } else {
                $letras = $letras & Centenas(Intval($Numero / 100000000));
                if ((Intval($Numero / 100000000) <> 1) and (Intval($Numero / 100000000) <> 5) and (Intval($Numero / 100000000) <> 7) and (Intval($Numero / 100000000) <> 9)) {
                    $letras .= (string) "cientos ";
                } else {
                    $letras .= (string) " ";
                }
            }
            $Numero = $Numero - (Intval($Numero / 100000000) * 100000000);
        }

        // \'*---> Decenas de Mill�n
        if (($Numero < 100000000) and ($Numero >= 10000000)) {
            if (Intval($Numero / 1000000) < 16) {
                $tempo = Decenas(Intval($Numero / 1000000));
                $letras .= (string) $tempo;
                $letras .= (string) " millones ";
                $Numero = $Numero - (Intval($Numero / 1000000) * 1000000);
            } else {
                $letras = $letras & Decenas(Intval($Numero / 10000000) * 10);
                $Numero = $Numero - (Intval($Numero / 10000000) * 10000000);
                if ($Numero > 1000000) {
                    $letras .= $letras & " y ";
                }
            }
        }

        // \'*---> Unidades de Mill�n
        if (($Numero < 10000000) and ($Numero >= 1000000)) {
            $tempo = (Intval($Numero / 1000000));
            if ($tempo == 1) {
                $letras .= (string) " un millon ";
            } else {
                $tempo = Unidades(Intval($Numero / 1000000));
                $letras .= (string) $tempo;
                $letras .= (string) " millones ";
            }
            $Numero = $Numero - (Intval($Numero / 1000000) * 1000000);
        }

        // \'*---> Centenas de Millar
        if (($Numero < 1000000) and ($Numero >= 100000)) {
            $tempo = (Intval($Numero / 100000));
            $tempo2 = ($Numero - ($tempo * 100000));
            if (($tempo == 1) and ($tempo2 < 1000)) {
                $letras .= (string) "cien mil ";
            } else {
                $tempo = Centenas(Intval($Numero / 100000));
                $letras .= (string) $tempo;
                $tempo = (Intval($Numero / 100000));
                if (($tempo <> 1) and ($tempo <> 5) and ($tempo <> 7) and ($tempo <> 9)) {
                    $letras .= (string) "cientos ";
                } else {
                    $letras .= (string) " ";
                }
            }
            $Numero = $Numero - (Intval($Numero / 100000) * 100000);
        }

        // \'*---> Decenas de Millar
        if (($Numero < 100000) and ($Numero >= 10000)) {
            $tempo = (Intval($Numero / 1000));
            if ($tempo < 16) {
                $tempo = Decenas(Intval($Numero / 1000));
                $letras .= (string) $tempo;
                $letras .= (string) " mil ";
                $Numero = $Numero - (Intval($Numero / 1000) * 1000);
            } else {
                $tempo = Decenas(Intval($Numero / 10000) * 10);
                $letras .= (string) $tempo;
                $Numero = $Numero - (Intval(($Numero / 10000)) * 10000);
                if ($Numero > 1000) {
                    $letras .= (string) " y ";
                } else {
                    $letras .= (string) " mil ";
                }
            }
        }


        // \'*---> Unidades de Millar
        if (($Numero < 10000) and ($Numero >= 1000)) {
            $tempo = (Intval($Numero / 1000));
            if ($tempo == 1) {
                $letras .= (string) "un";
            } else {
                $tempo = Unidades(Intval($Numero / 1000));
                $letras .= (string) $tempo;
            }
            $letras .= (string) " mil ";
            $Numero = $Numero - (Intval($Numero / 1000) * 1000);
        }

        // \'*---> Centenas
        if (($Numero < 1000) and ($Numero > 99)) {
            if ((Intval($Numero / 100) == 1) and (($Numero - (Intval($Numero / 100) * 100)) < 1)) {
                $letras = $letras & "cien ";
            } else {
                $temp = (Intval($Numero / 100));
                $l2 = Centenas($temp);
                $letras .= (string) $l2;
                if ((Intval($Numero / 100) <> 1) and (Intval($Numero / 100) <> 5) and (Intval($Numero / 100) <> 7) and (Intval($Numero / 100) <> 9)) {
                    $letras .= "cientos ";
                } else {
                    $letras .= (string) " ";
                }
            }

            $Numero = $Numero - (Intval($Numero / 100) * 100);
        }

        // \'*---> Decenas
        if (($Numero < 100) and ($Numero > 9)) {
            if ($Numero < 16) {
                $tempo = Decenas(Intval($Numero));
                $letras .= $tempo;
                $Numero = $Numero - Intval($Numero);
            } else {
                $tempo = Decenas(Intval(($Numero / 10)) * 10);
                $letras .= (string) $tempo;
                $Numero = $Numero - (Intval(($Numero / 10)) * 10);
                if ($Numero > 0.99) {
                    $letras .= (string) " y ";
                }
            }
        }

        // \'*---> Unidades
        if (($Numero < 10) and ($Numero > 0.99)) {
            $tempo = Unidades(Intval($Numero));
            $letras .= (string) $tempo;

            $Numero = $Numero - Intval($Numero);
        }


        // \'*---> Decimales
        if ($Decimales > 0) {

            // $letras .=(string) " con ";
            //  $Decimales= $Decimales*100;
            //  echo ("*");   
            //  $Decimales = number_format($Decimales, 2);  
            //  echo ($Decimales);  
            //  $tempo = Decenas(Intval($Decimales));   
            //  $letras .= (string) $tempo;
            //  $letras .= (string) "centavos";
        } else {
            if (($letras <> "Error en ConversiOn a Letras") and (strlen(Trim($letras)) > 0)) {
                $letras .= (string) " ";
            }
        }
        return strtoupper($letras);
    }
}



function numletras($numero, $moneda)
{
    $salida = "";

    if ($numero <> "") {
        $tt = $numero;
        $tt = $tt + 0.009;
        $Numero = intval($tt);
        $Decimales = $tt - Intval($tt);
        $Decimales = $Decimales * 100;
        $Decimales = Intval($Decimales);
        $x = NumerosALetras($Numero);
        $salida .= ($x);
        if ($Decimales > 0) {

            $y = NumerosALetras($Decimales);
            $salida .=  (" $moneda CON ");
            $salida .=  ($y);
            $salida .=  (" CENTAVOS");
        } else {
            $salida .=  (" $moneda "); //CERO CENTAVOS
        }
    }
    return $salida;
}

function numLetrasProcentaje($numero)
{
    $salida = "";

    if ($numero <> "") {
        $tt = $numero;
        $tt = $tt + 0.009;
        $Numero = intval($tt);
        $Decimales = $tt - Intval($tt);
        $Decimales = $Decimales * 100;
        $Decimales = Intval($Decimales);
        $x = NumerosALetras($Numero);
        $salida .= ($x);
        if ($Decimales > 0) {

            $y = NumerosALetras($Decimales);
            $salida .=  (" PUNTO ");
            $salida .=  ($y);
            $salida .=  (" PORCIENTO");
        } else {
            $salida .=  ("PORCIENTO"); //CERO CENTAVOS
        }
    }
    return $salida;
}
