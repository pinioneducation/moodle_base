<?php

require_once('../../config.php');
//require_once(dirname(__FILE__).'/lib.php');
//require($CFG->dirroot.'/local/pinion/functions.php');
header("Content-Type: application/json;charset=utf-8");


if(isloggedin()){
	echo json_encode(['s'=>-1]);
	die();
}

$action=optional_param('action',"",PARAM_TEXT);
$u=optional_param('u',"",PARAM_TEXT);
$p=optional_param('p',"",PARAM_TEXT);
$cohortid=optional_param('c',0,PARAM_INT);

if($action!='easylogin'){
	$xError=array('msg'=>'MISSING_PARAMETERS','s'=>0);
	echo json_encode($xError);
	die();
}


$userId=intval(base64_decode($u));


//$r=pinion_authenticate_user_easylogin(intval($userId),$p);
global $DB;
$queryArr=[
	"SELECT A.*,B.password",
	"FROM {user} A",
	"JOIN {pinion_easylogin_estudiantes} B ON B.mid=A.id AND A.id=$userId AND B.cohort_id=$cohortid",
	"WHERE B.password=?"
];

$records=$DB->get_records_sql(implode(" ",$queryArr),[$p]);


if(sizeOf($records)!=1){
	$xError=array('msg'=>'INVALID_USERNAME_PASSWORD','s'=>0);
	echo json_encode($xError);
	die();
}

//Tengo un user:
$user=complete_user_login(array_pop($records));

$user->profile['learn_role']="Estudiante";
$user->learn_role="Estudiante"; //estúpido Moodle

$x=array('msg'=>'SUCCESS','s'=>1,'r'=>$CFG->wwwroot);
echo json_encode($x);
die();

