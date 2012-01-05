<?php

	include_once "DB_Func.php";
	include_once "DB_Func_olap.php";

	$cod_reg =isset($_GET["cod_reg"])  ? $_GET["cod_reg"]  : null ;
	$cod_pro =isset($_GET["cod_pro"])  ? $_GET["cod_pro"]  : null ;
	$cod_com =isset($_GET["cod_com"])  ? $_GET["cod_com"]  : null ;
	$historic=isset($_GET["historic"]) ? $_GET["historic"] : null ;
	$most_pop=isset($_GET["most_pop"]) ? $_GET["most_pop"] : null;
	$most    =isset($_GET["most"])     ? $_GET["most"]     : null;

	$dw='';


	$style_pop=Array(Array(-1,0.00001,'empty'),Array(0.0001,2,'low'),Array(2,5,'medium'),Array(5,20,'high'),Array(20,2000,'veryhigh'));
	$style_area=Array(Array(-1,0.00001,'empty'),Array(0.0001,2,'low'),Array(2,5,'medium'),Array(5,20,'high'),Array(20,2000,'veryhigh'));
	$style_perc=Array(Array(-1,0.00001,'empty'),Array(0.0001,33,'low'),Array(33,66,'medium'),Array(66,100,'high'),Array(100,2000,'veryhigh'));


	$script='';
	$onload='';

	Load_Data2Array('select Max(date_stat) from osm_stat ', $dsq, $err);
	$date_stat= $dsq[0][0];
	$date_stat_tab= str_replace('-','',$date_stat);

	if($historic==null){		
		if($cod_pro != null)
			$dw=" AND ( date_stat='".$date_stat."' OR date_stat is null ) ";
		else
			$dw=" AND  date_stat='".$date_stat."' ";
	}

	$script.='<link rel="stylesheet" href="style.css" type="text/css" media="screen" />';

	$current_tag='';
	$current_name='Italia';


	if($most!=null){
		
		if($most=='wanted'){

			$title='Most Wanted: Elenco dei comuni senza dati OpenStreetMap';

			$SQL='select r.nome_reg, p.nome_pro, c.nome_com, c.pop2001 From (select * from osm_stat s WHERE s.date_stat =\''.$date_stat.'\') s RIGHT JOIN it_comuni c  ON s.pro_com= c.pro_com, it_province p, it_regioni r WHERE c.cod_pro=p.cod_pro AND p.cod_reg=r.cod_reg AND s.date_stat is null order by pop2001 desc';
			
			Load_Data2Array($SQL, $mat, $err);
			$html.='<table class="sortable"><tr><th>Regione</th><th>Provincia</th><th>Comuni</th><th>Popolazione</th></tr>';
			foreach($mat as $row){
				$html.='<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td>'.$row[3].'</td></tr>';
			}

			$html.='</table>';

		}
		else if($most=='top_active'){

				$SQL="select distinct date_stat from osm_stat  order by date_stat desc LIMIT 2";
				Load_Data2Array($SQL, $mat, $err);		
				$last=$mat[0][0];
				$last1=$mat[1][0];

				$title.="Comuni più attivi dal ".$last1." al ".$last;

				$ar=Array(Array('In termini assoluti','a.sum-b.sum'),Array('In termini percentuali','a.sum/b.sum'));						


				foreach($ar as $a){

					$html.="<h3>".$a[0]."</h3>";
					$sql2=" select r.nome_reg, p.nome_pro, c.nome_com, s.dif, s.perc from ( select a.pro_com, a.sum-b.sum as dif, (a.sum-b.sum)/b.sum as perc from";
					$sql2.=" (select pro_com, sum(length) from osm_stat WHERE  date_stat='".$last."' GROUP BY pro_com ) a, (select pro_com, sum(length) from osm_stat WHERE  date_stat='".$last1."' GROUP BY pro_com ) b, it_comuni c ";
					$sql2.=" WHERE a.pro_com=b.pro_com AND a.pro_com=c.pro_com ";
					if($cod_reg!=null){
                                                $sql2.= " AND c.cod_reg=".$cod_reg." ";
                                        }
					$sql2.=" order by ".$a[1]." desc LIMIT 20) s, it_comuni c, it_province p, it_regioni r WHERE c.pro_com=s.pro_com AND c.cod_pro=p.cod_pro AND p.cod_reg=r.cod_reg";					

						
					Load_Data2Array($sql2, $mat, $err);
					$html.='<table class="sortable"><tr><th>Regione</th><th>Provincia</th><th>Comuni</th><th>Differenza KM</th><th>Differenza %</th></tr>';
				
					foreach($mat as $row){
						$html.='<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td>'.htmlCell(($row[3]/1000),Array(),2).''.htmlCell(($row[4]*100),Array(),2).'</tr>';
					}

					$html.='</table>';
					
				}


		}
		else if($most=='top_ten' || $most=='bottom_ten'){
			
			$desc="";

			if($most=='top_ten' ){
 				$title='I comuni pi&ugrave;mappati';
				$desc=' desc ';
			}
			else 
				$title='I comuni meno mappati';

			$ar=Array(Array('Per lunghezza delle strade','length'),Array('Per indice di popolazione','index_pers'),Array('Per indice di superficie','index_area'));						


			foreach($ar as $a){

				$html.='<h3>'.$a[0].'</h3>';

				$SQL=('select r.nome_reg, p.nome_pro, c.nome_com, sum(length) as length, sum(length)/sum(c.pop2001) as index_pers, sum(length)/(c.area) as index_area,   sum(s.length_with_name) as with_name, sum(length)/least(greatest(6*pop2001, area/1000),80*(area/10000)) as index_comb  From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_province p, it_regioni r WHERE  r.cod_reg=p.cod_reg AND p.cod_pro=c.cod_pro '.$dw.' group by  r.nome_reg, p.nome_pro, c.nome_com, date_stat, p.num_com, c.area, c.pop2001  order by '.$a[1].' '.$desc.' LIMIT 40');
				
				Load_Data2Array($SQL, $mat, $err);
				$html.='<table class="sortable"><tr><th>Regione</th><th>Provincia</th><th>Comuni</th><th>Strade</th><th>Indice Pop</th><th>Indice Area</th></tr>';

			
				foreach($mat as $row){
					$html.='<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td>'.htmlCell(($row[3]/1000),Array(),2).''.htmlCell(($row[4]),$style_pop,2).''.htmlCell(($row[5]*10000),$style_area,2).'</tr>';
				}

				$html.='</table>';
			}

		}

	} #end of "most" section
	else if(isset($_GET["loc"])){
		$title="Località senza strade";
		if($cod_reg<>'*'){
			
 
 

				$rw=sprintf(" WHERE cod_reg=%d ",$cod_reg);
				

			    $SQL="select l.tipo_loc , l.denom_loc, l.pop2001, st_x(transform(l.geom,4326)), st_y(transform(l.geom,4326))  from";
				$SQL.=" osm_stat_".$date_stat_tab." o right JOIN ";
				$SQL.=" (select *, (transform(expand(geom,300),900913)) as bb from loc_istat ".$rw." ) ";
				$SQL.="l ON o.intersection && l.bb AND intersects(o.intersection, l.bb) WHERE o.intersection is null order by tipo_loc, pop2001 desc";
	

				$html.='Elenco delle <a href="http://geodati.gfoss.it/wiki/index.php/Dati_liberi_-_Localit%C3%A0_Istat">località istat</a> che sono a più di 300 metri di distanza dal network stradale. ';

				//$SQL2.=sprintf($SQL,$cod_reg)

				Load_Data2Array($SQL, $mat2, $err2);
			$html.='<table class="sortable"><th>Località</th><th>Tipo</th><th>Popolazione</th><th>Mappa</th></tr>';
			foreach($mat2 as $row){
				$html.='<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td><a target="_NEW" href="http://www.openstreetmap.org/?mlat='.$row[4].'&mlon='.$row[3].'&zoom=16">Mappa</a></td>';
				$html.='</tr>';
			}
			$html.='</table>';

			

		}												

	}
	else {

		
		if($cod_reg != null){ 

			if($cod_reg=='*'){
				$title="Dati Provinciali -  Italia ";
				$breadcrumbles.='<a href=?>Italia</a> ';
				exeQueryA(('select cod_pro, nome_pro from it_province '), $dec, $err);

				$SQL=('select c.cod_pro, date_stat, sum(length) as length, sum(length)/sum(c.pop2001) as index_pers, sum(length)/sum(c.area) as index_area,  count(s.pro_com)::float4/p.num_com::float4 as num_comuni From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_province p, it_regioni r WHERE  r.cod_reg=p.cod_reg AND p.cod_pro=c.cod_pro '.$dw.' group by  c.cod_pro, date_stat, p.num_com order by c.cod_pro, date_stat');
				//$SQL2=('select date_stat, sum(length) as length, sum(length)/sum(c.pop2001) as index_pers, sum(length)/sum(c.area) as index_area,  count(s.pro_com)::float4/(r.num_com)::float4 as num_comuni From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_regioni r, it_province p WHERE  r.cod_reg=p.cod_reg  AND p.cod_pro=c.cod_pro  group by  date_stat, r.num_com order by date_stat');

			}			
			else{


				

				Load_Data2Array(sprintf('select nome_reg FROM it_regioni WHERE cod_reg=%d LIMIT 1',$cod_reg), $npq, $err);
				$title="Dati Provinciali -  Regione ".$npq[0][0];
				$breadcrumbles.='<a href=?>Italia</a> - '.$npq[0][0];
				$current_name=$npq[0][0];
				exeQueryA(sprintf('select cod_pro, nome_pro from it_province WHERE cod_reg=%d',$cod_reg), $dec, $err);

				$SQL=sprintf('select c.cod_pro, date_stat, sum(length) as length, sum(length)/sum(c.pop2001) as index_pers, sum(length)/sum(c.area) as index_area,  count(s.pro_com)::float4/p.num_com::float4 as num_comuni From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_province p, it_regioni r WHERE r.cod_reg=%d AND r.cod_reg=p.cod_reg AND p.cod_pro=c.cod_pro '.$dw.' group by  c.cod_pro, date_stat, p.num_com order by c.cod_pro, date_stat',$cod_reg);
				$SQL2=sprintf('select date_stat, sum(length) as length, sum(length)/sum(c.pop2001) as index_pers, sum(length)/sum(c.area) as index_area,  count(s.pro_com)::float4/(r.num_com)::float4 as num_comuni From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_regioni r, it_province p WHERE r.cod_reg=%d AND r.cod_reg=p.cod_reg  AND p.cod_pro=c.cod_pro  group by  date_stat, r.num_com order by date_stat',$cod_reg);
			}

			$current_tag='cod_reg='.$cod_reg;

			
		
		}
		else if($cod_pro != null){
			exeQueryA(sprintf('select pro_com, nome_com, xmin(geom), ymin(geom),xmax(geom),ymax(geom) from (select pro_com, nome_com, transform(geom,4326) as geom from it_comuni WHERE cod_pro=%d) x',$cod_pro), $dec, $err);

			Load_Data2Array(sprintf('select p.nome_pro, r.cod_reg, r.nome_reg FROM  it_province p, it_regioni r WHERE r.cod_reg=p.cod_reg AND  p.cod_pro=%d LIMIT 1',$cod_pro), $npq, $err);
			$title="Dati comunali Provincia di ".$npq[0][0];

			$current_tag='cod_pro='.$cod_pro;
			$current_name=$npq[0][0];

			$breadcrumbles.='<a href=?>Italia</a> - <a href="?cod_reg='.$npq[0][1].'">'.$npq[0][2].'</a> - '.$npq[0][0];


			$SQL=sprintf('select c.pro_com, date_stat, (s.length) as length,  (length)/(c.pop2001) as index_pers, (length)/(c.area) as index_area,   s.length_with_name as with_name, length/least(greatest(6*pop2001, area/1000),80*(area/10000)) as index_comb From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com  WHERE cod_pro=%d '.$dw.'  order by c.pro_com, date_stat',$cod_pro);		
			$SQL2=sprintf('select date_stat, sum(s.length) as length,  sum(length)/sum(c.pop2001) as index_pers, sum(length)/sum(c.area) as index_area, count(s.pro_com)::float4/p.num_com::float4  From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com, it_province p  WHERE c.cod_pro=%d AND c.cod_pro=p.cod_pro  GROUP BY date_stat, p.num_com order by date_stat',$cod_pro);		
			//echo( $SQL);

		}
		else if($cod_com != null){

			Load_Data2Array(sprintf('select x.pro_com, x.nome_com, xmin(geom), ymin(geom),xmax(geom),ymax(geom), r.cod_reg, p.cod_pro, r.nome_reg, p.nome_pro from (select pro_com, nome_com, cod_pro, transform(geom,900913) as geom from it_comuni WHERE pro_com=%d) x, it_province p, it_regioni r WHERE r.cod_reg=p.cod_reg AND p.cod_pro=x.cod_pro ',$cod_com), $dec, $err);

			$title='Comune di '.$dec[0][1];

			$SQL=sprintf('select min(name) , xmin(extent(intersection)), ymin(extent(intersection)), xmax(extent(intersection)), ymax(extent(intersection)) from osm_stat_'.$date_stat_tab.' WHERE pro_com=%d group by lower(name) order by lower(name) ',$cod_com);

			$breadcrumbles.='<a href=?>Italia</a> - <a href="?cod_reg='.$dec[0][6].'">'.$dec[0][8].'</a> - <a href="?cod_pro='.$dec[0][7].'">'.$dec[0][9].'</a>';

			$num = Load_Data2Array($SQL, $mat, $err);
			$body='';

			$stradario=array();

			foreach($mat as $r=>$row){
				
				$name=$row[0];
				$pos = strpos($name,' ')+1;
				if($pos==1)
					$pos=0;

				$dug=substr($name,0,$pos);
				$top=substr($name,$pos);

				if( starts_with($top,'del ') || starts_with($top,'dei ') || starts_with($top,'dei ') || starts_with($top,'per ') ) {
					$pos+=4;
				}
				else if( starts_with($top,'delle ') || starts_with($top,'della ') || starts_with($top,'degli ') ) {
					$pos+=6;
				}
				else if( starts_with($top,"dell'") || starts_with($top,'alle ') || starts_with($top,'alla ') ) {
					$pos+=5;
				}
				else if( starts_with($top,"di ") || starts_with($top,"ai ") || starts_with($top,"al ")) {
					$pos+=3;
				}

				$dug=substr($name,0,$pos);
				$top=substr($name,$pos);

				$init=strtolower(substr($top,0,1));
				$name_ok = $top;

				if(trim($dug)!='') 
					$name_ok.= ' ('.trim($dug).')';				

				$link_ok = '<a href="javascript:setExtent('.$row[1].','.$row[2].','.$row[3].','.$row[4].')">'.$name_ok.'</a>';
				
				if($init!='')
					$stradario[$init][$name_ok]= $link_ok;

			}

			ksort($stradario);

			$lets='';
			$corps='';
			$first=true;

			foreach(  array_keys($stradario) as $let ){
				$lets.="<span><a href=\"javascript:changeTab('div_".$let."')\">".$let."</a></span>";
				
				$list = $stradario[$let];

				ksort($list);				
				
				$corps.="<div  style='display:none' class='tabbed_body' id='div_".$let."'>";
				foreach(  array_keys($list) as $via ){
					$corps.=$list[$via].'<br/>';
				}
				$corps.="</div>";
			}
			
			$corps.="<script>changeTab('div_a');</script>";

			$script.='<script src="http://www.openlayers.org/api/OpenLayers.js"></script>';
			$script.='<script src="script/map.js"></script>';

			$onload.=' onLoad="init(); setExtent('.$dec[0][2].','.$dec[0][3].','.$dec[0][4].','.$dec[0][5].');"';

			$script.='<script></script>';

			$body.='<div id="container">';
				$body.='<div id="map"></div>';
				$body.='<div id="tab" class="tabbed">';
					$body.='<a href="javascript:setExtent('.$dec[0][2].','.$dec[0][3].','.$dec[0][4].','.$dec[0][5].');">Visualizza tutto il comune</a>';
					$body.="<h3>Stradario</h3>";
					$body.='<div class="tabbed_headers">'.$lets.'</div>';
					$body.='<div class="tabbed_bodies">'.$corps.'</div>';
				$body.='</div>';
			$body.='</div>';
		}
		else if ($most_pop!=null){

			$cod_reg2=$_GET["cod_reg2"];

			if($most_pop!='capoluoghi'){

				if($cod_reg2!=null){				
					exeQueryA(sprintf('select pro_com, nome_com, xmin(geom), ymin(geom),xmax(geom),ymax(geom) from (select pro_com, nome_com, transform(geom,4326) as geom from it_comuni WHERE pop2001>%d AND cod_reg=%d) x',$most_pop,$cod_reg2), $dec, $err);			
				}
				else {
					exeQueryA(sprintf('select pro_com, nome_com, xmin(geom), ymin(geom),xmax(geom),ymax(geom) from (select pro_com, nome_com, transform(geom,4326) as geom from it_comuni WHERE pop2001>%d) x',$most_pop), $dec, $err);
				}

				$title="Dati comunali Città con più di ".$most_pop."  abitanti.";
				$breadcrumbles.='<a href=?>Italia</a> - ';
				if($cod_reg2!=null){
					$SQL=sprintf('select c.pro_com, date_stat, (s.length) as length,  (length)/(c.pop2001) as index_pers, (length)/(c.area) as index_area,   s.length_with_name as with_name, length/ least(greatest(6*pop2001, area/1000),80*(area/10000))  From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com   WHERE c.pop2001>=%d AND c.cod_reg=%d  '.$dw.'  order by c.pop2001 desc, date_stat',$most_pop,$cod_reg2);		
				}
				else{
	                               $SQL=sprintf('select c.pro_com, date_stat, (s.length) as length,  (length)/(c.pop2001) as index_pers,(length)/(c.area) as index_area, s.length_with_name as with_name, length/ least(greatest(6*pop2001, area/1000),80*(area/10000)) From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com WHERE c.pop2001>=%d '.$dw.'  order by c.pop2001 desc, date_stat',$most_pop);
				}

				
			}
			else{
				exeQueryA(sprintf('select pro_com, nome_com, xmin(geom), ymin(geom),xmax(geom),ymax(geom) from (select pro_com, nome_com, transform(geom,4326) as geom from it_comuni WHERE nome_com=nome_pro) x',$most_pop), $dec, $err);			
				$title="Dati comunali - Città capoluogo di provincia.";
				$breadcrumbles.='<a href=?>Italia</a> - ';
				$SQL=sprintf('select c.pro_com, date_stat, (s.length) as length,  (length)/(c.pop2001) as index_pers, (length)/(c.area) as index_area,   s.length_with_name as with_name, length/ least(greatest(6*pop2001, area/1000),80*(area/10000)) From osm_stat s RIGHT JOIN it_comuni c ON s.pro_com= c.pro_com  WHERE c.nome_com=c.nome_pro '.$dw.'  order by c.pop2001 desc, date_stat',$most_pop);		
			}

		}
		else {
			$title="Dati regionali";
			$breadcrumbles.='<a href=?>Italia</a> - ';
			exeQueryA('select cod_reg, nome_reg from it_regioni ', $dec, $err);
			$SQL='select r.cod_reg, date_stat,  sum(length) as length, sum(length)/sum(c.pop2001) as index_pers,  sum(length)/sum(c.area) as index_area, count(s.pro_com)::float4/r.num_com::float4 as num_comuni From osm_stat s, it_comuni c, it_regioni r, it_province p WHERE s.pro_com= c.pro_com AND r.cod_reg=p.cod_reg AND p.cod_pro=c.cod_pro '.$dw.' group by  r.cod_reg, date_stat, r.num_com order by r.cod_reg, date_stat';

			$SQL2='select date_stat,  sum(length) as length, sum(length)/sum(c.pop2001) as index_pers,  sum(length)/sum(c.area) as index_area, count(s.pro_com)::float4/8101::float4 as num_comuni From osm_stat s, it_comuni c, it_regioni r, it_province p WHERE s.pro_com= c.pro_com AND r.cod_reg=p.cod_reg AND p.cod_pro=c.cod_pro group by  date_stat order by  date_stat';

		}
		$num = Load_Data2Array($SQL, $mat, $err);
		

		if($historic!=null){

			$val_pos=2; //km di strade
			if($historic=='ind_pop')
				$val_pos=3;
			else if($historic=='ind_sup')
				$val_pos=4;
			else if($historic=='num_com')
				$val_pos=5;
			else if($historic=='ind_comb' && $cod_pro!=null )
				$val_pos=6;

			$res=olap($mat, $val_pos);
		}
		else{
			$res=$mat;
		}
	
		if($body!=null){
			$html.=$body;
		}
		else {		
			$html="<table class='sortable'>";
			if($historic==null){
				$html.="<tr><th>Zona</th><th>km di strade OSM</th><th>metri/abitante</th><th>metri/ettaro</th>";
				if($cod_pro!=null || $most_pop!=null)
					$html.="<th>Percentuale delle strade con nome</th><th>Indice combinato</th></tr>";
				else					
					$html.="<th>Percentuale di comuni coperti</th></tr>";
			}
			foreach($res as $r=>$row){
				$html.="<tr>";
				foreach($row as $c=>$val){
					if($r==0 && $historic!=null){
						$html.="<th>".$val."</th>";
					}
					else if ($c==0){
						$cod='cod_reg';
						if($cod_reg!=null) $cod='cod_pro';
						else if($cod_pro!=null || $most_pop!=null) $cod='cod_com';

						$hist_call='';
						if($historic!=null)
								$hist_call='&historic='.$historic;
							
						$html.="<th><a href='?".$cod."=".$val.$hist_call."'>".$dec[$val][1]."</a></th>";
					}
					else{
					
						if (($historic==null && $c==1 ) ) #escludi la prima colonna
							;
						else if (($historic=='ind_sup')  ) #percentuale
							$html.=htmlCell(($val*10000),$style_area,2);			
						else if (($historic==null && ($c==5 || $c==6)) || ($historic=='num_com') || ($historic=='ind_comb') ) #percentuale
							$html.=htmlCell($val*100,$style_perc,2);			
						else if ($historic==null && $c==2) #numeri alti
							$html.=htmlCell(($val/1000),Array(),2);			
						else if ($historic==null && $c==4) #indice/superficie
							$html.=htmlCell(($val*10000),$style_area,2);			
						else
							$html.=htmlCell($val,$style_pop,2);			
					}
				}
				$html.="</tr>";
			}
			$html.="</table>";
		}
	}

	if($SQL2!=null){
		$row_old;
		Load_Data2Array($SQL2, $mat2, $err2);
			$html.='<h3>Andamento storico</h2><table class="sortable"><th>Data</th><th>km di strade OSM</th><th>metri/abitante</th><th>metri/ettaro</th><th>Comuni Coperti</th><th>KM/gg</th></tr>';
			foreach($mat2 as $row){
				$html.='<tr><td>'.$row[0].'</td>';
				$html.=htmlCell(($row[1]/1000),Array(),2);
				$html.=htmlCell(($row[2]),$style_pop,2);
				$html.=htmlCell(($row[3]*10000),$style_area,2);
				$html.=htmlCell(($row[4]*100),$style_perc,2);
				if($row_old!=null){
					$html.="".htmlCell( ($row[1]-$row_old[1]) / ((strtotime($row[0])-strtotime($row_old[0]))/86.400) , Array(),2)."";
				}
				else{
					$html.="<td></td>";
				}

				$row_old=$row;

				$html.='</tr>';
			}
			$html.='</table>';
		
	}

	if($historic==null && $current_tag==''){
		$left.='<ul><li><a href="?most=wanted">Comuni mancanti</a></li>';
		$left.='<li><a href="?most=top_active">Comuni Hot!</a></li>';
		$left.='<li><a href="?most=top_ten">Comuni pi&ugrave; mappati</a></li>';
		$left.='<li><a href="?most=bottom_ten">Comuni meno mappati</a></li>';
		$left.='<li><a href="?most_pop=25000">Statistiche comuni con pi&ugrave; di 20000 abitanti</a></li>';
		$left.='<li><a href="?cod_reg=*">Statistiche di tutte le province</a></li>';
		$left.='</ul>';
	}
	else if ($cod_reg!=null){
		$left.='<ul>';
		$left.='<li><a href="?most_pop=0&cod_reg2='.$cod_reg.'">Tutti i comuni</a></li>';
		$left.='<li><a href="?most=top_active&cod_reg='.$cod_reg.'">Comuni Hot!</a></li>';
		$left.='<li><a href="?loc=no_road&cod_reg='.$cod_reg.'">Località senza strade</a></li></ul>';
	}

	if($historic!=null){
		$left.="Visualizza i dati per l'area <a href=?".$current_tag.">".$current_name."</a>.<p/>";
	}

	$left.="Visualizza l'evoluzione storica degli indici:<ul>";
	$left.='<li><a href="?historic=length&'.$current_tag.'">Metri di strade inserite</a></li>';
	$left.='<li><a href="?historic=ind_pop&'.$current_tag.'">Indice Metri/abitante</a></li>';
	$left.='<li><a href="?historic=ind_sup&'.$current_tag.'">Indice Metri/ettaro</a></li>';
	if($cod_pro!=null){
		$left.='<li><a href="?historic=num_com&'.$current_tag.'">Percentuale delle strade con nome</a></li>';
		$left.='<li><a href="?historic=ind_comb&'.$current_tag.'">Indice combinato</a></li>';
	}
	else		
		$left.='<li><a href="?historic=num_com&'.$current_tag.'">Percentuale dei comuni</a></li>';
	$left.='</ul>';


	$left.='<hr/>Statistiche dei dati inseriti in <a href="http://www.openstreetmap.org">OpenStreetMap</a>, aggiornate al '.$date_stat.'. Clicca sull\'intestazione della tabella per ordinare i dati.<p>';


	if($cod_com!=null){
		$script.='<style>#left_col{display:none;} #body{width: 100%;}</style>';
	}	

 	header('Content-type: text/html; charset=utf-8');   
   echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" http://www.w3.org/TR/html4/loose.dtd>';
	echo '<html><head><title>OSM Italia - Statistiche</title>';
	echo '<meta http-equiv="Content-type" value="text/html; charset=UTF-8" />';
	echo '<script src="script/sorttable.js"></script>';
	echo '<script src="script/utils.js"></script>';
	echo $script;
	echo '</head><body '.$onload.'>';
	echo '<div id="header">';
		echo '<h1>'.$title.'</h1>';	
	echo '</div>';
	echo '<div id="content">';
		echo '<div id="left_col">'. $left. '</div>';
		echo '<div id="body">';
			echo '<div class="note"></div>';
			echo '<div class="breadcrubles">'.$breadcrumbles.'</div>';
			echo $html;
		echo '</div>';		
	echo '</div>';
	echo '<script type="text/javascript">var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));</script><script type="text/javascript">try {var pageTracker = _gat._getTracker("UA-72991-13");pageTracker._trackPageview();} catch(err) {}</script>';
	echo '</body></html>';

?>
