<template>
    <div class="min-h-screen bg-slate-50/40 pb-12">
        <TheTopbar title="MI PERFIL" />

        <LoadingSpinner v-show="isLoading" />

        <Section v-show="!isLoading" class="py-8">
            <div class="max-w-7xl mx-auto px-4 lg:px-8">

                <!-- CABECERA: Identidad Ultra-Minimal (Estilo Google Accounts) -->
                <div class="flex flex-col items-center mb-6">
                    <ProfilePictureManager
                        v-if="id"
                        :userId="id"
                        :editable="canEditMyProfile"
                        v-model="newItem.profile_picture"
                        :initialBase64="newItem.profile_picture_base64"
                        @refresh-session="refreshAuthenticatedSession"
                        @picture-changed="(data) => { newItem.profile_picture_base64 = data.base64 }"
                    />
                    <div class="mt-3 text-center">
                        <h2 class="text-xl font-bold text-slate-800">{{ newItem.name }} {{ newItem.last_name }}</h2>
                        <div class="mt-1 flex items-center justify-center gap-2">
                            <span class="text-xs font-bold text-primary uppercase">{{ userRoleLabel }}</span>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO: Tarjeta Compacta con Divisores (Estilo Apple/Google) -->
                <div class="bg-white rounded-lg border border-slate-200/50 mb-6">
                    <Form :validation-schema="validationSchema" ref="form" @submit="submitUsers" class="divide-y divide-slate-100">

                        <!-- Sección: Datos Personales -->
                        <div class="p-6 sm:p-7">
                            <div class="flex items-center gap-2.5 mb-5">
                                <div class="text-primary-700"><IconUser class="size-4" /></div>
                                <h3 class="text-xs font-bold text-primary-700 uppercase">Datos Personales</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
                                <InputWrapper label="Nombre" required>
                                    <InputText name="name" v-model="newItem.name" :rules="validationRules.name" :disabled="!canEditMyProfile" class="rounded-xl border-slate-100 focus:ring-primary/5 text-sm py-2.5" />
                                </InputWrapper>
                                <InputWrapper label="Primer apellido" required>
                                    <InputText name="last_name" v-model="newItem.last_name" :rules="validationRules.last_name" :disabled="!canEditMyProfile" class="rounded-xl border-slate-100 text-sm py-2.5" />
                                </InputWrapper>
                                <InputWrapper label="DNI" required>
                                    <InputText name="dni" v-model="newItem.dni" :rules="validationRules.dni" :disabled="!canEditMyProfile" class="rounded-xl border-slate-100 text-sm py-2.5" />
                                </InputWrapper>
                            </div>
                        </div>

                        <!-- Sección: Contacto -->
                        <div class="p-6 sm:p-7">
                            <div class="flex items-center gap-2.5 mb-5">
                                <div class="text-primary-700"><IconEnvelopeCheckSolid class="size-4" /></div>
                                <h3 class="text-xs font-bold text-primary-700 uppercase">Contacto</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
                                <InputWrapper label="Email" required class="lg:col-span-2">
                                    <InputText name="email" type="email" v-model="newItem.email" :rules="validationRules.email" :disabled="!canEditMyProfile" class="rounded-xl border-slate-100 text-sm py-2.5" />
                                </InputWrapper>
                                <InputWrapper
                                    label="Teléfono móvil"
                                    required
                                    info="Solo se admiten teléfonos móviles españoles: 9 dígitos y empiezan por 6 o 7. Si necesitas guardar un fijo, usa un campo de observaciones."
                                >
                                    <InputText name="phone" v-model="newItem.phone" :rules="validationRules.phone" :disabled="!canEditMyProfile" class="rounded-xl border-slate-100 text-sm py-2.5" />
                                </InputWrapper>
                            </div>
                        </div>

                        <!-- Sección: Seguridad -->
                        <div class="p-6 sm:p-7 bg-slate-50/20">
                            <div class="flex items-center gap-2.5 mb-5">
                                <div class="text-primary-700"><IconShieldLockSolid class="size-4" /></div>
                                <h3 class="text-xs font-bold text-primary-700 uppercase">Seguridad</h3>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-5">
                                <InputWrapper label="Actualizar Contraseña">
                                    <div class="relative group">
                                        <InputText
                                            name="password"
                                            :type="!isShowingPassword ? 'password' : 'text'"
                                            v-model="newItem.password"
                                            :disabled="!canEditMyProfile"
                                            :rules="validationRules.password"
                                            placeholder="••••••••"
                                            class="rounded-xl border-slate-100 pr-12 bg-white group-hover:border-slate-200 transition-colors text-sm py-2.5"
                                        />
                                        <button
                                            type="button"
                                            :disabled="!canEditMyProfile"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 p-1 text-slate-300 hover:text-primary transition-colors focus:outline-none"
                                            @click="isShowingPassword = !isShowingPassword"
                                        >
                                            <IconEye v-if="!isShowingPassword" class="size-3.5" />
                                            <IconEyeSlash v-else class="size-3.5" />
                                        </button>
                                    </div>
                                </InputWrapper>
                            </div>
                        </div>

                    </Form>
                </div>

                <!-- Botones de Acción (Componente Estandarizado) -->
                <FormControls
                    v-if="canEditMyProfile"
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
import { defineComponent, nextTick } from 'vue';
import { Form, Field, ErrorMessage } from 'vee-validate';
import AdminMixin from "@/components/mixins/AdminMixin.vue";
import * as yup from 'yup';

const spanishMobilePhoneRegex = /^[67]\d{8}$/;
const spanishMobilePhoneMessage = 'El teléfono móvil debe tener 9 dígitos y empezar por 6 o 7';

export default defineComponent({
    name: 'MyProfile',
    components: {
        Form,
        ErrorMessage,
        Field,
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
                last_name: yup.string().required('El primer apellido es obligatorio.'),
                dni: yup.string().required('El DNI es obligatorio.'),
                phone: yup.string()
                    .required('El teléfono móvil es obligatorio.')
                    .matches(spanishMobilePhoneRegex, spanishMobilePhoneMessage),
                email: yup.string().email('Email inválido').required('Email es obligatorio'),
                password: yup.string().min(4, 'Mínimo 4 caracteres')
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
        canEditMyProfile() {
            return this.$can('my_profile', 'edit')
        },
        validationSchema() {
            return yup.object().shape({
                name: this.validationRules.name,
                last_name: this.validationRules.last_name,
                dni: this.validationRules.dni,
                phone: this.validationRules.phone,
                email: this.validationRules.email,
                password: this.newItem.password ? this.validationRules.password : yup.string()
            });
        },
        userRoleLabel() {
            const levels = {
                0: 'Superadministrador',
                10: 'Administrador',
                20: 'Usuario',
            };
            return levels[this.user?.level] || 'Usuario';
        }
    },
    methods: {

        async refreshAuthenticatedSession() {
            try {
                const response = await this.$axios.post('/api/refresh');
                const token = response?.data?.token;
                const authenticatedUser = response?.data?.user;

                if (token) {
                    localStorage.setItem('token', token);
                    this.$axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                }

                if (authenticatedUser) {
                    this.$store.dispatch('auth/setUser', authenticatedUser);
                }
            } catch (error) {
                this.showDevError(error);
            }
        },

        async loadUsersData(id) {
            this.newItem = await this.fetchAllData(`/api/users/${id}`);
            this.isLoading = false;
        },



        async submitUsers(flag = false) {
            if (!this.canEditMyProfile || this.submitting) return;
            this.submitting = true;
            const result = await this.$refs.form.validate();
            if (!result.valid) {
                this.submitting = false;
                return this.openAlert("info", "Rellene correctamente el formulario");
            }
            try {
                this.isLoading = true;
                this.isEditing = this.id;
                await this.$axios.put(`api/users/${this.id}`, this.newItem);
                await this.loadUsersData(this.id);
                await this.refreshAuthenticatedSession();

                this.openAlert("success", "Perfil actualizado con éxito.", null);

                if (flag) {
                    this.$router.push({name: 'home'});
                } else {
                    this.submitting = false;
                    this.isLoading = false;
                }

            } catch (error) {
                this.submitting = false;
                console.error(error);
                this.openAlert("error", error);
            }
        },
    },

    beforeUnmount() {
    }
});
</script>

<style scoped>
/* Transiciones para el overlay de la foto */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.profile-menu-enter-active,
.profile-menu-leave-active {
  transition: opacity 0.16s ease, transform 0.16s ease;
}

.profile-menu-enter-from,
.profile-menu-leave-to {
  opacity: 0;
  transform: translate(-50%, -0.35rem) scale(0.97);
}
</style>
