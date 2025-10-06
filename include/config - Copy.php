<?php
// ##############################################################################
// #                                                                            #
// # Modulo Configuracion                                                       #
// # 2015 Derechos reservados INFORMATICA Y SISTEMAS.                           #
// # Web: http://infosistemas.hn3.net  Email: infosistemas@hn3.net              #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################


// #########################################
// ######### Configuracion Basica ##########
// #########################################


// Aplicacion
define("app_title", "Sistema Movesa", true);  // Titulo Applicacion
define("app_empresa", "MOVESA", true);  // Titulo Empresa

// base de datos local
define("db_user", "usr_movesa", true);  // Usuario de la Base de datos
define("db_pw", "Hk80dlezi0f6", true);  // Clave
define("db_ip", "localhost", true);  // Ip o host donde se encuentra la base de datos
define("db_name", "movesa_garantias", true); //Nombre de base de datos

// base de datos SAP
 define("db2_usuario", "sa", true);  // Usuario de la Base de datos
 define("db2_clave", "Ibtisam1900", true);  // Clave
 define("db2_ip", "192.168.1.220,1433", true);  // Ip o host donde se encuentra la base de datos
 define("db2_dbn", "movesatest", true);  // Nombre de base de datos

//varios
define("app_dias_subir_doctos", 15, true); // maximo de dias donde permite subir documentos pendientes despues de activada una garantia

// Cuenta de correo 
define("app_email", "prueba@hn3.net", true);
define("app_email_name", "MOVESA", true);
define("app_email_host", "mail.hn3.net", true);
define("app_email_user", "prueba@hn3.net", true);
define("app_email_pass", "Oahy59K7caWT", true);
define("app_email_port", "587", true);

// Format de la fecha y hora
date_default_timezone_set('America/Tegucigalpa');
define("db_gmt_offset", -6 , true); // offset de Hora local segun la Hora GMT 
define("app_formato_fecha", "dd/mm/yyyy" , true);  // **No Modificar esto**
define("app_formato_fecha_php", "d/m/Y" , true);  // **No Modificar esto**
define("app_formato_fecha_jquery", "dd/mm/yy" , true);  // **No Modificar esto**


define("app_Seed", "h0i3Zf57Hlo1a", true);  // Semilla para operaciondes en criptografia
define("app_Seed_ancho", 8, true);  // Cantidad de caracteres de la segunda semilla aleatoria

//Control de errores
//error_reporting(0); //***** Activar EN PRODUCCION ******

?>