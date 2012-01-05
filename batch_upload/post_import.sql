-- optional, makes it easier to wrap place names
update italy_osm_point set name = replace(name,'-',' ');

-- grant our user access to the data
--grant SELECT on italy_osm_line to "www-data";
--grant SELECT on italy_osm_point to "www-data";
--grant SELECT on italy_osm_polygon to "www-data";

-- correct a few common typos
update italy_osm_line set tunnel='yes' where tunnel='true';
update italy_osm_line set tunnel='no' where tunnel='false';
update italy_osm_line set tunnel='yes' where tunnel='yel';
update italy_osm_line set bridge='yes' where bridge='true';

update italy_osm_line set oneway='yes' where oneway='Yes';
update italy_osm_line set oneway='yes' where oneway='true';
update italy_osm_line set oneway='yes' where oneway='1';

-- rename one of the columns to make it easier to write mapserver expressions
alter table italy_osm_polygon rename column "natural" to nature;

-- create a few indexes on the data for speedier access
create index italy_osm_polygon_building_idx on 
italy_osm_polygon(building);
create index italy_osm_polygon_amenity_idx on 
italy_osm_polygon(amenity);
create index italy_osm_polygon_landuse_idx on 
italy_osm_polygon(landuse);
create index italy_osm_line_highway_idx on italy_osm_line(highway);
create index italy_osm_line_aeroway_idx on italy_osm_line(aeroway);
create index italy_osm_line_railway_idx on italy_osm_line(railway);
create index italy_osm_line_bridge_idx on italy_osm_line(bridge);
create index italy_osm_polygon_leisure_idx on 
italy_osm_polygon(leisure);
create index italy_osm_polygon_aeroway_idx on 
italy_osm_polygon(aeroway);
create index italy_osm_polygon_waterway_idx on 
italy_osm_polygon(waterway);
create index italy_osm_polygon_natural_idx on 
italy_osm_polygon(nature);
create index italy_osm_point_place_idx on italy_osm_point(place);
create index italy_osm_line_zorder_idx on italy_osm_line(z_order);

