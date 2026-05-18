/**
 * ErrorLogger - Sistema de captura de errores global
 *
 * Captura automáticamente:
 * - Errores de JavaScript
 * - Promesas rechazadas
 * - Errores de API (fetch y XHR)
 * - Errores de red
 */
class ErrorLogger {
  constructor() {
    this.errors = [];
    this.maxErrors = 100; // Límite para evitar consumo excesivo de memoria
    this.setupGlobalHandlers();
  }

  /**
   * Configura los manejadores globales de errores
   */
  setupGlobalHandlers() {
    // Captura errores de JavaScript
    window.addEventListener('error', (event) => {
      this.logError({
        type: 'JavaScript Error',
        message: event.message,
        source: event.filename,
        line: event.lineno,
        column: event.colno,
        stack: event.error?.stack,
        timestamp: new Date().toISOString(),
        url: window.location.href
      });
    });

    // Captura promesas rechazadas
    window.addEventListener('unhandledrejection', (event) => {
      this.logError({
        type: 'Unhandled Promise Rejection',
        message: event.reason?.message || String(event.reason),
        stack: event.reason?.stack,
        timestamp: new Date().toISOString(),
        url: window.location.href
      });
    });

    // Intercepta fetch para capturar errores de API
    this.interceptFetch();

    // Intercepta XHR para capturar errores de red
    this.interceptXHR();
  }

  /**
   * Intercepta las llamadas fetch
   */
  interceptFetch() {
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
      const startTime = Date.now();
      try {
        const response = await originalFetch(...args);

        // Captura errores HTTP (4xx, 5xx)
        if (!response.ok) {
          const duration = Date.now() - startTime;
          let responseBody = null;

          try {
            // Intenta leer el cuerpo de la respuesta
            const clonedResponse = response.clone();
            responseBody = await clonedResponse.text();
          } catch (e) {
            // Si no se puede leer, continuar sin el cuerpo
          }

          this.logError({
            type: 'API Error (Fetch)',
            url: typeof args[0] === 'string' ? args[0] : args[0]?.url,
            method: args[1]?.method || 'GET',
            status: response.status,
            statusText: response.statusText,
            duration: `${duration}ms`,
            responseBody: responseBody ? responseBody.substring(0, 500) : null, // Limitar tamaño
            timestamp: new Date().toISOString(),
            pageUrl: window.location.href
          });
        }

        return response;
      } catch (error) {
        // Captura errores de red (timeout, connection refused, etc.)
        const duration = Date.now() - startTime;
        this.logError({
          type: 'Network Error (Fetch)',
          url: typeof args[0] === 'string' ? args[0] : args[0]?.url,
          method: args[1]?.method || 'GET',
          message: error.message,
          stack: error.stack,
          duration: `${duration}ms`,
          timestamp: new Date().toISOString(),
          pageUrl: window.location.href
        });
        throw error;
      }
    };
  }

  /**
   * Intercepta XMLHttpRequest
   */
  interceptXHR() {
    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function(...args) {
      this._method = args[0];
      this._url = args[1];
      this._startTime = Date.now();
      return originalOpen.apply(this, args);
    };

    XMLHttpRequest.prototype.send = function(...args) {
      this.addEventListener('error', () => {
        const duration = Date.now() - (this._startTime || 0);
        window.errorLogger?.logError({
          type: 'Network Error (XHR)',
          url: this._url,
          method: this._method,
          duration: `${duration}ms`,
          timestamp: new Date().toISOString(),
          pageUrl: window.location.href
        });
      });

      this.addEventListener('load', () => {
        if (this.status >= 400) {
          const duration = Date.now() - (this._startTime || 0);
          window.errorLogger?.logError({
            type: 'API Error (XHR)',
            url: this._url,
            method: this._method,
            status: this.status,
            statusText: this.statusText,
            response: this.responseText ? this.responseText.substring(0, 500) : null,
            duration: `${duration}ms`,
            timestamp: new Date().toISOString(),
            pageUrl: window.location.href
          });
        }
      });

      return originalSend.apply(this, args);
    };
  }

  /**
   * Registra un error
   * @param {Object} error - Objeto con información del error
   */
  logError(error) {
    // Ignorar errores conocidos de librerías externas que no son críticos
    const ignoredKeywords = ['datepicker', 'v-calendar', 'v-datepicker', 'dayindex', 'calendar', '429', 'too many requests'];
    const errorMessage = (error.message || error.statusText || '').toLowerCase();
    const errorStack = (error.stack || '').toLowerCase();

    const shouldIgnore = ignoredKeywords.some(keyword =>
      errorMessage.includes(keyword) || errorStack.includes(keyword)
    ) || error.status === 429;

    if (shouldIgnore) {
      // console.warn('[ErrorLogger] Ignorado error conocido de librería:', error.message);
      return;
    }

    // Evitar duplicados exactos en un corto período de tiempo
    const isDuplicate = this.errors.some(e =>
      e.type === error.type &&
      e.message === error.message &&
      e.url === error.url &&
      (new Date(error.timestamp) - new Date(e.timestamp)) < 1000 // Menos de 1 segundo
    );

    if (!isDuplicate) {
      this.errors.push(error);

      // Limitar el número de errores almacenados
      if (this.errors.length > this.maxErrors) {
        this.errors.shift(); // Eliminar el error más antiguo
      }

      console.error('[ErrorLogger]', error);

      // Emitir evento personalizado para que los componentes puedan reaccionar
      window.dispatchEvent(new CustomEvent('errorLogged', {
        detail: {
          error,
          totalErrors: this.errors.length
        }
      }));
    }
  }

  /**
   * Obtiene todos los errores registrados
   * @returns {Array} Array de errores
   */
  getErrors() {
    return [...this.errors];
  }

  /**
   * Obtiene el número de errores registrados
   * @returns {Number} Número de errores
   */
  getErrorCount() {
    return this.errors.length;
  }

  /**
   * Descarga un reporte de errores en formato HTML
   */
  downloadErrorReport() {
    const now = new Date();
    const timestamp = now.toLocaleString('es-ES', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });

    // Agrupar errores por tipo
    const errorsByType = {};
    this.errors.forEach(error => {
      const type = error.type || 'Unknown';
      if (!errorsByType[type]) {
        errorsByType[type] = [];
      }
      errorsByType[type].push(error);
    });

    // Generar HTML
    const html = `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Errores - ${timestamp}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; line-height: 1.5; color: #333; font-size: 14px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; color: white; padding: 20px 30px; border-bottom: 3px solid #34495e; }
        .header h1 { font-size: 20px; font-weight: 600; margin-bottom: 4px; }
        .header p { font-size: 13px; opacity: 0.85; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); background: #ecf0f1; border-bottom: 1px solid #bdc3c7; }
        .summary-item { padding: 15px 20px; border-right: 1px solid #bdc3c7; }
        .summary-item:last-child { border-right: none; }
        .summary-item label { display: block; font-size: 11px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .summary-item value { display: block; font-size: 18px; font-weight: 600; color: #2c3e50; }
        .content { padding: 20px 30px; }
        .error-group { margin-bottom: 30px; }
        .error-group-title { font-size: 14px; font-weight: 600; color: #2c3e50; padding: 8px 12px; background: #ecf0f1; border-left: 3px solid #3498db; margin-bottom: 10px; }
        .error-group-title .count { float: right; background: #3498db; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
        .error { border: 1px solid #e0e0e0; margin-bottom: 8px; border-radius: 3px; overflow: hidden; }
        .error-summary { padding: 10px 15px; background: #fafafa; cursor: pointer; user-select: none; border-bottom: 1px solid #e0e0e0; }
        .error-summary:hover { background: #f0f0f0; }
        .error-message { font-weight: 500; color: #c0392b; margin-bottom: 4px; font-size: 13px; }
        .error-message .toggle { float: right; color: #95a5a6; font-size: 10px; }
        .error-info { font-size: 12px; color: #7f8c8d; }
        .error-info span { margin-right: 15px; }
        .error-details { padding: 15px; background: white; display: none; font-size: 12px; }
        .error-details.show { display: block; }
        .detail-section { margin-bottom: 12px; }
        .detail-section:last-child { margin-bottom: 0; }
        .detail-label { font-weight: 600; color: #34495e; margin-bottom: 3px; font-size: 11px; text-transform: uppercase; }
        .detail-content { background: #f8f9fa; padding: 8px 10px; border-left: 2px solid #e0e0e0; font-family: 'Courier New', monospace; font-size: 11px; color: #2c3e50; overflow-x: auto; white-space: pre-wrap; word-break: break-all; }
        .stack-trace { background: #2c3e50; color: #ecf0f1; padding: 10px; font-family: 'Courier New', monospace; font-size: 11px; overflow-x: auto; white-space: pre-wrap; line-height: 1.4; border-radius: 3px; }
        .footer { background: #ecf0f1; padding: 15px 30px; border-top: 1px solid #bdc3c7; font-size: 11px; color: #7f8c8d; }
        .footer p { margin-bottom: 3px; }
        @media print { body { background: white; padding: 0; } .error-details { display: block !important; } .toggle { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte de Errores</h1>
            <p>${timestamp}</p>
        </div>
        <div class="summary">
            <div class="summary-item"><label>Total Errores</label><value>${this.errors.length}</value></div>
            <div class="summary-item"><label>Tipos</label><value>${Object.keys(errorsByType).length}</value></div>
            <div class="summary-item"><label>Navegador</label><value>${navigator.userAgent.includes('Chrome') ? 'Chrome' : navigator.userAgent.includes('Firefox') ? 'Firefox' : navigator.userAgent.includes('Safari') ? 'Safari' : 'Otro'}</value></div>
            <div class="summary-item"><label>Resolución</label><value>${window.innerWidth}x${window.innerHeight}</value></div>
        </div>
        <div class="content">
            ${Object.entries(errorsByType).map(([type, errors]) => `
                <div class="error-group">
                    <div class="error-group-title">${type}<span class="count">${errors.length}</span></div>
                    ${errors.map((error, index) => `
                        <div class="error">
                            <div class="error-summary" onclick="toggle(this)">
                                <div class="error-message">${error.message || error.statusText || 'Sin mensaje'} <span class="toggle">▼</span></div>
                                <div class="error-info">
                                    <span>${new Date(error.timestamp).toLocaleString('es-ES')}</span>
                                    ${error.url ? `<span>${error.url.substring(error.url.lastIndexOf('/') + 1)}</span>` : ''}
                                    ${error.status ? `<span>HTTP ${error.status}</span>` : ''}
                                    ${error.method ? `<span>${error.method}</span>` : ''}
                                </div>
                            </div>
                            <div class="error-details">
                                ${error.pageUrl ? `<div class="detail-section"><div class="detail-label">Página</div><div class="detail-content">${error.pageUrl}</div></div>` : ''}
                                ${error.source ? `<div class="detail-section"><div class="detail-label">Archivo</div><div class="detail-content">${error.source}:${error.line}:${error.column}</div></div>` : ''}
                                ${error.componentName ? `<div class="detail-section"><div class="detail-label">Componente Vue</div><div class="detail-content">${error.componentName}</div></div>` : ''}
                                ${error.responseData ? `<div class="detail-section"><div class="detail-label">Respuesta del Servidor</div><div class="detail-content">${JSON.stringify(error.responseData, null, 2)}</div></div>` : ''}
                                ${error.stack ? `<div class="detail-section"><div class="detail-label">Stack Trace</div><div class="stack-trace">${error.stack}</div></div>` : ''}
                                <div class="detail-section"><div class="detail-label">JSON Completo</div><div class="detail-content">${JSON.stringify(error, null, 2)}</div></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `).join('')}
        </div>
        <div class="footer">
            <p><strong>Sistema:</strong> ${navigator.userAgent}</p>
            <p><strong>Plataforma:</strong> ${navigator.platform} | <strong>Idioma:</strong> ${navigator.language} | <strong>Pantalla:</strong> ${window.screen.width}x${window.screen.height}</p>
        </div>
    </div>
    <script>
        function toggle(el) {
            const details = el.nextElementSibling;
            const icon = el.querySelector('.toggle');
            if (details.classList.contains('show')) {
                details.classList.remove('show');
                icon.textContent = '▼';
            } else {
                details.classList.add('show');
                icon.textContent = '▲';
            }
        }
    </script>
</body>
</html>`;

    const blob = new Blob([html], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte-errores-${new Date().toISOString().replace(/[:.]/g, '-')}.html`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  /**
   * Limpia todos los errores registrados
   */
  clearErrors() {
    this.errors = [];
    window.dispatchEvent(new CustomEvent('errorsCleared'));
  }
}

export default ErrorLogger;
