<?php
function LowIrradianceParamerters($P1000, $P600, $P200) {
//This function ...

//Definition 					//	MatrixA = (	00 01 02 )  
$MatrixA[0][0]= 1;				//		 	  ( 10 11 12 )
$MatrixA[0][1]= 1;				//			  (	20 21 22 )
$MatrixA[0][2]= 0;
$MatrixA[1][0]= 1;
$MatrixA[1][1]= 0.6;
$MatrixA[1][2]= log(0.6);
$MatrixA[2][0]= 1;
$MatrixA[2][1]= 0.2;
$MatrixA[2][2]= log(0.2);
//Definition 						MatrixB = (	00 )  
$MatrixB[0][0]= 1;					//		  ( 10 )
$MatrixB[1][0]= (10/6)*$P600/$P1000;//		  (	20 )
$MatrixB[2][0]= 5*$P200/$P1000;

//A*X=B
//Solve the system of three equations with three unknowns by the Sarrus method
	//Determinant(MatrixA)
	$DetA=(($MatrixA[0][0])*(($MatrixA[1][1]*$MatrixA[2][2])-($MatrixA[2][1]*$MatrixA[1][2])))-(($MatrixA[0][1])*(($MatrixA[1][0]*$MatrixA[2][2])-($MatrixA[2][0]*$MatrixA[1][2])))+(($MatrixA[0][2])*(($MatrixA[1][0]*$MatrixA[2][1])-($MatrixA[2][0]*$MatrixA[1][1])));
	//Determinant X (replaced the first column of MatrixA by MatrixB)
	$DetX=($MatrixB[0][0]*$MatrixA[1][1]*$MatrixA[2][2]+$MatrixA[0][1]*$MatrixA[1][2]*$MatrixB[2][0]+$MatrixA[0][2]*$MatrixB[1][0]*$MatrixA[2][1])-($MatrixA[0][2]*$MatrixA[1][1]*$MatrixB[2][0]+$MatrixA[2][0]*$MatrixA[1][2]*$MatrixA[2][1]+$MatrixA[0][1]*$MatrixB[1][0]*$MatrixA[2][2]);
	//Determinant Y (replaced the second column of MatrixA by MatrixB)
	$DetY=($MatrixA[0][0]*$MatrixB[1][0]*$MatrixA[2][2]+$MatrixA[1][0]*$MatrixB[2][0]*$MatrixA[0][2]+$MatrixA[2][0]*$MatrixB[0][0]*$MatrixA[1][2])-($MatrixA[0][2]*$MatrixB[1][0]*$MatrixA[2][0]+$MatrixA[1][2]*$MatrixB[2][0]*$MatrixA[0][0]+$MatrixA[2][2]*$MatrixB[0][0]*$MatrixA[1][0]);
	//Determinant Z (replaced the third column of MatrixA by MatrixB)
	$DetZ=($MatrixA[0][0]*$MatrixA[1][1]*$MatrixB[2][0]+$MatrixA[0][1]*$MatrixB[1][0]*$MatrixA[2][0]+$MatrixB[0][0]*$MatrixA[1][0]*$MatrixA[2][1])-($MatrixB[0][0]*$MatrixA[1][1]*$MatrixA[2][0]+$MatrixA[0][0]*$MatrixB[1][0]*$MatrixA[2][1]+$MatrixA[0][1]*$MatrixA[1][0]*$MatrixB[2][0]);

	//X= DetX/DetA
	$x=$DetX/$DetA;
	//Y= DetY/DetA
	$y=$DetY/$DetA;
	//Z= DetZ/DetA
	$z=$DetZ/$DetA;

	
return $LGP = array(
				'a' => $x,
				'b' => $y,
				'c' => $z);

}

?>