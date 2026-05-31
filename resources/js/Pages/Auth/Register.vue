<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    business_type: [],
});

const onboardingPoints = [
    {
        title: 'Conta pronta para operar',
        description: 'Crie um acesso para acompanhar produtos, fluxo e decisões em um só lugar.',
    },
    {
        title: 'Time alinhado',
        description: 'Cadastre-se para conectar áreas com mais clareza e menos ruído no dia a dia.',
    },
    {
        title: 'Visão de produto',
        description: 'Entre com uma base pensada para priorização, velocidade e acompanhamento prático.',
    },
];

const passwordMismatch = ref('');
const backendErrors = ref([]);

function translateValidationKey(key, field = '') {
    const map = {
        'validation.unique': `Já existe um conta com esse email.`,
        'validation.required': `Campo obrigatório.`,
        'validation.email': `Formato de e-mail inválido.`,
        'validation.min': `Valor mínimo não atendido.`,
        'validation.confirmed': `A confirmação não confere.`,
    };

    return map[key] || key;
}

const submit = () => {
    passwordMismatch.value = '';
    backendErrors.value = [];
    if (form.password !== form.password_confirmation) {
        passwordMismatch.value = 'As senhas não conferem.';
        return;
    }

    form.post(route('register'), {
        onError: (errors) => {
            const fieldKeys = ['name', 'email', 'password', 'password_confirmation', 'business_type'];

            const normalize = (msg, field = '') => {
                if (Array.isArray(msg)) return msg.map((m) => normalize(m, field)).flat();
                if (typeof msg === 'string') {
                    if (/^validation\./.test(msg)) return translateValidationKey(msg, field);
                    return msg;
                }
                if (typeof msg === 'object' && msg !== null) return Object.values(msg).map((m) => normalize(m, field)).flat();
                return String(msg);
            };

            fieldKeys.forEach((f) => {
                if (errors[f]) {
                    const translated = normalize(errors[f], f);
                    form.errors[f] = Array.isArray(translated) ? translated : [translated];
                }
            });

            const top = Object.keys(errors)
                .filter((k) => !fieldKeys.includes(k))
                .map((k) => errors[k])
                .flat()
                .map((m) => normalize(m));

            if (errors.message) {
                const msg = normalize(errors.message);
                if (Array.isArray(msg)) top.unshift(...msg);
                else top.unshift(msg);
            }

            backendErrors.value = Array.from(new Set(top.filter(Boolean)));
        },
        onSuccess: () => {
            backendErrors.value = [];
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const businessOptions = [
    'Mercadinho',
    'Loja de Acessórios',
    'Eletrodomésticos',
    'Roupas e Moda',
    'Calçados',
    'Farmácia',
    'Papelaria',
    'Materiais de Construção',
    'Alimentos e Bebidas',
    'Casa e Decoração',
    'Eletrônicos',
    'Produtos de Beleza',
];

function toggleBusiness(option) {
    const arr = Array.isArray(form.business_type) ? [...form.business_type] : [];

    const idx = arr.indexOf(option);
    if (idx !== -1) {
        arr.splice(idx, 1);
    } else {
        if (arr.length >= 5) return; // prevent more than 5 selections
        arr.push(option);
    }

    form.business_type = arr;
}

const businessSelectedCount = () => (Array.isArray(form.business_type) ? form.business_type.length : 0);

function onBusinessSelect(e) {
    const selected = Array.from(e.target.selectedOptions).map((o) => o.value);
    if (selected.length > 5) {
        // keep first 5, update UI to reflect limit
        form.business_type = selected.slice(0, 5);
        // update select element to match
        const options = e.target.options;
        for (let i = 0; i < options.length; i++) {
            options[i].selected = form.business_type.includes(options[i].value);
        }
        return;
    }

    form.business_type = selected;
}

const showBusiness = ref(false);
</script>

<template>
    <GuestLayout>
        <Head title="Criar conta" />

        <div class="relative mx-auto grid w-full max-w-7xl overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/5 shadow-[0_24px_60px_rgba(15,23,42,0.25)] backdrop-blur box-border lg:grid-cols-[0.92fr_1.08fr]">
            <section class="hidden min-w-0 flex-col justify-between bg-[#0f172a] px-7 py-7 text-white lg:flex lg:px-8 lg:py-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[0.65rem] tracking-[0.18em] text-white/80 uppercase">
                        FirstDecision
                    </div>

                    <div class="max-w-lg space-y-3">
                        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.24em] text-[#ff8a2a]">
                            Crie sua conta
                        </p>
                        <h1 class="text-2xl font-semibold leading-tight text-balance xl:text-3xl">
                            Comece com uma estrutura pronta para produto, operação e decisão.
                        </h1>
                        <p class="max-w-lg text-sm leading-5 text-white/72 xl:text-sm">
                            Seu acesso à FirstDecision nasce com uma experiência pensada para organizar o fluxo e acelerar o que precisa sair do papel.
                        </p>
                    </div>

                    <div class="grid gap-2.5">
                        <article
                            v-for="item in onboardingPoints"
                            :key="item.title"
                            class="rounded-2xl border border-white/10 bg-white/6 p-2.5 backdrop-blur"
                        >
                            <div class="mb-2 h-1.5 w-10 rounded-full bg-[#ff8a2a]"></div>
                            <h2 class="text-[0.9rem] font-semibold text-white">
                                {{ item.title }}
                            </h2>
                            <p class="mt-1 text-xs leading-5 text-white/68">
                                {{ item.description }}
                            </p>
                        </article>
                    </div>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-2.5">
                        <p class="text-lg font-semibold text-white">3 passos</p>
                        <p class="mt-1 text-xs text-white/65">Nome, e-mail e senha</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-2.5">
                        <p class="text-lg font-semibold text-white">3 letras</p>
                        <p class="mt-1 text-xs text-white/65">Senha mínima configurada</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-2.5">
                        <p class="text-lg font-semibold text-white">Entrada rápida</p>
                        <p class="mt-1 text-xs text-white/65">Para começar sem atrito</p>
                    </div>
                </div>
            </section>

            <section class="flex min-w-0 items-center justify-center px-4 py-5 sm:px-5 lg:px-8">
                <div class="w-full max-w-full rounded-[1.5rem] border border-white/70 bg-white/90 p-5 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:max-w-md sm:p-6">
                    <div class="mb-6 space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#ff7a1a]">
                            Criar conta
                        </p>
                        <h2 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                            Comece agora
                        </h2>
                        <p class="text-sm leading-6 text-slate-600">
                            Preencha seus dados para acessar o ambiente da FirstDecision.
                        </p>
                    </div>

                    <form class="space-y-3.5" @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Nome" class="text-slate-700" />

                            <TextInput
                                id="name"
                                type="text"
                                class="mt-2 block w-full max-w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />

                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="business_type" value="Mercado Alvo (até 5)" class="text-slate-700" />

                            <div class="mt-2 relative">
                                <div>
                                    <div @click.prevent="showBusiness = !showBusiness" class="mt-2 block w-full max-w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus-within:border-[#ff7a1a] focus-within:ring-[#ff7a1a] cursor-pointer flex items-center justify-between">
                                        <span class="text-sm text-slate-700">Selecionar Mercado Alvo (até 5)</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.12 1L10.56 13.4a.75.75 0 01-1.12 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                    </div>
                                </div>

                                <div v-if="showBusiness" class="absolute z-50 mt-2 w-full max-w-md rounded-lg border border-slate-200 bg-white shadow-lg">
                                    <div class="max-h-48 overflow-auto p-2">
                                        <button v-for="option in businessOptions" :key="option" type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 flex items-center gap-2" @click.prevent="toggleBusiness(option)">
                                            <input type="checkbox" :checked="form.business_type.includes(option)" class="h-4 w-4" />
                                            <span class="text-sm text-slate-700">{{ option }}</span>
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-slate-100 px-3 py-2">
                                        <p class="text-xs text-slate-500">Selecionados: {{ businessSelectedCount() }} / 5</p>
                                        <button type="button" class="text-xs text-[#ff7a1a]" @click="showBusiness = false">Fechar</button>
                                    </div>
                                </div>
                            </div>

                            <InputError class="mt-2" :message="form.errors.business_type" />
                        </div>

                        <div>
                            <InputLabel for="email" value="E-mail" class="text-slate-700" />

                            <TextInput
                                id="email"
                                type="email"
                                class="mt-2 block w-full max-w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />

                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Senha" class="text-slate-700" />

                            <TextInput
                                id="password"
                                type="password"
                                class="mt-2 block w-full max-w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel
                                for="password_confirmation"
                                value="Confirmar senha"
                                class="text-slate-700"
                            />

                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-2 block w-full max-w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />

                            <InputError
                                class="mt-2"
                                :message="passwordMismatch || form.errors.password_confirmation"
                            />
                        </div>

                        <div v-if="backendErrors.length" class="mb-3">
                            <div class="rounded-md bg-red-50 border border-red-100 p-3 text-sm text-red-700">
                                <ul class="list-disc pl-4">
                                    <li v-for="(err, i) in backendErrors" :key="i">{{ err }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                            <Link
                                :href="route('login')"
                                class="text-sm font-medium text-[#cf5d00] transition hover:text-[#9e4800]"
                            >
                                Entrar
                            </Link>

                            <PrimaryButton
                                class="flex w-full justify-center rounded-2xl bg-[#0f172a] px-5 py-2.5 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#17233b] sm:w-auto"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Criar conta
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </GuestLayout>
</template>
