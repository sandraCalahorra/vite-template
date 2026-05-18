<template>
  <!--
    Alert global de la app.
    Normalmente no se usa directamente desde cada vista: se renderiza una vez en App.vue
    y se dispara con this.openAlert(type, title, description) desde AlertMixin.
  -->
  <transition
    enter-active-class="transform transition duration-300 ease-out"
    enter-from-class="translate-y-4 opacity-0 sm:translate-x-4 sm:translate-y-0"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transform transition duration-200 ease-in"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
  >
    <div
      v-if="open"
      class="fixed z-50 px-4 left-0 right-0 bottom-4 sm:left-auto sm:right-4 sm:w-full sm:max-w-md"
      :class="routeSpacingClass"
    >
      <div
        class="overflow-hidden rounded-lg border shadow-lg ring-1 ring-black/5"
        :style="toastStyles"
        :role="ariaRole"
        :aria-live="ariaLive"
        @mouseenter="pauseTimer"
        @mouseleave="resumeTimer"
      >
        <div class="flex items-center gap-3 px-2 py-2">
          <div
            class="flex shrink-0 items-center justify-center"
            :style="iconStyles"
            aria-hidden="true"
          >
            <IconCheckCircle v-if="resolvedVariant.icon === 'success'" class="w-4 h-4 min-w-4" />
            <HeroiconXCircle v-else-if="resolvedVariant.icon === 'error'" class="w-4 h-4 min-w-4" />
            <IconWarning v-else-if="resolvedVariant.icon === 'warning'" class="w-4 h-4 min-w-4" />
            <IconInfo v-else class="w-4 h-4 min-w-4" />
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-3">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold leading-5" :style="titleStyles">
                  {{ title }}
                </p>

                <div
                  v-if="description"
                  class="mt-1 text-sm leading-5 break-words"
                  :style="descriptionStyles"
                >
                  <div v-if="isHtmlContent(description)" v-html="description"></div>
                  <p v-else>{{ description }}</p>
                </div>
              </div>

              <button
                type="button"
                class="alert-close inline-flex size-7 shrink-0 items-center justify-center rounded-md transition-colors focus:outline-none"
                :style="closeButtonStyles"
                aria-label="Cerrar alerta"
                @click.stop="closeAlert"
              >
                <svg class="size-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 5L15 15M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Barra inferior que indica el tiempo restante antes del autocierre. -->
        <div class="h-1 w-full" :style="progressTrackStyles">
          <div
            class="h-full transition-[width] duration-100 linear"
            :style="progressStyles"
          ></div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
/**
 * Alert
 *
 * Toast global renderizado desde App.vue y alimentado por Vuex.
 * Flujo recomendado de uso:
 *
 * this.openAlert("success", "Registro guardado");
 * this.openAlert("error", "Error al guardar", "No se pudo completar la accion.");
 *
 * Tipos soportados desde AlertMixin:
 * - success
 * - error
 * - info
 * - warning
 *
 * Comportamiento:
 * - autocierre configurable con `duration`
 * - pausa del temporizador al hacer hover
 * - cierre con tecla Escape
 * - cierre manual con la X
 * - barra inferior de progreso
 */
const COLOR_MAP = {
  green: {
    accent: "#16a34a",
    bar: "#16a34a",
    icon: "success",
    role: "status",
    surface: "#dcfce7",
    border: "rgba(22, 163, 74, 0.22)",
    title: "#14532d",
    description: "#166534",
    track: "rgba(20, 83, 45, 0.10)",
    close: "#166534",
    closeHoverBg: "rgba(20, 83, 45, 0.08)",
    closeHoverColor: "#14532d",
    focus: "rgba(22, 163, 74, 0.22)",
  },
  red: {
    accent: "#dc2626",
    bar: "#dc2626",
    icon: "error",
    role: "alert",
    surface: "#fee2e2",
    border: "rgba(220, 38, 38, 0.22)",
    title: "#7f1d1d",
    description: "#991b1b",
    track: "rgba(127, 29, 29, 0.10)",
    close: "#991b1b",
    closeHoverBg: "rgba(127, 29, 29, 0.08)",
    closeHoverColor: "#7f1d1d",
    focus: "rgba(220, 38, 38, 0.22)",
  },
  blue: {
    accent: "#2563eb",
    bar: "#2563eb",
    icon: "info",
    role: "status",
    surface: "#dbeafe",
    border: "rgba(37, 99, 235, 0.22)",
    title: "#1e3a8a",
    description: "#1d4ed8",
    track: "rgba(30, 58, 138, 0.10)",
    close: "#1d4ed8",
    closeHoverBg: "rgba(30, 58, 138, 0.08)",
    closeHoverColor: "#1e3a8a",
    focus: "rgba(37, 99, 235, 0.22)",
  },
  yellow: {
    accent: "#d97706",
    bar: "#d97706",
    icon: "warning",
    role: "status",
    surface: "#fef3c7",
    border: "rgba(217, 119, 6, 0.22)",
    title: "#92400e",
    description: "#b45309",
    track: "rgba(146, 64, 14, 0.10)",
    close: "#b45309",
    closeHoverBg: "rgba(146, 64, 14, 0.08)",
    closeHoverColor: "#92400e",
    focus: "rgba(217, 119, 6, 0.22)",
  },
};

export default {
  name: "Alert",
  props: {
    color: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      default: "",
    },
    open: {
      type: Boolean,
      default: false,
    },
    duration: {
      type: Number,
      default: 5000,
    },
  },
  emits: ["close"],
  data() {
    return {
      timeoutId: null,
      intervalId: null,
      startedAt: null,
      remainingMs: this.duration,
      progress: 100,
      formRoutes: [
        "announcementsForm",
        "announcementsCreate",
        "customersForm",
        "customersCreate",
        "devicesForm",
        "devicesCreate",
        "myProfile",
      ],
    };
  },
  computed: {
    // Resuelve la paleta visual del toast a partir del color que llega desde Vuex.
    resolvedVariant() {
      return COLOR_MAP[this.color] || COLOR_MAP.blue;
    },
    // En formularios con más elementos fijos abajo, elevamos el toast para no taparlos.
    routeSpacingClass() {
      return this.formRoutes.includes(this.$route.name) ? "sm:bottom-20" : "sm:bottom-6";
    },
    toastStyles() {
      return {
        backgroundColor: this.resolvedVariant.surface,
        borderColor: this.resolvedVariant.border,
      };
    },
    iconStyles() {
      return {
        color: this.resolvedVariant.accent,
      };
    },
    titleStyles() {
      return {
        color: this.resolvedVariant.title,
      };
    },
    descriptionStyles() {
      return {
        color: this.resolvedVariant.description,
      };
    },
    closeButtonStyles() {
      return {
        color: this.resolvedVariant.close,
        "--alert-close-hover-bg": this.resolvedVariant.closeHoverBg,
        "--alert-close-hover-color": this.resolvedVariant.closeHoverColor,
        "--alert-focus-ring": this.resolvedVariant.focus,
      };
    },
    progressTrackStyles() {
      return {
        backgroundColor: this.resolvedVariant.track,
      };
    },
    progressStyles() {
      return {
        width: `${this.progress}%`,
        backgroundColor: this.resolvedVariant.bar,
      };
    },
    ariaRole() {
      return this.resolvedVariant.role;
    },
    ariaLive() {
      return this.color === "red" ? "assertive" : "polite";
    },
  },
  watch: {
    open: {
      immediate: true,
      handler(isOpen) {
        if (isOpen) {
          this.startTimer();
          window.addEventListener("keydown", this.handleKeydown);
        } else {
          this.resetTimer();
          window.removeEventListener("keydown", this.handleKeydown);
        }
      },
    },
    duration(newDuration) {
      this.remainingMs = newDuration;
      this.progress = 100;
      if (this.open) {
        this.startTimer();
      }
    },
  },
  beforeUnmount() {
    this.resetTimer();
    window.removeEventListener("keydown", this.handleKeydown);
  },
  methods: {
    // Permite mensajes simples o mensajes HTML con saltos de linea / listas cortas.
    isHtmlContent(val) {
      return /<[^>]*>/.test(val || "");
    },
    // Arranca el ciclo completo del toast: timeout de cierre y barra de progreso.
    startTimer() {
      this.resetTimer(false);

      this.remainingMs = this.duration;
      this.progress = 100;
      this.startedAt = Date.now();

      this.timeoutId = setTimeout(() => {
        this.closeAlert();
      }, this.remainingMs);

      this.intervalId = setInterval(() => {
        const elapsed = Date.now() - this.startedAt;
        const nextRemaining = Math.max(this.remainingMs - elapsed, 0);
        this.progress = (nextRemaining / this.duration) * 100;
      }, 100);
    },
    // Congela el toast mientras el usuario lo esta leyendo o va a cerrarlo manualmente.
    pauseTimer() {
      if (!this.open || !this.timeoutId) {
        return;
      }

      const elapsed = Date.now() - this.startedAt;
      this.remainingMs = Math.max(this.remainingMs - elapsed, 0);
      this.clearTimers();
    },
    // Reanuda el tiempo restante cuando el usuario deja de interactuar con el toast.
    resumeTimer() {
      if (!this.open || this.timeoutId || this.remainingMs <= 0) {
        return;
      }

      this.startedAt = Date.now();

      this.timeoutId = setTimeout(() => {
        this.closeAlert();
      }, this.remainingMs);

      this.intervalId = setInterval(() => {
        const elapsed = Date.now() - this.startedAt;
        const nextRemaining = Math.max(this.remainingMs - elapsed, 0);
        this.progress = (nextRemaining / this.duration) * 100;
      }, 100);
    },
    clearTimers() {
      clearTimeout(this.timeoutId);
      clearInterval(this.intervalId);
      this.timeoutId = null;
      this.intervalId = null;
    },
    resetTimer(resetProgress = true) {
      this.clearTimers();
      this.startedAt = null;
      this.remainingMs = this.duration;
      if (resetProgress) {
        this.progress = 100;
      }
    },
    // Atajo de accesibilidad para cerrar el toast sin raton.
    handleKeydown(event) {
      if (event.key === "Escape" && this.open) {
        this.closeAlert();
      }
    },
    closeAlert() {
      this.resetTimer();
      this.$emit("close");
    },
  },
};
</script>

<style scoped>
.alert-close:hover {
  background-color: var(--alert-close-hover-bg);
  color: var(--alert-close-hover-color);
}

.alert-close:focus-visible {
  box-shadow: 0 0 0 2px var(--alert-focus-ring);
}
</style>
