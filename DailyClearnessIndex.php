<?php

function DailyClearnessIndex($Gd0, $BOd0, $TIME) {

	//Calculation
	for ($i = 0; $i < $TIME['Ndays']; $i++) 
		{
		//Daily Clearness Index, KTd
		if ($BOd0[$i] <= 0)
			{
			$KTd[$i]=0;
			}else
				{
				$KTd[$i]=$Gd0[$i]/$BOd0[$i];
				}
		}
return $KTd;	
	
}

?>