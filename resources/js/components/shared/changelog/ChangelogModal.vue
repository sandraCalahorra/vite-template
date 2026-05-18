<template>
    <transition name="fade" appear>
        <div
            v-if="showModal"
            class="fixed inset-0 z-[300] flex items-center justify-center bg-black/40 backdrop-blur-md p-4"
            @click.self="!blockModal ? close() : null"
        >
            <transition name="pop" appear>
                <div
                    v-if="showContent"
                    class="relative w-full max-w-3xl rounded-2xl shadow-2xl border border-neutral-200/50 dark:border-white/10 overflow-hidden"
                    :style="backgroundStyle"
                >
                    <div class="relative px-8 pt-8 pb-2 bg-white/90 dark:bg-neutral-900/90 text-neutral-800 dark:text-neutral-100 transition-colors duration-300">
                        <!-- Header -->
                        <div class="flex flex-col items-center text-center mb-8">
                            <h2 class="text-2xl font-semibold tracking-tight">{{ title }}</h2>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ version }} • {{ date }}</p>
                        </div>

                        <button
                            @click.prevent="close"
                            class="absolute top-4 right-4 text-gray-800 hover:text-gray-900 transition-colors text-lg font-bold"
                            aria-label="Cerrar"
                        >
                            <IconXSolid class="size-4" />
                        </button>

                        <!-- Body -->
                        <div class="space-y-6 max-h-[65vh] overflow-y-auto px-1 scrollbar">
                            <ChangelogSection
                                v-if="changelog?.news"
                                icon="IconAutoAwesoneFilled"
                                title="Novedades"
                                :content="changelog.news"
                            />

                            <ChangelogSection
                                v-if="changelog?.improvements"
                                icon="IconSettings"
                                title="Mejoras"
                                :content="changelog.improvements"
                            />

                            <ChangelogSection
                                v-if="changelog?.bugfixes"
                                icon="IconBug"
                                title="Arreglos"
                                :content="changelog.bugfixes"
                            />
                        </div>

                        <!-- Footer  -->
                        <div class="py-3 flex flex-col items-center justify-center gap-2 text-sm opacity-80">
                            <p class="text-center text-xs italic leading-relaxed">
                                ¡Lea todas las
                                <a
                                    :href="$router.resolve({ name: 'changelogs' }).href"
                                    class="text-primary-500 hover:underline cursor-pointer hover:text-primary-700"
                                >
                                notas de las versiones de Montearagón
                                </a>
                                y vea cómo funciona!
                            </p>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<script>
export default {
    emits: ['close'],
    props: {
        open: {
            type: Boolean,
            required: true
        },
        blockModal: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            required: true,
            default: "Título"
        },
        date: {
            type: String,
            default: ""
        },
        version: {
            type: String,
            default: ""
        },
        changelog: {
            type: Object,
            default: () => ({ news: '', improvements: '', bugfixes: '' })
        }
    },

    data() {
        return {
            showModal: false,
            showContent: false
        };
    },

    computed: {
        backgroundStyle() {
            return `
                background-image: url('img/Artboard5.svg');
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center;
            `;
        }
    },

    watch: {
        open: {
            immediate: true,
            handler(newValue) {
                if (newValue) {
                    this.showModal = true;
                    setTimeout(() => (this.showContent = true), 10);
                } else {
                    this.close();
                }
            }
        }
    },

    methods: {
        close() {
            this.showContent = false;
            setTimeout(() => {
                this.showModal = false;
                this.$emit('close');
            }, 200);
        }
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.pop-enter-active,
.pop-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.pop-enter-from,
.pop-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(10px);
}
.dark .scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(200, 200, 200, 0.2);
}
</style>
