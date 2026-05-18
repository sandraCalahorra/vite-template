export default [
  {
    path: '/anuncio/:id',
    name: 'announcementsForm',
    meta: {requiresAuth: true },
    component: () => import('@/components/views/forms/AnnouncementsForm.vue')
  },
  {
    path: '/anuncio/create',
    name: 'announcementsCreate',
    meta: {requiresAuth: true },
    component: () => import('@/components/views/forms/AnnouncementsForm.vue')
  },
]
