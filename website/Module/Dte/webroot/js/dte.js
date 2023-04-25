function Contribuyente() {
    'use strict';
    return;
}

Contribuyente.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.razon_social.value = "";
    f.giro.value = "";
    $(f.actividad_economica).val("").trigger('change.select2');
    f.direccion.value = "";
    $(f.comuna).val("").trigger('change.select2');
    f.telefono.value = "";
    f.email.value = "";
    if (f.config_ambiente_produccion_fecha != undefined) {
        f.config_ambiente_produccion_fecha.value = "";
        f.config_ambiente_produccion_numero.value = "";
    }
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.rut.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.rut) !== true) {
        Form.alert('RUT contribuyente es incorrecto', f.rut);
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.rut.value.charAt(f.rut.value.length - 1),
        rut = f.rut.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (c) {
            f.razon_social.value = c.razon_social;
            f.giro.value = c.giro;
            $(f.actividad_economica).val(c.actividad_economica).trigger('change.select2');
            f.direccion.value = c.direccion;
            $(f.comuna).val(c.comuna).trigger('change.select2');
            f.telefono.value = c.telefono;
            f.email.value = c.email;
            if (f.config_ambiente_produccion_fecha != undefined) {
                f.config_ambiente_produccion_fecha.value = c.config_ambiente_produccion_fecha !== undefined ? c.config_ambiente_produccion_fecha : null;
                f.config_ambiente_produccion_numero.value = c.config_ambiente_produccion_numero !== undefined ? c.config_ambiente_produccion_numero : null ;
            }
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Emisor() {
    'use strict';
    return;
}

Emisor.setDatos = function (form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSoc.value = "";
    f.GiroEmis.value = "";
    $(f.Acteco).val("").trigger('change.select2');
    f.DirOrigen.value = "";
    $(f.CmnaOrigen).val("").trigger('change.select2');
    f.Telefono.value = "";
    f.CorreoEmisor.value = "";
    f.FchResol.value = "";
    f.NroResol.value = "";
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTEmisor.value))
        return;
    // verificar validez del rut
    if (Form.check_rut(f.RUTEmisor) !== true) {
        Form.alert('RUT emisor es incorrecto', f.RUTEmisor);
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTEmisor.value.charAt(f.RUTEmisor.value.length - 1),
        rut = f.RUTEmisor.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    $.ajax({
        type: "GET",
        url: _url+'/api/dte/contribuyentes/info/'+rut,
        dataType: "json",
        success: function (c) {
            f.RznSoc.value = c.razon_social !== undefined ? c.razon_social : null;
            f.GiroEmis.value = c.giro !== undefined ? c.giro : null;
            $(f.Acteco).val(c.actividad_economica !== undefined ? c.actividad_economica : null).trigger('change.select2');
            f.DirOrigen.value = c.direccion !== undefined ? c.direccion : null;
            $(f.CmnaOrigen).val(c.comuna !== undefined ? c.comuna : null).trigger('change.select2');
            f.Telefono.value = c.telefono !== undefined ? c.telefono : null;
            f.CorreoEmisor.value = c.email !== undefined ? c.email : null;
            f.FchResol.value = c.config_ambiente_produccion_fecha !== undefined ? c.config_ambiente_produccion_fecha : null;
            f.NroResol.value = c.config_ambiente_produccion_numero !== undefined ? c.config_ambiente_produccion_numero : null;
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}

function Receptor() {
    'use strict';
    return;
}

Receptor.setDatos = function (form, tipo) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSocRecep.value = "";
    f.GiroRecep.value = "";
    f.DirRecep.value = "";
    $(f.CmnaRecep).val("").trigger('change.select2');
    f.Contacto.value = "";
    f.CorreoRecep.value = "";
    if (f.CdgIntRecep !== undefined) {
        f.CdgIntRecep.value = "";
    }
    if (f.RUTSolicita !== undefined) {
        f.RUTSolicita.value = "";
    }
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTRecep.value)) {
        return;
    }
    // verificar validez del rut
    if (Form.check_rut(f.RUTRecep) !== true) {
        Form.alert('RUT receptor es incorrecto', f.RUTRecep);
        return;
    }
    // si no se indicó tipo se usa el de receptor
    if (tipo === undefined) {
        tipo = 'receptor';
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTRecep.value.charAt(f.RUTRecep.value.length - 1),
        rut = f.RUTRecep.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    url = _url+'/api/dte/contribuyentes/info/'+rut;
    if (tipo == 'receptor') {
        url += '/'+f.RUTEmisor.value+'?tipo=receptor';
    }
    $.ajax({
        type: "GET",
        url: url,
        dataType: "json",
        success: function (c) {
            f.RznSocRecep.value = c.razon_social;
            f.GiroRecep.value = (c.giro!==undefined && c.giro) ? c.giro.substr(0, 40) : '';
            f.DirRecep.value = (c.direccion!==undefined && c.direccion) ? c.direccion : '';
            $(f.CmnaRecep).val((c.comuna!==undefined && c.comuna) ? c.comuna : '').trigger('change.select2');
            f.Contacto.value = (c.telefono!==undefined && c.telefono) ? c.telefono : '';
            f.CorreoRecep.value = (c.email!==undefined && c.email) ? c.email : '';
            if (f.CdgIntRecep !== undefined) {
                f.CdgIntRecep.value = (c.codigo!==undefined && c.codigo) ? c.codigo : '';
            }
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
    // si el RUT es de exportación o boleta no nominativa se quita obligatoriedad campos
    if (rut==55555555 || rut==66666666) {
        $('#CmnaRecepField').removeClass('check notempty');
    } else {
        $('#CmnaRecepField').addClass('check notempty');
    }
}

function DTE() {
    'use strict';
    return;
}

DTE.parseInt = function (valor) {
    var TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    if (TpoDoc!=110 && TpoDoc!=111 && TpoDoc!=112)
        return parseInt(valor);
    return parseFloat(valor);
}

DTE.round = function (valor) {
    var TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    if (TpoDoc!=110 && TpoDoc!=111 && TpoDoc!=112)
        return Math.round(valor);
    return valor;
}

DTE.setTipo = function (tipo) {
    // habilitar u ocultar datos para guía de despacho
    if (tipo==52) {
        $('#datosTransporte').show();
    } else {
        $('#datosTransporte').hide();
    }
    // habilitar u ocultar datos para exportación
    if (tipo==110 || tipo==111 || tipo==112) {
        $('#modalBuscar').hide();
        $('#RUTRecepField').removeAttr('onblur');
        $('#RUTRecepField').attr('readonly', 'readonly');
        $('#GiroRecepField').attr('disabled', 'disabled');
        $('#RUTSolicitaField').attr('disabled', 'disabled');
        $('#TpoMonedaField').removeAttr('disabled');
        $('#NacionalidadField').removeAttr('disabled');
        document.getElementById('RUTRecepField').value = '55.555.555-5';
        document.getElementById('RznSocRecepField').focus();
        document.getElementById('GiroRecepField').value = '';
        // inicio cambio de select a input text. Se esconde y se le cambia el id al select
        if (!document.getElementById('nuevo_div_comuna_receptor')){
            $('#CmnaRecepField').attr('disabled', 'disabled');
            document.getElementById('CmnaRecepField').value = '';
            let div_comuna_receptor = document.getElementById('div_comuna_receptor');
            let nuevo_div_comuna_receptor = document.createElement('div');
            let input_comuna_receptor = document.createElement('input');
            div_comuna_receptor.parentNode.insertBefore(nuevo_div_comuna_receptor, div_comuna_receptor.nextSibling);
            nuevo_div_comuna_receptor.appendChild(input_comuna_receptor);
            nuevo_div_comuna_receptor.setAttribute('id', 'nuevo_div_comuna_receptor');
            nuevo_div_comuna_receptor.setAttribute('class', 'col-md-3 mb-4');
            div_comuna_receptor.style.display = 'none';
            input_comuna_receptor.setAttribute('id','CiudadRecepField');
            input_comuna_receptor.setAttribute('name','CiudadRecep');
            input_comuna_receptor.setAttribute('class','form-control');
            input_comuna_receptor.setAttribute('placeholder','Comuna del receptor');
        }
        // fin cambio select
        $('#datosExportacion').show();
        if (config_extra_indicador_servicio) {
            document.getElementById('IndServicioField').value = config_extra_indicador_servicio;
        }
    } else {
        $('#modalBuscar').show();
        if (document.getElementById('dte_referencia_defecto').value==0) {
            $('#RUTRecepField').attr('onblur', 'Receptor.setDatos(\'emitir_dte\')');
            $('#RUTRecepField').removeAttr('readonly');
        }
        $('#GiroRecepField').removeAttr('disabled');
        $('#RUTSolicitaField').removeAttr('disabled');
        $('#TpoMonedaField').attr('disabled', 'disabled');
        $('#NacionalidadField').attr('disabled', 'disabled');
        $('#datosExportacion').hide();
        // inicio remover el input y mostrar el select
        let nuevo_div_comuna_receptor = document.getElementById('nuevo_div_comuna_receptor');
        let CmnaRecepField_aux = document.getElementById('CmnaRecepField_aux');
        if (nuevo_div_comuna_receptor){
            nuevo_div_comuna_receptor.remove();
        }
        if (CmnaRecepField_aux){
            CmnaRecepField_aux.id = 'CmnaRecepField';
            CmnaRecepField_aux.name = 'CmnaRecep';
            $('#CmnaRecepField').removeAttr('disabled');
            document.getElementById('div_comuna_receptor').style.display = '';
        }
        // fin remover el input y mostrar el select
        if (document.getElementById('IndServicioField').value==4 || document.getElementById('IndServicioField').value==5) {
            document.getElementById('IndServicioField').value = '';
        }
    }
    // agregar observación si existe una predeterminada
    if (emision_observaciones !== null && emision_observaciones[tipo] !== undefined) {
        document.getElementById("TermPagoGlosaField").value = emision_observaciones[tipo];
    }
}

DTE.setFormaPago = function (tipo) {
    // habilitar o ocultar datos para pagos programados
    if (tipo==2) {
        $('#datosPagos').show();
    } else {
        $('#datosPagos').hide();
    }
}

DTE.setMedioPago = function (medio) {
    if (medio == 'PE') {
        document.getElementById("BcoPagoField").value = BcoPago;
        $(document.getElementById("TpoCtaPagoField")).val(TpoCtaPago).trigger('change.select2');
        document.getElementById("NumCtaPagoField").value = NumCtaPago;
    } else {
        document.getElementById("BcoPagoField").value = "";
        $(document.getElementById("TpoCtaPagoField")).val("").trigger('change.select2');
        document.getElementById("NumCtaPagoField").value = "";
    }
}

DTE.setItem = function (contribuyente, codigo) {
    var f = document.getElementById("emitir_dte");
    var cols = codigo.parentNode.parentNode.parentNode.parentNode.childNodes;
    var fecha = document.getElementById("FchVencField").value;
    var sucursal = document.getElementById("CdgSIISucurField").value;
    var receptor_rut = document.getElementById("RUTRecepField").value;
    var receptor_codigo = document.getElementById("CdgIntRecepField").value;
    var lista = document.getElementById("lista_preciosField").value;
    var cantidad = cols[4].childNodes[0].childNodes[0].value;
    var url = _url+'/api/dte/admin/itemes/info/'+contribuyente+'/'+codigo.value
        +'?fecha='+fecha+'&sucursal='+sucursal+'&receptor_rut='+receptor_rut+'&receptor_codigo='+receptor_codigo+'&lista='+lista+'&cantidad='+cantidad
    ;
    if (codigo.value) {
        $.ajax({
            type: "GET",
            url: url,
            dataType: "json",
            success: function (item) {
                // asignar valores del item
                cols[0].childNodes[0].childNodes[0].childNodes[0].value = item.VlrCodigo !== undefined ? item.VlrCodigo : '';
                cols[1].childNodes[0].childNodes[0].value = item.NmbItem !== undefined ? item.NmbItem : '';
                cols[2].childNodes[0].childNodes[0].value = (item.DscItem !== undefined && item.DscItem) ? item.DscItem : '';
                cols[3].childNodes[0].childNodes[0].value = item.IndExe !== undefined ? item.IndExe : 0;
                cols[5].childNodes[0].childNodes[0].value = (item.UnmdItem !== undefined && item.UnmdItem)? item.UnmdItem : '';
                cols[6].childNodes[0].childNodes[0].value = item.PrcItem !== undefined ? item.PrcItem : '';
                cols[7].childNodes[0].childNodes[0].value = item.ValorDR !== undefined ? item.ValorDR : 0;
                cols[8].childNodes[0].childNodes[0].value = item.TpoValor !== undefined ? item.TpoValor : '%';
                if (cols.length == 12) {
                    cols[9].childNodes[0].childNodes[0].value = (item.CodImpAdic !== undefined && item.CodImpAdic>0) ? item.CodImpAdic : '';
                }
                // foco en cantidad sólo si se logró obtener el código
                if (item.VlrCodigo !== undefined) {
                    cols[4].childNodes[0].childNodes[0].focus();
                    cols[4].childNodes[0].childNodes[0].select();
                }
                // calcular valores del dte
                DTE.calcular();
            },
            error: function (jqXHR) {
                cols[0].childNodes[0].childNodes[0].childNodes[0].value = '';
                cols[1].childNodes[0].childNodes[0].value = '';
                cols[2].childNodes[0].childNodes[0].value = '';
                cols[3].childNodes[0].childNodes[0].value = 0;
                cols[4].childNodes[0].childNodes[0].value = 1;
                cols[5].childNodes[0].childNodes[0].value = '';
                cols[6].childNodes[0].childNodes[0].value = '';
                cols[7].childNodes[0].childNodes[0].value = 0;
                cols[8].childNodes[0].childNodes[0].value = '%';
                if (cols.length == 12) {
                    cols[9].childNodes[0].childNodes[0].value = '';
                }
                // no hay stock
                if (jqXHR.status == 413) {
                    var bb = bootbox.dialog({
                        message: '<div class="text-center"><i class="fa fa-info-circle"></i> ' + jqXHR.responseJSON + '</div>',
                        centerVertical: true,
                        closeButton: true,
                        onEscape: true
                    });
                    window.setTimeout(function(){
                        bb.modal('hide');
                    }, 5000);
                }
                // otro error
                else {
                    console.log(jqXHR.responseJSON);
                }
            }
        });
    }
}

DTE.setFechaReferencia = function (contribuyente, field) {
    var cols = field.parentNode.parentNode.parentNode.childNodes;
    var dte = cols[1].childNodes[0].childNodes[0].value;
    var folio = cols[2].childNodes[0].childNodes[0].value;
    if (!__.empty(dte) && !__.empty(folio)) {
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/dte_emitidos/info/'+dte+'/'+folio+'/'+contribuyente,
            dataType: "json",
            success: function (dte) {
                if (dte.fecha) {
                    cols[0].childNodes[0].childNodes[0].value = dte.fecha;
                }
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
            }
        });
    }
}

DTE.calcular = function () {
    var neto = 0, exento = 0, descuento = 0, CodImpAdic, CodImpAdic_tasa, adicional = 0, retencion = 0;
    // realizar cálculo de detalles
    $('input[name="QtyItem[]"]').each(function (i, e) {
        if (!__.empty($(e).val()) && !__.empty($('input[name="PrcItem[]"]').get(i).value)) {
            // calcular subtotal sin aplicar descuento
            $('input[name="subtotal[]"]').get(i).value = DTE.round(parseFloat($('input[name="QtyItem[]"]').get(i).value) * parseFloat($('input[name="PrcItem[]"]').get(i).value));
            // agregar descuento si aplica
            if (!__.empty($('input[name="ValorDR[]"]').get(i).value) && $('input[name="ValorDR[]"]').get(i).value!=0) {
                if ($('select[name="TpoValor[]"]').get(i).selectedOptions[0].value=="%")
                    descuento = DTE.round($('input[name="subtotal[]"]').get(i).value * (parseFloat($('input[name="ValorDR[]"]').get(i).value)/100.0));
                else
                    descuento = DTE.parseInt($('input[name="ValorDR[]"]').get(i).value);
                $('input[name="subtotal[]"]').get(i).value -= descuento;
            }
            if (parseInt($('select[name="IndExe[]"]').get(i).selectedOptions[0].value)===0) {
                neto += DTE.parseInt($('input[name="subtotal[]"]').get(i).value);
            } else if (parseInt($('select[name="IndExe[]"]').get(i).selectedOptions[0].value)===1) {
                exento += DTE.parseInt($('input[name="subtotal[]"]').get(i).value);
            }
            // si existe código de impuesto adicional se contabiliza
            if ($('select[name="CodImpAdic[]"]').get(i) !== undefined && $('select[name="CodImpAdic[]"]').get(i).value) {
                CodImpAdic = $('select[name="CodImpAdic[]"]').get(i).value;
                if (document.getElementById("impuesto_adicional_tipo_" + CodImpAdic + "Field")) {
                    CodImpAdic_tasa = parseFloat(document.getElementById("impuesto_adicional_tasa_" + CodImpAdic + "Field").value);
                    // es adicional / anticipo
                    if (document.getElementById("impuesto_adicional_tipo_" + CodImpAdic + "Field").value == "A") {
                        adicional += DTE.round($('input[name="subtotal[]"]').get(i).value * (CodImpAdic_tasa/100.0));
                    }
                    // es retención
                    else {
                        retencion += DTE.round($('input[name="subtotal[]"]').get(i).value * (CodImpAdic_tasa/100.0));
                    }
                }
            }
        }
    });
    // calcular descuento global si existe el input (contribuyentes con impuestos adicionales no tienen descuentos globales)
    if ($('select[name="TpoValor_global"]').length) {
        // calcular descuento global para neto
        if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
            descuento = DTE.round(neto * (parseFloat($('input[name="ValorDR_global"]').get(0).value)/100.0));
        else
            descuento = DTE.parseInt($('input[name="ValorDR_global"]').get(0).value);
        neto -= descuento;
        if (neto<0)
            neto = 0;
        // calcular descuento global para exento
        if ($('select[name="TpoValor_global"]').get(0).selectedOptions[0].value=="%")
            descuento = DTE.round(exento * (parseFloat($('input[name="ValorDR_global"]').get(0).value)/100.0));
        else
            descuento = DTE.parseInt($('input[name="ValorDR_global"]').get(0).value);
        exento -= descuento;
        if (exento<0)
            exento = 0;
    }
    // asignar neto y exento
    $('input[name="neto"]').val(neto);
    $('input[name="exento"]').val(exento)
    // asignar IVA y monto total
    $('input[name="iva"]').val(DTE.round(neto*(DTE.parseInt($('input[name="tasa"]').val())/100)));
    $('input[name="total"]').val(neto + exento + DTE.parseInt($('input[name="iva"]').val()) + adicional - retencion);
}

DTE.check = function (formulario) {
    var status = true, TpoDoc = parseInt(document.getElementById("TpoDocField").value);
    var dte_check_detalle = [33, 34, 39, 41];
    var n_itemAfecto = 0, n_itemExento = 0;
    var monto_pago;
    // revisión general formulario
    if (!Form.check()) {
        return false;
    }
    // revisión detalle del dte
    $('input[name="QtyItem[]"]').each(function (i, e) {
        $('input[name="NmbItem[]"]').get(i).value = $('input[name="NmbItem[]"]').get(i).value.trim();
        if (__.empty($('input[name="NmbItem[]"]').get(i).value)) {
            Form.alert('En la línea '+(i+1)+', item no puede estar en blanco', $('input[name="NmbItem[]"]').get(i));
            $('input[name="NmbItem[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (dte_check_detalle.indexOf(TpoDoc)!=-1) {
            if (__.empty($(e).val())) {
                Form.alert('En la línea '+(i+1)+', cantidad no puede estar en blanco', $(e));
                $(e).focus();
                status = false;
                return false;
            }
            if (Form.check_real($('input[name="QtyItem[]"]').get(i))!==true) {
                Form.alert('En la línea '+(i+1)+', cantidad debe ser un número (entero o decimal)', $(e));
                $(e).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="PrcItem[]"]').get(i).value)) {
                Form.alert('En la línea '+(i+1)+', precio no puede estar en blanco', $('input[name="PrcItem[]"]').get(i));
                $('input[name="PrcItem[]"]').get(i).focus();
                status = false;
                return false;
            }
            if (Form.check_real($('input[name="PrcItem[]"]').get(i))!==true) {
                Form.alert('En la línea '+(i+1)+', cantidad debe ser un número (entero o decimal)', $('input[name="PrcItem[]"]').get(i));
                $('input[name="PrcItem[]"]').get(i).focus();
                status = false;
                return false;
            }
            if (__.empty($('input[name="ValorDR[]"]').get(i).value)) {
                Form.alert('En la línea '+(i+1)+', descuento no puede estar en blanco', $('input[name="ValorDR[]"]').get(i));
                $('input[name="ValorDR[]"]').get(i).focus();
                status = false;
                return false;
            }
        }
        // si el documento es 34 o 41 forzar que todos los detalles sean exentos
        if (TpoDoc==34 || TpoDoc==41 || TpoDoc==110 || TpoDoc==111 || TpoDoc==112) {
            // sólo se asigna a monto exento si el item no es un monto no facturable
            if ($('select[name="IndExe[]"]').get(i).value != 2) {
                $('select[name="IndExe[]"]').get(i).value = 1;
            }
        }
        // contabilizar items afectos
        if (!parseInt($('select[name="IndExe[]"]').get(i).value))
            n_itemAfecto++;
        else
            n_itemExento++;
    });
    if (!status)
        return false;
    // si no hay afecto pero si exento y el documento es 33 se cambia a 34 o
    // si es 39 se cambia a 41
    if (!n_itemAfecto && n_itemExento) {
        if (TpoDoc==33) {
            document.getElementById("TpoDocField").value = 34;
        }
        if (TpoDoc==39) {
            document.getElementById("TpoDocField").value = 41;
        }
    }
    // revisión referencia del dte
    $('select[name="CodRef[]"]').each(function (i, e) {
        if (__.empty($('select[name="TpoDocRef[]"]').get(i).value)) {
            Form.alert('En la línea '+(i+1)+' de referencia:'+"\n"+'Tipo de documento referenciado no puede estar en blanco', $('select[name="TpoDocRef[]"]').get(i));
            $('select[name="TpoDocRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FolioRef[]"]').get(i).value)) {
            Form.alert('En la línea '+(i+1)+' de referencia:'+"\n"+'Folio no puede estar en blanco', $('input[name="FolioRef[]"]').get(i));
            $('input[name="FolioRef[]"]').get(i).focus();
            status = false;
            return false;
        }
        if (__.empty($('input[name="FchRef[]"]').get(i).value)) {
            Form.alert('En la línea '+(i+1)+' de referencia:'+"\n"+'Fecha no puede estar en blanco', $('input[name="FchRef[]"]').get(i));
            $('input[name="FchRef[]"]').get(i).focus();
            status = false;
            return false;
        }
    });
    if (!status)
        return false;
    // verificar montos programados si es que existen (forma de pago crédito)
    if (document.getElementById("FmaPagoField").value==2 && $('input[name="MntPago[]"]').length) {
        monto_pago = 0;
        $('input[name="MntPago[]"]').each(function (i, m) {
            monto_pago += DTE.parseInt(m.value);
        });
        if (monto_pago != $('input[name="total"]').val()) {
            Form.alert('Monto de pago programado $' + __.num(monto_pago) + '.- no cuadra con el total del documento', $('input[name="total"]'));
            return false;
        }
    }
    // pedir confirmación de generación de factura
    DTE.calcular();
    return Form.confirm(formulario, 'Confirmar '+document.getElementById("TpoDocField").selectedOptions[0].textContent+' por $'+__.num($('input[name="total"]').val())+' a '+$('input[name="RUTRecep"]').val());
}

function dte_recibido_check() {
    var emisor = document.getElementById("emisorField");
    var dte = document.getElementById("dteField");
    var folio = document.getElementById("folioField");
    var receptor = document.getElementById("receptorField");
    if (emisor.value && dte.value && folio.value) {
        estado = Form.check_rut(emisor);
        if (estado !== true) {
            Form.alert(estado.replace("%s", "RUT emisor"), emisor);
            return;
        }
        $.ajax({
            type: "GET",
            url: _url+'/api/dte/dte_recibidos/info/'+emisor.value+'/'+dte.value+'/'+folio.value+'/'+receptor.value,
            dataType: "json",
            success: function (documento) {
                document.getElementById("fechaField").value = documento.fecha;
                document.getElementById("exentoField").value = documento.exento;
                document.getElementById("netoField").value = documento.neto;
                document.getElementById("impuesto_tipoField").value = documento.impuesto_tipo;
                document.getElementById("tasaField").value = documento.tasa;
                document.getElementById("ivaField").value = documento.iva;
                document.getElementById("periodoField").value = documento.periodo;
                document.getElementById("iva_uso_comunField").value = documento.iva_uso_comun;
                document.getElementById("impuesto_adicionalField").value = documento.impuesto_adicional ? documento.impuesto_adicional  : '';
                document.getElementById("impuesto_sin_creditoField").value = documento.impuesto_sin_credito;
                document.getElementById("monto_activo_fijoField").value = documento.monto_activo_fijo;
                document.getElementById("monto_iva_activo_fijoField").value = documento.monto_iva_activo_fijo;
                document.getElementById("iva_no_retenidoField").value = documento.iva_no_retenido;
                document.getElementById("anuladoField").checked = documento.anulado == 'A' ? true : false;
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseJSON);
            }
        });
    }
}

function dte_imprimir(formato, documento, id) {
    // armar URL de donde obtener los datos que se desean imprimir según el formato
    var config = {
        websocket: 'ws://127.0.0.1:2186/print/'+formato,
        compress: 1,
        cedible: 1
    };
    var urls = {
        pdf: {
            cotizacion: _url+'/dte/dte_tmps/cotizacion/{receptor}/{dte}/{codigo}?compress='+config.compress,
            previsualizacion: _url+'/dte/dte_tmps/pdf/{receptor}/{dte}/{codigo}?compress='+config.compress,
            dte_emitido: _url+'/dte/dte_emitidos/pdf/{dte}/{folio}?compress='+config.compress+'&cedible='+config.cedible,
            dte_recibido: _url+'/dte/dte_recibidos/pdf/{emisor}/{dte}/{folio}?compress='+config.compress+'&cedible='+config.cedible
        },
        escpos: {
            cotizacion: _url+'/api/dte/dte_tmps/escpos/{receptor}/{dte}/{codigo}/{emisor}?compress='+config.compress+'&cotizacion=1',
            previsualizacion: _url+'/api/dte/dte_tmps/escpos/{receptor}/{dte}/{codigo}/{emisor}?compress='+config.compress+'&cotizacion=0',
            dte_emitido: _url+'/api/dte/dte_emitidos/escpos/{dte}/{folio}/{emisor}?compress='+config.compress+'&cedible='+config.cedible,
            dte_recibido: _url+'/api/dte/dte_recibidos/escpos/{emisor}/{dte}/{folio}/{receptor}?compress='+config.compress+'&cedible='+config.cedible
        }
    }
    var url = urls[formato][documento];
    for (var key in id) {
        url = url.replace('{'+key+'}', id[key]);
    }
    // obtener datos del archivo que se desea imprimir
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.responseType = 'blob';
    xhr.onload = function(e) {
        if (this.status == 200) {
            var file_code = this.response;
            // consumir el websocket y con esto imprimir
            try {
                var socket = new WebSocket(config.websocket);
                socket.onopen = function (event) {
                    socket.send(file_code);
                };
                socket.onmessage = function (event) {
                    if (event.data !==undefined) {
                        response = JSON.parse(event.data)
                        Form.alert(response.message);
                    }
                }
                socket.onerror=function(event){
                    Form.alert('<p>No fue posible conectar a LibreDTE websocketd</p><p>¿Está en ejecución el cliente de LibreDTE para escritorio?</p><p>Más información en el <a href="https://soporte.sasco.cl/kb/faq.php?id=220" target="_blank">siguiente enlace</a></p>');
                }
            } catch(e) {
                console.log(e);
            }
        }
    };
    xhr.send();
}
