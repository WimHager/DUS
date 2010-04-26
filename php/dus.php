<?php

include "dus-lib.php";


if (file_exists($DbName)) { $DusArr= load_array_dump($DbName);
}else{ $DusArr= array(); }

if ($Methode == "UPD") {
	CleanUp(); // remove old records TTL time out

	if (array_key_exists($objectkey, $DusArr)) { 
		write_log("dus.log", "$objectkey Update");
		}else{
	 	write_log("dus.log", "$objectkey Create"); 
	}

	UpdateRecord($objectkey,$PrimUrl,$PrimTtl);

	save_array_dump($DbName,$DusArr);
}

if ($UUID != "") {
	write_log("dus.log", "$UUID Get url");
	echo GetUrl($UUID);
}


?>
