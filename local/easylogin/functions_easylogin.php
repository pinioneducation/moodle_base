<?php
defined('MOODLE_INTERNAL') || die('non');

if(!defined('LOCALPINION')){
	require_once($CFG->dirroot.'/local/pinion/functions.php');
}

/*****LAS FUNCIONES DEL EASY LOGIN**********/
function pinion_easylogin_generar_color($firstName,$cohortId){

	$colores=[1,2,3,4,5];
	$coloresAdicionales=[6,7,8];

	global $DB;

	//$usernameQ=trim(explode(" ",$firstName)[0]);
	$usernameQ=trim($firstName);

	if(mb_strlen($usernameQ)==0){
		return 0;
	}

	//$usernameQ=mb_split("'",$usernameQ);
	//$usernameQ=implode("''",$usernameQ);

	$usernameQ.='%';

	$queryArr=[
		"SELECT A.id,firstname,B.color FROM {user} A",
		"JOIN {pinion_easylogin_estudiantes} B ON B.mid=A.id AND B.cohort_id=? AND A.firstname LIKE ? AND A.deleted!=1",
		];

	try{
		$records=$DB->get_records_sql(implode(" ",$queryArr),[$cohortId,$usernameQ]);
	}catch(Exception $e){
		var_dump($e);die();
		return 0;
	}

	$coloresFound=[];
	foreach($records as $k=>$v){

		if($v->firstname!=$usernameQ){
			continue;
		}

		$color=intval($v->color);

		if (in_array($color,$colores)){
			$coloresFound[]=$color;
		}
	}

	$coloresFound=array_unique($coloresFound);

	$coloresDisponibles=array_values(array_diff($colores,$coloresFound)); //el array_diff arruina los índices

	if(sizeOf($coloresDisponibles)==0){

		//ver si hay más disponibles
		$coloresTotales=array_merge($colores,$coloresAdicionales);
		$coloresDisponibles=array_values(array_diff($coloresTotales,$coloresFound));

		//all colors are unavailabe
		if(sizeOf($coloresDisponibles)==0){
			return $coloresTotales[mt_rand(0, sizeOf($coloresTotales) - 1)];
		}
		
		//there are available colors


		return $coloresDisponibles[mt_rand(0, sizeOf($coloresDisponibles) - 1)];
	}

	return $coloresDisponibles[mt_rand(0, sizeOf($coloresDisponibles) - 1)];
}

function pinion_easylogin_get_cohort_demo(){
	global $DB;

	$queryArr=[
		"SELECT A.id uid, -1 cohortid, A.firstname,A.lastname,A.suspended,'|PINION|EASYLOGIN|DEMO' idnumber,'Pinion Education Primero E' nombre_grupo",
		"FROM {user} A",
		"WHERE department ='|PINION|' AND username like '%-pinion'",
		"ORDER BY A.firstname"
	];


	$records=$DB->get_records_sql(implode(" ",$queryArr));

	$x=1;
	foreach($records as &$r){
		$r->color=$x%5 + 1;
		$x++;
	}

	return $records;
}

//$grupo es el slug del grupo
function pinion_easylogin_get_cohort_by_slug($grupo){
	global $DB;
	//extraer la clave del slug

//	$clave=explode("-",$grupo)[0];
//	$clave=str_replace("'","",$clave);

	$queryArr=[
//		"SELECT C.id uid,A.cohort_id cohortid,B.userid,C.firstname,C.lastname,D.color, E.nombre as nombre_escuela,E.clave,F.idnumber,F.name nombre_grupo",
		"SELECT C.id uid,A.cohort_id cohortid,B.userid,C.firstname,C.lastname,C.suspended,D.color,F.idnumber,A.nombre nombre_grupo", //,F.name nombre_grupo",
		"FROM {pinion_cohorts} A",
		"JOIN {cohort_members} B ON B.cohortid=A.cohort_id",
		"JOIN {user} C ON C.id=B.userid AND C.suspended!=1 AND C.deleted!=1",
		"JOIN {pinion_easylogin_estudiantes} D ON D.mid=C.id AND D.cohort_id=A.cohort_id",
//		"JOIN pinion_escuelas_slug E ON E.slug like '{$clave}%'",
		"JOIN {cohort} F ON F.id=A.cohort_id",
		"WHERE A.slug=?",
		"ORDER BY C.firstname"
	];

	$records=$DB->get_records_sql(implode(" ",$queryArr),[$grupo]);

	return $records;
}
//escuela es el slug de la escuela
function pinion_easylogin_get_grupos_by_slug($escuela){
//el join podría ser más rápido si además uso el contextid
	global $DB;
	$queryArr=[
		"SELECT C.cohort_id id,A.clave, A.nombre nombre_escuela,C.grado,C.slug, D.name,D.idnumber",
		"FROM pinion_escuelas_slug A",
		"JOIN {pinion_cohorts} C ON C.clave_escuela=A.clave AND C.grado<4 AND grado!=0 AND A.slug=?",
		"JOIN {cohort} D ON C.cohort_id=D.id",
		"WHERE D.idnumber not like '%|R0%'",
		"ORDER BY C.grado,D.name"
	];

	$records=$DB->get_records_sql(implode(" ",$queryArr),[$escuela]);

	return $records;
}

function pinion_easylogin_get_escuela_by_clave($clave){

	global $DB;

	$queryArr=[
		"SELECT A.mid,A.clave, A.nombre nombre_escuela",
		"FROM pinion_escuelas_slug A",
		"WHERE A.clave=? "
	];

	$records=$DB->get_records_sql(implode(" ",$queryArr),[$clave]);

	return $records;

	
}

function pinion_easylogin_delete_user($userid){
	global $DB;

	$sql="DELETE FROM {pinion_easylogin_estudiantes} WHERE mid=$userid";

	return $DB->execute($sql);
}


/*
	easylogin for demo
*/

global $PinionConfig;

if($PinionConfig->flavor=='demo'){
	@include __DIR__ ."/functions_easylogin_demo.php";
}