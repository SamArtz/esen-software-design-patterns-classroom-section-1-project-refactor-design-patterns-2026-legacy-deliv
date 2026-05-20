# Guía de Docker · Legacy Delivery

Este documento explica qué es Docker, cómo instalarlo en tu sistema operativo, cómo encaja con el proyecto Laravel, y cómo resolver los problemas más comunes.

Si solo quieres levantar el proyecto, ve a `SETUP_GUIDE.md`. Esta guía es para cuando algo no funciona o quieres entender qué está pasando.

---

## Contenido

- [¿Qué es Docker y por qué lo usamos?](#qué-es-docker-y-por-qué-lo-usamos)
- [Instalación por sistema operativo](#instalación-por-sistema-operativo)
  - [macOS](#macos)
  - [Windows](#windows)
  - [Linux (Ubuntu / Debian)](#linux-ubuntu--debian)
- [Cómo encaja Docker con Laravel](#cómo-encaja-docker-con-laravel)
- [Comandos esenciales de Docker Compose](#comandos-esenciales-de-docker-compose)
- [Troubleshooting](#troubleshooting)
- [Preguntas frecuentes](#preguntas-frecuentes)

---

## ¿Qué es Docker y por qué lo usamos?

Docker permite empaquetar una aplicación junto con todo lo que necesita para correr (PHP, MySQL, Redis, extensiones) en **contenedores** — entornos aislados que funcionan igual en cualquier máquina.

Sin Docker, cada estudiante tendría que instalar PHP 8.3, MySQL 8.0, Redis, y las extensiones correctas en su sistema. Eso genera horas de problemas de compatibilidad entre versiones y sistemas operativos.

Con Docker, el entorno está definido en dos archivos del proyecto:

- `Dockerfile` — describe cómo construir la imagen de la aplicación (PHP 8.3 + extensiones + Composer)
- `docker-compose.yml` — define los tres servicios que componen el sistema:

| Servicio | Imagen | Puerto | Función |
|----------|--------|--------|---------|
| `app` | PHP 8.3-fpm (custom) | 8000 | La aplicación Laravel |
| `db` | MySQL 8.0 | 3306 | Base de datos |
| `redis` | Redis Alpine | 6379 | Caché y sesiones |

Los tres corren juntos y se comunican entre sí por red interna. Tú solo interactúas con `app` en el puerto 8000.

---

## Instalación por sistema operativo

### macOS

**Opción recomendada: Docker Desktop**

1. Descarga Docker Desktop desde [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Abre el `.dmg` y arrastra Docker a Aplicaciones
3. Abre Docker Desktop desde Aplicaciones y espera a que el ícono de la ballena en la barra de menú deje de animarse
4. Verifica en terminal:

```bash
docker --version
docker compose version
```

Deberías ver algo como `Docker version 26.x.x` y `Docker Compose version v2.x.x`.

**Mac con Apple Silicon (M1/M2/M3/M4)**

El `docker-compose.yml` ya incluye `platform: linux/amd64` donde es necesario. Si ves errores de arquitectura al hacer build, agrega esto al servicio `app` en `docker-compose.yml`:

```yaml
app:
  platform: linux/amd64
  build: ...
```

---

### Windows

**Requisito previo: WSL2**

Docker Desktop en Windows requiere WSL2 (Windows Subsystem for Linux 2). Si no lo tienes:

```powershell
# Ejecutar en PowerShell como Administrador
wsl --install
```

Reinicia tu computadora después de instalar WSL2.

**Instalar Docker Desktop**

1. Descarga Docker Desktop desde [docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Durante la instalación, asegúrate de que "Use WSL 2 instead of Hyper-V" esté marcado
3. Abre Docker Desktop y en Settings → Resources → WSL Integration, activa la integración con tu distribución de Linux (normalmente Ubuntu)
4. Verifica en PowerShell o en la terminal de WSL:

```bash
docker --version
docker compose version
```

**Importante:** Clona el repositorio dentro del sistema de archivos de WSL, no en `/mnt/c/`. El rendimiento con volúmenes de Docker es significativamente mejor así.

```bash
# En la terminal de WSL (Ubuntu)
cd ~
git clone <tu-repo-url>
cd legacy-delivery
```

---

### Linux (Ubuntu / Debian)

```bash
# Desinstalar versiones anteriores si las hay
sudo apt-get remove docker docker-engine docker.io containerd runc

# Instalar dependencias
sudo apt-get update
sudo apt-get install ca-certificates curl gnupg lsb-release

# Agregar el repositorio oficial de Docker
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar Docker Engine y Compose
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Agregar tu usuario al grupo docker (para no usar sudo siempre)
sudo usermod -aG docker $USER
newgrp docker

# Verificar
docker --version
docker compose version
```

---

## Cómo encaja Docker con Laravel

El proyecto tiene tres capas que Docker une:

```
Tu navegador / Postman
        ↓ :8000
   Contenedor app
   (PHP 8.3 + Laravel)
        ↓ red interna
   Contenedor db          Contenedor redis
   (MySQL 8.0)            (Redis Alpine)
```

**El volumen** en `docker-compose.yml` mapea tu carpeta local al contenedor:

```yaml
volumes:
  - .:/var/www/html
```

Esto significa que **cuando editas un archivo en tu editor, el cambio es inmediato en el contenedor**. No necesitas hacer build cada vez que cambias código.

**Los comandos de Artisan** siempre se ejecutan dentro del contenedor `app`:

```bash
# Correr migraciones
docker compose exec app php artisan migrate

# Correr tests
docker compose exec app php artisan test

# Limpiar caché
docker compose exec app php artisan cache:clear

# Abrir una shell dentro del contenedor
docker compose exec app bash
```

**Composer** también corre dentro del contenedor:

```bash
docker compose exec app composer install
docker compose exec app composer require nombre/paquete
```

---

## Comandos esenciales de Docker Compose

| Comando | Qué hace |
|---------|----------|
| `docker compose up -d` | Levanta todos los servicios en background |
| `docker compose down` | Detiene y elimina los contenedores |
| `docker compose down -v` | Detiene, elimina contenedores **y volúmenes** (borra la BD) |
| `docker compose ps` | Muestra el estado de los servicios |
| `docker compose logs app` | Muestra los logs de la aplicación |
| `docker compose logs -f app` | Muestra logs en tiempo real (Ctrl+C para salir) |
| `docker compose exec app bash` | Abre una shell dentro del contenedor |
| `docker compose build --no-cache` | Reconstruye la imagen desde cero |
| `docker compose restart app` | Reinicia solo el servicio app |

---

## Troubleshooting

### El puerto 8000 ya está en uso

```
Error: Bind for 0.0.0.0:8000 failed: port is already allocated
```

**Mac/Linux:** Encuentra qué proceso usa el puerto:
```bash
lsof -i :8000
# Mata el proceso por su PID
kill -9 <PID>
```

**Alternativa:** Cambia el puerto en `docker-compose.yml`:
```yaml
ports:
  - "8080:8000"  # Accede en http://localhost:8080
```

---

### El puerto 3306 ya está en uso

Tienes MySQL instalado localmente y corriendo. Detente el servicio local:

```bash
# Mac con Homebrew
brew services stop mysql

# Linux
sudo systemctl stop mysql

# Windows (en PowerShell como Admin)
Stop-Service MySQL80
```

O cambia el puerto expuesto del contenedor:
```yaml
db:
  ports:
    - "3307:3306"
```

---

### `docker compose up` falla con "permission denied"

**Linux:** Tu usuario no está en el grupo `docker`:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

**Mac/Windows:** Docker Desktop no está corriendo. Ábrelo desde Aplicaciones.

---

### Los cambios en el código no se reflejan

Verifica que el volumen esté montado correctamente:
```bash
docker compose exec app ls /var/www/html
```

Deberías ver los archivos de tu proyecto. Si no aparecen, revisa que estés ejecutando `docker compose up` desde la carpeta raíz del proyecto.

---

### `php artisan migrate` falla con "Connection refused"

La base de datos no está lista todavía. El `docker-compose.yml` tiene un healthcheck, pero a veces MySQL tarda más de lo esperado en iniciar.

Espera 10-15 segundos y vuelve a intentar. Si el problema persiste:

```bash
# Verifica que el contenedor db esté corriendo y healthy
docker compose ps

# Ve los logs de la base de datos
docker compose logs db
```

Si ves `mysqld: ready for connections`, MySQL está listo. Si ves errores, continúa leyendo.

---

### MySQL no arranca — error de permisos en el volumen

```
[ERROR] --initialize specified but the data directory has files in it.
```

El volumen tiene datos de una instalación anterior incompatible. Limpia y reinicia:

```bash
docker compose down -v   # Elimina contenedores y volúmenes
docker compose up -d     # Levanta de nuevo (MySQL inicializa limpio)
docker compose exec app php artisan migrate --seed
```

**Advertencia:** `down -v` borra todos los datos de la base de datos.

---

### El build falla con errores de red / no descarga paquetes

Problema de conectividad durante el build. Intenta:

```bash
# Limpiar caché de Docker y reconstruir
docker compose down
docker system prune -f
docker compose build --no-cache
docker compose up -d
```

---

### Permisos en `storage/` o `bootstrap/cache/`

```
The stream or file "storage/logs/laravel.log" could not be opened: failed to open stream: Permission denied
```

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

---

### En Windows: los archivos se guardan pero Laravel no los ve

Estás trabajando en `/mnt/c/` en lugar del sistema de archivos de WSL. Los volúmenes de Docker tienen problemas de rendimiento y sincronización con `/mnt/c/`.

Solución: clona el repositorio dentro de WSL:
```bash
# En la terminal de Ubuntu (WSL)
cd ~
git clone <tu-repo-url>
```

Y abre VS Code desde WSL:
```bash
code .
```

---

### Apple Silicon: `no matching manifest for linux/arm64`

Alguna imagen no tiene versión para ARM. Agrega `platform: linux/amd64` al servicio que falla en `docker-compose.yml`:

```yaml
services:
  app:
    platform: linux/amd64
    build: ...
```

---

### Los tests fallan con error de base de datos en CI

Los tests usan SQLite en memoria (configurado en `phpunit.xml`) — no necesitan el contenedor de MySQL. Si un test falla con error de conexión a base de datos, el problema es otro. Revisa el mensaje exacto del test.

---

### No encuentro el error en los logs

```bash
# Ver todos los logs recientes
docker compose logs --tail=50

# Ver logs de un servicio específico en tiempo real
docker compose logs -f app

# Ver logs de Laravel directamente
docker compose exec app tail -f storage/logs/laravel.log
```

---

## Preguntas frecuentes

**¿Tengo que hacer `docker compose build` cada vez que cambio código?**
No. El volumen sincroniza tu carpeta local con el contenedor en tiempo real. Solo necesitas hacer build si cambias el `Dockerfile` o instalas nuevos paquetes de PHP.

**¿Puedo conectarme a la base de datos con TablePlus o DBeaver?**
Sí. El contenedor expone MySQL en `localhost:3306` con estas credenciales:
- Host: `127.0.0.1`
- Puerto: `3306`
- Base de datos: `legacy_delivery`
- Usuario: `legacy_user`
- Contraseña: `legacy_pass`

**¿Qué pasa con mis datos si hago `docker compose down`?**
Los datos persisten en el volumen `db_data`. Solo se eliminan con `docker compose down -v`.

**¿Puedo correr el proyecto sin Docker?**
Técnicamente sí, pero tendrías que instalar PHP 8.3, MySQL 8.0, Redis, y todas las extensiones manualmente. No es el camino recomendado para este curso.

**¿Docker Desktop es gratis?**
Para uso educativo y personal, sí. Para uso comercial en empresas con más de 250 empleados o más de $10M de ingresos, requiere suscripción.
