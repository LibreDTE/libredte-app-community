BEGIN;

--
-- Actualización al día 01 de enero de 2019
--

-- agregar sucursal a dte_tmp
ALTER TABLE dte_tmp ADD sucursal_sii INTEGER;
CREATE INDEX dte_tmp_sucursal_sii_idx ON dte_tmp (sucursal_sii);

-- agregar usuario a dte_tmp
ALTER TABLE dte_tmp ADD usuario INTEGER; -- debería ser NOT NULL, aplicar en el futuro
ALTER TABLE dte_tmp ADD CONSTRAINT dte_tmp_usuario_fk FOREIGN KEY (usuario)
    REFERENCES usuario (id) MATCH FULL
    ON UPDATE CASCADE ON DELETE RESTRICT;
CREATE INDEX dte_tmp_usuario_idx ON dte_tmp (usuario);

COMMIT;
