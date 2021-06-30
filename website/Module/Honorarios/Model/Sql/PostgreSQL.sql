BEGIN;

-- tabla para boletas de honorarios electrónicas
CREATE TABLE boleta_honorario (
    emisor INTEGER NOT NULL,
    numero INTEGER NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    receptor INTEGER NOT NULL,
    fecha DATE NOT NULL,
    total_honorarios INTEGER NOT NULL,
    total_retencion INTEGER NOT NULL,
    total_liquido INTEGER NOT NULL,
    anulada DATE,
    CONSTRAINT boleta_honorario_pk PRIMARY KEY(emisor, numero),
    CONSTRAINT boleta_honorario_emisor_fk FOREIGN KEY (emisor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT boleta_honorario_receptor_fk FOREIGN KEY (receptor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX boleta_honorario_fecha_idx ON boleta_honorario (receptor, fecha);

-- tabla para boletas de terceros electrónicas
CREATE TABLE boleta_tercero (
    emisor INTEGER NOT NULL,
    numero INTEGER NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    receptor INTEGER NOT NULL,
    fecha DATE NOT NULL,
    fecha_emision DATE NOT NULL,
    total_honorarios INTEGER NOT NULL,
    total_retencion INTEGER NOT NULL,
    total_liquido INTEGER NOT NULL,
    anulada BOOLEAN NOT NULL DEFAULT false,
    sucursal_sii INTEGER,
    CONSTRAINT boleta_tercero_pk PRIMARY KEY(emisor, numero),
    CONSTRAINT boleta_tercero_emisor_fk FOREIGN KEY (emisor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT boleta_tercero_receptor_fk FOREIGN KEY (receptor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX boleta_tercero_fecha_idx ON boleta_tercero (receptor, fecha);

COMMIT;
