# Guía de Configuración del Entorno

Este documento te guía para levantar el proyecto en tu máquina local desde cero.

> Para problemas de Docker más allá de los listados aquí, consulta `DOCKER_GUIDE.md`.

---

## Contenido

- [Prerequisitos](#prerequisitos)
- [Paso 1: Clonar el repositorio](#paso-1-clonar-el-repositorio)
- [Paso 2: Configurar el archivo de entorno](#paso-2-configurar-el-archivo-de-entorno)
- [Paso 3: Levantar los contenedores](#paso-3-levantar-los-contenedores)
- [Paso 4: Instalar dependencias](#paso-4-instalar-dependencias)
- [Paso 5: Generar la clave de la aplicación](#paso-5-generar-la-clave-de-la-aplicación)
- [Paso 6: Correr migraciones y seeders](#paso-6-correr-migraciones-y-seeders)
- [Paso 7: Verificar que todo funciona](#paso-7-verificar-que-todo-funciona)
- [Credenciales de prueba](#credenciales-de-prueba)
- [Paso 8: Correr los tests](#paso-8-correr-los-tests)
- [Problemas comunes](#problemas-comunes)
- [Detener el entorno](#detener-el-entorno)

---

## Prerequisitos

Antes de empezar, instala lo siguiente:

| Herramienta | Enlace | Versión mínima |
|---|---|---|
| Docker Desktop | https://www.docker.com/products/docker-desktop | 4.x |
| Git | https://git-scm.com | 2.x |
| VS Code o PhpStorm | (cualquiera) | — |

Verifica que Docker esté corriendo antes de continuar.

---

## Paso 1: Clonar el repositorio

Obtén el link de tu repositorio desde GitHub Classroom y clónalo:

```bash
git clone https://github.com/patrones-eseno-2026-2/legacy-delivery-TU_USUARIO.git
cd legacy-delivery-TU_USUARIO
```

---

## Paso 2: Configurar el archivo de entorno

```bash
cp .env.example .env
```

No necesitas cambiar nada del `.env` para el entorno local con Docker.

---

## Paso 3: Levantar los contenedores

```bash
docker compose up -d
```

La primera vez descarga las imágenes — puede tardar 2-3 minutos. Cuando termine, verifica que estén corriendo:

```bash
docker compose ps
```

Deberías ver los contenedores `app` y `db` (o similar) en estado `running`.

---

## Paso 4: Instalar dependencias

```bash
docker compose exec app composer install
```

---

## Paso 5: Generar la clave de la aplicación

```bash
docker compose exec app php artisan key:generate
```

Este paso es obligatorio. Laravel usa esta clave para cifrar sesiones y cookies — sin ella la aplicación falla con errores de cifrado. El comando escribe la clave directamente en tu archivo `.env`.

---

## Paso 6: Correr migraciones y seeders

```bash
docker compose exec app php artisan migrate --seed
```

Esto crea todas las tablas y carga datos de prueba (vendors, productos, órdenes, usuarios).

---

## Paso 7: Verificar que todo funciona

Abre tu navegador en `http://localhost:8000` — deberías ver el dashboard del sistema.

También puedes verificar la API:

```bash
curl http://localhost:8000/api/vendors
```

Deberías ver una lista de vendors en formato JSON.

---

## Credenciales de prueba

| Rol | Email | Contraseña |
|---|---|---|
| Admin | admin@legacy.dev | Password1 |
| Customer | customer1@legacy.dev | Password1 |
| Vendor | vendor1@legacy.dev | Password1 |
| Courier | courier1@legacy.dev | Password1 |

---

## Paso 8: Correr los tests

```bash
docker compose exec app php artisan test
```

Los tests deben pasar en verde. Si alguno falla antes de que hayas modificado código, repórtalo al catedrático.

---

## Problemas comunes

**El puerto 80 ya está en uso:**
```bash
# Mac: verifica qué proceso usa el puerto
lsof -i :80
```
Cierra el proceso o cambia el puerto en `docker-compose.yml` (ej. `8080:80`).

**Permisos en Mac/Linux:**
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
```

**Chip M1/M2 (Apple Silicon):**
El `docker-compose.yml` ya incluye `platform: linux/amd64`. Si ves errores de arquitectura, agrega `--platform linux/amd64` al comando de build.

**Windows con WSL2:**
Asegúrate de que Docker Desktop tenga habilitada la integración con WSL2 en Settings → Resources → WSL Integration.

---

## Detener el entorno

```bash
docker compose down
```

Para eliminar también los volúmenes (base de datos):

```bash
docker compose down -v
```
