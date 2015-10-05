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
