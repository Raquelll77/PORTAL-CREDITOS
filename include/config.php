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
define("app_title", "Sistema Movesa");  // Titulo Applicacion
define("app_empresa", "MOVESA");  // Titulo Empresa

// base de datos local
// define("db_user", "usr_movesa");  // Usuario de la Base de datos
// define("db_pw", "Hk80dlezi0f6");  // Clave
// define("db_ip", "p:localhost:8080");  // Ip o host donde se encuentra la base de datos
// define("db_name", "movesa_garantias"); //Nombre de base de datos

define('db_ip', '192.168.1.164');   // o '192.168.1.164:3306' (opcional)
define('db_user', 'portal_web');
define('db_pw', 'Hk80dlezi0f6');  // escribe la misma que usas en HeidiSQL
define('db_name', 'movesa_garantias');


// base de datos SAP
define("db2_usuario", "web");  // Usuario de la Base de datos
define("db2_clave", "P)m4-my9");  // Clave
define("db2_ip", "192.168.1.3,1433");  // Ip o host donde se encuentra la base de datos
define("db2_dbn", "movesa");  // Nombre de base de datos

//varios
define("app_dias_subir_doctos", 15); // maximo de dias donde permite subir documentos pendientes despues de activada una garantia

// Cuenta de correo 
define("app_email", "prueba@hn3.net");
define("app_email_name", "MOVESA");
define("app_email_host", "mail.hn3.net");
define("app_email_user", "prueba@hn3.net");
define("app_email_pass", "Oahy59K7caWT");
define("app_email_port", "587");

// Format de la fecha y hora
date_default_timezone_set('America/Tegucigalpa');
define("db_gmt_offset", -6); // offset de Hora local segun la Hora GMT 
define("app_formato_fecha", "dd/mm/yyyy");  // **No Modificar esto**
define("app_formato_fecha_php", "d/m/Y");  // **No Modificar esto**
define("app_formato_fecha_jquery", "dd/mm/yy");  // **No Modificar esto**


define("app_Seed", "h0i3Zf57Hlo1a");  // Semilla para operaciondes en criptografia
define("app_Seed_ancho", 8);  // Cantidad de caracteres de la segunda semilla aleatoria

//Control de errores
//error_reporting(0); //***** Activar EN PRODUCCION ******

?>