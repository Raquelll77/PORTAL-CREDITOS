<?php

/**
 * Mapeo de parametros $_REQUEST[] concurrente mente usados en los archivos php, uso y significado
 */
class RequestParams
{
    const ACCION = "a";
    const CREDITO_ID = "cid";
    const ETAPA_CREDITO = "cst";
    const CAMPO_UNICO = "cpo";
    const GUARDAR = "s";
}

/**
 * Mapeo de etapas de credito y sus respectivos IDs
 * Revisar en dbo.PRESTAMO_ETAPA
 */
class TipoEtapas
{
    const ETAPA_NUEVA_SOLICITUD = 1;
    const ETAPA_VERIFICAR_DOCUMENTOS = 2;
    const ETAPA_VERIFICAR_BURO = 3;
    const ETAPA_VERIFICAR_TELEFONIA = 4;
    const ETAPA_VERIFICACION_LABORAL = 5;
    const ETAPA_VERIFICACION_CAMPO = 6;
    const ETAPA_CALCULO_FINANCIERO = 7;
    const ETAPA_CONDICION_APROBACION = 8;
    const ETAPA_IMPRESION_DOCUMENTOS = 9;
    const ETAPA_FIRMA_CONTRATOS = 10;
    const ETAPA_CIERRE = 11;
    const ETAPA_RECIBIR_DOCUMENTACION = 12;
}


/**
 * Referencias de valores dados en tabla dbo.USUARIO_NIVEL
 * Relacionar entre: dbo.usuario_nivelxgrupo.nivel_id = dbo.USUARIO_NIVEL.ID
 */
class PermisosModulos
{
    const PERMISOS_MODULO_CREDITOS = 15;
    const PERMISOS_TODAS_GESTIONES_SERVICIOS = 16;
    const PERMISOS_VENDEDOR = 18;
    const PERMISOS_PERSONAL_CREDITO = 19;
    /**
     * @var int indica que el usuario pertenece al nivel de acceso de el personal del depto SKG creditos.
     */
    const PERMISOS_JEFE_TIENDA = 22;
    const PERMISOS_GENERAR_CONTRATO = 23;
    const PERMISOS_IMPRIMIR_CONTRATO = 24;
    const PERMISOS_MARCA_AUTORIZADO_GERENCIA = 25;
    const PERMISOS_CANAL_INDIRECTO = 26;
}

class Estados
{
    const ESTADO_EN_PROCESO = 1;
    const ESTADO_APROBADO = 2;
    const ESTADO_DENEGADO = 3;
}



class DocumentosDatos
{
    const LISTA_PLAZOS = "plazos.json";
    const LISTA_PERFILES = "perfiles.json";
    const TIPOS_PRODUCTOS = "tiposProductos.json";
    const TIPOS_IDENTIFICACION = "tiposIdentificacion.json";
    const TIPOS_CLIENTES = "tiposClientes.json";
    const DEPARTAMENTOS = "departamentos.json";
    const TIPOS_TRABAJO = "tiposTrabajos.json";
    const TIPOS_SALARIOS = "tiposSalarios.json";
    const TIPOS_PERSONAS = "tiposPersonas.json";
    const TIPOS_ESCOLARIDAD = "tiposEscolaridad.json";
    const TIPOS_ESTADO_CIVIL = "tiposEstadoCivil.json";
    const TIPOS_VIVIENDA = "tiposVivienda.json";

    public static function obtener_datos($archivo)
    {
        $folder = "data";
        $datosArchivo = file_get_contents("./{$folder}/{$archivo}");
        return $datosArchivo == false ? [] : json_decode($datosArchivo, true);
    }
}
