<?php
/****************************************************************************
Functions to manage DB queries
****************************************************************************/

function htmlTable($mat, $header=false, $format=NULL){
	$html.='<table class="sortable">';	
	$r=0;
	foreach(  $mat as $qRow ){
		$c=0;
		$html .= "<tr>";
		foreach( $qRow as $val){
			if($header && ($r==0 || $c==0) ){
				$html .= "<th>" . $val . "</th>";
			}
			else {
				if($format!=null){
					//call_function(format,Array($val));
					$html .= $format($val);
				}
				else{
					$html .= "<td>" . $val . "</td>";									
				}
			}
			$c++;
		}
		$html .= "</td>";
		$r++;
	}
	$html.='</table>';
	return $html;
}

function htmlCell($val, $styles, $decimal=0){	
	foreach($styles as $s){
		if($val >= $s[0] && $val <$s[1]){
			return '<td class="'.$s[2].'">'. number_format($val,$decimal,'.',',') .'</td>';
		}
	}
	return '<td>'.number_format($val,$decimal,'.',',').'</td>';
}


/* 
* Prende una matrice di stringhe con tre colonne: la prima la mette sulle righe, la seconda sulle colonne ela terza come valore
*/
function olap ($mat,  $val_pos=2, $row_pos=0, $col_pos=1 ){

	$rows= Array();
	$cols= Array();
	$res = Array();
	
	foreach(  $mat as $Row ){
		
		if ( ! isset($rows[$Row[$row_pos]] )){
			$rows[$Row[$row_pos]]='';
		}
		if ( ! isset($cols[$Row[$col_pos]])){
			$cols[$Row[$col_pos]]='';
		}
		$res[$Row[$row_pos]][$Row[$col_pos]]=$Row[$val_pos];
	}

	$ret = Array();

	$ret[0][0]='';
	$r=1;
	foreach(  array_keys($rows) as $row ){
		
		$ret[$r][0]=$row;
		$c=1;
		foreach(  array_keys($cols) as $col ){			
			if($r==1){
				$ret[0][$c]=$col;	
			}
			$ret[$r][$c]=$res[$row][$col];
			$c++;					
		}
		$r++;
	}

	return $ret;
}

function	exeQueryA( $SQL, &$aData, &$Errors, $printFields=false )
	{
	$aData   = array();
	$nData   = 0;
	$Errors  = "";
	$result  = PG_QueryStart( $SQL, $aFields, $link, $Errors );
		if( !($result === false) )
			{

				while( $qRow = pg_fetch_array( $result, null, PGSQL_NUM ) )
					{
					$aData[$qRow[0]] = $qRow; $nData++;
					}
			}
	PG_QueryEnd( $link, $result );
		if( $Errors != "" )
			$nData = -1;
	// echo "\n<!-- $SQL ==> $nData -->\n";
	return( $nData );
	}


function starts_with($string, $search)
	{
		//echo('|'.strtolower($string).'-'.$search.'='.(strncmp(strtolower($string), $search, strlen($search)) == 0).'|');
		return (strncmp(strtolower($string), $search, strlen($search)) == 0);
	}



?>
