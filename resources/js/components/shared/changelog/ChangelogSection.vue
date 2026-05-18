<template>
    <div
        class="group relative rounded-2xl bg-white dark:bg-neutral-900
            border border-neutral-200 dark:border-neutral-800
            shadow-sm hover:shadow-md transition-all duration-300
            overflow-hidden flex"
    >
        <!-- Accent bar -->
        <div
            class="w-1 transition-all duration-300"
            :class="barColor"
        />

        <!-- Content -->
        <div class="p-6 flex-1">
            <div class="flex items-center gap-3 mb-4">
                <!-- Icon -->
                <div
                    class="h-9 w-9 min-h-9 min-w-9 flex-shrink-0
                            flex items-center justify-center rounded-xl
                            bg-neutral-100 dark:bg-neutral-800
                            transition-colors duration-300"
                    :class="iconAccent"
                >
                    <component :is="icon" class="h-4 w-4 flex-shrink-0" />
                </div>

                <!-- Title -->
                <h3 class="text-sm font-semibold tracking-wide text-neutral-800 dark:text-neutral-100 uppercase">
                    {{ title }}
                </h3>

            </div>

            <!-- Content -->
            <div
                class="prose prose-sm dark:prose-invert max-w-none
                    text-neutral-600 dark:text-neutral-300
                    leading-relaxed"
                v-html="content || 'Sin contenido.'"
            />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
        icon: String,
        content: String
    },

    computed: {
        barColor() {
            switch (this.title) {
            case "Novedades":
                return "bg-indigo-500/80";
            case "Mejoras":
                return "bg-emerald-500/80";
            case "Arreglos":
                return "bg-rose-500/80";
            default:
                return "bg-neutral-400";
            }
        },

        iconAccent() {
            switch (this.title) {
            case "Novedades":
                return "text-indigo-600 dark:text-indigo-400";
            case "Mejoras":
                return "text-emerald-600 dark:text-emerald-400";
            case "Arreglos":
                return "text-rose-600 dark:text-rose-400";
            default:
                return "text-neutral-600 dark:text-neutral-400";
            }
        }
    }
};
</script>
