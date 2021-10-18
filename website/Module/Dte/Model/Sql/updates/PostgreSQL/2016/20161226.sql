--
-- Actualización al día 26 de diciembre de 2016
--

BEGIN;

ALTER TABLE dte_recibido ALTER folio TYPE BIGINT;
ALTER TABLE dte_emitido ADD iva_fuera_plazo BOOLEAN NOT NULL DEFAULT false;
ALTER TABLE contribuyente_dte ADD activo BOOLEAN NOT NULL DEFAULT true;
ALTER TABLE dte_emitido ADD cesion_xml TEXT;
ALTER TABLE dte_emitido ADD cesion_track_id INTEGER;
ALTER TABLE dte_recibido ALTER tasa TYPE REAL;

COMMIT;
