# 🚀 Guía de Despliegue en Render (Despliegue de los 3 Servicios en 1 Clic con Blueprint)

Hemos configurado un archivo `render.yaml` (Blueprint) en la raíz de tu repositorio para que Render cree y configure automáticamente los **3 servicios web** de una sola vez.

---

## 📌 Los 3 Servicios que se crearán automáticamente

| Servicio | Tipo | Directorio | Puerto Local (Intacto) | Base de Datos por Defecto |
| :--- | :--- | :--- | :--- | :--- |
| **`omnidex-app`** | Web Service (Nginx) | `pagina Omnidex/omnidex-app` | `9090` | N/A (Frontend estático) |
| **`futbol-estadistica`** | Web Service (PHP 8.4 + Nginx) | `pagina futbol estadistica` | `8000` | SQLite lista para demo |
| **`onigiri-pos`** | Web Service (PHP 8.2 + Nginx) | `pagina Gastronomia/onigiri-pos` | `8080` | SQLite lista para demo |

---

## ⚡ Cómo Desplegar los 3 Servicios en Render

1. Entra a tu cuenta en [dashboard.render.com](https://dashboard.render.com).
2. Haz clic en el botón arriba a la derecha **New +** y selecciona **Blueprint**.
3. Selecciona tu repositorio de GitHub: `Portafolio-Daikel`.
4. Render detectará automáticamente el archivo `render.yaml` y te mostrará la lista con los 3 servicios web listos.
5. Asigna un nombre a tu Blueprint (o deja el sugerido) y haz clic en **Apply**.
6. ¡Listo! Render comenzará a compilar e iniciar los 3 servicios en paralelo.

---

## 🌐 Tus URLs Generadas
Una vez finalizada la compilación, cada proyecto tendrá su propia URL pública con HTTPS gratis:
* `https://omnidex-app-XXXX.onrender.com`
* `https://futbol-estadistica-XXXX.onrender.com`
* `https://onigiri-pos-XXXX.onrender.com`

---

## 🗄️ (Opcional) Conectar una Base de Datos MySQL Externa
Por defecto, los proyectos de Laravel se iniciarán con **SQLite** para que funcionen inmediatamente sin configuración extra. Si más adelante deseas conectar una base de datos MySQL en la nube (como [Aiven.io](https://aiven.io/free-mysql) o [TiDB Cloud](https://tidbcloud.com/)):
1. Entra al servicio en Render > pestaña **Environment**.
2. Cambia `DB_CONNECTION` a `mysql`.
3. Agrega las variables `DB_HOST`, `DB_PORT` (3306), `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
