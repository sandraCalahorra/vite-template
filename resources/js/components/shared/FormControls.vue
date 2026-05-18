<template>
    <div class="fixed bottom-3 right-3 z-40 flex flex-col gap-3 sm:flex-row sm:gap-3">
        <!-- Save & Exit Button -->
        <ButtonAction
            v-if="saveAndExit && !excludedRoutes.includes($route.name)"
            :label="newItem.id === undefined ? 'Guardar y salir' : 'Guardar cambios y salir'"
            color="gray"
            @click="handleSubmit('saveFormAndExit')"
        >
            <IconArrowUturnLeft class="text-white w-5" />
        </ButtonAction>

        <!-- Save Button -->
        <ButtonAction
            :label="newItem.id === undefined && !excludedRoutes.includes($route.name) ? 'Guardar' : 'Guardar cambios'"
            color="primary"
            @click="handleSubmit('saveForm')"
        >
            <IconPlus v-if="newItem.id === undefined && !excludedRoutes.includes($route.name)" class="text-white w-5" />
            <IconFloppy v-else class="text-white size-5" />
        </ButtonAction>
    </div>
</template>

<script>
    export default {
        name: "FormControls",

        props: {
            newItem: {
                type: Object,
                required: true,
            },
            saveAndExit: {
                type: Boolean,
                default: false
            },
            alreadyExistNif: {
                type: Boolean
            },
            phoneError: {
                type: Boolean,
                default: false
            },
            phone2Error: {
                type: Boolean,
                default: false
            },
            postalCodeError: {
                type: Boolean,
                default: false
            },
            selectedTypes: {
                type: Array,
                default: () => []
            }
        },

        data() {
            return {
                lastAction: '',
                excludedRoutes: [
                    'info',
                ],
            };
        },

        methods: {
            handleSubmit(action) {
                this.lastAction = action;
                if (this.newItem.id == undefined && this.newItem.nif && this.newItem.name && !this.phoneError && !this.phone2Error && !this.newItem.postalCodeError) {
                    if (action == 'saveForm') {
                        this.submitForm();
                        return;
                    }
                    if (action == 'saveFormAndExit') {
                        this.submitFormAndExit();
                        return;
                    }
                } else {
                    if (action == 'saveForm') {
                        this.submitForm();
                        return;
                    }
                    if (action == 'saveFormAndExit') {
                        this.submitFormAndExit();
                        return;
                    }
                }
            },

            submitForm() {
                this.$emit("submitForm");
            },

            submitFormAndExit() {
                this.$emit("submitFormAndExit");
            },
        },
    };
</script>
