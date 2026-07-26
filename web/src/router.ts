import { createRouter, createWebHistory } from 'vue-router'
import SearchPage from '@/pages/SearchPage.vue'
import JobDetailPage from '@/pages/JobDetailPage.vue'
import UploadCvPage from '@/pages/UploadCvPage.vue'
import ProfilePage from '@/pages/ProfilePage.vue'
import MatchesPage from '@/pages/MatchesPage.vue'
import MyCvsPage from '@/pages/MyCvsPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import { isAuthenticated } from '@/lib/authStore'

declare module 'vue-router' {
  interface RouteMeta {
    public?: boolean
  }
}

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'search', component: SearchPage },
    { path: '/jobs/:jobId', name: 'job-detail', component: JobDetailPage, props: true },
    { path: '/cv', name: 'upload-cv', component: UploadCvPage },
    { path: '/cv/:candidateProfileId', name: 'cv-profile', component: ProfilePage, props: true },
    { path: '/cv/:candidateProfileId/matches', name: 'cv-matches', component: MatchesPage, props: true },
    { path: '/my-cvs', name: 'my-cvs', component: MyCvsPage },
    { path: '/login', name: 'login', component: LoginPage, meta: { public: true } },
    { path: '/register', name: 'register', component: RegisterPage, meta: { public: true } },
  ],
})

router.beforeEach((to) => {
  if (to.meta.public || isAuthenticated.value) return true

  return { name: 'login', query: { redirect: to.fullPath } }
})
