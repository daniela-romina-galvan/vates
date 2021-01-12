<?php


class InspeccionTareaEnEjecucionView extends View {

	function panel($inspeccion_collection, $inspeccionlegajo_collection, $inspeccionvehiculo_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/panel.html");
		$gui_tbl_inspeccion = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_inspeccion.html");
		$gui_tbl_inspeccionlegajo = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_inspeccionlegajo.html");
		$gui_tbl_inspeccionvehiculo = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_inspeccionvehiculo.html");
		$gui_tbl_inspeccion = $this->render_regex_dict('TBL_INSPECCION', $gui_tbl_inspeccion, $inspeccion_collection);
		$gui_tbl_inspeccionlegajo = $this->render_regex_dict('TBL_INSPECCIONLEGAJO', $gui_tbl_inspeccionlegajo, $inspeccionlegajo_collection);
		$gui_tbl_inspeccionvehiculo = $this->render_regex_dict('TBL_INSPECCIONVEHICULO', $gui_tbl_inspeccionvehiculo, $inspeccionvehiculo_collection);

		$render = str_replace('{tbl_inspeccion}', $gui_tbl_inspeccion, $gui);
		$render = str_replace('{tbl_inspeccionlegajo}', $gui_tbl_inspeccionlegajo, $render);
		$render = str_replace('{tbl_inspeccionvehiculo}', $gui_tbl_inspeccionvehiculo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function ingresar($legajopersonal_collection, $inspecciontipo_collection, $inspector_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/ingresar.html");
		$gui_slt_inspecciontipo = file_get_contents("static/modules/inspeccionresultado/slt_inspecciontipo.html");
		$gui_slt_inspector = file_get_contents("static/modules/inspeccionresultado/slt_inspector.html");
		$gui_tbl_legajopersonal = file_get_contents("static/modules/inspeccionresultado/tbl_chk_legajopersonal_array.html");

		foreach ($inspecciontipo_collection as $clave=>$valor) unset($inspecciontipo_collection[$clave]->grupocuestionario_collection);
		foreach ($inspecciontipo_collection as $clave=>$valor) $valor->selected = '';
		foreach ($inspector_collection as $clave=>$valor) unset($inspector_collection[$clave]->tarea_collection);
		foreach ($inspector_collection as $clave=>$valor) $valor->selected = '';

		$gui_slt_inspecciontipo = $this->render_regex('SLT_INSPECCIONTIPO', $gui_slt_inspecciontipo, $inspecciontipo_collection);
		$gui_slt_inspector = $this->render_regex('SLT_INSPECTOR', $gui_slt_inspector, $inspector_collection);
		$gui_tbl_legajopersonal = $this->render_regex_dict('TBL_LEGAJOPERSONAL', $gui_tbl_legajopersonal, $legajopersonal_collection);

		$render = str_replace('{slt_inspecciontipo}', $gui_slt_inspecciontipo, $gui);
		$render = str_replace('{slt_inspector}', $gui_slt_inspector, $render);
		$render = str_replace('{fecha_sys}', date('Y-m-d'), $render);
		$render = str_replace('{hora_sys}', date('H:i:s'), $render);
		$render = str_replace('{tbl_legajopersonal}', $gui_tbl_legajopersonal, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function seleccionar_integrante($obj_inspeccionresultado, $itm, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/seleccionar_integrante.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/inspecciontareaenejecucion_array.html");
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$obj_inspector = $obj_inspeccionresultado->inspector;
		$obj_responsable = (!empty($obj_inspeccionresultado->legajopersonal)) ? $obj_inspeccionresultado->legajopersonal : '' ;
		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspeccionresultado->legajopersonal,
		$obj_inspeccionresultado->resultadorespuesta_collection, $obj_inspeccionresultado->inspector,
		$obj_inspeccionresultado->archivo_collection );
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_denominacion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'PENDIENTE' : 'CERRADA';
		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);

		$render = $this->render($obj_inspeccionresultado, $gui);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function p1_legajopersonal($legajopersonal_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p1_legajopersonal.html");
		$tbl_legajo = file_get_contents("static/modules/inspeccionresultado/tbl_short_option_legajo.html");
		$tbl_legajo = $this->render_regex_dict('TBL_LEGAJO', $tbl_legajo, $legajopersonal_collection);

		$render = str_replace('{tbl_short_option_legajo}', $tbl_legajo, $gui);
		$render = str_replace('{url_app}', URL_APP, $render);
		$render = str_replace('{url_static}', URL_STATIC, $render);
		$render = $this->render_breadcrumb($render);
		print $render;
	}

	function p2_legajopersonal($obj_inspeccionresultado, $obj_legajopersonal) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p2_legajopersonal.html");
		$lst_grupales = file_get_contents("static/modules/inspeccionresultado/lst_grupales.html");

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;

		$lst_grupocuestionario = file_get_contents("static/modules/inspeccionresultado/lst_grupocuestionario.html");
        $render_grupocuestionario = '';
        $cod_grupocuestionario = $this->get_regex('LST_GRUPOCUESTIONARIO', $lst_grupocuestionario);
        foreach ($grupocuestionario_collection as $dict_grupocuestionario) {
					$grupocuestionario_id = $dict_grupocuestionario->grupocuestionario_id;
        	$dict_grupocuestionario->active = ($dict_grupocuestionario === reset($grupocuestionario_collection)) ? 'in' : '';
            $itemcuestionario_collection = $dict_grupocuestionario->itemcuestionario_collection;
            unset($dict_grupocuestionario->itemcuestionario_collection);
            $dict_grupocuestionario = $this->set_dict($dict_grupocuestionario);
            $btn_grupocuestionario = $this->render($dict_grupocuestionario, $cod_grupocuestionario);

			$lst_itemcuestionario = file_get_contents("static/modules/inspeccionresultado/lst_itemcuestionario.html");
            $cod_btn_itemcuestionario = $this->get_regex('LST_ITEMCUESTIONARIO', $lst_itemcuestionario);
            $render_itemcuestionario = '';

            foreach($itemcuestionario_collection as $dict) {
                $itemcuestionario_id = $dict->itemcuestionario_id;
                $respuestacuestionario_collection = $dict->respuestacuestionario_collection;

                unset($dict->respuestacuestionario_collection);

                $dict = $this->set_dict($dict);
                $btn_itemcuestionario = $this->render($dict, $cod_btn_itemcuestionario);

				$lst_respuestacuestionario = file_get_contents("static/modules/inspeccionresultado/lst_respuestacuestionario.html");
                $cod_btn_respuesta = $this->get_regex('LST_RESPUESTACUESTIONARIO', $lst_respuestacuestionario);
                $render_respuestacuestionario = '';
                $respuestacuestionario_collection = $this->order_collection_objects($respuestacuestionario_collection, 'orden', SORT_ASC);
                $respuestacuestionario_collection = $this->set_collection_dict($respuestacuestionario_collection);
                foreach ($respuestacuestionario_collection as $clave=>$valor) {

										$cod_btn_respuesta = str_replace('{grupocuestionario-grupocuestionario_id}', $grupocuestionario_id, $cod_btn_respuesta);
										$render_respuestacuestionario .= $this->render($valor, $cod_btn_respuesta);
                }

                $render_respuestacuestionario = str_replace('{itemcuestionario-itemcuestionario_id}', $itemcuestionario_id, $render_respuestacuestionario);
                $btn_itemcuestionario = str_replace('{lst_respuestacuestionario}', $render_respuestacuestionario, $btn_itemcuestionario);
                $render_itemcuestionario .= $btn_itemcuestionario;
            }

			$btn_grupocuestionario = str_replace('{lst_itemcuestionario}', $render_itemcuestionario, $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{lst_grupales}',$lst_grupales , $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{grupocuestionario-grupocuestionario_id}',$grupocuestionario_id , $btn_grupocuestionario);

			$render_grupocuestionario .= $btn_grupocuestionario;
        }

        $gui = str_replace('{lst_grupocuestionario}', $render_grupocuestionario, $gui);
        $gui = str_replace('{url_app}', URL_APP, $gui);
        print $gui;
	}

	function p1_vehiculo($vehiculo_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p1_vehiculo.html");
		$tbl_vehiculo = file_get_contents("static/modules/inspeccionresultado/tbl_short_option_vehiculo.html");
		$tbl_vehiculo = $this->render_regex_dict('TBL_VEHICULO', $tbl_vehiculo, $vehiculo_collection);

		$render = str_replace('{tbl_short_option_vehiculo}', $tbl_vehiculo, $gui);
		$render = str_replace('{url_app}', URL_APP, $render);
		$render = str_replace('{url_static}', URL_STATIC, $render);
		$render = $this->render_breadcrumb($render);
		print $render;
	}

	function p2_vehiculo($obj_inspeccionresultado, $obj_vehiculo) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p2_vehiculo.html");
        $gui = str_replace('{url_app}', URL_APP, $gui);
        print $gui;
	}

	function agregar_integrante($obj_inspeccionresultado, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/agregar_integrante.html");
		$gui_tbl_resultadorespuesta = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_resultadorespuesta.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_consultar.html");
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			unset($obj_responsable->inspeccionresultado_collection);
		}else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
				$obj_inspeccionresultado->inspector,$obj_inspeccionresultado->archivo_collection);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_inspeccion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'Pendiente' : 'Cerrada';
		unset($obj_inspector->tarea_collection);

		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);

		$render = str_replace("{responsable-denominacion}", $responsable_denominacion, $gui);
		$render = str_replace("{responsable-responsable_id}", $responsable_id, $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function p2_legajopersonal_agregar($obj_inspeccionresultado, $obj_legajopersonal) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p2_legajopersonal_agregar.html");
		$lst_grupales = file_get_contents("static/modules/inspeccionresultado/lst_grupales.html");

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;

		$lst_grupocuestionario = file_get_contents("static/modules/inspeccionresultado/lst_grupocuestionario.html");
        $render_grupocuestionario = '';
        $cod_grupocuestionario = $this->get_regex('LST_GRUPOCUESTIONARIO', $lst_grupocuestionario);
        foreach ($grupocuestionario_collection as $dict_grupocuestionario) {
					$grupocuestionario_id = $dict_grupocuestionario->grupocuestionario_id;
        	$dict_grupocuestionario->active = ($dict_grupocuestionario === reset($grupocuestionario_collection)) ? 'in' : '';
            $itemcuestionario_collection = $dict_grupocuestionario->itemcuestionario_collection;
            unset($dict_grupocuestionario->itemcuestionario_collection);
            $dict_grupocuestionario = $this->set_dict($dict_grupocuestionario);
            $btn_grupocuestionario = $this->render($dict_grupocuestionario, $cod_grupocuestionario);

			$lst_itemcuestionario = file_get_contents("static/modules/inspeccionresultado/lst_itemcuestionario.html");
            $cod_btn_itemcuestionario = $this->get_regex('LST_ITEMCUESTIONARIO', $lst_itemcuestionario);
            $render_itemcuestionario = '';
            foreach($itemcuestionario_collection as $dict) {
                $itemcuestionario_id = $dict->itemcuestionario_id;
                $respuestacuestionario_collection = $dict->respuestacuestionario_collection;
                unset($dict->respuestacuestionario_collection);

                $dict = $this->set_dict($dict);
                $btn_itemcuestionario = $this->render($dict, $cod_btn_itemcuestionario);

				$lst_respuestacuestionario = file_get_contents("static/modules/inspeccionresultado/lst_respuestacuestionario.html");
                $cod_btn_respuesta = $this->get_regex('LST_RESPUESTACUESTIONARIO', $lst_respuestacuestionario);
                $render_respuestacuestionario = '';
                $respuestacuestionario_collection = $this->order_collection_objects($respuestacuestionario_collection, 'orden', SORT_ASC);
                $respuestacuestionario_collection = $this->set_collection_dict($respuestacuestionario_collection);
                foreach ($respuestacuestionario_collection as $clave=>$valor) {

										$cod_btn_respuesta = str_replace('{grupocuestionario-grupocuestionario_id}', $grupocuestionario_id, $cod_btn_respuesta);
										$render_respuestacuestionario .= $this->render($valor, $cod_btn_respuesta);
                }

                $render_respuestacuestionario = str_replace('{itemcuestionario-itemcuestionario_id}', $itemcuestionario_id, $render_respuestacuestionario);
                $btn_itemcuestionario = str_replace('{lst_respuestacuestionario}', $render_respuestacuestionario, $btn_itemcuestionario);
                $render_itemcuestionario .= $btn_itemcuestionario;
            }

			$btn_grupocuestionario = str_replace('{lst_itemcuestionario}', $render_itemcuestionario, $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{lst_grupales}',$lst_grupales , $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{grupocuestionario-grupocuestionario_id}',$grupocuestionario_id , $btn_grupocuestionario);

			$render_grupocuestionario .= $btn_grupocuestionario;
        }

        $gui = str_replace('{lst_grupocuestionario}', $render_grupocuestionario, $gui);
        $gui = str_replace('{url_app}', URL_APP, $gui);
        print $gui;
	}

	function p2_vehiculo_agregar($obj_inspeccionresultado, $obj_vehiculo) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/p2_vehiculo_agregar.html");
		$gui = str_replace('{url_app}', URL_APP, $gui);
		print $gui;
	}

	function consulta_grupal($obj_inspeccionresultado, $inspeccion_collection, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/consulta_grupal.html");
		$gui_tbl_resultadorespuesta = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_resultadogeneral.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_consultar.html");
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			unset($obj_responsable->inspeccionresultado_collection);
		} else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector, $obj_inspeccionresultado->archivo_collection);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_denominacion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'PENDIENTE' : 'CERRADA';
		$obj_inspeccionresultado->btn_editar = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'block' : 'none';

		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$gui_tbl_resultadorespuesta = $this->render_regex_dict('TBL_RESULTADORESPUESTA', $gui_tbl_resultadorespuesta, $inspeccion_collection);

		$render = str_replace("{tbl_resultado}", $gui_tbl_resultadorespuesta, $gui);
		$render = str_replace("{responsable-denominacion}", $responsable_denominacion, $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function consulta_personal($obj_inspeccionresultado, $obj_legajopersonal,$inspecciontareaenejecucion_collection,$archivoinspeccionresultado_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/consulta_personal.html");
		$gui_tbl_resultadorespuesta = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_resultadorespuesta.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_consultar.html");
		$gui_ls_archivoinspeccionresultado = file_get_contents("static/modules/inspecciontareaenejecucion/ls_archivoinspeccionresultado.html");
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;
		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_id = $obj_responsable->legajopersonal_id;
			$responsable_denominacion = $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			unset($obj_responsable->inspeccionresultado_collection);
		} else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector,$obj_inspeccionresultado->archivo_collection);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_denominacion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'PENDIENTE' : 'CERRADA';
                $obj_inspeccionresultado->btn_editar = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'block' : 'none';

		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$obj_legajopersonal = $this->set_dict($obj_legajopersonal);

		if (is_array($archivoinspeccionresultado_collection)) {
			$url_buttom = $archivoinspeccionresultado_collection[0]['INSPECCIONRESULTADOID'].'_'.$archivoinspeccionresultado_collection[0]['URL'];
			unset($archivoinspeccionresultado_collection[0]);
			$gui_ls_archivoinspeccionresultado = $this->render_regex_dict('LST_ARCHIVOINSPECCIONRESULTADO', $gui_ls_archivoinspeccionresultado, $archivoinspeccionresultado_collection);
			$option_buttom = 'block';
 		}else {
			$option_buttom = 'none';
			$url_buttom = '';
 		}

		if (!empty($archivoinspeccionresultado_collection)) {
			$gui = str_replace('{ls_archivoinspeccionresultado}', $gui_ls_archivoinspeccionresultado, $gui);
		}else {
			$gui = str_replace('{ls_archivoinspeccionresultado}', '', $gui);
		}


		$gui_tbl_resultadorespuesta = $this->render_regex('TBL_RESULTADORESPUESTA', $gui_tbl_resultadorespuesta, $resultadorespuesta_collection);

		$render = str_replace("{tbl_resultadorespuesta}", $gui_tbl_resultadorespuesta, $gui);
		$render = str_replace("{responsable-denominacion}", $responsable_denominacion, $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		// $render = str_replace('{ls_archivoinspeccionresultado}', $gui_ls_archivoinspeccionresultado, $render);
		$render = str_replace('{option-buttom}', $option_buttom, $render);
		$render = str_replace('{url-buttom}', $url_buttom, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render($obj_legajopersonal, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function consulta_vehicular($obj_inspeccionresultado, $obj_vehiculo, $inspecciontareaenejecucion_collection,$archivoinspeccionresultado_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/consulta_vehicular.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_consultar.html");
		$gui_ls_archivoinspeccionresultado = file_get_contents("static/modules/inspecciontareaenejecucion/ls_archivoinspeccionresultado.html");

		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			unset($obj_responsable->inspeccionresultado_collection);
		} else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector, $obj_inspeccionresultado->archivo_collection);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_denominacion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'PENDIENTE' : 'CERRADA';
                $obj_inspeccionresultado->btn_editar = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'block' : 'none';

		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$obj_vehiculo = $this->set_dict($obj_vehiculo);

		if (is_array($archivoinspeccionresultado_collection)) {
			$url_buttom = $archivoinspeccionresultado_collection[0]['INSPECCIONRESULTADOID'].'_'.$archivoinspeccionresultado_collection[0]['URL'];
			unset($archivoinspeccionresultado_collection[0]);
			$gui_ls_archivoinspeccionresultado = $this->render_regex_dict('LST_ARCHIVOINSPECCIONRESULTADO', $gui_ls_archivoinspeccionresultado, $archivoinspeccionresultado_collection);
			$option_buttom = 'block';
		}else {
			$option_buttom = 'none';
			$url_buttom = '';
		}

		if (!empty($archivoinspeccionresultado_collection)) {
			$gui = str_replace('{ls_archivoinspeccionresultado}', $gui_ls_archivoinspeccionresultado, $gui);
		}else {
			$gui = str_replace('{ls_archivoinspeccionresultado}', '', $gui);
		}


		$render = str_replace("{responsable-denominacion}", $responsable_denominacion, $gui);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		// $render = str_replace('{ls_archivoinspeccionresultado}', $gui_ls_archivoinspeccionresultado, $render);
		$render = str_replace('{option-buttom}', $option_buttom, $render);
		$render = str_replace('{url-buttom}', $url_buttom, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render($obj_vehiculo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function editar_personal($legajopersonal_collection, $obj_inspeccionresultado, $obj_legajopersonal, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/editar_personal.html");
		$gui_slt_responsable = file_get_contents("static/common/slt_legajopersonal_array.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_inspecciontareaenejecucion_array_readonly.html");
		$gui_slt_responsable = $this->render_regex_dict('SLT_LEGAJOPERSONAL', $gui_slt_responsable, $legajopersonal_collection);
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);
		$inspecciontareaenejecucion_id = $inspecciontareaenejecucion_collection[0]['inspecciontareaenejecucion_id'];

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->num_legajo . ' - ' . $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			$responsable_denominacion = $obj_responsable->apellido.' '.$obj_responsable->nombre;
		} else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector, $obj_inspeccionresultado->archivo_collection);

		$editar_cuestionario = $this->editar_cuestionario_personal($resultadorespuesta_collection, $obj_inspeccionresultado, $obj_legajopersonal);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection, $obj_inspector->tarea_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;

		$integrante_inspeccion = strtoupper($obj_legajopersonal->apellido . ' ' . $obj_legajopersonal->nombre);
                //print_r($obj_inspector);exit;
		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$render = str_replace('{slt_responsable}', $gui_slt_responsable, $gui);
		$render = str_replace('{responsable-denominacion}', $responsable_denominacion, $render);
		$render = str_replace('{responsable-responsable_id}', $responsable_id, $render);
		$render = str_replace('{cuestionario}', $editar_cuestionario, $render);
		$render = str_replace('{legajopersonal-legajopersonal_id}', $obj_legajopersonal->legajopersonal_id, $render);
		$render = str_replace('{integrante}', $integrante_inspeccion, $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = str_replace('{inspecciontareaenejecucion_id}', $inspecciontareaenejecucion_id, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function editar_cuestionario_personal($respuestacuestionario_collection, $obj_inspeccionresultado, $obj_legajopersonal) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/editar_cuestionario_personal.html");
		$lst_grupales = file_get_contents("static/modules/inspeccionresultado/lst_grupales.html");

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;

		$lst_grupocuestionario = file_get_contents("static/modules/inspeccionresultado/lst_grupocuestionario.html");
	    $render_grupocuestionario = '';
	    $cod_grupocuestionario = $this->get_regex('LST_GRUPOCUESTIONARIO', $lst_grupocuestionario);

    	foreach ($grupocuestionario_collection as $dict_grupocuestionario) {
			$grupocuestionario_id = $dict_grupocuestionario->grupocuestionario_id;
    		$dict_grupocuestionario->active = ($dict_grupocuestionario === reset($grupocuestionario_collection)) ? 'in' : '';
     		$itemcuestionario_collection = $dict_grupocuestionario->itemcuestionario_collection;
            unset($dict_grupocuestionario->itemcuestionario_collection);
            $dict_grupocuestionario = $this->set_dict($dict_grupocuestionario);
            $btn_grupocuestionario = $this->render($dict_grupocuestionario, $cod_grupocuestionario);

	      	$lst_itemcuestionario = file_get_contents("static/modules/inspeccionresultado/lst_itemcuestionario_editar.html");
            $cod_btn_itemcuestionario = $this->get_regex('LST_ITEMCUESTIONARIO', $lst_itemcuestionario);
            $render_itemcuestionario = '';
            foreach($itemcuestionario_collection as $dict) {
                $itemcuestionario_id = $dict->itemcuestionario_id;
                $respuestacuestionario_collection = $dict->respuestacuestionario_collection;
                unset($dict->respuestacuestionario_collection);

                $dict = $this->set_dict($dict);
                $btn_itemcuestionario = $this->render($dict, $cod_btn_itemcuestionario);

        		$lst_respuestacuestionario = file_get_contents("static/modules/inspeccionresultado/lst_respuestacuestionario_editar.html");
              	$cod_btn_respuesta = $this->get_regex('LST_RESPUESTACUESTIONARIO', $lst_respuestacuestionario);
                $render_respuestacuestionario = '';

                $respuestacuestionario_collection = $this->order_collection_objects($respuestacuestionario_collection, 'orden', SORT_ASC);
                $respuestacuestionario_collection = $this->set_collection_dict($respuestacuestionario_collection);
                foreach ($respuestacuestionario_collection as $clave=>$valor) {
					$cod_btn_respuesta = str_replace('{grupocuestionario-grupocuestionario_id}', $grupocuestionario_id, $cod_btn_respuesta);
					$render_respuestacuestionario .= $this->render($valor, $cod_btn_respuesta);
                }

                $render_respuestacuestionario = str_replace('{itemcuestionario-itemcuestionario_id}', $itemcuestionario_id, $render_respuestacuestionario);
                $btn_itemcuestionario = str_replace('{lst_respuestacuestionario}', $render_respuestacuestionario, $btn_itemcuestionario);
                $render_itemcuestionario .= $btn_itemcuestionario;
            }

            $btn_grupocuestionario = str_replace('{lst_itemcuestionario}', $render_itemcuestionario, $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{lst_grupales}',$lst_grupales , $btn_grupocuestionario);
			$btn_grupocuestionario = str_replace('{grupocuestionario-grupocuestionario_id}',$grupocuestionario_id , $btn_grupocuestionario);

			$render_grupocuestionario .= $btn_grupocuestionario;
        }

        $gui = str_replace('{lst_grupocuestionario}', $render_grupocuestionario, $gui);
        $gui = str_replace('{url_app}', URL_APP, $gui);
        return $gui;
	}

	function editar_vehicular($legajopersonal_collection, $obj_inspeccionresultado, $obj_vehiculo, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/editar_vehiculo.html");
		$gui_slt_responsable = file_get_contents("static/common/slt_legajopersonal_array.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_inspecciontareaenejecucion_array_readonly.html");
		$gui_slt_responsable = $this->render_regex_dict('SLT_LEGAJOPERSONAL', $gui_slt_responsable, $legajopersonal_collection);
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);
		$inspecciontareaenejecucion_id = $inspecciontareaenejecucion_collection[0]['inspecciontareaenejecucion_id'];

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->num_legajo . ' - ' . $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			$responsable_denominacion = $obj_responsable->apellido.' '.$obj_responsable->nombre;
		}else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}

 		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector, $obj_inspeccionresultado->archivo_collection);

 		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;

		$integrante_inspeccion = $obj_vehiculo->vehiculomodelo->vehiculomarca->denominacion . ' ';
		$integrante_inspeccion .= $obj_vehiculo->vehiculomodelo->denominacion;
		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$render = str_replace('{slt_responsable}', $gui_slt_responsable, $gui);
		$render = str_replace('{responsable-denominacion}',$responsable_denominacion, $render);
		$render = str_replace('{responsable-responsable_id}', $responsable_id, $render);
 		$render = str_replace('{vehiculo-vehiculo_id}', $obj_vehiculo->vehiculo_id, $render);
		$render = str_replace('{integrante}', strtoupper($integrante_inspeccion), $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = str_replace('{inspecciontareaenejecucion_id}', $inspecciontareaenejecucion_id, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function editar_cabecera($obj_inspeccionresultado, $inspeccion_collection, $inspecciontareaenejecucion_collection) {
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/editar_cabecera.html");
		$gui_tbl_resultadorespuesta = file_get_contents("static/modules/inspecciontareaenejecucion/tbl_resultadogeneral.html");
		$gui_slt_inspecciontareaenejecucion = file_get_contents("static/modules/inspecciontareaenejecucion/slt_inspecciontareaenejecucion_array.html");
		$gui_slt_inspecciontareaenejecucion = $this->render_regex_dict('INSPECCIONENEJECUCION', $gui_slt_inspecciontareaenejecucion, $inspecciontareaenejecucion_collection);

		$obj_inspecciontipo = $obj_inspeccionresultado->inspecciontipo;
		$grupocuestionario_collection = $obj_inspecciontipo->grupocuestionario_collection;
		$obj_inspector = $obj_inspeccionresultado->inspector;

		if(!empty($obj_inspeccionresultado->legajopersonal)){
			$obj_responsable = $obj_inspeccionresultado->legajopersonal;
			$responsable_denominacion = $obj_responsable->apellido . ' ' . $obj_responsable->nombre;
			$responsable_id = $obj_responsable->legajopersonal_id;
			unset($obj_responsable->inspeccionresultado_collection);
		}else {
			$responsable_denominacion = ' ';
			$responsable_id = ' ';
		}


		$resultadorespuesta_collection = $obj_inspeccionresultado->resultadorespuesta_collection;
		unset($obj_inspeccionresultado->legajopersonal, $obj_inspeccionresultado->resultadorespuesta_collection,
			  $obj_inspeccionresultado->inspector, $obj_inspeccionresultado->archivo_collection);
		unset($obj_inspeccionresultado->inspecciontipo, $obj_inspecciontipo->grupocuestionario_collection);
		$obj_inspeccionresultado->inspecciontipo = $obj_inspecciontipo->denominacion;
		$obj_inspeccionresultado->estado_denominacion = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'PENDIENTE' : 'CERRADA';
		$obj_inspeccionresultado->btn_editar = ($obj_inspeccionresultado->estado_inspeccion == 0) ? 'block' : 'none';

		$obj_inspeccionresultado = $this->set_dict($obj_inspeccionresultado);
		$obj_inspector = $this->set_dict($obj_inspector);
		$obj_inspecciontipo = $this->set_dict($obj_inspecciontipo);
		$gui_tbl_resultadorespuesta = $this->render_regex_dict('TBL_RESULTADORESPUESTA', $gui_tbl_resultadorespuesta, $inspeccion_collection);

		$render = str_replace("{tbl_resultado}", $gui_tbl_resultadorespuesta, $gui);
		$render = str_replace("{responsable-denominacion}", $responsable_denominacion, $render);
		$render = str_replace('{inspecciontareaenejecucion}', $gui_slt_inspecciontareaenejecucion, $render);
		$render = $this->render($obj_inspeccionresultado, $render);
		$render = $this->render($obj_inspector, $render);
		$render = $this->render($obj_inspecciontipo, $render);
		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

	function exportador($contratista_collection,$vehiculo_collection,$legajopersonal_collection){
		$gui = file_get_contents("static/modules/inspecciontareaenejecucion/exportador.html");
		$gui_slt_contratista = file_get_contents("static/modules/inspecciontareaenejecucion/slt_contratista.html");
		$gui_slt_vehiculo = file_get_contents("static/modules/inspecciontareaenejecucion/slt_vehiculo.html");
		$gui_slt_legajopersonal = file_get_contents("static/modules/inspecciontareaenejecucion/slt_legajopersonal.html");

		foreach($vehiculo_collection as $value) {
		unset($value->inspeccionresultado_collection,$value->contratista);
		}

		foreach($legajopersonal_collection as $value) {
		unset($value->inspeccionresultado_collection,$value->contratista);
		}

		$contratista_collection = $this->order_collection_objects($contratista_collection, 'denominacion', SORT_ASC);
		$vehiculo_collection = $this->order_collection_objects($vehiculo_collection, 'dominio', SORT_ASC);
		$legajopersonal_collection = $this->order_collection_objects($legajopersonal_collection, 'apellido', SORT_ASC);

		$gui_slt_contratista = $this->render_regex('SLT_CONTRATISTA', $gui_slt_contratista, $contratista_collection);
		$gui_slt_vehiculo = $this->render_regex('SLT_VEHICULO', $gui_slt_vehiculo, $vehiculo_collection);
		$gui_slt_legajopersonal = $this->render_regex('SLT_LEGAJOPERSONAL', $gui_slt_legajopersonal, $legajopersonal_collection);

 		$render = str_replace('{slt_contratista}', $gui_slt_contratista, $gui);
		$render = str_replace('{slt_vehiculo}', $gui_slt_vehiculo, $render);
		$render = str_replace('{slt_legajopersonal}', $gui_slt_legajopersonal, $render);

		$render = $this->render_breadcrumb($render);
		$template = $this->render_template($render);
		print $template;
	}

}
?>
