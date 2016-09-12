function dte_generar_xml_validar() {
    // validar rut emisor
    if (Form.check_rut(document.getElementById('RUTEmisorField'))!==true) {
        alert('RUT emisor incorrecto');
        return false;
    }
    // validar rut receptor
    if (Form.check_rut(document.getElementById('RUTRecepField'))!==true) {
        alert('RUT receptor incorrecto');
        return false;
    }
    // confirmar envío y retornar
    return Form.checkSend('¿Está seguro de querer generar el DTE?');
}

function dte_generar_xml_plantilla(id) {
    if (plantillas_dte[id]===undefined) {
        document.getElementById("documentosField").value = "";
    } else {
        if (typeof atob == 'function') {
            document.getElementById("documentosField").value = atob(plantillas_dte[id]);
        } else {
            alert('Lo siento, no tienes soporte en tu navegador web para usar las plantillas');
        }
    }
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
