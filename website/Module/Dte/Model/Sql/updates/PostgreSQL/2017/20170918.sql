BEGIN;

--
-- Actualización al día 18 de septiembre de 2017
-- 2 años exactos desde que se liberó la primera versión "útil" de la biblioteca de LibreDTE!!! :-)
--

DROP TABLE IF EXISTS dte_emitido_email CASCADE;
CREATE TABLE dte_emitido_email (
    emisor INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    folio INTEGER NOT NULL,
    certificacion BOOLEAN NOT NULL DEFAULT false,
    email VARCHAR(80) NOT NULL,
    fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    CONSTRAINT dte_emitido_email_pk PRIMARY KEY (emisor, dte, folio, certificacion, email, fecha_hora),
    CONSTRAINT dte_emitido_email_dte_emitido_fk FOREIGN KEY (emisor, dte, folio, certificacion)
        REFERENCES dte_emitido (emisor, dte, folio, certificacion) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE item_clasificacion ALTER superior TYPE VARCHAR(35);

ALTER TABLE dte_recibido
    ADD rcv_accion CHAR(3),
    ADD tipo_transaccion SMALLINT,
    -- esta CONSTRAINT podría ocasionar error ¡se deben corregir inconsistencias antes de ejecutar!
    ADD CONSTRAINT dte_recibido_intercambio_fk FOREIGN KEY (receptor, intercambio, certificacion)
        REFERENCES dte_intercambio (receptor, codigo, certificacion)
        ON UPDATE CASCADE ON DELETE RESTRICT
;

ALTER TABLE dte_emitido ADD receptor_evento CHAR(1);

COMMIT;
