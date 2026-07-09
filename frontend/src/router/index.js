import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'Home',
    meta: { title: 'CoFund — Crowdfunding Platform' },
    component: () => import('@/views/Home.vue'),
  },
  {
    path: '/campaigns',
    name: 'CampaignList',
    meta: { title: 'Jelajahi Kampanye — CoFund' },
    component: () => import('@/views/Campaign/CampaignList.vue'),
  },
  {
    path: '/campaigns/create',
    name: 'CampaignCreate',
    meta: { title: 'Buat Kampanye — CoFund', requiresAuth: true, requiresCreator: true },
    component: () => import('@/views/Campaign/CampaignForm.vue'),
  },
  {
    path: '/campaigns/edit/:slug',
    name: 'CampaignEdit',
    meta: { title: 'Edit Kampanye — CoFund', requiresAuth: true, requiresCreator: true },
    component: () => import('@/views/Campaign/CampaignForm.vue'),
  },
  {
    path: '/campaigns/:slug',
    name: 'CampaignDetail',
    meta: { title: 'Detail Kampanye — CoFund' },
    component: () => import('@/views/Campaign/CampaignDetail.vue'),
  },
  {
    path: '/login',
    name: 'Login',
    meta: { title: 'Masuk — CoFund', guestOnly: true },
    component: () => import('@/views/Auth/Login.vue'),
  },
  {
    path: '/register',
    name: 'Register',
    meta: { title: 'Daftar — CoFund', guestOnly: true },
    component: () => import('@/views/Auth/Register.vue'),
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    meta: { title: 'Lupa Password — CoFund', guestOnly: true },
    component: () => import('@/views/Auth/ForgotPassword.vue'),
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    meta: { title: 'Reset Password — CoFund' },
    component: () => import('@/views/Auth/ResetPassword.vue'),
  },
  {
    path: '/verify-email',
    name: 'EmailVerification',
    meta: { title: 'Verifikasi Email — CoFund' },
    component: () => import('@/views/Auth/EmailVerification.vue'),
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    meta: { title: 'Dashboard — CoFund', requiresAuth: true },
    component: () => import('@/views/Auth/Dashboard.vue'),
  },
  {
    path: '/profile',
    name: 'Profile',
    meta: { title: 'Profil Saya — CoFund', requiresAuth: true },
    component: () => import('@/views/ProfilePage.vue'),
  },
  {
    path: '/notifications',
    name: 'Notifications',
    meta: { title: 'Notifikasi — CoFund', requiresAuth: true },
    component: () => import('@/views/NotificationPage.vue'),
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    meta: { title: 'Panel Admin — CoFund', requiresAuth: true, requiresAdmin: true },
    component: () => import('@/views/AdminDashboard.vue'),
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    meta: { title: 'Halaman Tidak Ditemukan — CoFund' },
    component: () => import('@/views/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  const titleFn = to.meta.title
  document.title = typeof titleFn === 'function' ? titleFn() : 'CoFund'

  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  if (to.meta.requiresAdmin && authStore.getUserRole !== 'admin') {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresCreator && authStore.getUserRole !== 'creator') {
    return next({ name: 'Dashboard' })
  }

  if (to.name === 'Dashboard' && authStore.getUserRole === 'admin') {
    return next({ name: 'AdminDashboard' })
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'Dashboard' })
  }

  next()
})

export default router
