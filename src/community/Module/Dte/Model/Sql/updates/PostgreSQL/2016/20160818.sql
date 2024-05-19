--
-- Actualización al día 18 de agosto de 2016
--
-- Importante: se cambia la forma en que se manejan los IVA no recuperables y
-- los impuestos adicionales para permitir múltiples de cada uno. Esto implica
-- que ahora se guardará en un campo de TEXTO un JSON con dichos datos. Se
-- deberán convertir los datos actuales si llegasen a existir
--

BEGIN;

ALTER TABLE dte_recibido
	ADD impuesto_puros INTEGER,
	ADD impuesto_cigarrillos INTEGER,
	ADD impuesto_tabaco_elaborado INTEGER,
	ADD impuesto_vehiculos INTEGER,
	ADD numero_interno INTEGER,
	ADD emisor_nc_nd_fc SMALLINT,
	ADD sucursal_sii_receptor INTEGER
;
ALTER TABLE dte_recibido DROP CONSTRAINT dte_recibido_iva_no_recuperable_fk;
ALTER TABLE dte_recibido ALTER iva_no_recuperable TYPE TEXT;
-- UPDATE dte_recibido SET iva_no_recuperable = NULL;
ALTER TABLE dte_recibido DROP CONSTRAINT dte_recibido_impuesto_adicional_fk;
ALTER TABLE dte_recibido ALTER impuesto_adicional TYPE TEXT;
-- UPDATE dte_recibido SET impuesto_adicional = NULL;
ALTER TABLE dte_recibido DROP impuesto_adicional_tasa;
ALTER TABLE dte_recibido ALTER iva_uso_comun TYPE INTEGER;

COMMIT;
