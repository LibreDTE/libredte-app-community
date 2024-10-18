<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero
 * de GNU publicada por la Fundación para el Software Libre, ya sea la
 * versión 3 de la Licencia, o (a su elección) cualquier versión
 * posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU
 * para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General
 * Affero de GNU junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website\Dte;

use sowerphp\autoload\Model;
use website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_intercambio_resultado" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteIntercambioResultado extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'responde' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Responde',
            ],
            'recibe' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Recibe',
            ],
            'codigo' => [
                'type' => self::TYPE_CHAR,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Código',
            ],
            'contacto' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 40,
                'verbose_name' => 'Contacto',
                'show_in_list' => false,
            ],
            'telefono' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 40,
                'verbose_name' => 'Teléfono',
                'show_in_list' => false,
            ],
            'email' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 80,
                'verbose_name' => 'Email',
                'validation' => ['email'],
                'sanitize' => ['strip_tags', 'spaces', 'trim', 'email'],
            ],
            'fecha_hora' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Fecha Hora',
            ],
            'xml' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'XML',
                'show_in_list' => false,
            ],
        ],
    ];

    /**
     * Método que guarda el XML del Resultado de un intercambio.
     */
    public function saveXML($Emisor, $xml)
    {
        // crear respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->loadXML($xml);
        if (!$RespuestaEnvio->esResultadoDTE()) {
            return null; // no es ResultadoDTE se debe procesar otro archivo
        }
        // no cumple con esquema XML del SII (no se procesa)
        if (!$RespuestaEnvio->schemaValidate()) {
            throw new \Exception('Falló la validación del esquema del XML: '.implode(' / ', \sasco\LibreDTE\Log::readAll()));
        }
        // el RUT no es válido
        $Resultado = $RespuestaEnvio->toArray()['RespuestaDTE']['Resultado'];
        if (explode('-', $Resultado['Caratula']['RutRecibe'])[0] != $Emisor->rut) {
            throw new \Exception('El RUT del receptor no es válido.');
        }
        // guardar recepción
        $this->getDatabaseConnection()->beginTransaction();
        $this->responde = explode('-', $Resultado['Caratula']['RutResponde'])[0];
        $this->recibe = $Emisor->rut;
        $this->codigo = md5($xml);
        $this->contacto = !empty($Resultado['Caratula']['NmbContacto'])
            ? substr($Resultado['Caratula']['NmbContacto'], 0, 40)
            : null
        ;
        $this->telefono = !empty($Resultado['Caratula']['FonoContacto'])
            ? substr($Resultado['Caratula']['FonoContacto'], 0, 40)
            : null
        ;
        $this->email = !empty($Resultado['Caratula']['MailContacto'])
            ? substr($Resultado['Caratula']['MailContacto'], 0, 80)
            : null
        ;
        $this->fecha_hora = str_replace('T', ' ', $Resultado['Caratula']['TmstFirmaResp']);
        $this->xml = base64_encode($xml);
        if (!$this->save()) {
            $this->getDatabaseConnection()->rollback();
            throw new \Exception('No fue posible guardar el resultado del intercambio.');
        }
        // procesar cada resultado
        foreach ($RespuestaEnvio->getResultados() as $Resultado) {
            // si el RUT del emisor no corresponde con el del contribuyente el
            // acuse no es para este
            if (explode('-', $Resultado['RUTEmisor'])[0] != $Emisor->rut) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('El RUT del emisor del DTE informado no corresponde.');
            }
            // buscar DTE emitido en el ambiente del emisor
            $DteEmitido = new Model_DteEmitido(
                $Emisor->rut,
                $Resultado['TipoDTE'],
                $Resultado['Folio'],
                $Emisor->enCertificacion()
            );
            // si no existe o si los datos del DTE emitido no corresponden error
            if (
                !$DteEmitido->exists()
                || explode('-', $Resultado['RUTRecep'])[0] != $DteEmitido->receptor
                || $Resultado['FchEmis'] != $DteEmitido->fecha
                || $Resultado['MntTotal'] != $DteEmitido->total
            ) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('DTE informado no existe o sus datos no corresponden.');
            }
            // guardar recibo para el DTE
            $DteIntercambioResultadoDte = new Model_DteIntercambioResultadoDte(
                $DteEmitido->emisor, $DteEmitido->dte, $DteEmitido->folio, $DteEmitido->certificacion
            );
            $DteIntercambioResultadoDte->responde = $this->responde;
            $DteIntercambioResultadoDte->codigo = $this->codigo;
            $DteIntercambioResultadoDte->estado = $Resultado['EstadoDTE'];
            $DteIntercambioResultadoDte->glosa = isset($Resultado['EstadoDTEGlosa'])
                ? substr($Resultado['EstadoDTEGlosa'], 0, 256)
                : ''
            ;
            if (!$DteIntercambioResultadoDte->save()) {
                $this->getDatabaseConnection()->rollback();
                throw new \Exception('No fue posible guardar el DTE del resultado del intercambio.');
            }
        }
        // aceptar transacción
        $this->getDatabaseConnection()->commit();
        return true;
    }

}
