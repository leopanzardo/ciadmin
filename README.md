# CIAdmin

**CIAdmin** es una herramienta para generar automáticamente una aplicación administrativa basada en una base de datos usando [CodeIgniter 4](https://codeigniter.com/).

Su objetivo es simplificar la creación de sistemas CRUD monolíticos con mínimo esfuerzo, manteniendo el código generado limpio y fácil de extender. Es un excelente punto de partida para cualquier aplicación que requiera administrar bases de datos.
<br/><br/>

---
<br/>

## 🚀 Instalación

1. Crea una nueva carpeta para tu proyecto y clona este repositorio:

```
git clone https://github.com/leopanzardo/ciadmin.git
```

2. Instala las dependencias con Composer:

```
composer install
```

3. Configura la conexión a la base de datos como en cualquier otra aplicación que haga uso del framework CodeIgniter, ya sea en el archivo .env o en app/Config/Database.php según prefieras.
<br/><br/>

---
<br/>

## ⚙️ Uso

Para generar toda la estructura administrativa basada en la base de datos basta con correr el siguiente comando en una terminal:

```
php spark make:ciadmin
```

Esto generará automáticamente:

- Modelos
- Controladores
- Vistas básicas (index, create, edit)
- Rutas
- Un Dashboard de inicio con enlaces a cada módulo
<br/><br/>

---
<br/>

## 📋 Requisitos

Debería ser suficiente cumplir con los requisitos básicos de CodeIgniter 4, pero se recomienda:

- PHP 8.1 o superior
- Composer
- MySQL o compatible
- Servidor Web (Apache, Nginx, etc.)
<br/><br/>

---
<br/>

## 📚 Créditos

Creado con ❤️ por Leonardo Panzardo.

