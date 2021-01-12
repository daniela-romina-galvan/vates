<?php
require_once "modules/inspeccionresultado/model.php";


class InspeccionTareaEnEjecucion extends StandardObject {

	function __construct(Inspeccionresultado $inspeccionresultado=NULL) {
		$this->inspecciontareaenejecucion_id = 0;
    	$this->tarea = "";
    	$this->numero_tarea = "";
		$this->numero_remito = "";
		$this->lugar = "";
    	$this->unidad_car = "";
    	$this->inspeccionresultado = $inspeccionresultado;
	}
}
?>
