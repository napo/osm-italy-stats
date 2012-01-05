<?php
/****************************************************************************
Function to manage DB queries
****************************************************************************/

/////////////////////////////////////////////////////////////////////////////
// PG_QueryStart
// ====================
// 
// Begin query on PostgrSQL: all the parameter are global variables
function	PG_QueryStart( $SQL, &$aFields, &$link, &$Errors )
	{
	$gMyHOST	= "127.0.0.1";
	$gMyDB	= "osm";
	$gMyUSER = "osm";
	$gMyPASS = "insertPassword";
	
	$Errors = "";
	$result = false;
	$conn_string = "host=$gMyHOST port=5432 dbname=$gMyDB user=$gMyUSER password=$gMyPASS";
	$link = pg_connect( $conn_string );
		if( $link === false )
			$Errors .= "Impossibile connettersi a '$gMyHOST' come '$gMyUSER'";
		else
			{
			$result = pg_query( $SQL );
				if( $result === false )
					$Errors .= ($Errors[0] ? "" : "<br>") . "Errore durante la query '$SQL': " . pg_last_error();
				else
					{
					$nFields = pg_num_fields( $result );
						for( $ind = 0; $ind < $nFields; $ind++ )
							{
							$aFields[$ind] = array( pg_field_name( $result, $ind ), pg_field_type( $result, $ind ) );
							}
					}
			}
	return( $result );
	}

/////////////////////////////////////////////////////////////////////////////
// PG_QueryEnd
// ====================
// query end: all the results and links are free now
function	PG_QueryEnd( $link, $result )
	{
		if( !($result === false) )
			pg_free_result( $result );
		if( !($link === false) )
			pg_close( $link );
	}

/////////////////////////////////////////////////////////////////////////////
// PG_SimpleQuery
// ==============
// query without record, only the result
function	PG_SimpleQuery( $SQL, &$Errors )
	{
	$result = PG_QueryStart( $SQL, $aFields, $link, $Errors );
	PG_QueryEnd( $link, $result );
	}

/////////////////////////////////////////////////////////////////////////////
// SQL_Ins_Prep
// ====================
// organization for INSERT query: 
// FName = field names
// FVal  = field values 
// Name  = current field
// Value = value current field
// ======> insert into TBL (FName) values (FVal)
function	SQL_Ins_Prep( &$FName, &$FVal, $Name, $Value )
	{
		if( $FName != "" )
			{
			$FName .= ", ";
			$FVal  .= ", ";
			}
	$FName .= $Name;
		if( $Value == "now()" )
			$FVal .= $Value;
		else if( is_string( $Value ) )
			$FVal .= "'" . str_replace( "'", "''", $Value ) . "'";
		else if( isset($Value) )
			$FVal .= $Value;
		else
			$FVal .= "null";
	}
	
/////////////////////////////////////////////////////////////////////////////
// SQL_Upd_Prep
// ====================
// UPDATE query:
// SET   = operations 
// Name  = current field
// Value = current field value
// ======> update TBL SET where CODITION
function	SQL_Upd_Prep( &$SET, $Name, $Value )
	{
		if( $SET != "" )
			{
			$SET .= ", ";
			}
	$SET .= $Name . " = ";
		if( $Value == "now()" )
			$SET .= $Value;
		else if( is_string( $Value ) )
			{
			$SET .= "'" . str_replace( "'", "''", $Value ) . "'";
			}
		else if( isset($Value) )
			$SET .= $Value;
		else
			$SET .= "null";
	}


/////////////////////////////////////////////////////////////////////////////
// GetSelFromArray
// ===============
// Search row in the array with the corrispetive value
function	GetSelFromArray( $aData, $Value, $nCol )
	{
	$nData = count( $aData );
		if( !isset($nCol) || $nCol < 0 )
			$nCol = 0;
	$nSel = -1;
		for( $j = 0; $j < $nData && $nSel < 0; $j++ )
			{
				if( $aData[$j][$nCol] == $Value )
					$nSel = $j;
			}
	return( $nSel );
	}

/////////////////////////////////////////////////////////////////////////////
// Load_Data2Array
// ===============
// Load the query result in an array
function	Load_Data2Array( $SQL, &$aData, &$Errors )
	{
	$aData   = array();
	$nData   = 0;
	$Errors  = "";
	$result  = PG_QueryStart( $SQL, $aFields, $link, $Errors );
		if( !($result === false) )
			{
				while( $qRow = pg_fetch_array( $result, null, PGSQL_NUM ) )
					{
					$aData[$nData++] = $qRow;
					}
			}
	PG_QueryEnd( $link, $result );
		if( $Errors != "" )
			$nData = -1;
	// echo "\n<!-- $SQL ==> $nData -->\n";
	return( $nData );
	}
	
/////////////////////////////////////////////////////////////////////////////
// Load_Data2ArrayEx
// =================
// Carica il risultato di una query in un array
function	Load_Data2ArrayEx( $SQL, &$aData, &$aFields, &$Errors )
	{
	$aData   = array();
	$nData   = 0;
	$Errors  = "";
	$result  = PG_QueryStart( $SQL, $aFields, $link, $Errors );
		if( !($result === false) )
			{
				while( $qRow = pg_fetch_array( $result, null, PGSQL_NUM ) )
					{
					$aData[$nData++] = $qRow;
					}
			}
	PG_QueryEnd( $link, $result );
		if( $Errors != "" )
			$nData = -1;
	// echo "\n<!-- $SQL ==> $nData -->\n";
	return( $nData );
	}
	
/////////////////////////////////////////////////////////////////////////////
// Load_Data2JS
// ===============
// Scrive come array JavaScript il risultato di una query
function	Load_Data2JS( $SQL, $sName, &$Errors )
	{
	$AName = "_a" . $sName;
	$CName = "_n" . $sName;
	$myHTML  = "\n<script type='text/javascript'>\n";
	$myHTML .= "<!--\n";
	// $myHTML .= "// $SQL\n";
	$myHTML .= "$AName = Array();\n";
	$myHTML .= "$CName = 0;\n";
	$Errors  = "";
	$result  = PG_QueryStart( $SQL, $aFields, $link, $Errors );
		if( !($result === false) )
			{
			$nFields = count( $aFields );
				while( $qRow = pg_fetch_array( $result, null, PGSQL_NUM ) )
					{
					$myHTML .= $AName . "[" . $CName . "++] = Array( ";
						for( $j = 0; $j < $nFields; $j++ )
							{
								if( $j > 0 )
									$myHTML .= ", ";
								if( ! isset($qRow[$j]) )
									$myHTML .= "null";
								else if( is_numeric( $qRow[$j] ) )
									$myHTML .= $qRow[$j];
								else if( is_string( $qRow[$j] ) )
									{
									$sText = str_replace( "à", "&agrave;", $qRow[$j] );
									$sText = str_replace( "é", "&eacuta;", $sText );
									$sText = str_replace( "è", "&egrave;", $sText );
									$sText = str_replace( "ì", "&igrave;", $sText );
									$sText = str_replace( "ò", "&ograve;", $sText );
									$sText = str_replace( "ù", "&ugrave;", $sText );
									$sText = str_replace( "\"", "\\\"", $sText );
									$myHTML .= "\"$sText\"";
									}
								else
									{
									$dtVal = MySQL_DateConv( $qRow[$j] );
									$myHTML .= strftime("'%d-%m-%Y'", $dtVal );
									}
							}
					$myHTML .= ");\n";
					}
			}
	PG_QueryEnd( $link, $result );
	$myHTML .= "//-->\n";
	$myHTML .= "</script>\n";
	return( $myHTML );
	}

/////////////////////////////////////////////////////////////////////////////
// Load_Data2JSON
// ===============
// Scrive come array JSON il risultato di una query
function	Load_Data2JSON( $SQL, $sName="", $bShowError=true,  $noHeader =true)
	{
	$nFD = fopen( "Load_Data2JSOS.trc", "a" );
		if( $nFD > 0 )
			{
			$sTime = strftime("%Y-%m-%d %H:%M:%S",time());
			fputs( $nFD, "$sTime | $SQL\n" );
			fclose( $nFD );
			}
		$Errors  = "";
   	$JSON		= "";
		$nData   = 0;
		$result  = PG_QueryStart( $SQL, $aFields, $link, $Errors );
		if( !($result === false) )
			{
			$nFields = count( $aFields );
			
				if(!$noHeader)
					$JSON .= "{\n\t \"name\": \"".$sName."\",\n\t \"items\":[\n ";

				while( $qRow = pg_fetch_array( $result, null, PGSQL_NUM ) )
					{
						if( $nData > 0 )
                		$JSON .= ",\n";
						$JSON .= "\t{\n";
							for( $j = 0; $j < $nFields; $j++ )
								{
									$sVal = "" . $qRow[$j];
									
									$JSON .= "\t\t\"".htmlspecialchars($aFields[$j][0])."\": \"".htmlspecialchars($sVal)."\"";
									if($j< ($nFields-1))
										$JSON.=",\n";
								}
						$JSON .= "\n\t}";					
						$nData++;
					}
				if(!$noHeader)
					$JSON .= "]}";
			}
	PG_QueryEnd( $link, $result );
	return( $JSON );
	}

?>
