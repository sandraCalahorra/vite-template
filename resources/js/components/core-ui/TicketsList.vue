<template>
    <!-- Filters -->
    <div class="flex items-center gap-2 mb-2">
        <span class="text-sm font-semibold text-gray-800 uppercase">Filtros:</span>

        <button
            v-for="filter in filters"
            :key="filter.key"
            type="button"
            @click="$emit('toggle-filter', filter.key)"
            class="px-2 py-0.5 text-xs font-medium rounded-full border transition-all"
            :class="ticketFilter.includes(filter.key)
                ? filter.activeClass
                : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
        >
            {{ filter.label }}
        </button>
    </div>

    <!-- Sections -->
    <div v-for="(tickets, sectionName) in groupedTickets" :key="sectionName" class="mb-6">
        <h3 class="font-bold text-gray-700 mb-3 border-b border-gray-200 pb-1 uppercase text-xs tracking-wider">
            {{ sectionName === 'servicesExpansion'
                ? 'Asistente de recompra'
                : sectionName }}
        </h3>

        <div class="flex flex-col gap-3">
            <div
                v-for="ticket in tickets"
                :key="ticket.id"
                class="bg-white border border-gray-200 rounded-md transition-all duration-200"
                :class="{ 'shadow-md border-gray-300': expandedTicketIds.includes(ticket.id) }"
            >
                <!-- Header -->
                <div
                    class="flex flex-col md:flex-row items-start md:items-center justify-between p-3 cursor-pointer hover:bg-gray-50 transition-colors gap-3"
                    @click="$emit('toggle-ticket', ticket.id)"
                >
                    <div class="flex items-center gap-4 flex-grow min-w-0">
                        <!-- Status -->
                        <div class="relative" @click.stop v-if="ticket.status">
                            <button
                                type="button"
                                @click="canManageStatus ? $emit('toggle-status-dropdown', ticket.id) : null"
                                class="px-3 py-1 text-xs font-bold rounded-full uppercase shadow-sm tracking-wide border flex items-center gap-1 transition-all"
                                :class="[statusClass(ticket), canManageStatus ? 'hover:shadow-md cursor-pointer' : 'cursor-default']"
                            >
                                <span>{{ statusLabel(ticket) }}</span>
                                <svg v-if="canManageStatus" class="h-3 w-3 opacity-60" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div
                                v-if="openStatusDropdownId === ticket.id"
                                class="absolute left-0 mt-2 w-32 bg-white rounded-lg shadow-xl border z-50"
                            >
                                <button
                                    v-for="opt in statusOptions"
                                    :key="opt.key"
                                    @click="$emit('set-status', ticket, opt.key)"
                                    class="w-full text-left px-4 py-2 text-xs font-medium hover:bg-gray-50 flex items-center gap-2"
                                >
                                    <span :class="opt.dot"></span>
                                    {{ opt.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900 truncate">
                                    {{ ticket.subject }}
                                </span>
                                <!-- Email Error Indicator & Resend Button -->
                                <div v-if="ticket.email_sent === 0 || ticket.email_sent === false"
                                     class="flex items-center gap-2"
                                >
                                    <div class="flex items-center gap-1 text-red-500 animate-pulse"
                                         title="El correo de soporte no pudo enviarse.">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span class="text-[10px] font-bold uppercase tracking-tight">Error Email</span>
                                    </div>

                                    <button
                                        v-if="canManageStatus"
                                        type="button"
                                        @click.stop="$emit('resend-email', ticket)"
                                        class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-bold uppercase rounded border border-red-200 hover:bg-red-100 transition-colors"
                                    >
                                        Reenviar
                                    </button>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500 truncate">
                                {{ applicantFullName(ticket) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <!-- Priority Badge -->
                        <div
                            v-if="ticket.priority"
                            class="px-2 py-0.5 text-[10px] font-bold rounded border uppercase tracking-tighter inline-block"
                            :class="priorityClass(ticket.priority)"
                        >
                            {{ priorityLabel(ticket.priority) }}
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ ticket.created_at && new Date(ticket.created_at).toLocaleDateString() }}
                        </span>
                    </div>
                </div>

                <!-- Expanded -->
                <div
                    v-if="expandedTicketIds.includes(ticket.id)"
                    class="bg-gray-50 border-t p-4 pl-12 flex flex-col gap-5"
                >
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Mensaje</div>
                        <div
                            class="text-sm text-gray-800 leading-relaxed ql-editor-display"
                            v-html="ticket.message"
                        ></div>
                    </div>
                    <div v-if="ticket.files && ticket.files.length > 0" class="flex flex-col gap-2">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Archivos adjuntos</div>
                        <div class="flex flex-wrap gap-3">
                            <div
                                v-for="file in ticket.files"
                                :key="file.id"
                                @click="downloadFile(ticket.id, file.name)"
                                class="flex items-center gap-2 bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:text-primary hover:border-primary/30 hover:shadow-sm transition-all cursor-pointer group"
                            >
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span class="font-medium">{{ file.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div v-if="!hasTickets" class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-8 text-center flex flex-col items-center justify-center gap-2 my-4">
        <svg class="size-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <p class="text-sm font-medium text-gray-500 uppercase tracking-widest">
            No se han encontrado tickets con este estado
        </p>
    </div>
</template>

<script>
export default {
    name: 'TicketsList',
    props: {
        groupedTickets: Object,
        ticketFilter: Array,
        expandedTicketIds: Array,
        openStatusDropdownId: Number,
        normalizedStatus: Function,
        canManageStatus: {
            type: Boolean,
            default: false
        }
    },
    emits: [
        'toggle-filter',
        'toggle-ticket',
        'toggle-status-dropdown',
        'set-status',
        'resend-email'
    ],
    computed: {
        filters() {
            return [
                { key: 'pending', label: 'Pendientes', activeClass: 'bg-yellow-100 text-yellow-800 border-yellow-200' },
                { key: 'completed', label: 'Atendidos', activeClass: 'bg-green-100 text-green-800 border-green-200' },
                { key: 'cancelled', label: 'Cancelados', activeClass: 'bg-red-100 text-red-800 border-red-200' }
            ];
        },
        statusOptions() {
            return [
                { key: 'pending', label: 'Pendiente', dot: 'w-1.5 h-1.5 bg-yellow-400 rounded-full' },
                { key: 'cancelled', label: 'Cancelado', dot: 'w-1.5 h-1.5 bg-red-400 rounded-full' },
                { key: 'completed', label: 'Atendido', dot: 'w-1.5 h-1.5 bg-green-400 rounded-full' }
            ];
        },
        hasTickets() {
            return Object.keys(this.groupedTickets || {}).length > 0;
        }
    },
    methods: {
        statusClass(ticket) {
            return {
                'bg-green-100 text-green-800 border-green-200': this.normalizedStatus(ticket.status) === 'completed',
                'bg-red-100 text-red-800 border-red-200': this.normalizedStatus(ticket.status) === 'cancelled',
                'bg-yellow-100 text-yellow-800 border-yellow-200': this.normalizedStatus(ticket.status) === 'pending'
            };
        },
        statusLabel(ticket) {
            const s = this.normalizedStatus(ticket.status);
            return s === 'completed' ? 'Atendido' : s === 'cancelled' ? 'Cancelado' : 'Pendiente';
        },

        priorityClass(priority) {
            switch (priority) {
                case 'low': return 'bg-gray-50 text-gray-500 border-gray-200';
                case 'high': return 'bg-orange-50 text-orange-600 border-orange-200';
                case 'urgent': return 'bg-red-50 text-red-600 border-red-200';
                default: return 'bg-blue-50 text-blue-600 border-blue-200';
            }
        },

        priorityLabel(priority) {
            switch (priority) {
                case 'low': return 'Baja';
                case 'high': return 'Alta';
                case 'urgent': return 'Urgente';
                default: return 'Normal';
            }
        },

        applicantFullName(ticket) {
            if (ticket.user) {
                const name = ticket.user.name || '';
                const last1 = ticket.user.last_name || '';
                const last2 = ticket.user.last_name2 || '';
                return `${name} ${last1} ${last2}`.trim();
            }
            return ticket.name || ticket.email;
        },

        async downloadFile(ticketId, fileName) {
            try {
                const response = await this.$axios.get(`api/tickets/${ticketId}/file/${fileName}`, {
                    responseType: 'blob'
                });
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', fileName);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('Error downloading file:', error);
            }
        }
    }
};
</script>
