<template>
    <div>
        <TheTopbar :title="'NOTAS DE VERSIÓN'" />

        <div class="min-h-screen bg-[#f8fafc]">
            <LoadingSpinner v-show="isLoading" />

            <Section v-show="!isLoading">
                <div class="w-full">

                    <div
                        v-if="orderedChangelogs.length === 0"
                        class="flex justify-center pt-8 text-sm font-semibold uppercase text-slate-400"
                    >
                        No hay changelogs disponibles
                    </div>

                    <div v-else class="relative mt-2 w-full pl-5 sm:pl-6">
                        <div class="absolute bottom-0 left-[0.56rem] top-2 w-px bg-primary sm:left-[0.62rem]"></div>

                        <div class="space-y-8">
                            <article
                                v-for="log in orderedChangelogs"
                                :key="log.id"
                                class="relative grid gap-3 sm:grid-cols-[8.75rem,minmax(0,1fr)] sm:gap-4 lg:grid-cols-[10rem,minmax(0,1fr)] lg:gap-6"
                            >
                                <div class="relative">
                                    <div class="absolute left-[-1.03rem] top-1.5 h-3 w-3 rounded-full border-2 border-primary bg-white sm:left-[-1.2rem]"></div>

                                    <div class="sm:pt-0.5">
                                        <p class="text-base font-bold text-slate-800">
                                            {{ formatDate(log.created_at) }}
                                        </p>
                                        <p class="mt-2 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500">
                                            v{{ log.version }}
                                        </p>
                                    </div>
                                </div>

                                <div class="divide-y divide-slate-100 rounded-lg border border-slate-200 bg-white px-4 py-1 shadow-sm">
                                    <div
                                        v-for="entry in entriesFor(log)"
                                        :key="`${log.id}-${entry.key}`"
                                        class="grid gap-2 py-3 sm:grid-cols-[7rem,minmax(0,1fr)] sm:items-start sm:gap-4"
                                    >
                                        <div
                                            class="inline-flex w-fit shrink-0 items-center rounded-full px-3 py-1 text-xs font-bold uppercase"
                                            :class="entry.badgeClass"
                                        >
                                            {{ entry.title }}
                                        </div>

                                        <div
                                            class="prose prose-sm max-w-none leading-relaxed text-slate-600"
                                            v-html="entry.content"
                                        />
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
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

    computed: {
        orderedChangelogs() {
            return [...this.changelogs].sort((left, right) => {
                return new Date(right.created_at) - new Date(left.created_at);
            });
        },
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
            return new Date(dateString).toLocaleDateString('es-ES', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        },

        entriesFor(log) {
            return [
                {
                    key: 'news',
                    title: 'Novedades',
                    content: log?.news || '',
                    badgeClass: 'bg-indigo-100 text-indigo-700'
                },
                {
                    key: 'improvements',
                    title: 'Mejoras',
                    content: log?.improvements || '',
                    badgeClass: 'bg-emerald-100 text-emerald-700'
                },
                {
                    key: 'bugfixes',
                    title: 'Arreglos',
                    content: log?.bugfixes || '',
                    badgeClass: 'bg-rose-100 text-rose-700'
                }
            ].filter((entry) => Boolean(entry.content));
        }
    }
}
</script>
