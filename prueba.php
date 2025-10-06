<?php
   
date_default_timezone_set('America/Tegucigalpa');
error_reporting(E_ALL ^ E_DEPRECATED);
require_once('lib/numletras.php'); 

$tt = 1243539.39;  


ECHO numletras($tt,'LEMPIRAS');

EXIT;

$aaa= new NumberToLetterConverter();
echo $aaa->to_word($tt,'LPS');


echo "<br><br>";



EXIT;



exit;



define("db2_usuario", "USUARIOB", true);  // Usuario de la Base de datos
define("db2_clave", "pruebas", true);  // Clave
define("db2_ip", "ACER-YCPKU23I8V\SQLEXPRESS", true);  // Ip o host donde se encuentra la base de datos
define("db2_dbn", "movesa", true);  // Ip o host donde se encuentra la base de datos  
 
 
 function SAP_verificar_estado_moto($serie_motor) {
    $salida=-1;
    //SAP
    $sql="SELECT TOP 1   [Status] FROM serie WHERE  SerieMotor ='$serie_motor'";
     $conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave, "CharacterSet" => "UTF-8") );  
    $stmt2 = sqlsrv_query( $conn2, $sql );  
    if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
    
            if (sqlsrv_has_rows($stmt2)===true) 
            {
             $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) ;
               
                    $salida = $row['Status'];

         
    
            }
    
    return $salida;
}
 
 
 echo SAP_verificar_estado_moto('162FMJ2E650170');
 

 
 exit;
   

$bodegas="SELECT T0.[WhsCode], T0.[WhsName],  T0.[U_CardCode] FROM OWHS T0 WHERE T0.[U_CardCode] ='CL100728' ";
		
		$clientes="SELECT T0.[CardCode], T0.[CardName],T0.CardName FROM OCRD T0 WHERE T0.[CardType] ='C' and T0.GroupCode = '100' ";
		
		
		

		$series="SELECT distinct T1.ItemCode ,T1.ItemName,T0.SuppSerial SerieChasis,t3.DistNumber SerieMotor,t3.LotNumber anio,
	T0.WhsCode Almacen,T5.Name Marca,T6.Name Modelo,t4.Name Color,T7.Name ModeloESpecifico,T8.U_CardCode CodigoCliente
FROM OSRI T0  
    INNER JOIN OITM T1 ON T0.ItemCode = T1.ItemCode 
    INNER JOIN OITB T2 ON T1.ItmsGrpCod =     T2.ItmsGrpCod
    inner join OSRN t3 on T0.ItemCode = t3.ItemCode
    inner join [@SCOLOR] t4 on T1.U_ACOLOR = t4.Code
    inner join [@AMARCA] T5 ON T1.U_AMARCA = T5.Code
    INNER JOIN [@AMODELO] T6 ON T1.U_AMODELO=T6.Code
    INNER JOIN [@OITMMODELOSMOTOS] T7 ON T1.U_ModelID=T7.Code
    Inner join OWHS T8 on T0.WhsCode = T8.WhsCode
WHERE T0.[Status]  = '0' and  T2.[ItmsGrpNam] ='Motocicleta' and T0.Whscode='TRO-29'";

$series="SELECT TOP 1000 [ItemCode]
      ,[ItemName]
      ,[SerieChasis]
      ,[SerieMotor]
      ,[anio]
      ,[Almacen]
      ,[Marca]
      ,[Modelo]
      ,[Color]
      ,[ModeloESpecifico]
      ,[CodigoCliente]
      ,[Whscode]
      ,[status]
  FROM series
  WHERE [Status]  = '0' and  Whscode='TRO-02'";


//$sql = "select USUARIO,NIVEL from SOLICITUD_PAGO_ACCIONES where ESTADO=1";

$sql=$clientes;



	$conn = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave, "CharacterSet" => "UTF-8") );	if( $conn === false ) {  die( print_r( "No se pudo conectar con la base de datos [sqlsrv]". var_dump(sqlsrv_errors()), true));}
	$stmt = sqlsrv_query( $conn, $sql );	if( $stmt === false) {   die( print_r( sqlsrv_errors(), true) );}

	//if (sqlsrv_has_rows($stmt)===true) {    }

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
   
	//     echo $row['WhsCode'].", ".$row['WhsName'].", ".$row['U_CardCode']."<br />";
	 echo $row['CardCode'].", ".$row['CardName']."<br />";
	//echo $row["ModeloESpecifico"] ." - ".$row["SerieChasis"] ." - ". $row["SerieMotor"] ." - ". $row["anio"] ." - ". $row["Marca"] ." - ". $row["Almacen"] ."<br>";	
	
}








?>
   
   
   
   
   
