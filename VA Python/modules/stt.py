import speech_recognition as sr


class ReconocedorDeVoz:
    """Gestión del micrófono, calibración de ruido ambiental y transcripción."""

    def __init__(self, umbral_energia: int = 4000, umbral_pausa: float = 0.8) -> None:
        self._reconocedor: sr.Recognizer = sr.Recognizer()
        self._reconocedor.energy_threshold = umbral_energia
        self._reconocedor.pause_threshold = umbral_pausa
        self._microfono: sr.Microphone = sr.Microphone()

    def calibrar(self, duracion: float = 2.0) -> None:
        """Ajusta el umbral de energía al ruido ambiental."""
        try:
            with self._microfono as fuente:
                self._reconocedor.adjust_for_ambient_noise(fuente, duration=duracion)
        except OSError as exc:
            raise RuntimeError(f"No se pudo acceder al micrófono: {exc}") from exc

    def escuchar(self, tiempo_espera: int = 5, limite_frase: int = 15) -> str:
        """Captura audio del micrófono y lo transcribe a texto.

        Retorna la transcripción en minúsculas o cadena vacía si no
        se reconoce nada.
        """
        try:
            with self._microfono as fuente:
                audio: sr.AudioData = self._reconocedor.listen(
                    fuente,
                    timeout=tiempo_espera,
                    phrase_time_limit=limite_frase,
                )
        except sr.WaitTimeoutError:
            return ""
        except OSError as exc:
            raise RuntimeError(f"Error de micrófono: {exc}") from exc

        return self._transcribir(audio)

    def _transcribir(self, audio: sr.AudioData) -> str:
        """Envía el audio a Google Web Speech API para transcripción."""
        try:
            texto: str = self._reconocedor.recognize_google(audio, language="es-ES")
            return texto.lower().strip()
        except sr.UnknownValueError:
            return ""
        except sr.RequestError:
            print("[STT] Sin conexión para transcribir.")
            return ""
