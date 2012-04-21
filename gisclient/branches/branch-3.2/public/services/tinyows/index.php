<?php
include('../../../config/config.php');

$project = null;
$typeName = null;
$parts = explode('/', $_SERVER['REQUEST_URI']);
$startIndex = array_search('tinyows', $parts);
if(!isset($parts[$startIndex+1])) die();
else $project = $parts[$startIndex+1];
if(!isset($parts[$startIndex+2])) die();
else $typeName = $parts[$startIndex+2];

if(!file_exists(ROOT_PATH.'map/'.$project.'/'.$typeName.'.xml')) die();


if(defined('DEBUG') && DEBUG == 1) {
	$string = var_export($_REQUEST, true)."\n\n".file_get_contents('php://input');
	file_put_contents('tinyows-logs.txt', $string);
}

$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array("file", "tinyows-errors.txt", "a") // stderr is a file to write to
);

$envVars = array(
	'TINYOWS_CONFIG_FILE' => ROOT_PATH.'map/'.$project.'/'.$typeName.'.xml'
);

$db = GCApp::getDB();
list($layergroupName, $layerName) = explode('.', $typeName);
$sql = 'select project_name from '.DB_SCHEMA.'.theme 
	inner join '.DB_SCHEMA.'.layergroup using(theme_id) 
	inner join '.DB_SCHEMA.'.layer using(layergroup_id)
	where layergroup_name=:lg_name and layer_name=:l_name';
$stmt = $db->prepare($sql);
$stmt->execute(array(':lg_name'=>$layergroupName, ':l_name'=>$layerName));
$projectName = $stmt->fetchColumn(0);
if(empty($projectName)) die();

if(!isset($_SESSION['GISCLIENT_USER_LAYER'])) {
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Gisclient"');
		header('HTTP/1.0 401 Unauthorized');
	} else {
		$userData = array(
			"user"=>"username",
			"pwd"=>"password",
			'request_data'=>array(
				'username'=>$_SERVER['PHP_AUTH_USER'],
				'password'=>md5($_SERVER['PHP_AUTH_PW'])
			)
		);
		$user = new userApps($userData);
		if ($_SERVER['PHP_AUTH_USER'] == SUPER_USER && $_SERVER['PHP_AUTH_PW'] == SUPER_PWD){
			$_SESSION["USERNAME"] = SUPER_USER;
			$user->status = true;
		} else {
			$user->checkUser();
		}
		if($user->status) {
			$user->setAuthorizedLayers(array('project_name'=>$projectName));
		}
	}
}

$authorized = false;
if(!empty($_SESSION['USERNAME']) && $_SESSION['USERNAME'] == SUPER_USER) $authorized = true;
if(!empty($_SESSION['GISCLIENT_USER_LAYER'][$project][$typeName]['WFST'])) $authorized = true;
if(!$authorized) die('<?xml version="1.0" encoding="UTF-8"?><ServiceExceptionReport xmlns="http://www.opengis.net/ogc" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/ogc http://schemas.opengis.net/wms/1.1.1/OGC-exception.xsd" version="1.2.0"><ServiceException code="PermissionDenied">Permission Denied</ServiceException></ServiceExceptionReport>');


if(!empty($_GET)) {
	$envVars['REQUEST_METHOD'] = 'GET';
	$envVars['QUERY_STRING'] = $_SERVER['QUERY_STRING'];
} else {
	$fileContent = file_get_contents('php://input');
	$envVars['REQUEST_METHOD'] = 'POST';
	$envVars['CONTENT_LENGTH'] = strlen($fileContent);
	$envVars['CONTENT_TYPE'] = 'text/xml';
	if(defined('DEBUG') && DEBUG == 1) file_put_contents('tinyows-input.txt', $fileContent);
}
if(defined('DEBUG') && DEBUG == 1) file_put_contents('tinyows-input.txt', var_export($envVars, true), FILE_APPEND);
$pipes = array();

$process = proc_open(TINYOWS_EXEC, $descriptorspec, $pipes, TINYOWS_PATH, $envVars);
if(is_resource($process)) {
	if($envVars['REQUEST_METHOD'] == 'POST') {
		fwrite($pipes[0], $fileContent);
		fclose($pipes[0]);
	}
	$response = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$return = proc_close($process);
	
	$pos = strpos($response, '<?xml');
	if($pos !== false) {
		$response = substr($response, $pos);
	}
	if(defined('DEBUG') && DEBUG == 1) file_put_contents('tinyows-output.txt', $response);
	echo $response;
} else {
	if(defined('DEBUG') && DEBUG == 1) file_put_contents('tinyows-errors.txt', var_export($envVars, true)."\n\n".$fileContent);
}