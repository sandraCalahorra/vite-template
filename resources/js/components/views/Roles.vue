<template>

    <div>
    <TheTopbar :title="sectionTitle" />

    <Section>
        <FilterControls
            :addTarget="false"
            :filterOptions="[
                'stringSearch',
                'noFilters'
            ]"
            @click:add-button="loadData"
            @update:filters="updateFilters"
            :storagePreference="storageObj"
            permissionSection="roles"
        />

        <LoadingSpinner v-show="isLoading" />

        <div class="flex flex-col gap-5" v-show="!isLoading">
            <DatatableComponent
                v-if="!isLoading"
                :data="data"
                :columns="tableHeaders"
                :actions="datatableActions"
                :totalRecords="pagination.total"
                slug="api/roles/filter"
                propId="level"
                :filterProps="filters"
                :keepPagination="keepPagination"
                :tableNameForPagination="tableNameForPagination"
                @on-row-click="loadData"
                @on-delete-click="openDeleteModal"
                @on-page-change="handlePagination"
                @on-sort-change="handleSortChange"
                :page="currentPagination(1)['page']"
                :perPage="currentPagination(1)['per_page']"
            />
        </div>
        <TheModal
            :open="isModalOpen"
            :title="isEditing ? 'ACTUALIZAR ROL' : 'CREAR ROL'"
            @close="isModalOpen = false"
        >
            <template #body>
                <Form :validation-schema="validationRules" class="grid grid-cols-2 gap-4" ref="form" @submit="submitRoles" @keyup.enter="submitRoles">
                    <InputWrapper label="Nombre" required class="col-span-2 md:col-span-1">
                        <InputText
                            name="name"
                            type="text"
                            v-model="newItem.name"
                            :rules="validationRules.name"
                        />
                    </InputWrapper>
                    <InputWrapper label="Nivel de acceso (único)" required class="col-span-2 md:col-span-1">
                        <InputText
                            name="level"
                            type="text"
                            v-model="newItem.level"
                            :rules="validationRules.level"
                        />
                    </InputWrapper>
                </Form>
            </template>

            <template #action>
                <ButtonAction
                    :label="newItem.id == undefined ? 'Añadir' : 'Guardar cambios'"
                    color="green"
                    size="sm"
                    @click="submitRoles"
                />
            </template>
        </TheModal>

        <TheModal :open="isDeleteModalOpen" title="Borrar Rol" @close="isDeleteModalOpen = false">
            <template #body>
                <p class="pr-8 leading-7 tracking-wide text-gray-800">
                    SE VA A ELIMINAR ESTE ROL
                    <br>Esta acción no se podrá deshacer.
                </p>
            </template>
            <template #action>
                <ButtonAction
                    :label="'Borrar'"
                    color="red"
                    size="sm"
                    @click="deleteRole"
                />
            </template>
        </TheModal>
    </Section>
    </div>
</template>

<script>
    import { defineComponent } from 'vue';
    import { Form, Field, ErrorMessage } from 'vee-validate';
    import AdminMixin from "@/components/mixins/AdminMixin.vue";
    import * as yup from 'yup';

    /* MultiSelect */
    import 'vue-multiselect/dist/vue-multiselect.css';

    export default defineComponent({
        components: {
            Form,
            ErrorMessage,
            Field
        },

        mixins:[AdminMixin],

        data(){
            return {
                newItem: {},
                auxItem: {},
                data: [],
                tableData: [],
                // tableHeaders: {
                //     name: {
                //         name: 'Nombre',
                //         openModal: true,
                //     },
                //     level: {
                //         name: 'Nivel',
                //         openModal: true,
                //     },
                // },
                filters: {},
                pagination: {},
                isDeleteModalOpen: false,
                dataRole: [],
                validationRules: {
                    name: yup.string()
                        .trim()
                        .required("El Nombre es obligatorio")
                        .matches(/^[a-zñA-ZÑ\s]+$/, "El nombre solo puede contener letras y espacios.")
                        .test('no-slash', 'El nombre no puede contener el carácter "/"', value => !value || !value.includes('/')),
                    level: yup.string()
                        .required("El nivel de acceso es obligatorio")
                        .matches(/^\d+$/, 'El nivel de acceso solo puede contener numeros.')
                        .test(
                            'unique-level',
                            'El Nivel de acceso ya existe',
                            this.checkUniqueLevel
                        ),
                },
                nameStorageObj: 'roleStorageObj',
                storageObj: {
                    page: 1,
                    per_page: 10,
                    show_all: false,
                },
                tableHeaders: [],
                keepPagination: false,
                tableNameForPagination: 'roles',
                sortProps: {},
                storedPagination: [],
            }
        },

        computed: {
            sectionTitle() {
                return this.pagination.total ? `<div class="flex flex-row gap-2 items-baseline">Roles <span class="text-sm font-normal">${this.pagination.total.toLocaleString('es-ES')}</span></div>` : 'Roles';
            },
            validationSchema() {
                return yup.object().shape({
                    name: this.validationRules.name,
                    level: this.validationRules.level,
                });
            },
            datatableActions() {
                const array = [];

                if (this.user?.level < 10) {
                    array.push('delete');
                }

                return array;
            },
        },

        async mounted(){
            this.fillTableHeaders();
            this.filterRolesTable(false, this.sortProps);
        },

        methods:{
            fillTableHeaders() {
                this.tableHeaders = [
                    { prop: 'name', name: 'Nombre', sortable: true },
                    { prop: 'level', name: 'Nivel', sortable: true },
                    { prop: 'actionColumn', name: 'Acciones', size: 100 },
                ];
            },
            checkUniqueLevel(value) {
                if (!value) return false;
                if (this.dataRole.includes(value) && value != this.auxItem.level) {
                    return false;
                }
                return true;
            },

            updateFilters(filters) {
                this.filters = { ...filters };
                this.filterRolesTable(true, this.sortProps);
            },

            currentPagination(id) {
                const fallback = { page: 1, per_page: 10 };
                const finded = this.storedPagination.find(f => f.name == `${this.tableNameForPagination}_${id}`);

                return finded?.pagination || fallback;
            },

            async handleSortChange(params) {
                this.sortProps = params;
                await this.filterRolesTable(true, this.sortProps);
            },

            paginateRolesTable(oldPage, newPage) {
                this.paginateTable(oldPage, newPage, 'api/roles/filter');
            },

           loadData (item = false) {
                this.isEditing=false;
                if (item) {
                    this.isEditing=true;
                    this.auxItem = item;
                    this.openEditModal(item);
                } else {
                    this.auxItem = {}
                    this.newItem={...this.newItem}
                    this.openCreateModal();
                }
            },

            async filterRolesTable(isInternalLoading = false, sortProps = {}){
                let params = { ...this.filters, ...sortProps };

                if (this.keepPagination) this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [];

                const pagination = this.currentPagination(1);
                params.page = pagination.page;
                params.per_page = pagination.per_page;
                params.pagination = this.storedPagination;

                await this.fetchPaginatedData('api/roles/filter', params, false, isInternalLoading);
                const array = await this.fetchAllData('api/roles');
                const result = Array.isArray(array) ? array.map(m => m.level) : [];
                this.dataRole = result;
            },

            async submitRoles(){
                // validation of select
                const result = await this.$refs.form.validate();
                // If validation fails, return to avoid proceeding
                if (await this.validateForm(result)) return;

                this.isEditing = this.auxItem.level;

                if (this.auxItem.level != this.newItem.level || this.auxItem.name != this.newItem.name) {
                    if (!this.auxItem.level) {
                        this.auxItem.level = this.newItem.level;
                    }
                    const slug = !this.isEditing
                        ? { method: 'post', url: '/api/roles/create' }
                        : { method: 'put', url: `/api/roles/${this.auxItem.level}` };

                    await this.$axios[slug.method](slug.url, this.newItem);
                    this.isModalOpen = false;
                    await this.filterRolesTable(true, this.sortProps);
                } else {
                    this.isModalOpen = false;
                }
            },

            openDeleteModal(item){
                this.auxItem = item;
                this.openRemoveModal(item);
            },

            async deleteRole(){
                this.loadingComponent = true;
                this.$axios
                .delete(`api/roles`, {
                    params: {
                        id: this.auxItem.level
                    }
                })
                .then(() => {
                    this.isDeleteModalOpen = false;
                    this.openAlert("success", "Registro eliminado con éxito.", null);
                    this.filterRolesTable(true, this.sortProps);
                });
            },

            async validateForm(result) {
                // Convert result.errors into an array for easier manipulation
                const errors = Object.entries(result.errors);

                if (errors.length){
                    let resultText = [
                        ...errors.map(([_, msg]) => msg) // Include all errors from form validation
                    ].filter(Boolean).join('<br />'); // Remove empty messages and join with line breaks

                    this.openAlert("info", "Rellene correctamente el formulario", resultText);

                    return true;
                }
                // Return false if no validation errors found
                return false;
            },

            async handlePagination(data) {
                if (data) {
                    this.storedPagination = data;
                    await this.filterRolesTable(true, this.sortProps);
                    this.$emit("update:pagination", data);
                }
            },

        },

        beforeRouteLeave(to, from, next) {
            this.beforeRouteLeaving(to, from, next, this.storagePreference, this.$route.path);
        },
    })
</script>

<style>
</style>
