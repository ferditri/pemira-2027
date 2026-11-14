<script setup lang="ts">
import Flash from "@/components/Flash.vue";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { PageProps } from "@/types";
import { Icon } from "@iconify/vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage<PageProps>();
</script>

<template>
  <div class="vote-royal-bg flex items-center justify-center p-4 sm:p-6">
    <Card class="max-w-md w-full vote-parchment-card border-0">
      <CardHeader class="text-center text-pretty pt-8 pb-5 px-6 sm:px-8">
        <img
          src="/assets/images/vote/logo-vote.webp"
          alt="Logo PEMIRA Fasilkom 2027"
          class="size-28 mx-auto mb-3 drop-shadow-md object-contain"
        />
        <CardTitle
          class="font-cinzel text-3xl sm:text-4xl font-extrabold text-[#0c1d44] tracking-wider"
        >
          PEMIRA
        </CardTitle>
        <p class="font-cinzel text-sm sm:text-base font-bold text-[#0c1d44] mt-1">
          FAKULTAS ILMU KOMPUTER 2027
        </p>
        <CardDescription class="text-[#2f3d59] font-medium text-xs sm:text-sm mt-1">
          Universitas Pembangunan Nasional "Veteran" Jawa Timur
        </CardDescription>
      </CardHeader>

      <CardContent class="px-6 sm:px-10 pb-5 space-y-3">
        <a :href="route('auth.redirect')" class="block">
          <Button
            size="lg"
            class="w-full gap-2.5 vote-btn-navy rounded-xl py-6 text-sm uppercase"
          >
            <Icon icon="fa6-brands:google" class="size-4" />
            Masuk dengan Google
          </Button>
        </a>
        <Flash />
      </CardContent>

      <CardFooter class="px-6 pb-7 pt-1 justify-center">
        <p class="text-xs sm:text-sm text-[#2f3d59]">
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
      </CardFooter>
    </Card>
  </div>
</template>
