BEGIN;

--
-- Actualización al día 12 de octubre de 2019 (parche versión 20190815)
--

-- actualización tabla para boletas de terceros electrónicas
ALTER TABLE boleta_tercero ADD sucursal_sii INTEGER;

COMMIT;
