import { createRouter, createWebHistory } from 'vue-router'
import SearchPage from '@/pages/SearchPage.vue'
import JobDetailPage from '@/pages/JobDetailPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'search', component: SearchPage },
    { path: '/jobs/:jobId', name: 'job-detail', component: JobDetailPage, props: true },
  ],
})
