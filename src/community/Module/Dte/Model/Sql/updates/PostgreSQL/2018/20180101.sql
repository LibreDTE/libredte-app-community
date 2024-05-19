BEGIN;

--
-- Actualización al día 01 de enero de 2018
--

-- tabla para los datos extra del usuario (api, configuraciones, etc, propias de la aplicación)
-- estricto rigor es un cambio a una tabla del framework, pero se deja acá para que no se olvide actualizar
DROP TABLE IF EXISTS usuario_config CASCADE;
CREATE TABLE usuario_config (
    usuario INTEGER NOT NULL,
    configuracion VARCHAR(32) NOT NULL,
    variable VARCHAR(64) NOT NULL,
    valor TEXT,
    json BOOLEAN NOT NULL DEFAULT false,
    CONSTRAINT usuario_config_pkey PRIMARY KEY (usuario, configuracion, variable),
    CONSTRAINT usuario_config_usuario_fk FOREIGN KEY (usuario)
                REFERENCES usuario (id) MATCH FULL
                ON UPDATE CASCADE ON DELETE CASCADE
);

COMMIT;
