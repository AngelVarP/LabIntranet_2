# LabIntranet

**LabIntranet** es una aplicación web en desarrollo destinada a la gestión de usuarios dentro de un sistema de administración. Este proyecto incluye una interfaz de usuario para gestionar la base de datos de usuarios, permitiendo agregar, editar, eliminar y visualizar usuarios con diferentes roles (administrador, profesor, delegado, alumno).

El sistema se construye utilizando **PHP** para el backend, **MySQL** para la gestión de la base de datos y **JavaScript** para la interacción dinámica del lado del cliente. Además, cuenta con una estructura modular que facilita la integración de nuevos componentes y funcionalidades.

## Características Principales

- **Gestión de Usuarios**: Agregar, editar, eliminar y listar usuarios.
- **Roles de Usuarios**: Administra diferentes roles como administrador, profesor, delegado y alumno.
- **Interfaz de Usuario**: Diseño de una interfaz sencilla y eficiente para facilitar la administración.
- **Sistema de Autenticación**: Funcionalidades de inicio de sesión para gestionar el acceso a la plataforma.

## Tecnologías Usadas

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Base de Datos**: MySQL
- **Servidor**: XAMPP (Apache, MySQL)

## Estado del Proyecto

Este proyecto aún está en desarrollo. Actualmente, se están implementando las funcionalidades básicas de gestión de usuarios y optimizando la integración entre el frontend y el backend. Se prevé incluir más características como la autenticación de usuarios, la exportación de reportes y mejoras en la seguridad.

## Instalación

1. **Clona el repositorio**:
   ```bash
   git clone https://github.com/usuario/LabIntranet.git
2. **Configuración del entorno**:
- Instala **XAMPP** (o usa cualquier servidor que soporte PHP y MySQL).
- Coloca los archivos del proyecto en el directorio `htdocs` de XAMPP.
- Abre XAMPP y asegúrate de que los servicios **Apache** y **MySQL** estén corriendo.

3. **Importar la base de datos**:
- Accede a **phpMyAdmin**.
- Ejecuta el archivo `base_de_datos/init_db.sql` para crear la base de datos `labintranet` y la tabla `usuarios`.

4. **Acceder al proyecto**:
- Abre tu navegador y accede a:  
  [http://localhost/LabIntranet](http://localhost/LabIntranet)

## Estructura del Proyecto

- **backend/**  
  Contiene los archivos PHP responsables de la lógica del servidor.
  - **admin/**  
    Gestión de usuarios y administración general.

- **frontend/**  
  Contiene los archivos HTML, CSS y JavaScript de la interfaz.
  - **public/**  
    Archivos accesibles públicamente, incluyendo el panel de administración y la interfaz de usuarios.
  - **assets/**  
    Archivos CSS, imágenes y scripts.

- **base_de_datos/**  
  Contiene el archivo `init_db.sql` para crear la base de datos y la tabla de usuarios.
## Desarrolladores

- **Adriano Rafael Olivos Gallardo**
- **Dino Paul Roel Suazo Zanabria**
- **Luis Angel Vargas Ponce**
- **Farid Paolo Zamudio Sanchez**


