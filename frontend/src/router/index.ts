import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/movies/:id',
      name: 'movie-detail',
      component: () => import('../views/MovieDetailView.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue')
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue')
    },
    {
      path: '/2fa/verify',
      name: '2fa-verify',
      component: () => import('../views/TwoFactorVerify.vue')
    },
    {
      path: '/2fa/setup',
      name: '2fa-setup',
      component: () => import('../views/TwoFactorSetup.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/admin/AdminLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/admin/movies'
        },
        {
          path: 'movies',
          name: 'admin-movies',
          component: () => import('../views/admin/MoviesView.vue')
        },
        {
          path: 'movies/create',
          name: 'admin-movies-create',
          component: () => import('../views/admin/MovieFormView.vue')
        },
        {
          path: 'movies/:id/edit',
          name: 'admin-movies-edit',
          component: () => import('../views/admin/MovieFormView.vue')
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('../views/admin/UsersView.vue'),
          meta: { requiresAdmin: true }
        },
        {
          path: 'comments',
          name: 'admin-comments',
          component: () => import('../views/admin/CommentsView.vue')
        }
      ]
    }
  ]
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'home' })
  } else {
    next()
  }
})

export default router
