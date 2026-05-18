@component('mail::message')
# ¡Hola!

Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en **Residencias Montearagón**.

Si fuiste tú quien la solicitó, haz clic en el botón de abajo para establecer una nueva contraseña:

<!-- @component('mail::button', ['url' => $resetUrl])
Restablecer contraseña
@endcomponent -->
<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 20px auto;">
    <tr>
        <td align="center">
            <a href="{{ $resetUrl }}" target="_blank" class="reset-link" >
                Restablecer contraseña
            </a>
        </td>
    </tr>
</table>

Este enlace es válido solo durante los próximos **60 minutos**, así que asegúrate de utilizarlo antes de que caduque.

Si no has solicitado este cambio, puedes ignorar este correo. Tu contraseña actual no se verá afectada, y no es necesario que realices ninguna acción.

Gracias por confiar en **Residencias Montearagón**.



Saludos cordiales, El equipo de Residencias Montearagón.


.

@endcomponent

<style>
    table.content, td.body {
        background-color: #f2f2f2 !important;
    }

    td.header a:first-child {
        display: inline-block;
        width: 100px; /* Set width for the image */
        height: 50px; /* Set height for the image */
        background-image: url('https://montearagon.ecomputer.es/img/montearagon.png'); /* URL to the image */
        background-size: contain; /* Make sure the image fits within the link */
        background-repeat: no-repeat;
        color: transparent; /* Hide link text */
        text-decoration: none;
    }

    td p:last-child {
        color: transparent; /* Hides original text */
        font-size: 0; /* Prevents any leftover visual artifacts */
        position: relative;
    }

    .reset-link {
        display: inline-block;
        padding: 10px 15px;
        color: #ffffff;
        text-decoration: none;
        font-weight: bold;
        font-size: 16px;
        border-radius: 5px;
        background-color: #455B7E;
        text-align: center;
    }

    .reset-link:hover {
        background-color: #CA612C !important;
    }
</style>
