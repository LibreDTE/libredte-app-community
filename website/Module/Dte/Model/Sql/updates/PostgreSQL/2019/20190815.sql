BEGIN;

--
-- Actualización al día 15 de agosto de 2019
--

-- índice para búsqueda de contribuyente por email
CREATE INDEX contribuyente_email_idx ON contribuyente (email);

-- tabla para los correos de los DTE temporales
CREATE TABLE dte_tmp_email (
    emisor INTEGER NOT NULL,
    receptor INTEGER NOT NULL,
    dte SMALLINT NOT NULL,
    codigo CHAR(32) NOT NULL,
    email VARCHAR(80) NOT NULL,
    fecha_hora TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    CONSTRAINT dte_tmp_email_pk PRIMARY KEY (emisor, receptor, dte, codigo, email, fecha_hora),
    CONSTRAINT dte_tmp_email_dte_tmp_fk FOREIGN KEY (emisor, receptor, dte, codigo)
        REFERENCES dte_tmp (emisor, receptor, dte, codigo) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- se renombra configuración de aplicación dropbox
UPDATE contribuyente_config SET configuracion = 'apps' WHERE configuracion = 'respaldos' AND variable = 'dropbox';

-- tabla para registro de compras
CREATE TABLE registro_compra (
    -- campos de LibreDTE
    receptor INTEGER NOT NULL,
    periodo INTEGER NOT NULL,
    estado SMALLINT NOT NULL CHECK (estado IN (0, 1, 2, 3)), -- 0: PENDIENTE, 1: REGISTRO, 2: NO_INCLUIR, 3: RECLAMADO
    certificacion BOOLEAN NOT NULL DEFAULT false,
    -- campos que vienen del SII en la sincronización (se dejaron los mismos nombres del SII por simplicidad)
    -- postgresql transformará cada campo a minúsculas completo
    dhdrCodigo BIGINT NOT NULL,
    dcvCodigo BIGINT NOT NULL,
    dcvEstadoContab VARCHAR(20),
    detCodigo BIGINT NOT NULL,
    detTipoDoc SMALLINT NOT NULL,
    detRutDoc INTEGER NOT NULL,
    detNroDoc INTEGER NOT NULL,
    detFchDoc DATE NOT NULL,
    detFecAcuse TIMESTAMP WITHOUT TIME ZONE,
    detFecReclamado TIMESTAMP WITHOUT TIME ZONE,
    detFecRecepcion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    detMntExe INTEGER,
    detMntNeto INTEGER,
    detMntActFijo INTEGER,
    detMntIVAActFijo INTEGER,
    detMntIVANoRec INTEGER,
    detMntCodNoRec INTEGER,
    detMntSinCredito INTEGER,
    detMntIVA INTEGER,
    detMntTotal INTEGER NOT NULL,
    detTasaImp SMALLINT,
    detAnulado BOOLEAN,
    detIVARetTotal INTEGER,
    detIVARetParcial INTEGER,
    detIVANoRetenido INTEGER,
    detIVAPropio INTEGER,
    detIVATerceros INTEGER,
    detIVAUsoComun INTEGER,
    detLiqRutEmisor INTEGER,
    detLiqValComNeto INTEGER,
    detLiqValComExe INTEGER,
    detLiqValComIVA INTEGER,
    detIVAFueraPlazo INTEGER,
    detTipoDocRef SMALLINT,
    detFolioDocRef INTEGER,
    detExpNumId VARCHAR(10),
    detExpNacionalidad SMALLINT,
    detCredEc INTEGER,
    detLey18211 INTEGER,
    detDepEnvase INTEGER,
    detIndSinCosto SMALLINT,
    detIndServicio SMALLINT,
    detMntNoFact INTEGER,
    detMntPeriodo INTEGER,
    detPsjNac INTEGER,
    detPsjInt INTEGER,
    detNumInt INTEGER,
    detCdgSIISucur INTEGER,
    detEmisorNota INTEGER,
    detTabPuros INTEGER,
    detTabCigarrillos INTEGER,
    detTabElaborado INTEGER,
    detImpVehiculo INTEGER,
    detTpoImp SMALLINT NOT NULL,
    detTipoTransaccion SMALLINT NOT NULL,
    detEventoReceptor CHAR(3),
    detEventoReceptorLeyenda VARCHAR(200),
    cambiarTipoTran INTEGER,
    detPcarga INTEGER NOT NULL,
    totalDtoiMontoImp INTEGER,
    totalDinrMontoIVANoR INTEGER,
    CONSTRAINT registro_compra_pk PRIMARY KEY (detRutDoc, detTipoDoc, detNroDoc, certificacion),
    CONSTRAINT registro_compra_receptor_fk FOREIGN KEY (receptor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT registro_compra_emisor_fk FOREIGN KEY (detRutDoc)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT registro_compra_liquidacion_emisor_fk FOREIGN KEY (detLiqRutEmisor)
        REFERENCES contribuyente (rut) MATCH FULL
        ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE INDEX registro_compra_periodo_idx ON registro_compra(receptor, periodo, certificacion, estado);
CREATE INDEX registro_compra_detFecRecepcion_idx ON registro_compra(receptor, detFecRecepcion, certificacion, estado);

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
