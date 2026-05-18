<template>
    <div>
        <TheTopbar title="MI PERFIL" />

        <LoadingSpinner v-show="isLoading" />
        <Section v-show="!isLoading">
             <div class="w-full max-w-4xl p-6 mx-auto space-y-6 bg-white shadow-md rounded-xl">
                    <h2 class="text-xl font-semibold tracking-wide text-center uppercase text-primary">
                        DATOS DEL PERFIL
                    </h2>
                    <hr>

                <Form :validation-schema="validationSchema" class="grid grid-cols-1 gap-6 md:grid-cols-2" ref="form" @submit="submitUsers">
                    <div class="col-span-full grid grid-cols-2 gap-x-4 gap-y-3">
                        <InputWrapper label="Nombre" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="name"
                                type="text"
                                v-model="newItem.name"
                                :rules="validationRules.name"
                            />
                        </InputWrapper>
                        <InputWrapper label="Primer apellido" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="last_name"
                                type="text"
                                v-model="newItem.last_name"
                                :rules="validationRules.last_name"
                            />
                        </InputWrapper>
                        <InputWrapper label="Segundo apellido" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="last_name2"
                                type="text"
                                v-model="newItem.last_name2"
                                :rules="validationRules.last_name2"
                            />
                        </InputWrapper>
                    </div>

                    <div class="col-span-full grid grid-cols-2 gap-x-4 gap-y-3">
                        <InputWrapper label="DNI" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="dni"
                                type="text"
                                v-model="newItem.dni"
                                :rules="validationRules.dni"
                            />
                        </InputWrapper>
                        <InputWrapper label="Teléfono" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="phone"
                                type="text"
                                v-model="newItem.phone"
                                :rules="validationRules.phone"
                            />
                        </InputWrapper>
                    </div>

                    <div class="col-span-full grid grid-cols-2 gap-x-4 gap-y-3">
                        <InputWrapper label="Correo electrónico" required class="col-span-2 md:col-span-1">
                            <InputText
                                name="email"
                                type="email"
                                v-model="newItem.email"
                                :rules="validationRules.email"
                            />
                        </InputWrapper>
                        <InputWrapper label="Contraseña" class="col-span-2 md:col-span-1" :info="infoPassword">
                            <span class="flex w-full gap-2">
                                <InputText
                                    class="w-full"
                                    name="password"
                                    :type="!isShowingPassword ? 'password' : 'text'"
                                    v-model="newItem.password"
                                    :rules="validationRules.password"
                                    :placeholder="'**********'"
                                />
                                <span class="flex justify-end mt-1">
                                    <IconEye class="cursor-pointer w-5 h-5 min-w-5" v-if="!isShowingPassword" @click="isShowingPassword=!isShowingPassword" />
                                    <IconEyeSlash class="cursor-pointer w-5 h-5 min-w-5" v-else @click="isShowingPassword=!isShowingPassword" />
                                </span>
                            </span>
                        </InputWrapper>
                    </div>
                </Form>

                <FormControls
                    :newItem="newItem"
                    :saveAndExit="true"
                    @submitForm="submitUsers()"
                    @submitFormAndExit="submitUsers(true)"
                />
            </div>
        </Section>
    </div>
</template>

<script>
import { ref, defineComponent } from 'vue';
import { Form, Field, ErrorMessage } from 'vee-validate';
import AdminMixin from "@/components/mixins/AdminMixin.vue";
import * as yup from 'yup';

export default defineComponent({
    components: {
        Form,
        ErrorMessage,
        Field
    },
    mixins: [AdminMixin],
    data() {
        return {
            id: '',
            isLoading: false,
            isShowingPassword: false,
            infoPassword: '',
            validationRules: {
                name: yup.string().max(255, 'Nombre debe tener como máximo 255 caracteres.').required('El nombre es obligatorio.'),
                email: yup.string().email('Email inválido').required('Email es obligatorio'),
            }
        };
    },
    async mounted() {
        this.id = this.user?.id;
        if (this.id) {
            this.isLoading = true;
            await this.loadUsersData(this.id);
        }
        this.infoPassword = `
            Este campo puede estar en blanco, si es el caso <strong> no se modificará </strong> la contraseña. <br>
            En caso contrario, <strong> se actualizará </strong> por la introducida
        `;
        this.isLoading = false;
    },
    computed: {
        validationSchema() {
            return yup.object().shape({
                name: this.validationRules.name,
                email: this.validationRules.email,
                password: this.newItem.password ? this.validationRules.password : ''
            });
        }
    },
    methods: {
        async loadUsersData(id) {
            this.newItem = await this.fetchAllData(`/api/users/${id}`);
            this.isLoading = false;
        },
        async submitUsers(flag = false) {
            if (this.submitting) return; // prevent multiple submissions
            this.submitting = true;
            const result = await this.$refs.form.validate();
            if (!result.valid) {
                this.submitting = false;
                return this.openAlert("info", "Rellene correctamente el formulario");
            }
            try {
                this.isLoading = true;
                this.isEditing = this.id;
                await this.storeOrUpdateItem('api/users/create', `api/users/${this.id}`);

                let message = "";
                if (this.isEditing) {
                    message = "Registro actualizado con éxito.";
                } else {
                    message = "Registro añadido con éxito.";
                }
                this.openAlert("success", message, null);

                if (flag) {
                    if (this.user?.level >= 30) {
                        this.$router.push({name: 'home'});
                    } else {
                        this.$router.push({ name: 'expedients' });
                    }
                } else {
                    this.submitting = false;
                    this.isLoading = false;
                }

            } catch (error) {
                this.submitting = false; // re-enable the button in case of an error
                console.error(error);
                this.openAlert("error", error);
            }
        }
    }
});
</script>
