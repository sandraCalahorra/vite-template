<template>

    <div>
    <TheTopbar :title="sectionTitle" />

    <Section>

        <FilterControls
            :addTarget="false"
            section="USUARIO"
            :filterOptions="[
                'stringSearch',
                'role',
            ]"
            @click:add-button="loadData"
            :availableRoles="dataRole"
            @update:filters="updateFilters"
        >
            <template #extra-actions>
                <ButtonAction
                    v-if="selectedUsers.length > 0"
                    label="Borrar seleccionados"
                    color="red"
                    size="sm"
                    class="ml-2"
                    @click="isBulkDeleteModalOpen = true"
                />
            </template>
        </FilterControls>

        <LoadingSpinner v-show="isLoading" />

        <div class="flex flex-col gap-5" v-show="!isLoading">

            <DatatableComponent
                v-if="!isLoading"
                :data="data"
                :columns="tableHeaders"
                :actions="datatableActions"
                :totalRecords="pagination.total"
                slug="api/users/filter"
                propId="id"
                :filterProps="filters"
                :keepPagination="keepPagination"
                :tableNameForPagination="tableNameForPagination"
                @on-row-click="loadData"
                @on-delete-click="openRemoveModal"
                @on-page-change="handlePagination"
                @on-sort-change="handleSortChange"
                @on-selection-change="handleSelectionChange"
                :page="currentPagination(1)['page']"
                :perPage="currentPagination(1)['per_page']"
            />
        </div>

        <TheModal
            :open="isModalOpen"
            :title="newItem.id == undefined ? 'CREAR USUARIO' : `ACTUALIZAR USUARIO (ID ${newItem.id})`"
            @close="isModalOpen = false"
        >
            <template #body>
                <Form :validation-schema="validationSchema" class="grid grid-cols-2 gap-x-4 gap-y-3" ref="form" @submit="submitUsers" @keyup.enter="submitUsers">
                    <InputWrapper label="Nombre" required class="col-span-2 md:col-span-1">
                        <InputText
                            name="name"
                            type="text"
                            v-model="newItem.name"
                            :rules="validationRules.name"
                            :disabled="user?.level >= 20"
                        />
                    </InputWrapper>
                    <InputWrapper label="Correo electrónico" required class="col-span-2 md:col-span-1">
                        <InputText
                            name="email"
                            type="email"
                            v-model="newItem.email"
                            :rules="validationRules.email"
                            :disabled="user?.level >= 20"
                        />
                    </InputWrapper>
                    <InputWrapper label="Rol" required class="col-span-2 md:col-span-1">
                        <NewMultiselect
                            v-model="newItem.level"
                            :options="mappedRoles"
                            :multiple="false"
                            placeholder="Seleccione una opción"
                            :disabled="user?.level >= 20"
                        />
                    </InputWrapper>
                    <InputWrapper label="Contraseña" class="col-span-2 md:col-span-1" :info="newItem.id ? infoPassword : ''" :required="isEditing ? false : true">

                        <span class="flex w-full gap-2">
                            <InputText
                                class="w-full"
                                name="password"
                                :type="!isEnabled ? 'password' : 'text'"
                                v-model="newItem.password"
                                :rules="validationRules.password"
                                :disabled="user?.level >= 20"
                                :placeholder="'**********'"
                            />
                            <span class="flex justify-end mt-1">
                                <IconEye class="cursor-pointer" v-if="!isEnabled" @click="isEnabled=!isEnabled" />
                                <IconEyeSlash class="cursor-pointer" v-else @click="isEnabled=!isEnabled" />
                            </span>
                        </span>
                    </InputWrapper>
                </Form>
            </template>

            <template #action>
                <span class="flex w-full rounded-md shadow-sm sm:w-auto">
                <ButtonAction
                    :label="newItem.id == undefined ? 'Añadir' : 'Actualizar'"
                    color="green"
                    size="sm"
                    @click="submitUsers"
                />
                </span>
            </template>
        </TheModal>

        <TheModal :open="isDeleteModalOpen" title="Borrar Usuario" @close="isDeleteModalOpen = false">
            <template #body>
                <p class="pr-8 text-gray-800 tracking-wide leading-7">
                    SE VA A ELIMINAR ESTE USUARIO
                    <br>Esta acción no se podrá deshacer.
                </p>
            </template>

            <template #action>
                <span class="flex w-full rounded-md shadow-sm sm:w-auto">
                    <ButtonAction
                        label="Borrar"
                        color="red"
                        size="sm"
                        @click="deleteUser"
                    />
                </span>
            </template>
        </TheModal>

        <TheModal :open="isBulkDeleteModalOpen" title="Borrar Usuarios Seleccionados" @close="isBulkDeleteModalOpen = false">
            <template #body>
                <p class="pr-8 text-gray-800 tracking-wide leading-7">
                    SE VAN A ELIMINAR <strong>{{ selectedUsers.length }}</strong> USUARIOS.
                    <br>Esta acción no se podrá deshacer.
                </p>
            </template>

            <template #action>
                <span class="flex w-full rounded-md shadow-sm sm:w-auto">
                    <ButtonAction
                        label="Borrar todo"
                        color="red"
                        size="sm"
                        @click="deleteBulkUsers"
                    />
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

/* MultiSelect */
import * as yup from 'yup';

export default defineComponent({
    components: {
        Form,
        ErrorMessage,
        Field
    },

    mixins:[AdminMixin],

    setup() {
    },

    data(){
        return {
            newItem:{},
            data:[],
            tableHeaders: [],
            filters: {},
            validationRules: {
                name: yup.string().required('Nombre es obligatorio'),
                email: yup.string().email('Email inválido').required('Email es obligatorio'),
                password: !this.isEditing ? yup.string()
                .required('Contraseña es obligatorio')
                .min(4, 'Contraseña tiene que tener al menos 4 caracteres') : '',
            },
            pagination: {},
            isDeleteModalOpen: false,
            isBulkDeleteModalOpen: false,
            isEmpty: false,
            isEnabled: false,
            infoPassword: '',
            dataRole: [],
            users: [],
            selectedUsers: [],
            tableNameForPagination: 'usuarios',
            keepPagination: false,
            sortProps: {},
            storedPagination: [],
        }
    },

    async mounted(){
        this.fillTableHeaders();
        this.dataRole = await this.fetchAllGetData('api/roles', this.filters);
        this.filterUsersTable(false, this.sortProps);
    },

    computed: {
        sectionTitle() {
            return this.pagination.total ? `<div class="flex flex-row gap-2 items-baseline">Usuarios <span class="text-sm font-normal">${this.pagination.total.toLocaleString('es-ES')}</span></div>` : 'Usuarios';
        },

        mappedRoles() {
            return this.dataRole.map(role => ({
                id: role.level,
                name: role.name
            }));
        },

        validationSchema() {
            return yup.object().shape({
                name: this.validationRules.name,
                email: this.validationRules.email,
                password: !this.isEditing ? this.validationRules.password : '',
            });
        },

        debouncedFunction() {
            return this.debounce(this.filterUsersTable(false, this.sortProps), 200);
        },

        datatableActions() {
            const array = [];
                array.push('delete');


            return array;
        },
    },

    methods:{
        fillTableHeaders() {
            this.tableHeaders = [
                { type: 'selection', size: 50 },
                { prop: 'name', name: 'Nombre', sortable: true },
                { prop: 'role_name', name: 'Rol', sortable: true },
                { prop: 'email', name: 'Email', sortable: true },
                { prop: 'actionColumn', name: 'Acciones', size: 100 },
            ];
        },

        anyCheckedElements(array) {
            let aux = [];
            if(Array.isArray(array)){
                aux = array;
            } else {
                aux = Object.entries(array).map(([key, value]) => ( value ));
            }
            return aux?.filter(e => e.checked).length > 0;
        },

        updateFilters(filters) {
            this.filters = { ...filters };
            this.filterUsersTable(true, this.sortProps);
        },

        currentPagination(id) {
            const fallback = { page: 1, per_page: 10 };
            const finded = this.storedPagination.find(f => f.name == `${this.tableNameForPagination}_${id}`);

            return finded?.pagination || fallback;
        },

        async handlePagination(data) {
            if (data) {
                this.storedPagination = data;
                await this.filterUsersTable(true, this.sortProps);
                this.$emit("update:pagination", data);
            }
        },

        async handleSortChange(params) {
            this.sortProps = params;
            await this.filterUsersTable(true, this.sortProps);
        },

        loadData (item = false) {
            this.isEditing=false;
            if (item) {
                this.infoPassword = `
                    Este campo puede estar en blanco, si es el caso <strong> no se modificará </strong> la contraseña. <br>
                    En caso contrario, <strong> se actualizará </strong> por la introducida
                `;
                this.isEditing=true;

                this.openEditModal(item);
            } else {
                this.newItem={...this.newItem}
                this.openCreateModal();
            }
        },

        async filterUsersTable(isInternalLoading = false, sortProps = {}){
            try {
                let params = { ...this.filters, ...sortProps };

                if (this.keepPagination) this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [];

                const pagination = this.currentPagination(1);
                params.page = pagination.page;
                params.per_page = pagination.per_page;
                params.pagination = this.storedPagination;

                await this.fetchPaginatedData('api/users/filter', params, false, isInternalLoading);
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

        async submitUsers(){
            // validation of select
            const result = await this.$refs.form.validate();

            if (!result.valid) {
                // console.log(result, this.newItem);
                return this.openAlert("info", "Rellene correctamente el formulario");
            }
            if (!this.newItem.level) {
                return this.openAlert("info", "El campo Rol no puede estar vacío");
            }

            if (this.newItem.level.level) {
                this.newItem.level = this.newItem.level.level;
            }
            this.storeOrUpdateItem(`api/register`, `api/users/${this.newItem.id}`, "default");
        },

        deleteUser(){
            this.removeItem('api/users');
        },

        handleSelectionChange(val) {
            this.selectedUsers = val;
        },

        async deleteBulkUsers() {
            const ids = this.selectedUsers.map(u => u.id);
            this.isLoading = true;
            try {
                await this.$axios.delete('api/users', {
                    params: { ids }
                });
                this.openAlert("success", "Usuarios eliminados con éxito.");
                this.isBulkDeleteModalOpen = false;
                this.selectedUsers = [];
                await this.filterUsersTable();
            } catch (error) {
                this.showDevError(error);
            } finally {
                this.isLoading = false;
            }
        },

    },

    beforeRouteLeave(to, from, next) {
        this.beforeRouteLeaving(to, from, next, this.tableNameForPagination, this.$route.path);
    },
})
</script>
