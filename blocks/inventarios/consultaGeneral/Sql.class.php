<?php

namespace inventarios\consultaGeneral;

if (!isset($GLOBALS ["autorizado"])) {
    include ("../index.php");
    exit();
}

include_once ("core/manager/Configurador.class.php");
include_once ("core/connection/Sql.class.php");

// Para evitar redefiniciones de clases el nombre de la clase del archivo sqle debe corresponder al nombre del bloque
// en camel case precedida por la palabra sql
class Sql extends \Sql {

    var $miConfigurador;

    function __construct() {
        $this->miConfigurador = \Configurador::singleton();
    }

    function getCadenaSql($tipo, $variable = "") {

        /**
         * 1.
         * Revisar las variables para evitar SQL Injection
         */
        $prefijo = $this->miConfigurador->getVariableConfiguracion("prefijo");
        $idSesion = $this->miConfigurador->getVariableConfiguracion("id_sesion");

        switch ($tipo) {

            /**
             * Clausulas específicas
             */
            case "buscarUsuario" :
                $cadenaSql = "SELECT ";
                $cadenaSql .= "FECHA_CREACION, ";
                $cadenaSql .= "PRIMER_NOMBRE ";
                $cadenaSql .= "FROM ";
                $cadenaSql .= "USUARIOS ";
                $cadenaSql .= "WHERE ";
                $cadenaSql .= "`PRIMER_NOMBRE` ='" . $variable . "' ";
                break;

            case "insertarRegistro" :
                $cadenaSql = "INSERT INTO ";
                $cadenaSql .= $prefijo . "registradoConferencia ";
                $cadenaSql .= "( ";
                $cadenaSql .= "`idRegistrado`, ";
                $cadenaSql .= "`nombre`, ";
                $cadenaSql .= "`apellido`, ";
                $cadenaSql .= "`identificacion`, ";
                $cadenaSql .= "`codigo`, ";
                $cadenaSql .= "`correo`, ";
                $cadenaSql .= "`tipo`, ";
                $cadenaSql .= "`fecha` ";
                $cadenaSql .= ") ";
                $cadenaSql .= "VALUES ";
                $cadenaSql .= "( ";
                $cadenaSql .= "NULL, ";
                $cadenaSql .= "'" . $variable ['nombre'] . "', ";
                $cadenaSql .= "'" . $variable ['apellido'] . "', ";
                $cadenaSql .= "'" . $variable ['identificacion'] . "', ";
                $cadenaSql .= "'" . $variable ['codigo'] . "', ";
                $cadenaSql .= "'" . $variable ['correo'] . "', ";
                $cadenaSql .= "'0', ";
                $cadenaSql .= "'" . time() . "' ";
                $cadenaSql .= ")";
                break;

            case "actualizarRegistro" :
                $cadenaSql = "UPDATE ";
                $cadenaSql .= $prefijo . "conductor ";
                $cadenaSql .= "SET ";
                $cadenaSql .= "`nombre` = '" . $variable ["nombre"] . "', ";
                $cadenaSql .= "`apellido` = '" . $variable ["apellido"] . "', ";
                $cadenaSql .= "`identificacion` = '" . $variable ["identificacion"] . "', ";
                $cadenaSql .= "`telefono` = '" . $variable ["telefono"] . "' ";
                $cadenaSql .= "WHERE ";
                $cadenaSql .= "`idConductor` =" . $_REQUEST ["registro"] . " ";
                break;

            /**
             * Clausulas genéricas.
             * se espera que estén en todos los formularios
             * que utilicen esta plantilla
             */
            case "iniciarTransaccion" :
                $cadenaSql = "START TRANSACTION";
                break;

            case "finalizarTransaccion" :
                $cadenaSql = "COMMIT";
                break;

            case "cancelarTransaccion" :
                $cadenaSql = "ROLLBACK";
                break;

            case "eliminarTemp" :

                $cadenaSql = "DELETE ";
                $cadenaSql .= "FROM ";
                $cadenaSql .= $prefijo . "tempFormulario ";
                $cadenaSql .= "WHERE ";
                $cadenaSql .= "id_sesion = '" . $variable . "' ";
                break;

            case "insertarTemp" :
                $cadenaSql = "INSERT INTO ";
                $cadenaSql .= $prefijo . "tempFormulario ";
                $cadenaSql .= "( ";
                $cadenaSql .= "id_sesion, ";
                $cadenaSql .= "formulario, ";
                $cadenaSql .= "campo, ";
                $cadenaSql .= "valor, ";
                $cadenaSql .= "fecha ";
                $cadenaSql .= ") ";
                $cadenaSql .= "VALUES ";

                foreach ($_REQUEST as $clave => $valor) {
                    $cadenaSql .= "( ";
                    $cadenaSql .= "'" . $idSesion . "', ";
                    $cadenaSql .= "'" . $variable ['formulario'] . "', ";
                    $cadenaSql .= "'" . $clave . "', ";
                    $cadenaSql .= "'" . $valor . "', ";
                    $cadenaSql .= "'" . $variable ['fecha'] . "' ";
                    $cadenaSql .= "),";
                }

                $cadenaSql = substr($cadenaSql, 0, (strlen($cadenaSql) - 1));
                break;

            case "rescatarTemp" :
                $cadenaSql = "SELECT ";
                $cadenaSql .= "id_sesion, ";
                $cadenaSql .= "formulario, ";
                $cadenaSql .= "campo, ";
                $cadenaSql .= "valor, ";
                $cadenaSql .= "fecha ";
                $cadenaSql .= "FROM ";
                $cadenaSql .= $prefijo . "tempFormulario ";
                $cadenaSql .= "WHERE ";
                $cadenaSql .= "id_sesion='" . $idSesion . "'";
                break;

            /**
             * Clausulas Del Caso Uso.
             */
            case "rubros" :
                $cadenaSql = " SELECT RUB_IDENTIFICADOR, RUB_RUBRO ||' - '|| RUB_NOMBRE_RUBRO ";
                $cadenaSql .= " FROM RUBROS ";

                break;

            case "dependencia" :
                $cadenaSql = "SELECT DISTINCT  ESF_ID_ESPACIO, ESF_NOMBRE_ESPACIO ";
                $cadenaSql .= " FROM ESPACIOS_FISICOS ";
                $cadenaSql .= " WHERE  ESF_ESTADO='A'";
                break;

            case "consultarDependencia" :
                $cadenaSql = " SELECT   ESF_ID_ESPACIO, ESF_NOMBRE_ESPACIO ";
                $cadenaSql .= "FROM ESPACIOS_FISICOS  ";
                $cadenaSql .= " WHERE ESF_ID_SEDE='" . $variable . "' ";
                $cadenaSql .= " AND  ESF_ESTADO='A'";
                break;

            case "dependencias" :
                $cadenaSql = "SELECT DISTINCT  ESF_ID_ESPACIO, ESF_NOMBRE_ESPACIO ";
                $cadenaSql .= " FROM ESPACIOS_FISICOS ";
                $cadenaSql .= " WHERE ESF_ID_SEDE='" . $variable . "' ";
                $cadenaSql .= " AND  ESF_ESTADO='A'";
                break;

            case "dependencias_encargada" :
                $cadenaSql = 'SELECT DISTINCT "ESF_CODIGO_DEP", "ESF_DEP_ENCARGADA" ';
                $cadenaSql .= " FROM arka_parametros.arka_dependencia as dependencia ";
                $cadenaSql .= ' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=dependencia."ESF_ID_ESPACIO" ';
                $cadenaSql .= ' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE" ';
                $cadenaSql .= ' WHERE 1=1 ';
                $cadenaSql.= ' AND "ESF_ID_SEDE"=';
                $cadenaSql.= "'" . $variable . "'";
                $cadenaSql.= ' AND  dependencia."ESF_ESTADO"=';
                $cadenaSql.= "'A'";
                break;


            case "clase_entrada";
                $cadenaSql = " SELECT id_clase, descripcion ";
                $cadenaSql .= "FROM clase_entrada; ";
                break;

            case "sede" :
                $cadenaSql = "SELECT DISTINCT  ESF_ID_SEDE, ESF_SEDE ";
                $cadenaSql .= " FROM ESPACIOS_FISICOS ";
                $cadenaSql .= " WHERE   ESF_ESTADO='A'";

                break;

            case 'seleccion_contratista' :
                $cadenaSql = " SELECT id_contratista, ";
                $cadenaSql .= "  identificacion||' - '|| nombre_razon_social contratista ";
                $cadenaSql .= "FROM contratista_servicios;";
                break;

            case "funcionarios":
                $cadenaSql = " SELECT FUN_IDENTIFICACION, FUN_IDENTIFICACION ||' - '|| FUN_NOMBRE ";
                $cadenaSql .= "FROM FUNCIONARIOS ";
                break;

            case "tipoConsulta":
                $cadenaSql = " SELECT id_tipo_consulta, descripcion ";
                $cadenaSql .= " FROM tipo_consulta ";
                $cadenaSql .= " WHERE estado_consulta='TRUE' ";
                break;

            case "buscar_entradas":
                $cadenaSql = " SELECT id_entrada valor,consecutivo descripcion  ";
                $cadenaSql.= " FROM entrada; ";
                break;

            case "vigencia_entrada" :
                $cadenaSql = " SELECT DISTINCT vigencia, vigencia ";
                $cadenaSql.= " FROM entrada ";
                break;

            case "buscar_salidas":
                $cadenaSql = " SELECT id_salida valor,consecutivo descripcion  ";
                $cadenaSql.= " FROM salida; ";
                break;

            case "vigencia_salida" :
                $cadenaSql = " SELECT DISTINCT fecha, fecha ";
                $cadenaSql.= " FROM salida ";
                break;

            case "buscar_placa":
                $cadenaSql = " SELECT id_elemento_ind, placa FROM elemento_individual;";
                break;

            case "buscar_serie":
                $cadenaSql = " SELECT id_elemento_ind, serie FROM elemento_individual; ";
                break;

            case "buscar_bajas":
                $cadenaSql = " SELECT id_baja, id_elemento_ind ";
                $cadenaSql.= " FROM baja_elemento ";
                $cadenaSql.= " WHERE estado_registro='TRUE' ";
                break;

            case "buscar_traslado":
                $cadenaSql = " SELECT id_salida valor,id_salida descripcion  ";
                $cadenaSql.= " FROM salida; ";
                break;

            case "buscar_faltante":
                $cadenaSql = " SELECT id_elemento_ind, id_faltante ";
                $cadenaSql.= " FROM estado_elemento ";
                $cadenaSql.= " WHERE estado_registro='TRUE' ";
                $cadenaSql.= " AND tipo_faltsobr='3' ";
                $cadenaSql.= " AND id_faltante !='0' ";
                break;

            case "buscar_hurto":
                $cadenaSql = " SELECT id_elemento_ind,id_hurto ";
                $cadenaSql.= " FROM estado_elemento ";
                $cadenaSql.= " WHERE estado_registro='TRUE' ";
                $cadenaSql.= " AND tipo_faltsobr='2' ";
                $cadenaSql.= " AND id_hurto !='0' ";
                break;

            case "buscar_estadobaja":
                $cadenaSql = " SELECT id_estado, descripcion FROM estado_baja; ";
                break;

            case "proveedores" :
                $cadenaSql = " SELECT PRO_IDENTIFICADOR,PRO_NIT||' - '||PRO_RAZON_SOCIAL AS proveedor ";
                $cadenaSql .= " FROM PROVEEDORES ";
                break;


//--------------- Consultas Reportes Específicos -----------------//

            case "consultarEntrada" :
                $cadenaSql = "SELECT  ";
                $cadenaSql.= "consecutivo,  ";
                $cadenaSql.= "entrada.fecha_registro, ";
                $cadenaSql.= "clase_entrada.descripcion as clase_entrada, ";
                $cadenaSql.= "vigencia, ";
                $cadenaSql.= "tipo_contrato.descripcion as tipo_contrato,  ";
                $cadenaSql.= "numero_contrato,  ";
                $cadenaSql.= "fecha_contrato,  ";
                $cadenaSql.= "proveedor,  ";
                $cadenaSql.= ' "PRO_RAZON_SOCIAL" nombre_proveedor, ';
                $cadenaSql.= "numero_factura,  ";
                $cadenaSql.= "fecha_factura,  ";
                $cadenaSql.= "observaciones,  ";
                $cadenaSql.= "estado_entrada.descripcion as estado_entrada ";
                $cadenaSql.= "FROM entrada ";
                $cadenaSql.= "JOIN clase_entrada ON entrada.clase_entrada=clase_entrada.id_clase ";
                $cadenaSql.= "JOIN tipo_contrato ON tipo_contrato.id_tipo=entrada.tipo_contrato ";
                $cadenaSql.= "JOIN estado_entrada ON estado_entrada.id_estado=entrada.estado_entrada ";
                $cadenaSql.= 'JOIN arka_parametros.arka_proveedor ON arka_parametros.arka_proveedor."PRO_NIT"=cast(proveedor as character varying) ';
                $cadenaSql.= "WHERE estado_registro='TRUE' ";
                $cadenaSql.= "AND 1=1 ";
                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= " AND entrada.dependencia = '" . $variable ['dependencia'] . "'";
                }

                if ($variable ['numero_entrada'] != '') {
                    $cadenaSql .= " AND id_entrada = '" . $variable ['numero_entrada'] . "'";
                }

                if ($variable ['vigencia_entrada'] != '') {
                    $cadenaSql .= " AND vigencia = '" . $variable ['vigencia_entrada'] . "'";
                }

                if ($variable ['proveedor'] != '') {
                    $cadenaSql .= " AND proveedor = '" . $variable ['proveedor'] . "'";
                }

                if ($variable ['tipo_entrada'] != '') {
                    $cadenaSql .= " AND entrada.clase_entrada = '" . $variable ['tipo_entrada'] . "'";
                }

                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql .= " AND fecha_registro BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql .= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }
                break;

            case "consultarSalida" :
                $cadenaSql = ' SELECT salida.consecutivo num_salida, entrada.consecutivo num_entrada, salida.fecha_registro, ';
                $cadenaSql.= ' sedes."ESF_SEDE" sede, ';
                $cadenaSql.= ' dependencias."ESF_DEP_ENCARGADA" dependencia, ';
                $cadenaSql.= ' espacios."ESF_NOMBRE_ESPACIO" ubicacion, ';
                $cadenaSql .= ' salida.funcionario,arka_parametros.arka_funcionarios."FUN_NOMBRE" nombre_funcionario, salida.observaciones, count(id_elemento_ind) as numero_elementos, ';
                $cadenaSql .= " sum(total_iva_con) valor_salida ";
                $cadenaSql .= " FROM salida  ";
                $cadenaSql .= " JOIN elemento_individual ON elemento_individual.id_salida=salida.id_salida  ";
                $cadenaSql .= " JOIN elemento ON elemento_individual.id_elemento_gen=elemento.id_elemento ";
                $cadenaSql .= ' JOIN arka_parametros.arka_funcionarios ON arka_parametros.arka_funcionarios."FUN_IDENTIFICACION"=salida.funcionario ';
                $cadenaSql .= ' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=salida.ubicacion ';
                $cadenaSql .= ' JOIN arka_parametros.arka_dependencia as dependencias ON dependencias."ESF_CODIGO_DEP"=salida.dependencia  ';
                $cadenaSql .= " JOIN entrada ON elemento.id_entrada=entrada.id_entrada ";
                $cadenaSql .= ' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE"  ';

                $cadenaSql.= " WHERE 1=1 ";
                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= ' AND dependencias."ESF_CODIGO_DEP" = ';
                    $cadenaSql .= " '" . $variable ['dependencia'] . "' ";
                }

                if ($variable ['sede'] != '') {
                    $cadenaSql .= ' AND sedes."ESF_ID_SEDE" = ';
                    $cadenaSql .= " '" . $variable ['sede'] . "' ";
                }

                if ($variable ['funcionario'] != '') {
                    $cadenaSql .= " AND salida.funcionario = '" . $variable ['funcionario'] . "'";
                }

                if ($variable ['numero_entrada'] != '') {
                    $cadenaSql .= " AND entrada.id_entrada = '" . $variable ['numero_entrada'] . "'";
                }

                if ($variable ['vigencia_entrada'] != '') {
                    $cadenaSql .= " AND entrada.vigencia = '" . $variable ['vigencia_entrada'] . "'";
                }

                if ($variable ['numero_salida'] != '') {
                    $cadenaSql .= " AND salida.id_salida = '" . $variable ['numero_salida'] . "'";
                }

                if ($variable ['vigencia_salida'] != '') {
                    $cadenaSql .= " AND salida.fecha = '" . $variable ['vigencia_salida'] . "'";
                }

                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql .= " AND salida.fecha BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql .= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }
                $cadenaSql .= ' GROUP BY salida.id_salida, salida.fecha_registro, salida.dependencia, salida.sede, salida.funcionario,salida.observaciones, entrada.consecutivo,arka_parametros.arka_funcionarios."FUN_NOMBRE", sedes."ESF_SEDE", dependencias."ESF_DEP_ENCARGADA", espacios."ESF_NOMBRE_ESPACIO" ';

                break;

            case "consultarElementos":
                $cadenaSql = " SELECT  elemento_individual.placa, ";
                $cadenaSql.= ' sedes."ESF_SEDE" sede, ';
                $cadenaSql.= ' dependencias."ESF_DEP_ENCARGADA" dependencia, ';
                $cadenaSql.= ' espacios."ESF_NOMBRE_ESPACIO" ubicacion, ';
//$cadenaSql.= "  tipo_bienes.descripcion, ";
                $cadenaSql.= " elemento_nombre, elemento.descripcion, marca, elemento.serie, cantidad, valor, iva, ajuste, total_iva_con, bodega  ";
                $cadenaSql.= " FROM elemento  ";
                $cadenaSql.= " JOIN entrada ON elemento.id_entrada=entrada.id_entrada  ";
                $cadenaSql.= " JOIN catalogo.catalogo_elemento ON catalogo.catalogo_elemento.elemento_id=nivel  ";
//$cadenaSql.= " JOIN tipo_bienes ON tipo_bienes.id_tipo_bienes=tipo_bien  ";
                $cadenaSql.= " JOIN elemento_individual ON elemento_individual.id_elemento_gen=elemento.id_elemento  ";
                $cadenaSql.= ' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.= ' JOIN arka_parametros.arka_dependencia as dependencias ON dependencias."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.= ' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE" ';

                $cadenaSql.= " JOIN salida ON elemento_individual.id_salida=salida.id_salida WHERE elemento.estado='1' ";

                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= ' AND dependencias."ESF_CODIGO_DEP" = ';
                    $cadenaSql .= " '" . $variable ['dependencia'] . "' ";
                }

                if ($variable ['funcionario'] != '') {
                    $cadenaSql .= " AND elemento_individual.funcionario = '" . $variable ['funcionario'] . "'";
                }

                if ($variable ['numero_entrada'] != '') {
                    $cadenaSql .= " AND entrada.id_entrada = '" . $variable ['numero_entrada'] . "'";
                }

                if ($variable ['vigencia_entrada'] != '') {
                    $cadenaSql .= " AND entrada.vigencia = '" . $variable ['vigencia_entrada'] . "'";
                }

                if ($variable ['numero_salida'] != '') {
                    $cadenaSql .= " AND salida.id_salida = '" . $variable ['numero_salida'] . "'";
                }

                if ($variable ['numero_placa'] != '') {
                    $cadenaSql .= " AND elemento.placa = '" . $variable ['numero_placa'] . "'";
                }

                if ($variable ['numero_serie'] != '') {
                    $cadenaSql .= " AND elemento.serie = '" . $variable ['numero_serie'] . "'";
                }

                if ($variable ['vigencia_salida'] != '') {
                    $cadenaSql .= " AND salida.fecha = '" . $variable ['vigencia_salida'] . "'";
                }

                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql .= " AND elemento.fecha_registro BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql .= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }
                break;

            case "consultarTraslados":
                $cadenaSql = " SELECT DISTINCT historial_elemento_individual.fecha_registro, ";
                $cadenaSql .= "placa, ";
                $cadenaSql .= ' sedes."ESF_SEDE" sede, ';
                $cadenaSql .= ' dependencias."ESF_DEP_ENCARGADA" dependencia, ';
                $cadenaSql .= ' espacios."ESF_NOMBRE_ESPACIO" ubicacion, ';
                $cadenaSql .= ' pasado."FUN_NOMBRE" as funcionario_anterior, ';
                $cadenaSql .= ' actual."FUN_NOMBRE" as funcionario_siguiente, ';
                $cadenaSql .= " marca,  ";
                $cadenaSql .= " elemento_individual.serie, ";
                $cadenaSql .= " historial_elemento_individual.observaciones observaciones_traslados, ";
                $cadenaSql .= " elemento_nombre as nombre_nivel, ";
                $cadenaSql .= " salida.vigencia vigenciasalida, ";
                $cadenaSql .= " elemento.descripcion as descripcion_elemento ";
                $cadenaSql .= " FROM elemento_individual ";
                $cadenaSql .= " JOIN elemento ON id_elemento_gen = elemento.id_elemento ";
                $cadenaSql .= " JOIN historial_elemento_individual ON historial_elemento_individual.elemento_individual = elemento_individual.id_elemento_ind  ";
                $cadenaSql .= " JOIN salida ON salida.id_salida = elemento_individual.id_salida ";
                $cadenaSql .= " JOIN catalogo.catalogo_elemento ON catalogo.catalogo_elemento.elemento_id=nivel ";
                $cadenaSql .= ' JOIN arka_parametros.arka_funcionarios actual ON actual."FUN_IDENTIFICACION"=elemento_individual.funcionario ';
                $cadenaSql .= ' JOIN arka_parametros.arka_funcionarios pasado ON cast(pasado."FUN_IDENTIFICACION" as character varying)=historial_elemento_individual.descripcion_funcionario ';
                $cadenaSql .= ' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql .= ' JOIN arka_parametros.arka_dependencia as dependencias ON dependencias."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql .= ' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE" ';
                $cadenaSql .= "JOIN catalogo.catalogo_lista ON catalogo.catalogo_elemento.elemento_catalogo=catalogo.catalogo_lista.lista_id ";

                $cadenaSql.= " WHERE elemento_individual.id_salida=salida.id_salida ";
                if ($variable ['IDtraslado'] != '') {
                    $cadenaSql.= " AND historial_elemento_individual.id_evento = '" . $variable ['IDtraslado'] . "'";
                }

                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= ' AND dependencias."ESF_CODIGO_DEP" = ';
                    $cadenaSql .= " '" . $variable ['dependencia'] . "' ";
                }

                if ($variable ['funcionario'] != '') {
                    $cadenaSql .= " AND historial_elemento_individual.funcionario = '" . $variable ['funcionario'] . "'";
                }

                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql.= " AND historial_elemento_individual.fecha_registro BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql.= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }
                $cadenaSql .= " ORDER BY historial_elemento_individual.fecha_registro ASC";
                break;

            case "consultarSobranteFaltante":
                $cadenaSql = " SELECT  placa, ";
                $cadenaSql.=' sedes."ESF_SEDE" sede, ';
                $cadenaSql.=' dependencias."ESF_DEP_ENCARGADA" dependencia, ';
                $cadenaSql.=' espacios."ESF_NOMBRE_ESPACIO" ubicacion, ';
                $cadenaSql.=" tipo_falt_sobr.descripcion, elemento_individual.serie, ";
                $cadenaSql.=" elemento_individual.funcionario,  ";
                $cadenaSql.=" nombre_denuncia, ";
                $cadenaSql.=" fecha_denuncia, fecha_hurto, estado_elemento.fecha_registro, estado_elemento.observaciones ";
                $cadenaSql.=" FROM estado_elemento ";
                $cadenaSql.=" JOIN elemento_individual ON elemento_individual.id_elemento_ind = estado_elemento.id_elemento_ind ";
                $cadenaSql.=" JOIN tipo_falt_sobr ON tipo_falt_sobr.id_tipo_falt_sobr = estado_elemento.tipo_faltsobr ";
                $cadenaSql.=" JOIN elemento ON elemento.id_elemento = elemento_individual.id_elemento_gen ";
                $cadenaSql.=" JOIN salida ON salida.id_entrada = elemento.id_entrada ";
                $cadenaSql.=' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.=' JOIN arka_parametros.arka_dependencia as dependencias ON dependencias."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.=' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE" ';
                $cadenaSql.=" WHERE estado_elemento.estado_registro = 't' ";
                if ($variable ['IDfaltante'] != '') {
                    $cadenaSql.= " AND historial_elemento_individual.id_evento = '" . $variable ['IDfaltante'] . "'";
                }

                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= " AND salida.dependencia = '" . $variable ['dependencia'] . "'";
                }

                if ($variable ['funcionario'] != '') {
                    $cadenaSql .= " AND salida.funcionario = '" . $variable ['funcionario'] . "'";
                }


                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql.= " AND estado_elemento.fecha_registro BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql.= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }

                $cadenaSql.=" ORDER BY tipo_faltsobr ";

                break;

            case "consultarBajas":
                $cadenaSql = " SELECT id_baja,tramite, baja_elemento.id_elemento_ind,  ";
                $cadenaSql.=' sedes."ESF_SEDE" sede, ';
                $cadenaSql.=' dependencias."ESF_DEP_ENCARGADA" dependencia, ';
                $cadenaSql.=' espacios."ESF_NOMBRE_ESPACIO" ubicacion, ';
                $cadenaSql.=" tipo_mueble.descripcion as tipo_mueble,baja_elemento.fecha_registro as fecha_registro, ";
                $cadenaSql.=" observaciones, dependencia_funcionario, ";
                $cadenaSql.=" estado_baja.descripcion as descripcion";
                $cadenaSql.=" FROM baja_elemento ";
                $cadenaSql.=" JOIN estado_baja ON estado_funcional=id_estado ";
                $cadenaSql.=" JOIN elemento_individual ON elemento_individual.id_elemento_ind=baja_elemento.id_elemento_ind ";
                $cadenaSql.=' JOIN arka_parametros.arka_espaciosfisicos as espacios ON espacios."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.=' JOIN arka_parametros.arka_dependencia as dependencias ON dependencias."ESF_ID_ESPACIO"=elemento_individual.ubicacion_elemento ';
                $cadenaSql.=' JOIN arka_parametros.arka_sedes as sedes ON sedes."ESF_COD_SEDE"=espacios."ESF_COD_SEDE" ';
                $cadenaSql.=" JOIN tipo_mueble ON tipo_mueble=id_tipo_mueble ";
                $cadenaSql.=" WHERE baja_elemento.estado_registro='t' ";
                if ($variable ['IDbaja'] != '') {
                    $cadenaSql.= " AND baja_elemento.id_baja = '" . $variable ['IDbaja'] . "'";
                }
                if ($variable['fecha_inicio'] != '' && $variable ['fecha_final'] != '') {
                    $cadenaSql.= " AND baja_elemento.fecha_registro BETWEEN CAST ( '" . $variable ['fecha_inicio'] . "' AS DATE) ";
                    $cadenaSql.= " AND  CAST ( '" . $variable ['fecha_final'] . "' AS DATE)  ";
                }
                break;

            case "consultarInventario" :
                $cadenaSql = "SELECT ";
                $cadenaSql .= " salida.consecutivo salida_consecutivo, ";
                $cadenaSql .= " entrada.consecutivo entrada_consecutivo, ";
                $cadenaSql .= " elemento_individual.placa, ";
                $cadenaSql .= " elemento.descripcion elemento_descripcion, ";
                $cadenaSql .= " elemento_individual.fecha_registro,  ";
                $cadenaSql .= ' arka_parametros.arka_espaciosfisicos."ESF_NOMBRE_ESPACIO" dependencia, ';
                $cadenaSql .= ' arka_parametros.arka_funcionarios."FUN_NOMBRE" nombre_funcionario, ';
                $cadenaSql .= " elemento_individual.serie,  ";
                $cadenaSql .= " tipo_falt_sobr.descripcion as descripcion ";
                $cadenaSql .= " FROM elemento_individual  ";
                $cadenaSql .= " JOIN salida ON salida.id_salida=elemento_individual.id_salida ";
                $cadenaSql .= " JOIN elemento ON elemento_individual.id_elemento_gen=elemento.id_elemento  ";
                $cadenaSql .= " JOIN entrada ON elemento.id_entrada=entrada.id_entrada  ";
                $cadenaSql .= " JOIN estado_elemento ON estado_elemento.id_estado_elemento=elemento_individual.estado_elemento ";
                $cadenaSql .= " JOIN tipo_falt_sobr ON tipo_falt_sobr.id_tipo_falt_sobr=estado_elemento.tipo_faltsobr  ";
                $cadenaSql .= ' JOIN arka_parametros.arka_funcionarios ON arka_parametros.arka_funcionarios."FUN_IDENTIFICACION"=salida.funcionario ';
                $cadenaSql .= ' JOIN arka_parametros.arka_espaciosfisicos ON arka_parametros.arka_espaciosfisicos."ESF_ID_ESPACIO" = salida.dependencia ';
                $cadenaSql .= " WHERE elemento_individual.estado_registro = 't' ";
                $cadenaSql .= " UNION ";
                $cadenaSql .= " SELECT ";
                $cadenaSql .= " salida.consecutivo salida_consecutivo, ";
                $cadenaSql .= " entrada.consecutivo entrada_consecutivo, ";
                $cadenaSql .= " elemento_individual.placa, ";
                $cadenaSql .= " elemento.descripcion elemento_descripcion, ";
                $cadenaSql .= " elemento_individual.fecha_registro, ";
                $cadenaSql .= ' arka_parametros.arka_espaciosfisicos."ESF_NOMBRE_ESPACIO" dependencia, ';
                $cadenaSql .= ' arka_parametros.arka_funcionarios."FUN_NOMBRE" nombre_funcionario, ';
                $cadenaSql .= " elemento_individual.serie, 'Activo' as descripcion ";
                $cadenaSql .= " FROM elemento_individual ";
                $cadenaSql .= " JOIN salida ON salida.id_salida = elemento_individual.id_salida ";
                $cadenaSql .= " JOIN elemento ON elemento_individual.id_elemento_gen = elemento.id_elemento ";
                $cadenaSql .= " JOIN entrada ON elemento.id_entrada = entrada.id_entrada ";
                $cadenaSql .= ' JOIN arka_parametros.arka_funcionarios ON arka_parametros.arka_funcionarios."FUN_IDENTIFICACION" = salida.funcionario ';
                $cadenaSql .= ' JOIN arka_parametros.arka_espaciosfisicos ON arka_parametros.arka_espaciosfisicos."ESF_ID_ESPACIO" = salida.dependencia ';
                $cadenaSql .= " WHERE elemento_individual.id_elemento_ind NOT IN( SELECT elemento_individual.id_elemento_ind ";
                $cadenaSql .= " FROM elemento_individual JOIN estado_elemento ON estado_elemento.id_estado_elemento = elemento_individual.estado_elemento ";
                $cadenaSql .= " JOIN tipo_falt_sobr ON tipo_falt_sobr.id_tipo_falt_sobr = estado_elemento.tipo_faltsobr ) ";
                if ($variable ['dependencia'] != '') {
                    $cadenaSql .= " AND salida.dependencia = '" . $variable ['dependencia'] . "'";
                }

                if ($variable ['funcionario'] != '') {
                    $cadenaSql .= " AND salida.funcionario = '" . $variable ['funcionario'] . "'";
                }
                break;
        }
        return $cadenaSql;
    }

}

?>
