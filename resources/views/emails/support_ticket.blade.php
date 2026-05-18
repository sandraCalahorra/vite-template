<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>{{ $subject }}</title>

<style type="text/css">
    body, td {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    body {
        background-color: #f4f6f8;
        margin: 0;
        padding: 0;
        width: 100% !important;
        color: #1f2937;
    }
    table { border-collapse: collapse; }

    .wrapper {
        width: 100%;
        background-color: #f4f6f8;
        padding: 30px 0;
    }

    .content {
        max-width: 600px;
        margin: 0 auto;
    }

    .logo {
        width: 160px;
        height: 50px;
        background: url('https://montearagon.com/img/montearagon.png') center / contain no-repeat;
    }

    .card {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    }

    .card-body {
        padding: 40px;
    }

    h1 {
        font-size: 20px;
        margin: 0 0 12px;
        font-weight: 600;
    }

    p {
        font-size: 15px;
        line-height: 1.6;
        margin: 0 0 14px;
    }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .info-box {
        background-color: #f9fafb;
        border-left: 4px solid #F08C30;
        padding: 18px;
        border-radius: 6px;
        margin: 22px 0;
    }

    .info-box td {
        font-size: 14px;
        padding: 4px 0;
    }

    .label {
        font-weight: 600;
        color: #374151;
        width: 90px;
    }

    .message-box {
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 20px;
        margin-top: 10px;
        font-size: 14px;
        line-height: 1.6;
        color: #1f2937;
    }

    .footer {
        text-align: center;
        padding-top: 25px;
    }

    .footer p {
        font-size: 12px;
        color: #9ca3af;
        margin: 0;
    }
</style>
</head>

<body>
<table class="wrapper" width="100%">
<tr>
<td align="center">

    <!-- Logo -->
    <table width="350" style="margin-bottom: 20px;">
        <tr>
            <td align="center">
                <div class="logo"></div>
            </td>
        </tr>
    </table>

    <!-- Content -->
    <table class="content" width="100%">
        <tr>
            <td>
                <table class="card" width="100%">
                    <tr>
                        <td class="card-body">

                            <h1>Nueva consulta de soporte</h1>
                            <p>Se ha recibido un nuevo ticket desde el formulario de contacto de <strong>Residencias Montearagón</strong>.</p>

                            <!-- Datos remitente -->
                            <div class="info-box">
                                <div class="section-title">DATOS DEL REMITENTE</div>
                                <table width="100%">
                                    @if($contactForm->name)
                                    <tr>
                                        <td class="label">Nombre</td>
                                        <td>{{ $contactForm->name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="label">Email</td>
                                        <td>{{ $contactForm->email }}</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Prioridad -->
                            @php
                                $priorityColor = '#3b82f6';
                                $priorityLabel = 'Normal';
                                if(isset($priority) && $priority === 'urgent') { $priorityColor = '#ef4444'; $priorityLabel = 'Urgente'; }
                                elseif(isset($priority) && $priority === 'high') { $priorityColor = '#f97316'; $priorityLabel = 'Alta'; }
                                elseif(isset($priority) && $priority === 'low') { $priorityColor = '#6b7280'; $priorityLabel = 'Baja'; }
                            @endphp

                            <p style="margin-top: 10px;">
                                <strong>Prioridad:</strong>
                                <span style="background: {{ $priorityColor }}; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700;">
                                    {{ $priorityLabel }}
                                </span>
                            </p>

                            <!-- Mensaje -->
                            <div class="section-title" style="margin-top: 24px;">MENSAJE</div>
                            <div class="message-box">
                                {!! $contactForm->message !!}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td class="footer">
                <p>© {{ date('Y') }} Residencias Montearagón · Todos los derechos reservados</p>
            </td>
        </tr>
    </table>

</td>
</tr>
</table>
</body>
</html>
