BEGIN;

--
-- Actualización 20.10
--

-- modificaciones para tabla de temporales y emitidos para soportar datos extras
ALTER TABLE dte_tmp ADD extra TEXT;
ALTER TABLE dte_emitido ADD extra TEXT;

COMMIT;
