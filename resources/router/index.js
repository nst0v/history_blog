import { createRouter, createWebHistory } from 'vue-router';
import PostList from '../components/PostList.vue';

const routes = [
  {
    path: '/posts',
    name: 'PostList',
    component: PostList,
  },
  // Другие маршруты...
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
