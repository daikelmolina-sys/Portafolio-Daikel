import pyttsx3


class SintetizadorDeVoz:
    """Motor de síntesis de voz offline con pyttsx3.

    Recrea el engine en cada llamada a hablar() para evitar el bug de
    runAndWait() en Windows SAPI5.
    """

    def __init__(self, velocidad: int = 160, volumen: float = 1.0) -> None:
        self._velocidad: int = velocidad
        self._volumen: float = volumen
        self._id_voz: str | None = None

        # Detectar voz en español disponible una sola vez
        try:
            motor: pyttsx3.Engine = pyttsx3.init()
        except Exception as exc:
            raise RuntimeError(f"No se pudo inicializar el motor TTS: {exc}") from exc

        voces = motor.getProperty("voices")
        voz_espanol = next(
            (v for v in voces if "spanish" in v.name.lower() or "es" in v.id.lower()),
            None,
        )
        if voz_espanol:
            self._id_voz = voz_espanol.id

        motor.stop()
        del motor

    def _crear_motor(self) -> pyttsx3.Engine:
        """Crea y configura una instancia limpia del motor TTS."""
        motor: pyttsx3.Engine = pyttsx3.init()
        motor.setProperty("rate", self._velocidad)
        motor.setProperty("volume", self._volumen)
        if self._id_voz:
            motor.setProperty("voice", self._id_voz)
        return motor

    def hablar(self, texto: str) -> None:
        """Sintetiza y reproduce el texto proporcionado.

        Crea un motor nuevo en cada llamada para evitar el bug de
        runAndWait() en Windows SAPI5, donde el loop de eventos
        se queda muerto tras la primera ejecución.
        """
        if not texto:
            return
        motor: pyttsx3.Engine | None = None
        try:
            motor = self._crear_motor()
            motor.say(texto)
            motor.runAndWait()
        except Exception:
            # Reintento con motor completamente nuevo
            try:
                motor = pyttsx3.init()
                motor.say(texto)
                motor.runAndWait()
            except Exception:
                pass
        finally:
            if motor:
                try:
                    motor.stop()
                except Exception:
                    pass
