# 🚀 Guía de Despliegue en Render (Multi-Proyecto con Nginx y Docker)

Esta guía te explica paso a paso cómo desplegar los 3 proyectos en [Render.com](https://render.com) utilizando contenedores **Docker con Nginx y PHP-FPM**.

---

## 📌 Resumen de Puertos y Servicios

| Proyecto | Tipo de Servicio en Render | Root Directory en Render | Puerto Local (Preservado) | Puerto en Render |
| :--- | :--- | :--- | :--- | :--- |
| **Omnidex** | Web Service (Docker) | `pagina Omnidex/omnidex-app` | `9090` | Automático (`$PORT`) |
| **Fútbol Estadística** | Web Service (Docker) | `pagina futbol estadistica` | `8000` (DB: `3306`) | Automático (`$PORT`) |
| **Onigiri POS** | Web Service (Docker) | `pagina Gastronomia/onigiri-pos` | `8080` (DB: `3307`) | Automático (`$PORT`) |

---

## 🛠️ Paso 1: Subir tus cambios a GitHub
Asegúrate de hacer commit y push de tus archivos a tu repositorio de GitHub:
```bash
git add .
git commit -m "feat: docker and nginx configurations for Render"
git push origin main
```

---

## 🔮 Paso 2: Desplegar Omnidex en Render

1. En el panel de **Render**, haz clic en **New +** y selecciona **Web Service**.
2. Conecta tu repositorio de GitHub.
3. Configura los siguientes campos:
   * **Name:** `omnidex-app`
   * **Language / Environment:** `Docker`
   * **Root Directory:** `pagina Omnidex/omnidex-app`
   * **Dockerfile Path:** `./Dockerfile`
   * **Instance Type:** `Free`
4. Haz clic en **Create Web Service**. ¡Listo! Nginx servirá tu aplicación inmediatamente.

---

## ⚽ Paso 3: Desplegar Fútbol Estadística en Render

1. Haz clic en **New +** > **Web Service** y selecciona tu repositorio.
2. Configura los campos:
   * **Name:** `futbol-estadistica`
   * **Language / Environment:** `Docker`
   * **Root Directory:** `pagina futbol estadistica`
   * **Dockerfile Path:** `./Dockerfile`
   * **Instance Type:** `Free`
3. En la sección **Environment Variables**, añade las siguientes variables:
   * `APP_NAME`: `FutbolEstadistica`
   * `APP_ENV`: `production`
   * `APP_DEBUG`: `false`
   * `APP_KEY`: *(Genera una con `php artisan key:generate --show` o copia la de tu `.env`)*
   * `APP_URL`: *(La URL que te asigne Render, ej: `https://futbol-estadistica.onrender.com`)*
   * `DB_CONNECTION`: `mysql` *(o `sqlite` si prefieres prueba rápida)*
   * `DB_HOST`: *(Host de tu base de datos MySQL en la nube)*
   * `DB_PORT`: `3306`
   * `DB_DATABASE`: `nombre_de_tu_bd`
   * `DB_USERNAME`: `usuario_de_tu_bd`
   * `DB_PASSWORD`: `password_de_tu_bd`
4. Haz clic en **Create Web Service**.

---

## 🍱 Paso 4: Desplegar Onigiri POS en Render

1. Haz clic en **New +** > **Web Service** y selecciona tu repositorio.
2. Configura los campos:
   * **Name:** `onigiri-pos`
   * **Language / Environment:** `Docker`
   * **Root Directory:** `pagina Gastronomia/onigiri-pos`
   * **Dockerfile Path:** `./Dockerfile`
   * **Instance Type:** `Free`
3. En la sección **Environment Variables**, añade:
   * `APP_NAME`: `OnigiriPOS`
   * `APP_ENV`: `production`
   * `APP_DEBUG`: `false`
   * `APP_KEY`: *(Tu clave de Laravel)*
   * `APP_URL`: *(La URL que te asigne Render)*
   * `DB_CONNECTION`: `mysql`
   * `DB_HOST`: *(Host de tu base de datos MySQL en la nube)*
   * `DB_PORT`: `3306`
   * `DB_DATABASE`: `onigiri_db`
   * `DB_USERNAME`: `usuario_de_tu_bd`
   * `DB_PASSWORD`: `password_de_tu_bd`
4. Haz clic en **Create Web Service**. El multi-stage build compilará los assets de Vite con Node y levantará Nginx + PHP-FPM automáticamente.

---

## 🗄️ ¿Dónde obtener una Base de Datos MySQL gratuita en la nube?

Puedes crear una base de datos MySQL gratuita en menos de 2 minutos en cualquiera de estas opciones:
* **[Aiven.io](https://aiven.io/free-mysql):** Plan gratuito de MySQL administrado.
* **[TiDB Cloud](https://tidbcloud.com/):** Compatible 100% con MySQL con plan gratuito permanente.
* **[Clever Cloud](https://www.clever-cloud.com/):** Ofrece MySQL gratuito (hasta 5 conexiones).
