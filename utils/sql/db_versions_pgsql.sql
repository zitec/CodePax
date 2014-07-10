DROP TABLE IF EXISTS "z_db_versions";
CREATE TABLE z_db_versions
(
  id serial NOT NULL,
  major smallint NOT NULL,
  minor smallint NOT NULL,
  point smallint NOT NULL,
  script_type smallint NOT NULL DEFAULT 0,
  date_added timestamp without time zone NOT NULL,
  CONSTRAINT z_db_versions_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);