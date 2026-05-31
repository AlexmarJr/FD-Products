<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">
                Informações do Perfil
            </h2>

            <p class="mt-1 text-sm text-white/60">
                Atualize as informações do seu perfil e o endereço de e-mail da conta.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nome" class="text-white/75" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full rounded-2xl border-white/10 bg-[#0f1b31] text-white placeholder:text-white/35 focus:border-[#ff8a2a]/60 focus:ring-[#ff8a2a]/30"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="E-mail" class="text-white/75" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-2xl border-white/10 bg-[#0f1b31] text-white placeholder:text-white/35 focus:border-[#ff8a2a]/60 focus:ring-[#ff8a2a]/30"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-white/75">
                    Seu e-mail ainda não foi verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-[#8fb0ff] underline hover:text-white focus:outline-none focus:ring-2 focus:ring-[#8fb0ff]/40"
                    >
                        Clique aqui para reenviar o e-mail de verificação.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-emerald-300"
                >
                    Um novo link de verificação foi enviado para o seu e-mail.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing" class="rounded-full bg-[#ff8a2a] text-white hover:bg-[#ff7a14] focus:bg-[#ff7a14] active:bg-[#e86f10]">
                    Salvar
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-white/60"
                    >
                        Salvo.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
