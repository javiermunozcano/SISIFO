<?php 
function ReadTMY($METEO) {
	
require_once '../Excel/reader.php';
 
$data = new Spreadsheet_Excel_Reader();
$data->setOutputEncoding('CP1251');
$data->read('ExampleTMY.xls');

//To see the table, uncomment all: echo("<tr>")
//echo("<table>");
	for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
	    //echo("<tr>");
	    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {		//Sheets[0] is equivalent: Excel Hoja1.  Sheets[1]= Hoja2 ...
	    //    echo("<td>".$data->sheets[0]['cells'][$i][$j] ."</td>");
	    
	    	switch ($j) {
	    		case '1':	//DAY: Measurement day
	    			if ($i==1) {
	    				$Day= array($data->sheets[0]['cells'][$i][$j]);
	    			}else{    			
	    			array_push($Day, $data->sheets[0]['cells'][$i][$j]);
	    			}
	    			break;
	    			
	    		case '2':	//TIME: Measurement hours
	    			if ($i==1) {
	    				$Time=array($data->sheets[0]['cells'][$i][$j]);
	    			}else{ 
	    			array_push($Time, $data->sheets[0]['cells'][$i][$j]);
	    			}
	    			break;
	    		
	    		case '3':	//G0: Global radiance
	    			if ($i==1) {
	    				$G0=array($data->sheets[0]['cells'][$i][$j]);
	    			}else{
	    			array_push($G0, $data->sheets[0]['cells'][$i][$j]);
	    			}
	    			break;
	    		
	    		case '4':	//D0: Diffuse radiance
	    			if ($i==1) {
	    				$D0=array($data->sheets[0]['cells'][$i][$j]);
	    			}else{
	    			array_push($D0, $data->sheets[0]['cells'][$i][$j]);
	    			}
	    			break;
	    		
	    		case '5':	//Ta: Environment temperarute
	    			if ($i==1) {
	    				$Ta=array($data->sheets[0]['cells'][$i][$j]);
	    			}else{
	    			array_push($Ta, $data->sheets[0]['cells'][$i][$j]);
	    			}
	    			break;
	
	    	}
	    
	    
	    }
	 
	}

	//B0=G0-D0     B0: Direct radiance
	$B0[0]='B0';
	for ($i = 1; $i <= $data->sheets[0]['numRows']-1; $i++) 
	{
		$B0[$i]=$G0[$i]-$D0[$i];
	}
	
	$METEO=array($Day,$Time,$G0,$D0,$B0,$Ta);

	return $METEO;
}
?>