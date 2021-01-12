<?php
class Collector {
    public $coleccion = array();

    public function get($objeto) {
        $modelo = ucwords($objeto);
        $tabla = strtolower($objeto);
        $pid = "{$tabla}_id";

        $sql = "SELECT $pid from $tabla";
        $resultados = execute_query($sql);
        if ($resultados != 0) {
            foreach($resultados as $arrasoc) {
                $obj = new $modelo();
                $obj->$pid = $arrasoc[$pid];
                $obj->get();
                $this->coleccion[] = $obj;
            }
        }
        return $this->coleccion;
    }
}

function Collector() { return new Collector(); }