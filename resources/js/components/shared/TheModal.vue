<template>
    <div
        v-if="showModal"
        class="fixed z-50 px-4 pb-4 inset-0 flex items-end sm:items-center justify-center text-sm"
    >
        <transition
            enter-active-class="duration-500"
            leave-active-class="duration-500"
            enter-class="opacity-0"
            enter-to-class="opacity-100"
            leave-class="opacity-100"
            leave-to-class="opacity-0"
            appear
            @before-leave="backdropLeaving = true"
            @after-leave="backdropLeaving = false"
        >
            <div v-if="showBackdrop">
                <div
                    class="absolute inset-0 bg-gray-900 bg-opacity-40 backdrop-filter backdrop-blur-sm transition-opacity"
                    @click="!blockModal ? close() : ''"
                />
            </div>
        </transition>

        <transition
            enter-active-class="ease-out duration-300"
            leave-active-class="ease-in duration-200"
            enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            appear
            @before-leave="cardLeaving = true"
            @after-leave="cardLeaving = false"
        >
            <div v-if="showContent" class="relative z-50">
                <div
                    class="fixed bottom-0 inset-x-0 m-4 sm:relative bg-white rounded-lg shadow-2xl overflow-hidden border border-gray-100 transform transition-all"
                    :class="
                        large ? 'sm:w-192 lg:w-[70rem]' : 'sm:w-160 lg:w-200'
                    "
                    style="max-width: 90vw"
                >
                    <div v-if="hasHeader" class="px-6 py-4 bg-white border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2
                                class="font-semibold text-lg leading-6 text-gray-900"
                                :class="titleColor"
                            >
                                {{ title }}
                            </h2>

                            <slot name="header-after-title" /><!-- icon after title -->

                        </div>
                    </div>
                    <div
                        class="px-6 py-6 staticMultiselect"
                        style="max-height: 65vh; overflow-y: auto"
                    >
                        <slot name="body" />
                    </div>
                    <div
                        v-if="hasFooter == true"
                        class="bg-gray-50 border-t border-gray-100 px-6 py-4 sm:flex sm:flex-row-reverse space-x-3 space-x-reverse space-y-3 sm:space-y-0 text-sm"
                    >
                        <slot name="action" />
                        <ButtonAction
                            v-if="actionButton"
                            size="sm"
                            :label="actionButton.text"
                            :color="actionButton.disabled ? 'gray' : 'primary'"
                            @click="actionButton.action"
                            :disabled="actionButton.disabled"
                        />
                        <ButtonAction
                            v-if="!blockModal && !actionButton"
                            :label="'Cancelar'"
                            size="sm"
                            color="white"
                            class="border border-gray-300 shadow-sm text-gray-700 hover:bg-gray-50"
                            @click.prevent="close"
                        />
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    emits: ["close"],

    props: {
        open: {
            type: Boolean,
            required: true,
        },
        blockModal: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            required: true,
            default: "Título",
        },
        titleColor: {
            type: String,
            default: '',
        },
        large: {
            type: Boolean,
            default: false,
        },
        actionButton: {
            type: Object,
            default: () => null,
        },
        hasHeader: {
            type: Boolean,
            default: true,
        },
        hasFooter: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            showModal: false,
            showBackdrop: false,
            showContent: false,
            backdropLeaving: false,
            cardLeaving: false,
        };
    },

    computed: {
        leaving() {
            return this.backdropLeaving || this.cardLeaving;
        },
    },

    watch: {
        open: {
            handler: function (newValue) {
                if (newValue) {
                    this.show();
                } else {
                    this.close();
                }
            },
            immediate: true,
        },

        leaving(newValue) {
            if (newValue === false) {
                this.showModal = false;
                this.$emit("close");
            }
        },
    },

    methods: {
        show() {
            this.showModal = true;
            this.showBackdrop = true;
            this.showContent = true;
        },

        close() {
            this.showBackdrop = false;
            this.showContent = false;
        },
    },
};
</script>

<style></style>
