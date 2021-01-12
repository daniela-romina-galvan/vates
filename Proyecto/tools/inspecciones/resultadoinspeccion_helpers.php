<?php
require_once 'core/collector.php';
require_once 'core/collector_condition.php';

require_once 'modules/inspeccionresultado/model.php';
require_once 'modules/inspecciontareaejecutada/model.php';
require_once 'modules/inspeccioncamionetadiario/model.php';
require_once 'modules/intervencion/model.php';
require_once 'modules/inspecciontipo/model.php';
require_once 'modules/resultadorespuesta/model.php';
require_once 'modules/vehiculo/model.php';
require_once 'modules/transformador/model.php';
require_once 'modules/rutadiaria/model.php';
require_once 'modules/legajopersonal/model.php';
require_once 'modules/archivo/model.php';
require_once 'tools/inspecciones/tareas_helper.php';
function recibir ($print=TRUE){

    $data = array();
    $data ["accion"]="obtener";
    $ch = curl_init("https://www.edelar.com.ar/api_geco_desa/helpers/inspeccionresultado/resultadoinspeccion_sincro.php");
    curl_setopt($ch, CURLOPT_TIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
    $response = curl_exec($ch);
    //print_r($response);exit;
    curl_close($ch);
    $inspecciones = json_decode($response);
//    print_r($inspecciones);exit;
    $inspeccionesAux=array();
    $inspeccinesADD=array();
    $error=0;

    if(!empty($inspecciones->resultados)){
        foreach ($inspecciones->resultados as $inspeccion_verificar ) {
            $select = "ir.inspeccionresultado_id";
            $from = "inspeccionresultado ir";
            $where = "SELECT ir.inspeccionresultado_id FROM inspeccionresultado ir WHERE ir.fecha = '".$inspeccion_verificar->fecha."' AND ir.hora = '".$inspeccion_verificar->hora."'";
            $inspeccion_exits = CollectorCondition()->get(NULL, $where, 5, $from, $select);
            if($inspeccion_exits==0){
                $inspeccinesADD[]=$inspeccion_verificar;
            }
        }
        
        if(!empty($inspeccinesADD)){
            //print_r($inspeccinesADD);exit;
            foreach ($inspeccinesADD as $inspeccion ) {

            $integrante = $inspeccion->integrante;
            $integranteTipo = $inspeccion->tipointegrante;
            $inspeccionresultado = new InspeccionResultado();
            $inspeccionresultado->fecha = $inspeccion->fecha;
            $inspeccionresultado->hora = $inspeccion->hora;
            $inspeccionresultado->latitud = $inspeccion->latitud;
            $inspeccionresultado->longitud = $inspeccion->longitud;
            $inspeccionresultado->altitud = $inspeccion->altitud;
            $inspeccionresultado->estado_inspeccion = $inspeccion->estado_inspeccion;
            $inspeccionresultado->inspecciontipo = $inspeccion->inspecciontipo;
            $inspeccionresultado->inspector = $inspeccion->inspector;
            $inspeccionresultado->legajopersonal = $inspeccion->legajopersonal;
            $inspeccionresultado->observacion = $inspeccion->observacion;
            $inspeccionresultado->puntaje = $inspeccion->puntaje;
            //print_r($inspeccionresultado);exit;
            $inspeccionresultado->save();

            $auxID = $inspeccionresultado->inspeccionresultado_id;
            
            if($auxID > 0){

                
                $inspecciontipo = new InspeccionTipo();
                if($inspecciontipo->codigo=='IPTP01'){
                    $tareaAux = new Tarea();
                    $tareaAux->tarea_id = $inspeccion->tarea;
                    $tareaAux->get();                    
                    $inspecciontipo->inspecciontipo_id = $inspeccion->tipotarea->inspecciontipo_id;
                    $inspecciontipo->get();
                    $inspeccionresultado->inspecciontipo = $inspecciontipo;
                    $inspeccionresultado->save();
                }else{
                    $inspecciontipo->inspecciontipo_id = $inspeccion->inspecciontipo;
                    $inspecciontipo->get();
                }
                
                switch ($inspecciontipo->codigo) {
                    case 'IPTE01':
                        $inspecciontareaenejecucion = new InspeccionTareaEnEjecucion();
                        $inspecciontareaenejecucion->tarea = $inspeccion->tarea;
                        $inspecciontareaenejecucion->numero_tarea = $inspeccion->numero_tarea;
                        $inspecciontareaenejecucion->numero_remito = $inspeccion->numero_remito;
                        $inspecciontareaenejecucion->lugar = $inspeccion->lugar;
                        $inspecciontareaenejecucion->unidad_car = $inspeccion->unidad_car;
                        $inspecciontareaenejecucion->inspeccionresultado = $auxID;
                        $inspecciontareaenejecucion->save();
                        break;
                    case 'IPTP01':
                        $inspecciontareaejecutada = new InspeccionTareaEjecutada();
                        $inspecciontareaejecutada->tarea = $inspeccion->tarea;
                        $inspecciontareaejecutada->inspeccionresultado = $inspeccionresultado;
                        $inspecciontareaejecutada->save();
                        $tarea = new Tarea();
                        $tarea->tarea_id=$inspeccion->tarea;
                        $tarea->get();
                        $tarea->marcar_tarea($tarea->nro_tarea);
                        $rutadiaria = new RutaDiaria();
                        $rutadiaria->actualizadar_rutadiario(array($tarea->nro_tarea,'REALIZADO',$inspeccionresultado->fecha." ".$inspeccionresultado->hora,$inspeccionresultado->inspector));
                        $cantidadMaterial=0;
                        if (!empty($inspeccion->resultadorespuestamaterial_collection)) {
                            foreach ($inspeccion->resultadorespuestamaterial_collection as $respuestamaterial) {

                                $resultadomaterial = new ResultadoRespuestaMaterial();
                                //$resultadomaterial->id = $respuestamaterial->idRemoto;
                                $resultadomaterial->material = $respuestamaterial->material;
                                $resultadomaterial->cantidad = $respuestamaterial->cantidad;
                                $resultadomaterial->puntaje = $respuestamaterial->puntaje;
                                $resultadomaterial->tareamaterial_id = $respuestamaterial->tareamaterial_id;
                                $resultadomaterial->save();
                                $auxIDRespuestaMaterial = $resultadomaterial->resultadorespuestamaterial_id;
                                if ($auxIDRespuestaMaterial > 0) {
                                    $cantidadMaterial++;
                                    $inspecciontareaejecutada->add_resultadorespuestamaterial($resultadomaterial);
                                }
                            }
                        }else{
                           $inspecciontareaejecutada->resultadorespuestamaterial_collection = array(); 
                        }
                        if($cantidadMaterial!=0&&$cantidadMaterial == count($inspecciontareaejecutada->resultadorespuestamaterial_collection)){
                            $rrmite = new ResultadoRespuestaMaterialInspeccionResultado($inspecciontareaejecutada);
                            $rrmite->save();                            
                        }else if($cantidadMaterial==0&&$cantidadMaterial == count($inspecciontareaejecutada->resultadorespuestamaterial_collection)){
                            $error = 0;                            
                        }else {
                            $error = 1;
                        }
                        break;
                    case 'IPCD01':
                        $inspeccioncamionetadiario = new InspeccionCamionetaDiario();
                        $inspeccioncamionetadiario->turno = $inspeccion->turno;
                        $inspeccioncamionetadiario->kilometro = $inspeccion->kilometro;
                        $inspeccioncamionetadiario->inspeccionresultado = $auxID;
                        $inspeccioncamionetadiario->save();
                        break;  
                    case 'PIRT01':
                        $intervencion = new Intervencion();
                        /*$intervencion->fecha_inicio = $inspeccion->fecha_inicio;
                        $intervencion->fecha_fin = $inspeccion->fecha_fin;*/
                        $intervencion->unicom = $inspeccion->unicom;
                        $intervencion->ultimo_punto_instalacion = $inspeccion->ultimo_punto_instalacion;
                        if($inspeccion->tallerexterno!='NULL')
                        $intervencion->tallerexterno = $inspeccion->tallerexterno;
                        $intervencion->inspeccionresultado = $auxID;
                        $intervencion->save();
                        $cantidadTrabajo=0;
                        foreach ($inspeccion->resultadorespuestatrabajo_collection as $respuestrabajo ) {
                            
                            $resultadotrabajo = new ResultadoRespuestaTrabajo();
                            $resultadotrabajo->trabajo_denominacion = $respuestrabajo->trabajo_denominacion;
                            $resultadotrabajo->cantidad = $respuestrabajo->cantidad;
                            $resultadotrabajo->cantidad_utilizada = $respuestrabajo->cantidad_utilizada;
                            $resultadotrabajo->trabajo = $respuestrabajo->trabajo;
                            $resultadotrabajo->save();
                            $auxIDRespuestaTrabajo = $resultadotrabajo->resultadorespuestatrabajo_id;
                            if($auxIDRespuestaTrabajo > 0){
                                $cantidadTrabajo++;
                                $intervencion->add_resultadorespuestatrabajo($resultadotrabajo);                                
                            }               
                        }
                        if($cantidadTrabajo== count($inspeccion->resultadorespuestatrabajo_collection)){
                            $rrmite = new ResultadoRespuestaTrabajoInspeccionResultado($intervencion);
                            $rrmite-> save();
                        } else {
                            $error = 1;
                        }
                        break;  
                }

                $respuestasAux=array();
                $auxIDRespuesta = 0;
                $cantidad = 0;
                foreach ($inspeccion->resultadorespuesta_collection as $respuesta ) {
                    $resultadorespuesta = new ResultadoRespuesta();
                    $resultadorespuesta->respuesta = $respuesta->respuesta;
                    $resultadorespuesta->observacion = $respuesta->observacion;
                    $resultadorespuesta->puntaje = $respuesta->puntaje;
                    $resultadorespuesta->itemcuestionario_id = $respuesta->itemcuestionario_id;
                    $resultadorespuesta->save();
                    $auxIDRespuesta = $resultadorespuesta->resultadorespuesta_id;
                    if($auxIDRespuesta > 0){
                        $cantidad++;
                        $inspeccionresultado->add_resultadorespuesta($resultadorespuesta);
                    }
                    $respuestasAux[] = $resultadorespuesta;
                }

                    if($cantidad == count($inspeccionresultado->resultadorespuesta_collection)){
                        $respuestasinspecciones = new ResultadoRespuestaInspeccionResultado($inspeccionresultado);
                        $respuestasinspecciones->save();

                        if($integranteTipo == "vehiculo"){
                            $vehiculo = new Vehiculo();
                            $vehiculo->vehiculo_id = $integrante;
                            $vehiculo->get();
                            $vehiculo->getInspecciones();
                            $vehiculo->add_inspeccionresultado($inspeccionresultado);

                            $irv = new InspeccionResultadoVehiculo($vehiculo);
                            $irv->save();

                        } else if($integranteTipo == "persona"){
                            $persona = new LegajoPersonal();
                            $persona->legajopersonal_id = $integrante;
                            $persona->get();
                            $persona->getInspecciones();
                            $persona->add_inspeccionresultado($inspeccionresultado);

                            $irp= new InspeccionResultadoLegajoPersonal($persona);
                            $irp->save();
                        } else if($integranteTipo == "movil"){
                            $movil = new Movil();
                            $movil->movil_id = $integrante;
                            $movil->get();
                            $movil->getInspecciones();
                            $movil->add_inspeccionresultado($inspeccionresultado);
                            $irm= new InspeccionResultadoMovil($movil);
                            $irm->save();
                        }else if($integranteTipo == "transformador"){
                            $transformador = new Transformador();
                            $transformador->transformador_id = $integrante;
                            $transformador->get();
                            $transformador->getInspecciones();
                            $transformador->add_inspeccionresultado($inspeccionresultado);
                            $irm= new InspeccionResultadoTransformador($transformador);
                            $irm->save(); 
                        }
                    }else{
                        $error = 1;
                    }
                    if(!empty($inspeccion->archivo_collection)){
                       foreach ($inspeccion->archivo_collection as $archivo ) {

                        $id=$auxID;
                        $carpeta=URL_APPFILES . 'inspeccionresultado/'.$id;
                        $archivoAux= new Archivo();
                        $archivoAux->denominacion=$archivo->denominacion;
                        $archivoAux->url=$archivo->url;
                        $archivoAux->fecha_carga=$archivo->fecha_carga;
                        $archivoAux->formato=$archivo->formato;
                        $archivoAux->save();
                        if($archivoAux->archivo_id>0){
                            $inspeccionresultado->add_archivo($archivoAux);
                            if (!file_exists($carpeta)) {
                                mkdir($carpeta, 0777, true);
                                $data = base64_decode($archivo->archivo);
                                file_put_contents($carpeta.'/'.$archivoAux->denominacion,$data);
                            }else{
                                $data = base64_decode($archivo->archivo);
                                file_put_contents($carpeta.'/'.$archivoAux->denominacion,$data);
                            }
                        }

                    }
                    $archivoinspeccionresultado = new ArchivoInspeccionResultado($inspeccionresultado);
                    $archivoinspeccionresultado->save();
                    }
            }else{
                $error = 1;
            }
            }
        }
    }
    
    if($print){
//        $sql = "DELETE FROM tareainspector";
//        $datos = array();
//        $resultados = execute_query($sql, $datos);
        print_r($error);
    }else{
        return $error;
    }
}
function enviar($print = TRUE) {
    $error=0;
    $inspeccion_collection = array();
    $inspeccion_collection1 = array();
    $select = " ir.inspeccionresultado_id, ir.fecha, ir.hora, ipte.tarea, ipte.numero_tarea, ipte.numero_remito, ipte.lugar, ipte.unidad_car,
                    ir.latitud, ir.longitud, ir.altitud, ir.estado_inspeccion, ir.observacion, ir.puntaje,
                    it.inspecciontipo_id inspecciontipo, ir.inspector, ir.legajopersonal, irlp.compuesto integrante";
    $from = "inspeccionresultado ir, inspeccionresultadolegajopersonal irlp, inspecciontipo it, inspecciontareaenejecucion ipte";
    $where = "ir.estado_inspeccion = 1 AND ir.inspeccionresultado_id = irlp.compositor AND ir.inspecciontipo= it.inspecciontipo_id AND ir.inspeccionresultado_id=ipte.inspeccionresultado";
    $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
    if (is_array($col)) {
    foreach ($col as $inspeccion) {
        $inspeccion['tipointegrante'] = "persona";
        $inspeccion_collection[] = $inspeccion;
    }
    }

    $select = " ir.inspeccionresultado_id, ir.fecha, ir.hora, ipte.tarea, ipte.numero_tarea, ipte.numero_remito, ipte.lugar, ipte.unidad_car,
                    ir.latitud, ir.longitud, ir.altitud, ir.estado_inspeccion, ir.observacion, ir.puntaje,
                    it.inspecciontipo_id inspecciontipo, ir.inspector, ir.legajopersonal, irv.compuesto integrante";
    $from = "inspeccionresultado ir, inspeccionresultadovehiculo irv, inspecciontipo it, inspecciontareaenejecucion ipte";
    $where = "ir.estado_inspeccion = 1 AND ir.inspeccionresultado_id = irv.compositor AND ir.inspecciontipo= it.inspecciontipo_id AND ir.inspeccionresultado_id=ipte.inspeccionresultado";
    $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
    if (is_array($col)) {
    foreach ($col as $inspeccion) {
        $inspeccion['tipointegrante'] = "vehiculo";
        $inspeccion_collection[] = $inspeccion;
    }
    }
    
    $select = " ir.inspeccionresultado_id, ir.fecha, ir.hora, ipcd.turno, ipcd.kilometro, 
                    ir.latitud, ir.longitud, ir.altitud, ir.estado_inspeccion, ir.observacion, ir.puntaje,
                    it.inspecciontipo_id inspecciontipo, ir.inspector, ir.legajopersonal, irv.compuesto integrante";
    $from = "inspeccionresultado ir, inspeccionresultadovehiculo irv, inspecciontipo it, inspeccioncamionetadiario ipcd";
    $where = "ir.estado_inspeccion = 1 AND ir.inspeccionresultado_id = irv.compositor AND ir.inspecciontipo= it.inspecciontipo_id AND ir.inspeccionresultado_id=ipcd.inspeccionresultado";
    $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
    if (is_array($col)) {
    foreach ($col as $inspeccion) {
        $inspeccion['tipointegrante'] = "vehiculo";
        $inspeccion_collection[] = $inspeccion;
    }
    }
    
    $select = " ir.inspeccionresultado_id, ir.fecha, ir.hora, i.fecha_inicio, i.fecha_fin, i.unicom, i.ultimo_punto_instalacion, i.tallerexterno, 
                    ir.latitud, ir.longitud, ir.altitud, ir.estado_inspeccion, ir.observacion, ir.puntaje,
                    it.inspecciontipo_id inspecciontipo, ir.inspector, ir.legajopersonal, irt.compuesto integrante";
    $from = "inspeccionresultado ir, inspeccionresultadotransformador irt, inspecciontipo it, intervencion i";
    $where = "ir.estado_inspeccion = 1 AND ir.inspeccionresultado_id = irt.compositor AND ir.inspecciontipo= it.inspecciontipo_id AND ir.inspeccionresultado_id=i.inspeccionresultado";
    $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
    if (is_array($col)) {
    foreach ($col as $inspeccion) {
        $inspeccion['tipointegrante'] = "transformador";
        $inspeccion_collection[] = $inspeccion;
    }
    }

    $select = " ir.inspeccionresultado_id, ir.fecha, ir.hora, iptp.tarea, ir.latitud, ir.longitud,
                    ir.altitud, ir.estado_inspeccion, ir.observacion, ir.puntaje,
                    it.inspecciontipo_id inspecciontipo, ir.inspector, ir.legajopersonal, irm.compuesto integrante";
        $from = "inspeccionresultado ir, inspeccionresultadomovil irm, inspecciontipo it, inspecciontareaejecutada iptp";
        $where = "ir.estado_inspeccion = 1 AND ir.inspeccionresultado_id = irm.compositor AND ir.inspecciontipo= it.inspecciontipo_id AND ir.inspeccionresultado_id=iptp.inspeccionresultado";
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
     if (is_array($col)) {   
        foreach ($col as $inspeccion) {
            $inspeccion['tipointegrante'] = "movil";
            $inspeccion_collection[] = $inspeccion;  
            }
     }    
        foreach ($inspeccion_collection as $inspeccion) {    
        $resultadorespuesta_collection = array();

        $select = "r.resultadorespuesta_id, r.respuesta, r.observacion, r.puntaje, r.itemcuestionario_id, i.compositor inspeccionresultado, i.compuesto integrante_id";
        $from = "resultadorespuestainspeccionresultado ri, inspeccionresultadolegajopersonal i, resultadorespuesta r, inspeccionresultado ir";
        $where = "i.compositor= ri.compuesto AND r.resultadorespuesta_id = ri.compositor
                  AND i.compositor = ir.inspeccionresultado_id
                  AND ri.compuesto = ir.inspeccionresultado_id
                  AND ir.estado_inspeccion = 1
                  AND ir.inspeccionresultado_id=" . $inspeccion["inspeccionresultado_id"];
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);

        if (is_array($col)) {
            foreach ($col as $resultadorespuesta) {
                $resultadorespuesta['tiporesultado'] = "persona";
                $resultadorespuesta_collection[] = $resultadorespuesta;
            }
        }
        $from = "resultadorespuestainspeccionresultado ri, inspeccionresultadovehiculo i, resultadorespuesta r, inspeccionresultado ir";
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);

        if (is_array($col)) {
            foreach ($col as $resultadorespuesta) {
                $resultadorespuesta['tiporesultado'] = "vehiculo";
                $resultadorespuesta_collection[] = $resultadorespuesta;
            }
        }

        $from = "resultadorespuestainspeccionresultado ri, inspeccionresultadomovil i, resultadorespuesta r, inspeccionresultado ir";
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
        
        if (is_array($col)) {
            foreach ($col as $resultadorespuesta) {
                $resultadorespuesta['tiporesultado'] = "movil";
                $resultadorespuesta_collection[] = $resultadorespuesta;
            }
            
        }
        
        $from = "resultadorespuestainspeccionresultado ri, inspeccionresultadotransformador i, resultadorespuesta r, inspeccionresultado ir";
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
        
        if (is_array($col)) {
            foreach ($col as $resultadorespuesta) {
                $resultadorespuesta['tiporesultado'] = "trasformador";
                $resultadorespuesta_collection[] = $resultadorespuesta;
            }
            
        }
        
        $inspeccion['resultadorespuesta_collection'] = $resultadorespuesta_collection;

        $resultadomaterial_collection=array();
        $select = "rm.resultadorespuestamaterial_id, rm.material, rm.cantidad, rm.puntaje, rm.tareamaterial_id, rmi.compuesto inspeccionresultado";
        $from = "resultadorespuestamaterialinspeccionresultado rmi, resultadorespuestamaterial rm, inspeccionresultado ir";
        $where = "rm.resultadorespuestamaterial_id= rmi.compositor 
                AND rmi.compuesto = ir.inspeccionresultado_id 
                AND ir.inspeccionresultado_id = ". $inspeccion["inspeccionresultado_id"];;
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
        if (is_array($col)) {
            foreach ($col as $resultadomaterial) {
                $resultadomaterial_collection[]=$resultadomaterial;
            }
        }
        $inspeccion['resultadorespuestamaterial_collection'] = $resultadomaterial_collection;

        $resultadotrabajo_collection=array();
        $select = "rt.resultadorespuestatrabajo_id, rt.trabajo_denominacion, rt.cantidad, rt.cantidad_utilizada, rt.trabajo, rti.compuesto inspeccionresultado";
        $from = "resultadorespuestatrabajoinspeccionresultado rti, resultadorespuestatrabajo rt, inspeccionresultado ir";
        $where = "rt.resultadorespuestatrabajo_id= rti.compositor 
                AND rti.compuesto = ir.inspeccionresultado_id 
                AND ir.inspeccionresultado_id = ". $inspeccion["inspeccionresultado_id"];;
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);
        if (is_array($col)) {
            foreach ($col as $resultadotrabajo) {
                $resultadotrabajo_collection[]=$resultadotrabajo;
            }
        }
        $inspeccion['resultadorespuestatrabajo_collection'] = $resultadotrabajo_collection;
        
        $archivo_collection=array();
        $archivo_collection1=array();
        $select = "a.archivo_id, a.denominacion, a.url, a.fecha_carga, a.formato, ai.compuesto inspeccionresultado";
        $from = "archivoinspeccionresultado ai, archivo a, inspeccionresultado ir";
        $where = "a.archivo_id= ai.compositor
                AND ai.compuesto = ir.inspeccionresultado_id
                AND ir.estado_inspeccion = 1
                AND ir.inspeccionresultado_id=" . $inspeccion["inspeccionresultado_id"];
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);

        if (is_array($col)) {
            foreach ($col as $archivo) {
                $archivo_collection[] = $archivo;
            }

            foreach ($archivo_collection as $archivo) {
                $id = $inspeccion["inspeccionresultado_id"];
                $carpeta = URL_APPFILES . 'inspeccionresultado/' . $id;
                $nombre_archivo = $archivo["denominacion"];

                if (file_exists($carpeta . '/' . $nombre_archivo)) {
                    $im = file_get_contents($carpeta . '/' . $nombre_archivo);
                    $data = base64_encode($im);
                    $archivo["archivo"] = $data;
                }
                $archivo_collection1[]=$archivo;
            }
        }


        $inspeccion['archivo_collection'] = $archivo_collection1;

        $inspeccion_collection1[] = $inspeccion;
        
    }

    $data = array();
    $data ["accion"] = "vaciar";
    $ch = curl_init("https://www.edelar.com.ar/api_geco_desa/helpers/inspeccionresultado/resultadoinspeccion_sincro.php");
    curl_setopt($ch, CURLOPT_TIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120000);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response == 0) {        
        if (!empty($inspeccion_collection1)) {
            $data = array();
            $data ["accion"] = "recibir";
            $data ["datos"] = json_encode(array('resultados' => $inspeccion_collection1, JSON_UNESCAPED_UNICODE));
            //url contra la que atacamos
            $ch = curl_init("https://www.edelar.com.ar/api_geco_desa/helpers/inspeccionresultado/resultadoinspeccion_sincro.php");
            //a true, obtendremos una respuesta de la url, en otro caso,
            //true si es correcto, false si no lo es
            curl_setopt($ch, CURLOPT_TIMEOUT, 120000);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120000);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //establecemos el verbo http que queremos utilizar para la petición
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //enviamos el array data
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            //obtenemos la respuesta
            $response = curl_exec($ch);
            //$json = json_encode(array('resultados' => $inspeccion_collection1, JSON_UNESCAPED_UNICODE));
            //print_r($response);exit;
            // Se cierra el recurso CURL y se liberan los recursos del sistema
            curl_close($ch);
            if ($response == 0) {                
                $error = 0;
            } else {
                $error = 1;
            }
        }
    } else {
        $error = 1;
    }
    if ($print) {
        print_r($error);
    } else {
        return $error;
    }
}

function sincronizar(){
    $error=1;
    $respuestaRecibir=recibir(FALSE);
    if($respuestaRecibir==0){ 
        
        $respuestaEnviar=enviar(FALSE);
        if($respuestaEnviar==0){  
            
            $respuestaTareas=sincro_tareas();
            if($respuestaTareas==0){  
                $error=0;
            }
        }
    }
    print_r($error);
}
?>
