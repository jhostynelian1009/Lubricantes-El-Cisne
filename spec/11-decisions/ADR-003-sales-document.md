# ADR-003: Alcance del documento de venta

- Estado: Provisional.
- Fecha: 2026-08-29.

## Contexto

Los antecedentes mencionan punto de venta, facturación y comprobantes impresos, pero no definen firma electrónica, autorización, ambiente, impuestos ni integración con el SRI. Tratar un recibo interno como factura oficial sería técnicamente y conceptualmente incorrecto.

## Decisión provisional

El MVP registra ventas y genera una vista de **comprobante interno** para impresión mediante navegador. Debe rotularse como documento interno y no como factura electrónica autorizada. Descuentos e impuestos permanecen deshabilitados hasta resolver P-001 y P-003.

## Consecuencias

- Se puede implementar y evaluar inventario/ventas sin simular cumplimiento tributario.
- La empresa debe definir los datos del comprobante.
- Si se requiere facturación electrónica, se abrirá una épica independiente con análisis vigente, proveedor/firma, ambientes, contingencia, documentos, impuestos y pruebas.

## Validación pendiente

- Respuesta de P-001: finalidad legal del documento.
- Respuesta de P-007: campos, tamaño de papel, logo y numeración visible.

