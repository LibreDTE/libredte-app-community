BEGIN;

DROP TABLE IF EXISTS actividad_economica CASCADE;
CREATE TABLE actividad_economica (
	codigo INTEGER PRIMARY KEY,
	actividad_economica CHARACTER VARYING (120) NOT NULL,
	afecta_iva BOOLEAN,
	categoria SMALLINT
);
COMMENT ON TABLE actividad_economica IS 'Actividades económicas del país';
COMMENT ON COLUMN actividad_economica.codigo IS 'Código de la actividad económica';
COMMENT ON COLUMN actividad_economica.actividad_economica IS 'Glosa de la actividad económica';
COMMENT ON COLUMN actividad_economica.afecta_iva IS 'Si la actividad está o no afecta a IVA';
COMMENT ON COLUMN actividad_economica.categoria IS 'Categoría a la que pertenece la actividad (tipo de contribuyente)';

COMMIT;
