<?php

require_once('../../config.php');

$lang=optional_param('lang',"",PARAM_TEXT);

if(in_array($lang,['es','en'])){
	$CFG->lang=$lang;
}


//require_once(dirname(__FILE__).'/lib.php'); //este lib es functions_easylogin.php
require('functions_easylogin.php');
//sólo debería de desplegar el listado de grupos por escuela y la entrada al grupo



$addToastCss=false;

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_title("Pinion EasyLogin");
//$PAGE->set_url($CFG->wwwroot."/local/pinion/easylogin");
//$PAGE->requires->js("/lib/jquery/jquery-3.4.1.min.js",false); //esto quita el async pero lo pone al final
//jquery se carga por una cosa del Yui,
//borrando el  /theme/moove/javascript/scripts.js -->no tiene ningún efecto sobre el jquery creo que es algo de AMD


//NO ACEPTAR USUARIOS CON LOGIN

if (isloggedin()) {
	echo $OUTPUT->header();

	//echo "<p>You are logged in</p>";
	echo "<p>Ya estás dentro del sitio.</p>";
	echo $OUTPUT->footer();

//redireccionar a algún lugar o morir
die();
}

unset($CFG->langmenu); //esto quita el menú de idioma de la página
$GLOBALS['nousermenu']=1;
$GLOBALS['nonavdrawer']=1;

//Tomar la variable de escuela AKA slug de la escuela
//O el slug del grupo

$escuela=optional_param('escuela',"",PARAM_TEXT);
$grupo=optional_param('grupo',"",PARAM_TEXT);

//TODO TODO TODO hacer el de la escuela


if($escuela=="" && $grupo==""){
	//TODO redireccionar al login en general -->esto puede estar en las settings
	die('missing required params');
}

//TODO hacer el flavor del demo más decente
if($PinionConfig->flavor=='demo'){

	if($escuela!=""){
		$PAGE->set_url("/local/easylogin/?escuela=$escuela");
		$alumnos =pinion_easylogin_demo_get_users(strtoupper($escuela));
		
		if(sizeOf($alumnos)==0){
			echo "<p>No se encontraron usuarios registrados.</p>";
			die();
		}

		$context=[];
		$context['emptyAlumnos']=(sizeOf($alumnos) == 0 );
		if(!($context['emptyAlumnos'])){
			$first=reset($alumnos);
			$context['nombreGrupo']=$first->nombre_grupo;
			$context['cohortid']=$first->cohortid;


		}
		$color=1;
		foreach($alumnos as $k=>$a){
			//if(intval($a->suspended)==1){continue;}
			$a->hideID=base64_encode($a->uid);
			$a->color=$color;
			$context['alumnos'][]=$a;
			$color++;
			if($color>=6){
				$color=1;
			}
		}
	
		/*
		ob_start();
	
		if( (include "./easylogin_templates/grupo_demo.php") === false ){
	
			ob_get_clean();
			echo "Hubo un error inesperado";
			die();
		}
		*/
	


		$outputTemplate=ob_get_clean();

		echo $OUTPUT->header();
		local_pinion_load_pinionsend();
		$outputTemplate=$OUTPUT->render_from_template('local_easylogin/grupo',$context);
		$outputTemplate.=$OUTPUT->render_from_template('local_easylogin/grupolightbox',[]);
		
		$PAGE->requires->js_amd_inline($OUTPUT->render_from_template('local_easylogin/msgs',[]));
		$PAGE->requires->js_amd_inline($OUTPUT->render_from_template('local_easylogin/gruposcript',[]));



		echo $outputTemplate;
		
		/*TOASTS ENGINE*/
			$PAGE->requires->js_amd_inline(file_get_contents($CFG->dirroot.'/local/pinion/js/toastify.js'));
			$PAGE->requires->js_amd_inline(file_get_contents($CFG->dirroot.'/local/pinion/js/pinionToasts.js'));
			echo '<style>';
			include $CFG->dirroot.'/local/pinion/css/toastify.css';
			echo '</style>';
			$addToastCss=true;
		/*TOASTS ENGINE END*/
		
		echo $OUTPUT->footer();
	}else{
		die('missing required params');
	}
	return;
}

//echo $OUTPUT->header();

$outputTemplate="";

if($escuela!=""){
/*
	//
	//ver si es una escuela válida
	$r=pinion_get_escuela_by_slug($escuela);

	if($r===false){
		//TODO redireccionar
		die('ERROR');
	}
	
	//echo a la plantilla
	
	$escuelaId=$r->id;
	$escuelaNombre=$r->nombre;
*/
	$grupos=pinion_easylogin_get_grupos_by_slug($escuela);

	$context=[];
	$context['permalink']=$CFG->wwwroot."/local/easylogin/?";
	$context['grados']=[];
	$context['lang']=$lang;
	if(sizeOf($grupos)==0){
		$context['emptyGrupos']=true;
	}else{
		$porGrados=[];
		foreach ($grupos as $k=>$r){
			if(!isset($porGrados[$r->grado])){
				$porGrados[$r->grado]=[];
				$porGrados[$r->grado]['grupos']=[];
				if($r->grado==-1){
					$porGrados[$r->grado]['gradoLabel']="PREFIRST";
				}else{
					$porGrados[$r->grado]['gradoLabel']=$r->grado."º PRIMARIA";
				}
			}
			$nombreCohort=str_replace("{$r->clave}-","",$r->name);
			$r->nombreCohort=trim(str_replace("Primaria","",$nombreCohort));
			$porGrados[$r->grado]['grupos'][]=$r;
		}
		foreach([1,2,3] as $grado){
			if(!isset($porGrados[$grado])){continue;}
			$context['grados'][]=$porGrados[$grado];
		}
		if(isset($porGrados[-1])){
			$context['prefirst'][]=$porGrados[-1];
		}
	}

	/*
	if(sizeOf($grupos)==0){
		echo "<p>No se encontraron grupos registrados.</p>";
		die();
	}

	ob_start();

	if( (include "./easylogin_templates/escuela.php") === false ){

		ob_get_clean();
		echo "Hubo un error inesperado";
		die();
	}

	*/
	$PAGE->set_url("/local/easylogin/?escuela=$escuela"); //

	if($escuela==='pinion-education'){
		$PAGE->set_heading('Nombre del Colegio');
	}else{
		$PAGE->set_heading(reset($grupos)->nombre_escuela);
	}
	//$outputTemplate= ob_get_clean();
	$outputTemplate=$OUTPUT->render_from_template('local_easylogin/escuela',$context);
	//pinion_echo_template("./easylogin_templates/escuela.php",[$escuelaNombre,$grupos]);
}

if($grupo!=""){

	$claveEscuela=explode("-",$grupo)[0];

	$escuela=pinion_easylogin_get_escuela_by_clave($claveEscuela);


	if($escuela===false || sizeOf($escuela)==0){
		//TODO redireccionar
		die('Hubo un error inesperado');
	}

	$escuelaInfo=reset($escuela);


	if($grupo=='pinion-easydemo'){
		$alumnos=pinion_easylogin_get_cohort_demo();
	}else{
		$alumnos=pinion_easylogin_get_cohort_by_slug($grupo); //para todos los usuarios
	}


	$context=[];
	$context['emptyAlumnos']=(sizeOf($alumnos) == 0 );
	if(!($context['emptyAlumnos'])){
		$first=reset($alumnos);
		$clave=$escuelaInfo->clave;
		$nombreGrupo=str_replace("{$clave}-","",$first->nombre_grupo);
		$context['nombreGrupo']=$nombreGrupo;
		$context['cohortid']=$first->cohortid;

	}
	foreach($alumnos as $k=>$a){
		//if(intval($a->suspended)==1){continue;}
		$a->hideID=base64_encode($a->uid);
		$context['alumnos'][]=$a;

	}


//	pinion_echo_template("./easylogin_templates/grupo.php",$alumnos);
/*
	ob_start();

	if( (include "./easylogin_templates/grupo.php") === false ){

		ob_get_clean();
		echo "Hubo un error inesperado";
		die();
	}
*/


	$PAGE->set_url("/local/easylogin/?grupo=$grupo");
	$PAGE->set_heading($escuelaInfo->nombre_escuela);
//	$outputTemplate=ob_get_clean();	
	$outputTemplate=$OUTPUT->render_from_template('local_easylogin/grupo',$context);
	$outputTemplate.=$OUTPUT->render_from_template('local_easylogin/grupolightbox',[]);
	$PAGE->requires->js_amd_inline($OUTPUT->render_from_template('local_easylogin/msgs',[]));
	$PAGE->requires->js_amd_inline(file_get_contents($CFG->dirroot."/local/pinion/plugin_templates/send_request.js"));
	$PAGE->requires->js_amd_inline($OUTPUT->render_from_template('local_easylogin/gruposcript',[]));

		/*TOASTS ENGINE*/
	$PAGE->requires->js_amd_inline(file_get_contents($CFG->dirroot.'/local/pinion/js/toastify.js'));
	$PAGE->requires->js_amd_inline(file_get_contents($CFG->dirroot.'/local/pinion/js/pinionToasts.js'));
	//$PAGE->requires->css('/local/pinion/css/toastify.css');
	$addToastCss=true;
	/*TOASTS ENGINE END*/

}

echo $OUTPUT->header();
echo $outputTemplate;
if($addToastCss){
	echo '<style>';
	include $CFG->dirroot.'/local/pinion/css/toastify.css';
	echo '</style>';
}
echo $OUTPUT->footer();
