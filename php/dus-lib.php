<?php

$headers 	= request_headers(); //apache_request_headers();

//if (count($headers) < 15) {echo "SHA-2 cryption key wrong!! Bye."; exit;} //let them guess ;)

$objectgrid 	= $headers["X-SecondLife-Shard"];
$objectname 	= $headers["X-SecondLife-Object-Name"];
$objectkey     	= $headers["X-SecondLife-Object-Key"];
$objectpos 	= $headers["X-SecondLife-Local-Position"];
$ownerkey     	= $headers["X-SecondLife-Owner-Key"];
$ownername 	= $headers["X-SecondLife-Owner-Name"];
$regiondata     = $headers["X-SecondLife-Region"];
$regiontmp 	= explode ("(",$regiondata); // cut cords off 
$regionpos 	= explode (")",$regiontmp[1]); //
$regionname 	= substr($regiontmp[0],0,-1); // cut last space from simname

$Methode	= $_POST["func"];
$PrimUrl        = $_POST["url"];
$PrimTtl        = $_POST["ttl"];
$UUID		= $_GET["uuid"];

//check all input

$DbName= "dus.db";
$DusArr= array();

function write_log($filename, $message) {
    $fp = fopen($filename, "a+");
    fwrite($fp, date("[Y/m/d-H:i:s];").$message."\n");
    fclose($fp);
}

function load_array_dump($filename) {
    $content= file_get_contents($filename);
    $array= unserialize($content);
    return($array);
}

function save_array_dump($filename, $array) {
    $dump= serialize($array);
    file_put_contents($filename, $dump);
    file_put_contents("dus.arr", print_r($array, true));
}

function request_headers() { // Replacement if apache_request_headers not exist 
    foreach($_SERVER as $name => $value)
    if(substr($name, 0, 5) == 'HTTP_')
        $headers[str_replace('X-Secondlife-', 'X-SecondLife-', str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))))] = $value;
    return $headers;
}

function GetUrl($uuid) { //Returns url for given UUID
	global $DusArr;
	return $DusArr[$uuid]["url"];
} 

function CleanUp() { //Removes all old Records
	global $DusArr;
	$TmpArr= $DusArr;
	$Now= time();
	foreach ($TmpArr as &$Content) {
		$MaxLife= $Content["upd"] + $Content["ttl"];
		if ( $Now > $MaxLife) { 
			write_log("dus.log",key($DusArr)." Removed TTL timeout");
    			unset($DusArr[key($DusArr)]);
		}
	}
	unset($Content); 
} 

function UpdateRecord($uuid, $url, $ttl) { //Updates record for given UUID
	global $DusArr;
	$DusArr[$uuid]["url"]= $url;
	$DusArr[$uuid]["upd"]= time();
	$DusArr[$uuid]["ttl"]= $ttl;
} 



//Function by Simba Fuhr
//Use under the GPL License
function CallLSLScript($URL, $Data, $Timeout = 10) {
	//Parse the URL into Server, Path and Port
 	$Host = str_ireplace("http://", "", $URL);
	$Path = explode("/", $Host, 2);
 	$Host = $Path[0];
 	$Path = $Path[1];
 	$PrtSplit = explode(":", $Host);
 	$Host = $PrtSplit[0];
 	$Port = $PrtSplit[1];
 
 	//Open Connection
 	$Socket = fsockopen($Host, $Port, $Dummy1, $Dummy2, $Timeout);
 	if ($Socket) {
  		//Send Header and Data
  		fputs($Socket, "POST /$Path HTTP/1.1\r\n");
  		fputs($Socket, "Host: $Host\r\n");
  		fputs($Socket, "Content-type: application/x-www-form-urlencoded\r\n");
  		fputs($Socket, "User-Agent: Opera/9.01 (Windows NT 5.1; U; en)\r\n");
  		fputs($Socket, "Accept-Language: de-DE,de;q=0.9,en;q=0.8\r\n");
  		fputs($Socket, "Content-length: ".strlen($Data)."\r\n");
  		fputs($Socket, "Connection: close\r\n\r\n");
  		fputs($Socket, $Data);
 
  		//Receive Data
  		while(!feof($Socket))
   		{$res .= fgets($Socket, 128);}
  		fclose($Socket);
 	}
 
 	//ParseData and return it
 	$res = explode("\r\n\r\n", $res);
 	return $res[1];
}



?> 