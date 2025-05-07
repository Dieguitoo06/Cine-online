# 🎬 Cine Online

Sistema web para la reserva de entradas de cine. Los usuarios pueden registrarse, ver la cartelera, seleccionar funciones, asientos y realizar reservas.

## 🚀 Funcionalidades

- Registro e inicio de sesión de usuarios.
- Visualización de películas en cartelera.
- Selección de funciones por fecha, hora y sala.
- Reserva de asientos disponibles.
- Confirmación de compra con generación de código único.
- Panel de usuario con historial de reservas.
- Administración de películas, funciones y salas (modo admin).

## 🛠️ Tecnologías Usadas

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP (con MySQLi)
- **Base de datos**: MySQL
- **Servidor local**: XAMPP

## 📦 Estructura del Proyecto

--------------------------------

## ⚙️ Instalación

1. Cloná el repositorio o descargá el ZIP.
2. Importá el archivo `db_setup.sql` en **phpMyAdmin**.
3. Configurá la conexión a la base en `includes/conexion.php`.
4. Iniciá XAMPP y abrí el proyecto desde `http://localhost/cine_online`.

## 👤 Usuario Admin

- Email: `admin@cineonline.com`
- Contraseña: `admin123`

## 🧪 Testing

(Podés agregar tests unitarios si lo integrás con PHPUnit o similar)

## 📌 Notas

- Recordá asignar precios a las funciones para que se calcule el total correctamente.
- Las reservas se pueden cancelar si aún no comenzó la función.

---

¡Listo para usar en una presentación o subir a GitHub!

