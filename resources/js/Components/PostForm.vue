<template>
  <form @submit.prevent="createPost" class="mb-6">
    <input
      v-model="title"
      type="text"
      placeholder="Заголовок"
      class="border rounded px-4 py-2 mr-2"
      required
    />
    <input
      v-model="slug"
      type="text"
      placeholder="Слаг (например: example-post)"
      class="border rounded px-4 py-2 mr-2"
      required
    />
    <input
      v-model="category"
      type="text"
      placeholder="Категория"
      class="border rounded px-4 py-2 mr-2"
    />
    <textarea
      v-model="body"
      placeholder="Содержание"
      class="border rounded px-4 py-2 mr-2"
      required
    ></textarea>
    <input
      type="file"
      @change="handleFileUpload"
      class="border rounded px-4 py-2"
    />
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Добавить</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const title = ref('')
const slug = ref('')
const category = ref('')
const body = ref('')
const image = ref(null)

const emit = defineEmits(['postCreated'])

const createPost = async () => {
  const formData = new FormData()
  formData.append('title', title.value)
  formData.append('slug', slug.value)
  formData.append('category', category.value)
  formData.append('body', body.value)
  if (image.value) {
    formData.append('image', image.value)
  }

  const response = await axios.post('/api/posts', formData)
  emit('postCreated')

  title.value = ''
  slug.value = ''
  category.value = ''
  body.value = ''
  image.value = null
}

const handleFileUpload = (event) => {
  image.value = event.target.files[0]
}
</script>
