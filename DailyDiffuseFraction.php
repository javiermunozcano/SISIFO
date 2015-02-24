<?php

function DailyDiffuseFraction($KTd, $ws, $OPTIONS, $TIME) {
	
	//Diffuse correlation
	$correlation=$OPTIONS['DailyDiffuseCorrelation'];
	
	//Calculation
	for ($i = 0; $i < $TIME['Ndays']; $i++) 
		{
		if ($correlation == 1)
			{
			//Page
			$KDd[$i]= 1-1.13*$KTd[$i];
			}elseif ($correlation == 2)
				{
				//Erbs
				if ($ws[$i] <= 1.4208)
					{
					$KDd[$i] = 1.391-3.56* $KTd[$i] +4.189 *$KTd[$i] *$KTd[$i] -2.137 *$KTd[$i] *$KTd[$i] *$KTd[$i];
					}elseif ( $ws[$i] > 1.4208 )
						{
						$KDd[$i] = 1.311-3.022 *$KTd[$i] +3.427 *$KTd[$i] *$KTd[$i] -1.821 *$KTd[$i] *$KTd[$i] *$KTd[$i];
						}
				}elseif ($correlation == 3)
					{//Macagnan
        			$KDd[$i]=0.758 -0.428 *$KTd[$i] -0.503 *$KTd[$i] *$KTd[$i];
					}else
						{
        				echo 'Daily diffuse correlation option is wrong';
						}
		}//end for TIME NDays

	return $KDd;
}

?>