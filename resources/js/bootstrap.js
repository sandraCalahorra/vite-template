import _ from 'lodash'
import axios from 'axios'

window._ = _
window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Si usas JWT en localStorage
const token = localStorage.getItem('token')
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}
