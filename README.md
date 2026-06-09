# Pruebas MyFit

Guía rápida de comandos del proyecto.

---

## Desarrollo

| Comando | Descripción |
|---------|-------------|
| `composer run dev` | Inicia el entorno de desarrollo (servidor PHP, Vite y cola de trabajos en paralelo). |
| `composer dump-autoload` | Regenera el autoload de clases. Útil si hay errores al migrar por nombres de clases no encontrados. |

Usa siempre la misma URL que `APP_URL` en el navegador (por ejemplo `http://localhost:8000`, no mezclar con `127.0.0.1`). `composer run dev` arranca el servidor en `localhost` para que OAuth y la sesión coincidan.

---

## Strava

### 1. Crear aplicación en Strava

1. Entra en [developers.strava.com](https://developers.strava.com) y crea una aplicación.
2. En **Authorization Callback Domain** pon `localhost` si desarrollas en `http://localhost:8000`.
3. Copia el **Client ID** y el **Client Secret** (botón *Show* en la página de API). No uses el *Access Token* ni el *Refresh Token* de esa misma pantalla: van en `.env` solo `STRAVA_CLIENT_ID` y `STRAVA_CLIENT_SECRET`.

### 2. Variables de entorno

En `.env` (proyecto **JzFitness**):

```env
APP_URL=http://localhost:8000
STRAVA_CLIENT_ID=tu_client_id
STRAVA_CLIENT_SECRET=tu_client_secret
STRAVA_REDIRECT_URI="${APP_URL}/strava/callback"
```

Luego:

```bash
php artisan config:clear
```

### 3. Conectar y sincronizar

1. Arranca la app con `composer run dev` (incluye cola `queue:listen`, necesaria para sync programada).
2. Inicia sesión y abre **Strava** en el menú.
3. Pulsa **Conectar Strava** y autoriza el acceso.
4. Tras conectar, las actividades se importan al instante; también puedes usar **Sincronizar** manualmente.

El dashboard solo cuenta actividades de Strava de la **semana actual**. Actividades más antiguas aparecen en `/strava` pero pueden no sumar en el resumen semanal.

---

## Base de datos

### Migraciones y modelos

| Comando | Descripción |
|---------|-------------|
| `php artisan migrate` | Ejecuta las migraciones pendientes y crea o actualiza las tablas en la base de datos. |
| `php artisan make:migration create_<tabla>_table` | Genera un archivo de migración vacío para definir la estructura de una nueva tabla. |
| `php artisan make:model Support -m` | Crea el modelo `Support` junto con su migración (`-m`). |
| `php artisan make:model Costumer -m` | Crea el modelo `Costumer` junto con su migración. |
| `php artisan make:model Ticket -m` | Crea el modelo `Ticket` junto con su migración. |

### Factories y seeders

Las **factories** generan datos de prueba ficticios. Los **seeders** los insertan en la base de datos.

| Comando | Descripción |
|---------|-------------|
| `php artisan make:factory CostumerFactory --model=Costumer` | Crea la factory para generar datos de prueba del modelo `Costumer`. |
| `php artisan make:factory SupportFactory --model=Support` | Crea la factory para generar datos de prueba del modelo `Support`. |
| `php artisan make:factory TicketFactory --model=Ticket` | Crea la factory para generar datos de prueba del modelo `Ticket`. |
| `php artisan db:seed` | Ejecuta los seeders y rellena la base de datos con datos de prueba. |
| `php artisan migrate:refresh` | Revierte todas las migraciones y las vuelve a ejecutar. **Borra todos los datos** de las tablas. |

---

## Controladores

El archivo `app/Http/Controllers/Controller.php` es la clase base de los controladores.

Actúa como **intermediario entre las rutas y las vistas o modelos**: recibe la petición HTTP, aplica la lógica de negocio y devuelve la respuesta.

### Crear controlador de un modelo

| Comando | Descripción |
|---------|-------------|
| `php artisan make:controller TestController` | Crea un controlador vacío, sin métodos predefinidos. |
| `php artisan make:controller CostumerController --resource` | Crea el controlador con los métodos CRUD vacíos: `index`, `create`, `store`, `show`, `edit`, `update` y `destroy`. |
| `php artisan make:controller SupportController --resource` | Igual que el anterior, para el modelo `Support`. |
| `php artisan make:controller TicketController --resource` | Igual que el anterior, para el modelo `Ticket`. |

Tras generarlos con `--resource`, hay que **rellenar cada método** con la lógica correspondiente (consultas al modelo, validación, respuesta Inertia, etc.).

---

## Rutas

Las rutas del proyecto se definen en `routes/web.php`.

Con `Route::resource()` Laravel registra automáticamente las 7 rutas REST de un recurso y las enlaza con los métodos del controlador:

| Método HTTP | URI | Acción del controlador |
|-------------|-----|------------------------|
| GET | `/costumers` | `index` |
| GET | `/costumers/create` | `create` |
| POST | `/costumers` | `store` |
| GET | `/costumers/{costumer}` | `show` |
| GET | `/costumers/{costumer}/edit` | `edit` |
| PUT/PATCH | `/costumers/{costumer}` | `update` |
| DELETE | `/costumers/{costumer}` | `destroy` |

En el proyecto están registradas así (requieren autenticación):

```php
Route::resource('costumers', CostumerController::class);
Route::resource('supports', SupportController::class);
Route::resource('tickets', TicketController::class);
```

| Comando | Descripción |
|---------|-------------|
| `php artisan route:list` | Lista todas las rutas registradas en la aplicación, incluidas las de `web.php`. Útil para comprobar que los controladores están bien enlazados. |




REACT 
<!-- Explica brevemente para que es cada uno de los directorios -->
Directorio: C:\xampp\htdocs\pruebasmyfit\resources\js


Mode                 LastWriteTime         Length Name
----                 -------------         ------ ----
d-----        30/05/2026     22:34                actions
d-----        27/05/2026     15:38                components
d-----        27/05/2026     15:38                hooks
d-----        27/05/2026     15:38                layouts y subdoriectorios
d-----        27/05/2026     15:38                lib
d-----        31/05/2026     13:50                pages
d-----        30/05/2026     22:34                routes
d-----        27/05/2026     15:38                types
d-----        30/05/2026     22:34                wayfinder
------        27/05/2026     15:38           1205 app.tsx

