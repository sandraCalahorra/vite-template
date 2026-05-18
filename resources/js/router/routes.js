export default [
    {
        path: '/',
        name: 'home',
        meta: { requiresAuth: true, title: 'Inicio - Template'},
        component: () => import('@/components/views/Home.vue')
    },
    {
        path: '/usuarios',
        name: 'users',
        alias: '/usuarios',
        meta: { requiresAuth: true, title: 'Usuarios - Template'},
        component: () => import('@/components/views/Users.vue')
    },
    {
        path: '/roles',
        name: 'roles',
        meta: { requiresAuth: true, title: 'Roles - Template'},
        component: () => import('@/components/views/Roles.vue')
    },
    {
        path: '/mi-prefil',
        name: 'myProfile',
        meta: {requiresAuth: true, title: 'Mi perfil - Template'},
        component: () => import('@/components/views/forms/MyProfile.vue')
    },
    {
        path: '/ajustes',
        name: 'settings',
        meta: {requiresAuth: true, title: 'Ajustes de la aplicación - Template' },
        component: () => import('@/components/views/Settings.vue')
    },
    {
        path: '/notas-de-version',
        name: 'changelogs',
        meta: { requiresAuth: true, title: 'Notas de version - Template' },
        component: () => import('@/components/views/Changelogs.vue')
    },
    {
        path: '/centro-de-soporte',
        name: 'tickets',
        meta: {requiresAuth: true, title: 'Centro de soporte - Template' },
        component: () => import('@/components/views/Tickets.vue')
    },
    {
        path: '/manual-del-usuario',
        name: 'userManual',
        meta: {requiresAuth: true, title: 'Manual del usuario - Template' },
        component: () => import('@/components/views/UserManual.vue')
    },
    {
        path: '/permisos',
        name: 'permissions',
        meta: {requiresAuth: true, title: 'Permisos - Template' },
        component: () => import('@/components/views/Permissions.vue')
    }
]
