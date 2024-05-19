BEGIN;

--
-- Actualización 21.10
--

-- se agregan como índices los Track ID
CREATE INDEX dte_emitido_track_id_idx ON dte_emitido (track_id);
CREATE INDEX dte_emitido_cesion_track_id_idx ON dte_emitido (cesion_track_id);
CREATE INDEX dte_venta_track_id_idx ON dte_venta (track_id);
CREATE INDEX dte_compra_track_id_idx ON dte_compra (track_id);
CREATE INDEX dte_guia_track_id_idx ON dte_guia (track_id);
CREATE INDEX dte_boleta_consumo_track_id_idx ON dte_boleta_consumo (track_id);

-- se agrandan campos de montos totales a bigint
ALTER TABLE dte_tmp ALTER total TYPE BIGINT;
ALTER TABLE dte_emitido ALTER exento TYPE BIGINT;
ALTER TABLE dte_emitido ALTER neto TYPE BIGINT;
ALTER TABLE dte_emitido ALTER iva TYPE BIGINT;
ALTER TABLE dte_emitido ALTER total TYPE BIGINT;
ALTER TABLE dte_recibido ALTER exento TYPE BIGINT;
ALTER TABLE dte_recibido ALTER neto TYPE BIGINT;
ALTER TABLE dte_recibido ALTER iva TYPE BIGINT;
ALTER TABLE dte_recibido ALTER total TYPE BIGINT;
ALTER TABLE cobranza ALTER monto TYPE BIGINT;
ALTER TABLE registro_compra ALTER detMntExe TYPE BIGINT;
ALTER TABLE registro_compra ALTER detMntNeto TYPE BIGINT;
ALTER TABLE registro_compra ALTER detMntIVA TYPE BIGINT;
ALTER TABLE registro_compra ALTER detMntTotal TYPE BIGINT;

-- tabla con las sucursales por defecto asignadas a cada usuario
CREATE TABLE contribuyente_usuario_sucursal (
    contribuyente INTEGER NOT NULL,
    usuario INTEGER NOT NULL,
    sucursal_sii INT NOT NULL,
    CONSTRAINT contribuyente_usuario_sucursal_pk PRIMARY KEY (contribuyente, usuario, sucursal_sii),
    CONSTRAINT contribuyente_usuario_sucursal_contribuyente_fk FOREIGN KEY (contribuyente)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT contribuyente_usuario_sucursal_usuario_fk FOREIGN KEY (usuario)
        REFERENCES usuario (id) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

COMMIT;
