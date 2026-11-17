<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps<{
  organization: Organization;
  details: BallotDetail[];
}>();

const submit = () => {
  router.post(
    route("vote.result.confirm", {
      organization: props.organization.id,
    }),
  );
};
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
    class="min-h-dvh flex justify-center p-4 sm:p-6 lg:p-8 bg-[#F8E4BB] bg-[url('/background.webp')] bg-cover bg-center bg-fixed relative"
  >
    <div class="w-full max-w-5xl relative z-10">
      <div
        class="rounded-[2.5rem] bg-[#F8E4BB] px-6 py-10 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
      >
        <h1 class="font-title text-3xl sm:text-5xl font-black text-[#0F1B4D]">
          KONFIRMASI PILIHAN
        </h1>
        <p class="font-label mt-1 text-base font-bold text-[#151333]">
          PASTIKAN PILIHANMU SUDAH SESUAI SEBELUM MELANJUTKAN
        </p>

        <div class="mt-5 flex justify-center gap-3">
          <Link
            :href="
              route('vote.organization', {
                organization: organization.id,
              })
            "
            class="font-label rounded-md bg-[#0F1B4D] px-8 py-2 text-sm font-bold text-[#F8E4BB] hover:brightness-125"
          >
            KEMBALI
          </Link>

          <ConfirmationDialog
            title="Apakah kamu yakin dengan pilihanmu?"
            description="Pilihanmu tidak dapat diubah setelah ini."
            @accept="submit"
          >
            <button
              type="button"
              class="font-label rounded-md border border-[#A86B1F] bg-[#FBF3DE] px-8 py-2 text-sm font-bold text-[#0F1B4D] hover:brightness-95"
            >
              KONFIRMASI
            </button>
          </ConfirmationDialog>
        </div>

        <div class="mt-10 flex flex-wrap items-stretch justify-center gap-10">
          <div
            v-for="detail in details"
            :key="detail.id"
            class="flex w-80 flex-col items-center gap-3"
          >
            <p class="font-title text-xl font-black text-[#0F1B4D]">
              {{ detail.group?.name }}
            </p>

            <div
              class="flex w-full flex-1 flex-col items-center rounded-t-[10rem] rounded-b-3xl border-4 border-[#A86B1F] bg-[#FBF3DE] px-6 pb-6 pt-12 shadow-xl"
            >
              <img
                v-if="detail.candidate?.picture"
                :src="detail.candidate.picture"
                :alt="detail.candidate?.name_1"
                class="size-56 rounded-full border-4 border-[#A86B1F] object-cover object-top"
              />
              <div
                v-else
                class="size-56 rounded-full border-4 border-[#A86B1F] bg-[#F8E4BB]"
              ></div>

              <p
                class="font-label mt-5 text-lg font-bold leading-snug text-[#0F1B4D]"
              >
                {{ detail.candidate?.name_1 }}
                <template v-if="detail.candidate?.name_2">
                  <br />&amp;<br />
                  {{ detail.candidate?.name_2 }}
                </template>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>