<template>
    <div class="excel-preview-system flex flex-col gap-3">
        <!-- Resumen Compacto -->
        <div v-if="data && data.length > 0" class="flex items-center justify-between px-3 py-2 bg-white border border-primary/20 rounded-md shadow-sm border-l-4 border-l-primary">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Vista Previa de registros</span>
                <span class="px-1.5 py-0.5 rounded bg-primary text-white text-[10px] font-black animate-pulse shadow-sm">{{ data.length }}</span>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="table-container relative border border-gray-200 rounded-md bg-white overflow-hidden shadow-sm">
            <div class="overflow-x-auto overflow-y-auto max-h-[450px] custom-scrollbar-admin">
                <table class="w-full text-left border-collapse table-auto min-w-max">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gray-50 border-b-2 border-primary/20">
                            <th
                                v-for="(header, key) in data?.[0]?.values"
                                :key="key"
                                class="px-3 py-2.5 text-[10px] font-bold text-primary uppercase tracking-widest border-r border-gray-200 last:border-r-0 whitespace-nowrap bg-gray-50 min-w-[120px]"
                            >
                                {{ header?.name || header }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!data || data.length === 0">
                            <td :colspan="data?.[0]?.values?.length || 1" class="px-4 py-8 text-center text-xs text-gray-400 italic font-medium">
                                No hay datos para mostrar.
                            </td>
                        </tr>

                        <tr
                            v-for="(row, index) in data"
                            :key="index"
                            class="hover:bg-primary/[0.03] transition-colors"
                        >
                            <td
                                v-for="(col, index2) in row.values"
                                :key="index2"
                                class="px-3 py-2 text-xs border-r border-gray-50 last:border-r-0 max-w-xs truncate min-w-[120px] text-gray-600"
                                :class="{
                                    'bg-red-50 text-red-700 font-semibold border-l-2 border-l-red-500': isErrorCell(row, col)
                                }"
                                :title="col.value"
                            >
                                {{ col.value }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Array,
            required: true
        },
    },
    methods: {
        isErrorCell(row, col) {
            if (!row.result || !Array.isArray(row.result)) return false;
            // Check if the current column object or its key/value is in the row.result array
            return row.result.some(err => err === col || (err.key === col.key && err.value === col.value));
        }
    }
};
</script>

<style scoped>
.excel-preview-system {
    animation: fadeInCompact 0.3s ease-out;
}

@keyframes fadeInCompact {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar-admin::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar-admin::-webkit-scrollbar-track {
    background: #f9fafb;
}
.custom-scrollbar-admin::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.custom-scrollbar-admin::-webkit-scrollbar-thumb:hover {
    background: theme('colors.primary.DEFAULT', '#3b82f6');
}
</style>
