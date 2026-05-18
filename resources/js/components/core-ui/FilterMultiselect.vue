<template>
    <component
        :is="title ? 'InputWrapper' : 'div'"
        :label="title ? title : ''"
        :class="customInputWrapperClass ?? ''"
    >
        <NewMultiselect
            ref="filterMultiselect"
            v-model="internalValue"
            :disabled="disabled"
            :options="transformedOptions"
            :class="{'backgroundEmptyData':isEmpty}"
            :loading="loading"
            :multiple="multiple"
            placeholder="Seleccione una opción"
            @change="onChange"
            @open="handleOpenMultiselect"
        />
    </component>
</template>

<script>
import NewMultiselect from "@/components/core-ui/NewMultiselect.vue";
import AdminMixin from "@/components/mixins/AdminMixin.vue";

export default {
    name: "FilterMultiselect",
    mixins: [AdminMixin],
    data() {
        return {
            internalValue: undefined,
            isEmpty: false,
            loading: false,
            auxOptions: [],
        }
    },

    components: {
        NewMultiselect,
    },

    props: {
        filter: {
            type: [Array, Object, null],
            default: () => [],
            required: true,
        },
        options: {
            type: Array,
            default: () => [],
            required: true,
        },
        title:{
            type: String,
        },
        label:{
            type: String,
            default: '',
            required: true,
        },
        trackBy: {
            type: String,
            default: '',
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        slug: {
            type: [Object, String],
            default: () => ({slug: ''}),
        },
        closeOnSelect: {
            type: Boolean,
            default: true,
        },
        multiple: {
            type: Boolean,
            default: false,
        },
        customInputWrapperClass: {
            type: String,
        }
    },

    computed: {
        transformedOptions() {
            // Transform options from label/trackBy format to id/name format for NewMultiselect
            return this.auxOptions.map(option => {
                // If option is already in the correct format (has id and name), return as is
                if (typeof option === 'object' && option.id !== undefined && option.name !== undefined) {
                    return option;
                }

                // Otherwise, transform using label and trackBy props
                return {
                    id: typeof option === 'object' ? option[this.trackBy] : option,
                    name: typeof option === 'object' ? option[this.label] : option,
                    ...option // Preserve other properties
                };
            });
        },

        transformedFilter() {
            return Array.isArray(this.internalValue) ? [...this.internalValue] : this.internalValue;
        }
    },

    watch: {
        filter: {
            handler(newVal) {
                this.updateInternalValue(newVal);
            },
            immediate: true,
            deep: true,
        },
        options: {
            handler(newVal) {
                this.auxOptions = [...newVal];
            },
            immediate: true,
        }
    },

    mounted() {
        this.updateInternalValue(this.filter);
        this.auxOptions = [...this.options];
    },

    methods: {
        updateInternalValue(filterValue) {
            if (!filterValue) {
                this.internalValue = this.multiple ? [] : undefined;
                return;
            }

            // Convert filter value to id format for NewMultiselect
            if (Array.isArray(filterValue)) {
                this.internalValue = filterValue.map(item =>
                    typeof item === 'object' ? item[this.trackBy] : item
                );
            } else if (typeof filterValue === 'object') {
                this.internalValue = filterValue[this.trackBy];
            } else {
                this.internalValue = filterValue;
            }
        },

        forceUpdate(newVal) {
            this.updateInternalValue(newVal);
            this.emitTransformedValue();
        },

        async handleOpenMultiselect(isOpen) {
            if (!isOpen) return;

            if (this.slug.slug) {
                this.auxOptions = [];
                this.loading = true;

                this.changeBackground(this.auxOptions, this.label);
                const fetchedData = await this.fetchAllData(this.slug.slug);
                this.auxOptions = fetchedData || [];

                this.changeBackground(this.auxOptions, this.label);

                this.loading = false;
            } else {
                this.changeBackground(this.auxOptions, this.label);
            }
        },

        onChange(value) {
            // Convert id values back to original object format if needed
            let transformedValue;

            if (this.multiple) {
                transformedValue = value.map(id => {
                    const option = this.auxOptions.find(opt =>
                        (typeof opt === 'object' ? opt[this.trackBy] : opt) === id
                    );
                    return option || id;
                });
            } else {
                const option = this.auxOptions.find(opt =>
                    (typeof opt === 'object' ? opt[this.trackBy] : opt) === value
                );
                transformedValue = option || value;
            }

            this.$emit('input', transformedValue);
        },

        emitTransformedValue() {
            this.$emit('input', this.transformedFilter);
        }
    }

};
</script>
