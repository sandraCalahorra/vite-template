<template>
  <div>
    <TheTopbar :title="sectionTitle" />

    <Section>
        <FilterControls
            addTarget="announcementsCreate"
            section="ANUNCIOS"
            :filterOptions="[
                'stringSearch',
            ]"
            @update:filters="updateFilters"
        />

        <LoadingSpinner v-show="isLoading" />
        <div class="flex flex-col gap-5" v-show="!isLoading">
            <DatatableComponent
                v-if="!isLoading && user"
                :data="data"
                :columns="tableHeaders"
                :actions="datatableActions"
                :totalRecords="pagination.total"
                slug="api/announcements/filter"
                propId="id"
                :filterProps="filters"
                :keepPagination="keepPagination"
                :tableNameForPagination="tableNameForPagination"
                @on-delete-click="openRemoveModal"
                @on-page-change="handlePagination"
                @on-sort-change="handleSortChange"
                @on-row-click="navigateToAnnouncementsForm"
                :page="currentPagination(1)['page']"
                :perPage="currentPagination(1)['per_page']"
            />
        </div>

        <TheModal :open="isDeleteModalOpen" title="Borrar Anuncio" destructive @close="isDeleteModalOpen = false">
            <template #body>
                <p class="pr-8 text-gray-800 tracking-wide leading-7">
                    SE VA A ELIMINAR ESTE ANUNCIO
                    <br>Esta acción no se podrá deshacer.
                </p>
            </template>
            <template #action>
                <span class="flex w-full rounded-md shadow-sm sm:w-auto">
                    <button
                        type="button"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-700 hover:bg-red-600 text-base font-medium text-white shadow-sm focus:outline-none focus:border-blue-300 focus:shadow-outline"
                        @click="deleteAnnouncement"
                    >Borrar</button>
                </span>
            </template>
        </TheModal>
    </Section>
  </div>
</template>

<script>
import { ref, defineComponent } from 'vue';
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default defineComponent({
    mixins: [AdminMixin],
    data() {
        return {
            selectedStatus: null,
            data: [],
            tableHeaders: [],
            filters: {},
            pagination: {},
            isDeleteModalOpen: false,
            isLoading: false,
            tableNameForPagination: 'anuncios',
            keepPagination: false,
            sortProps: {},
            storedPagination: [],
            selectedAnnouncement: null,
        };
    },
    async mounted() {
        this.fillTableHeaders();
        this.isLoading = false;

        await this.filterTable();
    },
    computed: {
        sectionTitle() {
            return this.pagination.total ? `<div class="flex flex-row gap-2 items-baseline">Anuncios <span class="text-sm font-normal">${this.pagination.total.toLocaleString('es-ES')}</span></div>` : 'Anuncios';
        },

        debouncedFunction() {
            return this.debounce(this.filterTable, 200);
        },

        datatableActions() {
            const array = [];

            if (this.user?.level <= 20) {
                array.push('delete');
            }

            return array;
        },
    },
    methods: {

        fillTableHeaders() {
            this.tableHeaders = [
                { prop: 'title', name: 'Título', sortable: true },
                { prop: 'content', name: 'Contenido', sortable: true },
                { prop: 'is_active', name: 'Estado', sortable: true, type: 'boolean' },
                { prop: 'actionColumn', name: 'Acciones', size: 100 },
            ];
        },

        updateFilters(filters) {
            this.filters = { ...filters };
            this.filterTable(true, this.sortProps);
        },

        async filterTable(isInternalLoading = false, sortProps = {}) {
            this.isLoading = true;
            let params = { ...this.filters, ...sortProps };

            if (this.keepPagination) this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [];
            params.pagination = this.storedPagination;

            const response = await this.$axios.get('api/announcements/filter', { params });
            this.data = response.data.data;
            this.pagination = response.data;
            this.isLoading = false;
        },

        currentPagination(id) {
            const fallback = { page: 1, per_page: 10 };
            const finded = (this.storedPagination || []).find(f => f.name == `${this.tableNameForPagination}_${id}`);

            return finded?.pagination || fallback;
        },

        async handlePagination(data) {
            if (data) {
                await this.filterTable(true, this.sortProps);
                this.$emit("update:pagination", data);
            }
        },

        async handleSortChange(params) {
            this.sortProps = params;
            await this.filterTable(true, this.sortProps);
        },

        openRemoveModal(row) {
            this.selectedAnnouncement = row;
            this.isDeleteModalOpen = true;
        },

        async deleteAnnouncement() {
            if (this.selectedAnnouncement) {
                await this.$axios.delete('api/announcements', { data: { id: this.selectedAnnouncement.id } });
                this.isDeleteModalOpen = false;
                this.filterTable();
                this.openAlert('success', 'Anuncio borrado correctamente');
            }
        },

        navigateToAnnouncementsForm(row) {
            if (row && row.id) {
                this.$router.push({ name: 'announcementsForm', params: { id: row.id } });
            }
        },

    },

    beforeRouteLeave(to, from, next) {
        this.beforeRouteLeaving(to, from, next, this.tableNameForPagination, this.$route.path);
    },


});
</script>
