<?php

require_once "modules/inspecciontareaenejecucion/model.php";
require_once "modules/inspecciontareaenejecucion/view.php";
require_once "modules/inspeccionresultado/model.php";
require_once "modules/inspecciontipo/model.php";
require_once "modules/inspector/model.php";
require_once "modules/legajopersonal/model.php";
require_once "modules/vehiculo/model.php";
require_once "modules/contratista/model.php";
require_once "modules/resultadorespuesta/model.php";
require_once "tools/inspecciones/resultadoinspeccion_helpers.php";
require_once "modules/inspecciontareaenejecucion/model.php";

class InspeccionTareaEnEjecucionController {

    function __construct() {
        $this->model = new InspeccionTareaEnEjecucion();
        $this->view = new InspeccionTareaEnEjecucionView();
        //$this->size = 1048576;
    }

    function panel() {
        SessionHandler()->check_session();
        $periodo_actual = date('Ym');

        $select_inspecciones = "date_format(ir.fecha, '%d/%m/%Y') AS FECHA, it.denominacion AS INSPECCIONTIPO, ite.tarea AS TAREA, ite.lugar AS LUGAR,
								ite.numero_tarea AS NT, ite.numero_remito AS NR, ir.inspeccionresultado_id AS IRID, ir.fecha AS ORDFEC,
								CASE ir.estado_inspeccion WHEN 0 THEN 'PENDIENTE' WHEN 1 THEN 'CERRADA' END AS ESTADO,
								CASE ir.estado_inspeccion WHEN 0 THEN 'inline-block' WHEN 1 THEN 'none' END AS BTN_AGREGAR,
								CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s')) AS REGISTRO";
        $from_inspecciones = "inspeccionresultado ir INNER JOIN inspecciontipo it ON ir.inspecciontipo = it.inspecciontipo_id
						      INNER JOIN inspecciontareaenejecucion ite ON ir.inspeccionresultado_id = ite.inspeccionresultado";
        $groupby_inspecciones = "CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s')), ite.numero_tarea";
        $inspeccion_collection = CollectorCondition()->get('InspeccionResultado', NULL, 4, $from_inspecciones, $select_inspecciones, $groupby_inspecciones);

        $select_inspecciones_legajo = "date_format(ir.fecha, '%d/%m/%Y') AS FECHA, it.denominacion AS INSPECCIONTIPO, c.denominacion AS CONTRATISTA,  ir.puntaje AS PUJE,
									   CONCAT(lp.apellido, ' ', lp.nombre) AS LEGAJOPERSONAL, ite.tarea AS TAREA, ite.lugar AS LUGAR, ir.inspeccionresultado_id AS IRID,
									   CASE ir.estado_inspeccion WHEN 0 THEN 'PENDIENTE' WHEN 1 THEN 'CERRADA' END AS ESTADO, ir.fecha AS ORDFEC";
        $from_inspecciones_legajo = "legajopersonal lp INNER JOIN inspeccionresultadolegajopersonal irlp ON lp.legajopersonal_id = irlp.compuesto INNER JOIN
									 inspeccionresultado ir ON irlp.compositor = ir.inspeccionresultado_id INNER JOIN
									 inspecciontipo it ON ir.inspecciontipo = it.inspecciontipo_id INNER JOIN
									 contratista c ON lp.contratista = c.contratista_id
									 INNER JOIN inspecciontareaenejecucion ite ON ir.inspeccionresultado_id = ite.inspeccionresultado";
        $inspeccionlegajo_collection = CollectorCondition()->get('InspeccionResultado', NULL, 4, $from_inspecciones_legajo, $select_inspecciones_legajo);

        $select_inspecciones_vehiculo = "date_format(ir.fecha, '%d/%m/%Y') AS FECHA, it.denominacion AS INSPECCIONTIPO, c.denominacion AS CONTRATISTA, ir.fecha AS ORDFEC,
										 CONCAT(v.dominio, ' - ', vma.denominacion, ' ', vmo.denominacion) AS VEHICULO, ite.tarea AS TAREA, ite.lugar AS LUGAR,
										 ir.inspeccionresultado_id AS IRID, CASE ir.estado_inspeccion WHEN 0 THEN 'PENDIENTE' WHEN 1 THEN 'CERRADA' END AS ESTADO ";
        $from_inspecciones_vehiculo = "vehiculo v INNER JOIN vehiculomodelo vmo ON v.vehiculomodelo = vmo.vehiculomodelo_id INNER JOIN
									   vehiculomarca vma ON vmo.vehiculomarca = vma.vehiculomarca_id INNER JOIN inspeccionresultadovehiculo irv ON v.vehiculo_id = irv.compuesto INNER JOIN
									   inspeccionresultado ir ON irv.compositor = ir.inspeccionresultado_id INNER JOIN
									   inspecciontipo it ON ir.inspecciontipo = it.inspecciontipo_id INNER JOIN contratista c ON v.contratista = c.contratista_id
										 INNER JOIN inspecciontareaenejecucion ite ON ir.inspeccionresultado_id = ite.inspeccionresultado";
        $inspeccionvehiculo_collection = CollectorCondition()->get('InspeccionResultado', NULL, 4, $from_inspecciones_vehiculo, $select_inspecciones_vehiculo);

        $this->view->panel($inspeccion_collection, $inspeccionlegajo_collection, $inspeccionvehiculo_collection);
    }

    function ingresar($arg) {
        SessionHandler()->check_session();
        $select = "CONCAT(lp.num_legajo, ' - ', lp.apellido, ' ', lp.nombre) AS LEGAJO, c.denominacion AS CONTRATISTA,
				   lp.legajopersonal_id AS LPID, lp.cuil AS CUIT";
        $from = "legajopersonal lp INNER JOIN contratista c ON lp.contratista = c.contratista_id ORDER BY lp.apellido ASC,
				 lp.nombre ASC";
        $legajopersonal_collection = CollectorCondition()->get('LegajoPersonal', NULL, 4, $from, $select);
        $inspecciontipo_collection = Collector()->get('InspeccionTipo');
        $inspector_collection = Collector()->get('Inspector');
        $this->view->ingresar($legajopersonal_collection, $inspecciontipo_collection, $inspector_collection);
    }

    function guardar_cabecera() {
        SessionHandler()->check_session();

        $irm = new InspeccionResultado();
        $irm->fecha = filter_input(INPUT_POST, 'fecha');
        $irm->hora = filter_input(INPUT_POST, 'hora');
        $irm->latitud = filter_input(INPUT_POST, 'latitud');
        $irm->longitud = filter_input(INPUT_POST, 'longitud');
        $irm->altitud = filter_input(INPUT_POST, 'altitud');
        $irm->estado_inspeccion = filter_input(INPUT_POST, 'estado_inspeccion');
        $irm->observacion = filter_input(INPUT_POST, 'observacion');
        $irm->inspecciontipo = filter_input(INPUT_POST, 'inspecciontipo');
        $irm->inspector = filter_input(INPUT_POST, 'inspector');
        $irm->legajopersonal = filter_input(INPUT_POST, 'legajopersonal');
        $irm->save();
        $inspeccionresultado_id = $irm->inspeccionresultado_id;

        /* GUARDAMOS EN INSPECCIONTAREAENEJECUCION */
        $this->model->tarea = filter_input(INPUT_POST, 'tarea');
        $this->model->numero_tarea = filter_input(INPUT_POST, 'numero_tarea');
        $this->model->numero_remito = filter_input(INPUT_POST, 'numero_remito');
        $this->model->lugar = filter_input(INPUT_POST, 'lugar');
        $this->model->unidad_car = filter_input(INPUT_POST, 'unidad_car');
        $this->model->inspeccionresultado = $inspeccionresultado_id;
        $this->model->save();

        header("Location: " . URL_APP . "/inspecciontareaenejecucion/seleccionar_integrante/{$inspeccionresultado_id}");
    }

    function seleccionar_integrante($arg) {
        SessionHandler()->check_session();
        $_SESSION['resultado-inspeccion'] = array();

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $itm = new InspeccionTipo();
        $itm->inspecciontipo_id = $irm->inspecciontipo->inspecciontipo_id;
        $itm->get();

        $select = "inspecciontareaenejecucion_id,tarea AS TAREA,numero_tarea AS NUMERO_DET,numero_remito AS NUMERO_REMITO,
				   lugar AS LUGAR,unidad_car AS UNIDAD_CAR,inspeccionresultado AS INSPECCIONRESULTADO";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);
        $this->view->seleccionar_integrante($irm, $itm, $inspecciontareaenejecucion_collection);
    }

    function p1_legajopersonal() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $select = "CONCAT(lp.apellido, ' ', lp.nombre, ' <strong>(LEG: ', lp.num_legajo, ')</strong>') AS LEGAJO, c.denominacion AS CONTRATISTA,
				   lp.legajopersonal_id AS LPID, lp.cuil AS CUIT";
        $from = "legajopersonal lp INNER JOIN contratista c ON lp.contratista = c.contratista_id ORDER BY lp.apellido ASC, lp.nombre ASC";
        $legajopersonal_collection = CollectorCondition()->get('LegajoPersonal', NULL, 4, $from, $select);
        $this->view->p1_legajopersonal($legajopersonal_collection);
    }

    function guardar_inspeccion_personal() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $legajopersonal_id = filter_input(INPUT_POST, 'legajopersonal');
        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajopersonal_id;
        $lpm->getInspecciones();

        $inspeccionresultado_id = filter_input(INPUT_POST, 'inspeccionresultado_id');
        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $respuestacuestionario_collection = $_POST['respuestacuestionario'];
        foreach ($respuestacuestionario_collection as $clave => $valor) {

            $rrm = new ResultadoRespuesta();
            if (!empty($valor['respuesta'])) {
                $respuesta = explode("@", $valor['respuesta']);
                $rrm->respuesta = $respuesta[0];
                $rrm->puntaje = $respuesta[1];
            }
            $rrm->itemcuestionario_id = $clave;
            $rrm->save();
            $resultadorespuesta_id = $rrm->resultadorespuesta_id;

            $rrm = new ResultadoRespuesta();
            $rrm->resultadorespuesta_id = $resultadorespuesta_id;
            $rrm->get();
            $irm->add_resultadorespuesta($rrm);
        }

        $rrirm = new ResultadoRespuestaInspeccionResultado($irm);
        $rrirm->save();

        $irm = new InspeccionResultado;
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $lpm->add_inspeccionresultado($irm);
        $irlpm = new InspeccionResultadoLegajoPersonal($lpm);
        $irlpm->save();

        $puntaje = 0;
        foreach ($irm->resultadorespuesta_collection as $rraux) {
            $puntaje = $puntaje + $rraux->puntaje;
        }
        $irm->puntaje = $puntaje;
        $irm->save();
        header("Location: " . URL_APP . "/inspecciontareaenejecucion/agregar_integrante/{$inspeccionresultado_id}");
    }

    function p2_legajopersonal($arg) {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $ids = explode('@', $arg);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $ids[0];
        $irm->get();

        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $ids[1];
        $lpm->get();
        $this->view->p2_legajopersonal($irm, $lpm);
    }

    function p1_vehiculo() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $select = "v.vehiculo_ID AS VID, CONCAT('<strong>', v.dominio, '</strong>') AS DOMINIO,
				   CONCAT(vma.denominacion, ' ', vmo.denominacion) AS VEHICULO";
        $from = "vehiculo v INNER JOIN vehiculomodelo vmo ON v.vehiculomodelo = vmo.vehiculomodelo_id INNER JOIN
				 vehiculomarca vma ON vmo.vehiculomarca = vma.vehiculomarca_id ORDER BY CONCAT(vma.denominacion, ' ', vmo.denominacion) ASC";
        $vehiculo_collection = CollectorCondition()->get('Vehiculo', NULL, 4, $from, $select);
        $this->view->p1_vehiculo($vehiculo_collection);
    }

    function p2_vehiculo($arg) {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $ids = explode('@', $arg);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $ids[0];
        $irm->get();

        $vm = new Vehiculo();
        $vm->vehiculo_id = $ids[1];
        $vm->get();
        $this->view->p2_vehiculo($irm, $vm);
    }

    function guardar_inspeccion_vehicular() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $inspeccionresultado_id = filter_input(INPUT_POST, 'inspeccionresultado_id');
        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $irm->observacion = filter_input(INPUT_POST, 'observacion');
        $irm->save();

        $vehiculo_id = filter_input(INPUT_POST, 'vehiculo');
        $vm = new Vehiculo();
        $vm->vehiculo_id = $vehiculo_id;
        $vm->getInspecciones();

        $vm->add_inspeccionresultado($irm);
        $irvm = new InspeccionResultadoVehiculo($vm);
        $irvm->save();

        header("Location: " . URL_APP . "/inspecciontareaenejecucion/agregar_integrante/{$inspeccionresultado_id}");
    }

    function agregar_integrante($arg) {
        SessionHandler()->check_session();
        $select_legajo_id = "irlp.compuesto";
        $from_legajo_id = "inspeccionresultadolegajopersonal irlp";
        $where_legajo_id = "irlp.compositor = {$arg}";
        $legajo_id = CollectorCondition()->get('LegajoPersonal', $where_legajo_id, 4, $from_legajo_id, $select_legajo_id);
        $legajo_id = (is_array($legajo_id)) ? $legajo_id[0]['compuesto'] : 0;

        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajo_id;
        $lpm->get();
        unset($lpm->inspeccionresultado_collection);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $select_inspecciontareaenejecucion = "inspecciontareaenejecucion_id,tarea AS TAREA,numero_tarea AS NUMERO_DET,numero_remito AS NUMERO_REMITO,lugar AS LUGAR,unidad_car AS UNIDAD_CAR,inspeccionresultado AS INSPECCIONRESULTADO";
        $from_inspecciontareaenejecucion = "inspecciontareaenejecucion";
        $where_inspecciontareaenejecucion = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where_inspecciontareaenejecucion, 4, $from_inspecciontareaenejecucion, $select_inspecciontareaenejecucion);

        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($resultadorespuesta_collection as $clave => $valor) {
            $icm = new ItemCuestionario();
            $icm->itemcuestionario_id = $valor->itemcuestionario_id;
            $icm->get();
            $temp_denominacion = $icm->denominacion;
            $valor->item_denominacion = $temp_denominacion;
            $valor->orden = $icm->orden;
        }

        $this->view->agregar_integrante($irm, $inspecciontareaenejecucion_collection);
    }

    function p2_legajopersonal_agregar($arg) {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $ids = explode('@', $arg);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $ids[0];
        $irm->get();

        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $ids[1];
        $lpm->get();
        $this->view->p2_legajopersonal_agregar($irm, $lpm);
    }

    function p2_vehiculo_agregar($arg) {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $ids = explode('@', $arg);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $ids[0];
        $irm->get();

        $vm = new Vehiculo();
        $vm->vehiculo_id = $ids[1];
        $vm->get();
        $this->view->p2_vehiculo_agregar($irm, $vm);
    }

    function agregar_inspeccion_personal() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        /* GUARDA CABECERA */
        $irm = new InspeccionResultado();
        $irm->fecha = filter_input(INPUT_POST, 'fecha');
        $irm->hora = filter_input(INPUT_POST, 'hora');
        $irm->latitud = filter_input(INPUT_POST, 'latitud');
        $irm->longitud = filter_input(INPUT_POST, 'longitud');
        $irm->altitud = filter_input(INPUT_POST, 'altitud');
        $irm->estado_inspeccion = 0;
        $irm->observacion = filter_input(INPUT_POST, 'observacion');
        $irm->inspecciontipo = filter_input(INPUT_POST, 'inspecciontipo');
        $irm->inspector = filter_input(INPUT_POST, 'inspector');
        $irm->legajopersonal = filter_input(INPUT_POST, 'responsable');
        $irm->save();
        $inspeccionresultado_id = $irm->inspeccionresultado_id;

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        /* GUARDAMOS EN INSPECCIONTAREAENEJECUCION */
        $this->model->tarea = filter_input(INPUT_POST, 'tarea');
        $this->model->numero_tarea = filter_input(INPUT_POST, 'numero_tarea');
        $this->model->numero_remito = filter_input(INPUT_POST, 'numero_remito');
        $this->model->lugar = filter_input(INPUT_POST, 'lugar');
        $this->model->unidad_car = filter_input(INPUT_POST, 'unidad_car');
        $this->model->inspeccionresultado = $inspeccionresultado_id;
        $this->model->save();

        /* GUARDA CHECKS */
        $legajopersonal_id = filter_input(INPUT_POST, 'legajopersonal');
        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajopersonal_id;
        $lpm->get();

        $respuestacuestionario_collection = $_POST['respuestacuestionario'];
        foreach ($respuestacuestionario_collection as $clave => $valor) {

            $rrm = new ResultadoRespuesta();
            if (!empty($valor['respuesta'])) {
                $respuesta = explode("@", $valor['respuesta']);
                $rrm->respuesta = $respuesta[0];
                $rrm->puntaje = $respuesta[1];
            }
            $rrm->observacion = $valor['observacion'];
            $rrm->itemcuestionario_id = $clave;
            $rrm->save();
            $resultadorespuesta_id = $rrm->resultadorespuesta_id;

            $rrm = new ResultadoRespuesta();
            $rrm->resultadorespuesta_id = $resultadorespuesta_id;
            $rrm->get();
            $irm->add_resultadorespuesta($rrm);
        }

        $rrirm = new ResultadoRespuestaInspeccionResultado($irm);
        $rrirm->save();

        $irm = new InspeccionResultado;
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();
        $lpm->add_inspeccionresultado($irm);

        $irlpm = new InspeccionResultadoLegajoPersonal($lpm);
        $irlpm->save();

        $puntaje = 0;
        foreach ($irm->resultadorespuesta_collection as $rraux) {
            $puntaje = $puntaje + $rraux->puntaje;
        }
        $irm->puntaje = $puntaje;
        $irm->save();
        header("Location: " . URL_APP . "/inspecciontareaenejecucion/agregar_integrante/{$inspeccionresultado_id}");
    }

    function agregar_inspeccion_vehicular() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        /* GUARDA CABECERA */
        $irm = new InspeccionResultado();
        $irm->fecha = filter_input(INPUT_POST, 'fecha');
        $irm->hora = filter_input(INPUT_POST, 'hora');
        $irm->latitud = filter_input(INPUT_POST, 'latitud');
        $irm->longitud = filter_input(INPUT_POST, 'longitud');
        $irm->altitud = filter_input(INPUT_POST, 'altitud');
        $irm->estado_inspeccion = 0;
        $irm->observacion = filter_input(INPUT_POST, 'observacion');
        $irm->inspecciontipo = filter_input(INPUT_POST, 'inspecciontipo');
        $irm->inspector = filter_input(INPUT_POST, 'inspector');
        $irm->legajopersonal = filter_input(INPUT_POST, 'responsable');
        $irm->save();
        $irm->get();
        $inspeccionresultado = $irm->inspeccionresultado_id;

        /* GUARDAMOS EN INSPECCIONTAREAENEJECUCION */
        $this->model->tarea = filter_input(INPUT_POST, 'tarea');
        $this->model->numero_tarea = filter_input(INPUT_POST, 'numero_tarea');
        $this->model->numero_remito = filter_input(INPUT_POST, 'numero_remito');
        $this->model->lugar = filter_input(INPUT_POST, 'lugar');
        $this->model->unidad_car = filter_input(INPUT_POST, 'unidad_car');
        $this->model->inspeccionresultado = $inspeccionresultado;
        $this->model->save();

        $vehiculo_id = filter_input(INPUT_POST, 'vehiculo');
        $vm = new Vehiculo();
        $vm->vehiculo_id = $vehiculo_id;
        $vm->get();

        $vm->add_inspeccionresultado($irm);
        $irvm = new InspeccionResultadoVehiculo($vm);
        $irvm->save();

        header("Location: " . URL_APP . "/inspecciontareaenejecucion/agregar_integrante/{$inspeccionresultado}");
    }

    function consulta_grupal($arg) {
        SessionHandler()->check_session();

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();
        $registro = date("d-m-Y", strtotime($irm->fecha)) . '-' . date("His", strtotime($irm->hora));

        $select = "ir.inspeccionresultado_id AS INSPECCIONRESULTADO_ID, date_format(ir.fecha, '%d/%m/%Y') AS FECHA, it.denominacion AS INSPECCIONTIPO,
				   ite.tarea AS TAREA, ite.lugar AS LUGAR, ite.numero_tarea AS NUMT, ite.numero_remito AS NR, ir.inspeccionresultado_id AS IRID, CASE ir.estado_inspeccion
				   WHEN 0 THEN 'PENDIENTE' WHEN 1 THEN 'CERRADA' END AS ESTADO,	CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s')) AS REGISTRO,
				   CASE WHEN lp.legajopersonal_id IS NULL AND v.vehiculo_id IS NULL THEN 'NO DEFINIDO'
				   WHEN lp.legajopersonal_id IS NULL AND v.vehiculo_id IS NOT NULL THEN CONCAT(v.dominio, ' - ', vmar.denominacion, ' ', vmod.denominacion)
				   WHEN lp.legajopersonal_id IS NOT NULL AND v.vehiculo_id IS NULL THEN CONCAT(lp.apellido, ' ', lp.nombre) END AS INTEGRANTE";
        $from = "inspeccionresultado ir INNER JOIN inspecciontipo it ON ir.inspecciontipo = it.inspecciontipo_id LEFT JOIN
				 inspeccionresultadolegajopersonal irlp ON ir.inspeccionresultado_id = irlp.compositor LEFT JOIN
				 legajopersonal lp ON irlp.compuesto = lp.legajopersonal_id LEFT JOIN
				 inspeccionresultadovehiculo irv ON ir.inspeccionresultado_id = irv.compositor LEFT JOIN
				 vehiculo v ON irv.compuesto = v.vehiculo_id LEFT JOIN
				 vehiculomodelo vmod ON v.vehiculomodelo = vmod.vehiculomodelo_id LEFT JOIN
				 vehiculomarca vmar ON vmod.vehiculomarca = vmar.vehiculomarca_id
				 INNER JOIN inspecciontareaenejecucion ite ON ir.inspeccionresultado_id = ite.inspeccionresultado";
        $where = "(CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s'))) = '{$registro}'";
        $inspeccion_collection = CollectorCondition()->get('InspeccionResultado', $where, 4, $from, $select);

        $select = "inspecciontareaenejecucion_id, tarea AS TAREA, numero_tarea AS NUMERO_DET, numero_remito AS NUMERO_REMITO,
				   lugar AS LUGAR, unidad_car AS UNIDAD_CAR, inspeccionresultado AS INSPECCIONRESULTADO, inspecciontareaenejecucion_id AS ITEE_ID";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);

        if (is_array($inspeccion_collection)) {
            foreach ($inspeccion_collection as $clave => $value) {
                $inspeccionresultado_id = $value['INSPECCIONRESULTADO_ID'];

                $select = "compositor";
                $from = "inspeccionresultadovehiculo";
                $where = "compositor = {$inspeccionresultado_id}";
                $inspeccionvehiculo_collection = CollectorCondition()->get('InspeccionResultadoVehiculo', $where, 4, $from, $select);
                $inspeccion_collection[$clave]["PAGINA"] = (!empty($inspeccionvehiculo_collection)) ? 'vehicular' : 'personal';
            }
        }

        $this->view->consulta_grupal($irm, $inspeccion_collection, $inspecciontareaenejecucion_collection);
    }

    function consulta_personal($arg) {
        require_once "core/helpers/files.php";

        SessionHandler()->check_session();
        $select_legajo_id = "irlp.compuesto";
        $from_legajo_id = "inspeccionresultadolegajopersonal irlp";
        $where_legajo_id = "irlp.compositor = {$arg}";
        $legajo_id = CollectorCondition()->get('LegajoPersonal', $where_legajo_id, 4, $from_legajo_id, $select_legajo_id);
        $legajo_id = $legajo_id[0]['compuesto'];

        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajo_id;
        $lpm->get();
        unset($lpm->inspeccionresultado_collection);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $select_inspecciontareaenejecucion = "tarea AS TAREA,numero_tarea AS NUMERO_DET,numero_remito AS NUMERO_REMITO,lugar AS LUGAR,unidad_car AS UNIDAD_CAR,inspeccionresultado AS INSPECCIONRESULTADO";
        $from_inspecciontareaenejecucion = "inspecciontareaenejecucion";
        $where_inspecciontareaenejecucion = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where_inspecciontareaenejecucion, 4, $from_inspecciontareaenejecucion, $select_inspecciontareaenejecucion);

        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($resultadorespuesta_collection as $clave => $valor) {
            $icm = new ItemCuestionario();
            $icm->itemcuestionario_id = $valor->itemcuestionario_id;
            $icm->get();
            $temp_denominacion = $icm->denominacion;
            $valor->item_denominacion = $temp_denominacion;
            $valor->orden = $icm->orden;
        }

        /* TRAIGO IMAGENES DE INSPECCION */
        $select = "air.compuesto AS INSPECCIONRESULTADOID,a.url AS URL";
        $from = "archivoinspeccionresultado air INNER JOIN archivo a ON air.compositor = a.archivo_id ";
        $where = "compuesto  = {$arg}";
        $archivoinspeccionresultado_collection = CollectorCondition()->get('ArchivoInspeccionResultado', $where, 4, $from, $select);

        $this->view->consulta_personal($irm, $lpm, $inspecciontareaenejecucion_collection, $archivoinspeccionresultado_collection);
    }

    function consulta_vehicular($arg) {
        require_once "core/helpers/files.php";

        SessionHandler()->check_session();
        $select_vehiculo_id = "irv.compuesto";
        $from_vehiculo_id = "inspeccionresultadovehiculo irv";
        $where_vehiculo_id = "irv.compositor = {$arg}";
        $vehiculo_id = CollectorCondition()->get('Vehiculo', $where_vehiculo_id, 4, $from_vehiculo_id, $select_vehiculo_id);
        $vehiculo_id = $vehiculo_id[0]['compuesto'];

        $vm = new Vehiculo();
        $vm->vehiculo_id = $vehiculo_id;
        $vm->get();
        unset($vm->inspeccionresultado_collection);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $select = "tarea AS TAREA,numero_tarea AS NUMERO_DET,numero_remito AS NUMERO_REMITO, lugar AS LUGAR,
				   unidad_car AS UNIDAD_CAR,inspeccionresultado AS INSPECCIONRESULTADO";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);

        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($resultadorespuesta_collection as $clave => $valor) {
            $icm = new ItemCuestionario();
            $icm->itemcuestionario_id = $valor->itemcuestionario_id;
            $icm->get();
            $temp_denominacion = $icm->denominacion;
            $valor->item_denominacion = $temp_denominacion;
            $valor->orden = $icm->orden;
        }

        /* TRAIGO IMAGENES DE INSPECCION */
        $select = "air.compuesto AS INSPECCIONRESULTADOID,a.url AS URL";
        $from = "archivoinspeccionresultado air INNER JOIN archivo a ON air.compositor = a.archivo_id ";
        $where = "compuesto  = {$arg}";
        $archivoinspeccionresultado_collection = CollectorCondition()->get('ArchivoInspeccionResultado', $where, 4, $from, $select);

        $this->view->consulta_vehicular($irm, $vm, $inspecciontareaenejecucion_collection, $archivoinspeccionresultado_collection);
    }

    function editar_personal($arg) {
        SessionHandler()->check_session();
        $select_legajo_id = "irlp.compuesto";
        $from_legajo_id = "inspeccionresultadolegajopersonal irlp";
        $where_legajo_id = "irlp.compositor = {$arg}";
        $legajo_id = CollectorCondition()->get('LegajoPersonal', $where_legajo_id, 4, $from_legajo_id, $select_legajo_id);
        $legajo_id = $legajo_id[0]['compuesto'];

        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajo_id;
        $lpm->get();
        unset($lpm->inspeccionresultado_collection);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $select = "inspecciontareaenejecucion_id, tarea AS TAREA, numero_tarea AS NUMERO_DET, numero_remito AS NUMERO_REMITO,
				   lugar AS LUGAR, unidad_car AS UNIDAD_CAR, inspeccionresultado AS INSPECCIONRESULTADO, inspecciontareaenejecucion_id AS ITEE_ID";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);

        $grupocuestionario_collection = $irm->inspecciontipo->grupocuestionario_collection;
        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($grupocuestionario_collection as $grupocuestionario) {
            $itemcuestionario_collection = $grupocuestionario->itemcuestionario_collection;

            foreach ($itemcuestionario_collection as $itemcuestionario) {
                $respuestacuestionario_collection = $itemcuestionario->respuestacuestionario_collection;
                $itemcuestionario_id = $itemcuestionario->itemcuestionario_id;
                $itemcuestionario->observacion = '';

                foreach ($resultadorespuesta_collection as $resultadorespuesta) {
                    $temp_itemcuestionario_id = $resultadorespuesta->itemcuestionario_id;
                    $temp_respuesta = $resultadorespuesta->respuesta;
                    if ($itemcuestionario_id == $temp_itemcuestionario_id) {
                        $itemcuestionario->observacion = $resultadorespuesta->observacion;
                        foreach ($respuestacuestionario_collection as $clave => $valor) {
                            $respuestacuestionario = $valor->denominacion;

                            $respuestacuestionario_collection[$clave]->checked = '';
                            if ($respuestacuestionario == $temp_respuesta) {
                                $respuestacuestionario_collection[$clave]->checked = 'checked';
                                unset($resultadorespuesta);
                            }
                        }
                    }
                }
            }
        }

        $irm->inspecciontipo->grupocuestionario_collection = $grupocuestionario_collection;
        $select = "CONCAT(lp.num_legajo, ' - ', lp.apellido, ' ', lp.nombre) AS DENOMINACION, lp.legajopersonal_id AS LEGAJOPERSONAL_ID";
        $from = "legajopersonal lp ORDER BY lp.apellido ASC, lp.nombre ASC";
        $legajopersonal_collection = CollectorCondition()->get('LegajoPersonal', NULL, 4, $from, $select);

        $this->view->editar_personal($legajopersonal_collection, $irm, $lpm, $inspecciontareaenejecucion_collection);
    }

    function actualizar_inspeccion_personal() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();
        $legajopersonal_id = filter_input(INPUT_POST, 'legajopersonal');
        $lpm = new LegajoPersonal();
        $lpm->legajopersonal_id = $legajopersonal_id;
        $lpm->get();

        $inspeccionresultado_id = filter_input(INPUT_POST, 'inspeccionresultado_id');
        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($resultadorespuesta_collection as $clave => $valor) {
            $resultadorespuesta_id = $valor->resultadorespuesta_id;
            $rrm = new ResultadoRespuesta();
            $rrm->resultadorespuesta_id = $resultadorespuesta_id;
            $rrm->delete();
        }

        $irm->resultadorespuesta_collection = array();
        $respuestacuestionario_collection = $_POST['respuestacuestionario'];
        foreach ($respuestacuestionario_collection as $clave => $valor) {

            $rrm = new ResultadoRespuesta();
            if (!empty($valor['respuesta'])) {
                $respuesta = explode("@", $valor['respuesta']);
                $rrm->respuesta = $respuesta[0];
                $rrm->puntaje = $respuesta[1];
            }
            $rrm->observacion = $valor['observacion'];
            $rrm->itemcuestionario_id = $clave;
            $rrm->save();
            $resultadorespuesta_id = $rrm->resultadorespuesta_id;

            $rrm = new ResultadoRespuesta();
            $rrm->resultadorespuesta_id = $resultadorespuesta_id;
            $rrm->get();
            $irm->add_resultadorespuesta($rrm);
        }

        $rrirm = new ResultadoRespuestaInspeccionResultado($irm);
        $rrirm->save();

        $irm = new InspeccionResultado;
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $lpm->add_inspeccionresultado($irm);
        $irlpm = new InspeccionResultadoLegajoPersonal($lpm);
        $irlpm->save();

        $puntaje = 0;
        foreach ($irm->resultadorespuesta_collection as $rraux) {
            $puntaje = $puntaje + $rraux->puntaje;
        }
        $irm->puntaje = $puntaje;
        $irm->save();

        header("Location: " . URL_APP . "/inspecciontareaenejecucion/consulta_personal/{$inspeccionresultado_id}");
    }

    function editar_vehicular($arg) {
        SessionHandler()->check_session();
        $select_vehiculo_id = "irv.compuesto";
        $from_vehiculo_id = "inspeccionresultadovehiculo irv";
        $where_vehiculo_id = "irv.compositor = {$arg}";
        $vehiculo_id = CollectorCondition()->get('Vehiculo', $where_vehiculo_id, 4, $from_vehiculo_id, $select_vehiculo_id);
        $vehiculo_id = $vehiculo_id[0]['compuesto'];

        $vm = new Vehiculo();
        $vm->vehiculo_id = $vehiculo_id;
        $vm->get();
        unset($vm->inspeccionresultado_collection);

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();

        $select = "inspecciontareaenejecucion_id, tarea AS TAREA, numero_tarea AS NUMERO_DET, numero_remito AS NUMERO_REMITO,
				   lugar AS LUGAR, unidad_car AS UNIDAD_CAR, inspeccionresultado AS INSPECCIONRESULTADO, inspecciontareaenejecucion_id AS ITEE_ID";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);

        $grupocuestionario_collection = $irm->inspecciontipo->grupocuestionario_collection;
        $resultadorespuesta_collection = $irm->resultadorespuesta_collection;
        foreach ($grupocuestionario_collection as $grupocuestionario) {
            $itemcuestionario_collection = $grupocuestionario->itemcuestionario_collection;

            foreach ($itemcuestionario_collection as $itemcuestionario) {
                $respuestacuestionario_collection = $itemcuestionario->respuestacuestionario_collection;
                $itemcuestionario_id = $itemcuestionario->itemcuestionario_id;
                $itemcuestionario->observacion = '';

                foreach ($resultadorespuesta_collection as $resultadorespuesta) {
                    $temp_itemcuestionario_id = $resultadorespuesta->itemcuestionario_id;
                    $temp_respuesta = $resultadorespuesta->respuesta;
                    if ($itemcuestionario_id == $temp_itemcuestionario_id) {
                        $itemcuestionario->observacion = $resultadorespuesta->observacion;
                        foreach ($respuestacuestionario_collection as $clave => $valor) {
                            $respuestacuestionario = $valor->denominacion;

                            $respuestacuestionario_collection[$clave]->checked = '';
                            if ($respuestacuestionario == $temp_respuesta) {
                                $respuestacuestionario_collection[$clave]->checked = 'checked';
                                unset($resultadorespuesta);
                            }
                        }
                    }
                }
            }
        }

        $irm->inspecciontipo->grupocuestionario_collection = $grupocuestionario_collection;
        $select_legajo = "CONCAT(lp.num_legajo, ' - ', lp.apellido, ' ', lp.nombre) AS DENOMINACION, lp.legajopersonal_id AS LEGAJOPERSONAL_ID";
        $from_legajo = "legajopersonal lp ORDER BY lp.apellido ASC, lp.nombre ASC";
        $legajopersonal_collection = CollectorCondition()->get('LegajoPersonal', NULL, 4, $from_legajo, $select_legajo);

        $this->view->editar_vehicular($legajopersonal_collection, $irm, $vm, $inspecciontareaenejecucion_collection);
    }

    function actualizar_inspeccion_vehicular() {
        SessionHandler()->check_session();
        //SessionHandler()->check_admin_level();

        $inspeccionresultado_id = filter_input(INPUT_POST, 'inspeccionresultado_id');
        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();
        $irm->observacion = filter_input(INPUT_POST, 'observacion');
        unset($irm->resultadorespuesta_collection);
        $irm->save();
        $inspeccionresultado_id = $irm->inspeccionresultado_id;
        header("Location: " . URL_APP . "/inspecciontareaenejecucion/consulta_vehicular/{$inspeccionresultado_id}");
    }

    /*     * ********************************************************************************************************* */

    function editar_cabecera($arg) {
        SessionHandler()->check_session();

        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $arg;
        $irm->get();
        $registro = date("d-m-Y", strtotime($irm->fecha)) . '-' . date("His", strtotime($irm->hora));

        $select = "ir.inspeccionresultado_id AS INSPECCIONRESULTADO_ID, date_format(ir.fecha, '%d/%m/%Y') AS FECHA, it.denominacion AS INSPECCIONTIPO,
				   ite.tarea AS TAREA, ite.lugar AS LUGAR, ite.numero_tarea AS NUMT, ite.numero_remito AS NR, ir.inspeccionresultado_id AS IRID, CASE ir.estado_inspeccion
				   WHEN 0 THEN 'PENDIENTE' WHEN 1 THEN 'CERRADA' END AS ESTADO,	CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s')) AS REGISTRO,
				   CASE WHEN lp.legajopersonal_id IS NULL AND v.vehiculo_id IS NULL THEN 'NO DEFINIDO'
				   WHEN lp.legajopersonal_id IS NULL AND v.vehiculo_id IS NOT NULL THEN CONCAT(v.dominio, ' - ', vmar.denominacion, ' ', vmod.denominacion)
				   WHEN lp.legajopersonal_id IS NOT NULL AND v.vehiculo_id IS NULL THEN CONCAT(lp.apellido, ' ', lp.nombre) END AS INTEGRANTE";
        $from = "inspeccionresultado ir INNER JOIN inspecciontipo it ON ir.inspecciontipo = it.inspecciontipo_id LEFT JOIN
				 inspeccionresultadolegajopersonal irlp ON ir.inspeccionresultado_id = irlp.compositor LEFT JOIN
				 legajopersonal lp ON irlp.compuesto = lp.legajopersonal_id LEFT JOIN
				 inspeccionresultadovehiculo irv ON ir.inspeccionresultado_id = irv.compositor LEFT JOIN
				 vehiculo v ON irv.compuesto = v.vehiculo_id LEFT JOIN
				 vehiculomodelo vmod ON v.vehiculomodelo = vmod.vehiculomodelo_id LEFT JOIN
				 vehiculomarca vmar ON vmod.vehiculomarca = vmar.vehiculomarca_id
				 INNER JOIN inspecciontareaenejecucion ite ON ir.inspeccionresultado_id = ite.inspeccionresultado";
        $where = "(CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s'))) = '{$registro}'";
        $inspeccion_collection = CollectorCondition()->get('InspeccionResultado', $where, 4, $from, $select);

        $select = "inspecciontareaenejecucion_id, tarea AS TAREA, numero_tarea AS NUMERO_DET, numero_remito AS NUMERO_REMITO,
				   lugar AS LUGAR, unidad_car AS UNIDAD_CAR, inspeccionresultado AS INSPECCIONRESULTADO, inspecciontareaenejecucion_id AS ITEE_ID";
        $from = "inspecciontareaenejecucion";
        $where = "inspeccionresultado = {$arg}";
        $inspecciontareaenejecucion_collection = CollectorCondition()->get('InspeccionTareaEnEjecucion', $where, 4, $from, $select);

        if (is_array($inspeccion_collection)) {
            foreach ($inspeccion_collection as $clave => $value) {
                $inspeccionresultado_id = $value['INSPECCIONRESULTADO_ID'];

                $select = "compositor";
                $from = "inspeccionresultadovehiculo";
                $where = "compositor = {$inspeccionresultado_id}";
                $inspeccionvehiculo_collection = CollectorCondition()->get('InspeccionResultadoVehiculo', $where, 4, $from, $select);
                $inspeccion_collection[$clave]["PAGINA"] = (!empty($inspeccionvehiculo_collection)) ? 'vehicular' : 'personal';
            }
        }

        $this->view->editar_cabecera($irm, $inspeccion_collection, $inspecciontareaenejecucion_collection);
    }

    function actualizar_cabecera() {
        SessionHandler()->check_session();

        $inspeccionresultado_id = filter_input(INPUT_POST, 'inspeccionresultado_id');
        $irm = new InspeccionResultado();
        $irm->inspeccionresultado_id = $inspeccionresultado_id;
        $irm->get();

        $registro = date("d-m-Y", strtotime($irm->fecha)) . '-' . str_replace(':', '', $irm->hora);
        $select = "ir.inspeccionresultado_id AS ID";
        $from = "inspeccionresultado ir";
        $where = "CONCAT(date_format(ir.fecha, '%d-%m-%Y'), '-', date_format(ir.hora, '%H%i%s')) = '{$registro}'";
        $inspecciones = CollectorCondition()->get('InspeccionResultado', $where, 4, $from, $select);

        if (is_array($inspecciones) && !empty($inspecciones)) {
            foreach ($inspecciones as $elemento) {
                $inspeccion_id = $elemento['ID'];
                $irm = new InspeccionResultado();
                $irm->inspeccionresultado_id = $inspeccion_id;
                $irm->get();

                $irm->fecha = filter_input(INPUT_POST, 'fecha');
                $irm->hora = filter_input(INPUT_POST, 'hora');
                $irm->latitud = filter_input(INPUT_POST, 'latitud');
                $irm->longitud = filter_input(INPUT_POST, 'longitud');
                $irm->altitud = filter_input(INPUT_POST, 'altitud');
                $irm->estado_inspeccion = filter_input(INPUT_POST, 'estado_inspeccion');
                $irm->observacion = filter_input(INPUT_POST, 'observacion');
                $irm->save();


                $select = "itee.inspecciontareaenejecucion_id AS ID";
                $from = "inspecciontareaenejecucion itee";
                $where = "itee.inspeccionresultado = {$inspeccion_id}";
                $inspecciontareaenejecucion_id = CollectorCondition()->get('InspeccionTareaEneEjecucion', $where, 4, $from, $select);
                $inspecciontareaenejecucion_id = $inspecciontareaenejecucion_id[0]['ID'];

                $this->model->inspecciontareaenejecucion_id = $inspecciontareaenejecucion_id;
                $this->model->get();
                $this->model->tarea = filter_input(INPUT_POST, 'tarea');
                $this->model->numero_tarea = filter_input(INPUT_POST, 'numero_tarea');
                $this->model->numero_remito = filter_input(INPUT_POST, 'numero_remito');
                $this->model->lugar = filter_input(INPUT_POST, 'lugar');
                $this->model->unidad_car = filter_input(INPUT_POST, 'unidad_car');
                $this->model->save();
            }
        }

        header("Location: " . URL_APP . "/inspecciontareaenejecucion/consulta_grupal/{$inspeccionresultado_id}");
    }

    /*     * ************************************************************************************************************ */

    function exportador() {
        SessionHandler()->check_session();
        $contratista_collection = Collector()->get('Contratista');
        $vehiculo_collection = Collector()->get('Vehiculo');
        $legajopersonal_collection = Collector()->get('LegajoPersonal');
        $this->view->exportador($contratista_collection, $vehiculo_collection, $legajopersonal_collection);
    }

    function exportar_inspeccion() {
        $tipo_resumen = filter_input(INPUT_POST, "tipo_resumen");
        $contratista = filter_input(INPUT_POST, "contratista");
        $fecha_desde = filter_input(INPUT_POST, "fecha_desde");
        $fecha_hasta = filter_input(INPUT_POST, "fecha_hasta");
        $vehiculo = filter_input(INPUT_POST, "vehiculo");
        $legajopersonal = filter_input(INPUT_POST, "legajopersonal");

        $cm = new Contratista();
        $cm->contratista_id = $contratista;
        $cm->get();

        switch ($tipo_resumen) {
            case 1:
                $vm = new Vehiculo();
                $vm->vehiculo_id = $vehiculo;
                $vm->get();

                $query = file_get_contents("sql/inspeccion_movil_srl_dominio_fecha.sql");
                $titulo = "RESUMEN INSP EQ Y MOVILES POR MOVIL";
                $subtitulo = "DOMINIO: {$vm->dominio} - SRL: {$cm->denominacion}";
                $query = str_replace('{vehiculo}', $vehiculo, $query);
                break;
            case 2:
                $lpm = new LegajoPersonal();
                $lpm->legajopersonal_id = $legajopersonal;
                $lpm->get();
                $persona = $lpm->apellido . "," . $lpm->nombre;

                $query = file_get_contents("sql/inspeccion_personal_srl_fecha.sql");
                $titulo = "DESEMPEÑO DE PERSONAL POR CONTRATISTA";
                $subtitulo = "PERSONAL: {$persona} - SRL: {$cm->denominacion} - FECHA: {$fecha_desde} a {$fecha_hasta}";
                $query = str_replace('{legajopersonal}', $legajopersonal, $query);
                break;
            case 3:
                $query = file_get_contents("sql/inspeccion_personal_contratista.sql");
                $titulo = "RESUMEN INSP DE PERSONAL POR PERSONA";
                $subtitulo = "SRL: {$cm->denominacion}";
                break;
            case 4:
                $query = file_get_contents("sql/inspeccion_personal_contador.sql");
                $titulo = "RESUMEN CANTIDAD INSP POR PERSONA";
                $subtitulo = "SRL: {$cm->denominacion} Fecha: {$fecha_desde} a {$fecha_hasta}";
                break;
            case 5:
                $query = file_get_contents("sql/inspeccion_vehiculos_contador.sql");
                $titulo = "RESUMEN CANTIDAD INSP POR VEHICULO";
                $subtitulo = "SRL: {$cm->denominacion} Fecha: {$fecha_desde} a {$fecha_hasta}";
                break;
            case 6:
                $query = file_get_contents("sql/inspeccion_personal_srl_informe.sql");
                $titulo = "RESUMEN INSP DE PERSONAL POR SRL E INFORME";
                $subtitulo = "SRL: {$cm->denominacion}";
                break;
        }

        $query = str_replace('{contratista}', $contratista, $query);
        $query = str_replace('{fecha_desde}', $fecha_desde, $query);
        $query = str_replace('{fecha_hasta}', $fecha_hasta, $query);
        $sql_collection = execute_query($query);

        switch ($tipo_resumen) {
            case 1:
                $array_encabezados = array('CODIGO', 'FECHA', 'OBSERVACION');
                $array_exportacion = array();
                $array_exportacion[] = $array_encabezados;

                if (is_array($sql_collection)) {
                    foreach ($sql_collection as $clave => $valor) {
                        $codigo = $valor["CONTRATISTA"] . "-" . $valor["REFERENCIA"];
                        $array_temp = array();
                        $array_temp = array(
                            $codigo
                            , $valor["FECHA_INSPECION"]
                            , trim(preg_replace('/\s+/', ' ', $valor["OBSERVACION_INSPECCION"])));
                        $array_exportacion[] = $array_temp;
                    }
                }
                break;
            case 2:
                $grupocuestionario_collection = Collector()->get('grupocuestionario');
                $array_encabezados[] = array('Fecha');
                foreach ($grupocuestionario_collection as $clave => $valor) {
                    array_push($array_encabezados[0], $valor->denominacion);
                }

                if (is_array($sql_collection)) {
                    $array_cuerpo = array();
                    $array_temp = array();
                    foreach ($sql_collection as $clave => $valor) {
                        $inspeccionresultado_id = $valor['INSPECCIONRESULTADO_ID'];
                        $fecha = $valor['FECHA'];
                        $select = "rr.respuesta AS RESPUESTA,gc.denominacion AS DENOMINACION, grupocuestionario_id AS GRUPOCUESTIONARIO_ID";
                        $from = "resultadorespuestainspeccionresultado rrir INNER JOIN resultadorespuesta rr ON rrir.compositor = rr.resultadorespuesta_id
						INNER JOIN  itemcuestionariogrupocuestionario icgc ON  rr.itemcuestionario_id = icgc.compositor
						INNER JOIN	grupocuestionario gc ON	gc.grupocuestionario_id = icgc.compuesto";
                        $where = "rrir.compuesto = {$inspeccionresultado_id} GROUP BY gc.denominacion ,  rr.respuesta ORDER BY gc.orden";
                        $resultadorespuestainspeccionresultado_collection = CollectorCondition()->get('ResultadoRespuestaInspeccionResultado', $where, 4, $from, $select);

                        $result = array();
                        foreach ($resultadorespuestainspeccionresultado_collection as $key => $value) {

                            $repeat = false;
                            for ($i = 0; $i < count($result); $i++) {
                                if ($result[$i]['GRUPOCUESTIONARIO_ID'] == $value['GRUPOCUESTIONARIO_ID']) {
                                    if ($result[$i]['RESPUESTA'] != $value['RESPUESTA']) {
                                        if ($result[$i]['RESPUESTA'] != 'Mal') {
                                            if ($value['RESPUESTA'] == 'Mal') {
                                                $result[$i]['RESPUESTA'] = $value['RESPUESTA'];
                                            } elseif ($result[$i]['RESPUESTA'] != NULL) {
                                                if ($value['RESPUESTA'] != 'NC' AND $value['RESPUESTA'] != 'No Obs') {
                                                    $result[$i]['RESPUESTA'] = $value['RESPUESTA'];
                                                }
                                            } else {
                                                $result[$i]['RESPUESTA'] = $value['RESPUESTA'];
                                            }
                                        }
                                    }
                                    $repeat = true;
                                    break;
                                }
                            }
                            if ($repeat == false)
                                $result[] = array('GRUPOCUESTIONARIO_ID' => $value['GRUPOCUESTIONARIO_ID'], 'RESPUESTA' => $value['RESPUESTA']);
                        }

                        /* VOY CREANDO ARRAY CON RESPUESTAS Y FECHA */
                        $array_cuerpo[] = array($inspeccionresultado_id, $fecha);
                        foreach ($array_cuerpo as $indice => $cuerpo) {
                            if ($cuerpo[0] == $inspeccionresultado_id) {
                                foreach ($result as $respuesta) {
                                    array_push($array_cuerpo[$indice], $respuesta['RESPUESTA']);
                                }
                            }
                        }
                    }

                    /* ELIMINO EL ID DE INSPECCION */
                    foreach ($array_cuerpo as $key => $value) {
                        unset($array_cuerpo[$key][0]);
                    }
                    $array = array();
                    foreach ($array_cuerpo as $key => $value) {
                        $array[] = array_values($value);
                        ;
                    }

                    $array_exportacion = array_merge($array_encabezados, $array);
                } else {
                    $array_exportacion = $array_encabezados;
                }
                break;
            case 3:
                $array_encabezados[] = array('APELLIDO Y NOMBRE', 'REFERENCIA', 'FECHA', 'DESCRIPCION');
                $array_exportacion = array();
                if (is_array($sql_collection)) {
                    $array_cuerpo = array();
                    $array_temp = array();
                    foreach ($sql_collection as $key => $value) {
                        $array_temp = array(
                            $value["NOMBRE_APELLIDO"]
                            , $value["REFERENCIA"]
                            , $value["FECHA"]
                            , trim(preg_replace('/\s+/', ' ', $value["OBSERVACION"])));
                        $array_cuerpo[] = $array_temp;
                    }
                    $array_exportacion = array_merge($array_encabezados, $array_cuerpo);
                } else {
                    $array_exportacion = $array_encabezados;
                }
                break;
            case 4:
                $array_encabezados[] = array('APELLIDO Y NOMBRE', 'CANTIDAD');
                $array_exportacion = array();
                if (is_array($sql_collection)) {
                    $array_cuerpo = array();
                    $array_temp = array();
                    foreach ($sql_collection as $key => $value) {
                        $array_temp = array(
                            $value["NOMBRE_APELLIDO"]
                            , $value["CONTADOR"]);
                        $array_cuerpo[] = $array_temp;
                    }
                    $array_exportacion = array_merge($array_encabezados, $array_cuerpo);
                } else {
                    $array_exportacion = $array_encabezados;
                }
                break;
            case 5:
                $array_encabezados[] = array('DOMINIO', 'CANTIDAD');
                $array_exportacion = array();
                if (is_array($sql_collection)) {
                    foreach ($sql_collection as $key => $value) {
                        $array_temp = array(
                            $value["DOMINIO"]
                            , $value["CONTADOR"]);
                        $array_cuerpo[] = $array_temp;
                    }
                    $array_exportacion = array_merge($array_encabezados, $array_cuerpo);
                } else {
                    $array_exportacion = $array_encabezados;
                }
                break;
            case 6:
                $array_encabezados[] = array('APELLIDO Y NOMBRE', 'REFERENCIA', 'DESCRIPCION');
                $array_exportacion = array();
                if (is_array($sql_collection)) {
                    foreach ($sql_collection as $key => $value) {
                        $array_observacion = array();
                        $inspeccionresultado_id = $value["INSPECCIONRESULTADO_ID"];
                        $observacion = trim(preg_replace('/\s+/', ' ', $value["OBSERVACION"]));

                        $select = "rr.observacion AS OBSERVACION";
                        $from = "resultadorespuestainspeccionresultado rrir INNER JOIN resultadorespuesta rr ON	rrir.compositor = rr.resultadorespuesta_id";
                        $where = "rrir.compuesto = {$inspeccionresultado_id}";
                        $resultadorespuestainspeccionresultado_collection = CollectorCondition()->get('ResultadoRespuestaInspeccionResultado', $where, 4, $from, $select);

                        foreach ($resultadorespuestainspeccionresultado_collection as $clave => $valor) {
                            if (!empty($valor["OBSERVACION"])) {
                                $array_observacion[] = $valor["OBSERVACION"];
                            }
                        }

                        if (empty($array_observacion) AND empty($observacion)) {
                            $observaciones_final = 'S/N';
                        } elseif (empty($array_observacion) AND ! empty($observacion)) {
                            $observaciones_final = $observacion;
                        } elseif (!empty($array_observacion) AND empty($observacion)) {
                            $observaciones_final = implode("\n", $array_observacion);
                        } elseif (!empty($array_observacion) AND ! empty($observacion)) {
                            array_unshift($array_observacion, $observacion);
                            $observaciones_final = implode("\n", $array_observacion);
                        }
                        $sql_collection[$key]["OBSERVACION_FINAL"] = $observaciones_final;
                    }

                    foreach ($sql_collection as $clave => $valor) {
                        $array_temp = array(
                            $valor["NOMBRE_APELLIDO"]
                            , $valor["REFERENCIA"]
                            , $valor["OBSERVACION_FINAL"]);
                        $array_exportacion[] = $array_temp;
                    }
                    $array_exportacion = array_merge($array_encabezados, $array_exportacion);
                } else {
                    $array_exportacion = $array_encabezados;
                }
                break;
        }

        ExcelReport()->extraer_informe_conjunto($titulo, $subtitulo, $array_exportacion);
    }

}

?>
