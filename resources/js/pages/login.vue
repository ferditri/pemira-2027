<script setup lang="ts">
import Flash from "@/components/Flash.vue";
import { Icon } from "@iconify/vue";
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Cinzel+Decorative:wght@700;900&family=Libre+Baskerville&display=swap");

.font-title {
    font-family: "Cinzel Decorative", serif;
}
.font-label {
    font-family: "Cinzel", serif;
}
.font-body {
    font-family: "Libre Baskerville", serif;
}
</style>

<template>
    <div
        class="min-h-dvh flex items-center justify-center p-4 relative overflow-hidden isolate bg-[#F8E4BB]"
    >
        <!-- Latar: sementara pakai gambar lama, nanti ganti ke /images/ui/bg-castle.webp -->
        <img
            src="/background.webp"
            alt=""
            class="absolute inset-0 size-full object-cover -z-10"
        />

        <div
            class="w-full max-w-md rounded-[2.5rem] bg-[#F8E4BB] px-8 py-10 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
        >
            <img
                src="/logo.webp"
                alt="Logo PEMIRA"
                class="size-28 mx-auto mb-3 drop-shadow-md"
            />

            <h1 class="font-title text-5xl font-black text-[#0F1B4D]">
                PEMIRA
            </h1>
            <p class="font-label mt-1 text-sm font-bold text-[#151333]">
                FAKULTAS ILMU KOMPUTER 2027
            </p>
            <p class="font-body mt-1 text-xs text-[#151333]">
                Universitas Pembangunan Nasional "Veteran" Jawa Timur
            </p>

            <a :href="route('auth.redirect')" class="block mt-6">
                <button
                    type="button"
                    class="font-label w-full inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] px-6 py-3 text-sm font-bold tracking-wide text-[#F8E4BB] transition hover:brightness-125"
                >
                    <Icon icon="fa6-brands:google" class="size-5" />
                    MASUK DENGAN GOOGLE
                </button>
            </a>

            <div class="mt-3">
                <Flash />
            </div>

            <p class="font-body mt-6 text-sm text-[#151333]">
                Butuh Bantuan?
                <a
                    href="https://wa.me/62859183994546"
                    target="_blank"
                    class="ml-1 font-semibold text-[#FF7F0F] hover:underline"
                    >Hubungi Kami Disini</a
                >
            </p>
        </div>
    </div>
</template>