<?php
require_once 'core/helpers/object_parser.php';


abstract class StandardObject {

    function save() {
        $propiedad_id = ObjectParser($this)->propiedad_id;
        $tabla = ObjectParser($this)->tabla;

        if(!$this->$propiedad_id) {
            $string_propiedades_insert = ObjectParser($this)->insert_string;
            $string_sustituciones_insert = ObjectParser($this)->sustituciones_string;
            
            $sql = "INSERT INTO $tabla ($string_propiedades_insert) VALUES ($string_sustituciones_insert)";
            $datos = ObjectParser($this)->valores_sustituciones;
            $this->$propiedad_id = execute_query($sql, $datos);
        } else {
            $string_propiedades_update = ObjectParser($this)->update_string;
            
            $sql = "UPDATE $tabla SET $string_propiedades_update WHERE $propiedad_id = ?";
            $datos = ObjectParser($this)->valores_sustituciones;
            array_push($datos, $this->$propiedad_id);
            execute_query($sql, $datos);
        }
    }

    function get() {
        $tabla = ObjectParser($this)->tabla;
        $propiedad_id = ObjectParser($this)->propiedad_id;
        $propiedades = ObjectParser($this)->string_campos;

        $sql = "SELECT $propiedades FROM $tabla WHERE $propiedad_id = ?";
        $datos = array($this->$propiedad_id);
        $result = execute_query($sql, $datos);

        if ($result[0]) {
            foreach ($result[0] as $propiedad=>$valor) {
                if(is_null($this->$propiedad) && !is_null($valor)) {
                    $this->$propiedad = $this->set_composite($propiedad, $valor);
                } else {
                    $this->$propiedad = $valor;
                }
            }
        }
            $this->set_collections();
    }

    function delete() {
        $tabla = ObjectParser($this)->tabla;
        $propiedad_id = ObjectParser($this)->propiedad_id;

        $sql = "DELETE FROM $tabla WHERE $propiedad_id = ?";
        $datos = array($this->$propiedad_id);
        execute_query($sql, $datos);
    }

    private function set_collections() {
        $compuesto_nombre = ucwords(ObjectParser($this)->tabla);
        $propiedades = get_object_vars($this);
        $propiedad_id = ObjectParser($this)->propiedad_id;

        foreach($propiedades as $nombre=>$valor) {
            if(is_array($valor)) {
                $compositor_nombre = str_replace("_collection", "", $nombre);
                    
                $cls_nombre = "{$compositor_nombre}{$compuesto_nombre}";
                if (!class_exists($cls_nombre)) {
                    $cls_nombre = ucwords("{$compositor_nombre}");
                    $mtd_nombre = strtolower("get_id_from_{$compuesto_nombre}");
                    $mtd_agregacion = "add_{$compositor_nombre}";
                    $compositor_id = "{$compositor_nombre}_id";

                    $tmp_objetos = $cls_nombre::$mtd_nombre($this->$propiedad_id);
                    if ($tmp_objetos) {
                        foreach($tmp_objetos as $tmp_objeto) {
                            $pc = new $cls_nombre();
                            $pc->$compositor_id = $tmp_objeto["{$compositor_id}"];
                            $pc->get();
                            $this->$mtd_agregacion($pc);
                        }
                    }
                } else {
                    $coleccion = new $cls_nombre($this);
                    $coleccion->get();
                }
            }
        }         
    }

    private function set_composite($compositor, $compositor_valor) {
        $cls_nombre = ucwords($compositor);
        $propiedad_id = "{$compositor}_id";

        $compositor = new $cls_nombre();
        $compositor->$propiedad_id = $compositor_valor;
        $compositor->get(); 
        return $compositor;
    }
}
?>