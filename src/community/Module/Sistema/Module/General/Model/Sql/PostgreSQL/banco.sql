BEGIN;

DROP TABLE IF EXISTS banco CASCADE;
CREATE TABLE banco (
    codigo character(3) NOT NULL,
    banco character varying(40) NOT NULL
);
ALTER TABLE ONLY banco ADD CONSTRAINT banco_pkey PRIMARY KEY (codigo);

COMMIT;

