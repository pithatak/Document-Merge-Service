<template>
    <div class="min-h-screen bg-gradient-to-b from-blue-100 to-blue-200 flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-lg font-sans">

            <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">
                PDF Composer
            </h2>

            <form @submit.prevent="submit" class="space-y-6">

                <label
                    class="flex flex-col items-center justify-center w-full h-40 px-4 transition border-2 border-dashed rounded-2xl cursor-pointer"
                    :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-white'"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <svg
                        class="w-10 h-10 text-gray-400 mb-2"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"
                        />
                    </svg>

                    <span class="text-gray-600 text-sm text-center">
                        Drag & drop files here or click to upload
                      </span>

                    <input
                        type="file"
                        multiple
                        class="hidden"
                        accept="application/pdf,image/jpeg,image/png"
                        @change="handleFiles"
                    />
                </label>

                <ul v-if="files.length" class="space-y-2">
                    <li
                        v-for="(file, index) in files"
                        :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border"
                    >
                        <span class="text-sm truncate max-w-xs">
                          {{ file.name }}
                        </span>

                        <button
                            type="button"
                            @click="removeFile(index)"
                            class="text-red-500 hover:text-red-700 text-sm"
                        >
                            Remove
                        </button>
                    </li>
                </ul>

                <p v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </p>

                <button
                    type="submit"
                    :disabled="files.length < 2 || loading"
                    class="w-full bg-green-500 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl text-lg font-medium transition"
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
    error.value = null

    const droppedFiles = Array.from(event.dataTransfer.files)

    const validationError = validateFiles(droppedFiles)
    if (validationError) {
        error.value = validationError
        return
    }

    files.value.push(...droppedFiles)
}

const validateFiles = (newFiles) => {

    if (newFiles.length + files.value.length > 10) {
        return 'Maximum 10 files allowed'
    }

    for (const file of newFiles) {

        if (!['application/pdf', 'image/jpeg', 'image/png'].includes(file.type)) {
            return `Unsupported file type: ${file.name}`
        }

        if (file.size > 15 * 1024 * 1024) {
            return `File too large (max 15MB): ${file.name}`
        }
    }

    return null
}


const handleFiles = (event) => {
    error.value = null
    const selected = Array.from(event.target.files)

    const validationError = validateFiles(selected)
    if (validationError) {
        error.value = validationError
        return
    }

    files.value.push(...selected)
}

const removeFile = (index) => {
    files.value.splice(index, 1)
}

const submit = async () => {

    error.value = null

    if (files.value.length < 2) {
        error.value = 'Minimum 2 files required'
        return
    }

    loading.value = true

    const formData = new FormData()
    files.value.forEach(file => {
        formData.append('files[]', file)
    })

    try {

        const response = await axios.post('/api/merge', formData, {
            responseType: 'blob'
        })

        const url = window.URL.createObjectURL(response.data)
        const link = document.createElement('a')
        link.href = url
        link.download = 'merged.pdf'
        link.click()
        window.URL.revokeObjectURL(url)

        files.value = []

    } catch (err) {
        handleApiError(err)
    } finally {
        loading.value = false
    }
}

const handleApiError = (err) => {

    if (!err.response) {
        error.value = 'Network error'
        return
    }

    const reader = new FileReader()

    reader.onload = () => {
        try {
            const json = JSON.parse(reader.result)

            if (json.errors) {
                error.value = Array.isArray(json.errors)
                    ? json.errors.join(', ')
                    : json.errors
            } else if (json.error) {
                error.value = json.error
            } else {
                error.value = 'Server error'
            }

        } catch {
            error.value = 'Invalid server response'
        }
    }

    reader.readAsText(err.response.data)
}
</script>
