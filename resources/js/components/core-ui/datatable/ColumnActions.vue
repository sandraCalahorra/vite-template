<template>
    <div class="flex justify-center items-center gap-2">
        <InputToggleButton
            :value="row.is_enabled"
            :name="row.is_enabled ? 'Habilitado' : 'Deshabilitado'"
            v-if="actions.includes('enable')"
            @input="enableItem(row)"
            @click.stop
        />
        <InputToggleButton
            :value="row.solved"
            :name="row.solved ? 'Resuelta' : 'Pendiente'"
            v-if="actions.includes('resolve')"
            @input="resolveItem(row)"
            @click.stop
        />
        <IconDelete
            v-if="actions.includes('delete') && canDelete"
            class="size-5 cursor-pointer text-gray-900 min-w-5 hover:text-red-600"
            @click.stop="deleteItem(row)"
        />
    </div>
</template>

<script>
export default {
    name: 'ColumnActions',
    emits: ['delete', 'enable', 'resolve'],
    props: {
        row: {
            type: Object,
            default: () => {},
        },
        actions: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        canDelete() {
            // If not on the users page, we rely on the actions prop
            if (this.$route.path !== '/usuarios') return true;

            // On users page, prevent self-deletion or deleting equal/higher level users
            if (this.user && this.row) {
                return this.user.level < this.row.level;
            }
            return false;
        },
        canEnable() {
            if (this.$route.path !== '/usuarios') return true;
            if (this.user && this.row) {
                return this.user.level < this.row.level;
            }
            return false;
        },
    },
    methods: {
        deleteItem(row) {
            this.$emit('delete', row);
        },
        enableItem(row) {
            this.$emit('enable', row);
        },
        resolveItem(row) {
            this.$emit('resolve', row);
        },
    },
};
</script>
