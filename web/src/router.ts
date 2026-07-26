import { createRouter, createWebHistory } from 'vue-router'
import SearchPage from '@/pages/SearchPage.vue'
import JobDetailPage from '@/pages/JobDetailPage.vue'
import UploadCvPage from '@/pages/UploadCvPage.vue'
import MatchesPage from '@/pages/MatchesPage.vue'

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'search', component: SearchPage },
    { path: '/jobs/:jobId', name: 'job-detail', component: JobDetailPage, props: true },
    { path: '/cv', name: 'upload-cv', component: UploadCvPage },
    { path: '/cv/:candidateProfileId/matches', name: 'cv-matches', component: MatchesPage, props: true },
  ],
})
