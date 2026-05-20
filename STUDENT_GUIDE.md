# Guía del Estudiante · Patrones de Diseño de Software

Este documento explica cómo trabajar con el repositorio a lo largo del ciclo.

---

## Contenido

- [El proyecto](#el-proyecto)
- [Workflow semanal](#workflow-semanal)
- [El contrato de tests](#el-contrato-de-tests)
- [Tags de Git](#tags-de-git)
- [La bitácora](#la-bitácora)
- [Cómo pedir ayuda](#cómo-pedir-ayuda)
- [Entorno](#entorno)

---

## El proyecto

Este es el sistema **legacy-delivery**: un marketplace de delivery multi-vendor con problemas de diseño intencionales. Tu trabajo durante el ciclo es identificar esos problemas y refactorizarlos aplicando los patrones de diseño que se verán en clase.

El código funciona, pero está mal diseñado. Esa es la premisa.

---

## Workflow semanal

Cada semana sigue este ciclo:

```
1. Lee el módulo asignado
2. Identifica el problema de diseño
3. Crea un branch para tu trabajo
4. Refactoriza aplicando el patrón
5. Verifica que los tests siguen en verde
6. Escribe tu bitácora
7. Abre un Pull Request a main
```

### Crear un branch para cada mini-entrega

```bash
git checkout -b me1-solid-refactor      # Mini-entrega 1
git checkout -b me2-strategy            # Mini-entrega 2
git checkout -b me3-factory-observer    # Mini-entrega 3
git checkout -b me4-adapter-facade      # Mini-entrega 4
git checkout -b me5-decorator-composite # Mini-entrega 5
```

---

## El contrato de tests

Antes de tocar cualquier código, corre los tests:

```bash
docker compose exec app php artisan test
```

Deben estar en verde. Después de tu refactor, deben seguir en verde. Si tu refactor rompe un test, el comportamiento del sistema cambió — eso no está bien.

Los tests son tu red de seguridad. No los elimines, no los comentes.

---

## Tags de Git

El repositorio usa tags para marcar el estado del sistema en cada etapa del ciclo:

| Tag | Estado |
|---|---|
| `v0` | Punto de partida — el legacy tal como fue entregado |
| `v1` | Referencia tras Mini-entrega 1 (SOLID) |
| `v2` | Referencia tras Mini-entrega 2 (Strategy + Template Method) |
| `v3` | Referencia tras Mini-entrega 3 (Factory + Singleton + Observer) |
| `v4` | Referencia tras Mini-entrega 4 (Adapter + Facade) |
| `v5` | Referencia tras Mini-entrega 5 (Decorator + Composite + CoR) |

Para ver el estado original en cualquier momento:

```bash
git checkout v0
git checkout main  # para volver a tu trabajo
```

---

## La bitácora

La bitácora es **obligatoria** para cada mini-entrega. Vive en la carpeta `/bitacoras/` de tu repo. Usa el template en `/bitacoras/_template.md`.

Cinco preguntas, siempre las mismas:

1. ¿Qué problema de diseño identifiqué? (con cita de archivo y línea)
2. ¿Qué patrón aplicaste y cómo resuelve ese problema?
3. ¿Qué patrón descartaste y por qué?
4. ¿Qué trade-off aceptaste?
5. ¿Qué cambiarías si lo hicieras de nuevo?

Extensión: ½ a 1 página. Sin relleno — el catedrático evalúa precisión, no longitud.

---

## Cómo pedir ayuda

Si te atascas, incluye siempre:

1. Qué estás intentando hacer (en una oración)
2. Qué hiciste (el código o el comando)
3. Qué resultado esperabas
4. Qué resultado obtuviste (pega el error exacto)

Una pregunta con esos cuatro elementos se responde en minutos. Una pregunta sin ellos puede tardar horas.

---

## Entorno

Ver `SETUP_GUIDE.md` para instrucciones de instalación y configuración de Docker.
