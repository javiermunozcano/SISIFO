<?php

function DustDegreeParameters($DustDegree) {
	
//This function ...
	//Model parameters - SOILING IMPACT
	switch ($DustDegree) 
	{
		case '1': 		//1.Clean (0%)
			$ar= 0.17;
			$c2= -0.069;
			$Transm= 1;
			break;
		
		case '2':		//2.Low (2%)
			$ar=0.2;
			$c2=-0.054;
			$Transm=0.98;
			break;
			
		case '3':		//3. Medium (3%)
			$ar=0.21;
			$c2=-0.049;
			$Transm=0.97;
			break;
					
		case '4':		//4. High (8%)
			$ar=0.27;
			$c2=-0.023;
			$Transm=0.92;
			break;
		
		default:
			echo 'Insert a correct value (1,2,3,4)';
			$ar=0;
			$c2=-0;
			$Transm=0;
			break;
	}

$DDP=array(
	'ar' => $ar,
	'c2' =>$c2,
	'Transm' => $Transm);

return $DDP;
}

?>