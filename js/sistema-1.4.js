function salir() {
  if (confirm("Desea salir del sistema?") == true) {
    return true;
  } else {
    return false;
  }
}

function showmessage(titulo, mensaje, tipo) {
  if (tipo === undefined) tipo = "ui-icon-info"; //ui-icon-alert

  var NewDialog = $(
    '<div><p><span class="ui-icon ' +
      tipo +
      '" style="display:inline-block"></span> ' +
      mensaje +
      "</p> </div>"
  );
  NewDialog.dialog({
    modal: true,
    title: titulo,
    show: "clip",
    hide: "clip",
    stack: true,
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "Cerrar",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

function actualizarbox(campo, url) {
  $("#" + campo).html(
    ' <div align="center" valign="middle"><br><br><p><img src="images/load.gif"/></p></div>'
  );

  $("#" + campo).load(url, function (response, status, xhr) {
    if (status == "error") {
      $("#" + campo).html("Error al cargar los datos. Intente nuevamente"); // xhr.status + " " + xhr.statusText
    }
  });
}

function actualizarbox_confirmar(campo, url, titulo, mensaje, tipo, elemento) {
  if (tipo === undefined) tipo = "ui-icon-info"; //ui-icon-alert

  var NewDialog = $(
    '<div><p><span class="ui-icon ' +
      tipo +
      '" style="display:inline-block"></span> ' +
      mensaje +
      "</p> </div>"
  );
  NewDialog.dialog({
    modal: true,
    title: titulo,
    show: "clip",
    hide: "clip",
    stack: true,
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "SI",
        click: function () {
          actualizarbox(campo, url);
          $("#" + elemento).hide();
          $("#btnimprimir").show();
          $(this).dialog("close");
        },
      },
      {
        text: "NO",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

function procesar(url) {
  $("#Guardar").attr("disabled", "disabled");

  $("#respuesta").html('<div><p><img src="images/load.gif"/></p></div>');

  var respuesta = $.ajax({
    type: "GET",
    url: url + "&" + $("#formop").serialize(),
    async: false,
  }).responseText;

  $("#respuesta").html(respuesta.trim());
  // $("#respuesta").load(url+'&'+$("#formop").serialize(), function(response, status, xhr) {
  // if (status == "error") {
  // $("#respuesta").html("Error al abrir pagina. Intente nuevamente"); // xhr.status + " " + xhr.statusText
  // }
  // });

  $("#Guardar").removeAttr("disabled");
}

function procesar_datos(url, forma) {
  $("#Guardar" + forma).attr("disabled", "disabled");

  $("#respuesta" + forma).html(
    '<div><p><img src="images/load.gif"/></p></div>'
  );

  var respuesta = $.ajax({
    type: "GET",
    url: url + "&" + $("#forma" + forma).serialize(),
    async: false,
  }).responseText;

  $("#respuesta" + forma).html(respuesta.trim());
  // $("#respuesta").load(url+'&'+$("#formop").serialize(), function(response, status, xhr) {
  // if (status == "error") {
  // $("#respuesta").html("Error al abrir pagina. Intente nuevamente"); // xhr.status + " " + xhr.statusText
  // }
  // });

  $("#Guardar" + forma).removeAttr("disabled");
}

function procesar_datos_contrato(url, forma) {
  $("#Guardar" + forma).attr("disabled", "disabled");

  $("#respuesta" + forma).html(
    '<div><p><img src="images/load.gif"/></p></div>'
  );

  var ajaxData = $("#forma2" + forma).serialize();

  var respuesta = $.ajax({
    type: "GET",
    url: url + "&" + $("#forma" + forma).serialize(),
    data: ajaxData,
    async: false,
  }).responseText;

  if (respuesta.trim() == "OK") {
    $("#respuesta" + forma).html(
      '<div class="alert alert-success" role="alert">El Contrato fue Generado Satisfactoriamente</div>'
    );

    $("#Guardar" + forma).hide();
    $("#respboton" + forma).show();
  } else {
    $("#respuesta" + forma).html(respuesta.trim());
    $("#Guardar" + forma).removeAttr("disabled");
  }
}

function procesar_gestion10(url, forma) {
  $("#gestionbtn" + forma).attr("disabled", "disabled");

  $("#gestionrespuesta" + forma).html(
    '<div><p><img src="images/load.gif"/></p></div>'
  );

  var ajaxData = $("#forma2" + forma).serialize();

  var respuesta = $.ajax({
    type: "GET",
    url: url + "&" + $("#forma" + forma).serialize(),
    data: ajaxData,
    async: false,
  }).responseText;

  if (respuesta.trim() == "OK") {
    $("#gestionrespuesta" + forma).html(
      '<div class="alert alert-success" role="alert">Gestion Creada Satisfactoriamente</div>'
    );

    $("#gestionbtn" + forma).hide();
  } else {
    $("#gestionrespuesta" + forma).html(respuesta.trim());
  }
}

function procesar_datos_remitirgestion(url, forma) {
  $("#Guardar" + forma).attr("disabled", "disabled");

  $("#respuesta" + forma).html(
    '<div><p><img src="images/load.gif"/></p></div>'
  );

  var respuesta = $.ajax({
    type: "GET",
    url: url + "&" + $("#forma" + forma).serialize(),
    async: false,
  }).responseText;

  $("#respuesta" + forma).html(respuesta.trim());

  $("#Guardar" + forma).hide();
  // $("#Guardar"+forma).removeAttr("disabled");
}

function procesar_datos_gestion(url, forma) {
  $("#Guardar" + forma).attr("disabled", "disabled");

  $("#respuesta" + forma).html(
    '<div><p><img src="images/load.gif"/></p></div>'
  );

  var ajaxData = $("#forma2" + forma).serialize();

  var respuesta = $.ajax({
    type: "POST",
    data: ajaxData,
    url: url + "&" + $("#forma" + forma).serialize(),
    async: false,
  }).responseText;

  $("#respuesta" + forma).html(respuesta.trim());

  $("#Guardar" + forma).removeAttr("disabled");
}

function get_gestiones(solicitud, numero) {
  $("#tablasolicitudes").hide();
  $("#tablagestiones").show();

  $("#tablagestiones").html('<div><p><img src="images/load.gif"/></p></div>');

  var respuesta = $.ajax({
    type: "GET",
    url: "creditos.php?a=0b&cid=" + solicitud + "&num=" + numero,
    async: false,
  }).responseText;

  $("#tablagestiones").html(respuesta.trim());
}

function get_gestiones_regresar() {
  $("#tablagestiones").hide();
  $("#tablasolicitudes").show();
}

function convertir_num(valor) {
  ///var str = valor ;
  //var res = str.replace("_", "");
  //return parseInt(res);
  return parseFloat(valor);
}

function calculos_financieros1() {
  var t1 = parseFloat($("#endeuda_tarjeta").val());
  var t2 = parseFloat($("#endeuda_prestamo").val());
  var t3 = parseFloat($("#endeuda_cooperativa").val());
  var t4 = parseFloat($("#cuota").val());
  var t5 = parseFloat($("#endeuda_otros").val());

  var total = t1 + t2 + t3 + t4 + t5;

  $("#endeuda_total").val(total.toFixed(2));

  calculos_financieros2();
}

function calculos_financieros2() {
  showValues();
  calcularCuotaPromocionOctubre();

  var t1 = parseFloat($("#endeuda_total").val());
  var t2 = parseFloat($("#endeuda_sueldo").val());

  $("#endeuda_movesa").val($("#cuota").val());

  var total = t1 / t2;

  $("#endeuda_nivel").val(total.toFixed(2));

  var t9 = parseFloat($("#cuota").val());
  var total9 = 0;
  total9 = t9 / 0.25;
  $("#endeuda_sueldo_requerido").val(total9.toFixed(2));
}

function calculos_financieros3() {}

function calculos_financieros4() {
  var t1 = parseFloat($("#monto_prestamo").val());
  var t2 = parseFloat($("#monto_seguro").val());
  var t3 = parseFloat($("#monto_prima").val());

  var total = t1 + t2 - t3;

  $("#monto_financiar").val(total.toFixed(2));

  showValues();
  calcularCuotaPromocionOctubre();

  var t9 = parseFloat($("#cuota").val());
  var total9 = 0;
  total9 = t9 / 0.25;
  $("#endeuda_sueldo_requerido").val(total9.toFixed(2));
}

// -------------------------------
// 🔹 CÁLCULO NORMAL (INTERÉS FLAT)
// -------------------------------
function calcularCuotaFlat(monto, tasaAnual, plazo) {
  if (isNaN(monto) || isNaN(tasaAnual) || isNaN(plazo) || plazo <= 0) return 0;

  // Interés mensual flat
  const interesMensual = (monto * (tasaAnual / 100)) / 12;
  const cuota = interesMensual + monto / plazo;

  return Math.ceil(cuota * 100) / 100;
}

// ----------------------------------------------------------
// 🔹 CÁLCULO CON PROMOCIÓN (DISTRIBUYE UN MES DE INTERÉS FLAT)
// ----------------------------------------------------------
function calcularCuotaFlatPromocion(monto, tasaAnual, plazo) {
  const cuotaNormal = calcularCuotaFlat(monto, tasaAnual, plazo);

  // Interés mensual base
  const interesMensual = (monto * (tasaAnual / 100)) / 12;

  // Suma el interés de un mes extra repartido entre todas las cuotas
  const interesExtraPorMes = interesMensual / plazo;
  const cuotaConPromo = cuotaNormal + interesExtraPorMes;

  return Math.ceil(cuotaConPromo * 100) / 100;
}

// ----------------------------------------------------------------------
// 🔹 FUNCIÓN GENERAL: devuelve ambos valores (normal y con promoción)
// ----------------------------------------------------------------------
function obtenerCuotas(monto, tasaAnual, plazo) {
  const normal = calcularCuotaFlat(monto, tasaAnual, plazo);
  const promocion = calcularCuotaFlatPromocion(monto, tasaAnual, plazo);
  return { normal, promocion };
}

function showValues() {
  var amt = $("#monto_financiar").val();
  var rate = $("#tasa").val();
  var tenure = $("#plazo").val();
  var ext = 0;

  // CALCULA LA CUOTA DE LA FORMA INTERES FLAT
  var cuota = (amt * (rate / 100)) / 12 + amt / tenure;
  cuota = Math.ceil(cuota * 100) / 100;

  if (!(isNaN(amt) || isNaN(rate) || isNaN(tenure) || isNaN(ext))) {
    $("#cuota").val(cuota.toFixed(2));
    // $("#amSchedule").amortize({
    //   amount: amt,
    //   rate: rate,
    //   tenure: tenure, 1,443   1,803.75
    //   extras: ext,
    // });
  }
}

// Agrega el interes segun el tipo de perfil del cliente
// (parametro anterior: cid)
function perfil_interes() {
  const tasas_perfiles = {
    per_normal: {
      tipo_perfil: "Perfil Prima Normal",
      interes_mensual: 3.98,
      interes_moratorio: 3.9,
      tasa: 47.8,
    },
    per_alta: {
      tipo_perfil: "Perfil Prima Alta",
      interes_mensual: 3,
      interes_moratorio: 2.92,
      tasa: 36,
    },
    per_informal: {
      tipo_perfil: "Perfil Informal",
      interes_mensual: 4,
      interes_moratorio: 3.92,
      tasa: 48,
    },
    per_mototaxi: {
      tipo_perfil: "Perfil Mototaxi",
      interes_mensual: 4,
      interes_moratorio: 3.92,
      tasa: 48,
    },
    per_usados: {
      tipo_perfil: "Perfil Usados",
      interes_mensual: 4,
      interes_moratorio: 3.92,
      tasa: 48,
    },
    per_ktm: {
      tipo_perfil: "Perfil KTM",
      interes_mensual: 3.75,
      interes_moratorio: 3.67,
      tasa: 45,
    },
    per_planilla: {
      tipo_perfil: "Convenio deduccion Leyde",
      interes_mensual: 3,
      interes_moratorio: 2.92,
      tasa: 36,
    },
    per_splanilla: {
      tipo_perfil: "Convenio sin deduccion Planilla",
      interes_mensual: 3.67,
      interes_moratorio: 3.58,
      tasa: 44,
    },
    per_vehico: {
      tipo_perfil: "Perfil Vehico",
      interes_mensual: 2.5,
      interes_moratorio: 2.42,
      tasa: 30,
    },
    per_vehico_pre: {
      tipo_perfil: "Perfil Vehico Preferencial",
      interes_mensual: 1.54,
      interes_moratorio: 1.46,
      tasa: 18.5,
    },
    per_empresas: {
      tipo_perfil: "Convenio Empresas",
      interes_mensual: 3.33,
      interes_moratorio: 3.25,
      tasa: 40,
    },
    per_empleados: {
      tipo_perfil: "Credito Empleados",
      interes_mensual: 2.25,
      interes_moratorio: 2.17,
      tasa: 27,
    },
    per_cfarmaciaahorro: {
      tipo_perfil: "Convenio Farmacias el Ahorro",
      interes_mensual: 3.58,
      interes_moratorio: 3.5,
      tasa: 43,
    },
  };

  var perfil = $("#cierre_interes_mensual").val();
  var perfil_id = $("#cierre_interes_mensual option:selected").attr("id");
  var tipo_perfil = $("#tipo_perfil").val();

  const datos_tasa_perfil = tasas_perfiles[perfil_id];

  var interes_mensual = datos_tasa_perfil.interes_mensual;
  var interes_moratorio = datos_tasa_perfil.interes_moratorio;
  var tasa = datos_tasa_perfil.tasa;
  tipo_perfil = datos_tasa_perfil.tipo_perfil;

  // if(perfil == 3.98){
  //   // Si es perfil Prima Normal
  //   // interes_agregado = 3.98;
  //   interes_mensual = 3.98;
  //   interes_moratorio = 3.9;
  //   tasa = 47.8;
  //   tipo_perfil = "Perfil Prima Normal";
  // } else if(perfil == 3){
  //   // Si es perfil Prima Alta
  //   // interes_agregado = 3;
  //   interes_mensual = 3;
  //   interes_moratorio = 2.92;
  //   tasa = 36
  //   tipo_perfil = "Perfil Prima Alta";
  // } else if(perfil == 4){
  //   // Si es perfil Informal, MotoTaxi, Usados
  //   // interes_agregado = 4;
  //   interes_mensual = 4;
  //   interes_moratorio = 3.92;
  //   tasa = 48;
  //   if(perfil_id == "per_informal"){
  //     tipo_perfil = "Perfil Informal";
  //   } else if(perfil_id == "per_mototaxi"){
  //     tipo_perfil = "Perfil Mototaxi"
  //   } else if (perfil_id == "per_usados"){
  //     tipo_perfil = "Perfil Usados";
  //   }

  // } else if(perfil == 2.33){
  //   // Si es perfil KTM
  //   // interes_agregado = 2.33;
  //   interes_mensual = 3;
  //   interes_moratorio = 2.92;
  //   tasa = 36;
  //   tipo_perfil = "Perfil KTM"

  // } else if(perfil == 1.92){
  //   // Si es perfil convenio de planilla
  //   // interes_agregado = 4;
  //   interes_mensual = 1.92;
  //   interes_moratorio = 1.83;
  //   tasa = 23;
  //   tipo_perfil = "Convenio de Planilla"
  // }  else {
  //   console.log("No hay perfil");
  // }

  $("#tasa").val(tasa);
  $("#cierre_interes_moratorio").val(interes_moratorio);
  $("#tipo_perfil").val(tipo_perfil);
  showValues();
  calcularCuotaPromocionOctubre();
}

function actualizarSelect() {
  setTimeout(() => {
    if ($("#cierre_interes_mensual")) {
      var tipo_perfil = $("#tipo_perfil").val();
      $("#cierre_interes_mensual option").removeAttr("selected");
      if (tipo_perfil == "Perfil Informal") {
        $("#per_informal").attr("selected", "selected");
      } else if (tipo_perfil == "Perfil Mototaxi") {
        $("#per_mototaxi").attr("selected", "selected");
      } else if (tipo_perfil == "Perfil Usados") {
        $("#per_usados").attr("selected", "selected");
      }
    } else {
      console.log("No existe en el DOM");
    }
  }, 3500);
}

function ubicacion_gps() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
      $("#direccion_gps").val(
        position.coords.latitude + " , " + position.coords.longitude
      );
    });
  } else {
    $("#direccion_gps").val("NO GPS");
  }
}

function abrir_mapa(gps) {
  // document.getElementById("GeoAPI").innerHTML = "<iframe style=\"width: 400px; height: 400px\" frameborder=\"0\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" src=\"http://maps.google.com/?ll=" + gps + "&z=16&output=embed\"></iframe>";
  $("#GeoAPI").html(
    '<iframe style="width: 400px; height: 400px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/?q=' +
      gps +
      '&z=16&output=embed"></iframe>'
  );
  $("#GeoAPI").show();
}

// FUNCION PARA MARCAR EL DOCUMENTO COMO ACEPTADO O CONFIRMADO
function marcar_verificado_gestion(n, cid) {
  var errores = "";
  var url2 = "";
  if ($("#obs_" + n).length) {
    // verificar si existe campo
    if (
      $("#obs_" + n)
        .val()
        .trim() == ""
    ) {
      errores = "si";
      showmessage("Comentario Obligatorio", "Debe ingresar el comentario");
    } else {
      url2 = "&obs=" + $("#obs_" + n).val();
    }
  }

  if (errores == "") {
    $("#chkbtn" + n).attr("disabled", "disabled");

    var respuesta = $.ajax({
      type: "GET",
      url:
        "creditos_gestion.php?cid=" + cid + "&a=5x2c&n=" + n + encodeURI(url2), // lo envia a la linea de codigo 900 (aproximadamente donde if($accion == "5x2c"))
      async: false,
    }).responseText;

    if (respuesta.trim() == "OK") {
      $("#chkbox" + n).html(
        '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>'
      );
    } else {
      $("#chkbtn" + n).removeAttr("disabled");
      showmessage("", respuesta.trim(), "ui-icon-alert");
    }
  }
}

function marcar_verificado(n, cid) {
  var url2 = "";
  var errores = "";

  if ($("#obs_" + n).length) {
    // verificar si existe campo
    if (
      $("#obs_" + n)
        .val()
        .trim() == ""
    ) {
      errores = "si";
      showmessage("Comentario Obligatorio", "Debe ingresar el comentario");
    } else {
      url2 = "&obs=" + $("#obs_" + n).val();
    }
  }

  url2 = url2 + "&eid=" + $("#ccetapa").val();

  if (errores == "") {
    $("#chkbtn" + n).attr("disabled", "disabled");

    var respuesta = $.ajax({
      type: "GET",
      url: "creditos_gestion.php?cid=" + cid + "&a=5x&n=" + n + encodeURI(url2),
      async: false,
    }).responseText;
    //alert('click');
    if (respuesta.trim() == "OK") {
      $("#chkbox" + n).html(
        '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>'
      );
    } else {
      $("#chkbtn" + n).removeAttr("disabled");
      showmessage("", respuesta.trim(), "ui-icon-alert");
    }
  }
}

function nueva_gestion(n, cid) {
  var url2 = "";
  var errores = "";

  if (
    $("#obs2_" + n)
      .val()
      .trim() == ""
  ) {
    errores = "si";
    showmessage("Comentario Obligatorio", "Debe ingresar el comentario");
  } else {
    url2 = "&obs=" + $("#obs2_" + n).val();
    url2 = url2 + "&est=" + $("#estado2_" + n).val();
    url2 = url2 + "&usr=" + $("#usr2_" + n).val();
  }

  url2 = url2 + "&eid=" + $("#ccetapa").val();

  if (errores == "") {
    $("#chkbtn2" + n).attr("disabled", "disabled");

    var respuesta = $.ajax({
      type: "GET",
      url: "creditos_gestion.php?cid=" + cid + "&a=5y&n=" + n + encodeURI(url2),
      async: false,
    }).responseText;

    if (respuesta.trim() == "OK") {
      $("#chkbox" + n).html(
        '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>'
      );
    } else {
      $("#chkbtn2" + n).removeAttr("disabled");
      showmessage("", respuesta.trim(), "ui-icon-alert");
    }
  }
}

function efectuar_proceso(a, titulo, o1, o2, o3) {
  var url = "get.php?a=" + a + "&o1=" + o1 + "&o2=" + o2 + "&o3=" + o3;

  var NewDialog = $('<div><p><img src="images/load.gif"/></p></div>');
  NewDialog.load(url).dialog({
    modal: true,
    title: titulo,
    width: 400,
    height: 400,
    position: { my: "center", at: "center", of: window },
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "Guardar",
        click: function () {
          //var ajaxData = { valores: JSON.stringify(valores) };

          var c2 = $("#mcca").val();
          var c3 = $("#mccb").val();
          var avanzar = true;

          if (o1 == 2) {
            var c32 = $("#mccb2").val();
            if (c3 != c32) {
              avanzar = false;
              showmessage(
                titulo,
                "La contrase&ntilde;a nueva y la repeticion de la contrase&ntilde;a nueva no son iguales.",
                "ui-icon-alert"
              );
            }
          }

          if (avanzar) {
            var url2 =
              "get.php?a2=u&a=" +
              a +
              "&o1=" +
              o1 +
              "&o2=" +
              c2 +
              "&o3=" +
              c3 +
              "&o4=" +
              o2;
            var respuesta = $.ajax({
              url: url2,
              type: "POST",
              async: false,
            }).responseText;

            if (respuesta.trim() == "ok") {
              showmessage(titulo, "Se guardo el registro");
              $(this).dialog("close");
            } else {
              showmessage(titulo, respuesta.trim(), "ui-icon-alert");
            }
          }
        },
      },
      {
        text: "Cerrar",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

function escapeTags(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

function file_ext(file) {
  return /[.]/.exec(file) ? /[^.]+$/.exec(file.toLowerCase()) : "";
}

function popupwindow(url, title, w, h) {
  var left = screen.width / 2 - w / 2;
  var top = screen.height / 2 - h / 2;
  return window.open(
    url,
    title,
    "toolbar=no, location=no, directories=no, status=no,scrollbars=1, menubar=no,   copyhistory=no, width=" +
      w +
      ", height=" +
      h +
      ", top=" +
      top +
      ", left=" +
      left
  );
}

function seleccionSalario() {
  var salario_tipo = $("#empresa_salario_tipo").val();
  if (salario_tipo == "PRESTAMO EXTERNO") {
    $(".prestamo_externo_especifique").show();
  } else {
    $(".prestamo_externo_especifique").hide();
  }
}

function seleccionNacionalidad() {
  var otraNacionalidad = $("#otra_nacionalidad").val();
  if (otraNacionalidad == "SI") {
    $(".nacionalidad_extra").show();
  } else {
    $(".nacionalidad_extra").hide();
  }
}

function origenTercero() {
  var origenTercero = $("#origen_fondo_tercero").val();

  if (origenTercero == "SI") {
    $(".monto_tercero").show();
    $(".vinculo_tercero").show();
    $(".nombre_tercero").show();
    $(".identificacion_tercero").show();
    $(".actividad_economica_tercero").show();
    $(".ingreso_mensual_tercero").show();
    $(".telefono_tercero").show();
  } else {
    $(".monto_tercero").hide();
    $(".vinculo_tercero").hide();
    $(".nombre_tercero").hide();
    $(".identificacion_tercero").hide();
    $(".actividad_economica_tercero").hide();
    $(".ingreso_mensual_tercero").hide();
    $(".telefono_tercero").hide();
  }
}

function imprimir_portada(rid) {
  var url = "creditos_imprimir_portada.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_garantia(rid) {
  var url = "generar_garantia.php?g=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_garantia_anulada(rid) {
  var url = "generar_garantia_nula.php?g=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_contratos(rid) {
  var url = "creditos_imprimir_contratos.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_relacionci(rid) {
  var url = "creditos_imprimir_relacionci.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_carta_poder(rid) {
  var url = "creditos_imprimir_carta_poder.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_autorizacion_decomiso(rid) {
  var url = "creditos_imprimir_decomiso.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function imprimir_forma_pago(rid) {
  var url = "creditos_imprimir_forma_pago.php?cid=" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function abrir_ajunto(rid) {
  var url = "doctos/" + rid;
  popupwindow(url, "Documento", screen.width, screen.height);
}

function asignar_distribuidor(rid) {
  var titulo = "Asignacion de Clientes";
  var url = "get.php?rid=" + rid + "&o=lb&a=81";

  var NewDialog = $('<div><p><img src="images/load.gif"/></p></div>');
  NewDialog.load(url).dialog({
    modal: true,
    title: titulo,
    width: "95%",
    height: 600,
    position: { my: "center", at: "center", of: window },
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "Guardar",
        click: function () {
          var valores = new Array();
          $("#box2View option").each(function (i) {
            valores.push($(this).val());
          });

          var ajaxData = { valores: JSON.stringify(valores) };
          var url2 = "get.php?rid=" + rid + "&o=sv&a=81";
          var respuesta = $.ajax({
            url: url2,
            type: "POST",
            data: ajaxData,
            async: false,
          }).responseText;

          if (respuesta.trim() == "ok") {
            showmessage(titulo, "Se guardo el registro", "");
            $(this).dialog("close");
          } else {
            showmessage(titulo, "Error al guardar registro", "ui-icon-alert");
            $(this).dialog("close");
          }
        },
      },
      {
        text: "Cerrar",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

function asignar_bodega(rid) {
  var titulo = "Asignacion de Almacenes";
  var url = "get.php?rid=" + rid + "&o=lb&a=82";

  var NewDialog = $('<div><p><img src="images/load.gif"/></p></div>');
  NewDialog.load(url).dialog({
    modal: true,
    title: titulo,
    width: "95%",
    height: 600,
    position: { my: "center", at: "center", of: window },
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "Guardar",
        click: function () {
          var valores = new Array();
          $("#box2View option").each(function (i) {
            valores.push($(this).val());
          });

          var ajaxData = { valores: JSON.stringify(valores) };
          var url2 = "get.php?rid=" + rid + "&o=sv&a=82";
          var respuesta = $.ajax({
            url: url2,
            type: "POST",
            data: ajaxData,
            async: false,
          }).responseText;

          if (respuesta.trim() == "ok") {
            showmessage(titulo, "Se guardo el registro", "");
            $(this).dialog("close");
          } else {
            showmessage(titulo, "Error al guardar registro", "ui-icon-alert");
            $(this).dialog("close");
          }
        },
      },
      {
        text: "Cerrar",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

function asignar_niveles(rid) {
  var titulo = "Asignacion de Permisos";
  var url = "get.php?rid=" + rid + "&o=lb&a=8";

  var NewDialog = $('<div><p><img src="images/load.gif"/></p></div>');
  NewDialog.load(url).dialog({
    modal: true,
    title: titulo,
    width: "95%",
    height: 600,
    position: { my: "center", at: "center", of: window },
    close: function (event, ui) {
      $(this).dialog("destroy").remove();
    },
    buttons: [
      {
        text: "Guardar",
        click: function () {
          var valores = new Array();
          $("#box2View option").each(function (i) {
            valores.push($(this).val());
          });

          var ajaxData = { valores: JSON.stringify(valores) };
          var url2 = "get.php?rid=" + rid + "&o=sv&a=8";
          var respuesta = $.ajax({
            url: url2,
            type: "POST",
            data: ajaxData,
            async: false,
          }).responseText;

          if (respuesta.trim() == "ok") {
            showmessage(titulo, "Se guardo el registro", "");
            $(this).dialog("close");
          } else {
            showmessage(titulo, "Error al guardar registro", "ui-icon-alert");
            $(this).dialog("close");
          }
        },
      },
      {
        text: "Cerrar",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });
}

// function calcularCuotaPromocionOctubre() {
//   console.log("Ejecutando promoción al inicio", { monto, tasaAnual, plazo });

//   const monto = parseFloat($("#monto_financiar").val()) || 0;
//   const tasaAnual = parseFloat($("#tasa").val()) || 0;
//   const plazo = parseInt($("#plazo").val()) || 0;

//   if (monto <= 0 || tasaAnual <= 0 || plazo <= 0) {
//     $("#cuota_promocion").val("");
//     return;
//   }

//   // Cuota normal (igual a showValues)
//   const interesMensual = (monto * (tasaAnual / 100)) / 12;
//   const cuotaNormal = interesMensual + monto / plazo;

//   // Interés adicional de 1 mes (distribuido)
//   const interesExtraDistribuido = interesMensual / plazo;
//   const cuotaPromocion = cuotaNormal + interesExtraDistribuido;

//   $("#cuota_promocion").val(cuotaPromocion.toFixed(2));
//   console.log("Ejecutando promoción al final", { monto, tasaAnual, plazo });
// }

function calcularCuotaPromocionOctubre() {
  const monto = parseFloat($("#monto_financiar").val()) || 0;
  const tasaAnual = parseFloat($("#tasa").val()) || 0;
  const plazo = parseInt($("#plazo").val()) || 0;

  if (monto <= 0 || tasaAnual <= 0 || plazo <= 0) {
    $("#cuota_promocion").val("");
    return;
  }

  const interesMensual = (monto * (tasaAnual / 100)) / 12;
  const cuotaNormal = interesMensual + monto / plazo;

  const interesExtraDistribuido = interesMensual / plazo;
  const cuotaPromocion = cuotaNormal + interesExtraDistribuido;

  $("#cuota_promocion").val(cuotaPromocion.toFixed(2));
  $("#cuota_promocion_octubre").val(cuotaPromocion.toFixed(2));
}

// 👇 hace la función accesible globalmente
window.calcularCuotaPromocionOctubre = calcularCuotaPromocionOctubre;
