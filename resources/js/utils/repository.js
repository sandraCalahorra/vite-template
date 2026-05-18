import axios from 'axios';

axios.defaults.baseURL = "/";

// Add a request interceptor
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor para capturar errores de API
axios.interceptors.response.use(
  (response) => {
    // Si la respuesta es exitosa, simplemente la devolvemos
    return response;
  },
  (error) => {
    // Capturar errores de API
    if (window.errorLogger) {
      const errorData = {
        type: 'API Error (Axios)',
        timestamp: new Date().toISOString(),
        url: error.config?.url,
        method: error.config?.method?.toUpperCase(),
        pageUrl: window.location.href
      };

      if (error.response) {
        // El servidor respondió con un código de estado fuera del rango 2xx
        errorData.status = error.response.status;
        errorData.statusText = error.response.statusText;
        errorData.responseData = error.response.data;
        errorData.headers = error.response.headers;
      } else if (error.request) {
        // La petición se hizo pero no se recibió respuesta
        errorData.type = 'Network Error (Axios)';
        errorData.message = error.message;
        errorData.code = error.code; // Ej: ECONNABORTED, ERR_NETWORK
      } else {
        // Algo pasó al configurar la petición
        errorData.type = 'Request Configuration Error (Axios)';
        errorData.message = error.message;
      }

      window.errorLogger.logError(errorData);
    }

    return Promise.reject(error);
  }
);

export default axios;

