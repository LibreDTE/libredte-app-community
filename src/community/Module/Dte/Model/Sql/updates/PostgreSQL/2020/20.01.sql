BEGIN;

--
-- Actualización 20.01
--

-- actualización tabla emitidos y recibidos
ALTER TABLE dte_emitido ADD fecha_hora_creacion TIMESTAMP WITHOUT TIME ZONE;
ALTER TABLE dte_recibido ADD fecha_hora_creacion TIMESTAMP WITHOUT TIME ZONE;

-- asignar datos de fecha_hora_creacion para las tablas
UPDATE dte_emitido SET fecha_hora_creacion = (fecha || ' 00:00:00')::TIMESTAMP WHERE fecha_hora_creacion IS NULL;
UPDATE dte_recibido SET fecha_hora_creacion = (fecha || ' 00:00:00')::TIMESTAMP WHERE fecha_hora_creacion IS NULL;

-- asignar como NOT NULL las columnas fecha_hora_creacion
ALTER TABLE dte_emitido ALTER fecha_hora_creacion SET NOT NULL;
ALTER TABLE dte_recibido ALTER fecha_hora_creacion SET NOT NULL;

COMMIT;
