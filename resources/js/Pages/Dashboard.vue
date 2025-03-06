<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import {ref, onMounted} from 'vue';

const fileList = ref([]);
const currentUserPath = ref('/');
const previousUserPath = ref(null);
const isCreateFolderModalOpen = ref(false);
const inputFolderName = ref('');
const fileInput = ref(null);
const uploadStatusBar = ref(0);

const openFolder = (path) => {
    try {
        axios.post('/api/file/open', {
            path: path,
        }).then(response => {
            previousUserPath.value = currentUserPath.value;

            fileList.value = response.data.files;
            currentUserPath.value = path;
        })
    } catch (error) {
        alert('Error while requesting root folder')
    }
}

const createFolder = () => {
    const folderName = inputFolderName.value.trim();
    if (!folderName) {
        alert('Введите название папки');
    }

    let pathToNewFolder = '';
    if (currentUserPath.value === '/') {
        pathToNewFolder = folderName;
    }
    else {
        pathToNewFolder = currentUserPath.value + '/' + inputFolderName.value;
    }

    try {
        axios.post('/api/file/create', {
            path: pathToNewFolder,
        }).then(response => {
            openFolder(currentUserPath.value)
            closeCreateFolderModalOpen();
        })
    } catch (error) {
        alert(error)
    }
}

const handleFileUpload = (event) => {
    const files = event.target.files;

    if (files.length > 0) {
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('file[]', files[i]);
        }
        formData.append('path', currentUserPath.value);

        try {
            axios.post('/api/file/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    uploadStatusBar.value = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                },
            })
            .then(response => {
                openFolder(currentUserPath.value)
                uploadStatusBar.value = 0
            })
        } catch (error) {
            alert('Error while uploading files: ' + error);
        }
    }
}

const triggerFileInput = () => {
    fileInput.value.click();
};

const openCreateFolderModal = () => {
    isCreateFolderModalOpen.value = true;
}

const closeCreateFolderModalOpen = () => {
    isCreateFolderModalOpen.value = false;
}

const goBack = () => {
    if (previousUserPath.value) {
        openFolder(previousUserPath.value);
    } else {
        alert('cant go back');
    }
};

const deleteFile = (path) => {
    try {
        axios.post('/api/file/delete', {
            path: path,
        }).then(response => {
            openFolder(currentUserPath.value)
        })
    } catch (error) {
        alert(error)
    }
}

onMounted(() => {
    openFolder(currentUserPath.value);
});
</script>

<template>
    <Head title="Dashboard"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="container-fluid">
                            <h2 class="mb-4 text-2xl font-bold text-gray-800">Ваши файлы</h2>
                            <div class="mb-6 flex space-x-4">
                                <button class="btn btn-primary" @click="triggerFileInput">
                                    <i class="fas fa-upload mr-2"></i> Загрузить файл
                                </button>
                                <input
                                    style="display: none;"
                                    ref="fileInput"
                                    type="file"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    multiple
                                    @change="handleFileUpload"
                                />
                                <button class="btn btn-secondary" @click="openCreateFolderModal()">
                                    <i class="fas fa-folder-plus mr-2"></i> Создать папку
                                </button>
                            </div>
                            <div v-if="uploadStatusBar > 0" class="progress-bar">
                                <div class="progress-fill" :style="{ width: uploadStatusBar + '%' }"></div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 gap-4">
                                    <div
                                        class="flex items-center justify-between bg-white p-4 rounded-lg shadow-sm cursor-pointer"
                                        v-if="previousUserPath != currentUserPath"
                                        @click="goBack()"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <i
                                                class="fas fa-arrow-left text-gray-500"
                                            ></i>
                                            <div>
                                                <span class="text-gray-800">...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-for="item in fileList"
                                        :key="item.name"
                                        :class="[
                                            'flex items-center justify-between bg-white p-4 rounded-lg shadow-sm',
                                            item.type === 'directory' ? 'cursor-pointer' : 'cursor-default'
                                        ]"
                                        @click="item.type === 'directory' ? openFolder(item.name) : null"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <i
                                                v-if="item.type === 'directory'"
                                                class="fas fa-folder text-yellow-500"
                                            ></i>
                                            <i
                                                v-else
                                                class="fas fa-file text-gray-500"
                                            ></i>
                                            <div>
                                                <span class="text-gray-800">{{ item.name }}</span>
                                                <span class="block text-sm text-gray-500">
                                                    {{ item.size }}
                                                </span>
                                            </div>
                                        </div>
                                        <button
                                            class="text-red-500 hover:text-red-700"
                                            @click="deleteFile(item.name)"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="isCreateFolderModalOpen"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        >
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-bold mb-4">Создать папку</h2>
                <input
                    v-model="inputFolderName"
                    type="text"
                    placeholder="Введите название папки"
                    class="w-full p-2 border rounded-lg mb-4"
                />
                <div class="flex justify-end space-x-4">
                    <button
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg"
                        @click="closeCreateFolderModalOpen()"
                    >
                        Отмена
                    </button>
                    <button
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg"
                        @click="createFolder"
                    >
                        Создать
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.bg-gray-50 {
    background-color: #f9fafb;
}

.shadow-sm {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.rounded-lg {
    border-radius: 0.5rem;
}

.bg-blue-50 {
    background-color: #eff6ff;
}

.upload-label {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4caf50;
    color: white;
    cursor: pointer;
    border-radius: 5px;
}

.progress-bar {
    width: 100%;
    height: 10px;
    background: #e0e0e0;
    border-radius: 5px;
    margin-top: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #4caf50;
    transition: width 0.3s;
}

</style>
