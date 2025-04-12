<template>
  <div>
    <h2 class="text-2xl font-bold mb-4">Список постов</h2>

    <PostForm @postCreated="fetchPosts" />

    <ul v-if="posts.length">
      <li
        v-for="post in posts"
        :key="post.id"
        class="mb-4 p-4 border rounded shadow flex justify-between items-center"
      >
        <div>
          <h3 class="text-xl font-semibold">{{ post.title }}</h3>
          <div class="flex items-center text-sm text-gray-500 mt-1">
            <span v-if="post.user">Автор: {{ post.user.name }}</span>
            <span class="mx-2">|</span>
            <span>Статус: {{ post.status }}</span>
            <span class="mx-2">|</span>
            <span>{{ formatDate(post.created_at) }}</span>
          </div>
          <div class="mt-2">
            <p class="text-gray-700">{{ truncateText(post.body, 150) }}</p>
          </div>
          <div class="mt-2" v-if="post.category || post.tags">
            <span v-if="post.category" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-2">
              {{ post.category }}
            </span>
            <span v-if="post.tags" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
              {{ post.tags }}
            </span>
          </div>
        </div>
        <div class="flex flex-col space-y-2">
          <a :href="`/posts/${post.slug}`" class="bg-blue-500 text-white px-3 py-1 rounded text-center">
            Просмотр
          </a>
          <button
            @click="deletePost(post.id)"
            class="bg-red-500 text-white px-3 py-1 rounded"
          >
            Удалить
          </button>
        </div>
      </li>
    </ul>
    <p v-else>Постов пока нет.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import PostForm from './PostForm.vue'

const posts = ref([])

const fetchPosts = async () => {
  try {
    const response = await axios.get('/api/posts')
    posts.value = response.data
  } catch (error) {
    console.error('Error fetching posts:', error)
  }
}

const deletePost = async (id) => {
  try {
    await axios.delete(`/api/posts/${id}`)
    await fetchPosts()
  } catch (error) {
    console.error('Error deleting post:', error)
  }
}

// Helper function to truncate text
const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Helper function to format date
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(fetchPosts)
</script>
