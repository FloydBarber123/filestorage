<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from '@inertiajs/vue3';
import FileList from "@/Pages/Dashboard/FileList.vue";
import {ref, onMounted} from 'vue';

const fileList = ref([]);
const currentUserPath = ref('/');
const isCreateFolderModalOpen = ref(false);
const inputFolderName = ref('');
const fileInput = ref(null);
const uploadStatusBar = ref(0);
const isSearchModalOpen = ref(false);
const searchDateFrom = ref('');
const searchDateTo = ref('');
const searchMaxSize = ref('');
const isSearchResultModalOpen = ref(false);
const searchedFileList = ref([]);
const history = ref([]);
const page = ref(1);
const perPage = 50;
const isLoading = ref(false);
const isEndReached = ref(false);

const openFolder = async (path, isAddPathToHistory = true, newPage = 1) => {
    if (newPage === 1) {
        fileList.value = [];
        isEndReached.value = false;
    }

    try {
        const response = await axios.post('/api/file/open', {
            path,
            page: newPage,
            perPage: perPage
        });

        if (response.data.files.length === 0) {
            isEndReached.value = true;
        } else {
            fileList.value = newPage === 1
                ? response.data.files
                : [...fileList.value, ...response.data.files];

            page.value = newPage + 1;
        }

        currentUserPath.value = path;

        if (isAddPathToHistory && (!history.value.length || history.value[history.value.length - 1] !== path)) {
            history.value.push(path);
        }
        isLoading.value = false;
    } catch (error) {
        console.error('Ошибка при загрузке файлов:', error);
        alert('Ошибка при загрузке файлов');
    }
};


const createFolder = () => {
    const folderName = inputFolderName.value.trim();
    if (!folderName) {
        alert('Введите название папки');
    }

    let pathToNewFolder = '';
    if (currentUserPath.value === '/') {
        pathToNewFolder = folderName;
    } else {
        pathToNewFolder = currentUserPath.value + '/' + inputFolderName.value;
    }

    try {
        axios.post('/api/file/create', {
            path: pathToNewFolder,
        }).then(response => {
            openFolder(currentUserPath.value, false)
            closeCreateFolderModalOpen();
        })
    } catch (error) {
        alert(error)
    }
}

const loadMoreFiles = () => {
    if (isLoading.value || isEndReached.value) return;

    isLoading.value = true;
    openFolder(currentUserPath.value, true, page.value);
};

const handleScroll = () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
        loadMoreFiles();
    }
};

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
            }).then(response => {
                openFolder(currentUserPath.value, false)
                uploadStatusBar.value = 0

                if (fileInput.value) {
                    fileInput.value.value = "";
                }
            })
        } catch (error) {
            alert('Error while uploading files: ' + error);
        }
    }
}

const openSearchResultModel = () => {
    isSearchResultModalOpen.value = true;
};

const closeSearchResultModel = () => {
    isSearchResultModalOpen.value = false;
};

const searchFiles = () => {
    axios.post('/api/file/search', {
        dateFrom: searchDateFrom.value,
        dateTo: searchDateTo.value,
        maxSize: searchMaxSize.value
    }).then(response => {
        closeSearchModal()
        openSearchResultModel()
        searchedFileList.value = response.data.files;
    }).catch(() => alert('Ошибка при поиске файлов'));
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const openCreateFolderModal = () => {
    isCreateFolderModalOpen.value = true;
}

const closeCreateFolderModalOpen = () => {
    isCreateFolderModalOpen.value = false;
}

const openSearchModal = () => {
    isSearchModalOpen.value = true;
};

const closeSearchModal = () => {
    isSearchModalOpen.value = false;
};

const goBack = () => {
    if (history.value.length > 1) {
        history.value.pop();
        openFolder(history.value[history.value.length - 1], false);
    }
};


const deleteFile = (item) => {
    let path = '';

    if (item.path === '/') {
        path = item.path + item.name;
    } else {
        path = item.path + '/' + item.name;
    }

    try {
        axios.post('/api/file/delete', {
            path: path,
        }).then(response => {
            openFolder(currentUserPath.value, false)
        })
    } catch (error) {
        alert(error)
    }
}

onMounted(() => {
    openFolder(currentUserPath.value);
    window.addEventListener('scroll', handleScroll);
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
                                <button class="btn btn-primary" @click="openSearchModal">
                                    <i class="fas fa-search mr-2"></i> Поиск
                                </button>
                            </div>
                            <div v-if="uploadStatusBar > 0" class="progress-bar">
                                <div class="progress-fill" :style="{ width: uploadStatusBar + '%' }"></div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 gap-4">
                                    <div
                                        class="flex items-center justify-between bg-white p-4 rounded-lg shadow-sm cursor-pointer"
                                        v-if="history.length > 1"
                                        @click="goBack"
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
                                    <FileList :fileList="fileList" :openFolder="openFolder" :isDeleteIcon="true"
                                              :deleteFile="deleteFile"></FileList>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isSearchModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-bold mb-4">Фильтр поиска</h2>
                <label class="block text-gray-700">Дата от:</label>
                <input type="date" v-model="searchDateFrom" class="w-full p-2 border rounded-lg mb-4"/>

                <label class="block text-gray-700">Дата до:</label>
                <input type="date" v-model="searchDateTo" class="w-full p-2 border rounded-lg mb-4"/>

                <label class="block text-gray-700">Размер до (MB):</label>
                <input type="number" v-model="searchMaxSize" class="w-full p-2 border rounded-lg mb-4"/>

                <div class="flex justify-end space-x-4">
                    <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg" @click="closeSearchModal">
                        Отмена
                    </button>
                    <button class="bg-blue-500 text-white px-4 py-2 rounded-lg" @click="searchFiles">
                        Искать
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isSearchResultModalOpen"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-bold mb-4">Результаты поиска</h2>
                <div v-if="fileList.length === 0" class="text-center text-gray-500">
                    <p>Файлы не найдены</p>
                </div>
                <div v-else>
                    <FileList :fileList="searchedFileList" :openFolder="openFolder" :isDeleteIcon="false"></FileList>
                </div>
                <div class="flex justify-end space-x-4">
                    <button class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg" @click="closeSearchResultModel">
                        Закрыть
                    </button>
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
