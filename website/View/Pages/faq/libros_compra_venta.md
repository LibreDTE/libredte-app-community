¿Cuál es el formato de los Libros de Compra y Venta en CSV?
===========================================================

Para facilitar la creación de los Libros de Compra y Venta, el detalle de estos
puede ser proporcionado a través de un archivo CSV, separado por ;

Las columnas de cada archivo CSV se describen a continuación, en ambos libros:

- La columna de inicio es la A
- Los títulos están en la fila 1
- Los datos del detalle inician el la fila 2

Detalle Libro Compra
--------------------

[Ejemplo de Libro de Compra en CSV]({_base}/ejemplos/libro_compras.csv)

- TpoDoc: código del tipo de documento (obligatorio)
- NroDoc: folio del documento (obligatorio)
- TasaImp: tasa del impuesto (obligatorio, si no hay es 0)
- FchDoc: fecha de emisión del documento (obligatorio)
- CdgSIISucur: código numérico de la sucursal asignado por el SII (opcional)
- RUTDoc: RUT del proveedor sin puntos con dv (obligatorio)
- RznSoc: razón social del proveedor (opcional)
- MntExe: monto exento
- MntNeto: monto neto
- MntIVA: monto del IVA (si no se proporciona se calculará)
- IVANoRec: detalle de IVA no recuperable (opcional)
  - CodIVANoRec: código del IVA no recuperable
  - MntIVANoRec: monto del IVA no recuperable (si no se proporciona se calculará)
- FctProp: tasa de proporcionalidad del IVA de uso común, de 0 a 100 (opcional)
- OtrosImp: detalle de otros impuestos (opcional)
  - CodImp: código del otro impuesto
  - TasaImp: tasa del otro impuesto
  - MntImp: monto del otro impuesto (si no se proporciona se calculará)
- MntTotal: monto total del documento (si no se proporciona se calculará)

Detalle Libro Venta
-------------------

[Ejemplo de Libro de Venta en CSV]({_base}/ejemplos/libro_ventas.csv)

- TpoDoc: código del tipo de documento (obligatorio)
- NroDoc: folio del documento (obligatorio)
- TasaImp: tasa del impuesto (obligatorio, si no hay es 0)
- FchDoc: fecha de emisión del documento (obligatorio)
- CdgSIISucur: código numérico de la sucursal asignado por el SII (opcional)
- RUTDoc: RUT del proveedor sin puntos con dv (obligatorio)
- RznSoc: razón social del proveedor (opcional)
- MntExe: monto exento
- MntNeto: monto neto
- MntIVA: monto del IVA (si no se proporciona se calculará)
- OtrosImp: detalle de otros impuestos (opcional)
  - CodImp: código del otro impuesto
  - TasaImp: tasa del otro impuesto
  - MntImp: monto del otro impuesto (si no se proporciona se calculará)
- MntTotal: monto total del documento (si no se proporciona se calculará)
