<script setup>
import {ref} from 'vue';

const props = defineProps({
    fileList: Array,
    isDeleteIcon: Boolean,
    deleteFile: Function,
    openFolder: Function,
});
</script>

<template>
    <div
        v-for="item in fileList"
        :key="item.name"
        :class="[
        'flex items-center justify-between bg-white p-4 rounded-lg shadow-sm',
        ]"
        @click="openFolder(item.path)"
    >
        <div class="flex items-center space-x-4">
            <i
                v-if="item.ext === 'directory'"
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
        <button v-if="isDeleteIcon"
            class="text-red-500 hover:text-red-700"
            @click="deleteFile(item)"
        >
            <i class="fas fa-trash"></i>
        </button>
    </div>
</template>

<style scoped>

</style>
