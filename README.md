# CIAdmin

![CIAdmin](https://img.shields.io/badge/Administrador%20de%20Bases%20de%20Datos-CodeIgniter%204-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white&labelColor=4223ef)

## Crea tu panel de administración en segundos con CodeIgniter

---

<br/>

## 📋 Descripción

**CIAdmin** es una herramienta para generar automáticamente una aplicación administrativa basada en una base de datos utilizando [CodeIgniter 4](https://codeigniter.com/).

Su objetivo es simplificar la creación de sistemas CRUD monolíticos con mínimo esfuerzo, manteniendo el código generado limpio y fácil de extender.  
Es un excelente punto de partida para cualquier aplicación que requiera administrar bases de datos.
<br/><br/>

---

<br/>

## 🚀 Instalación

### 🖥️ Crear el proyecto

Abrí una terminal y ejecutá:

```
mkdir MiProyecto
cd MiProyecto
```
<br/>

---

<br/>

### 🧩 Clonar el repositorio

Ahora tenés dos opciones:

1. Clonar en una subcarpeta llamada ciadmin (opción por defecto)

```
git clone https://github.com/leopanzardo/ciadmin.git
```

Esto creará una carpeta ciadmin dentro de MiProyecto.

2. Clonar directamente en la carpeta actual

```
git clone https://github.com/leopanzardo/ciadmin.git .
```

(Importante: el . al final indica que se clone directamente en la carpeta actual.)
<br/><br/>

---

<br/>

### ⚙️ Instalar dependencias

Una vez clonado el proyecto y ubicado en la carpeta en que lo clonaste, instalá las dependencias de Composer:

```
composer install
```
<br/>

---

<br/>

### 🛠️ Configurar la conexión a la base de datos

Configurá tu conexión como en cualquier proyecto de CodeIgniter 4, ya sea:

- Editando el archivo .env, o
- Modificando app/Config/Database.php.
<br/><br/>

---

<br/>

### 🛠️ Configuración del Servidor

Asegurate de configurar los siguientes parámetros en app/Config/App.php:

```
public string $baseURL = 'http://tu-dominio.local/'; // O localhost si usás php spark serve
public array $allowedHostnames = ['tu-dominio.local']; // Igual que baseURL, sin la barra final
public string $indexPage = ''; // Dejar vacío para eliminar index.php de las URLs
```
<br/><br/>

---

<br/>

### 🛠️ .htaccess

Ya se incluye un archivo .htaccess funcional en la carpeta /public.
No hace falta modificarlo salvo que tengas configuraciones especiales.

Importante: Asegurate de que el módulo mod_rewrite esté habilitado en Apache.
<br/><br/>

---

<br/>

### 🛠️ Virtual Host (si usás Apache)

Si preferís usar un servidor virtual en Apache (por ejemplo con WampServer o XAMPP), creá una entrada en tu httpd-vhosts.conf como esta:

```
<VirtualHost *:80>
    ServerName tu-dominio.local
    DocumentRoot "C:/Proyectos/MiProyecto/public"
    <Directory "C:/Proyectos/MiProyecto/public/">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Luego recordá agregar el dominio en tu archivo hosts, por ejemplo:

```
127.0.0.1   tu-dominio.local
```
<br/><br/>

---

<br/>

### ⚙️ Uso

Para generar toda la estructura administrativa basada en tu base de datos ejecutá:

```
php spark make:ciadmin
```

Esto generará automáticamente:

- Modelos
- Controladores
- Vistas básicas (index, create, edit)
- Rutas
- Un Dashboard de inicio con enlaces a cada módulo generado
<br/><br/>

---

<br/>

### Opciones disponibles

- --force (-f) ➔ Fuerza la sobreescritura de archivos existentes.

- --only=model, --only=controller, --only=view, --only=routes, --only=dashboard ➔ Permite generar solo el tipo de archivo que necesites.
<br/><br/>

---

<br/>

### Ejemplos

Forzar regenerar todo:

```
php spark make:ciadmin --force
```

Regenerar únicamente las vistas:

```
php spark make:ciadmin --only=view --force
```
<br/><br/>

---

<br/>

### 🧪 Probar la aplicación

Podés probar la aplicación de dos maneras:

1. Usando Virtual Hosts (Apache, Nginx, IIS, etc.)

  - Accedé directamente al dominio configurado (http://tu-dominio.local/).

2. Usando el servidor embebido de CodeIgniter

Ejecutá:

```
php spark serve
```

Eso lanzará el servidor de desarrollo incluído en CodeIgniter y podrás acceder a tu aplicación accediendo a http://localhost:8080/ en tu navegador.
<br/><br/>

---

<br/>

### 📋 Requisitos

Se recomienda cumplir con los requisitos mínimos de CodeIgniter 4:

- PHP 8.1 o superior
- Composer
- MySQL o compatible
- Servidor Web (Apache, Nginx, etc.)

Para mayor información sobre los requerimientos te sugiero que consultes la [documentación de CodeIniter](https://www.codeigniter.com/user_guide/intro/requirements.html) al respecto.
<br/><br/>

---

<br/>

## 👥 Autores

- **Leonardo Panzardo**
  - [GitHub](https://github.com/leopanzardo)
  - [LinkedIn](https://www.linkedin.com/in/leopanzardo/)
<br/><br/>

---

<br/>

### 📚 Créditos

Creado con ❤️ por Leonardo Panzardo.

