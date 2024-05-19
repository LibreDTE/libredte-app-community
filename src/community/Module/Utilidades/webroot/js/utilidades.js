function dte_generar_xml_validar(formulario) {
    // validar rut emisor
    if (Form.check.rut(document.getElementById('RUTEmisorField'))!==true) {
        __.alert('RUT emisor incorrecto', document.getElementById('RUTEmisorField'));
        return false;
    }
    // validar rut receptor
    if (Form.check.rut(document.getElementById('RUTRecepField'))!==true) {
        __.alert('RUT receptor incorrecto', document.getElementById('RUTRecepField'));
        return false;
    }
    // confirmar envío y retornar
    return __.confirm(formulario, '¿Está seguro de querer generar el DTE?');
}

function libro_generar_tipo(simplificado) {
    if (simplificado==1) {
        document.getElementById('PeriodoTributarioField').value = '2000-01';
        document.getElementById('FchResolField').value = '2006-01-20';
        document.getElementById('NroResolField').value = '102006';
        document.getElementById('TipoLibroField').value = 'ESPECIAL';
        document.getElementById('FolioNotificacionField').value = '102006';
    } else {
        document.getElementById('PeriodoTributarioField').value = '';
        document.getElementById('FchResolField').value = '';
        document.getElementById('NroResolField').value = '';
        document.getElementById('TipoLibroField').value = 'MENSUAL';
        document.getElementById('FolioNotificacionField').value = '';
    }
}
