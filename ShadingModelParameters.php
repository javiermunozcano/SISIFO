<?php

function ShadingModelParameters($ShadingModel) {
	//Shading, model parameters
	switch ($ShadingModel) {
		case '1':	//Pessimistic
			$MSP=1;
			$MSO=0;
			$MSC=0;
			break;
		
		case '2':	//Optimistic
    		$MSP=0;
    		$MSO=1;
    		$MSC=0;
			break;
		
		case '3':	//Classic
    		$MSP=0;
    		$MSO=0;
    		$MSC=1;
			break;
				
		case '4':	//Martinez
			$MSP=0;
			$MSO=0;
			$MSC=0;
			break;
		
		default:
			echo 'Introduce un valor correcto (1,2,3,4)';
			break;
	}
	
$SMP=array(
	'MSP' => $MSP,
	'MSO' => $MSO,
	'MSC' => $MSC);	
	
return $SMP;

}

?>
