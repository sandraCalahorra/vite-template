<template>
<div class="flex h-screen w-full bg-slate-100">

    <!-- PANEL INSTITUCIONAL -->
    <div class="hidden lg:flex w-1/2 relative bg-[linear-gradient(45deg,_var(--primary)_20%,_var(--primary-light)_80%)] text-white flex-col justify-between p-16">

        <div class="flex items-center gap-4">
            <img src="/img/montearagon.png" class="h-14"/>
            <div>
                <h1 class="text-xl font-semibold">Residencia CPIFP Montearagón</h1>
                <p class="text-sm opacity-80">Sistema de gestión residencial</p>
            </div>
        </div>

        <div class="max-w-md space-y-6">
            <h2 class="text-3xl font-bold leading-tight">
                Plataforma de gestión
                para residentes,
                familiares y administración
            </h2>

            <p class="text-sm opacity-90">
                Gestiona habitaciones, asistencia a comedor,
                incidencias y datos de alumnos desde un
                entorno centralizado y seguro.
            </p>
        </div>

        <div class="text-xs opacity-70">
            © {{ new Date().getFullYear() }} CPIFP Montearagón
        </div>

    </div>


    <!-- PANEL RESET PASSWORD -->
    <div
        class="flex flex-col justify-center items-center w-full lg:w-1/2 p-10
        bg-[linear-gradient(45deg,_var(--primary)_20%,_var(--primary-light)_80%)]
        lg:bg-none lg:bg-gray-50"
    >

        <div class="w-full max-w-md bg-white p-10 rounded-2xl shadow-xl">

            <!-- HEADER -->
            <div class="flex flex-col items-center mb-8">
                <h2 class="text-lg font-bold text-slate-800 uppercase text-center">
                    Restablecer contraseña
                </h2>
                <p class="text-sm text-slate-500 text-center">
                    Introduce tu nueva contraseña a continuación.
                </p>
            </div>

            <Form
                :validation-schema="validationSchema"
                ref="form"
                @submit="submitForm"
                @keyup.enter="submitForm"
                class="space-y-6"
            >

                <StyledInputText
                    name="password"
                    v-model="credentials.password"
                    :type="!is_enabled ? 'password' : 'text'"
                    placeholder="Nueva contraseña"
                    :rules="validationRules.password"
                />

                <StyledInputText
                    name="password_confirmation"
                    v-model="credentials.password_confirmation"
                    :type="!is_enabled ? 'password' : 'text'"
                    placeholder="Confirmar contraseña"
                    :rules="validationRules.password_confirmation"
                />

                <div class="flex justify-center">
                    <label
                        class="text-primary hover:text-primary-light cursor-pointer flex text-sm hover:underline"
                        @click="redirectToLogin"
                    >
                        Volver a Login {{ automaticRedirectMessage }}
                    </label>
                </div>

                <button
                    type="submit"
                    class="relative block w-full py-2 px-3 border border-transparent rounded-md bg-primary hover:bg-primary-light focus:outline-none focus:shadow-outline"
                >
                    <span class="text-sm leading-6 font-semibold text-white tracking-wide uppercase">
                        Restablecer Contraseña
                    </span>
                </button>

            </Form>

        </div>

    </div>

</div>
</template>

<script>
import * as yup from 'yup';
import { Form, Field, ErrorMessage } from 'vee-validate';
import moment from "moment";

export default {
    name: "ForgetPassword",

    components: {
        Form,
        ErrorMessage,
        Field,
    },

    provide() {
        return {
            validationRules: this.validationRules,
        };
    },

    props: ['token', 'email'],
    data() {
        return {
            showErrors: false,

            credentials: {
                password: "",
                password_confirmation: "",
            },
            is_enabled: false,
            validationRules: {
                password: yup.string()
                .required('La contraseña es obligatoria')
                .min(4, 'La contraseña tiene que tener al menos 4 caracteres'),
                password_confirmation: yup.string()
                .required('La contraseña es obligatoria')
                .min(4, 'La contraseña tiene que tener al menos 4 caracteres')
                .oneOf([yup.ref('password'), null], 'Las contraseñas no coinciden'),
            },
            countdown: null,
            endTime: moment().add(7, "seconds"),
            interval: null,
        };
    },

    computed: {
        validationSchema() {
            return yup.object().shape({
                password: this.validationRules.password,
                password_confirmation: this.validationRules.password_confirmation,
            });
        },
        automaticRedirectMessage(){
            return this.countdown != null ? ` (Redirigiendo automáticamente en ${this.countdown} segundos)` : ''
        }
    },
    mounted(){

    },

    methods: {
        async submitForm(){
            const result = await this.$refs.form.validate();

            // If validation fails, return to avoid proceeding
            if (await this.validateForm(result)) return;

            // Proceed if validation is successful
            if (result.valid) {
                try {
                    await this.$axios.post('/api/reset-password', {
                        email: this.email,
                        token: this.token,
                        password: this.credentials.password,
                        password_confirmation: this.credentials.password_confirmation
                    });
                    this.openAlert('success', 'Se ha modificado la contraseña')
                    this.updateCountdown();
                    setTimeout(() => {
                        this.$router.push({name: 'login'});
                    clearInterval(this.interval);
                    }, 7000);
                } catch (error) {
                    console.error(error.message);

                    // Handle specific expired link error
                    if (error.response && error.response.data.message === 'The password reset link has expired. Please request a new one.') {
                        this.openAlert('error', 'El enlace para restablecer la contraseña ha expirado. Por favor, solicite uno nuevo.');
                    }
                    // Handle other types of errors
                    else {
                        this.openAlert('error', 'Ha ocurrido un error, si persiste contacte con un Administrador');
                        console.error(error);
                    }
                }
            }
        },

        updateCountdown() {
            this.endTime = moment().add(7, "seconds");
            this.interval = setInterval(() => {
                const now = moment();
                const duration = moment.duration(this.endTime.diff(now));

                this.countdown = duration.seconds();
            }, 1000);
        },

        redirectToLogin(){
            this.$router.push({name: 'login'});
        },

        async validateForm(result) {
            // Convert result.errors into an array for easier manipulation
            const errors = Object.entries(result.errors);

            if (errors.length){
                let resultText = [
                    ...errors.map(([_, msg]) => msg) // Include all errors from form validation
                ].filter(Boolean).join('<br />'); // Remove empty messages and join with line breaks

                this.openAlert("info", "Rellene correctamente el formulario", resultText);

                return true;
            }
            // Return false if no validation errors found
            return false;
        },
    }
};
</script>
