<template>
<AuthTemplate
    title="Restablecer contraseña"
    subtitle="Escribe una nueva contraseña."
    eyebrow="Nueva contraseña"
>
    <Form
        :validation-schema="validationSchema"
        ref="form"
        @submit="submitForm"
        @keyup.enter="submitForm"
        class="space-y-5"
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

        <ButtonAction
            label="Restablecer contraseña"
            color="primary"
            size="md"
            customClass="w-full uppercase"
            @action="submitForm"
        />

        <div class="flex justify-center">
            <button
                type="button"
                class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline"
                @click="redirectToLogin"
            >
                Volver al login {{ automaticRedirectMessage }}
            </button>
        </div>
    </Form>
</AuthTemplate>
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
