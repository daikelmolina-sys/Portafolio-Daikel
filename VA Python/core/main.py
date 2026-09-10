"""Orquestador principal del asistente virtual J.A.R.V.I.S."""

import os
import signal
import sys
from typing import NoReturn

from dotenv import load_dotenv

from modules.stt import ReconocedorDeVoz
from modules.tts import SintetizadorDeVoz
from modules.nlp import ProcesadorNLP

load_dotenv()

PALABRA_ACTIVACION: str = os.getenv("WAKE_WORD", "jarvis").lower()
NOMBRE_ASISTENTE: str = os.getenv("ASSISTANT_NAME", "Jarvis")


class Asistente:
    """Bucle principal: escucha → detecta palabra clave → procesa → responde."""

    def __init__(self) -> None:
        self._activo: bool = True
        self._tts: SintetizadorDeVoz = SintetizadorDeVoz()
        self._stt: ReconocedorDeVoz = ReconocedorDeVoz()
        self._nlp: ProcesadorNLP = ProcesadorNLP()

        signal.signal(signal.SIGINT, self._apagar)
        signal.signal(signal.SIGTERM, self._apagar)

    def _apagar(self, signum: int, frame: object) -> None:
        """Apagado controlado por señal del sistema."""
        self._tts.hablar("Hasta luego, señor.")
        self._activo = False

    def iniciar(self) -> NoReturn:
        """Punto de entrada del bucle de escucha."""
        self._tts.hablar(
            f"Hola señor, soy {NOMBRE_ASISTENTE}. Calibrando sistemas de audio..."
        )

        try:
            self._stt.calibrar()
        except RuntimeError as exc:
            self._tts.hablar(str(exc))
            sys.exit(1)

        self._tts.hablar(
            f"Sistemas en línea. Diga '{PALABRA_ACTIVACION}' seguido de su instrucción."
        )

        while self._activo:
            self._ciclo_escucha()

        sys.exit(0)

    def _ciclo_escucha(self) -> None:
        """Ejecuta un ciclo: escucha pasiva → detección → comando → respuesta."""
        transcripcion: str = self._stt.escuchar(tiempo_espera=None, limite_frase=10)

        if not transcripcion:
            return

        if PALABRA_ACTIVACION not in transcripcion:
            return

        comando: str = transcripcion.replace(PALABRA_ACTIVACION, "").strip()

        if not comando:
            self._tts.hablar("A su servicio, ¿qué necesita?")
            comando = self._stt.escuchar(tiempo_espera=8, limite_frase=20)
            if not comando:
                self._tts.hablar("No capté ninguna instrucción, intente de nuevo.")
                return

        self._procesar_comando(comando)

    def _procesar_comando(self, comando: str) -> None:
        """Envía el comando al NLP y reproduce la respuesta."""
        respuesta: str = self._nlp.procesar(comando)
        self._tts.hablar(respuesta)


def main() -> None:
    """Punto de entrada."""
    asistente = Asistente()
    asistente.iniciar()


if __name__ == "__main__":
    main()
