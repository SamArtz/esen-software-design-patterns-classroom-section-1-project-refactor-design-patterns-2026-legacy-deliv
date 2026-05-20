# Arquitectura del Sistema

## Contenido

- [Entidades principales](#entidades-principales)
- [Flujo principal: placeOrder()](#flujo-principal-placeorder)
- [Estados de una Order](#estados-de-una-order)
- [Servicios externos](#servicios-externos)
- [Estructura de directorios](#estructura-de-directorios)

---

## Entidades principales

| Entidad | Descripción |
|---------|-------------|
| `User` | Autenticación. Roles: `customer`, `vendor`, `courier`, `admin`. |
| `Customer` | Perfil del cliente. Contiene `placeOrder()`. |
| `Vendor` | Negocio. Tiene productos, categorías, bundles y horario de apertura. |
| `Courier` | Repartidor con ubicación en tiempo real. |
| `Product` | Artículo de un vendor. Precio como `float`. |
| `ProductBundle` | Conjunto de productos con descuento. Soporta anidamiento de bundles. |
| `Category` | Categoría de producto. Soporta jerarquía con `parent_category_id`. |
| `Order` | Pedido central. Máquina de estados con `transitionTo()`. |
| `OrderItem` | Línea de orden. Tipo: `product` o `bundle`. |
| `Discount` | Cupón de descuento. Tipos: `percentage`, `fixed_amount`, `bogo`, `first_purchase`, `free_delivery`. |
| `Payment` | Registro de pago. Subclases: `PaymentInCash`, `PaymentByCard`, `PaymentByTransfer`, `PaymentByWallet`. |
| `PaymentProvider` | Pasarela de pago externa (Wompi, N1co, BAC). |
| `Notification` | Notificación multicanal (`email`, `sms`, `push`, `whatsapp`) con flags de procesamiento. |

---

## Flujo principal: placeOrder()

`Customer::placeOrder()` es el método central del sistema. Ejecuta en secuencia:

```
1. Validar customer (verificado, activo)
2. Validar vendor (activo, dentro de horario)
3. Construir items → calcular subtotal
4. Aplicar descuento
5. Calcular delivery fee
6. Persistir Order + OrderItems
7. Actualizar stock de productos
8. Procesar pago
9. Enviar notificaciones
10. Actualizar loyalty_points del customer
```

---

## Estados de una Order

```
created → paid → accepted → preparing → ready → picked_up → delivered
    ↓        ↓        ↓                                          ↓
cancelled  cancelled  cancelled                               refunded
```

Las transiciones válidas están definidas en `Order::transitionTo()`.

---

## Servicios externos

| Servicio | Clase | Nota |
|----------|-------|------|
| Pasarela Wompi | `App\Services\Payments\WompiHandler` | API en español, monto en dólares |
| Pasarela N1co | `App\Services\Payments\N1coHandler` | Monto en centavos |
| Pasarela BAC | `App\Services\Payments\BacTransferHandler` | Respuesta con código numérico `'00'` |
| Email | `App\Services\EmailService` | Stub — solo loguea |
| SMS | `App\Services\SMSService` | Stub — solo loguea |
| Push | `App\Services\PushService` | Stub — solo loguea |
| WhatsApp | `App\Services\WhatsAppService` | Stub — solo loguea |
| Inventario | `App\Services\InventoryService` | Stub — reserva/libera/confirma stock |
| Auditoría | `App\Services\AuditService` | Stub — registra eventos |
| Métricas | `App\Services\MetricsService` | Stub — incrementa contadores |

---

## Estructura de directorios

```
app/
  Models/           — Entidades del dominio
  Http/
    Controllers/
      Api/          — Endpoints REST (JSON)
      Web/          — Vistas Blade
  Services/
    Payments/       — Handlers de pasarelas (Wompi, N1co, BAC)
  Reports/          — Generadores PDF, CSV, Excel
  Support/          — Logger
  BadPatterns/      — Galería de anti-patrones (solo para S11 M, no tocar)
database/
  migrations/       — Estructura de tablas
  seeders/          — Datos de prueba
resources/views/    — Plantillas Blade
tests/
  Unit/             — Tests de modelos
  Feature/          — Tests HTTP de integración
bitacoras/          — Bitácoras de decisiones del estudiante
```
