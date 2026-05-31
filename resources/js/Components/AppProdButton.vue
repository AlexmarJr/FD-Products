<script setup>
// Componente simples para abrir a aplicação em produção
const url = 'https://firstdecision.fly.dev/';
</script>

<template>
  <a
    :href="url"
    target="_blank"
    rel="noopener noreferrer"
    class="inline-flex items-center gap-2 rounded-full bg-[#ff7a1a] px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-[#ff8a2a] transition-opacity duration-200 animate-pulse"
    title="Abrir App em produção"
  >
    APP em produção
  </a>
</template>
