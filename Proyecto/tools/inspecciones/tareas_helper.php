<?php
require_once 'core/collector.php';
require_once 'core/collector_condition.php';
require_once 'tools/inspecciones/resultadoinspeccion_helpers.php';
function sincro_tareas ($print=FALSE, $inspector=NULL){
    $error=1;
    if (recibir(FALSE) == 0) {
        
        $tarea_inspector_collection = array();
        $select = "compuesto, compositor";
        $from = "tareainspector";
        $where=NULL;
        if($inspector!=NULL) $where="compuesto = ".$inspector;
        $col = CollectorCondition()->get(NULL, $where, 4, $from, $select);

        //foreach ($col as $tareainspector) {
            //$tarea_inspector;
            //$tarea_inspector->compuesto = $tareainspector->compuesto;
            //$tarea_inspector->compositor = $tareainspector->compositor;
            //$tarea_inspector_collection[] = $tarea_inspector;
        //}
        //print_r($tarea_inspector_collection);exit();
        
        $data = array();
        $data ["accion"] = "actualizar";
        $data ["tarea"] = json_encode(array('resultados' => $col, JSON_UNESCAPED_UNICODE));
        
        $ch = curl_init("https://www.edelar.com.ar/api_geco_desa/helpers/tarea/tarea_helper.php");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120000);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        //print_r($response);exit;
        curl_close($ch);
        $error = ($response == 0) ? $error = 0 : $error = 1;
    } else {
        $error = 0;
    }
    
    if ($print) {
        print_r($error);
    } else {
        return $error;
    }
}
?>
