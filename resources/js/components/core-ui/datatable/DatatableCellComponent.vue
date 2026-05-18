<template>
    <LinkWrapper
        :displayWrapper="!ignoredPropColumns.includes(column.prop) && !!targetRoute"
        :href="resolvedHref"
        :className="className"
        @interaction="emitInteraction"
    >
        <!-- Color circle -->
        <template v-if="column.type === 'color'">
            <div class="flex items-center justify-center text-black">
                <span
                    class="inline-block w-5 h-5 rounded-full border border-gray-300"
                    :style="{ backgroundColor: row[column.prop] }"
                ></span>
            </div>
        </template>

        <!-- Color preview box -->
        <template v-else-if="column.type === 'preview'">
            <div
                class="px-2 py-1 text-center border border-gray-200"
                :style="{
                    backgroundColor: row['color'] || '#fff',
                    color: getContrastColor(row['color'] || '#fff'),
                }"
            >
                {{ row[column.prop] }}
            </div>
        </template>

        <template v-else-if="column.type === 'date'">
            <span class="text-black">
                {{ row[column.prop] ? new Date(row[column.prop])?.toLocaleDateString("es-ES", { day: '2-digit', month: '2-digit', year: 'numeric' }) : '' }}
            </span>
        </template>

        <template v-else-if="column.type === 'datetime'">
            <span class="text-black">
                {{ formattedDate }}
            </span>
        </template>

        <template v-else-if="column.type === 'simplePill'">
            <span class="flex flex-row flex-wrap gap-1">
                <span v-for="(action, actionIndex) in row[column.prop]" :key="actionIndex">
                    <SimplePill
                        :text="action.name"
                        :color="action.color"
                    />
                </span>
            </span>
        </template>

        <template v-else-if="column.type === 'pillColor'">
            <SimplePill
                :text="''"
                :color="row[column.prop]"
            />
        </template>

        <template v-else-if="column.type === 'display_icon'">
            <div class="flex justify-start" :title="renderIconTooltip">
                <span v-if="!resolvedIcon?.name">-</span>
                <component v-else :is="resolvedIcon" class="w-5 h-5 pointer-events-none text-gray-700" />
            </div>
        </template>

        <template v-else-if="column.variant === 'single'">
            <SimplePill
                v-if="row[column.prop]"
                :text="row[column.prop]?.type_name ?? (row[column.prop]?.name ?? '')"
                :color="row[column.prop].color || '#1B82BA'"
            />
        </template>

        <template v-else-if="column.variant === 'multiple_visual'">
            <span class="flex flex-row flex-wrap gap-1">
                <SimplePill
                    v-for="(action, actionIndex) in row[column.prop]"
                    :key="actionIndex"
                    :text="action.name"
                    :color="action.color || '#1B82BA'"
                />
            </span>
        </template>

        <template v-else-if="column.type === 'boolean'">
            <span :class="{ 'text-green-600': row[column.prop] == 1, 'text-red-600': row[column.prop] == 0 }">
                {{ row[column.prop] == 1 ? 'SÍ' : (row[column.prop] == 0 ? 'NO' : row[column.prop]) }}
            </span>
        </template>

        <template v-else-if="column.type === 'active'">
            <span :class="{ 'text-green-600': row[column.prop] == 1, 'text-red-600': row[column.prop] == 0 }">
                {{ row[column.prop] == 1 ? 'Activo' : (row[column.prop] == 0 ? 'Inactivo' : row[column.prop]) }}
            </span>
        </template>

        <div v-else-if="column.openModal"
            class="flex flex-row gap-1 items-center"
            title="Se abrirá en una ventana flotante"
        >
            <div
                class="inline-flex break-words whitespace-normal align-middle items-center gap-2 hover:underline text-black"
                :class="[
                    [{'disabled': column.type == 'customer' && row['customer']?.['is_enabled'] == 0}]
                ]"
            >
                <span v-if="isHtml(displayValue)" v-html="displayValue"></span>
                <span v-else>
                    {{ displayValue }}
                </span>
                <IconOpenNewWindowSolid
                    v-if="column.type == 'customer'"
                    class="min-w-4 inline-flex align-text-bottom flex-row items-baseline justify-center size-4 text-gray-400 mix-blend-multiply"
                />
            </div>
        </div>

        <template v-else-if="column.prop === 'expand' && !!row['parent_id']">
            <span class="text-black justify-between gap-2" :class="{'truncate': column.ellipsis}">
                <div class="inline-flex items-center">
                    <a
                        :href="`/equipos/${row.parent_id}`"
                        class="inline-block"
                    >
                        <HeroiconDeviceChildren class="text-black cursor-pointer mr-1" />
                    </a>
                </div>
            </span>
        </template>

        <template v-else>
            <span class="text-black" :class="{'truncate': column.ellipsis}">
                <span v-if="isHtml(row[column.prop])" v-html="row[column.prop]"></span>
                <span v-else>{{ row[column.prop] }}</span>
            </span>
        </template>
    </LinkWrapper>
</template>
<script>
import { defineComponent } from 'vue'
import { retrieveIconList } from "@/utils/icons.js"
import UtilsMixin from '@/components/mixins/UtilsMixin.vue'

const iconRegistry = retrieveIconList();

const emptyIcon = {
    render() {
        return null
    }
}

export default defineComponent({
    name: 'DatatableCellComponent',

    components: {

    },

    mixins: [UtilsMixin],

    emits: ['on-modal-click', 'row-click'],

    props: {
        row: {
            type: [Object, Array],
            required: true
        },
        column: {
            type: Object,
            required: true
        },
        storagePreference: {
            type: Object,
        },
        targetRoute: {
            type: String,
            default: null
        },
        ignoredPropColumns: {
            type: Array,
            default: () => []
        },
        className: {
            type: [String, Array, Object],
        },
    },

    setup() {

    },

    computed: {
        resolvedHref() {
            let routeName = this.targetRoute;
            let params = { id: this.row.id };

            if (this.column.openModal && this.column.type === 'customer') {
                routeName = 'customersForm';
                params = { id: this.row.customer_id || this.row.id };
            }

            try {
                return this.$router.resolve({ name: routeName, params }).href;
            } catch (e) {
                return '#';
            }
        },


        displayValue() {
            if (this.column.type === 'customer') {
                return this.row?.customer_name?.display ?? (this.row?.customer_name ?? (this.row?.customersName ?? '-'));
            }
            return this.row[this.column.prop] ?? '-';
        },

        formattedDate() {
            if (!this.row[this.column.prop]) return '';

            const date = new Date(this.row[this.column.prop]);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        },

        renderIconTooltip() {
            const icon = this.resolvedIcon;

            if (!icon?.name) return 'Icono no encontrado';
            return `Icono: ${this.row[this.column.prop]}`;
        },

        resolvedIcon() {
            return iconRegistry[this.row[this.column.prop]] ?? emptyIcon;
        }

    },
    methods: {
        emitInteraction(event) {
            if (this.column.openModal) {
                this.$emit('on-modal-click', this.row, this.column);
                return;
            }
        },
        handleModalClick(row, column) {
            this.$emit('on-modal-click', this.row, this.column);
        },
        isHtml(value) {
            if (typeof value !== 'string') return false;
            const htmlRegex = /<[a-z][\s\S]*>/i;
            return htmlRegex.test(value);
        },
    }
})
</script>
