<?php
function DailyParameters($HI,$ISI,$POWER,$TIME) 
{
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Daily Parameters,<1x365>
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Global daily irradiations, Wh/m2
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	for ($d = 0; $d < $TIME['Ndays']; $d++)
	{
		//Horizontal global irradiation
		$G0d[$d]=array_sum($HI['G0'][$d])/$TIME['Stepph'];
		//In-plane global irradiation
		$Gd[$d]=array_sum($ISI['G'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation (considering incidence and dust effects)
		$Gefd[$d]=array_sum($ISI['Gef'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less adyacent shading losses
		$Gefsad[$d]=array_sum($ISI['Gefsa'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less adyacent and back shading losses
		$Gefsaypd[$d]=array_sum($ISI['Gefsayp'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less shading and spectral losses
		$Gefsaypced[$d]=array_sum($ISI['Gefsaypce'][$d])/$TIME['Stepph'];
	}
	
	$DAILY= array(
			'G0d'	=> $G0d,
			'Gd'	=> $Gd,
			'Gefd'	=> $Gefd,
			'Gefsad'=> $Gefsad,
			'Gefsaypd'=> $Gefsaypd,
			'Gefsaypced'=> $Gefsaypced);
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Beam daily irradiations, Wh/m2
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	for ($d = 0; $d < $TIME['Ndays']; $d++)
	{
		//Horizontal beam irradiation
		$DAILY['B0d'][$d]=array_sum($HI['B0'][$d])/$TIME['Stepph'];
		//In-plane beam irradiation
		$DAILY['Bd'][$d]=array_sum($ISI['B'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation (considering incidence and dust effects)
		$DAILY['Befd'][$d]=array_sum($ISI['Bef'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less adyacent shading losses
		$DAILY['Befsad'][$d]=array_sum($ISI['Befsa'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less adyacent and back shading losses
		$DAILY['Befsaypd'][$d]=array_sum($ISI['Befsayp'][$d])/$TIME['Stepph'];
		//In-plane effective irradiation less shading and spectral losses
		$DAILY['Befsaypced'][$d]=array_sum($ISI['Befsaypce'][$d])/$TIME['Stepph'];	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Diffuse daily irradiations, Wh/m2
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//Horizontal diffuse irradiation
	$DAILY['D0d'][$d]=array_sum($HI['D0'][$d])/$TIME['Stepph'];
	//In-plane diffuse irradiation
	$DAILY['Dd'][$d]=array_sum($ISI['D'][$d])/$TIME['Stepph'];
	//In-plane effective irradiation (considering incidence and dust effects)
	$DAILY['Defd'][$d]=array_sum($ISI['Def'][$d])/$TIME['Stepph'];
	//In-plane effective irradiation less adyacent shading losses
	$DAILY['Defsad'][$d]=array_sum($ISI['Defsa'][$d])/$TIME['Stepph'];
	//In-plane effective irradiation less adyacent and back shading losses
	$DAILY['Defsaypd'][$d]=array_sum($ISI['Defsayp'][$d])/$TIME['Stepph'];
	//In-plane effective irradiation less shading and spectral losses
	$DAILY['Defsaypced'][$d]=array_sum($ISI['Defsaypce'][$d])/$TIME['Stepph'];
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//Daily energy yields, equivalent hours at nominal PV power
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//DC energy, ideal
	$DAILY['EDCSPd'][$d]=array_sum($POWER['PDCSP'][$d])/$TIME['Stepph'];
	//DC energy, real
	$DAILY['EDCPPd'][$d]=array_sum($POWER['PDCPP'][$d])/$TIME['Stepph'];
	//Less seasonal losses
	$DAILY['EDCPEd'][$d]=array_sum($POWER['PDCPE'][$d])/$TIME['Stepph'];
	//Less temperature losses
	$DAILY['EDCPTd'][$d]=array_sum($POWER['PDCPT'][$d])/$TIME['Stepph'];
	//Less low irradiance losses
	$DAILY['EDCBId'][$d]=array_sum($POWER['PDCBI'][$d])/$TIME['Stepph'];
	//Less DC wiring losses
	$DAILY['EDCPCd'][$d]=array_sum($POWER['PDCPC'][$d])/$TIME['Stepph'];
	//DC energy at the inverter input
	$DAILY['EDCd'][$d]=array_sum($POWER['PDC'][$d])/$TIME['Stepph'];
	//AC energy in LV
	$DAILY['EACACd'][$d]=array_sum($POWER['PACAC'][$d])/$TIME['Stepph'];
	//AC energy in LV less AC wiring losses
	$DAILY['EACd'][$d]=array_sum($POWER['PAC'][$d])/$TIME['Stepph'];
	//AC energy in MV (discounting transformer losses)
	$DAILY['EACMTd'][$d]=array_sum($POWER['PACMT'][$d])/$TIME['Stepph'];	
	}

return $DAILY;
}

?>