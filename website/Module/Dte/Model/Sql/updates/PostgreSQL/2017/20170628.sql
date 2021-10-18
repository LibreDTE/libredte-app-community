BEGIN;

DROP TABLE IF EXISTS contribuyente_usuario_dte CASCADE;
CREATE TABLE contribuyente_usuario_dte (
    contribuyente INTEGER NOT NULL,
    usuario INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    CONSTRAINT contribuyente_usuario_dte_pk PRIMARY KEY (contribuyente, usuario, dte),
    CONSTRAINT contribuyente_usuario_dte_contribuyente_fk FOREIGN KEY (contribuyente)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT contribuyente_usuario_dte_usuario_fk FOREIGN KEY (usuario)
        REFERENCES usuario (id) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT contribuyente_usuario_dte_dte_fk FOREIGN KEY (dte)
        REFERENCES dte_tipo (codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

COMMIT;
