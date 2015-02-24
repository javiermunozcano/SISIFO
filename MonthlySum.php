<?php

function MonthlySum($DailyMatrix) 
{
	//This function ...
	
	//Lenght of each month
	$MonthLength=[31,28,31,30,31,30,31,31,30,31,30,31];
	
	//Length of the matrix
	$Ndays=count($DailyMatrix);
	
	//Initialize monthly array
	$Month = array_fill(0, 12, 0);
	
	//Monthy sums
	if ($Ndays == 365)
	{		
		//All the year
		for ($i = 0; $i < 365; $i++) 
		{
			if ($i<31) 
			{
				$Month[0]=$Month[0]+$DailyMatrix[$i];
			}
			elseif ($i<59)
			{
				$Month[1]=$Month[1]+$DailyMatrix[$i];
			}
			elseif ($i<90)
			{
				$Month[2]=$Month[2]+$DailyMatrix[$i];
			}
			elseif($i<120)
			{
				$Month[3]=$Month[3]+$DailyMatrix[$i];
			}
			elseif ($i<151)
			{
				$Month[4]=$Month[4]+$DailyMatrix[$i];
			}
			elseif ($i<181)
			{
				$Month[5]=$Month[5]+$DailyMatrix[$i];
			}
			elseif ($i<212)
			{
				$Month[6]=$Month[6]+$DailyMatrix[$i];
			}
			elseif ($i<243)
			{
				$Month[7]=$Month[7]+$DailyMatrix[$i];
			}
			elseif ($i<273)
			{
				$Month[8]=$Month[8]+$DailyMatrix[$i];
			}
			elseif ($i<304)
			{
				$Month[9]=$Month[9]+$DailyMatrix[$i];
			}
			elseif ($i<334)
			{
				$Month[10]=$Month[10]+$DailyMatrix[$i];
			}
			elseif ($i<365)
			{
				$Month[11]=$Month[11]+$DailyMatrix[$i];
			}
	
		}//end for
		
	}
	elseif ($Ndays==12)		//Only characteristic days
	{
		$Month[0] = $DailyMatrix[0]*$MonthLength[0];	//January
		$Month[1] = $DailyMatrix[1]*$MonthLength[1];	//February
		$Month[2] = $DailyMatrix[2]*$MonthLength[2];	//March
		$Month[3] = $DailyMatrix[3]*$MonthLength[3];	//April
		$Month[4] = $DailyMatrix[4]*$MonthLength[4];	//May
		$Month[5] = $DailyMatrix[5]*$MonthLength[5];	//June
		$Month[6] = $DailyMatrix[6]*$MonthLength[6];	//July
		$Month[7] = $DailyMatrix[7]*$MonthLength[7];	//August
		$Month[8] = $DailyMatrix[8]*$MonthLength[8];	//September
		$Month[9] = $DailyMatrix[9]*$MonthLength[9];	//October
		$Month[10] = $DailyMatrix[10]*$MonthLength[10];	//November
		$Month[11] = $DailyMatrix[11]*$MonthLength[11];	//December
	}
	
	return $Month;
}

?>