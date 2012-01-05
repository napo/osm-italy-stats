cd $HOME/script/
dt=`date +"%Y%m%d"`

rm italy*

wget http://download.geofabrik.de/osm/europe/italy.osm.bz2
#wget http://downloads.cloudmade.com/europe/italy/italy.osm.bz2

date

bunzip2 italy.osm.bz2

date

osm2pgsql -s -H 127.0.0.1 -U osm -d osm -p italy_osm italy.osm

date

#indexing imported data
psql -h 127.0.0.1 -U osm -d osm -f post_import.sql

date

#update statistcs
psql -h 127.0.0.1 -U osm -d osm -c "create table osm_stat_$dt as 
select c.cod_reg, c.pro_com, l.highway, l.ref, l.name , intersection 
(l.way, transform(c.geom,900913)) FROM italy_osm_line l, it_comuni c  
WHERE l.highway <>'' AND l.way &&  transform(c.geom,900913) AND 
intersects(l.way ,  transform(c.geom,900913))"

date

#add polines highways type
psql -h 127.0.0.1 -U osm -d osm -c "insert into osm_stat_$dt select 
c.cod_reg, c.pro_com, l.highway, l.ref, l.name , intersection 
(ExteriorRing(l.way), transform(c.geom,900913)) as intersection  FROM 
italy_osm_polygon l, it_comuni c WHERE l.highway <>'' AND l.way &&  
transform(c.geom,900913) AND intersects(l.way ,  
transform(c.geom,900913));"

date

psql -h 127.0.0.1 -U osm -d osm -c "insert into osm_stat 
(date_stat, pro_com, num_street, length, length_with_name) select 
'$dt'::date as date_stat, h.pro_com, count(h.pro_com) as num_street, 
sum(length) as length,  sum(case WHEN name is null THEN 0 else length 
END)/sum(length) as length_with_name  FROM ( select c.pro_com, name,  
sum(length_spheroid(transform(intersection,4326),'SPHEROID[\"WGS84\",6378137,298.25728]')) 
as length from osm_stat_$dt o, it_comuni c WHERE  o.pro_com=c.pro_com 
group by name,  c.pro_com ) h group by pro_com"


date

rm italy.osm

