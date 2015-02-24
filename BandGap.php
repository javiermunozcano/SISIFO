<?php

function BandGap($SolarCellMaterial) 
{
	switch ($SolarCellMaterial) 
		{
		case '1':	//Si-c
					$EG=1.12;
					break;
		
		case '2':	//Te-Cd
					$EG=1.4;
					break;
		
		case '3':	//Si-a
					$EG=1.75;
					break;
		
		case '4':	//III-V  It is calculated later with SMART
					$EG=1;
					break;
		
		case '5':	//CIS
					$EG=1.2;
					break;
	
		default :
			echo 'Insert a correct Solar Cell Material';
			break;
		}
			
return $EG;
}

?>