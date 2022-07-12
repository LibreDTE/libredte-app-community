BEGIN;

--
-- Actualización 22.12
--

-- se borran los dte_recibido de dte_intercambio que se borren
ALTER TABLE dte_recibido
    DROP CONSTRAINT dte_recibido_intercambio_fk,
    ADD CONSTRAINT dte_recibido_intercambio_fk FOREIGN KEY (receptor, intercambio, certificacion)
		REFERENCES dte_intercambio (receptor, codigo, certificacion)
		ON UPDATE CASCADE ON DELETE CASCADE
;

COMMIT;
