<?php
// ##############################################################################
// #                                                                          #
// # Modulo Framework                                                           #
// # 2015 Derechos reservados INFORMATICA Y SISTEMAS.                           #
// # Web: http://infosistemas.hn3.net  Email: infosistemas@hn3.net              #
// # Se prohibe la copia o distribucion del codigo sin autorizacion por escrito #
// ##############################################################################


require_once('config.php');
require_once('sesion.php');


define("app_version", "1.0");  // Version de Applicacion

define("app_combo_si_no", '<option value="N">NO</option><option value="S">SI</option>');



//funcion para incluir campos unicamente si contienen informacion
function sqladd($sql, $valor, $tipo)
{
    $salida = "";
    if ($valor == "" or is_null($valor)) {
        $salida = "";
    } else {

        $salida = $sql . GetSQLValue($valor, $tipo);
    }
    return $salida;
}

function GetSQLValue($theValue, $theType)
{
    //$theValue=mysql_real_escape_string($theValue);  
    // $textvalue=$theValue;
    // $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

    switch ($theType) {
        case "text":
            $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
            break;
            // case "textarea":
            // $theValue = ($textvalue != "") ? "'" . $textvalue . "'" : "NULL";
            // break; 
        case "like":
            $theValue = ($theValue != "") ? "'%" . $theValue . "%'" : "NULL";
            break;

        case "long":
        case "int":
            $theValue = ($theValue != "") ? intval($theValue) : "NULL";
            break;
        case "int_cero":
            $theValue = ($theValue != "") ? intval($theValue) : "0";
            break;
        case "double":
            $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "0";
            break;
        case "date":
            $theValue = ($theValue != "") ? "'" . mysqldate($theValue) . "'" : "NULL";
            break;
    }
    return $theValue;
}

function agregar_nonulo($textoantes, $texto, $textodespues)
{
    $salida = "";
    if ($texto == "" or is_null($texto)) {
        $salida = "";
    } else {

        $salida = $textoantes . $texto . $textodespues;
    }
    return $salida;
}

function mysqldate($fecha)
{
    //return date('Y-m-d', strtotime(str_replace('-', '/', $fecha)));

    $sub = explode("/", $fecha);
    return trim($sub[2]) . "-" . trim($sub[1]) . "-" . trim($sub[0]);
}

function fechademysql($fecha)
{ //  dd/mm/yyyy
    //return date('Y-m-d', strtotime(str_replace('-', '/', $fecha)));

    $salida = "";
    if (strlen($fecha) == 10) {
        $sub = explode("-", $fecha);
        $salida = $sub[2] . "/" . $sub[1] . "/" . $sub[0];
    }
    return $salida;
}

function fechademysql_mini($fecha)
{ //  dd/mm/yy
    //return date('Y-m-d', strtotime(str_replace('-', '/', $fecha)));

    $salida = "";
    if (strlen($fecha) == 10) {
        $sub = explode("-", $fecha);
        $salida = $sub[2] . "/" . $sub[1] . "/" . substr($sub[0], -2);
    }
    return $salida;
}


function horademysql($fechahora, $solo_fecha = false)
{ //  hh:mm am/pm dd/mm/yyyy

    $formato = "h:i a";
    if ($solo_fecha == true) {
        $formato = "d/m/y";
    }
    $phpdate = strtotime($fechahora);
    $salida = date($formato, $phpdate);
    return $salida;
}

function fechahora_mysql($fechahora)
{ //  hh:mm am/pm dd/mm/yyyy

    $formato = "d/m/y h:i a";
    $phpdate = strtotime($fechahora);
    $salida = date($formato, $phpdate);
    return $salida;
}

function leercheckbox($valor)
{

    $salida = "";

    if ($valor == 'S') {
        $salida = " checked";
    } else {
        $salida = "";
    }

    return $salida;
}


// validate user input
// $input: variable to be validated
// $type: nofilter,date, text, int, alnum,alpha, email, url, ip
// $len: maximum length
// $chars: array of any non alpha-numeric characters to allow
// $requerido   = true false 
function validar($campo, $input, $type, $requerido, $len = null, $lenmin = null, $chars = null)
{
    $tmp = str_replace(' ', '', $input);
    if (!empty($tmp)) {
        if (isset($len)) {
            if (strlen($input) > $len) {
                return "El campo $campo tiene un limite de $len caracteres" . "<br>";
            }
        }
        if (isset($lenmin)) {
            if (strlen($input) < $lenmin) {
                return "El campo $campo debe ingresar un minimo de $lenmin caracteres" . "<br>";
            }
        }
        if (isset($chars)) {
            $input = str_replace($chars, '', $input);
        }
        $input = str_replace(' ', '', $input);

        switch ($type) {
            case 'alpha':
                if (!ctype_alpha($input)) {
                    return "El campo $campo debe ser alfabetico sin numeros" . "<br>";
                }
                break;

            case 'date':
                if (!checkfecha($input)) {
                    return "El campo $campo no es una fecha valida, el formato correcto es: " . app_formato_fecha . "<br>";
                }
                break;

            case 'int':
                if (!ctype_digit($input)) {
                    return "El campo $campo debe ser numerico" . "<br>";
                }
                break;

            case 'double':
                if (!is_numeric($input)) {
                    return "El campo $campo debe ser numerico" . "<br>";
                }
                break;

            case 'alnum':
                if (!ctype_alnum($input)) {
                    return "El campo $campo debe contener unicamene letras y numeros" . "<br>";
                }
                break;

            case 'email':
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    return "El campo $campo debe ser un email valido" . "<br>";
                }
                break;

            case 'url':
                if (!filter_var($input, FILTER_VALIDATE_URL)) {
                    return "El campo $campo debe ser una direccion web valida" . "<br>";
                }
                break;

            case 'ip':
                if (!filter_var($input, FILTER_VALIDATE_IP)) {
                    return "El campo $campo debe ser un numero de IP valida" . "<br>";
                }
                break;

            case 'nofilter':
                return "";
                break;
            case 'text':
                return "";
                break;
        }
        return "";
    } else {
        if ($requerido == true) {
            return "El campo $campo es obligatorio" . "<br>";
        } else {
            return "";
        }
    }
}

function checkfecha($mydate)
{
    if (strlen($mydate) == 10) {
        $sub = explode("/", $mydate);

        if (isset($sub[0], $sub[1], $sub[2])) {

            if (is_numeric($sub[2]) && is_numeric($sub[1]) && is_numeric($sub[0])) {
                return  checkdate($sub[1], $sub[0], $sub[2]);
            }
        }
    }

    return false;
}

function checkfecha_mysql($mydate)
{
    if (strlen($mydate) == 10) {
        $sub = explode("-", $mydate);

        if (isset($sub[0], $sub[1], $sub[2])) {

            if (is_numeric($sub[2]) && is_numeric($sub[1]) && is_numeric($sub[0])) {
                return  checkdate($sub[1], $sub[2], $sub[0]);
            }
        }
    }

    return false;
}

function enviar_correo($email_a, $subject, $cuerpo_html, $cuerpo_sinhtml)
{
    $salida = false;

    require_once('lib/phpcorreo/class.phpmailer.php');

    $mail = new PHPMailer;
    //$mail->SMTPDebug = 2;
    $mail->IsSMTP();

    $mail->Host = app_email_host;
    $mail->SMTPAuth = true;
    $mail->Username = app_email_user;
    $mail->Password = app_email_pass;
    $mail->Port = app_email_port;
    //$mail->SMTPSecure = 'tls';                           

    $mail->From = app_email;
    $mail->FromName = app_email_name;

    $mail->AddAddress($email_a, '');

    $mail->AddReplyTo(app_email, app_email_name);
    //$mail->AddCC('');         

    $mail->IsHTML(true);

    $mail->Subject = $subject;
    $mail->Body    = $cuerpo_html;
    $mail->AltBody = $cuerpo_sinhtml;

    $salida = true;
    if (!$mail->Send()) {
        $salida = false;      // echo 'Mailer Error: ' . $mail->ErrorInfo;
    }



    return $salida;
}

function campo_radio($nombre, $etiqueta, $valor, $adicional)
{
    $salida = "";

    $salida .= '<div class="form-group" ><div class="controls col-sm-offset-3 col-sm-9">';
    $salida .= '<input id="' . $nombre . $valor . '" name="' . $nombre . '" value="' . $valor . '"  type="radio" ' . $adicional . ' />';
    $salida .= ' <label  class="control-label " for="' . $nombre . $valor . '"> ' . $etiqueta . '</label></div></div>';

    return $salida;
}


function campotabla($nombre, $etiqueta, $tipo, $valor, $adicional, $valor2 = "", $corto = "")
{
    $salida = "<tr>";
    $salida .= "<td>$etiqueta</td>";
    $salida .= "<td class='text-right'>$valor</td>";
    $salida .= "</tr>";

    return $salida;
}

function campotabla_columna($nombre, $etiqueta, $tipo, $valor, $adicional, $valor2 = "", $corto = "")
{
    $salida = "";
    $salida .= "<td >$valor</td>";


    return $salida;
}


function campo($nombre, $etiqueta, $tipo, $valor, $adicional, $valor2 = "", $corto = "", $columna1 = 3, $columna2 = 9, $display = '')
{
    $salida = "";

    if ($etiqueta != "") {
        // $salida .= '<div class="control-group"><label class="control-label'.$corto.'" for="'.$nombre.'">'.$etiqueta.'</label><div class="controls'.$corto.'">' ;
        $salida .= '<div class="form-group ' . $nombre . '" style="' . $display . '"><label for="' . $nombre . '" class="control-label col-sm-' . $columna1 . '">' . $etiqueta . '</label><div class="col-sm-' . $columna2 . '">';
    }
    switch ($tipo) {

        case "boton":
            $salida = '<div class="form-group"><div class="col-sm-offset-' . $columna1 . ' col-sm-' . $columna2 . '"><button type="submit" id="' . $nombre . '" name="' . $nombre . '" class="btn btn-primary" ' . $adicional . '>' . $etiqueta . '</button>';
            break;
        case "botonlink":
            $salida = '<div class="form-group"><div class="col-sm-offset-' . $columna1 . ' col-sm-' . $columna2 . '"><a href="#"  class="btn btn-primary" ' . $adicional . '>' . $etiqueta . '</a>';
            break;
        case "label":
            $salida .= $valor;
            break;
        case "number":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="' . $tipo . '" ' . $adicional . ' />';
            break;
        case "text":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="' . $tipo . '" ' . $adicional . ' />';
            break;
        case "password":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="' . $tipo . '" ' . $adicional . ' />';
            break;

        case "hidden":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="' . $tipo . '" ' . $adicional . ' />';
            break;

        case "checkbox":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="' . $tipo . '" ' . $adicional . ' />';
            break;



        case "textarea":
            $salida .= '<textarea id="' . $nombre . '" name="' . $nombre . '" spellcheck="false" ' . $adicional . '>' . $valor . '</textarea>';
            break;

        case "select":
            $salida .= '<select id="' . $nombre . '" name="' . $nombre . '" ' . $adicional . '>' . $valor . '</select>';
            break;
        case "select2":
            $salida .= '<select id="' . $nombre . '" name="' . $nombre . '" ' . $adicional . '>' . $valor . '</select>';
            $salida .= '<script>$(document).ready(function() { $("#' . $nombre . '").select2( {placeholder: "Selecione",allowClear: true}); });</script>';
            break;

        case "select2multi":
            $salida .= '<select multiple id="' . $nombre . '" name="' . $nombre . '[]" ' . $adicional . '>' . $valor . '</select>';
            $salida .= '<script>$(document).ready(function() { $("#' . $nombre . '").select2( {placeholder: "Selecione",allowClear: true}); });</script>';
            break;


        case "select2ajax":
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '"  type="hidden" ' . $adicional . ' />';
            $salida .= "
      <script>
      $('#" . $nombre . "').select2({
          placeholder: 'Selecione...',
          allowClear: true,
          minimumInputLength: 3,
        ajax: {
            url: '" . $valor2 . "',
            dataType: 'json',
            quietMillis: 300,
            data: function (term, page) {
                return {
                    term: term 
                };
            },
            results: function (data, page) {
                return { results: data.results };
            }

        },
        initSelection: function(element, callback) {
            return $.getJSON('" . $valor2 . "&id=' + (element.val()), null, function(data) {
                    return callback(data);
            });
        }
    });
</script>   ";

            break;

        case "date":
            // $salida.= '<script>$(function() {$( "#'.$nombre.'" ).datepicker({showOn: "both",buttonImage: "images/calendar.gif",buttonImageOnly: true,changeMonth: true,changeYear: true,dateFormat: "'.app_formato_fecha_jquery.'"}); });</script>';
            // $salida .= '<input id="'.$nombre.'" name="'.$nombre.'" value="'.$valor.'"  type="text" '.$adicional.' />';
            $salida .= '<input id="' . $nombre . '" name="' . $nombre . '" value="' . $valor . '" data-date-format="dd/mm/yyyy" type="text" ' . $adicional . ' />';

            $salida .=  "<script>   $('#$nombre').datepicker(); </script> ";




            break;

        case "uploadlink":
            if ($valor == "") {
                $salida .= "Sin Asignar";
            } else {
                $salida .= '<a id="' . $nombre . '" href="#" onclick="abrir_ajunto(\'' . ($valor) . '\'); return false;" ><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Abrir documento: ' . $etiqueta . '</a>';
            }
            break;

        case "upload":

            $salida = '      
      
    <div class="form-group"><label  class="control-label col-sm-' . $columna1 . '">' . $etiqueta . '</label>
    
    <div class="col-sm-offset-' . $columna1 . ' col-sm-' . $columna2 . '">
     
         <div class="row"> 
          
          <div id="colbtn_' . $nombre . '" class="col-sm-3">
          <span class="btn btn-default fileinput-button">
          <i class="glyphicon glyphicon-cloud-upload"></i>
          <span>Subir archivo</span>
          <input id="fileupload_' . $nombre . '" type="file" name="files[]" >
        </span>
        </div>
        
      <input id="' . $nombre . '" name="' . $nombre . '" value=""  type="hidden"  />
      
        <div class=" col-sm-7">
          <div id="progress_' . $nombre . '" class="progress">
              <div class="progress-bar progress-bar-success"></div>
          </div>
         
          <div id="files_' . $nombre . '" ></div>
        </div>
        
        
  
      </div>
        ';
            //   'use strict';
            $salida .= "<script>        
          $(function () {
           
              
              
              $('#fileupload_$nombre').fileupload({
                  url: 'lib/fileupload/',
                  dataType: 'json',
              singleFileUploads: true,
              acceptFileTypes: /(\.|\/)(gif|jpe?g|png|doc|docx|pdf|txt|xls|xlsx)$/i,
                  maxFileSize: 5242880,
              maxNumberOfFiles: 1,
              disableVideoPreview: true,
              disableAudioPreview: true,
              disableImagePreview: true,
              previewThumbnail: false,
              
                  done: function (e, data) {
                  
                      $.each(data.result.files, function (index, file) {
                          $('#files_$nombre').text(file.name);
                  $('#$nombre').val(file.name);
                                    ";

            if ($valor2 <> "") { // para modificar documentos ya subidos
                $salida .= "                           
                            var respuesta2=  $.ajax({
                                type: \"GET\",
                                url: 'get.php?a=17&cid='+file.name+'&eid=$valor2&dd=$nombre',
                                async: false,
                            }).responseText;
                           
            
                            if (respuesta2.trim()=='OK') {
                                $('#files_$nombre').html('<a href=\"#\" onclick=\"abrir_ajunto(\''+file.name+'\'); return false;\" ><span class=\"glyphicon glyphicon-download-alt\" aria-hidden=\"true\"></span> Abrir documento: $etiqueta </a> ');
                             
                                 $('#colbtn_$nombre').hide();
                                $('#progress_$nombre').hide();
                                 
                                 } 
                            
                            ";
            }


            $salida .= "
                      });
                  },
                  progressall: function (e, data) {
                      var progress = parseInt(data.loaded / data.total * 100, 10);
                      $('#progress_$nombre .progress-bar').css(
                          'width',
                          progress + '%'
                      );
                  }
              }).prop('disabled', !$.support.fileInput)
                  .parent().addClass($.support.fileInput ? undefined : 'disabled');
              
          });
          
          
          </script>";
            break;
    }


    if ($etiqueta != "") {
        $salida .= '</div></div>';
    }
    return $salida;
}





function campo_upload($nombre, $etiqueta, $tipo, $valor, $adicional, $id_solicitud = "", $columna1 = 3, $columna2 = 9, $mostrar_upload = "NO")
{
    $salida = ""; {  //upload

        $salida .=
            "<div class='form-group'><label class='control-label col-sm-{$columna1}'>{$etiqueta}</label>
            <div class='col-sm-offset-{$columna1} col-sm-{$columna2}'>";

        if (!empty($valor)) {
            $salida .= '<a id="' . $nombre . '" href="#" onclick="abrir_ajunto(\'' . ($valor) . '\'); return false;" ><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Abrir documento: ' . $etiqueta . '</a> ';
        } else {
            $salida .= 'Sin Asignar';
        }

        if (empty($valor) or $mostrar_upload == "SI") {

            if ($mostrar_upload == "SI") {
                $n = str_replace("doc", "", $nombre);
                $salida .=
                    "&nbsp;&nbsp;&nbsp;    
                    <a href='#' class='btn btn-default' onclick='return false;' data-toggle='collapse' data-target='#UPL{$n}'>
                        <span class='glyphicon glyphicon-cloud-upload' aria-hidden='true'></span>
                    </a>
                    <div id='UPL{$n}' class='collapse'>
                    <br>";
            }

            $salida .=
                "<div class='row'> 
                    <div id='colbtn_{$nombre}' class='col-sm-3'>
                        <span class='btn btn-default fileinput-button'>
                            <i class='glyphicon glyphicon-cloud-upload'></i>
                            <span>Subir archivo</span>
                            <input id='fileupload_{$nombre}' type='file' name='files[]'>
                        </span>
                    </div>

                    <input id='{$nombre}' name='{$nombre}' value=''  type='hidden'  />

                    <div class='col-sm-7'>
                        <div id='progress_{$nombre}' class='progress'>
                            <div class='progress-bar progress-bar-success'></div>
                        </div>
                        <div id='files_{$nombre}' ></div>
                    </div>
                </div>";

            if ($mostrar_upload = "SI") {
                $salida .= '</div>';
            }
        }

        //   'use strict';
        $salida .=
            "<script>        
                $(function () {

                    $('#fileupload_{$nombre}').fileupload({
                        url: 'lib/fileupload/',
                        dataType: 'json',
                        singleFileUploads: true,
                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|doc|docx|pdf|txt|xls|xlsx)$/i,
                        maxFileSize: 5242880,
                        maxNumberOfFiles: 1,
                        disableVideoPreview: true,
                        disableAudioPreview: true,
                        disableImagePreview: true,
                        previewThumbnail: false,
                        timeout: 10000000,    
                        done: function (e, data) {    
                            $.each(data.result.files, function (index, file) {
                                $('#files_{$nombre}').text(file.name);
                                $('#{$nombre}').val(file.name);
            ";

        if (!empty($id_solicitud)) { // para guardar documento de un solo
            $salida .=
                "var respuesta2=  $.ajax({
                    type: \"GET\",
                    url: 'creditos_gestion.php?a=41&dd={$nombre}&nn='+file.name+'&cid={$id_solicitud}',
                    async: false,
                }).responseText;

                if (respuesta2.trim()=='OK') {
                    $('#files_{$nombre}').html('<a href=\"#\" onclick=\"abrir_ajunto(\''+file.name+'\'); return false;\" ><span class=\"glyphicon glyphicon-download-alt\" aria-hidden=\"true\"></span> Abrir documento: $etiqueta </a> ');
                    $('#colbtn_{$nombre}').hide();
                    $('#progress_{$nombre}').hide();          
                } else {
                    showmessage('Error',respuesta2.trim())
                }";
        }


        $salida .=
            "});
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress_$nombre .progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');                    
            });
        </script>";
    }

    if ($etiqueta != "") {
        $salida .= '</div></div>';
    }
    return $salida;
}

function leer_verificaciones_asignados($cid)
{

    $salida = array();

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "SELECT *
        ,(select usuario.nombre from usuario where usuario.usuario=prestamo_gestion.usuario limit 1) as unombre
,(select usuario.nombre from usuario where usuario.usuario=prestamo_gestion.usuario_responde limit 1) as uresponde
,(select usuario.nombre from usuario where usuario.usuario=prestamo_gestion.usuario_confirma limit 1) as uconfirma
        
    FROM prestamo_gestion
    where prestamo_id=$cid
    and campo_id is not null
    
    order by campo_id,id";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $salida[$row["campo_id"]] = $row["gestion_estado"];
                if (strpos($_SESSION["usuario"], "Cd") === false && strpos($_SESSION["usuario"], "D00") === false && strpos($_SESSION["usuario"], "Dis") === false) {
                    $salida["desc"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario"] . ' ' . substr($row["unombre"], 0, 11) . ' ' . fechahora_mysql($row["hora"]) . '</span><br>' . $row["descripcion"];
                    $salida["desc2"][$row["campo_id"]] = '<br><span class="label label-warning">' . $row["usuario"] . ' ' . substr($row["unombre"], 0, 11) . ' ' . fechahora_mysql($row["hora"]) . '</span><br>' . $row["descripcion"];
                } else {
                    $salida["desc"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario"] . ' ' . fechahora_mysql($row["hora"]) . '</span><br>' . $row["descripcion"];
                    $salida["desc2"][$row["campo_id"]] = '<br><span class="label label-warning">' . $row["usuario"] . ' ' . fechahora_mysql($row["hora"]) . '</span><br>' . $row["descripcion"];
                }
                $salida["desc3"][$row["campo_id"]] = "";
                $salida["desc4"][$row["campo_id"]] = '';
                $salida["btn"][$row["campo_id"]] = '';

                if ($row["gestion_estado"] == "Vendedor") {
                    $salida["btn"][$row["campo_id"]] = "&cst=" . $row["etapa_id"] . "&cpo=" . $row["campo_id"] . "&geid=" . $row["id"] . "&cid=" . $row["prestamo_id"] . "&gest=" . $row["gestion_estado"]; //."&num=".$row["numero"]
                }

                if (!es_nulo($row["hora_responde"])) {
                    if (strpos($_SESSION["usuario"], "Cd") === false && strpos($_SESSION["usuario"], "D00") === false && strpos($_SESSION["usuario"], "Dis") === false) {
                        $salida["desc3"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario_responde"] . ' ' . substr($row["uresponde"], 0, 11) . ' ' . fechahora_mysql($row["hora_responde"]) . '</span><br>' . $row["texto_responde"];
                    } else {
                        $salida["desc3"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario_responde"] . ' ' . ' ' . fechahora_mysql($row["hora_responde"]) . '</span><br>' . $row["texto_responde"];
                    }
                }
                if (!es_nulo($row["hora_confirma"])) {
                    if (strpos($_SESSION["usuario"], "Cd") === false && strpos($_SESSION["usuario"], "D00") === false && strpos($_SESSION["usuario"], "Dis") === false) {
                        $salida["desc4"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario_confirma"] . ' ' . substr($row["uconfirma"], 0, 11) . ' ' . fechahora_mysql($row["hora_confirma"]) . '</span><br>' . $row["texto_confirma"];
                    } else {
                        $salida["desc4"][$row["campo_id"]] = '<br><span class="label label-default">' . $row["usuario_confirma"] . ' ' . fechahora_mysql($row["hora_confirma"]) . '</span><br>' . $row["texto_confirma"];
                    }
                }
            }
        }
        $conn->close();
    }
    return $salida;
}

// $n es cada uno de los documentos que aparecen en verificar documentos
// $cid es el id de la prestamo
// $usuario es el usuario de alta
// $asignados es un array con los ids de los documentos que ya se han asignado
function boton_verificar($n, $cid, $comentario = false, $usuario = "", $asignados, $campo_unico)
{
    $salida = "";
    $estado_asignado = "";
    $usuarios_dirigidosa = "";

    if (isset($asignados[$n])) {
        $estado_asignado = $asignados[$n];
    }

    if ($estado_asignado <> "") {
		
        if ($estado_asignado == "Confirmado") {
            $salida = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
            $salida .= $asignados["desc"][$n];
            $salida .= $asignados["desc3"][$n];
            $salida .= $asignados["desc4"][$n];

            //segunda gestion
            if (tiene_permiso(19)) {
                $salida .= '<div id="chkbox'.$n.'">';

                $salida .= 
					'&nbsp;&nbsp;&nbsp;         
					<a href="#" onclick="return false;" data-toggle="collapse" data-target="#chk'.$n.'"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></a>
					<div id="chk'.$n.'" class="collapse">
					<br><h4>Nueva Gestion:</h4>';
                $salida .= campo("obs2_$n", "", 'textarea', '', 'class="form-control" rows="2"', '', '');
                $salida .= "Estado: " . campo("estado2_$n", "", 'select', valores_combobox_texto('<option value="1" selected>Revisar</option><option value="3">Rechazado</option>', ''), ' ', '', '');

                $usuarios_dirigidosa = '<option value="' . $usuario . '" selected>' . $usuario . '</option><option value="' . $_SESSION['usuario'] . '">' . $_SESSION['usuario_nombre'] . '</option>';
                if ($n == 31) { // Verificacion de campo cargar cobradores
                    $usuarios_dirigidosa = get_usuarios_cobradores_select();
                }

                $salida .= "<br>Dirigido a: " . campo("usr2_$n", "", 'select', valores_combobox_texto($usuarios_dirigidosa, ''), ' ', '', '');
                $salida .= '<br><br><a  id="chkbtn2' . $n . '" href="#" class="btn btn-primary" onclick="nueva_gestion(' . $n . ',' . $cid . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Notificar</a>';
                $salida .= '</div></div>';
            }
        }

        if ($estado_asignado == "Vendedor") {
            $salida = '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Gestion pendiente';
			
            $salida .= $asignados["desc2"][$n];
			if ($campo_unico == "") {
            //if (tiene_permiso(18) and $campo_unico == "") {
                $salida .= " <br> <a  href=\"#\" class=\"btn btn-default btn-sm\" onclick=\"actualizarbox('pagina','creditos_gestion.php?a=5b1" . $asignados["btn"][$n] . "') ; return false;\" >Responder</a>";
            }
        }

        if ($estado_asignado == "Creditos") {
            $salida = '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Gestion';
            $salida .= $asignados["desc2"][$n];
            $salida .= $asignados["desc3"][$n];
            if (tiene_permiso(19)) {
                // AQUI ESTA EL BOTON DE CONFIRMAR
                $salida .= '<div id="chkbox' . $n . '">';
                $salida .= '<a id="chkbtn' . $n . '" href="#" onclick="marcar_verificado_gestion(' . $n . ',' . $cid . '); return false;"  class="btn btn-success" >Confirmar</a>';
                if ($comentario == true) {
                    $salida .= campo("obs_$n", "", 'textarea', '', 'class="form-control" rows="2"', '', '');
                }
                $salida .= '</div>';
            }
        }
    } else {
        if (tiene_permiso(19)) {
            $salida = '<div id="chkbox' . $n . '">';
            $salida .= '<a id="chkbtn' . $n . '" href="#" onclick="marcar_verificado(' . $n . ',' . $cid . '); return false;"  class="btn btn-success" >Confirmar</a>';

            if ($comentario == true) {
                $salida .= campo("obs_$n", "", 'textarea', '', 'class="form-control" rows="2"', '', '');
            }

            $salida .= 
				'&nbsp;&nbsp;&nbsp;
				<a href="#" onclick="return false;" data-toggle="collapse" data-target="#chk' . $n . '"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></a>
				<div id="chk' . $n . '" class="collapse">
				<br><h4>Nueva Gestion:</h4>';
            $salida .= campo("obs2_$n", "", 'textarea', '', 'class="form-control" rows="2"', '', '');
            $salida .= "Estado: " . campo("estado2_$n", "", 'select', valores_combobox_texto('<option value="1" selected>Revisar</option><option value="3">Rechazado</option>', ''), ' ', '', '');

            $usuarios_dirigidosa = '<option value="' . $usuario . '" selected>' . $usuario . '</option><option value="' . $_SESSION['usuario'] . '">' . $_SESSION['usuario'] . '</option>';
            if ($n == 31) { // Verificacion de campo cargar cobradores
                $usuarios_dirigidosa = get_usuarios_cobradores_select();
            }

            $salida .= "<br>Dirigido a: " . campo("usr2_$n", "", 'select', valores_combobox_texto($usuarios_dirigidosa, ''), ' ', '', '');

            $salida .= '<br><br><a  id="chkbtn2' . $n . '" href="#" class="btn btn-primary" onclick="nueva_gestion(' . $n . ',' . $cid . '); return false;"><span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> Notificar</a>';

            $salida .= '</div></div>';
        }
    }

    return $salida;
}


function get_usuarios_cobradores_select()
{
    $salida = "";

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "select usuario.usuario,usuario.nombre from usuario
                LEFT OUTER JOIN usuario_nivelxgrupo ON (usuario.grupo_id=usuario_nivelxgrupo.grupo_id) 
                where usuario.activo='SI' and usuario_nivelxgrupo.nivel_id=21
                group by usuario.usuario,usuario.nombre";
        //echo $sql;
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $primero = "selected";
            while ($row = $result->fetch_assoc()) {

                $salida .= '<option value="' . $row['usuario'] . '" ' . $primero . '>' . $row['usuario'] . ' ' . $row['nombre'] . '</option>';
                $primero = "";
            }
        }
        $conn->close();
    }

    return $salida;
}

function generate_hash($vtext)
{
    $seed = substr(sha1(uniqid(rand(), true)), 0, app_Seed_ancho);
    return trim($seed . md5($seed . $vtext . app_Seed));
}


function verificar_password($vpass, $vpass2)
{
    $salida = false;
    $current_hash = $vpass2;
    $seed = substr($current_hash, 0, app_Seed_ancho);
    $hash1 = substr($current_hash, app_Seed_ancho, strlen($current_hash) - app_Seed_ancho);
    $hash2 = md5($seed . $vpass . app_Seed);
    if (trim($hash1) === trim($hash2)) {
        $salida = true;
    }
    return $salida;
}


function crear_password($vpass)
{
    $salida = $vpass;
    $current_hash = $vpass2;
    $seed = substr($current_hash, 0, app_Seed_ancho);
    $hash1 = substr($current_hash, app_Seed_ancho, strlen($current_hash) - app_Seed_ancho);
    $hash2 = md5($seed . $vpass . app_Seed);

    return $salida;
}



function valores_combobox($archivo, $codigo)
{


    $salida = file_get_contents($archivo, FILE_USE_INCLUDE_PATH);
    if ($codigo != "") {
        $salida = str_replace("\"" . $codigo . "\"", "\"" . $codigo . "\" selected", $salida);
    }

    return $salida;
}

function valores_combobox_texto($texto, $codigo)
{


    $salida = $texto;
    if ($codigo != "") {
        $salida = str_replace("\"" . $codigo . "\"", "\"" . $codigo . "\" selected", $salida);
    }

    return $salida;
}


function valores_combobox_db($tabla, $codigo, $campo, $where, $campo_etiqueta = '', $texto_primera = '', $campo_id = 'id')
{
    $salida = "";
    if ($texto_primera <> '') {
        $salida = "<option value=\"0\">$texto_primera</option>";
    }
    if ($campo_etiqueta == '') {
        $campo_etiqueta = $campo;
    }
    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "select $campo_id,$campo from $tabla $where";
        // echo $sql;
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                if ($row[$campo_id] == $codigo) {
                    $seleccionado = " selected";
                } else {
                    $seleccionado = "";
                }
                $salida .= '<option value="' . $row[$campo_id] . '" ' . $seleccionado . '>' . $row[$campo_etiqueta] . '</option>';
            }
        }
        $conn->close();
    }
    return $salida;
}



function leer_permisos_asignados($grupo_id)
{

    $salida = array();

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "SELECT  nivel_id FROM usuario_nivelxgrupo where grupo_id=$grupo_id order by nivel_id";

        $result = $conn->query($sql);
        $i = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salida[$i] = $row["nivel_id"];
                $i++;
            }
        }
        $conn->close();
    }
    return $salida;
}

/**
 * Valida si el usuario posee el nivel de permiso requerido (dbo.usuario_nivelxgrupo)
 * @param mixed $id codigo (id) del grupo (dbo.USUARIO_NIVEL) que se quiere validar
 * @return bool
 */
function tiene_permiso($id)
{
    $salida = in_array($id, $_SESSION['seg']);
    return $salida;
}

function valores_combobox_db_permisos_asignados($id)
{
    $salida = "";

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "SELECT  usuario_nivelxgrupo.nivel_id as id,usuario_nivel.nombre as texto
        FROM usuario_nivelxgrupo
        left outer join usuario_nivel on (usuario_nivelxgrupo.nivel_id=usuario_nivel.id)
        where usuario_nivelxgrupo.grupo_id=$id order by usuario_nivel.nombre";

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salida .= '<option value="' . $row["id"] . '">' . $row['texto'] . '</option>';
            }
        }
        $conn->close();
    }
    return $salida;
}

function valores_combobox_db_distribuidores_bodegas_asignados($id, $tabla)
{
    $salida = "";

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "SELECT  " . $tabla . "xusuario_grupo." . $tabla . "_id as id," . $tabla . ".nombre as texto
        FROM " . $tabla . "xusuario_grupo
        left outer join " . $tabla . " on (" . $tabla . "xusuario_grupo." . $tabla . "_id=" . $tabla . ".codigo)
        where " . $tabla . "xusuario_grupo.grupo_id=$id order by " . $tabla . ".nombre";



        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salida .= '<option value="' . $row["id"] . '">' . $row['texto'] . '</option>';
            }
        }
        $conn->close();
    }
    return $salida;
}


function get_fecha_sistema()
{  //formato de salida= mysql yyyy-mm-dd
    $salida = actual_time("Y-m-d");

    return $salida;
}

function dblookup($tabla, $campo, $codigo, $valor)
{
    $salida = "";
    if ($valor <> "") {
        $salida = get_dato_sql($tabla, $campo, " where $codigo=$valor");
    }

    return $salida;
}

function get_dato_sql($tabla, $campo, $where)
{
    $salida = "";

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        $sql = "select $campo as salida from $tabla $where";
        //echo $sql; exit;
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $salida = trim($row["salida"]);
            }
        }
        $conn->close();
    }
    return $salida;
}




function formato_numero($numero, $decimales, $moneda)
{
    return  $moneda . number_format($numero, $decimales);
}





function get_mes($fecha)
{ // formato entrada yyyy-mm-dd
    if ($fecha <> "") {
        $month = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $num = date("n", strtotime(($fecha)));
        $salida = $month[$num];
        return $salida;
    }
}


function get_dia($fecha)
{ // formato entrada yyyy-mm-dd
    if ($fecha <> "") {
        $salida = date("d", strtotime(($fecha)));
        return $salida;
    }
}

function get_anio($fecha)
{ // formato entrada yyyy-mm-dd
    if ($fecha <> "") {
        $salida = date("Y", strtotime(($fecha)));
        return $salida;
    }
}


function get_spanish_date()
{

    $month = array("", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $fecha = actual_time("j") . " / " . $month[actual_time("n")] . " / " . actual_time("Y");
    return $fecha;
}

function get_spanish_day($fecha)
{ // formato entrada yyyy-mm-dd

    $dia = array('Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab');
    $num = date("w", strtotime($fecha));
    $salida = $dia[$num];
    return $salida;
}



function get_spanish_date_mmm($fecha)
{ // formato entrada dd-mm-yyyy Salida dd-mmm-yyyy

    $pieces = explode("-", $fecha);
    $mes = intval($pieces[1]);
    $month = array("", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
    if (isset($month[$mes])) {
        $salida = $pieces[0] . "-" . $month[$mes] . "-" . $pieces[2];
    } else {
        $salida = $fecha;
    }
    return $salida;
}

function actual_time($format)
{
    $timestamp = time();
    $offset = db_gmt_offset * 60 * 60;
    $timestamp = $timestamp + $offset;
    return gmdate($format, $timestamp);
}


function salida_json($stud_arr)
{

    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 15 Jan 2000 07:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode($stud_arr);
    exit;
}


function no_negtivo_cero($valor)
{
    $salida = $valor;

    if ($valor < 0 and $valor > (-1)) {
        $salida = round($valor, 2);
    }

    return $salida;
}


function insertar_sql($sql)
{
    $salida = 0;
    if ($sql <> "") {


        $conn = new mysqli(db_ip, db_user, db_pw, db_name);
        if (!mysqli_connect_errno()) {
            $conn->set_charset("utf8");

            if ($conn->query($sql) === TRUE) {
                $salida = mysqli_insert_id($conn);
            }
        }
    }
    return $salida;
}

function ejecutar_sql($sql)
{
    $salida = false;
    if ($sql <> "") {


        $conn = new mysqli(db_ip, db_user, db_pw, db_name);
        if (!mysqli_connect_errno()) {
            $conn->set_charset("utf8");

            if ($conn->query($sql) === TRUE) {
                $salida = true;
            }
        }
    }
    return $salida;
}


function es_nulo($campo)
{
    $salida = true;
    if ($campo == "" or is_null($campo) or $campo = "0") {
        $salida = true;
    } else {
        $salida = false;
    }
    return $salida;
}

function ceroif_nulo($campo)
{
    if ($campo == "" or is_null($campo)) {
        $salida = 0;
    } else {
        $salida = $campo;
    }
    return $salida;
}


function restringir_pagina_permiso($permiso)
{
    $salida = false;
    if (!tiene_permiso($permiso)) {
        echo '<div class="row-fluid" >
      <div class="span12">
      <div class="pmbox">
      <h2>Restringido</h2>
        
        <div class="pmblock">No tiene privilegios sobre esta opcion</div>
        </div></div></div>';
        $salida = true;
    }
    return $salida;
}

function mensaje($texto, $tipo)
{
    //opcions: danger , warning , info , success
    return '<div class="alert alert-' . $tipo . '" role="alert">' . $texto . '</div>';
}


function boton_regresar($cod)
{
    return "<br><br><br><a href=\"#\" class=\"btn btn-default\" onclick=\"actualizarbox('pagina','get.php?a=$cod') ; return false;\"><span class=\"glyphicon glyphicon glyphicon-chevron-left \" aria-hidden=\"true\"></span> Regresar</a>  ";
}

function boton_regresar_pag($pagina)
{
    return "<br><br><br><a href=\"#\" class=\"btn btn-default\" onclick=\"actualizarbox('pagina','$pagina') ; return false;\"><span class=\"glyphicon glyphicon glyphicon-chevron-left \" aria-hidden=\"true\"></span> Regresar</a>  ";
}


function cargar_bodegas_dist_grupo($grupo_id)
{

    if ($grupo_id <> "") {


        $conn = new mysqli(db_ip, db_user, db_pw, db_name);
        if (!mysqli_connect_errno()) {
            $conn->set_charset("utf8");

            //bodegas
            $sql = "select bodega_id as salida from bodegaxusuario_grupo where grupo_id=$grupo_id";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($_SESSION['grupo_bodegas'], $row["salida"]);
                }
            }


            //distribuidores
            $sql = "select distribuidor_id as salida from distribuidorxusuario_grupo where grupo_id=$grupo_id";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    array_push($_SESSION['grupo_distribuidores'], $row["salida"]);
                }
            }
        }
    }
}

function armar_sql($campo, $arreglo, $separador)
{

    $salida = "";
    if (count($arreglo) > 0) {
        $salida = "(";
        $sep = "";
        foreach ($arreglo as  $value) {
            $salida .= "$sep $campo='$value'";
            $sep = " $separador ";
        }
        $salida .= ")";
    }
    return $salida;
}


function crear_datatable($nombre, $responsive = 'true', $filtros = false, $no_incluye_col1 = true)
{
    $salida = "
    <script>

             

 var table=$('#$nombre').dataTable(       {
  //    \"bAutoWidth\": true,
      \"bFilter\": true,
    //  \"sPaginationType\": \"full_numbers\",
      //\"bPaginate\": false,
    //  \"bSort\": true,

          //\"bInfo\": false,
          \"bStateSave\": true,
          
          
             \"responsive\": $responsive,   
        
       
          
    \"dom\": '<\"clear\">lfrtTip',
          \"oTableTools\": {
          \"sSwfPath\": \"js/datatable/extensions/TableTools/swf/copy_csv_xls_pdf.swf\"
          },
    
           
        \"bScrollCollapse\": true,
  //    \"bProcessing\": true,

      \"bJQueryUI\": false,
      

      
      \"oLanguage\": {
            \"sProcessing\":     \"Procesando...\",
            \"sLengthMenu\":     \"Mostrar _MENU_ registros\",
            \"sZeroRecords\":    \"No se encontraron resultados\",
            \"sEmptyTable\":     \"Ningun dato disponible en esta tabla\",
            \"sInfo\":           \"Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros\",
            \"sInfoEmpty\":      \"Mostrando registros del 0 al 0 de un total de 0 registros\",
            \"sInfoFiltered\":   \"(filtrado de un total de _MAX_ registros)\",
            \"sInfoPostFix\":    \"\",
            \"sSearch\":         \"Buscar:\",
            \"sUrl\":            \"\",
            \"sInfoThousands\":  \",\",
            \"sLoadingRecords\": \"Cargando...\",
            \"oPaginate\": {
                \"sFirst\":    \"Primero\",
                \"sLast\":     \"Ultimo\",
                \"sNext\":     \"Siguiente\",
                \"sPrevious\": \"Anterior\"
            },
            \"oAria\": {
                \"sSortAscending\":  \": Activar para ordenar la columna de manera ascendente\",
                \"sSortDescending\": \": Activar para ordenar la columna de manera descendente\"
            }

      }
      
      ";


    if ($no_incluye_col1) {
        $salida .= "  ,
            
            \"aoColumnDefs\": [
                        { \"bSearchable\": false,\"bSortable\": false,  \"aTargets\": [ 0 ] }
                        //,{ \"sType\": \"date\", \"bVisible\": true, \"aTargets\": [ 1 ] }
                    ] 
                      ";
    }


    $salida .= "    
    } 
    );
    ";




    if ($filtros) {
        // // Apply the search

        $salida .= "           
        $('#$nombre tfoot th').each(function (i) 
        {

            var title = $('#$nombre thead th').eq($(this).index()).text();
            // or just var title = $('#$nombre thead th').text();
            var serach = '<input type=\"text\" placeholder=\"Busca ' + title + '\" class=\"filtros\" />';
            $(this).html('');
            $(serach).appendTo(this).keyup(function(){table.fnFilter($(this).val(),i)})
        });     
     ";
    }


    $salida .= "
    </script>
    ";

    return $salida;
}

function SAP_verificar_estado_moto($serie_chasis)
{
    $salida = -1;
    //SAP
    //$sql="SELECT TOP 1   [Status] FROM serie WHERE  SerieMotor ='".trim($serie_chasis)."'";
    $sql = "SELECT TOP 1   [Status] FROM OSRI WHERE  SuppSerial ='" . trim($serie_chasis) . "'";

    $conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave, "CharacterSet" => "UTF-8"));
    $stmt2 = sqlsrv_query($conn2, $sql);
    if ($stmt2 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt2) === true) {
        $row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);

        $salida = $row['Status'];
    }

    return $salida;
}

function SAP_actualizar_estado_moto($serie_chasis)
{

    if ($serie_chasis <> "") {

        //SAP
        $sql = "UPDATE OSRN SET U_StatusSerie='02'  WHERE MnfSerial = '" . trim($serie_chasis) . "'";
        $conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave, "CharacterSet" => "UTF-8"));
        $stmt2 = sqlsrv_query($conn2, $sql);
    }
}

function SAP_actualiza_numero_factura_moto()
{
    global $entorno_desarrollo;
    if ($entorno_desarrollo == false) {

        $conn = new mysqli(db_ip, db_user, db_pw, db_name);
        if (mysqli_connect_errno()) {
            exit;
        }
        $conn->set_charset("utf8");
        $sql = "select serie_chasis from garantia where factura_numero_movesa is null";
        $result = $conn->query($sql);


        if ($result->num_rows > 0) {
            $conn2 = sqlsrv_connect(db2_ip, array("Database" => db2_dbn, "UID" => db2_usuario, "PWD" => db2_clave, "CharacterSet" => "UTF-8"));

            while ($row = $result->fetch_assoc()) {

                set_time_limit(120);
                $serie = trim($row['serie_chasis']);
                //SAP
                $sql2 = "SELECT T0.DocNum as nofactura ,CONVERT(char(10), T0.DocDate,126) as fechafactura
            from OINV T0
            Inner Join INV1 T1 on T0.DocEntry =T1.DocEntry
            Inner join OSRN T2 on T1.ItemCode=T2.ItemCode
            LEFT OUTER JOIN  OSRQ T3 on T3.ItemCode =T2.ItemCode and T3.SysNumber =T2.SysNumber and T3.Quantity > 0
            INNER JOIN [ITL1] t4 on t4.ItemCode= T2.ItemCode and t4.SysNumber =T2.SysNumber
            INNER JOIN [OITL] T5 on T5.LogEntry =t4.LogEntry
            where T2.MnfSerial = '$serie' 
            and T5.ApplyType = 13
            and T1.TargetType <> 13
            and T5.ApplyEntry = T1.DocEntry
            ";

                //echo $sql2; exit;

                $stmt2 = sqlsrv_query($conn2, $sql2);
                if ($stmt2 === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                if (sqlsrv_has_rows($stmt2) === true) {
                    $row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);

                    $factura = $row['nofactura'];
                    $fechafactura = $row['fechafactura'];

                    if (!es_nulo($factura)) {

                        ejecutar_sql("update garantia set factura_numero_movesa='$factura',factura_fecha_movesa='$fechafactura' where serie_chasis = '$serie'");
                        $salida = $factura;
                    }
                }
            }
        }
    }
}


function get_gestiones_sql() // genera el sql para mostrar gestiones segun permisos de usuario
{
    $salida = "";

    $salida .= " and prestamo_gestion.gestion_estado is not null
                and prestamo_gestion.gestion_estado<>'Confirmado'";

    if (tiene_permiso(19)) {
        $salida .= " and prestamo_gestion.gestion_estado='Creditos'";
    } else {

        if (tiene_permiso(22)) {

            $sql = '';
            if (tiene_permiso(7)) {
                $sql = " and " . armar_sql('prestamo_gestion.bodega', $_SESSION['grupo_bodegas'], 'or');
            } else {
                if ($_SESSION['usuario_bodega'] <> "") {
                    $sql = " and  prestamo_gestion.bodega='" . $_SESSION['usuario_bodega'] . "'";
                }
            }

            $salida .= " and prestamo_gestion.gestion_estado='Vendedor' $sql";
        } else {
            $salida .= " and prestamo_gestion.usuario_dirigido='" . $_SESSION['usuario'] . "' and prestamo_gestion.gestion_estado='Vendedor'";
        }
    }


    return $salida;
}


function enviar_notificacion_gestion($id_gestion, $tipo, $usuario_dirigido, $descripcion) // envia notificacion de email 
{

    if (isset($entorno_desarrollo)) {
        exit;
    }

    $conn = new mysqli(db_ip, db_user, db_pw, db_name);
    if (!mysqli_connect_errno()) {
        $conn->set_charset("utf8");

        if ($usuario_dirigido <> "") {
            $sql =
                "SELECT email FROM usuario
                WHERE usuario = '{$usuario_dirigido}'";
        } else {
            $sql =
                "SELECT usuario.email from usuario
                LEFT OUTER JOIN usuario_nivelxgrupo ON (usuario.grupo_id=usuario_nivelxgrupo.grupo_id) 
                WHERE usuario.activo = 'SI' AND usuario_nivelxgrupo.nivel_id = 19
                GROUP BY usuario.email";
        }


        $sql .= "        ";

        $sql .= "      ";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row["email"] <> "") {

                    $cuerpo_sinhtml = "Gestion No. $id_gestion disponible en el sistema. $descripcion";
                    $cuerpo_html = $cuerpo_sinhtml;
                    enviar_correo($row["email"], "Nueva Gestion", $cuerpo_html, $cuerpo_sinhtml);
                }
            }
        }
    }
}

function incrustar_objeto($nombre, $archivo, $alto = 600, $ancho = '100%')
{

    $salida = "<hr><strong>$nombre</strong><br><br>";

    if ($archivo <> "") {
        $archivo = "doctos\\" . $archivo;
        $ext = strtoupper(substr($archivo, -3));
        if ($ext == 'PDF') {
            $salida .= ' <embed src="' . $archivo . '" width="' . $ancho . '" height="' . $alto . '"></embed>';
        } else {
            $salida .= '<div class="table-responsive"><img src="' . $archivo . '" ></div>';
        }
    } else {
        $salida .= "No Asignado";
    }


    return $salida;
}

function mayus_string($texto)
{
    $textoMayus = strtoupper($texto);
    return $textoMayus;
}


function convertir_array_dropdown(
    $arregloDatos,
    $defaultValue = "",
    $agregarPlaceholder = true,
    $keyValue = "value",
    $keyLabel = "label",
    $keyID = null
) {
    $options = "";

    if ($agregarPlaceholder) {
        $options = "<option value=''>Seleccione</option>";
    }

    $options .= implode("\n", array_map(function ($item) use ($keyLabel, $keyValue, $keyID, $defaultValue) {
        $id = empty($keyID) ? "" : "id='{$item[$keyID]}'";
        $selected = $item[$keyValue] == $defaultValue ? "selected" : "";
        return "<option {$id} value='{$item[$keyValue]}' {$selected}>{$item[$keyLabel]}</option>";
    }, $arregloDatos));

    return $options;
}
