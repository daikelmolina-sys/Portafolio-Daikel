import subprocess
from typing import Optional

import requests


class EjecutorDeAcciones:
    """Ejecutor de acciones del sistema y peticiones HTTP."""

    @staticmethod
    def ejecutar_comando(comando: str, tiempo_limite: int = 30) -> str:
        """Ejecuta un comando del sistema y retorna su salida."""
        try:
            resultado: subprocess.CompletedProcess[str] = subprocess.run(
                comando,
                shell=True,
                capture_output=True,
                text=True,
                timeout=tiempo_limite,
            )
            salida: str = resultado.stdout.strip() or resultado.stderr.strip()
            return salida if salida else "Comando ejecutado sin salida."
        except subprocess.TimeoutExpired:
            return "El comando excedió el tiempo límite."
        except OSError as exc:
            return f"Error al ejecutar el comando: {exc}"

    @staticmethod
    def peticion_get(url: str, tiempo_limite: int = 10, cabeceras: Optional[dict[str, str]] = None) -> str:
        """Realiza una petición GET y retorna el cuerpo de la respuesta."""
        try:
            respuesta: requests.Response = requests.get(
                url, timeout=tiempo_limite, headers=cabeceras or {}
            )
            respuesta.raise_for_status()
            return respuesta.text[:2000]
        except requests.ConnectionError:
            return "Sin conexión a internet."
        except requests.Timeout:
            return "La petición HTTP excedió el tiempo límite."
        except requests.HTTPError as exc:
            return f"Error HTTP: {exc.response.status_code}"

    @staticmethod
    def peticion_post(
        url: str,
        datos: Optional[dict[str, object]] = None,
        tiempo_limite: int = 10,
        cabeceras: Optional[dict[str, str]] = None,
    ) -> str:
        """Realiza una petición POST y retorna el cuerpo de la respuesta."""
        try:
            respuesta: requests.Response = requests.post(
                url, json=datos, timeout=tiempo_limite, headers=cabeceras or {}
            )
            respuesta.raise_for_status()
            return respuesta.text[:2000]
        except requests.ConnectionError:
            return "Sin conexión a internet."
        except requests.Timeout:
            return "La petición HTTP excedió el tiempo límite."
        except requests.HTTPError as exc:
            return f"Error HTTP: {exc.response.status_code}"
