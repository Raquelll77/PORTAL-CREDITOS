<?php

require_once ('include/framework.php');


$mysqli = new mysqli(db_ip, db_user, db_pw, db_name);
if (mysqli_connect_errno()) {  echo mensaje("Error al Conectar a la Base de Datos [DB:101]","danger");exit;}
$mysqli->set_charset("utf8");



$mysqli->autocommit(FALSE);


			$conn = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave) );	if( $conn === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}
			$conn2 = sqlsrv_connect( db2_ip, array( "Database"=>db2_dbn, "UID"=>db2_usuario, "PWD"=>db2_clave) );	if( $conn2 === false ) { echo mensaje("Error al Conectar a la Base de Datos [DB:102]","danger");exit;}

	  
				$mysqli->query("TRUNCATE TABLE bodega");
				
	
					//BODEGAS
					$sql2="SELECT WhsCode codigo, WhsName nombre,U_CardCode FROM OWHS";				
					$stmt2 = sqlsrv_query( $conn2, $sql2 );	
					if( $stmt2 === false) {   die( print_r( sqlsrv_errors(), true) );}
	
			        if (sqlsrv_has_rows($stmt2)===true) 
			        {
			        	
			        	while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC) )
						{
				
							$mysqli->query("INSERT INTO bodega SET codigo='".trim($row2["codigo"])."',nombre='".trim($row2["nombre"])."',id_distribuidor='".trim($row2["U_CardCode"])."' ;");	
						}
					}
					//Fin bodegas
					
			
	        	
		
			








/* commit transaction */
if (!$mysqli->commit()) {
    print("ERROR al sincronizar los datos");
    exit();
} else {echo "Proceso terminado";}



$mysqli->close();




?>