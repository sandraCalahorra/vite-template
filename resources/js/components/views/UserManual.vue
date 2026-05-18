<template>
    <div class="user-manual-view h-screen flex flex-col bg-gray-50">
        <TheTopbar title="Centro de Manuales" />

        <div class="flex-1 overflow-hidden p-4 lg:p-6">
            <div class="max-w-7xl mx-auto h-full flex flex-col gap-4">

                <!-- Manual Selection Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 shrink-0">
                    <div
                        v-for="manual in visibleManuals"
                        :key="manual.path"
                        @click="selectedManual = manual"
                        class="manual-card group relative p-4 rounded-xl border-2 transition-all duration-300 cursor-pointer overflow-hidden bg-white shadow-sm hover:shadow-lg active:scale-[0.98]"
                        :class="[
                            selectedManual?.path === manual.path
                                ? 'border-primary ring-2 ring-primary/10 bg-primary/[0.02]'
                                : 'border-transparent hover:border-primary/20'
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <div class="logo-container relative shrink-0 w-24 h-12 flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                                <component
                                    v-if="manual.icon"
                                    :is="manual.icon"
                                    class="w-10 h-10 text-primary"
                                />
                                <img
                                    v-else
                                    :src="manual.image"
                                    :alt="manual.title"
                                    class="max-w-full max-h-full object-contain"
                                />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-0.5 group-hover:text-primary transition-colors">
                                    {{ manual.title }}
                                </h3>
                                <p class="text-xs text-gray-500 line-clamp-1 leading-relaxed">
                                    {{ manual.description }}
                                </p>
                            </div>
                            <div class="shrink-0 transition-transform duration-300 transform" :class="selectedManual?.path === manual.path ? 'translate-x-1' : 'opacity-0 group-hover:opacity-100 group-hover:translate-x-1'">
                                <HeroiconChevronRight class="w-5 h-5 text-primary" />
                            </div>
                        </div>
                        <!-- Background Decoration -->
                        <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors"></div>
                    </div>
                </div>

                <!-- Content Viewer Area -->
                <div class="flex-1 min-h-0 relative rounded-xl border border-gray-200 bg-white shadow-md overflow-hidden flex flex-col">
                    <div v-if="selectedManual" class="flex-1 flex flex-col h-full bg-gray-100">
                        <div class="px-4 py-3 bg-white border-b border-gray-200 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <IconQuestionDocument class="w-4 h-4 text-primary" />
                                <span class="font-semibold text-sm text-gray-700">{{ selectedManual.title }}</span>
                            </div>
                            <a
                                v-if="selectedManual.type === 'pdf'"
                                :href="selectedManual.path"
                                target="_blank"
                                class="flex items-center gap-2 text-[10px] font-bold text-primary hover:text-primary-dark transition-colors px-3 py-1 rounded-lg bg-primary/5 hover:bg-primary/10"
                            >
                                <IconDownload class="w-3.5 h-3.5" />
                                DESCARGAR PDF
                            </a>
                        </div>

                        <!-- PDF Viewer -->
                        <iframe
                            v-if="selectedManual.type === 'pdf'"
                            :src="`${selectedManual.path}#toolbar=0&navpanes=0&scrollbar=0`"
                            class="flex-1 w-full border-none"
                            title="Manual Viewer"
                        ></iframe>

                        <!-- HTML Content Viewer -->
                        <div v-else-if="selectedManual.type === 'html'" class="flex-1 overflow-y-auto p-6 bg-white">
                            <div class="max-w-4xl mx-auto prose prose-sm">
                                <div class="bg-gradient-to-r from-primary/10 to-primary/5 border-l-4 border-primary p-4 rounded-r-lg mb-6">
                                    <p class="text-sm text-gray-700 mb-0">
                                        <strong>📋 Objetivo:</strong> Esta guía te ayudará a identificar y reportar problemas técnicos en la plataforma, incluso si no tienes conocimientos de informática.
                                    </p>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                                    ¿Cuándo necesito reportar un error?
                                </h2>
                                <p class="text-gray-700 mb-4">
                                    Debes reportar un error cuando algo no funcione como esperas: un botón que no responde, información que no se guarda, páginas que no cargan, o cualquier comportamiento extraño de la plataforma.
                                </p>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                                    Método Rápido: Descarga de Reporte Directo
                                </h2>
                                <p class="text-gray-700 mb-4">
                                    Si detectas que algo no funciona bien, puedes descargar un reporte técnico con un solo clic desde tu menú de perfil.
                                </p>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">🚀 Cómo descargar el reporte:</h3>
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700 text-sm">
                                        <li>Haz clic en el <strong>icono del engranaje</strong> (tu menú de usuario) situado en la esquina superior derecha.</li>
                                        <li>Busca la opción que dice <strong class="text-red-600">"DESCARGAR REPORTE"</strong>.</li>
                                        <li>Se descargará automáticamente un archivo en tu ordenador con toda la información técnica necesaria.</li>
                                        <li>Envía ese archivo por correo o adjúntalo en el centro de soporte indicando qué estabas haciendo.</li>
                                    </ol>
                                    <p class="mt-3 text-gray-700 text-sm bg-white/50 p-2 rounded border border-blue-300 italic">
                                        <strong>💡 Ventaja:</strong> Este método captura errores internos que a veces no son visibles a simple vista en la pantalla.
                                    </p>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                                    Método Avanzado: Herramientas del Navegador
                                </h2>
                                <p class="text-gray-700 mb-4">
                                    Las herramientas del navegador nos permiten ver información técnica que ayuda a identificar el problema. No te preocupes, <strong>no vas a romper nada</strong> al abrirlas.
                                </p>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">📱 Paso a paso para abrir las herramientas:</h3>
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700 text-sm">
                                        <li><strong>Opción 1 (más fácil):</strong> Presiona la tecla <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">F12</kbd> en tu teclado</li>
                                        <li><strong>Opción 2:</strong> Haz clic derecho en cualquier parte de la página y selecciona <strong>"Inspeccionar"</strong> o <strong>"Inspeccionar elemento"</strong></li>
                                        <li><strong>Opción 3:</strong> Presiona <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Ctrl</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Shift</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">I</kbd> (en Windows) o <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Cmd</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Option</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">I</kbd> (en Mac)</li>
                                    </ol>
                                    <p class="mt-3 text-gray-700 text-sm bg-white/50 p-2 rounded border border-blue-300">
                                        <strong>⚠️ Nota importante:</strong> En algunos teclados (especialmente portátiles), puede que necesites presionar <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Fn</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">F12</kbd> en lugar de solo <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">F12</kbd>. La tecla <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Fn</kbd> suele estar en la esquina inferior izquierda del teclado.
                                    </p>
                                </div>

                                <p class="text-gray-700 mb-4">
                                    Se abrirá un panel en la parte inferior o lateral de tu navegador. <strong>No te asustes</strong> si ves mucho texto o código, es completamente normal.
                                </p>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">4</span>
                                    Pestaña "Console" (Consola)
                                </h2>
                                <p class="text-gray-700 mb-4">
                                    La pestaña <strong>Console</strong> muestra mensajes de error que ocurren en la plataforma. Es como un registro de lo que está pasando "por dentro".
                                </p>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">✅ Qué hacer en la pestaña Console:</h3>
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700 text-sm">
                                        <li>En el panel que se abrió, busca y haz clic en la pestaña que dice <strong>"Console"</strong> o <strong>"Consola"</strong></li>
                                        <li>Verás una lista de mensajes. Los errores suelen aparecer en <span class="text-red-600 font-bold">rojo</span></li>
                                        <li>Intenta reproducir el error (por ejemplo, vuelve a hacer clic en el botón que no funcionó)</li>
                                        <li>Observa si aparecen nuevos mensajes en rojo</li>
                                        <li><strong>Haz una captura de pantalla</strong> de toda la ventana, incluyendo el panel de Console</li>
                                    </ol>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">5</span>
                                    Pestaña "Network" (Red)
                                </h2>
                                <p class="text-gray-700 mb-4">
                                    La pestaña <strong>Network</strong> muestra todas las comunicaciones entre tu navegador y el servidor. Es útil para detectar problemas de conexión o errores al cargar datos.
                                </p>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">✅ Qué hacer en la pestaña Network:</h3>
                                    <ol class="list-decimal list-inside space-y-2 text-gray-700 text-sm">
                                        <li>Haz clic en la pestaña <strong>"Network"</strong> o <strong>"Red"</strong></li>
                                        <li><strong>Recarga la página</strong> presionando <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">F5</kbd> o el botón de recargar del navegador</li>
                                        <li>Verás una lista de elementos cargándose. Busca elementos que aparezcan en <span class="text-red-600 font-bold">rojo</span> o con números como <strong>400</strong>, <strong>500</strong>, o <strong>429</strong></li>
                                        <li>Si encuentras alguno, <strong>haz clic sobre él</strong> para ver más detalles</li>
                                        <li>En el panel derecho que aparece, busca y haz clic en las pestañas:
                                            <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                                <li><strong>"Preview"</strong> o <strong>"Vista previa"</strong> - muestra el contenido del error</li>
                                                <li><strong>"Payload"</strong> o <strong>"Carga útil"</strong> - muestra los datos que se enviaron</li>
                                            </ul>
                                        </li>
                                        <li><strong>Haz capturas de pantalla</strong> de:
                                            <ul class="list-disc list-inside ml-4 mt-1 space-y-1">
                                                <li>La lista completa mostrando el elemento en rojo seleccionado</li>
                                                <li>La pestaña <strong>Preview</strong> con el contenido del error</li>
                                                <li>La pestaña <strong>Payload</strong> con los datos enviados</li>
                                            </ul>
                                        </li>
                                    </ol>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">6</span>
                                    Cómo hacer capturas de pantalla
                                </h2>
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">📸 Métodos para capturar la pantalla:</h3>
                                    <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm">
                                        <li><strong>Windows:</strong> Presiona <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Windows</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Shift</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">S</kbd> para abrir la herramienta de recorte</li>
                                        <li><strong>Mac:</strong> Presiona <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Cmd</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">Shift</kbd> + <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">4</kbd> y selecciona el área</li>
                                        <li><strong>Herramienta de recortes:</strong> Busca "Recortes" en el menú de inicio de Windows</li>
                                    </ul>
                                    <p class="mt-3 text-gray-700 text-sm"><strong>💡 Consejo:</strong> Asegúrate de que la captura muestre claramente el error y las herramientas del navegador abiertas.</p>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-yellow-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">⚠️</span>
                                    Error "Too Many Requests" (429)
                                </h2>
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                    <h3 class="font-bold text-gray-900 mb-2 text-base">¿Qué significa este error?</h3>
                                    <p class="text-gray-700 mb-3 text-sm">
                                        El error <strong>"Too Many Requests"</strong> (o código <strong>429</strong>) significa que has hecho demasiadas peticiones a la plataforma en poco tiempo. Es como si llamaras muchas veces seguidas a una puerta y te pidieran que esperes un momento antes de volver a llamar.
                                    </p>
                                    <h3 class="font-bold text-gray-900 mb-2 text-base">¿Qué hacer?</h3>
                                    <p class="text-gray-700 mb-3 text-sm">
                                        <strong>✅ NO es necesario reportar este error.</strong> Es temporal y se soluciona solo. Simplemente:
                                    </p>
                                    <ol class="list-decimal list-inside space-y-1 text-gray-700 text-sm">
                                        <li>Espera <strong>1-2 minutos</strong></li>
                                        <li>Recarga la página presionando <kbd class="px-2 py-1 bg-gray-200 rounded border border-gray-300 text-xs font-mono">F5</kbd></li>
                                        <li>Intenta de nuevo lo que estabas haciendo</li>
                                    </ol>
                                    <p class="mt-3 text-gray-700 text-sm">
                                        <strong>💡 Consejo:</strong> Si este error aparece con mucha frecuencia, entonces sí puedes mencionarlo al soporte técnico, pero no es necesario enviar capturas de pantalla.
                                    </p>
                                </div>

                                <h2 class="text-xl font-bold text-gray-900 mb-4 mt-8 flex items-center gap-2">
                                    <span class="bg-primary text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">7</span>
                                    Enviar el reporte al soporte técnico
                                </h2>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                                    <h3 class="font-bold text-gray-900 mb-3 text-base">📧 Información a incluir en tu reporte:</h3>
                                    <ul class="list-disc list-inside space-y-2 text-gray-700 text-sm">
                                        <li><strong>Descripción del problema:</strong> Explica qué estabas intentando hacer y qué salió mal</li>
                                        <li><strong>Capturas de pantalla:</strong> Adjunta las capturas de Console y/o Network</li>
                                        <li><strong>Navegador utilizado:</strong> Chrome, Firefox, Edge, Safari, etc.</li>
                                        <li><strong>Fecha y hora:</strong> Cuándo ocurrió el problema</li>
                                        <li><strong>Pasos para reproducir:</strong> Si puedes, explica cómo hacer que el error vuelva a ocurrir</li>
                                    </ul>
                                </div>

                                <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4 mt-6">
                                    <p class="text-sm text-gray-700 mb-0">
                                        <strong>✨ ¡Gracias por tu ayuda!</strong> Con esta información, el equipo técnico podrá identificar y solucionar el problema mucho más rápido. No te preocupes si algo no te sale a la primera, lo importante es intentarlo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-6 bg-gradient-to-br from-white to-gray-50">
                        <div class="w-20 h-20 mb-6 relative">
                            <div class="absolute inset-0 bg-primary/5 rounded-full animate-pulse"></div>
                            <IconQuestionDocument class="absolute inset-0 m-auto w-10 h-10 text-primary/30" />
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Tu guía está lista</h2>
                        <p class="text-sm text-gray-500 max-w-xs">
                            Selecciona uno de los manuales superiores para comenzar a explorar las funcionalidades de la plataforma.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'UserManual',
    components: {
    },
    data() {
        return {
            selectedManual: null,
            manuals: [
                {
                    title: 'Manual de Usuario',
                    description: 'Guía completa sobre el uso de la plataforma.',
                    path: '/manual/TELECUIDAMOS. MANUAL DE USUARIO_2.0.0.pdf',
                    image: '/img/montearagon.png',
                    minLevel: 99,
                    type: 'pdf'
                },
                {
                    title: 'Cómo Detectar y Reportar Errores',
                    description: 'Guía paso a paso para identificar problemas en la plataforma y enviar capturas de pantalla al soporte técnico.',
                    path: null,
                    icon: 'IconError',
                    minLevel: 99,
                    type: 'html'
                }
            ]
        }
    },
    computed: {
        user() {
            return this.$store.getters['auth/user'];
        },
        visibleManuals() {
            if (!this.user) return [];
            return this.manuals.filter(manual => {
                return true;
            });
        }
    }
});
</script>

<style scoped>
.manual-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.manual-card:hover {
    transform: translateY(-4px);
}

.logo-container {
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

iframe {
    background-color: #525659; /* Standard PDF viewer background */
}

/* Custom scrollbar for webkit */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    @apply bg-gray-200 rounded-full hover:bg-gray-300 transition-colors;
}
</style>
