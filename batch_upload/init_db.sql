CREATE TABLE it_comuni
(
  id_feature integer NOT NULL,
  pro_com integer,
  cod_reg integer,
  cod_pro integer,
  nome_com text,
  pop2001 integer,
  geom geometry,
  _nome_pro character varying,
  _nome_reg character varying,
  area real,
  code_catasto character varying,
  code_new integer,
  CONSTRAINT it_comuni_pkey PRIMARY KEY (id_feature),
  CONSTRAINT it_comuni_pro_com_key UNIQUE (pro_com),
  CONSTRAINT enforce_dims_geom CHECK (ndims(geom) = 2),
  CONSTRAINT enforce_geotype_geom CHECK (geometrytype(geom) = 'MULTIPOLYGON'::text OR geom IS NULL)
)
WITH (
  OIDS=TRUE
);

-- Index: index_comuni_geom

-- DROP INDEX index_comuni_geom;

CREATE INDEX index_comuni_geom
  ON it_comuni
  USING gist
  (geom);

-- Index: index_geom_com

-- DROP INDEX index_geom_com;

CREATE INDEX index_geom_com
  ON it_comuni
  USING gist
  (geom);


CREATE TABLE it_province
(
  cod_reg integer,
  cod_pro integer NOT NULL,
  nome_pro character varying,
  num_com bigint,
  CONSTRAINT it_province_pkey PRIMARY KEY (cod_pro)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE it_regioni
(
  cod_reg integer NOT NULL,
  nome_reg character varying,
  num_com bigint,
  srid integer,
  CONSTRAINT it_regioni_pkey PRIMARY KEY (cod_reg)
)
WITH (
  OIDS=FALSE
);

CREATE TABLE osm_stat
(
  date_stat date,
  pro_com integer,
  num_street bigint,
  length double precision,
  length_with_name double precision
)
WITH (
  OIDS=FALSE
);

