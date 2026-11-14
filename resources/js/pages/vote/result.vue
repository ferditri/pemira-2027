<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useEnterMotion } from "@/composables/useMotion";
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
  organization: Organization;
  details: BallotDetail[];
}>();

const isSubmitting = ref(false);
const rootRef = ref<HTMLElement | null>(null);
useEnterMotion(rootRef, "[data-enter]", { y: 14, duration: 0.48, staggerDelay: 0.06 });

const submit = () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  router.post(
    route("vote.result.confirm", {
      organization: props.organization.id,
    }),
    {},
    {
      onFinish: () => {
        isSubmitting.value = false;
      },
    },
  );
};
</script>

<template>
  <div class="vote-royal-bg flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div ref="rootRef" class="max-w-5xl w-full relative z-10">
      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader class="text-center pt-8 pb-5 px-6 border-b border-[#9c8256]/35">
          <CardTitle
            class="font-cinzel text-2xl sm:text-4xl font-extrabold text-[#0c1d44] uppercase tracking-wider"
          >
            Konfirmasi Pilihan
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-sm sm:text-base font-medium mt-1">
            Pastikan pilihanmu sudah sesuai sebelum melanjutkan!
          </CardDescription>

          <div class="flex flex-wrap justify-center gap-3.5 mt-5">
            <Link
              :href="
                route('vote.organization', {
                  organization: organization.id,
                })
              "
            >
              <Button
                class="vote-btn-outline rounded-lg px-8 py-2.5 text-xs sm:text-sm uppercase"
              >
                Kembali
              </Button>
            </Link>

            <ConfirmationDialog
              title="Apakah kamu yakin dengan pilihanmu?"
              description="Pilihanmu tidak dapat diubah setelah ini."
              @accept="submit"
            >
              <Button
                :disabled="isSubmitting"
                class="vote-btn-navy rounded-lg px-8 py-2.5 text-xs sm:text-sm uppercase disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting">Memproses...</span>
                <span v-else>Konfirmasi</span>
              </Button>
            </ConfirmationDialog>
          </div>
        </CardHeader>

        <CardContent class="pt-7 pb-10 px-6 sm:px-10">
          <div class="flex flex-wrap justify-center gap-7">
            <div
              v-for="detail in details"
              :key="detail.id"
              class="flex flex-col items-center gap-3 w-full max-w-[240px]"
            >
              <h3
                class="font-cinzel text-center text-[#0c1d44] text-sm sm:text-base font-extrabold uppercase tracking-wide"
              >
                {{ detail.group?.name }}
              </h3>

              <div
                class="vote-candidate-arch w-full px-4 pt-6 pb-5 text-center flex flex-col items-center"
              >
                <div class="relative mb-3.5">
                  <div
                    class="absolute -top-1.5 left-1/2 -translate-x-1/2 size-3 rotate-45 bg-[#d99b38] border border-[#5c3d12] z-10"
                  ></div>
                  <div
                    class="size-28 sm:size-32 rounded-full p-1 bg-gradient-to-b from-[#e4b35a] to-[#a9782a] shadow-md"
                  >
                    <img
                      :src="detail.candidate?.picture"
                      :alt="detail.candidate?.name_1"
                      class="w-full h-full rounded-full object-cover bg-[#0c1d44]"
                    />
                  </div>
                </div>

                <p
                  class="font-cinzel text-xs sm:text-sm font-bold text-[#0c1d44] leading-snug uppercase"
                >
                  {{ detail.candidate?.name_1 }}
                  <template v-if="detail.candidate?.name_2">
                    <br />
                    &amp;
                    <br />
                    {{ detail.candidate?.name_2 }}
                  </template>
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
