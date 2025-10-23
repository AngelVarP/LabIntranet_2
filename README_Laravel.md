# 🧪 LabIntranet 2.0 — Sistema de Gestión de Laboratorio (Laravel + MySQL)

Proyecto desarrollado para el **Laboratorio de Química General - FIEE UNI**, reconstruido con **Laravel 12 + PHP 8.2 + MySQL** y un **frontend HTML/JS/CSS** intuitivo y moderno.

---

## ⚙️ Requerimientos
- **XAMPP** (PHP 8.2, Apache, MySQL)
- **Composer**: https://getcomposer.org/download/
- **Git**: https://git-scm.com/downloads
- **Visual Studio Code** (o editor similar)

---

## 💾 Instalar Composer (una vez)
Composer es obligatorio para que Laravel funcione.

1. Descarga e instala desde: https://getcomposer.org/download/
2. Cuando te pregunte por PHP, selecciona:
   ```
   C:\xampp\php\php.exe
   ```
3. Verifica en terminal:
   ```bash
   composer -V
   ```
   Debe mostrarse algo como `Composer version 2.x.x`.

---

## 🚀 Puesta en marcha del proyecto
Sigue estos pasos después de clonar el repositorio.

### 1) Clonar
```bash
git clone https://github.com/<TU_USUARIO>/<TU_REPO>.git
cd LabIntranet_2/backend_laravel
```

### 2) Instalar dependencias
```bash
composer install
```

### 3) Crear `.env` y configurar BD
```bash
cp .env.example .env
```

Edita `.env` y coloca:
```env
APP_NAME=LabIntranet
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labintranet
DB_USERNAME=root
DB_PASSWORD=
```

### 4) Generar clave de la app
```bash
php artisan key:generate
```

### 5) Iniciar servidor
```bash
php artisan serve
```
Abre: http://127.0.0.1:8000

---

## 🧩 Estructura del proyecto
```
LabIntranet_2/
├─ backend_laravel/        # Backend Laravel
│  ├─ app/                 # Modelos, controladores, lógica
│  ├─ routes/              # Rutas web y API
│  ├─ public/              # Carpeta pública
│  ├─ .env                 # Config local (no subir)
│  ├─ artisan
│  └─ composer.json
├─ frontend/               # Interfaz HTML/CSS/JS
│  ├─ login.html
│  ├─ dashboard.html
│  ├─ assets/
│  └─ scripts/
├─ README.md
└─ .gitignore
```

---

## 🧠 Flujo general
1. **Frontend (HTML/JS)**: envía peticiones con `fetch()`/`axios` a la API.
2. **Backend (Laravel)**: valida, procesa y responde (JSON/vistas).
3. **Base de datos (MySQL)**: persistencia vía **Eloquent ORM**.

---

## 🧰 Comandos útiles
| Acción                         | Comando                              |
|-------------------------------|--------------------------------------|
| Limpiar caché de configuración | `php artisan config:clear`           |
| Regenerar caché                | `php artisan config:cache`           |
| Crear modelo + controlador     | `php artisan make:model Nombre -c`   |
| Consola interactiva            | `php artisan tinker`                 |

---

## 🔒 Importante (.gitignore)
No subir nunca:
- `.env`
- `/vendor/`
- `/node_modules/`
- `/storage/logs/`
- `/public/storage/`

(Ya están ignorados en `.gitignore`.)

---

## 🧩 Integración con Vue.js (opcional)
Puedes añadir Vue por CDN para interactividad:
```html
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<div id="app">
  <h3>{{ mensaje }}</h3>
  <button @click="mensaje = '¡Conectado con Laravel!'">Probar</button>
</div>
<script>
const { createApp } = Vue;
createApp({ data(){ return { mensaje: 'Hola desde Vue.js' } } }).mount('#app');
</script>
```