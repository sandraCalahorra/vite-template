<template>
    <div>
        <TheTopbar title="Centro de soporte" />

        <Section>
            <div class="flex flex-col gap-6">
                <!-- Filters and Add Button -->
                <FilterControls
                    :addTarget="false"
                    :filterOptions="computedFilterOptions"
                    @update:filters="updateFilters"
                    @click:add-button="loadData"
                    :isAdd="user?.level === 10"
                    color="primary"
                />

                <LoadingSpinner v-show="isLoading" />

                <!-- Tickets List Container -->
                <div class="min-h-[400px]" v-show="!isLoading">
                    <TicketsList
                        v-if="!isLoading"   
                        :groupedTickets="groupedTickets"
                        :ticketFilter="ticketFilter"
                        :expandedTicketIds="expandedTicketIds"
                        :openStatusDropdownId="openStatusDropdownId"
                        :normalizedStatus="normalizedStatus"
                        :canManageStatus="user?.level < 10"
                        @toggle-filter="toggleFilter"
                        @toggle-ticket="toggleTicket"
                        @toggle-status-dropdown="toggleStatusDropdown"
                        @set-status="setStatus"
                        @resend-email="resendEmail"
                    />

                    <LoadingComponent
                        v-show="loadingComponent"
                        customClass="py-20"
                        width="60"
                        height="60"
                        image="/img/Telecuidamos_Imagotipo_Color.svg"
                    />
                </div>
            </div>
        </Section>

        <TheModal
            :open="isModalOpen"
            :title="isEditing ? 'Detalles de la incidencia' : 'Nueva incidencia'"
            @close="isModalOpen = false"
            :large="true"
        >
            <template #body>
                <Form
                    ref="ticketForm"
                    id="ticket-form"
                    @submit="submitForm"
                    :validation-schema="schema"
                    v-slot="{ errors }"
                >
                    <div class="flex flex-col gap-8">

                        <!-- CONTACTO -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <IconUserSolid class="w-4 h-4 text-primary" />
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900 tracking-wide uppercase">
                                    Información de contacto
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <InputWrapper label="Nombre completo" :error="errors.name">
                                    <InputText
                                        name="name"
                                        v-model="newItem.name"
                                        placeholder="Nombre y apellidos"
                                        :disabled="isEditing"
                                        class="h-12"
                                    />
                                </InputWrapper>

                                <InputWrapper label="Correo electrónico" :error="errors.email">
                                    <InputText
                                        name="email"
                                        v-model="newItem.email"
                                        placeholder="correo@ejemplo.com"
                                        :disabled="isEditing"
                                        class="h-12"
                                    />
                                </InputWrapper>
                            </div>
                        </div>

                        <!-- CONSULTA -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <IconAttachEmail class="w-4 h-4 text-primary" />
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900 tracking-wide uppercase">
                                    Detalle de la consulta
                                </h3>
                            </div>

                            <div class="flex flex-col gap-6">
                                <InputWrapper label="Asunto" :error="errors.subject">
                                    <InputText
                                        name="subject"
                                        v-model="newItem.subject"
                                        placeholder="Resumen breve del problema"
                                        :disabled="isEditing"
                                        class="h-12"
                                    />
                                </InputWrapper>

                                <InputWrapper label="Urgencia (Prioridad)" :error="errors.priority">
                                    <VueMultiselect
                                        v-model="newItem.priority"
                                        :options="priorityOptions"
                                        :searchable="false"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        placeholder="Seleccione la prioridad"
                                        label="label"
                                        track-by="value"
                                        class="cursor-pointer border border-gray-300 rounded-md"
                                        :disabled="isEditing"
                                    />
                                </InputWrapper>

                                <InputWrapper label="Mensaje" :error="errors.message">
                                    <!-- <InputText
                                        name="message"
                                        type="textarea"
                                        v-model="newItem.message"
                                        placeholder="Describe el problema con el mayor detalle posible…"
                                        rows="6"
                                        :disabled="isEditing"
                                        class="resize-none"
                                    />
                                    <p class="mt-1 text-xs text-gray-400">
                                        Cuanta más información nos facilites, más rápido podremos ayudarte.
                                    </p> -->
                                    <TextEditor
                                        v-model:content="newItem.message"
                                        content-type="html"
                                        placeholder="Describe el problema con el mayor detalle posible…"
                                    />
                                    <p class="mt-1 text-xs text-gray-400">
                                        Cuanta más información nos facilites, más rápido podremos ayudarte.
                                    </p>
                                </InputWrapper>
                            </div>
                        </div>

                        <!-- ARCHIVOS -->
                        <div class="rounded-2xl border border-gray-200 bg-white p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <IconImageSolid class="w-4 h-4 text-primary" />
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900 tracking-wide uppercase">
                                    Archivos adjuntos
                                </h3>
                            </div>
                            <FileUploader
                                v-if="!isEditing"
                                :multiple="true"
                                @files-changed="handleFilesChanged"
                            />
                        </div>
                    </div>
                </Form>
            </template>

            <!-- ACTION -->
            <template #action>
                <span v-if="!isEditing" class="flex w-full rounded-md shadow-sm sm:w-auto">
                    <button
                        type="button"
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-700 border border-transparent rounded-md shadow-sm hover:bg-green-600 focus:outline-none focus:border-blue-300 focus:shadow-outline"
                        @click="submitForm"
                    >
                        Enviar
                    </button>
                </span>
            </template>
        </TheModal>


        <!-- Delete Confirmation Modal -->
        <TheModal :open="isDeleteModalOpen" title="ELIMINAR INCIDENCIA" @close="isDeleteModalOpen = false">
            <template #body>
                <div class="flex flex-col items-center text-center gap-4 py-4">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                        <IconDelete class="w-8 h-8" />
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-800">¿Está seguro?</h4>
                        <p class="text-gray-500 mt-1">Esta incidencia será eliminada permanentemente. No podrá recuperar esta información después.</p>
                    </div>
                </div>
            </template>
            <template #action>
                <div class="flex gap-3 w-full justify-end">
                    <button @click="isDeleteModalOpen = false" class="px-4 py-2 text-gray-500 font-semibold">Descartar</button>
                    <ButtonAction color="red" @click="confirmDelete">Sí, eliminar</ButtonAction>
                </div>
            </template>
        </TheModal>
    </div>
</template>

<script>
    import { defineComponent } from 'vue';
    import AdminMixin from "@/components/mixins/AdminMixin.vue";
    import TicketsList from "@/components/core-ui/TicketsList.vue";
    import FileUploader from "@/components/core-ui/FileUploader.vue";
    import { Form } from 'vee-validate';
    /* MultiSelect */
    import VueMultiselect from 'vue-multiselect'
    import 'vue-multiselect/dist/vue-multiselect.css';
    import * as yup from 'yup';

    export default defineComponent({
        name: 'Tickets',
        mixins: [AdminMixin],
        components: {
            Form,
            TicketsList,
            FileUploader,
            VueMultiselect
        },

        data() {
            return {
                fetchUrl: 'api/tickets/filter',
                storagePreference: "tickets_pagination",
                filters: {
                    stringSearch: ''
                },
                tableHeaders: {
                    id: { name: 'ID', type: 'default' },
                    subject: { name: 'ASUNTO', type: 'default' },
                    name: { name: 'SOLICITANTE', type: 'default' },
                    status_html: { name: 'ESTADO', type: 'default' },
                    created_at: { name: 'FECHA', type: 'datetime' }
                },
                data: [],
                pagination: [],
                loadingComponent: true,
                submitting: false,
                isModalOpen: false,
                isDeleteModalOpen: false,
                isEditing: false,

                // New logic for TicketsList
                expandedTicketIds: [],
                openStatusDropdownId: null,
                ticketFilter: ['pending'], // Default filter

                newItem: {
                    name: '',
                    email: '',
                    subject: '',
                    message: '',
                    status: 'pending',
                    priority: 'normal'
                },
                priorityOptions: [
                    { value: 'low', label: 'Baja (Consulta leve o duda)' },
                    { value: 'normal', label: 'Normal (Funcionamiento habitual)' },
                    { value: 'high', label: 'Alta (Problema bloqueante)' },
                    { value: 'urgent', label: 'Urgente (Crítico / No puedo trabajar)' }
                ],
                priorityAdminOptions: [
                    { value: 'low', label: 'Baja' },
                    { value: 'normal', label: 'Normal' },
                    { value: 'high', label: 'Alta' },
                    { value: 'urgent', label: 'Urgente' }
                ],
                statusOptions: [
                    { value: 'pending', label: 'Pendiente' },
                    { value: 'in_progress', label: 'En proceso' },
                    { value: 'completed', label: 'Resuelto' },
                    { value: 'cancelled', label: 'Cerrado' }
                ],
                selectedFiles: []
            }
        },

        computed: {
            user() {
                return this.$store.getters['auth/user'];
            },

            schema() {
                return yup.object({
                    name: yup.string().required('El nombre es obligatorio'),
                    email: yup.string().email('Email inválido').required('El email es obligatorio'),
                    subject: yup.string().required('El asunto es obligatorio'),
                    message: yup.string().required('El mensaje es obligatorio').min(10, 'Mínimo 10 caracteres'),
                });
            },

            actions() {
                const acts = [];
                if (this.user?.level === 0) {
                    acts.push('delete');
                    acts.push('resend-email');
                }
                return acts;
            },

            groupedTickets() {
                if (!this.data) return {};

                // Apply frontend filtering based on ticketFilter
                const filtered = this.data.filter(ticket => {
                    const status = this.normalizedStatus(ticket.status);
                    return this.ticketFilter.includes(status);
                });

                // Group by date (Month Year)
                const groups = {};
                filtered.forEach(ticket => {
                    const date = new Date(ticket.created_at);
                    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                    ];
                    const groupName = `${monthNames[date.getMonth()]} ${date.getFullYear()}`;

                    if (!groups[groupName]) groups[groupName] = [];
                    groups[groupName].push(ticket);
                });

                return groups;
            },

            computedFilterOptions() {
                if (this.user?.level < 10) {
                    return ['noAddButton', 'stringSearch', 'noFilters'];
                }
                return ['noFilters', 'stringSearch'];
            }
        },

        methods: {
            getStatusBadge(status) {
                const config = {
                    pending: { label: 'Pendiente', color: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
                    in_progress: { label: 'En proceso', color: 'bg-blue-100 text-blue-800 border-blue-200' },
                    completed: { label: 'Atendido', color: 'bg-green-100 text-green-800 border-green-200' },
                    cancelled: { label: 'Cancelado', color: 'bg-red-100 text-red-800 border-red-200' }
                };
                const { label, color } = config[this.normalizedStatus(status)] || { label: status, color: 'bg-gray-100 text-gray-800 border-gray-200' };
                return `<span class="px-2.5 py-1 rounded-full text-xs font-bold border ${color} uppercase tracking-wider shadow-sm">${label}</span>`;
            },

            normalizedStatus(status) {
                if (['pending', 'in_progress'].includes(status)) return 'pending';
                if (['resolved', 'completed'].includes(status)) return 'completed';
                if (['closed', 'cancelled'].includes(status)) return 'cancelled';
                return status;
            },

            loadData(item = false) {
                if (item) {
                    this.openEditModal(item);
                } else {
                    const fullname = this.user?.name + ' ' + this.user?.last_name;
                    this.newItem = {
                        name: fullname,
                        email: this.user?.email || '',
                        subject: '',
                        message: '',
                        status: this.statusOptions.find(o => o.value === 'pending'),
                        priority: this.priorityOptions.find(o => o.value === 'normal'),
                    };
                    this.selectedFiles = [];
                    this.isEditing = false;
                    this.isModalOpen = true;
                }
            },

            openEditModal(item) {
                this.newItem = { ...item };
                this.newItem.priority = this.priorityOptions.find(o => o.value === item.priority) || this.priorityAdminOptions.find(o => o.value === item.priority);
                this.newItem.status = this.statusOptions.find(o => o.value === item.status);
                this.isEditing = true;
                this.isModalOpen = true;
            },

            handleFilesChanged(files) {
                this.selectedFiles = files;
            },

            async validateAndSubmit() {
                const { valid } = await this.$refs.ticketForm.validate();
                if (valid) {
                    this.submitForm();
                }
            },

            async submitForm() {
                this.submitting = true;
                try {
                    const formData = new FormData();
                    const payload = { ...this.newItem };

                    // Map objects back to primitive values for API
                    if (payload.priority && typeof payload.priority === 'object') {
                        payload.priority = payload.priority.value;
                    }
                    if (payload.status && typeof payload.status === 'object') {
                        payload.status = payload.status.value;
                    }

                    Object.keys(payload).forEach(key => {
                        formData.append(key, payload[key]);
                    });

                    if (this.selectedFiles.length > 0) {
                        this.selectedFiles.forEach((file, index) => {
                            formData.append(`files[${index}]`, file);
                        });
                    }

                    await this.$axios.post('api/tickets/create', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    this.openAlert('success', 'Ticket enviado correctamente.');

                    this.isModalOpen = false;
                    await this.fetchTicketsTable();
                } catch (e) {
                    this.showDevError(e);
                } finally {
                    this.submitting = false;
                }
            },

            async resendEmail(item) {
                try {
                    await this.$axios.post(`api/tickets/send-email/${item.id}`);
                    this.openAlert('success', 'Email reenviado correctamente');
                    await this.fetchTicketsTable(true);
                } catch (e) {
                    this.showDevError(e);
                }
            },

            async fetchTicketsTable(isInternalLoading = false) {
                this.loadingComponent = true;
                let params = { ...this.filters };

                let currentPagination = JSON.parse(sessionStorage.getItem(this.storagePreference)) || { value: { page: 1, per_page: 10 } };
                params.per_page = currentPagination.value.per_page;
                params.page = currentPagination.value.page;

                await this.fetchPaginatedData(this.fetchUrl, params, false, isInternalLoading);
                this.loadingComponent = false;
            },

            updateFilters(newFilters) {
                this.filters = { ...this.filters, ...newFilters };
                this.fetchTicketsTable(true);
            },

            toggleFilter(key) {
                const index = this.ticketFilter.indexOf(key);
                if (index > -1) {
                    if (this.ticketFilter.length > 1) {
                        this.ticketFilter.splice(index, 1);
                    }
                } else {
                    this.ticketFilter.push(key);
                }
            },

            toggleTicket(id) {
                const index = this.expandedTicketIds.indexOf(id);
                if (index > -1) {
                    this.expandedTicketIds.splice(index, 1);
                } else {
                    this.expandedTicketIds.push(id);
                }
            },

            toggleStatusDropdown(id) {
                this.openStatusDropdownId = this.openStatusDropdownId === id ? null : id;
            },

            async setStatus(ticket, status) {
                try {
                    await this.$axios.put(`api/tickets/${ticket.id}`, { status });
                    this.openAlert('success', 'Estado actualizado');
                    this.openStatusDropdownId = null;
                    await this.fetchTicketsTable(true);
                } catch (e) {
                    this.showDevError(e);
                }
            },

            handlePagination(data) {
                if (data) {
                    this.fetchTicketsTable(true);
                }
            },

            openRemoveModal(item) {
                this.newItem = { ...item };
                this.isDeleteModalOpen = true;
            },

            async confirmDelete() {
                try {
                    await this.$axios.delete(`api/tickets/${this.newItem.id}`);
                    this.openAlert('success', 'Incidencia eliminado');
                    this.isDeleteModalOpen = false;
                    await this.fetchTicketsTable();
                } catch (e) {
                    this.showDevError(e);
                }
            }
        },

        async mounted() {
            await this.fetchTicketsTable();
        }
    });
</script>

<style scoped>
    :deep(.form-input) {
        @apply transition-all duration-200 border-gray-300 shadow-sm;
    }
    :deep(.form-input:focus) {
        @apply border-primary ring-2 ring-primary ring-opacity-10;
    }
</style>
