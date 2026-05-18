<template>
    <div :class="gridClass">
        <component
            :is="item.clickable ? 'button' : 'article'"
            v-for="item in items"
            :key="item.key || item.label"
            type="button"
            class="flex min-w-0 flex-col gap-0.5 rounded-md border p-2 text-left shadow-sm transition-all"
            :class="cardClass(item)"
            @click="item.clickable && $emit('select', item)"
        >
            <p class="truncate text-[9px] font-semibold uppercase opacity-70 sm:text-[10px]">
                {{ item.label }}
            </p>
            <p class="text-lg font-bold leading-none tabular-nums sm:text-xl">
                {{ item.value }}
            </p>
            <p
                v-if="item.detail || item.caption"
                class="line-clamp-2 whitespace-pre-line text-[10px] font-semibold leading-tight opacity-80 italic"
            >
                {{ item.detail || item.caption }}
            </p>
        </component>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

const COLOR_CLASSES = {
    slate: {
        idle: 'border-slate-200 bg-white text-slate-800 hover:border-slate-300',
        active: 'border-slate-400 bg-slate-100 text-slate-900',
    },
    primary: {
        idle: 'border-primary-100 bg-white text-slate-900 hover:border-primary-200',
        active: 'border-primary bg-primary-50 text-primary',
    },
    emerald: {
        idle: 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100/70',
        active: 'border-emerald-300 bg-emerald-100 text-emerald-800',
    },
    sky: {
        idle: 'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100/70',
        active: 'border-sky-300 bg-sky-100 text-sky-800',
    },
    violet: {
        idle: 'border-violet-200 bg-violet-50 text-violet-700 hover:bg-violet-100/70',
        active: 'border-violet-300 bg-violet-100 text-violet-800',
    },
    amber: {
        idle: 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100/70',
        active: 'border-amber-300 bg-amber-100 text-amber-800',
    },
    cyan: {
        idle: 'border-cyan-200 bg-cyan-50 text-cyan-700 hover:bg-cyan-100/70',
        active: 'border-cyan-300 bg-cyan-100 text-cyan-800',
    },
    orange: {
        idle: 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-100/70',
        active: 'border-orange-300 bg-orange-100 text-orange-800',
    },
    rose: {
        idle: 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100/70',
        active: 'border-rose-300 bg-rose-100 text-rose-800',
    },
};

export default defineComponent({
    name: 'SummaryCards',
    emits: ['select'],
    props: {
        items: {
            type: Array,
            default: () => [],
        },
        columns: {
            type: String,
            default: 'grid-cols-2 sm:grid-cols-4',
        },
    },
    computed: {
        gridClass() {
            return `grid gap-2 ${this.columns}`;
        },
    },
    methods: {
        cardClass(item) {
            if (item.class) return item.class;
            const palette = COLOR_CLASSES[item.color || 'primary'] || COLOR_CLASSES.primary;
            return [
                item.active ? palette.active : palette.idle,
                item.clickable ? 'cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/20' : '',
            ].filter(Boolean).join(' ');
        },
    },
});
</script>
