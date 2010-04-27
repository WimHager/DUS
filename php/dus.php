<?php
/*
    This file is part of DUS.

    DUS is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    DUS is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with DUS.  If not, see <http://www.gnu.org/licenses/>.
*/

//    DUS is Dynamic UUID System for Prims
//    W. Hager founder and project leader

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
