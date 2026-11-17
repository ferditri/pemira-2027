<script setup lang="ts">
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { BookImage } from "lucide-vue-next";
import { reactive, ref } from "vue";
import imageCompression from "browser-image-compression";

const form = useForm<{
  ktm: File | undefined;
  verification: File | undefined;
}>({
  ktm: undefined,
  verification: undefined,
});

const urls = reactive<{
  ktm: string | undefined;
  verification: string | undefined;
}>({
  ktm: "",
  verification: "",
});
const errorMessage = ref<string | null>(null);
const isCompressing = ref<boolean>(false);

const ktmInput = ref<HTMLInputElement | null>(null);
const verificationInput = ref<HTMLInputElement | null>(null);

const handleInput = async (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];

  errorMessage.value = null;
  if (!file) return;

  const options = {
    maxSizeMB: 1,
    maxWidthOrHeight: 1920,
    useWebWorker: true,
  };

  try {
    isCompressing.value = true;

    const compressedFile = await imageCompression(file, options);

    if (target === ktmInput.value) {
      form.ktm = compressedFile;
      urls.ktm = URL.createObjectURL(compressedFile);
    } else {
      form.verification = compressedFile;
      urls.verification = URL.createObjectURL(compressedFile);
    }
  } catch (error) {
    errorMessage.value =
      "Gagal mengompres gambar. Pastikan format gambar didukung.";
  } finally {
    isCompressing.value = false;
    target.value = "";
  }
};

const submit = () => {
  errorMessage.value = null;
  if (form.ktm && form.verification) {
    form.post(route("verification.store"), {
      preserveScroll: true,
      onError: (errors) => {
        if (errors.ktm) {
          errorMessage.value = `KTM: ${errors.ktm}`;
        } else if (errors.verification) {
          errorMessage.value = `Wajah: ${errors.verification}`;
        } else {
          errorMessage.value = "Server gagal memproses gambar";
        }
      },
    });
  }
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
  <input
    ref="ktmInput"
    type="file"
    class="absolute -top-full -left-full"
    accept="image/*"
    @change="handleInput"
  />
  <input
    ref="verificationInput"
    type="file"
    class="absolute -top-full -left-full"
    accept="image/*"
    @change="handleInput"
  />

  <div
    class="min-h-screen w-full relative overflow-x-hidden flex flex-col bg-[#F8E4BB] bg-[url('/background.webp')] bg-cover bg-center bg-fixed"
  >
    <div
      class="relative z-10 flex-1 flex flex-col items-center py-10 px-4 w-full"
    >
      <div class="max-w-md w-full space-y-4">
        <div
          v-if="isCompressing"
          class="font-body animate-pulse rounded-2xl bg-[#FBF3DE] p-3 text-center text-sm font-bold text-[#0F1B4D]"
        >
          Sedang mengoptimasi gambar... Mohon tunggu.
        </div>

        <div
          v-if="errorMessage"
          class="rounded-2xl border-2 border-[#6B1A24] bg-[#FBF3DE] p-4"
        >
          <p class="font-body text-sm font-bold text-[#6B1A24]">
            {{ errorMessage }}
          </p>
        </div>

        <div
          class="rounded-[2rem] bg-[#F8E4BB] px-8 py-6 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
        >
          <h1 class="font-title text-2xl font-black text-[#0F1B4D]">
            PROSES VERIFIKASI
          </h1>
          <p class="font-body mt-1 text-xs text-[#151333]">
            Pastikan foto KTM dan wajah kamu terlihat jelas yaa!
          </p>
        </div>

        <div
          class="rounded-[2rem] bg-[#F8E4BB] px-8 py-6 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
        >
          <h2 class="font-label text-lg font-bold text-[#0F1B4D]">FOTO KTM</h2>
          <p class="font-body mt-1 text-xs text-[#151333]">
            Pastikan foto KTM kamu terlihat jelas yaa!
          </p>
          <img
            v-if="urls.ktm"
            :src="urls.ktm"
            alt="Preview KTM"
            class="mt-4 w-full rounded-lg border-2 border-[#A86B1F] object-cover"
          />
          <button
            type="button"
            class="font-label mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] py-3 text-sm font-bold text-[#F8E4BB] transition hover:brightness-125"
            @click="ktmInput?.click()"
          >
            <BookImage class="size-4" />
            {{ form.ktm ? "GANTI GAMBAR" : "AMBIL GAMBAR" }}
          </button>
        </div>

        <div
          class="rounded-[2rem] bg-[#F8E4BB] px-8 py-6 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
        >
          <h2 class="font-label text-lg font-bold text-[#0F1B4D]">
            FOTO KTM DAN WAJAH
          </h2>
          <p class="font-body mt-1 text-xs text-[#151333]">
            Pastikan foto KTM dan wajah kamu terlihat jelas yaa!
          </p>
          <img
            v-if="urls.verification"
            :src="urls.verification"
            alt="Preview KTM dan wajah"
            class="mt-4 w-full rounded-lg border-2 border-[#A86B1F] object-cover"
          />
          <button
            type="button"
            class="font-label mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] py-3 text-sm font-bold text-[#F8E4BB] transition hover:brightness-125"
            @click="verificationInput?.click()"
          >
            <BookImage class="size-4" />
            {{ form.verification ? "GANTI GAMBAR" : "AMBIL GAMBAR" }}
          </button>
        </div>

        <div
          class="flex flex-row-reverse items-center justify-between gap-3 rounded-[2rem] bg-[#F8E4BB] px-8 py-5 shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
        >
          <button
            v-if="!form.ktm || !form.verification"
            type="button"
            disabled
            class="font-label cursor-not-allowed rounded-xl bg-gray-400 px-8 py-2.5 text-sm font-bold text-gray-700"
          >
            PROSES
          </button>
          <ConfirmationDialog
            v-else
            title="Apakah kamu yakin?"
            description="Kedua foto akan dikirimkan untuk proses verifikasi dari panitia"
            @accept="submit"
          >
            <button
              type="button"
              class="font-label rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] px-8 py-2.5 text-sm font-bold text-[#F8E4BB] transition hover:brightness-125"
            >
              PROSES
            </button>
          </ConfirmationDialog>

          <Link
            :href="route('index')"
            class="font-label rounded-xl border border-[#0F1B4D]/40 px-6 py-2.5 text-sm font-bold text-[#0F1B4D] hover:bg-[#0F1B4D]/10"
          >
            KEMBALI
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>