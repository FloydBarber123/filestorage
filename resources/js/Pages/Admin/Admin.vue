<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array
});

const users = ref(props.users);
const isAdmin = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ''
});

const passwordForm = ref({
    password: '',
    password_confirmation: '',
    userId: null
});

const registerUser = () => {
    if (isAdmin.value) {
        form.role = 'admin';
    }
    else {
        form.role = 'user';
    }

    axios.post(route('admin.users.store'), {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        role: form.role,
        isJson: true
    })
    .then(response => {
        alert('success')
        users.value = response.data.users;
    })
    .catch(error => {
        if (error.response && error.response.data.message) {
            alert(error.response.data.message)
        }
    })
};

const changePassword = (userId) => {
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
        alert("Passwords do not match!");
        return;
    }

    axios.put(route('admin.users.updatePassword', userId), {
        password: passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation
    })
    .then(response => {
        alert('Password updated successfully!');
        passwordForm.value.password = '';
        passwordForm.value.password_confirmation = '';
        passwordForm.value.userId = null;
    })
    .catch(error => {
        if (error.response && error.response.data.message) {
            alert(error.response.data.message)
        }
    });
};

const deleteUser = (userId) => {
    if (confirm('Are you sure you want to delete this user?')) {
        axios.delete(route('admin.users.destroy', userId))
        .then(response => {
            alert('User deleted successfully!');
            users.value = users.value.filter(user => user.id !== userId);
        })
        .catch(error => {
            if (error.response && error.response.data.message) {
                alert(error.response.data.message)
            }
        });
    }
};
</script>

<template>
    <Head title="Admin panel"/>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Manage users</h2>
        </template>

        <div class="py-6 max-w-4xl mx-auto">
            <div class="bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-lg font-bold mb-4">Add user</h3>
                <form @submit.prevent="registerUser">
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Name</label>
                        <input v-model="form.name" type="text" class="w-full p-2 border rounded-lg"/>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Email</label>
                        <input v-model="form.email" type="email" class="w-full p-2 border rounded-lg"/>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Pass</label>
                        <input v-model="form.password" type="password" class="w-full p-2 border rounded-lg"/>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm font-medium">Re-enter pass</label>
                        <input v-model="form.password_confirmation" type="password" class="w-full p-2 border rounded-lg"/>
                    </div>
                    <div class="mb-3 flex items-center">
                        <input v-model="isAdmin" type="checkbox" value="admin" class="mr-2" />
                        <label>admin?</label>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                        Register
                    </button>
                </form>
            </div>

            <div class="mt-6 bg-white p-6 shadow-md rounded-lg">
                <h3 class="text-lg font-bold mb-4">Users</h3>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Email</th>
                        <th class="p-2 border">Role</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in users" :key="user.id" class="border">
                        <td class="p-2">{{ user.id }}</td>
                        <td class="p-2">{{ user.name }}</td>
                        <td class="p-2">{{ user.email }}</td>
                        <td class="p-2">{{ user.role }}</td>
                        <td class="p-2">
                            <button @click="passwordForm.userId = user.id" class="bg-yellow-500 text-white px-4 py-2 rounded-lg">Change Password</button>
                            <button @click="deleteUser(user.id)" class="bg-red-500 text-white px-4 py-2 rounded-lg">Delete</button>

                            <div v-if="passwordForm.userId === user.id" class="mt-4">
                                <h4 class="text-sm font-semibold mb-2">Change Password</h4>
                                <input v-model="passwordForm.password" type="password" class="w-full p-2 border rounded-lg mb-2" placeholder="New Password"/>
                                <input v-model="passwordForm.password_confirmation" type="password" class="w-full p-2 border rounded-lg" placeholder="Confirm Password"/>
                                <button @click="changePassword(user.id)" class="bg-green-500 text-white px-4 py-2 rounded-lg mt-2">Update Password</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
