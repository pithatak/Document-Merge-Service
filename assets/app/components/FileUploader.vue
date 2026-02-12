<template>
    <div class="min-h-screen bg-gradient-to-b from-blue-100 to-blue-200 flex items-center justify-center">
        <div class="wizard bg-white p-8 rounded-xl shadow-xl w-full max-w-lg font-sans">
            <form @submit.prevent="submit">

                <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">PDF Composer</h2>
                <div class="space-y-4 p-3">
                    <label
                        class="flex flex-col items-center justify-center w-full h-40 px-4 transition bg-white border-2 border-dashed rounded-2xl cursor-pointer"
                        :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                    >
                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                        </svg>

                        <span class="text-gray-600">
                          Drag & drop or click to upload
                        </span>

                        <input
                            type="file"
                            multiple
                            class="hidden"
                            accept="application/pdf,image/jpeg,image/png"
                            @change="handleFiles"
                        />
                    </label>
                </div>


                <ul class="space-y-2 p-3">
                    <li
                        v-for="(file, index) in files"
                        :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border"
                    >
                        <span class="text-sm truncate max-w-xs">
                          {{ file.name }}
                        </span>

                        <button
                            @click="removeFile(index)"
                            class="text-red-500 hover:text-red-700 text-sm"
                        >
                            Remove
                        </button>
                    </li>
                </ul>


                <p v-if="error" class="error">{{ error }}</p>

                <button
                    type="submit"
                    :disabled="files.length < 2 || loading"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded text-lg font-medium"
                >
                    {{ loading ? 'Merging...' : 'Merge Files' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import {ref} from 'vue'
import axios from 'axios'

const files = ref([])
const error = ref(null)
const loading = ref(false)
const isDragging = ref(false)

const handleDrop = (event) => {
    isDragging.value = false
    const droppedFiles = Array.from(event.dataTransfer.files)

    if (droppedFiles.length + files.value.length > 10) {
        error.value = 'Maximum 10 files allowed'
        return
    }

    files.value.push(...droppedFiles)
}


const handleFiles = (event) => {
    error.value = null
    const selected = Array.from(event.target.files)

    if (selected.length + files.value.length > 10) {
        error.value = 'Maximum 10 files allowed'
        return
    }

    files.value.push(...selected)
}

const removeFile = (index) => {
    files.value.splice(index, 1)
}

const submit = async () => {
    if (files.value.length < 2) {
        error.value = 'Minimum 2 files required'
        return
    }

    const formData = new FormData()
    files.value.forEach(file => {
        formData.append('files[]', file)
    })

    try {
        loading.value = true

        const response = await axios.post('/api/merge', formData, {
            responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'merged.pdf')
        document.body.appendChild(link)
        link.click()
        link.remove()

    } catch (err) {
        if (err.response && err.response.data) {
            const reader = new FileReader()

            reader.onload = () => {
                try {
                    const json = JSON.parse(reader.result)
                    error.value = json.error || 'Unknown error'
                } catch {
                    error.value = 'Unexpected error'
                }
            }

            reader.readAsText(err.response.data)
        } else {
            error.value = 'Network error'
        }
    } finally {
        loading.value = false
    }
}
</script>
