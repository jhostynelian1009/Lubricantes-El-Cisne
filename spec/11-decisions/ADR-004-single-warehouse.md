# ADR-004: Una empresa, sucursal y bodega

- Estado: Provisional.
- Fecha: 2026-08-29.

## Contexto

El material recuperado identifica a Lubricantes «El Cisne» en San Lorenzo y no define sucursales, bodegas o transferencias. Introducir multi-bodega modifica cada movimiento, consulta, permiso y reporte.

## Decisión provisional

El MVP administra una empresa, una sucursal y una existencia global por producto. No se crean tablas de bodegas ni transferencias hasta responder P-006.

## Consecuencias

- Modelo y operación iniciales más simples.
- Si existe más de una ubicación física con saldos independientes, esta decisión debe reemplazarse antes de implementar K-004.

