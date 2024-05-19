BEGIN;

--
-- Actualización 23.02
--

-- se borran los dte_recibido de dte_intercambio que se borren
ALTER TABLE dte_recibido
    DROP CONSTRAINT dte_recibido_intercambio_fk,
    ADD CONSTRAINT dte_recibido_intercambio_fk FOREIGN KEY (receptor, intercambio, certificacion)
		REFERENCES dte_intercambio (receptor, codigo, certificacion)
		ON UPDATE CASCADE ON DELETE CASCADE
;

-- actualización de índices en tabla dte_emitido
CREATE INDEX dte_emitido_emisor_certificacion_fecha_idx ON dte_emitido (emisor, certificacion, fecha);
DROP INDEX dte_emitido_fecha_emisor_idx;
CREATE INDEX dte_emitido_emisor_certificacion_receptor_idx ON dte_emitido (emisor, certificacion, receptor);
DROP INDEX dte_emitido_receptor_emisor_idx;
CREATE INDEX dte_emitido_emisor_certificacion_usuario_idx ON dte_emitido (emisor, certificacion, usuario);
DROP INDEX dte_emitido_usuario_emisor_idx;

-- actualización de índices en tabla dte_recibido
CREATE INDEX dte_recibido_receptor_certificacion_fecha_idx ON dte_recibido (receptor, certificacion, fecha);
DROP INDEX dte_recibido_fecha_emisor_idx;
CREATE INDEX dte_recibido_receptor_certificacion_emisor_idx ON dte_recibido (receptor, certificacion, emisor);
DROP INDEX dte_recibido_receptor_emisor_idx;

COMMIT;
