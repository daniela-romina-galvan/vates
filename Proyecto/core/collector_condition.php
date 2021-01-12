<?php
class CollectorCondition {
    public $coleccion = array();

    public function get($objeto='', $where=NULL, $type='', $multiple_tabla=NULL, $campos='', $group_by=FALSE) {
        $modelo = ucwords($objeto);
        $tabla = strtolower($objeto);
        $pid = "{$tabla}_id";

        switch ($type) {
            case 1:
                $sql = "SELECT {$campos} COUNT({$pid}) AS cantidad FROM ";
                if(!is_null($multiple_tabla)) {
                    $sql .= "{$tabla} tmp, {$multiple_tabla} ";
                } else {
                    $sql .= "{$tabla} ";
                }
                if(!is_null($where)) $sql .= "WHERE {$where}";
                break;
            case 2:
                $sql = "SELECT {$campos} {$pid} FROM ";
                if(!is_null($multiple_tabla)) {
                    $sql .= "{$tabla}, {$multiple_tabla} ";
                } else {
                    $sql .= "{$tabla} ";
                }
                if(!is_null($where)) $sql .= "WHERE {$where}";
                break;
            //FROM WITH INNER JOIN
            case 3:
                $sql = "SELECT {$campos} COUNT({$pid}) AS cantidad FROM ";
                if(!is_null($multiple_tabla)) {
                    $sql .= "{$tabla} tmp {$multiple_tabla} ";
                } else {
                    $sql .= "{$tabla} ";
                }
                if(!is_null($where)) $sql .= "WHERE {$where} ";
                if($group_by == TRUE) $sql .= "GROUP BY {$group_by}";
                break;
            //CLEAN SELECT, CLEAN FROM
            case 4:
                $sql = "SELECT {$campos} FROM ";
                if(!is_null($multiple_tabla)) { 
                    $sql .= "{$multiple_tabla} ";
                } else {
                    $sql .= "{$tabla} ";
                }
                if(!is_null($where)) $sql .= "WHERE {$where} ";
                if($group_by == TRUE) $sql .= "GROUP BY {$group_by}";
                break;
            //CONSULTA LIBRE    
            case 5:
            $sql = "{$where}";
                break;   
        }

        //print_r($sql);exit;
        $resultados = execute_query($sql);
        if ($type == 1 || $type == 3 || $type == 4 || $type == 5) {
            return $resultados;
        } else {
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
}

function CollectorCondition() { return new CollectorCondition(); }
