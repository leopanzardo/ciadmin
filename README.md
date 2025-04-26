# CIAdmin

![CIAdmin](https://img.shields.io/badge/Administrador%20de%20Bases%20de%20Datos-CodeIgniter%204-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white&labelColor=4223ef)

**Crea tu panel de administración en segundos con CodeIgniter.**
<br/><br/>

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

Para probar la aplicación generada tienes dos opciones:

1. Si utilizas Wampserver, XAMPP o tienes algún servidor local instalado como IIS, Apache, Nginx o algún otro, configura un servidor virtual que apunte a la carpeta public que se encuentra en la carpeta donde clonaste el repositorio. Una vez configurado y reiniciado el servidor en caso de ser necesario, puedes acceder a la aplicación navegando a la url que configuraste.

2. En una terminal ubicado en la carpeta del repositorio ejecuta el siguiente comando:

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

### 📚 Créditos

Creado con ❤️ por Leonardo Panzardo.

