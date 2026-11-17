<script lang="ts" setup>
import Flash from "@/components/Flash.vue";
import { Avatar, AvatarImage } from "@/components/ui/avatar";
import { PageProps } from "@/types";
import { Link, usePage } from "@inertiajs/vue3";
import { LogOut } from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps<{
  verification: UserVerification | null;
  organizations: Organization[];
}>();

const page = usePage<PageProps>();
const user = page.props.auth.user;

const brokenLogos = ref<number[]>([]);
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
    class="min-h-dvh flex items-center justify-center p-4 relative overflow-hidden"
  >
    <img
      src="/background.webp"
      alt=""
      class="absolute inset-0 size-full object-cover -z-10"
    />
    <div class="w-full max-w-md space-y-4 relative z-10">
      <div
        class="rounded-[2.5rem] bg-[#F8E4BB] px-8 py-8 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
      >
        <Flash />

        <Avatar class="size-24 mx-auto bg-[#0F1B4D]">
          <AvatarImage
            v-if="user.avatar"
            :alt="user.name"
            :src="user.avatar"
            referrerpolicy="no-referrer"
          />
        </Avatar>
        <p class="font-body mt-2 text-xs text-[#151333]">Selamat Datang</p>
        <h1 class="font-sans mt-1 text-2xl font-bold text-[#0F1B4D]">
          {{ user.name }}
        </h1>

        <hr class="my-5 border-[#151333]/40" />

        <div
          v-if="
            verification === null ||
            verification.ktm === null ||
            verification.verification === null
          "
          class="space-y-3"
        >
          <h2 class="font-label text-lg font-bold text-[#0F1B4D]">
            UPLOAD KTM
          </h2>
          <p class="font-body text-xs text-[#151333]">
            Sebelum pemilihan silakan upload KTM kamu dulu yaa!
          </p>
          <Link :href="route('verification')" class="block">
            <span
              class="font-label block w-full rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] py-3 text-sm font-bold text-[#F8E4BB] transition hover:brightness-125"
            >
              SELANJUTNYA
            </span>
          </Link>
        </div>

        <div v-else-if="organizations.length > 0" class="space-y-3">
          <h2 class="font-label text-lg font-bold text-[#0F1B4D]">
            ORGANISASI
          </h2>
          <p class="font-body text-xs text-[#151333]">
            Pilih Organisasi Yang Ingin Kamu Buka
          </p>
          <Link
            v-for="organization in organizations"
            :key="organization.id"
            :href="route('vote.organization', { organization: organization.id })"
            class="block overflow-hidden rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] transition hover:brightness-125"
          >
            <img
              v-if="organization.logo && !brokenLogos.includes(organization.id)"
              :src="organization.logo"
              :alt="organization.name"
              class="h-32 w-full object-cover"
              @error="brokenLogos.push(organization.id)"
            />
            <span
              class="font-body block w-full py-4 text-sm text-[#F8E4BB]"
            >
              {{ organization.name }}
            </span>
          </Link>
        </div>

        <div v-else class="space-y-1">
          <h2 class="font-label text-lg font-bold text-[#0F1B4D]">
            BELUM ADA PEMILIHAN
          </h2>
          <p class="font-body text-xs text-[#151333]">
            Wah kamu rajin sekali! Tunggu info selanjutnya yaa!
          </p>
        </div>

        <div
          class="mt-6 flex flex-col items-center justify-between gap-3 md:flex-row"
        >
          <p class="font-body text-sm text-[#151333]">
            Butuh Bantuan?
            <a
              href="https://wa.me/62859183994546"
              target="_blank"
              class="ml-1 font-semibold text-[#FF7F0F] hover:underline"
              >Hubungi Kami Disini</a
            >
          </p>
          <Link
            :href="route('logout')"
            class="font-label inline-flex items-center gap-2 rounded-xl border border-[#0F1B4D]/40 px-4 py-2 text-xs font-bold text-[#0F1B4D] hover:bg-[#0F1B4D]/10"
          >
            <LogOut class="size-4" />
            KELUAR
          </Link>
        </div>
      </div>

      <div
        v-if="user.type === 'admin'"
        class="rounded-[2rem] bg-[#F8E4BB] px-8 py-6 shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
      >
        <h2 class="font-label text-lg font-bold text-[#0F1B4D]">ADMIN</h2>
        <p class="font-body text-xs text-[#151333]">
          Kamu Punya Akses Ke Admin
        </p>
        <Link :href="route('admin.dashboard')" class="mt-4 block">
          <span
            class="font-body block w-full rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] py-3 text-center text-sm text-[#F8E4BB] transition hover:brightness-125"
          >
            Masuk Ke Dashboard
          </span>
        </Link>
      </div>
    </div>
  </div>
</template>