import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import i18n from '@/i18n'

const { t } = i18n.global

const routes = [
  {
    path: '/',
    name: 'Home',
    meta: { title: () => t('routes.home') },
    component: () => import('@/views/Home.vue'),
  },
  {
    path: '/campaigns',
    name: 'CampaignList',
    meta: { title: () => t('routes.campaigns') },
    component: () => import('@/views/Campaign/CampaignList.vue'),
  },
  {
    path: '/campaigns/:slug',
    name: 'CampaignDetail',
    meta: { title: () => t('routes.campaignDetail') },
    component: () => import('@/views/Campaign/CampaignDetail.vue'),
  },
  {
    path: '/login',
    name: 'Login',
    meta: { title: () => t('routes.login'), guestOnly: true },
    component: () => import('@/views/Auth/Login.vue'),
  },
  {
    path: '/register',
    name: 'Register',
    meta: { title: () => t('routes.register'), guestOnly: true },
    component: () => import('@/views/Auth/Register.vue'),
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    meta: { title: () => t('routes.dashboard'), requiresAuth: true },
    component: () => import('@/views/Auth/Dashboard.vue'),
  },
  {
    path: '/profile',
    name: 'Profile',
    meta: { title: () => t('routes.profile'), requiresAuth: true },
    component: () => import('@/views/ProfilePage.vue'),
  },
  {
    path: '/notifications',
    name: 'Notifications',
    meta: { title: () => t('routes.notifications'), requiresAuth: true },
    component: () => import('@/views/NotificationPage.vue'),
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    meta: { title: () => t('routes.admin'), requiresAuth: true, requiresAdmin: true },
    component: () => import('@/views/AdminDashboard.vue'),
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/',
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

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'Dashboard' })
  }

  next()
})

export default router
