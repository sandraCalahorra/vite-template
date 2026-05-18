<template>
    <div class="min-h-screen w-full bg-slate-100 lg:grid lg:grid-cols-[minmax(0,1.05fr)_minmax(28rem,0.95fr)]">
        <section class="relative hidden overflow-hidden bg-[linear-gradient(135deg,var(--primary-dark)_0%,var(--primary)_54%,var(--primary-medium-light)_100%)] text-white lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-12 lg:py-10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(255,255,255,0.20),transparent_28%),radial-gradient(circle_at_82%_78%,rgba(255,255,255,0.16),transparent_34%)]"></div>

            <div class="relative flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-white/95 p-2 shadow-lg">
                    <img src="/img/vitejs.svg" class="h-full w-full object-contain" alt="Vite Template" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/75">Template</p>
                    <h1 class="text-xl font-bold leading-tight">STARTER KIT VITE</h1>
                </div>
            </div>

            <div class="relative max-w-md">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.16em] text-white/70">Acceso</p>
                <h2 class="text-3xl font-bold leading-tight">
                    Plantilla lista para adaptar.
                </h2>
                <p class="mt-4 max-w-sm text-sm leading-6 text-white/85">
                    Base de autenticación para proyectos Vue y Vite.
                </p>
            </div>

            <div class="relative flex items-center justify-between text-xs text-white/70">
                <span>© {{ currentYear }} Vite Template</span>
                <span>Acceso seguro</span>
            </div>
        </section>

        <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-8 sm:px-6 lg:px-10">
            <section class="w-full max-w-sm rounded-lg bg-white px-5 py-7 shadow-lg shadow-slate-200/80 sm:px-7 sm:py-8">
                <div class="mb-6 flex flex-col items-center text-center">
                    <img src="/img/vitejs.svg" class="mb-3 h-12 w-12 object-contain lg:hidden" alt="Vite Template" />
                    <p v-if="eyebrow" class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-primary">{{ eyebrow }}</p>
                    <h2 class="text-xl font-bold leading-tight text-slate-900">{{ title }}</h2>
                    <p v-if="subtitle" class="mt-2 max-w-xs text-sm leading-5 text-slate-500">{{ subtitle }}</p>
                </div>

                <slot />
            </section>
        </main>
    </div>
</template>

<script>
export default {
    name: 'AuthTemplate',
    props: {
        title: {
            type: String,
            required: true,
        },
        subtitle: {
            type: String,
            default: '',
        },
        eyebrow: {
            type: String,
            default: 'Acceso',
        },
    },
    computed: {
        currentYear() {
            return new Date().getFullYear();
        },
    },
};
</script>
