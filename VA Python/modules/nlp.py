import os
from typing import Optional

from dotenv import load_dotenv
from google import genai
from google.genai import types

load_dotenv()

_PROMPT_SISTEMA: str = (
    "Eres J.A.R.V.I.S., el asistente de inteligencia artificial inspirado en "
    "el sistema creado por Tony Stark. Tu personalidad es sofisticada, eficiente "
    "y con un sutil toque de humor británico. Respondes de forma concisa, clara "
    "y en español. Tratas al usuario como 'señor' o 'señora'. "
    "Si no sabes algo, lo admites con elegancia. "
    "Nunca rompes el personaje."
)


class ProcesadorNLP:
    """Cliente de la API de Gemini con gestión de contexto conversacional."""

    def __init__(
        self,
        prompt_sistema: Optional[str] = None,
        historial_maximo: int = 20,
    ) -> None:
        clave_api: Optional[str] = os.getenv("GEMINI_API_KEY")
        if not clave_api:
            raise ValueError(
                "GEMINI_API_KEY no configurada. Revisa tu archivo .env"
            )

        self._cliente: genai.Client = genai.Client(api_key=clave_api)
        self._prompt_sistema: str = prompt_sistema or _PROMPT_SISTEMA
        self._historial_maximo: int = historial_maximo
        self._modelo: str = "gemini-3.6-flash"
        self._historial: list[types.Content] = []

    def procesar(self, entrada_usuario: str) -> str:
        """Envía el mensaje del usuario al LLM y retorna la respuesta."""
        self._historial.append(
            types.Content(role="user", parts=[types.Part(text=entrada_usuario)])
        )

        try:
            respuesta = self._cliente.models.generate_content(
                model=self._modelo,
                contents=self._historial,
                config=types.GenerateContentConfig(
                    system_instruction=self._prompt_sistema,
                ),
            )
            texto_asistente: str = respuesta.text.strip()
        except Exception as exc:
            self._historial.pop()
            return f"Error al conectar con el LLM: {exc}"

        self._historial.append(
            types.Content(role="model", parts=[types.Part(text=texto_asistente)])
        )
        self._recortar_historial()
        return texto_asistente

    def _recortar_historial(self) -> None:
        """Recorta el historial para no exceder el límite de contexto."""
        if len(self._historial) > self._historial_maximo * 2:
            self._historial = self._historial[-(self._historial_maximo * 2):]

    def reiniciar_contexto(self) -> None:
        """Reinicia la conversación descartando el historial."""
        self._historial.clear()
