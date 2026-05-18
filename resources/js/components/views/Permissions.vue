<template>

    <div>
        <TheTopbar :title="`Permisos`" />

        <Section>
            <FilterControls
                :addTarget="false"
                :filterOptions="[
                    'stringSearch',
                    'actions',
                    'role',
                    !$can('permissions', 'create') ? 'noAddButton' : ''
                ]"
                @click:add-button="loadData"
                @update:filters="updateFilters"
                :availableActions="actionOptions"
                :availableRoles="rolesOptions"
                permissionSection="permissions"
            />

            <LoadingSpinner v-show="isLoading" />

            <div class="flex flex-col gap-5" v-show="!isLoading">
                <DatatableComponent
                    v-if="!isLoading && user"
                    :data="data"
                    :columns="tableHeaders"
                    :actions="datatableActions"
                    :totalRecords="pagination.total"
                    slug="api/permissions/filter"
                    propId="id"
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
                :title="!auxItem.entity
                    ? `Crear Permiso`.toUpperCase()
                    : `Actualizar Permiso`.toUpperCase()"
                @close="isModalOpen = false"
            >
                <template #body>
                    <Form :validation-schema="validationRules" class="grid grid-cols-2 gap-4" ref="form">
                        <InputWrapper :label="`Secciones`" required class="col-span-2 md:col-span-1">
                            <InputText
                                id="Sección"
                                name="entity"
                                type="text"
                                v-model="newItem.entity"
                                :rules="validationRules.entity"
                            />
                        </InputWrapper>

                        <InputWrapper :label="`Rol`" required class="col-span-2 md:col-span-1">
                            <NewMultiselect
                                :modelValue="newItem.level"
                                :options="rolesOptions.map(r => ({ ...r, id: r.level }))"
                                required
                                errorMessage="Rol es obligatorio"
                                :multiple="false"
                                :disabled="$can('permissions', 'view', { gte: 10 })"
                                placeholder="Seleccione una opcion"
                                @update:modelValue="val => {
                                    newItem.level = val;
                                }"
                                @open="changeBackground(rolesOptions, 'level')"
                                @search="changeBackground(rolesOptions, 'level', $event)"
                            />
                        </InputWrapper>

                        <InputWrapper :label="`Acción`" required class="col-span-2 md:col-span-1">
                            <NewMultiselect
                                :modelValue="actionsArray.map(a => a.id)"
                                :options="actionOptions"
                                required
                                errorMessage="El campo de acciones es obligatorio"
                                :multiple="true"
                                :disabled="$can('permissions', 'view', { gte: 10 })"
                                placeholder="Seleccione una opcion"
                                @update:modelValue="val => {
                                    actionsArray = actionOptions.filter(a => val.includes(a.id));
                                }"
                                @open="changeBackground(actionOptions, 'name')"
                                @search="changeBackground(actionOptions, 'name', $event)"
                            />
                        </InputWrapper>

                    </Form>
                </template>

                <template #action>
                    <span class="flex w-full rounded-md shadow-sm sm:w-auto" v-if="auxItem.entity && $can('permissions', 'edit') || !auxItem.entity && $can('permissions', 'create')">
                        <ButtonAction
                            type="button"
                            size="sm"
                            color="green"
                            @click="submitPermissions"
                        >{{ auxItem.entity == undefined ? `Añadir` : `Actualizar` }}</ButtonAction>
                    </span>
                </template>
            </TheModal>

            <TheModal
                :open="isDeleteModalOpen"
                :title="`Borrar Permiso`.toUpperCase()"
                destructive
                @close="isDeleteModalOpen = false"
            >
                <template #body>
                    <p class="pr-8 leading-7 tracking-wide text-gray-800">
                        SE VA A ELIMINAR ESTE PERMISO
                        <br>Esta acción no se podrá deshacer.
                    </p>
                </template>

                <template #action>
                    <span class="flex w-full rounded-md shadow-sm sm:w-auto" v-if="$can('permissions', 'delete')">
                        <ButtonAction
                            type="button"
                            size="sm"
                            color="red"
                            @click="deletePermission"
                        >Borrar</ButtonAction>
                    </span>
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
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css';
export default defineComponent({
    components: {
        Form,
        ErrorMessage,
        VueMultiselect,
        Field
    },

    mixins:[AdminMixin],

    data(){
        return {
            newItem: {},
            auxItem: {},
            data: [],
            allData: [],
            tableData: [],
            tableHeaders: [],
            keepPagination: true,
            tableNameForPagination: 'permissions',
            sortProps: {},
            storedPagination: [],
            filters: {},
            pagination: [],
            isDeleteModalOpen: false,
            isEmpty: false,
            validationRules: {
            },
            actionOptions: [],
            rolesOptions: [],
            storagePreference: "permissions_pagination", // The variable used for pagination.
            filterPreference: `filters_${this.$route.name}`, // The variable used for filters.

            nameStorageObj: 'permissionsStorageObj',
            storageObj: {
                page: 1,
                per_page: 10,
                show_all: false,
            },
            dataRole: [],
            actionsArray: [],
        };
    },

    computed: {
        validationSchema() {
            return yup.object().shape({
                entity: this.validationRules.entity,
            });
        },

        arrayActions() {
            const array = [];
            if (this.$can('permissions', 'delete')) array.push({ value: 'delete', label: 'Borrar' });

            return array;
        },

        datatableActions() {
            const array = [];
            if (this.$can('permissions', 'delete')) array.push('delete');

            return array;
        },

        debouncedFunction() {
            return this.debounce(this.filterPermissionsTable(true, this.sortProps), 200);
        },
    },

    async mounted(){
        if (!this.$can('permissions', 'view')) {
            this.openAlert("error", "Permiso denegado");
            return this.$router.push({ name: "home" });
        }
        this.loadValidationTexts();
        this.loadTableHeaders();
        this.retrieveActionOptions();
        this.filterPermissionsTable(false, this.sortProps);

        const storedDataString = sessionStorage.getItem(`${this.nameStorageObj}`);
        if (storedDataString) {
            try {
                const storedDataObj = JSON.parse(storedDataString);
                this.storageObj = { ...this.storageObj, ...storedDataObj };

            } catch (error) {
                console.error('Error parsing sessionStorage storageObj:', error);
            }
        } else {
            this.storageObj = {
                page: 1,
                per_page: 10,
                show_all: false,
            }
            this.$emit("update:pagination", this.storageObj);
        }
    },

    methods:{
        loadValidationTexts(){
            this.validationRules = {
                entity: yup.string().required(`El campo sección es obligatorio`),
            }
        },
        loadTableHeaders() {
            this.tableHeaders = [
                { prop: 'entity', name: 'Sección', sortable: true },
                { prop: 'roleName', name: 'Rol', sortable: true },
                { prop: 'actions', name: 'Acción', type: 'simplePill' },
                { prop: 'actionColumn', name: 'Acciones', size: 100 },
            ];
        },

        async retrieveActionOptions() {
            const result = await this.$axios.get('api/retrievePermissionsData');
            this.allData = result.data;
            this.actionOptions = this.allData['action_types'];
            this.rolesOptions = this.allData['roles'];
        },

        updateFilters(filters) {
            this.filters = { ...filters };
            this.filterPermissionsTable(true, this.sortProps);
        },

        loadData (item = false) {
            this.isEditing = false;
            this.auxItem = item;
            this.actionsArray = [];
            const creating = !item;
            const editing = item;
            const denied =
                (creating && !this.$can('permissions', 'create')) ||
                (editing && !this.$can('permissions', 'edit'));

            if (denied) {
                this.openAlert("error", "Permiso denegado");
                this.isModalOpen = false;
            }

            if (item) {
                item.old_entity = null;
                this.isEditing = true;
                if (item.actions) {
                    this.actionsArray = this.actionOptions.filter(opt => item.actions.some(entry => entry.name === opt.name));
                }

                if (item.entity) {
                    item.old_entity = item.entity;
                }
                this.openEditModal(item);

            } else {
                this.newItem = {...this.newItem};
                this.openCreateModal();
            }
        },

        currentPagination(id) {
            const fallback = { page: 1, per_page: 10 };
            const finded = this.storedPagination.find(f => f.name == `${this.tableNameForPagination}_${id}`);

            return finded?.pagination || fallback;
        },

        async handleSortChange(params) {
            this.sortProps = params;
            await this.filterPermissionsTable(true, this.sortProps);
        },

        async handlePagination(data) {
            if (data) {
                this.storedPagination = data;
                await this.filterPermissionsTable(true, this.sortProps);
                this.$emit("update:pagination", data);
            }
        },

        async filterPermissionsTable(isInternalLoading = false, sortProps = {}) {
            try {
                let params = { ...this.filters, ...sortProps };

                if (this.keepPagination) this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [];

                const pagination = this.currentPagination(1);
                params.page = pagination.page;
                params.per_page = pagination.per_page;
                params.pagination = this.storedPagination;

                await this.fetchPaginatedData('api/permissions/filter', params, false, isInternalLoading);
            } catch (error) {
                if (error.response.data.error == 'Permission Denied') {
                    this.openAlert("error", "Permiso denegado");
                    return this.$router.push({ name: "home" });
                } else {
                    console.log(error);
                    this.openAlert("error", error.message || "Ocurrió un error.");
                }
            } finally {
                this.loadingComponent = false;
                this.isLoading = false;
            }
        },

        async submitPermissions(){
            // validation of select
            const result = await this.$refs.form.validate();

            // If validation fails, return to avoid proceeding
            if (await this.validateForm(result)) return;

            if (result.valid){
                try {
                    this.isEditing = this.auxItem.entity;

                    const slug = !this.isEditing
                        ? { method: 'post', url: '/api/permissions/create' }
                        : { method: 'put', url: `/api/permissions/${this.newItem.entity}` };

                    this.newItem.actionsArray = this.actionsArray;
                    if (this.newItem.level) {
                        this.newItem.level = this.newItem.level;
                    }

                    if (!this.isEditing && this.newItem.entity) {
                        this.newItem.old_entity = this.newItem.entity;
                    }
                    const permissionsResponse = await this.$axios[slug.method](slug.url, this.newItem);

                    let message;

                    if (this.isEditing) {
                        message = "Registro actualizado con éxito.";
                    } else {
                        message = "Registro creado con éxito.";
                    }
                    this.openAlert("success", message, null);
                    this.isModalOpen = false;
                    await this.filterPermissionsTable(false, this.sortProps);

                } catch (error) {
                    if (error.response.data.error == 'Permission Denied') {
                        this.openAlert("error", "Permiso denegado");
                        return this.$router.push({ name: "home" });
                    } else {
                        this.isModalOpen = false;
                        console.log(error);
                        this.openAlert("error", error.message || "Ocurrió un error.");
                    }
                }
            }

        },

        async validateForm(result) {
            // Convert result.errors into an array for easier manipulation
            const errors = Object.entries(result.errors);

            if (errors.length || !this.actionsArray.length || this.newItem.level === null || this.newItem.level === undefined){
                let resultText = [
                    !this.actionsArray.length && `Acciones es obligatorio`,
                    (this.newItem.level === null || this.newItem.level === undefined) && `Rol es obligatorio`,
                    ...errors.map(([_, msg]) => msg), // Include all errors from form validation
                ].filter(Boolean).join('<br />'); // Remove empty messages and join with line breaks

                this.openAlert("info", "Rellene correctamente el formulario", resultText);

                return true;
            }

            // Return false if no validation errors found
            return false;
        },

        openDeleteModal(item){
            this.auxItem = item;
            this.openRemoveModal(item);
        },

        async deletePermission(){
            try {
                await this.$axios
                .delete(`api/permissions`, {
                    params: {
                        entity: this.auxItem.entity,
                        level: this.auxItem.level,
                    }
                })
                .then(() => {
                    this.isDeleteModalOpen = false;
                    this.openAlert("success", "Registro eliminado con éxito.", null);
                    this.filterPermissionsTable(false, this.sortProps);
                });
            } catch (error) {
                if (error.response.data.error == 'Permission Denied') {
                    this.isDeleteModalOpen = false;
                    this.openAlert("error", "Permiso denegado");
                } else {
                    console.log(error);
                    this.isDeleteModalOpen = false;
                    this.openAlert("error", error.message || "Ocurrió un error.");
                }
            }
        },

        handleStoredFilters() {
            this.filters = sessionStorage.getItem(this.filterPreference) ? JSON.parse(sessionStorage.getItem(this.filterPreference)) : {...this.filters};

            [
                'roleObject',
                'languageObject',
                'usersObject',
                'actionsObject',
                'statusObject',
                'categoryObject',
                'incidentsObject',
                'cityHallObject',
                'statusObject'
            ].forEach(key => {
                delete this.filters[key];
            });
        },
    }
})
</script>
