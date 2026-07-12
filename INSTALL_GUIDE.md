# Guía de Instalación - JSPOS Sales

Esta guía te permite instalar el sistema desde cero, como si fueras un nuevo cliente.

## Requisitos Previos

| Requisito | Versión Mínima |
|-----------|----------------|
| PHP | 8.1+ |
| MySQL | 5.7+ |
| Composer | 2.0+ |
| Node.js | 18+ (opcional, solo si necesitas compilar assets) |

---

## Paso 1: Clonar el Repositorio

```bash
# Crear carpeta para el proyecto (si no existe)
mkdir C:\laragon\www\jspos-nuevo
cd C:\laragon\www\jspos-nuevo

# Clonar desde GitHub
git clone https://github.com/jhosagid7/jspos-sales.git .

# O si quieres una rama específica:
# git clone -b develop https://github.com/jhosagid7/jspos-sales.git .
```

**En Laragon**, simplemente:
1. Crear nuevo sitio en Laragon → "jspos-nuevo"
2. git clone en la carpeta

---

## Paso 2: Instalar Dependencias

```bash
cd C:\laragon\www\jspos-nuevo

# Instalar dependencias de PHP
composer install

# O si quieres más rápido:
# composer install --no-dev --optimize-autoloader
```

---

## Paso 3: Configurar Archivo .env

```bash
# Copiar el archivo de ejemplo
copy .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Limpiar cache
php artisan optimize:clear
```

**Editar .env** con tus datos:

```env
APP_NAME=JSPOS Ventas
APP_ENV=local
APP_KEY=base64:TU_CLAVE_AQUI
APP_DEBUG=true
APP_URL=http://jspos-nuevo.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jspos_nuevo        # Cambia esto
DB_USERNAME=root              # Tu usuario
DB_PASSWORD=tu_password       # Tu contraseña
```

---

## Paso 4: Verificar y Limpiar Instalación Previa

```bash
# Verificar si hay archivo de instalación previa
dir storage\installed

# Si existe, eliminarlo para poder reinstallar
del storage\installed

# También eliminar cache si existe
rmdir /s /q bootstrap\cache
mkdir bootstrap\cache
```

---

## Paso 5: Ejecutar el Wizard de Instalación

### Opción A: Desde el Navegador

1. Abrir: `http://jspos-nuevo.test/install`

2. **Paso 1 - Requisitos**: Verifica que todo esté en verde

3. **Paso 2 - Database**:
   - DB Host: `127.0.0.1`
   - DB Port: `3306`
   - DB Name: `jspos_nuevo` (se creará automáticamente)
   - DB User: `root`
   - DB Password: `tu_password`
   - App URL: `http://jspos-nuevo.test`

4. **Paso 3 - Migraciones**: Click en "Ejecutar Migraciones"

5. **Paso 4 - Licencia**:
   - Ingresa la **license_key** que te proporcionaron
   - Si no tienes license_key, contacta al proveedor

6. **Paso 5 - Admin**:
   - Name: Tu nombre
   - Email: Tu correo
   - Password: Tu contraseña

7. **Listo!** Click en "Ir al Sistema"

---

### Opción B: Por Consola (Instalación Automática)

Si prefieres hacerlo todo por línea de comandos:

```bash
# 1. Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# 2. Crear usuario administrador manualmente
php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => 'Administrador',
    'email' => 'admin@tucorreo.com',
    'password' => bcrypt('tu_password'),
    'profile' => 'Admin',
    'status' => 'Active'
]);
\$user->assignRole('Admin');
echo 'Usuario creado: ' . \$user->email;
"

# 3. Marcar como instalado
echo "JSPOS INSTALLED ON $(date)" > storage\installed
```

---

## Paso 6: Verificar Instalación

```bash
# Verificar estado de migraciones
php artisan migrate:status

# Limpiar cache
php artisan optimize:clear

# Probar el sistema
php artisan serve
```

---

## Solución de Problemas Comunes

### Error: "No connection could be made"
- Verificar que MySQL esté corriendo
- Verificar credenciales en .env

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "formatMoney() undefined"
```bash
composer dump-autoload
php artisan optimize:clear
```

### No aparece el menú de instalación
- Verificar que `storage/installed` no exista
- Verificar que `APP_INSTALLED=false` en .env

---

## Notas Importantes

1. **Licencia**: Cada instalación requiere una license_key única
2. **Base de datos**: El instalador crea la BD automáticamente
3. **Producción**: Antes de pasar a producción, cambiar:
   - `APP_ENV=production`
   - `APP_DEBUG=false`

---

## Estructura de Archivos Importantess

| Archivo/Carpeta | Propósito |
|-----------------|-----------|
| `storage/installed` | Marca que el sistema está instalado |
| `.env` | Configuración de la aplicación |
| `bootstrap/cache/` | Cache de Laravel |
| `database/migrations/` | Estructura de la base de datos |
| `database/seeders/` | Datos iniciales (roles, permisos) |

---

## Scripts de Referencia Rápida

### Reinstalar desde cero
```bash
# En la carpeta del proyecto
del storage\installed
php artisan migrate:fresh --seed
php artisan optimize:clear
```

### Verificar estado
```bash
php artisan about
php artisan migrate:status
php artisan cache:clear
```

---

**¿Necesitas más ayuda?**

Contacta al soporte técnico con:
- Error específico (si lo hay)
- Captura de pantalla del problema
- Versión de PHP: `php -v`
- Versión del sistema: buscar en `version.txt`
