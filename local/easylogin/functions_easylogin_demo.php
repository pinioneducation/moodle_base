<?php

defined('MOODLE_INTERNAL') || die('non');


function pinion_easylogin_demo_get_users($clave){
	global $DB;
	$queryArr=[
		"SELECT CM.userid uid,U.firstname,U.lastname,U.suspended,C.id cohortid,C.idnumber,C.name nombre_grupo",
		"FROM {cohort_members} CM",
		"JOIN {cohort} C ON C.id=CM.cohortid AND C.idnumber=?",
		"JOIN {user} U ON U.id=CM.userid AND U.suspended!=1 AND U.deleted!=1"
	];

	$records=$DB->get_records_sql(implode(" ",$queryArr),["|$clave|0|demo"]);

	return $records;
}

function pinion_easylogin_demo_fix_tomsawyer($cohortid=null){
	//inserta un Tom Sawyer en cada cohorte (si $cohotid===null) y lo escribe en la tabla pinion_easylogin_estudiantes
	global $DB,$CFG;

	require_once($CFG->dirroot.'/user/lib.php');

	if($cohortid!==null){
		$record=$DB->get_record('cohort',['id'=>$cohortid]);
		if($record===false){
			return false;
		}
        $idArr=explode('|',$record->idnumber);
        if(count($idArr)!=4){
            return false;
        }
		return [pinion_demo_create_demoestudiante($idArr[1],$record->id)];
		
	}

	//if $cohortid==null
	$queryArr=[
		"SELECT id,idnumber FROM {cohort}"
		];

	$records=$DB->get_records_sql(implode(" ",$queryArr));

	$ret=[];

	foreach($records as $record){
        $idArr=explode('|',$record->idnumber);
        if(count($idArr)!=4){
            continue;
        }
		$ret[]=pinion_demo_create_demoestudiante($idArr[1],$record->id);
	}

	return $ret;
}