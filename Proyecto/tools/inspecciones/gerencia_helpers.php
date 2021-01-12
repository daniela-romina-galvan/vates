<?php
require_once 'core/collector.php';
require_once 'core/collector_condition.php';

require_once 'modules/inspeccionresultado/model.php';
require_once 'modules/resultadorespuesta/model.php';
require_once 'modules/vehiculo/model.php';
require_once 'modules/legajopersonal/model.php';
require_once 'modules/archivo/model.php';
function recibir (){
            $data = array();
            $data ["accion"]="obtener";
            $ch = curl_init("https://url/gerencia_sincro.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
            $response = curl_exec($ch);
            curl_close($ch);
            $gerencias = json_decode($response);
            $error=0;
            foreach ($gerencias->resultados as $gerencia ) {
                $id = $gerencia->id;
                $denominacion = $gerencia->denominacion;
                $g = new Gerencia();
                $g->id = $id;
                $g->denominacion = $denominacion;
                $g->save();
            }
}

function enviar(){
        $col= Collector()->get("Gerencia");
        if($col>0){
			$data = array();
			$data ["accion"]="vaciar";
            //url contra la que atacamos
			$ch = curl_init("https://url/gerencia_sincro.php");
            //a true, obtendremos una respuesta de la url, en otro caso,
            //true si es correcto, false si no lo es
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //establecemos el verbo http que queremos utilizar para la petición
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //enviamos el array data
			curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
            //obtenemos la respuesta
			$response = curl_exec($ch);
            // Se cierra el recurso CURL y se liberan los recursos del sistema
			curl_close($ch);
        if($response==0){
            $data = array();
            $data ["accion"]="recibir";
            $data ["datos"]=json_encode(array('resultados' => $col, JSON_UNESCAPED_UNICODE));
            //url contra la que atacamos
            $ch = curl_init("https://url/gerencia_sincro.php");
            //a true, obtendremos una respuesta de la url, en otro caso,
            //true si es correcto, false si no lo es
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //establecemos el verbo http que queremos utilizar para la petición
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            //enviamos el array data
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
            //obtenemos la respuesta
            $response = curl_exec($ch);
            // Se cierra el recurso CURL y se liberan los recursos del sistema
            curl_close($ch);
			}
		}
    }
?>
