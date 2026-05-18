<template>
    <div
        v-if="showModal"
        class="fixed z-[1000] px-4 pb-4 inset-0 flex items-end sm:items-center justify-center text-sm"
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
                    :class="modalWidthClass"
                    style="max-width: 90vw"
                >
                    <div v-if="showHeader" class="px-6 py-4 bg-white border-b border-gray-100">
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
                        :class="bodyClass"
                        :style="bodyStyle"
                    >
                        <div v-if="isMessageModal" class="flex items-start gap-4">
                            <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border" :class="messageIconWrapperClass">
                                <span class="text-xl font-bold leading-none" :class="messageIconTextClass">
                                    {{ messageSymbol }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl font-semibold leading-7 text-slate-900">
                                    {{ title }}
                                </h2>
                                <div class="mt-2 text-sm leading-6 text-slate-500">
                                    <slot name="body" />
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="staticMultiselect" style="max-height: 65vh; overflow-y: auto">
                                <slot name="body" />
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="hasFooter == true"
                        class="modal-footer-actions flex w-full flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:w-auto sm:flex-row-reverse sm:gap-0 sm:space-x-3 sm:space-x-reverse"
                        :class="{ 'px-5 py-4': isMessageModal }"
                    >
                        <slot name="action" />
                        <ButtonAction
                            v-if="actionButton"
                            size="sm"
                            :label="actionButton.text"
                            :color="actionButton.disabled ? 'gray' : 'primary'"
                            @click="actionButton.action"
                            :disabled="actionButton.disabled"
                            class="w-full sm:w-auto"
                        />
                        <ButtonAction
                            v-if="!blockModal && !actionButton"
                            :label="'Cancelar'"
                            size="sm"
                            color="white"
                            class="w-full border border-gray-300 shadow-sm text-gray-700 hover:bg-gray-50 sm:w-auto"
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
        messageType: {
            type: String,
            default: '',
        },
        destructive: {
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
        resolvedMessageType() {
            if (this.messageType) return this.messageType;
            if (this.destructive) return 'danger';
            return '';
        },

        isMessageModal() {
            return !!this.resolvedMessageType;
        },

        showHeader() {
            return this.hasHeader && !this.isMessageModal;
        },

        modalWidthClass() {
            if (this.isMessageModal) {
                return 'sm:w-[32rem]';
            }

            return this.large ? 'sm:w-192 lg:w-[70rem]' : 'sm:w-160 lg:w-200';
        },

        bodyClass() {
            return this.isMessageModal ? 'px-5 py-6' : 'px-6 py-6';
        },

        bodyStyle() {
            return this.isMessageModal ? '' : '';
        },

        messageSymbol() {
            return {
                danger: '!',
                success: '✓',
                warning: '!',
                info: 'i',
            }[this.resolvedMessageType] || 'i';
        },

        messageIconWrapperClass() {
            return {
                danger: 'border-red-100 bg-red-50',
                success: 'border-emerald-100 bg-emerald-50',
                warning: 'border-amber-100 bg-amber-50',
                info: 'border-sky-100 bg-sky-50',
            }[this.resolvedMessageType] || 'border-slate-100 bg-slate-50';
        },

        messageIconTextClass() {
            return {
                danger: 'text-red-600',
                success: 'text-emerald-600',
                warning: 'text-amber-600',
                info: 'text-sky-600',
            }[this.resolvedMessageType] || 'text-slate-600';
        },

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

<style>
@media (max-width: 639px) {
    .modal-footer-actions > *,
    .modal-footer-actions button,
    .modal-footer-actions a {
        width: 100%;
    }
}
</style>
