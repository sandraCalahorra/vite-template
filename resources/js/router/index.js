import { createRouter, createWebHistory } from 'vue-router'
import routes from './routes'
import formRoutes from './formRoutes'
import store from '../store'
import { jwtDecode } from 'jwt-decode'
import axios from 'axios'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...routes,
        ...formRoutes,
        { path: '/login', name: 'login', component: () => import('@/components/auth/Login.vue') },
        { path: '/recuperar_acceso', name: 'forgotPassword', component: () => import('@/components/auth/ForgotPassword.vue') },
        { path: '/restablecer_acceso', name: 'resetPassword', component: () => import('@/components/auth/ResetPassword.vue'),  props: (route) => ({ token: route.query.token, email: route.query.email }) },
        { path: '/:pathMatch(.*)*', meta: { requiresAuth: true }, component: () => import('@/components/PageNotFound.vue') }
    ],
})

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')

    if (to.meta.requiresAuth) {
        if (!token) {
            return next({ path: '/login' })
        }

        if (!isValidToken(token)) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            return next({ path: '/login' })
        }

        const decoded = jwtDecode(token)
        store.dispatch('auth/setUser', decoded.user)

        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    }

    next()
})

function isValidToken(token) {
    try {
        const decoded = jwtDecode(token)
        const currentTime = Math.floor(Date.now() / 1000)
        return decoded.exp > currentTime
    } catch {
        return false
    }
}

export default router
