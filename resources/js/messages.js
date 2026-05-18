import { localize } from '@vee-validate/i18n';

const messages={
    // Generates an English message locale generator
    generateMessage: localize('es', {
        messages: {
            required: 'El campo es obligatorio',
            email: 'El correo no es válido',
            min: 'La longitud mínima debe ser 4 caracteres', // 4
            max: 'Se ha superado la longitud máxima', // 100
        },
    }),
}
export default messages;
