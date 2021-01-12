<?php


abstract class View {


    function render_template($contenido) {

        $configuracionmenu = $_SESSION["data-login-" . APP_ABREV]["usuario-configuracionmenu"];
        $sidebar = $this->render_menu($configuracionmenu);
        $dict = array("{app_nombre}"=>APP_TITTLE,
                      "{app_version}"=>APP_VERSION,
                      "{url_static}"=>URL_STATIC,
                      "{sidebar-menu}"=>$sidebar,
                      "{app_footer}"=>APP_TITTLE . " " . date("Y"),
                      "{usuariodetalle-nombre}"=>$_SESSION["data-login-" . APP_ABREV]["usuariodetalle-nombre"],
                      "{usuariodetalle-apellido}"=>$_SESSION["data-login-" . APP_ABREV]["usuariodetalle-apellido"],
                      "{usuario-denominacion}"=>$_SESSION["data-login-" . APP_ABREV]["usuario-denominacion"],
                      "{nivel-denominacion}"=>$_SESSION["data-login-" . APP_ABREV]["nivel-denominacion"],
                      "{contenido}"=>$contenido);

        $post_dict = array("{url_app}"=>URL_APP, "{url_static}"=>URL_STATIC);
        $plantilla = file_get_contents(TEMPLATE);
        $plantilla = $this->render($dict, $plantilla);
        $plantilla = $this->render($post_dict, $plantilla);
        return $plantilla;
    }


    function render($dict, $html) {
        $render = str_replace(array_keys($dict), array_values($dict), $html);
        return $render;
    }

    function get_regex($tag, $html) {
        $pcre_limit = ini_set("pcre.recursion_limit", 10000);
        $regex = "/<!--$tag-->(.|\n){1,}<!--$tag-->/";
        preg_match($regex, $html, $coincidencias);
        ini_set("pcre.recursion_limit", $pcre_limit);
        return $coincidencias[0];
    }

    function render_regex($tag, $base, $coleccion) {
        $render = '';
        $codigo = $this->get_regex($tag, $base);
        $coleccion = $this->set_collection_dict($coleccion);
        foreach($coleccion as $dict) {
            $render .= $this->render($dict, $codigo);
        }
        $render_final = str_replace($codigo, $render, $base);
        return $render_final;
    }

    function render_regex_dict($tag, $base, $coleccion) {
        $render = '';
        $codigo = $this->get_regex($tag, $base);
        if (!empty($coleccion)) {
            foreach($coleccion as $dict) {
                $render .= $this->render($dict, $codigo);
            }
        } else {
            $render = "<center><strong>No hay registros para mostrar!</strong></center>";
        }

        $base = str_replace($codigo, $render, $base);
        return $base;
    }

    function set_dict($obj) {
        $new_dict = array();
        foreach($obj as $clave=>$valor) {
            if (is_object($valor)) {
                $new_dict = array_merge($new_dict, $this->set_dict($valor));
            } else {
                $name_object = strtolower(get_class($obj));
                $new_dict["{{$name_object}-{$clave}}"] = $valor;
            }
        }
        return $new_dict;
    }

    function set_collection_dict($collection) {
        $new_array = array();
        foreach($collection as $obj) $new_array[] = $this->set_dict($obj);
        return $new_array;
    }

}
?>
