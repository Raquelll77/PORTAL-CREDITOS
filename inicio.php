<?php

require_once('include/protect.php');
require_once('include/framework.php');

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <meta name="description" content="">
  <meta name="author" content="Infosistemas">
  <link rel="icon" href="favicon.ico">

  <title><?php echo app_title; ?></title>

  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/bootstrap-theme.min.css" rel="stylesheet">

  <link href="css/smoothness/jquery-ui-1.10.0.custom.min.css" rel="stylesheet" />

  <link href="js/datatable/css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="js/datatable/extensions/TableTools/css/dataTables.tableTools.min.css" rel="stylesheet" />
  <link href="js/datatable/extensions/responsive/css/dataTables.responsive.css" rel="stylesheet" />
  <link href="js/select/select2.css" rel="stylesheet" />
  <link href="css/datepicker.css" rel="stylesheet" />
  <link href="js/jasny-bootstrap/css/jasny-bootstrap.min.css" rel="stylesheet" />

  <link href="css/jquery.fileupload.css" rel="stylesheet">

  <link href="css/sistema.css" rel="stylesheet">




</head>

<body role="document">

  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Menu</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>

        </button>
        <!--    <span class="navbar-brand" aria-hidden="true"><?php echo $_SESSION['usuario_distribuidor_nombre']; ?></span> -->
      </div>
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav pull-right">
          <li class="active"><a href="inicio.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> INICIO</a></li>

          <?php if (tiene_permiso(2)) { ?>

            <li role="presentation" class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">GARANTIAS <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <?php if (tiene_permiso(5)) { ?> <li><a href="#" onclick="actualizarbox('pagina','get.php?a=11') ; return false;">Activar Garantia</a></li><?php    } ?>
                <?php if (tiene_permiso(4)) { ?> <li><a href="#" onclick="actualizarbox('pagina','get.php?a=14') ; return false;">Consultar Garantias</a></li><?php    } ?>
                <?php if (tiene_permiso(10)) { ?> <li><a href="#" onclick="actualizarbox('pagina','get.php?a=18&b=p') ; return false;">Consultar Garantias Pendientes Facturar</a></li><?php    } ?>
                <?php if (tiene_permiso(11)) { ?> <li><a href="#" onclick="actualizarbox('pagina','get.php?a=19&b=f') ; return false;">Consultar Garantias Facturadas</a></li><?php    } ?>
                <?php if (tiene_permiso(3)) { ?> <li><a href="#" onclick="actualizarbox('pagina','servicio.php') ; return false;">Modulo de Servicio</a></li><?php    } ?>
              </ul>
            </li>



          <?php    } ?>






          <?php if (tiene_permiso(14)) { ?> <li><a href="#" onclick="actualizarbox('pagina','inventario.php') ; return false;">INVENTARIO</a></li><?php } ?>


          <?php if (tiene_permiso(13)) { ?> <li><a href="#" onclick="actualizarbox('pagina','contactos.php') ; return false;">CONTACTOS</a></li><?php } ?>

          <?php if (tiene_permiso(15)) { ?> <li><a href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;">CREDITOS</a></li><?php } ?>

          <?php if (tiene_permiso(27)) { ?> <li><a href="#" onclick="actualizarbox('pagina','cobros.php') ; return false;">COBROS</a></li><?php } ?>

          <?php if (tiene_permiso(1)) { ?>
            <li role="presentation" class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> <span class="caret"></span></a>


              <ul class="dropdown-menu">


                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=15&a=b') ; return false;">Usuarios</a></li>
                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=2&a=b') ; return false;">Grupos de Usuarios</a></li>
                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=6&a=b') ; return false;">Puestos</a></li>



                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=3&a=b') ; return false;">Clientes</a></li>
                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=4&a=b') ; return false;">Almacenes</a></li>
                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=5&a=b') ; return false;">Empresas</a></li>

                <li><a href="#" onclick="actualizarbox('pagina','crud.php?a=1&tbl=7&a=b') ; return false;">Opciones</a></li>
              </ul>
            </li>

          <?php } ?>


          <li role="presentation" class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <?php echo $_SESSION['usuario']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a onclick="efectuar_proceso(7,'Modificar Contrase&ntilde;a',2,'','');"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Contrase&ntilde;a</a></li>
              <li><a href="index.php?ac=logout" onclick="return salir();"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> SALIR</a></li>
            </ul>
          </li>

        </ul>









      </div><!--/.nav-collapse -->
    </div>
  </nav>

  <div class="container">

    <div id="pagina" class="starter-template">




      <div align="center" valign="middle"><img src="images/logo.png" vspace="10" /></div>
      <p>&nbsp;</p>
      <div class="row">
        <div class="col-sm-3 text-center">
          <?php if (tiene_permiso(2)) {           ?>

            <br><a href="#" onclick="actualizarbox('pagina','get.php?a=sub1') ; return false;" class="btn btn-lg btn-primary  botonesmenu"> GARANTIAS</a>

          <?php } ?>
        </div>

        <div class="col-sm-3 text-center">
          <?php if (tiene_permiso(14)) {           ?>

            <br><a href="#" onclick="actualizarbox('pagina','inventario.php') ; return false;" class="btn btn-lg btn-primary botonesmenu"> INVENTARIO</a>

          <?php } ?>
        </div>

        <div class="col-sm-3 text-center">
          <?php if (tiene_permiso(13)) {           ?>

            <br><a href="#" onclick="actualizarbox('pagina','contactos.php') ; return false;" class="btn btn-lg btn-primary botonesmenu "> CONTACTOS</a>

          <?php } ?>
        </div>




        <div class="col-sm-3 text-center">
          <?php if (tiene_permiso(15)) {           ?>

            <br><a href="#" onclick="actualizarbox('pagina','creditos.php') ; return false;" class="btn btn-lg btn-primary botonesmenu"> CREDITOS</a>

          <?php } ?>
        </div>

      </div>

      <div class="row">
        <br>
        <div class="col-sm-12 text-center">
          <?php if (tiene_permiso(27)) {           ?>

            <br><a href="#" onclick="actualizarbox('pagina','cobros.php') ; return false;" class="btn btn-lg btn-primary botonesmenu"> COBROS</a>

          <?php } ?>
        </div>

      </div>

    </div>

    <?php
    //echo "dist:".$_SESSION['usuario_distribuidor_nombre'] . '<br>'."bodega:".$_SESSION['usuario_bodega_nombre'];
    ?>


  </div><!-- /.container -->



  <footer class="footer">
    <div class="container footerbotones">
      <div class="row">
        <div class="col-xs-6"><a href="inicio.php" class="btn btn-sm btn-default "> <span class="glyphicon glyphicon-home" aria-hidden="true"></span><span class="hidden-xs"> Inicio</span></a> </div>
        <div class="col-xs-6">
          <div class="pull-right"><a href="index.php?ac=logout" onclick="return salir();" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span><span class="hidden-xs"> Salir</span></a></div>
        </div>

      </div>

    </div>
  </footer>


  <!--  JavaScript ==================================================  <script src="js/select2/js/select2.full.min.js"></script> -->
  <script src="js/jquery-2.1.3.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>



  <script src="js/bootstrap-datepicker.js"></script>
  <script type="text/javascript" src="js/sistema-1.4.js"></script>
  <script src="js/select/select2.min.js"></script>
  <script src="js/moment.min.js"></script>
  <script src="js/amortisation.js"></script>
  <script src="js/easypiechart.js"></script>

  <script src="js/fileupload/vendor/jquery.ui.widget.js"></script>
  <script src="js/fileupload/jquery.iframe-transport.js"></script>
  <script src="js/fileupload/jquery.fileupload.js"></script>

  <script src="js/jasny-bootstrap/js/jasny-bootstrap.min.js"></script>

  <script type="text/javascript">
    $.ajaxSetup({
      cache: false
    });

    $(document).ready(function() {

      $(document).on('click', '.navbar-collapse.in', function(e) {
        if ($(e.target).is('a')) {
          $(this).collapse('hide');
        }
      });

    });
  </script>


  <script type="text/javascript" language="javascript" src="js/datatable/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/datatable/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
  <script type="text/javascript" language="javascript" src="js/datatable/extensions/responsive/js/dataTables.responsive.min.js"></script>
  <script type="text/javascript" src="js/chart.min.js"></script>
  <script type="text/javascript" src="js/duallistbox/jquery.dualListBox-1.3.min.js"></script>




  <?php
  if (isset($_GET["redirect_to"]) && !empty($_GET["redirect_to"])) {
    $credito_id = $_GET["redirect_to"];
    echo
    "<script>
      actualizarbox('pagina','creditos_gestion.php?a=1&cid={$credito_id}');
    </script>";
  }
  ?>

</body>

</html>