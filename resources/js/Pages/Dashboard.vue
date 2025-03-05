<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';


const items = ref([
    { id: 1, name: 'example.pdf', size: '2.5 MB', type: 'file', isSelected: false },
    { id: 2, name: 'photo.jpg', size: '1.1 MB', type: 'file', isSelected: false },
    { id: 3, name: 'Documents', size: '10 MB', type: 'folder', isSelected: false },
]);

const openFolder = (path) => {

}

const toggleSelection = (item) => {
    item.isSelected = !item.isSelected;
};

onMounted(() => {
    openFolder('/');
});
</script>

<template>
    <Head title="Dashboard" />

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
                                <button class="btn btn-primary">
                                    <i class="fas fa-upload mr-2"></i> Загрузить файл
                                </button>
                                <button class="btn btn-secondary">
                                    <i class="fas fa-folder-plus mr-2"></i> Создать папку
                                </button>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 gap-4">
                                    <div
                                        v-for="item in items"
                                        :key="item.id"
                                        class="flex items-center justify-between bg-white p-4 rounded-lg shadow-sm cursor-pointer"
                                        :class="{ 'bg-blue-50': item.isSelected }"
                                        @click="toggleSelection(item)"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <i
                                                v-if="item.type === 'file'"
                                                class="fas fa-file text-gray-500"
                                            ></i>
                                            <i
                                                v-else
                                                class="fas fa-folder text-yellow-500"
                                            ></i>
                                            <div>
                                                <span class="text-gray-800">{{ item.name }}</span>
                                                <span class="block text-sm text-gray-500">
                                                    {{ item.type === 'file' ? item.size : 'Папка' }}
                                                </span>
                                            </div>
                                        </div>
                                        <button
                                            class="text-red-500 hover:text-red-700"
                                            @click.stop
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
</style>
