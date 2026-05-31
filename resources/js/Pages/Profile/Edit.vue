<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="space-y-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.32em] text-[#8fb0ff]">Perfil</p>
                    <h1 class="text-3xl font-semibold text-white sm:text-4xl">Configurações da conta</h1>
                    <p class="text-sm text-white/60">Atualize seus dados, senha e preferências de segurança.</p>
                </div>

                <div
                    class="rounded-[1.75rem] border border-white/10 bg-[#0b1526]/90 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="rounded-[1.75rem] border border-white/10 bg-[#0b1526]/90 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur sm:p-8"
                >
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div
                    class="rounded-[1.75rem] border border-white/10 bg-[#0b1526]/90 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.24)] backdrop-blur sm:p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
