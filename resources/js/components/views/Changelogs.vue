<template>
    <div>
        <TheTopbar :title="'TODAS LAS VERSIONES'" />

        <div class="bg-gray-50 min-h-screen py-8">
            <div v-if="changelogs.length === 0 && !isLoading" class="flex justify-center pt-10 font-semibold uppercase text-gray-400 tracking-wide">
                No hay changelogs disponibles
            </div>

            <LoadingSpinner v-show="isLoading" />

            <Section>
                <div class="max-w-4xl mx-auto space-y-6">
                    <ul v-show="!isLoading" class="space-y-6">
                        <li v-for="log in changelogs"
                            :key="log.id"
                            class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100"
                        >
                            <!-- Header versión -->
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-semibold tracking-wide">
                                        v{{ log.version }}
                                    </div>
                                </div>

                                <span class="text-sm text-gray-400">
                                {{ formatDate(log.created_at) }}
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="space-y-5">
                                <!-- News -->
                                <div v-if="log.news" class="flex gap-3">
                                    <IconAutoAwesoneFilled class="size-5 mt-1 text-indigo-500" />
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800 mb-1">
                                            Novedades
                                        </h3>
                                        <div class="text-sm text-gray-600 leading-relaxed" v-html="log.news" />
                                    </div>
                                </div>

                                <!-- Improvements -->
                                <div v-if="log.improvements" class="flex gap-3">
                                    <IconSettings class="size-5 mt-1 text-emerald-500" />
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800 mb-1">
                                            Mejoras
                                        </h3>
                                        <div class="text-sm text-gray-600 leading-relaxed" v-html="log.improvements" />
                                    </div>
                                </div>

                                <!-- Bugs -->
                                <div v-if="log.bugfixes" class="flex gap-3">
                                    <IconBug class="size-5 mt-1 text-rose-500" />
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800 mb-1">
                                            Correcciones
                                        </h3>
                                        <div class="text-sm text-gray-600 leading-relaxed" v-html="log.bugfixes" />
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </Section>
        </div>
    </div>
</template>

<script>
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default {
    name: "Changelog",
    mixins: [AdminMixin],
    data() {
        return {
            changelogs: [],
            isLoading: true,
        };
    },

    async mounted() {
        await this.fetchChangelogData();
    },

    methods: {
        async fetchChangelogData() {
            try {
                const response = await fetch('/api/changelogs');
                if (!response.ok) {
                throw new Error('Error en la solicitud');
                }
                const data = await response.json();
                this.changelogs = Array.isArray(data) ? data : [];
            } catch (error) {
                console.error('Error fetching changelog data:', error);
                this.changelogs = [];
            } finally {
                this.isLoading = false;
            }
        },

        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }, options);
        }
    }
}
</script>

<style>
.red-icon {
  fill: #8a2c2c;
}
</style>
