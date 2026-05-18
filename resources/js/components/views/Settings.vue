<template>
    <div>
        <TheTopbar :title="`AJUSTES DE LA APLICACIÓN`" />
        <LoadingSpinner v-show="isLoading" />

        <Section v-show="!isLoading">

                <!-- Section: Announcements -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2 border-b border-gray-50 pb-2">
                        <div class="p-1 bg-primary/5 text-primary rounded-lg">
                            <IconAnnouncement class="w-5 h-5"/>
                        </div>
                        <h2 class="text-sm font-bold text-gray-700 tracking-wider uppercase">Gestión de Anuncios</h2>
                    </div>

                    <div class="flex flex-col gap-4">
                        <FilterControls
                            :addTarget="false"
                            section="ANUNCIOS"
                            :filterOptions="[
                                'noFilters',
                            ]"
                            @click:add-button="loadAnnouncementsData"
                            :isReadOnly="!!loadingAnnouncementsTable"
                            @update:filters="updateAnnouncementsFilters"
                            permissionSection="announcements"
                        />

                        <DatatableComponent
                            v-if="!isLoading && user"
                            :data="announcementsData"
                            :columns="announcementsTableHeaders"
                            :actions="datatableAnnouncementsActions"
                            :totalRecords="announcementsPagination.total"
                            slug="api/v2/announcements/filter"
                            propId="id"
                            :filterProps="announcementsFilters"
                            :keepPagination="keepAnnouncementsPagination"
                            :tableNameForPagination="tableNameForAnnouncementsPagination"
                            @on-row-click="loadAnnouncementsData"
                            @on-delete-click="openRemoveAnnouncementsModal"
                            @on-enable-item="(row) => deleteAnnouncements(row)"
                            @on-sort-change="handleAnnouncementsSortChange"
                            @on-data-update="(newData) => announcementsData = newData"
                            :page="Number(currentAnnouncementsPagination(1)['page'])"
                            :perPage="Number(currentAnnouncementsPagination(1)['per_page'])"
                        />
                    </div>
                </div>
                <TheModal
                    :open="isAnnouncementModalOpen"
                    :title="newItem.id == undefined ? 'CREAR ANUNCIO' : `ACTUALIZAR ANUNCIO`"
                    :titleColor="newItem?.is_active == 0 ? 'text-red-600' : ''"
                    :large="true"
                    @close="isAnnouncementModalOpen = false"
                >
                    <template #body>
                        <Form :validation-schema="announcementValidationSchema" class="grid grid-cols-2 gap-x-4 gap-y-3" ref="form">
                            <InputWrapper label="Título" required class="col-span-2">
                                <InputText
                                    v-model="newItem.title"
                                    name="title"
                                    type="text"
                                    :rules="announcementValidationSchema.title"
                                />
                            </InputWrapper>

                            <InputWrapper label="Contenido" required class="col-span-2">
                                <TextEditor
                                    v-model:content="newItem.content"
                                    content-type="html"
                                />
                            </InputWrapper>

                            <InputWrapper label="Fecha de expiración" class="col-span-3 md:col-span-1">
                                <VDatePicker
                                    v-model="newItem.expires_on"
                                    mode="datetime"
                                    trim-weeks
                                    :popover="{ visibility: 'focus' }"
                                >
                                    <template v-slot="{ inputValue, inputEvents }">
                                    <input
                                        class="w-full px-1 text-sm py-1 border rounded focus:outline-none border-gray-300 focus:border-primary "
                                        :value="inputValue"
                                        v-on="inputEvents"
                                    />
                                    </template>
                                </VDatePicker>
                            </InputWrapper>

                            <InputWrapper label="Aviso informativo" class="col-span-2 md:col-span-1">
                                <template #icon>
                                    <IconWarningSolid class="size-5" />
                                </template>
                                <InputToggleButton
                                    :name="newItem.is_blocking == 1 ? 'Sí' : 'No'"
                                    :value="newItem.is_blocking == 1"
                                    :inactiveDotColor="'#B91C1C'"
                                    :inactiveLineColor="'#CCCCCC'"
                                    @change="newItem.is_blocking = newItem.is_blocking == 1 ? 0 : 1"
                                />
                            </InputWrapper>

                            <FormSection label="Estado del anuncio" centered collapsible>
                                <template #icon>
                                    <IconStatus class="size-5" />
                                </template>

                                <InputToggleButton
                                    :name="newItem.is_active == 1 ? 'Activo' : 'Inactivo'"
                                    :value="newItem.is_active == 1"
                                    :inactiveDotColor="'#B91C1C'"
                                    :inactiveLineColor="'#CCCCCC'"
                                    @change="newItem.is_active = newItem.is_active == 1 ? 0 : 1"
                                />
                            </FormSection>
                        </Form>
                    </template>
                    <template #action>
                        <ButtonAction
                            :label="newItem.id == undefined ? 'Añadir' : 'Guardar cambios'"
                            color="green"
                            size="sm"
                            @click="submitAnnouncements"
                        />
                    </template>
                </TheModal>

                <TheModal :open="isDeleteAnnouncementsModalOpen" title="Borrar Anuncio" @close="isDeleteAnnouncementsModalOpen = false">
                    <template #body>
                        <p class="pr-8 leading-7 tracking-wide text-gray-800">
                            SE VA A ELIMINAR ESTE ANUNCIO
                            <br>Esta acción no se podrá deshacer.
                        </p>
                    </template>

                    <template #action>
                        <ButtonAction
                            :label="'Borrar'"
                            color="red"
                            size="sm"
                            @click="deleteAnnouncements"
                        />
                    </template>
                </TheModal>
        </Section>
    </div>
</template>

<script>
    import { Form } from 'vee-validate';
    import AdminMixin from "@/components/mixins/AdminMixin.vue";
    import * as yup from 'yup';
    import { DatePicker } from 'v-calendar';
    import 'v-calendar/style.css';

    export default {
        name: "Settings",

        components: {
            Form,
            "VDatePicker": DatePicker,
        },

        mixins: [AdminMixin],

        data() {
            return {
                announcementsData: [],
                announcementsFilters: {},
                announcementsPagination: {},
                loadingAnnouncementsTable: false,
                isDeleteAnnouncementsModalOpen: false,
                isAnnouncementModalOpen: false,
                isLoading: false,
                isEditing: false,
                submitting: false,
                tableNameForAnnouncementsPagination: 'anuncios_settings',
                keepAnnouncementsPagination: false,
                sortProps: {},
                storedPagination: [],
                selectedAnnouncement: null,
                newItem: {
                    is_active: true,
                    is_blocking: 0,
                    title: '',
                    content: '',
                    expires_on: null
                }
            }
        },

        computed: {
            announcementsTableHeaders() {
                return [
                    { prop: 'title', name: 'Título', openModal: true, sortable: true },
                    { prop: 'is_blocking', name: 'Aviso informativo', openModal: true, type: 'boolean', sortable: true },
                    { prop: 'is_active', name: 'Estado', openModal: true, type: 'active', sortable: true },
                    { prop: 'expires_on', name: 'Expira', openModal: true, type: 'datetime', sortable: true },
                    { prop: 'actionColumn', name: 'Acciones', size: this.user?.level > '0' ? 100 : 160 },
                ];
            },

            datatableAnnouncementsActions() {
                const array = [];
                if (this.user?.level < 30) {
                    array.push('delete');
                }
                return array;
            },

            announcementValidationSchema() {
                return yup.object().shape({
                    title: yup.string().required('El título es obligatorio'),
                });
            },
        },

        async mounted() {
            await this.filterTable();
        },

        methods: {
            updateAnnouncementsFilters(filters) {
                this.announcementsFilters = { ...filters };
                this.filterTable(true, this.sortProps);
            },

            async filterTable(isInternalLoading = false, sortProps = {}) {
                this.isLoading = !isInternalLoading;
                let params = { ...this.announcementsFilters, ...sortProps };

                if (this.keepAnnouncementsPagination) {
                    this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForAnnouncementsPagination}`)) || [];
                }
                params.pagination = this.storedPagination;

                try {
                    const response = await this.$axios.get('api/announcements/filter', { params });
                    this.announcementsData = response.data.data;
                    this.announcementsPagination = response.data;
                } catch (e) {
                    this.showDevError(e);
                } finally {
                    this.isLoading = false;
                }
            },

            currentAnnouncementsPagination(id) {
                const fallback = { page: 1, per_page: 10 };
                const finded = (this.storedPagination || []).find(f => f.name == `${this.tableNameForAnnouncementsPagination}_${id}`);
                return finded?.pagination || fallback;
            },

            async handleAnnouncementsSortChange(data) {
                if (data) {
                    this.sortProps = data;
                    await this.filterTable(true, this.sortProps);
                }
            },

            loadAnnouncementsData(item = false) {
                if (item) {
                    this.newItem = { ...item };
                    if (this.newItem.expires_on) {
                        this.newItem.expires_on = new Date(this.newItem.expires_on);
                    }
                    this.isEditing = true;
                } else {
                    this.newItem = {
                        is_active: true,
                        is_blocking: 0,
                        title: '',
                        content: '',
                        expires_on: null
                    };
                    this.isEditing = false;
                }
                this.isAnnouncementModalOpen = true;
            },

            formatDate(date) {
                if (!date) return null;
                if (!(date instanceof Date)) date = new Date(date);
                return new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
            },

            async submitAnnouncements() {
                if (this.submitting) return;
                this.submitting = true;

                const result = await this.$refs.form.validate();
                if (!result.valid || !this.newItem.content) {
                    this.submitting = false;
                    if (!this.newItem.content) {
                        this.openAlert("info", "El campo Contenido no puede estar vacío");
                    } else {
                        this.openAlert("info", "Rellene correctamente el formulario");
                    }
                    return;
                }

                const dataToSave = { ...this.newItem };
                dataToSave.expires_on = this.formatDate(dataToSave.expires_on);

                const slug = !this.isEditing
                    ? { method: 'post', url: '/api/announcements/create' }
                    : { method: 'put', url: `/api/announcements/${this.newItem.id}` };

                try {
                    await this.$axios[slug.method](slug.url, dataToSave);
                    const message = this.isEditing ? "Anuncio actualizado con éxito." : "Anuncio añadido con éxito.";
                    this.openAlert("success", message, null);
                    this.isAnnouncementModalOpen = false;
                    await this.filterTable();
                } catch (error) {
                    console.error(error);
                    this.openAlert("error", "Error al guardar el anuncio");
                } finally {
                    this.submitting = false;
                }
            },

            openRemoveAnnouncementsModal(row) {
                this.selectedAnnouncement = row;
                this.isDeleteAnnouncementsModalOpen = true;
            },

            async deleteAnnouncements() {
                if (this.selectedAnnouncement) {
                    try {
                        await this.$axios.delete('api/announcements', { data: { id: this.selectedAnnouncement.id } });
                        this.isDeleteAnnouncementsModalOpen = false;
                        await this.filterTable();
                        this.openAlert('success', 'Anuncio borrado correctamente');
                    } catch (e) {
                        this.showDevError(e);
                    }
                }
            },
        }
    }
</script>

<style scoped>
</style>
