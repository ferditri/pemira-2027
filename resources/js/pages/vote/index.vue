<script lang="ts" setup>
import Flash from "@/components/Flash.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useEnterMotion } from "@/composables/useMotion";
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
const rootRef = ref<HTMLElement | null>(null);
useEnterMotion(rootRef, "[data-enter]", { y: 12, duration: 0.45, staggerDelay: 0.05 });
</script>

<template>
  <div class="vote-royal-bg flex items-center justify-center p-4 sm:p-6">
    <Flash />
    <div ref="rootRef" class="max-w-lg w-full space-y-5 relative z-10">
      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader
          class="space-y-2 border-b border-[#9c8256]/35 text-center text-pretty pt-8 pb-6 px-6"
        >
          <Avatar
            class="size-24 mx-auto border-2 border-[#c8a35a] bg-[#0c1d44] shadow-md"
          >
            <AvatarImage
              v-if="user.avatar"
              :alt="user.name"
              :src="user.avatar"
              referrerpolicy="no-referrer"
            />
            <AvatarFallback
              class="bg-[#0c1d44] text-[#fce7ba] font-cinzel text-xl font-bold"
            >
              {{ user.name?.slice(0, 2).toUpperCase() }}
            </AvatarFallback>
          </Avatar>

          <div class="space-y-1 pt-1">
            <CardDescription class="text-[#3d4a66] font-medium text-sm">
              Selamat Datang
            </CardDescription>
            <CardTitle
              class="font-cinzel text-xl sm:text-2xl font-extrabold text-[#0c1d44] leading-snug"
            >
              {{ user.name }}
            </CardTitle>
          </div>
        </CardHeader>

        <CardContent
          v-if="
            verification === null ||
            verification.ktm === null ||
            verification.verification === null ||
            verification.status === 'retake' ||
            verification.status === 'rejected'
          "
          class="p-6 sm:px-10 space-y-4 border-b border-[#9c8256]/35"
        >
          <div class="text-center space-y-1.5">
            <CardTitle class="font-cinzel text-[#0c1d44] text-lg font-bold">
              {{
                verification?.status === "retake"
                  ? "ULANGI UPLOAD KTM (RE-TAKE)"
                  : verification?.status === "rejected"
                    ? "VERIFIKASI TIDAK SAH"
                    : "UPLOAD KTM"
              }}
            </CardTitle>
            <CardDescription class="text-[#3d4a66] text-sm">
              <template v-if="verification?.status === 'retake'">
                Panitia meminta kamu mengunggah ulang foto KTM &amp; selfie agar dapat divalidasi.
              </template>
              <template v-else-if="verification?.status === 'rejected'">
                Foto verifikasi sebelumnya dinyatakan tidak sah oleh panitia. Silakan unggah ulang foto yang benar.
              </template>
              <template v-else>
                Sebelum pemilihan silakan upload KTM kamu dulu yaa!
              </template>
            </CardDescription>
            <div
              v-if="verification?.admin_note && (verification?.status === 'retake' || verification?.status === 'rejected')"
              class="mt-2 rounded-lg bg-amber-100/90 border border-amber-400 px-3 py-2 text-xs text-amber-950 font-medium"
            >
              Catatan Panitia: {{ verification.admin_note }}
            </div>
          </div>
          <div class="flex justify-center">
            <Link :href="route('verification')" class="w-full">
              <Button
                class="w-full vote-btn-navy rounded-xl py-6 text-sm uppercase"
              >
                {{
                  verification?.status === "retake" || verification?.status === "rejected"
                    ? "Upload Ulang Foto"
                    : "Selanjutnya"
                }}
              </Button>
            </Link>
          </div>
        </CardContent>

        <CardContent
          v-else-if="organizations.length > 0"
          class="p-6 sm:px-10 space-y-4 border-b border-[#9c8256]/35"
        >
          <div class="text-center space-y-1">
            <CardTitle
              class="font-cinzel text-[#0c1d44] text-lg font-bold tracking-wide"
            >
              ORGANISASI
            </CardTitle>
            <CardDescription class="text-[#3d4a66] text-xs sm:text-sm">
              Pilih Organisasi Yang Ingin Kamu Buka
            </CardDescription>
          </div>

          <div
            :class="[
              organizations.length === 1
                ? 'grid grid-cols-1 max-w-sm mx-auto'
                : 'grid grid-cols-1 sm:grid-cols-2',
              'gap-3.5',
            ]"
          >
            <Link
              v-for="organization in organizations"
              :key="organization.id"
              :href="
                route('vote.organization', {
                  organization: organization.id,
                })
              "
              class="block"
            >
              <Button
                class="w-full vote-btn-navy rounded-xl py-6 px-5 text-sm sm:text-base uppercase flex items-center justify-center gap-3 h-auto"
              >
                <img
                  v-if="organization.logo"
                  :src="organization.logo"
                  :alt="organization.name"
                  class="size-7 rounded-full object-cover border border-amber-200/40 shrink-0"
                />
                <span>{{ organization.name }}</span>
              </Button>
            </Link>
          </div>
        </CardContent>

        <CardContent v-else class="p-6 border-b border-[#9c8256]/35">
          <div class="text-center space-y-1.5">
            <CardTitle class="font-cinzel text-[#0c1d44] text-lg font-bold">
              BELUM ADA PEMILIHAN
            </CardTitle>
            <CardDescription class="text-[#3d4a66] text-sm">
              Wah kamu rajin sekali! tunggu info selanjutnya yaa! 🤩
            </CardDescription>
          </div>
        </CardContent>

        <CardFooter
          class="px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3"
        >
          <p class="text-xs sm:text-sm text-[#2f3d59] text-center sm:text-left">
            Butuh Bantuan?
            <a
              :href="`https://wa.me/${page.props.pemira?.support_whatsapp ?? '62859183994546'}`"
              target="_blank"
              rel="noopener noreferrer"
              class="text-[#c6831c] hover:text-[#a66a10] underline font-bold ml-1 transition-colors"
            >
              Hubungi Kami Disini
            </a>
          </p>
          <Link :href="route('logout')" class="w-full sm:w-fit">
            <Button
              variant="outline"
              class="w-full gap-2 vote-btn-outline rounded-full px-5 text-xs uppercase"
            >
              <LogOut class="size-3.5" />
              Keluar Akun
            </Button>
          </Link>
        </CardFooter>
      </Card>

      <Card v-if="user.type === 'admin'" class="vote-parchment-card border-0">
        <CardHeader class="pb-3 pt-6 px-6 sm:px-10">
          <CardTitle
            class="font-cinzel text-[#0c1d44] text-lg font-bold tracking-wide"
          >
            ADMIN
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-xs sm:text-sm">
            Kamu punya akses ke Admin
          </CardDescription>
        </CardHeader>
        <CardFooter class="grid px-6 sm:px-10 pb-6">
          <Link :href="route('admin.dashboard')">
            <Button
              class="w-full vote-btn-navy rounded-xl py-5 text-sm uppercase"
            >
              Masuk Ke Dashboard
            </Button>
          </Link>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
