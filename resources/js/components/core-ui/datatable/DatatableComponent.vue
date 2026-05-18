<template>
    <div class="flex sm:px-0 px-4 flex-col gap-2" :class="{ 'has-target-route': targetRoute, 'inherit': inherit, 'no-padding': noPadding }">

        <div class="flex gap-2" v-if="displayConfig">
            <span
                @click="applyTableBorder = !applyTableBorder"
                title="Aplicar Borde"
                class="flex gap-2 items-center justify-content-center cursor-pointer w-fit"
            >
                <span>Aplicar Borde</span>
                <IconTableBorder class="pointer-events-none size-5" :class="applyTableBorder ? 'text-gray-900' : 'text-gray-200'" />
            </span>
        </div>

        <div class="flex flex-col overflow-hidden rounded-md border border-gray-200 shadow">
            <div :class="`overflow-auto max-h-[${calculateMaxTableHeight}px]`"> <!-- max-h-[50*row + 40] -->

                <ElTable
                    ref="tableRef"
                    :key="tableKey"
                    row-key="id"
                    :data="localData"
                    :border="displayConfig ? applyTableBorder : false"
                    :default-expand-all="selectableTree"
                    :stripe="!!displayConfig || stripe"
                    :row-style="getRowStyle"
                    :row-class-name="getRowClass"
                    @row-click="handleRowClick"
                    @select-all="handleSelectAll"
                    @selection-change="handleSelectionChange"
                    @sort-change="handleSortChange"
                    :header-cell-style="headerCellStyle"
                    style="width: 100%; max-width: 100%"
                    :height="'auto'"
                >
                    <!-- :height="localData.length >= pageSize ? calculateMaxTableHeight : 'auto'" -->
                    <!-- Columns. The default min width is 200 -->
                    <ElTableColumn
                        v-for="(column, index) in columns"
                        :key="column.prop"
                        :prop="column.prop"
                        :label="column.name"
                        :sortable="column.sortable"
                        :width="getColumnWidth(column, index)"
                        :min-width="getColumnMinWidth(column)"
                        :class-name="!column.size && columnHasData(column) && column.type !== 'selection' ? 'auto-col' : ''"
                        :type="renderColumnType(column)"
                        :selectable="(row) => shouldShowCheckboxInput(row)"
                    >
                        <!-- :width="column.size"
                        :min-width="column.minWidth ?? 200" -->
                        <template #default="{ row }" v-if="column.type !== 'selection'">
                            <ColumnActions
                                v-if="isActionColumn(column, index)"
                                :row="row"
                                :actions="actions"
                                @delete="handleDelete"
                                @enable="handleEnable"
                                @resolve="handleResolve"
                            />

                            <DragHandle
                                v-else-if="column.type === 'drag'"
                                :disabled="isDragDisabled(row)"
                            />

                            <IconsCell
                                v-else-if="column.type === 'icons'"
                                :icons="getRowIcons(row)"
                                :row="row"
                            />

                            <DatatableCellComponent
                                v-else
                                :column="column"
                                :row="row"
                                :targetRoute="targetRoute"
                                :ignoredPropColumns="ignoredPropColumns"
                                :storagePreference="this.storedPagination"
                                :className="'flex flex-row flex-wrap gap-1'"
                                @on-modal-click="handleModalClick"
                            />
                        </template>
                    </ElTableColumn>
                </ElTable>
            </div>
            <!-- Pagination fixed at bottom -->
            <div class="p-2 border-t border-gray-200 bg-gray-50 flex justify-center">
                <ElPagination
                    background

                    v-model:page-size="pageSize"
                    v-model:current-page="currentPage"
                    :layout="$store.state.size === 'mobile' ? 'prev, next, jumper' : 'sizes, prev, pager, next'"
                    :total="totalRecords"

                    :disabled="totalRecords <= 10"

                    :page-sizes="[10, 20, 50, 100, 500, 1000]"

                    @update:current-page="emitSlug"
                    @update:page-size="emitSlug"
                />
            </div>
        </div>

    </div>
</template>

<script>
import { ElTable, ElTableColumn, ElPagination, ElLoading } from 'element-plus'
import Sortable from 'sortablejs';

export default {
    components: {
        ElTable,
        ElTableColumn,
        ElPagination,
    },


    data() {
        return {
            localData: [...(this.data || [])],
            pageSize: this.perPage || 10,
            currentPage: this.page || 1,
            loadingInstance: null,
            sortable: null,
            tableKey: 0,
            storedPagination: [],
            sortProps: {},
            applyTableBorder: true,
            applyTableBorder: true,
            ignoredPropColumns: ['drag', 'selection', 'actionColumn', 'expand'],
            wasIndeterminate: false,
            isInternalSelectionUpdate: false,
        }
    },

    watch: {
        data: {
            deep: true,
            handler(newVal) {
                if (this.isInternalSelectionUpdate) return;

                this.localData = [...(newVal || [])];
                // Force table re-render to recalculate column widths
                this.tableKey += 1;
                this.$nextTick(() => {
                    if (this.$refs.tableRef) {
                        this.localData.forEach(row => {
                            if (row.checked) {
                                this.$refs.tableRef.toggleRowSelection(row, true);
                            }
                        });
                    }
                });
            },
        },

        page(newVal) {
            this.currentPage = newVal;
        },

        perPage(newVal) {
            this.pageSize = newVal;
        },

        enableDrag(newVal) {
            if (newVal) this.initDrag();
            else this.destroyDrag();
        },
    },

    mounted() {
        if (this.keepPagination) this.storedPagination = JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [];
        if (this.enableDrag) this.initDrag();
    },

    beforeUnmount() {
        this.destroyDrag();
    },

    props: {
        data: {
            type: Array,
            default: () => []
        },
        columns: {
            type: Array,
            default: () => []
        },
        totalRecords: {
            type: Number,
            default: 0
        },
        actions: {
            type: Array,
            default: () => []
        },
        slug: String,
        tableId: {
            type: String,
            default: '1'
        },
        isMultiple: {
            type: Boolean,
            default: false
        },
        propId: String,
        tableNameForPagination: String,
        page: {
            type: Number,
            default: 1
        },
        perPage: {
            type: Number,
            default: 10
        },
        displayConfig: Boolean,
        filterProps: Object,
        keepPagination: {
            type: Boolean,
            default: true
        },
        immovableIds: {
            type: Array,
            default: () => []
        },
        targetRoute: {
            type: String,
            default: null
        },
        isEnabledDrag: {
            type: Boolean,
            default: false,
        },
        internalProp: {
            type: String,
            default: null,
        },
        inherit: {
            type: Boolean,
            default: false,
        },
        noPadding: {
            type: Boolean,
            default: false,
        },
        stripe: {
            type: Boolean,
            default: true,
        },
        /*
         * Define the props for applying background color to each row
         * applyRowColor: Determines whether the background color should be applied to each row
         * rowColorField: Specifies the name of the database column that contains the color value for each row
         */
        applyRowColor: {
            type: Boolean,
            default: false
        },
        rowColorField: {
            type: String,
            default: 'color' // name of the DB column
        },
        selectableTree: {
            type: Boolean,
            default: false
        },
    },

    computed: {
        calculateMaxTableHeight() {
            return (50 * this.pageSize) + 40;
        },

        enableDrag() {
            return this.columns.some(col => col.rowDrag === true);
        },
    },

    methods: {
        getFirstCol(array, key) {
            return Object.entries(array)[0][0] == key;
        },

        isActionColumn(column, index) {
            return (
                column.prop === 'actionColumn' ||
                (
                    (this.actions.includes('delete') || this.actions.includes('enable') || this.actions.includes('resolve')) &&
                    index === this.columns.length - 1
                )
            );
        },

        isDragDisabled(row) {
            return this.immovableIds.includes(row['id']) || !this.isEnabledDrag;
        },

        getRowIcons(row) {
            return [
                row.is_enabled == 0 && 'disabled',
                row.warning && 'warning',
            ].filter(Boolean)
        },


        columnHasData(column) {
            // Explicit override for non-textual columns
            if (column.hasVisualContent) return true;
            if (['drag', 'actionColumn', 'selection'].includes(column.type)) return true;
            if (!column.prop) return false;

            return this.localData.some(row => {
                const v = row[column.prop];
                if (v == null) return false;
                if (Array.isArray(v)) return v.length;
                if (typeof v === 'object') return Object.keys(v).length;
                return String(v).trim().length;
            });
        },

        getColumnWidth(column, index) {
            if (column.type !== 'icons') return column.size;

            const maxIcons = Math.max(
                0,
                ...this.localData.map(r => this.getRowIcons(r).length)
            );

            return maxIcons ? maxIcons * 45 : 1;
        },

        getColumnMinWidth(column) {
            // If explicit size -> no min-width needed (width will control)
            if (column.size) return undefined;

            // If column has no data -> don't force a min-width so it can shrink/grow to content
            if (!this.columnHasData(column)) return undefined;

            // If has data but no explicit size -> provide min width (fallback 150)
            return column.minWidth ?? 150;
        },

        renderColumnType(column) {
            switch (column.type) {
                case 'selection':
                    return 'selection';
                default:
                    return undefined;
            }
        },

        getRowState(row) {
            return {
                disabled: row.is_enabled == 0,
                parent: row.children?.length,
                children: row.parent_id,
                colored: this.applyRowColor && row?.[this.rowColorField],
            };
        },

        getRowStyle({ row }) {
            const { disabled, colored, children, parent } = this.getRowState(row);

            let bg = null;
            let hover = null;

            if (disabled) {
                bg = '#e5e7eb';
                hover = '#d1d5db';
            } else if (children) {
                bg = '#deedfe';
                hover = bg;
            } else if (parent) {
                bg = '#afd4fe';
                hover = bg;
            } else if (colored) {
                bg = row[this.rowColorField];
                hover = bg;
            }

            if (!bg) return {};

            const contrast = this.calculateDynamicColor(bg);

            return {
                cursor: 'pointer',
                backgroundColor: bg,
                '--row-bg': bg,
                '--row-hover': hover,
                '--row-hover-mix': contrast === '#000000' ? 'white' : 'black',
            };
        },

        getRowClass({ row }) {
            const { disabled, colored, children, parent } = this.getRowState(row);

            return [
                disabled && 'row-disabled',
                children && !disabled && 'row-children',
                parent && !disabled && 'row-parent',
                colored && !disabled && 'row-colored',
            ].filter(Boolean).join(' ');
        },


        headerCellStyle({ columnIndex }) {
            const base = {
                backgroundColor: 'var(--primary)',
                color: '#fff',
                borderColor: 'rgba(255, 255, 255, 0.1)',
            }

            // First column
            if (columnIndex === 0) {
                return {
                    ...base,
                    borderTopLeftRadius: '0.375rem', // same as rounded-tl-md
                }
            }

            // Last column
            if (columnIndex === this.columns.length - 1) {
                return {
                    ...base,
                    borderTopRightRadius: '0.375rem', // same as rounded-tr-md
                    pointerEvents: 'none',
                }
            }

            // Other columns
            return base
        },

        async loadData(pagination, propId, params = {}, internalProp = null) {
            const loader = ElLoading.service({ target: this.$el, lock: true, background: 'rgba(0, 0, 0, 0.2)' });

            try {
                const { data } = await this.$axios.get(this.slug, {
                params: { pagination, ...params }
                });
                // const result = Array.isArray(data) ? data : data.data;

                // const items = Array.isArray(result) ? result : Object.values(result || {});
                // const found = items.find(r => r[propId] == this.tableId) ?? items;

                const items = Array.isArray(data)
                    ? data
                    : Array.isArray(data?.data)
                        ? data.data
                        : data?.data
                            ? Object.values(data.data)
                            : Object.values(data || {});

                const found = this.isMultiple ? (items.find(r => r[propId] == this.tableId) ?? items) : items;
                // const found = this.isMultiple ? items.find(r => r[propId] == this.tableId) : items;

                // this.localData = internalProp ? [...found?.[internalProp]] : found;
                this.localData = internalProp
                    ? (found?.[internalProp] ?? [])
                    : found;
                this.$emit('on-data-update', this.localData);
            } catch (e) {
                console.error(e);
            } finally {
                loader.close();
            }
        },

        shouldShowCheckboxInput(row) {
            const isUsersRoute = this.$route.path === '/usuarios';
            const userLevel = this.user?.level;

            return !isUsersRoute || userLevel < row?.level;
        },

        handleSelectAll(selection) {
            if (this.wasIndeterminate) {
                this.$refs.tableRef.clearSelection();
            }
        },

        handleSelectionChange(val) {
            this.isInternalSelectionUpdate = true;

            // Determine Indeterminate State (based on current row data before update)
            const checkedCount = this.localData.reduce((acc, row) => acc + (row.checked ? 1 : 0), 0);
            this.wasIndeterminate = checkedCount > 0 && checkedCount < this.localData.length;

            // Sync Selection
            const selectedIds = new Set(val.map(item => item.id));

            this.localData.forEach(row => {
                const isSelected = selectedIds.has(row.id);
                // Sync the checked property only if changed to avoid redundant reactivity triggers
                if (row.checked !== isSelected) {
                    row.checked = isSelected;
                }

                if (row.children) {
                    row.children.forEach(child => {
                        const isSelected = selectedIds.has(child.id);
                        if (child.checked !== isSelected) {
                            child.checked = isSelected;
                        }
                    });
                }
            });

            this.$emit('on-selection-change', val);

            this.$nextTick(() => {
                this.isInternalSelectionUpdate = false;
            });
        },

        handleRowClick(row, column, event) {
            // If clicking drag handle or action icon -> ignore
            if (this.ignoredPropColumns.includes(column.property)) return;

            // Intercept modal columns
            const colDef = this.columns.find(c => c.prop == column.property);
            if (colDef && colDef.openModal && colDef.type == 'customer') {
                this.handleModalClick(row, colDef);
                return; // Prevent further navigation
            }

            this.$emit('on-row-click', row, column, event)
        },

        handleDelete(row) {
            this.$emit('on-delete-click', row);
        },

        handleEnable(row) {
            this.$emit('on-enable-item', row);
        },

        handleResolve(row) {
            this.$emit('on-resolve-item', row);
        },

        async emitSlug(e) {
            const pagination = { page: this.currentPage, per_page: this.pageSize };
            this.storedPagination = this.buildPagination(pagination);

            this.$emit('on-page-change', this.storedPagination);
            // await this.loadData(this.storedPagination, this.propId, { ...this.sortProps, ...this.filterProps }, this.internalProp);
        },

        buildPagination(pagination) {
            const storedPagination = this.keepPagination ? JSON.parse(sessionStorage.getItem(`${this.tableNameForPagination}`)) || [] : [];
            const name = `${this.tableNameForPagination}_${this.tableId}`;

            if (storedPagination.length > 0) {
                const finded = storedPagination.find(f => f.name == name);
                if (finded) {
                    finded.pagination = pagination;
                } else {
                    storedPagination.push({
                        name: name,
                        pagination: pagination
                    });
                }
            } else {
                storedPagination.push({
                    name: name,
                    pagination: pagination
                });
            }
            if (this.keepPagination) sessionStorage.setItem(`${this.tableNameForPagination}`, JSON.stringify(storedPagination));
            return storedPagination;
        },

        async handleSortChange({ prop, order }) {
            this.sortProps = { order_by: prop, order };
            const params = { ...this.filterProps, ...this.sortProps };
            // await this.loadData(this.storedPagination, this.propId, params, this.internalProp);
            this.$emit('on-sort-change', { order_by: prop, order });
        },

        async initDrag() {
            await this.$nextTick(() => {
                const tbody =
                    this.$refs.tableRef?.$el.querySelector('.el-table__body-wrapper tbody');

                if (!tbody) return;

                this.sortable = new Sortable(tbody, {
                    handle: '.drag-handle',
                    animation: 150,

                    onEnd: async (evt) => {
                        const oldIndex = evt.oldIndex;
                        const newIndex = evt.newIndex;

                        if (
                            oldIndex === newIndex ||
                            this.immovableIds.includes(this.localData[newIndex].id) ||
                            !this.isEnabledDrag
                        ) {
                            await this.rebuildTable();
                            this.openAlert(
                                'info',
                                oldIndex === newIndex
                                    ? 'Reordenación cancelada.'
                                    : 'No puedes reordenar este hito.'
                            );
                            return;
                        }

                        // IMPORTANT: reorder BEFORE Vue re-renders
                        const movedRow = this.localData.splice(oldIndex, 1)[0];
                        this.localData.splice(newIndex, 0, movedRow);

                        let index = ((this.pageSize * this.currentPage) - this.pageSize) + 1;

                        this.localData = this.localData.map((item) => ({
                            ...item,
                            order: index++,
                        }));

                        // Force rebuild of the table
                        await this.rebuildTable();

                        // Emit updated order
                        this.$emit('on-reorder', [...this.localData]);
                    },
                });
            });
        },

        async rebuildTable() {
            this.tableKey++;
            // re-init Sortable after table rebuild
            await this.$nextTick();
            this.reinitSortable();
        },

        reinitSortable() {
            if (this.sortable) {
                this.sortable.destroy();
                this.sortable = null;
            }
            // small delay ensures DOM exists
            this.$nextTick(() => { this.initDrag(); });
        },

        destroyDrag() {
            if (this.sortable) {
                this.sortable.destroy();
                this.sortable = null;
            }
        },

        handlePageChange(page) {
            this.$emit('on-page-change', page)
        },

        handleModalClick(row, column) {
            if (column.type == 'customer') {
                this.loadModalCustomer(row.customer_id || row.id);
            }
        },

        async loadModalCustomer(customer_id) {
            try {
                if (customer_id) {
                    const result = await this.$axios.get(`/api/v2/customers/${customer_id}`);
                    this.customerData = result.data;

                    this.isCustomerModalOpen = true;
                }
            } catch (error) {
                console.log(error);
                this.openAlert("error", error.message || "Ocurrió un error.");
            }
        },

        closeCustomerModal() {
            this.isCustomerModalOpen = false;
        },
    },
}
</script>

<style scoped>
    :deep(.el-table) {
        overflow: hidden;
    }
    :deep(.el-table .el-table__cell) {
        z-index: unset !important; /* this removes fixed, fixed: right and sticky headers */
    }

    /* Checkbox styles */
    :deep(.el-checkbox__input.is-checked .el-checkbox__inner),
    :deep(.el-checkbox__input.is-indeterminate .el-checkbox__inner) {
        background-color: #1B82BA !important;
    }
    :deep(.el-checkbox__input) {
        padding-left: 0.75rem !important;
    }

    /* caret styles for sortable columns */
    :deep(.el-table .ascending .sort-caret.ascending) {
        border-bottom-color: #1B82BA !important;
    }
    :deep(.el-table .descending .sort-caret.descending) {
        border-top-color: #1B82BA !important;
    }

    /* table cell  padding when has target route class */
    .inherit :deep(.el-table td.el-table__cell div) {
        min-height: inherit;
    }
    .has-target-route :deep(.el-table td.el-table__cell div) {
        padding: 0 !important;
    }
    .no-padding :deep(.el-table td.el-table__cell) {
        padding: 0 !important;
    }
    :deep(.el-table-column--selection .cell),
    .has-target-route :deep(.el-table__body tr.el-table__row > td) {
        /* padding: 5.5px 0 5.5px 0 !important; */
        padding: 0 !important;
    }

    /* table row hover background */
    :deep(.el-table__body tr.row-colored:hover > td),
    :deep(.el-table__body tr.row-children:hover > td),
    :deep(.el-table__body tr.row-parent:hover > td) {
        background-color: color-mix(
            in srgb,
            var(--row-bg) 100%,
            var(--row-hover-mix) 35% /* Dark rows -> lighten | Light rows -> darken */
        ) !important;
    }

    :deep(.el-table__body tr.row-parent > td) {
        font-weight: 600;
    }

    :deep(.el-table__body tr.row-parent:hover > td) {
        font-weight: bold;
    }

    :deep(.el-table__expand-icon) {
        color: black;
    }

    /* hide indent for children and disabled rows */
    :deep(.el-table__body tr.row-children > td > div .el-table__indent),
    :deep(.el-table__body tr.row-children > td > div .el-table__placeholder),
    :deep(.el-table__body tr.row-disabled > td > div .el-table__indent),
    :deep(.el-table__body tr.row-disabled > td > div .el-table__placeholder) {
        display: none !important;
    }

    /* Disabled / on_hold rows */
    :deep(.el-table__body tr.row-disabled) {
        /* box-shadow: inset 0 0 0 2px #b91c1c; */
        /* outline: 2px solid #b91c1c; */
        outline: 2px solid rgba(255, 0, 0, 0.15);
        outline-offset: -2px;
        background: repeating-linear-gradient(
            45deg,
            rgba(255, 0, 0, 0.05),
            rgba(255, 0, 0, 0.05) 10px,
            rgba(255, 0, 0, 0.15) 10px,
            rgba(255, 0, 0, 0.15) 20px
        );
    }

    /* Hover background for disabled without affecting shadow */
    :deep(.el-table__body tr.row-disabled:hover) {
        background-color: var(--row-hover) !important;
    }

    /* Make td backgrounds transparent to show tr shadow */
    :deep(.el-table__body tr.row-disabled > td),
    :deep(.el-table__body tr.row-parent > td),
    :deep(.el-table__body tr.row-children > td),
    :deep(.el-table__body tr.row-colored > td) {
        background-color: transparent !important;
    }

    /* Remove bottom border from all table cells */
    :deep(.el-table td.el-table__cell, .el-table th.el-table__cell) {
        border-bottom: none !important;
    }

    /* Auto column width */
    :deep(.el-table__body table),
    :deep(.el-table__header table) {
        table-layout: auto !important;
    }
    :deep(.el-table__body td.auto-col),
    :deep(.el-table__header th.auto-col) {
        min-width: 200px;
        max-width: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* text styles when has disabled customer */
    .disabled {
        color: red;
        text-decoration: line-through;
    }
</style>
