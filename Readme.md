# ✈️ Vacation Infodec — Prueba Técnica Fullstack

Solución integral desarrollada para la **Prueba Técnica Fullstack — Infodec**. La aplicación permite a los usuarios planificar sus viajes consultando en tiempo real el clima y calculando la conversión de su presupuesto en Pesos Colombianos (COP) a la divisa local del destino, manteniendo un historial personalizado de búsquedas y soporte multi-idioma (Español / Alemán).

---

## 📌 Tabla de Contenidos

* [Descripción del Proyecto]
* [Stack Tecnológico]
* [Arquitectura y Estructura del Repositorio]
* [Seguridad y Manejo de Tokens (JWT/JWE)]
* [Almacenamiento del Token en el Frontend]
* [APIs Externas y Estrategia de Respaldo]
* [Instalación y Configuración Paso a Paso]
* [Ejecución de Pruebas Unitarias]
* [Colección de Postman]
* [Evidencia en Video]

---

## 🧳 Descripción del Proyecto

La plataforma resuelve las necesidades clave de un viajero:

1. **Consulta de Clima:** Obtención de la temperatura en grados centígrados (°C) para el destino seleccionado.


2. **Conversión de Divisas:** Cálculo equivalente de un presupuesto ingresado en Pesos Colombianos (COP) a la moneda del país de destino (GBP, JPY, INR, DKK).


3. **Historial Único por Usuario:** Registro y visualización de las últimas 5 consultas realizadas exclusivamente por el usuario autenticado.


4. **Internacionalización (i18n):** Interfaz y mensajes de error adaptables dinámicamente entre **Español** y **Alemán**.


5. **Autenticación Segura:** Sistema de registro, inicio de sesión y gestión de sesiones mediante tokens de acceso y refresco.



---

## 🛠️ Stack Tecnológico

* **Backend:** Laravel 11 (PHP 8.2+)


* **Base de Datos:** PostgreSQL


* **Frontend:** Angular 16 + Bootstrap 5 (Diseño 100% responsivo para Desktop, Tablet y Mobile)


* **Pruebas:** PHPUnit


* **Internacionalización:** `@ngx-translate/core` en Angular / Manejador dinámico en Laravel



---

## 📐 Arquitectura y Estructura del Repositorio

El proyecto sigue una estructura limpia dividida en capas independientes:

```text
vacation-infodec/
├── backend/                # API REST en Laravel 11
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/ # Controladores REST
│   │   │   ├── Middleware/  # Middleware de autenticación (AuthTokenMiddleware)
│   │   │   └── Requests/    # Validaciones de entrada
│   │   ├── Services/        # Lógica de negocio (ClimaService, MonedaService)
│   │   └── Exceptions/      # Manejador global de excepciones estandarizado
│   ├── database/            # Migraciones y Seeders (Paises, Ciudades, Monedas)
│   └── tests/               # Pruebas unitarias con PHPUnit
├── database/                # Scripts SQL y Diagrama ER de PostgreSQL
└── frontend/                # Cliente Web en Angular 16
    ├── src/
    │   ├── app/
    │   │   ├── components/  # Vistas (Auth, Pasos 1-3, Historial)
    │   │   ├── guards/      # AuthGuard
    │   │   ├── interceptors/# HttpInterceptor (Inyección de Bearer Token y Auto-Refresh)
    │   │   └── services/    # Servicios de API e Idioma
    │   └── assets/i18n/     # Archivos de traducción (es.json, de.json)

```

---

## 🔐 Seguridad y Manejo de Tokens (JWT/JWE)

Por requerimiento estricto de seguridad, **no se utilizaron kits de autenticación automática** (Breeze, Jetstream o Fortify). Todo el flujo de registro, hashing y cifrado fue desarrollado a medida.

### Flujo de Autenticación y Renovación

```text
  [ Cliente Angular ]              [ Backend Laravel ]              [ Base de Datos / APIs ]
           │                                │                                  │
           ├───── POST /api/auth/login ────>│                                  │
           │                                ├─ Verfica Hash (bcrypt) ─────────>│
           │<── Access Token + Refresh ─────┤                                  │
           │                                │                                  │
   (Petición Protegida)                     │                                  │
           ├─ GET /api/paises (Bearer) ────>│                                  │
           │                                ├─ Middleware: Valida/Descifra ───>│
           │<──────── Data JSON ────────────┤                                  │
           │                                │                                  │
    (Token Expirado 401)                    │                                  │
           │<─── AUTH_TOKEN_EXPIRED ────────┤                                  │
           │                                │                                  │
    (Auto-Refresh Interceptor)              │                                  │
           ├─ POST /api/auth/refresh ──────>│                                  │
           │<── Nuevos Tokens ──────────────┼─ Invalida Token Anterior ────────>│
           │                                │                                  │
    (Cierre de Sesión)                      │                                  │
           ├─ POST /api/auth/logout ───────>├─ Agrega JTI a revocation_list ──>│

```

### Mecanismo de Cifrado Dual (Firma + Cifrado)

Para garantizar la confidencialidad e integridad del payload:

1. **Firma (HS256):** Previene la alteración mediante una clave derivada del secreto principal.


2. **Cifrado (AES-256-GCM):** Evita la lectura directa del token en herramientas como `jwt.io`.


3. **Derivación de Claves (HKDF):**
* `claveFirma` = `hash_hkdf("sha256", $secreto, 32, "jwt-firma")`

* `claveCifrado` = `hash_hkdf("sha256", $secreto, 32, "jwt-cifrado")`




---

## 💾 Almacenamiento del Token en el Frontend

* **Access Token:** Se almacena temporalmente en la memoria de la aplicación (o `sessionStorage`) para adjuntarlo mediante el `HttpInterceptor` en la cabecera `Authorization: Bearer <token>`.


* **Refresh Token:** Se almacena de forma segura para permitir la renovación automática al recibir una respuesta HTTP `401 AUTH_TOKEN_EXPIRED`.


* **Razón técnica:** Mantener los tokens fuera del almacenamiento persistente no cifrado previene vulnerabilidades de Cross-Site Scripting (XSS). En caso de caducidad, el interceptor refresca la sesión de manera transparente para el usuario sin interrumpir el flujo.



---

## 🌐 APIs Externas y Estrategia de Respaldo

La aplicación integra servicios de terceros utilizando claves protegidas mediante variables de entorno (`.env`):

* **Servicio de Clima:** OpenWeatherMap API.


* **Servicio de Divisas:** ExchangeRate-API.



### Resiliencia y Fallback Strategy:

* **Falla en la API de Moneda:** Se utiliza la última tasa de cambio registrada en la tabla `tasas_cambio` de la base de datos local. Si no hay historial previo, se notifica el mensaje *"Conversión no disponible"*.


* **Falla en la API de Clima:** La aplicación responde con la conversión monetaria y añade la notificación *"Clima no disponible"* sin interrumpir la experiencia del usuario.


* **Tiempos de Espera (Timeouts):** Respuestas estandarizadas con códigos HTTP `502 EXTERNAL_API_ERROR` o `504 EXTERNAL_API_TIMEOUT`.



---

## 🚀 Instalación y Configuración Paso a Paso

### Prerrequisitos

* PHP >= 8.2


* Composer
* Node.js >= 18.x
* Angular CLI (`npm install -g @angular/cli`)
* PostgreSQL activo



### 1. Clonar el Repositorio

```bash
git clone https://github.com/Luiis24/vacation-infodec.git
cd vacation-infodec

```

### 2. Configuración del Backend (Laravel)

```bash
cd backend
composer install

# Copiar archivo de variables de entorno
cp .env.example .env

# Generar clave de aplicación Laravel
php artisan key:generate

# Generar Secreto Personalizado para Firma y Cifrado JWT
php -r "echo base64_encode(random_bytes(32));"

```

Copiar el valor impreso en la variable `APP_TOKEN_SECRET` de tu archivo `.env`. Configurar las credenciales de PostgreSQL y las llaves de las APIs externas:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vacation_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password

APP_TOKEN_SECRET=tu_secreto_generado_en_base64
WEATHER_API_KEY=tu_api_key_clima
EXCHANGE_API_KEY=tu_api_key_moneda

```

Ejecutar migraciones y seeders para poblar datos iniciales (Países, Ciudades, Monedas y Usuario de Prueba):

```bash
php artisan migrate --seed
php artisan serve

```

### 3. Configuración del Frontend (Angular)

```bash
cd ../frontend
npm install
ng serve

```

Accede desde tu navegador a `http://localhost:4200`.

---

## 🧪 Ejecución de Pruebas Unitarias

El backend incluye una suite de **10 pruebas unitarias con PHPUnit** que evalúan el cumplimiento estricto de seguridad, validaciones de token, simulación de fallas de API (mocks) y control de errores:

```bash
cd backend
php artisan test

```

---

## 📮 Colección de Postman

En la raíz de la carpeta `database/` o `docs/` encontrarás el archivo JSON con la colección oficial de Postman.

**Características de la colección:**

* **Variables globales de entorno:** `base_url`, `access_token`, `refresh_token`.


* **Tests Scripts automatizados:** Al ejecutar la petición de `POST /api/auth/login`, los tokens se guardan automáticamente en las variables del entorno.


* **Organización:** Carpetas organizadas por *Autenticación*, *Países y Ciudades*, *Consultas*, *APIs Externas* y *Casos de Error (401, 409, 422)*.



---

## 🎥 Evidencia en Video

De acuerdo con las instrucciones de entrega:

* 📹 **[Video 1: Demostración de la Aplicación en Funcionamiento](https://www.google.com](https://drive.google.com/file/d/1GPNQZWs4TMu3i_hsrCxztWNuSgPEzBwZ/view?usp=sharing))**

* 📹 **[Video 2: Explicación Técnica de Código, Arquitectura y Tokens](https://drive.google.com/file/d/1qYPMknYsDzGU8rN6kTVVrdiHnbt_ShHB/view?usp=sharing)**


---

*Desarrollado con pasión y buenas prácticas por Luis Morales para Infodec.*
