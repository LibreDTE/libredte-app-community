BEGIN;

--
-- Actualización 20.07
--

--
-- Función que indica si un valor es no un número
--
DROP FUNCTION IF EXISTS is_numeric(v_number TEXT);
CREATE OR REPLACE FUNCTION is_numeric(v_number TEXT)
RETURNS BOOLEAN
AS $$
DECLARE X NUMERIC;
BEGIN
    x = v_number::NUMERIC;
    RETURN TRUE;
EXCEPTION WHEN others THEN
    RETURN FALSE;
END
$$ LANGUAGE plpgsql;

--
-- Función que entrega el detalle de los items de un XML de un DTE cualquiera (se pasa el XML)
--
DROP FUNCTION IF EXISTS dte_get_detalle(xml TEXT);
CREATE OR REPLACE FUNCTION dte_get_detalle(xml TEXT)
RETURNS TABLE (NroLinDet SMALLINT, TpoCodigo VARCHAR(10), VlrCodigo VARCHAR(35), IndExe SMALLINT, NmbItem VARCHAR(80), QtyItem REAL, UnmdItem VARCHAR(4), PrcItem REAL, DescuentoPct REAL, DescuentoMonto REAL, CodImpAdic SMALLINT, MontoItem REAL)
AS $$
DECLARE nodos XML[];
DECLARE nodo XML;
DECLARE fila RECORD;
BEGIN
    -- obtener todos los nodos con el detalle del documento XML
    IF xml IS NOT NULL THEN
        SELECT XPATH('/n:*/n:SetDTE/n:DTE/n:*/n:Detalle', CONVERT_FROM(DECODE(xml, 'base64'), 'ISO8859-1')::XML, '{{n,http://www.sii.cl/SiiDte}}') INTO nodos;
        -- iterar cada nodo de detalle encontrato para obtener los campos de cada detalle
        FOREACH nodo IN ARRAY nodos
        LOOP
            -- seleccionar los campos de interés del detalle
            SELECT
                array_to_string(XPATH('/n:Detalle/n:NroLinDet/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS NroLinDet,
                array_to_string(XPATH('/n:Detalle/n:CdgItem/n:TpoCodigo/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS TpoCodigo,
                array_to_string(XPATH('/n:Detalle/n:CdgItem/n:VlrCodigo/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS VlrCodigo,
                array_to_string(XPATH('/n:Detalle/n:IndExe/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS IndExe,
                array_to_string(XPATH('/n:Detalle/n:NmbItem/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS NmbItem,
                array_to_string(XPATH('/n:Detalle/n:QtyItem/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS QtyItem,
                array_to_string(XPATH('/n:Detalle/n:UnmdItem/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS UnmdItem,
                array_to_string(XPATH('/n:Detalle/n:PrcItem/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS PrcItem,
                array_to_string(XPATH('/n:Detalle/n:DescuentoPct/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS DescuentoPct,
                array_to_string(XPATH('/n:Detalle/n:DescuentoMonto/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS DescuentoMonto,
                array_to_string(XPATH('/n:Detalle/n:CodImpAdic/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS CodImpAdic,
                array_to_string(XPATH('/n:Detalle/n:MontoItem/text()[1]', nodo, '{{n,http://www.sii.cl/SiiDte}}'), '') AS MontoItem
            INTO
                fila
            ;
            -- asignar para retornar en la tabla de la función
            NroLinDet := fila.NroLinDet;
            TpoCodigo := NULLIF(fila.TpoCodigo, '');
            VlrCodigo := NULLIF(fila.VlrCodigo, '');
            IndExe := NULLIF(fila.IndExe, '');
            NmbItem := NULLIF(fila.NmbItem, '');
            QtyItem := NULLIF(fila.QtyItem, '');
            UnmdItem := NULLIF(fila.UnmdItem, '');
            PrcItem := NULLIF(fila.PrcItem, '');
            DescuentoPct := NULLIF(fila.DescuentoPct, '');
            DescuentoMonto := NULLIF(fila.DescuentoMonto, '');
            CodImpAdic := NULLIF(fila.CodImpAdic, '');
            MontoItem := NULLIF(fila.MontoItem, '');
            RETURN NEXT;
        END LOOP;
    END IF;
END
$$ LANGUAGE plpgsql;

COMMIT;
