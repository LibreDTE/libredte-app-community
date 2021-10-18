BEGIN;

--
-- Actualización al día 21 de mayo de 2018
--

CREATE INDEX dte_intercambio_receptor_certificacion_emisor_idx ON dte_intercambio (receptor, certificacion, emisor);

COMMIT;
