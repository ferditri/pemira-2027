<script setup lang="ts">
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
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
import { Link, useForm } from "@inertiajs/vue3";
import { ArrowLeft, BookImage, Camera, Eye, FileImage, HelpCircle, RefreshCw, X } from "lucide-vue-next";
import { nextTick, onUnmounted, reactive, ref } from "vue";
import imageCompression from "browser-image-compression";

const rootRef = ref<HTMLElement | null>(null);
useEnterMotion(rootRef, "[data-enter]", { y: 12, duration: 0.45, staggerDelay: 0.05 });

const props = defineProps<{
  verification?: UserVerification | null;
  exampleImages?: {
    ktm: string | null;
    selfie: string | null;
  };
}>();

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

const showExampleModal = ref(true);
const previewImage = ref<{ title: string; url: string } | null>(null);
const cameraTarget = ref<"ktm" | "verification" | null>(null);

const formatFileSize = (bytes?: number) => {
  if (!bytes) return "";
  if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
};

const clearPhoto = (targetField: "ktm" | "verification") => {
  if (targetField === "ktm") {
    if (urls.ktm) URL.revokeObjectURL(urls.ktm);
    form.ktm = undefined;
    urls.ktm = "";
  } else {
    if (urls.verification) URL.revokeObjectURL(urls.verification);
    form.verification = undefined;
    urls.verification = "";
  }
};
const facingMode = ref<"user" | "environment">("user");
const cameraError = ref<string | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
let mediaStream: MediaStream | null = null;

const stopCamera = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach((t) => t.stop());
    mediaStream = null;
  }
  cameraTarget.value = null;
  cameraError.value = null;
};

const startCameraStream = async () => {
  cameraError.value = null;
  if (mediaStream) {
    mediaStream.getTracks().forEach((t) => t.stop());
    mediaStream = null;
  }
  if (!navigator.mediaDevices?.getUserMedia) {
    cameraError.value =
      "Browser tidak mendukung kamera langsung. Silakan gunakan tombol Upload File.";
    return;
  }
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: facingMode.value },
      audio: false,
    });
    await nextTick();
    if (videoRef.value) {
      videoRef.value.srcObject = mediaStream;
    }
  } catch {
    cameraError.value =
      "Izin kamera ditolak atau kamera tidak ditemukan. Silakan gunakan tombol Upload File.";
  }
};

const openCamera = async (target: "ktm" | "verification") => {
  cameraTarget.value = target;
  facingMode.value = target === "ktm" ? "environment" : "user";
  await startCameraStream();
};

const switchCamera = async () => {
  facingMode.value = facingMode.value === "user" ? "environment" : "user";
  await startCameraStream();
};

const processImageFile = async (
  file: File,
  targetField: "ktm" | "verification",
) => {
  errorMessage.value = null;
  const options = {
    maxSizeMB: 1,
    maxWidthOrHeight: 1920,
    useWebWorker: true,
  };
  try {
    isCompressing.value = true;
    const compressedFile = await imageCompression(file, options);
    if (targetField === "ktm") {
      if (urls.ktm) URL.revokeObjectURL(urls.ktm);
      form.ktm = compressedFile;
      urls.ktm = URL.createObjectURL(compressedFile);
    } else {
      if (urls.verification) URL.revokeObjectURL(urls.verification);
      form.verification = compressedFile;
      urls.verification = URL.createObjectURL(compressedFile);
    }
  } catch {
    errorMessage.value =
      "Gagal mengompres gambar. Pastikan format gambar didukung.";
  } finally {
    isCompressing.value = false;
  }
};

const capturePhoto = async () => {
  if (!videoRef.value || !cameraTarget.value) return;
  const video = videoRef.value;
  const canvas = document.createElement("canvas");
  canvas.width = video.videoWidth || 1280;
  canvas.height = video.videoHeight || 720;
  const ctx = canvas.getContext("2d");
  if (!ctx) return;
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  const target = cameraTarget.value;
  stopCamera();

  const blob = await new Promise<Blob | null>((resolve) =>
    canvas.toBlob(resolve, "image/jpeg", 0.9),
  );
  if (!blob) return;
  const file = new File([blob], `${target}-${Date.now()}.jpg`, {
    type: "image/jpeg",
  });
  await processImageFile(file, target);
};

onUnmounted(() => {
  stopCamera();
  if (urls.ktm) URL.revokeObjectURL(urls.ktm);
  if (urls.verification) URL.revokeObjectURL(urls.verification);
});

const handleInput = async (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  const targetField = target === ktmInput.value ? "ktm" : "verification";
  target.value = "";
  await processImageFile(file, targetField);
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

  <div class="vote-royal-bg flex flex-col items-center justify-center py-10 px-4 sm:px-6">
    <div ref="rootRef" class="max-w-lg w-full space-y-4 relative z-10">
      <div
        v-if="props.verification?.status === 'retake' || props.verification?.status === 'rejected'"
        class="bg-amber-100/95 border border-amber-400 rounded-xl p-4 shadow-md text-amber-950 text-sm space-y-1"
      >
        <p class="font-bold">
          {{
            props.verification.status === "retake"
              ? "Diminta Upload Ulang (Re-take)"
              : "Verifikasi Sebelumnya Tidak Sah"
          }}
        </p>
        <p v-if="props.verification.admin_note" class="text-xs">
          Catatan Panitia: {{ props.verification.admin_note }}
        </p>
      </div>

      <div
        v-if="isCompressing"
        class="text-center text-[#0c1d44] bg-[#fcf6e8]/95 border border-[#ab8240] rounded-xl py-2.5 px-4 font-bold animate-pulse shadow-md"
      >
        Sedang mengoptimasi gambar... Mohon tunggu.
      </div>

      <div
        v-if="errorMessage"
        class="bg-red-100/95 border border-red-400 rounded-xl p-4 flex items-center gap-3 shadow-md"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="size-6 text-red-700 shrink-0"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <p class="text-red-900 text-sm font-medium">{{ errorMessage }}</p>
      </div>

      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader class="text-center pt-5 pb-5 px-6">
          <div class="flex justify-start mb-2">
            <a
              :href="route('landing')"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-[#9c8256]/40 bg-white/55 hover:bg-white/90 text-xs font-semibold text-[#2f3d59] hover:text-[#0c1d44] transition-colors min-h-[36px]"
            >
              <ArrowLeft class="size-3.5" />
              Kembali ke Beranda
            </a>
          </div>
          <CardTitle
            class="font-cinzel text-[#0c1d44] text-2xl sm:text-3xl font-extrabold tracking-wider uppercase"
          >
            Proses Verifikasi
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-sm sm:text-base font-medium mt-1">
            Pastikan foto KTM dan wajah kamu terlihat jelas yaa!
          </CardDescription>
          <div class="pt-3 flex justify-center">
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="vote-btn-outline rounded-lg gap-1.5 text-xs"
              @click="showExampleModal = true"
            >
              <HelpCircle class="size-4" />
              Lihat Contoh Foto yang Benar
            </Button>
          </div>
        </CardHeader>
      </Card>

      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader class="pb-3 pt-6 px-6">
          <CardTitle
            class="font-cinzel text-[#0c1d44] text-lg sm:text-xl font-bold tracking-wide uppercase"
          >
            Foto KTM
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-sm">
            Pastikan foto KTM kamu terlihat jelas yaa!
          </CardDescription>
        </CardHeader>
        <CardContent v-if="urls.ktm && form.ktm" class="px-5 sm:px-6 pb-3">
          <div class="flex items-center justify-between gap-2.5 rounded-xl border border-[#ab8240]/60 bg-white/85 px-3.5 py-2.5 shadow-sm">
            <button
              type="button"
              class="flex items-center gap-2.5 min-w-0 flex-1 text-left group"
              @click="previewImage = { title: 'Preview Foto KTM', url: urls.ktm! }"
            >
              <div class="size-9 rounded-lg bg-[#0c1d44]/10 text-[#0c1d44] flex items-center justify-center shrink-0">
                <FileImage class="size-4" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs sm:text-sm font-semibold text-[#0c1d44] truncate group-hover:underline">
                  {{ form.ktm.name }}
                </p>
                <p class="text-[11px] text-[#3d4a66]">
                  {{ formatFileSize(form.ktm.size) }} · Klik untuk preview
                </p>
              </div>
            </button>
            <div class="flex items-center gap-1 shrink-0">
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="h-8 px-2.5 text-xs border-[#ab8240]/50 text-[#0c1d44] hover:bg-[#0c1d44]/5 gap-1"
                @click="previewImage = { title: 'Preview Foto KTM', url: urls.ktm! }"
              >
                <Eye class="size-3.5" />
                <span class="hidden sm:inline">Preview</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="icon"
                class="size-8 text-red-700 hover:bg-red-100 hover:text-red-800"
                title="Hapus foto KTM"
                aria-label="Hapus foto KTM"
                @click="clearPhoto('ktm')"
              >
                <X class="size-4" />
              </Button>
            </div>
          </div>
        </CardContent>
        <CardFooter class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 px-5 sm:px-6 pb-5 sm:pb-6">
          <Button
            type="button"
            class="gap-2 vote-btn-navy rounded-xl font-bold text-xs sm:text-sm py-4 sm:py-5 px-4 uppercase"
            @click="openCamera('ktm')"
          >
            <Camera class="size-4 shrink-0" />
            Buka Kamera
          </Button>
          <Button
            type="button"
            variant="outline"
            class="gap-2 vote-btn-outline rounded-xl font-bold text-xs sm:text-sm py-4 sm:py-5 px-4 uppercase"
            @click="ktmInput?.click()"
          >
            <BookImage class="size-4 shrink-0" />
            Upload File
          </Button>
        </CardFooter>
      </Card>

      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader class="pb-3 pt-6 px-6">
          <CardTitle
            class="font-cinzel text-[#0c1d44] text-lg sm:text-xl font-bold tracking-wide uppercase"
          >
            Foto KTM dan Wajah
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-sm">
            Pastikan foto KTM dan wajah kamu terlihat jelas yaa!
          </CardDescription>
        </CardHeader>
        <CardContent v-if="urls.verification && form.verification" class="px-5 sm:px-6 pb-3">
          <div class="flex items-center justify-between gap-2.5 rounded-xl border border-[#ab8240]/60 bg-white/85 px-3.5 py-2.5 shadow-sm">
            <button
              type="button"
              class="flex items-center gap-2.5 min-w-0 flex-1 text-left group"
              @click="previewImage = { title: 'Preview Foto KTM & Wajah', url: urls.verification! }"
            >
              <div class="size-9 rounded-lg bg-[#0c1d44]/10 text-[#0c1d44] flex items-center justify-center shrink-0">
                <FileImage class="size-4" />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs sm:text-sm font-semibold text-[#0c1d44] truncate group-hover:underline">
                  {{ form.verification.name }}
                </p>
                <p class="text-[11px] text-[#3d4a66]">
                  {{ formatFileSize(form.verification.size) }} · Klik untuk preview
                </p>
              </div>
            </button>
            <div class="flex items-center gap-1 shrink-0">
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="h-8 px-2.5 text-xs border-[#ab8240]/50 text-[#0c1d44] hover:bg-[#0c1d44]/5 gap-1"
                @click="previewImage = { title: 'Preview Foto KTM & Wajah', url: urls.verification! }"
              >
                <Eye class="size-3.5" />
                <span class="hidden sm:inline">Preview</span>
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="icon"
                class="size-8 text-red-700 hover:bg-red-100 hover:text-red-800"
                title="Hapus foto selfie"
                aria-label="Hapus foto selfie"
                @click="clearPhoto('verification')"
              >
                <X class="size-4" />
              </Button>
            </div>
          </div>
        </CardContent>
        <CardFooter class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 px-5 sm:px-6 pb-5 sm:pb-6">
          <Button
            type="button"
            class="gap-2 vote-btn-navy rounded-xl font-bold text-xs sm:text-sm py-4 sm:py-5 px-4 uppercase"
            @click="openCamera('verification')"
          >
            <Camera class="size-4 shrink-0" />
            Buka Kamera
          </Button>
          <Button
            type="button"
            variant="outline"
            class="gap-2 vote-btn-outline rounded-xl font-bold text-xs sm:text-sm py-4 sm:py-5 px-4 uppercase"
            @click="verificationInput?.click()"
          >
            <BookImage class="size-4 shrink-0" />
            Upload File
          </Button>
        </CardFooter>
      </Card>

      <div class="vote-parchment-card p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row-reverse justify-between gap-2.5 sm:gap-3">
          <Button
            v-if="!form.ktm || !form.verification"
            disabled
            class="w-full sm:w-auto bg-gray-400/70 text-gray-700 cursor-not-allowed rounded-xl font-cinzel font-bold text-sm py-4 sm:py-5 px-7 uppercase"
          >
            Proses
          </Button>
          <ConfirmationDialog
            v-else
            title="Apakah kamu yakin?"
            description="Kedua foto akan dikirimkan untuk proses verifikasi dari panitia"
            @accept="submit"
          >
            <Button
              :disabled="
                form.processing || isCompressing || !form.ktm || !form.verification
              "
              class="w-full sm:w-auto vote-btn-navy rounded-xl text-sm py-4 sm:py-5 px-7 uppercase disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Mengunggah...</span>
              <span v-else-if="isCompressing">Mengompres...</span>
              <span v-else>Proses</span>
            </Button>
          </ConfirmationDialog>

          <Link :href="route('index')" class="w-full sm:w-auto">
            <Button
              variant="outline"
              class="w-full sm:w-auto vote-btn-outline px-7 py-4 sm:py-5 rounded-xl text-sm uppercase"
            >
              Kembali
            </Button>
          </Link>
        </div>
      </div>
    </div>
  </div>

  <!-- Popup Contoh Foto -->
  <div
    v-if="showExampleModal"
    class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
    @click.self="showExampleModal = false"
  >
    <Card class="vote-parchment-card border-0 max-w-lg w-full max-h-[90vh] overflow-y-auto">
      <CardHeader class="flex-row items-center justify-between border-b border-[#9c8256]/35 pb-4">
        <div>
          <CardTitle class="font-cinzel text-[#0c1d44] text-lg font-bold uppercase">
            Panduan Foto Verifikasi
          </CardTitle>
          <CardDescription class="text-[#3d4a66] text-xs mt-0.5">
            Ikuti contoh berikut agar verifikasi kamu langsung disetujui panitia.
          </CardDescription>
        </div>
        <Button variant="ghost" size="icon" @click="showExampleModal = false">
          <X class="size-4 text-[#0c1d44]" />
        </Button>
      </CardHeader>
      <CardContent class="p-5 space-y-4">
        <div class="rounded-xl border border-[#ab8240]/50 bg-white/70 p-3 space-y-2">
          <p class="text-xs font-bold text-[#0c1d44] uppercase">
            1. Contoh Foto KTM
          </p>
          <div
            v-if="props.exampleImages?.ktm"
            class="aspect-[16/9] rounded-lg overflow-hidden border bg-white"
          >
            <img
              :src="props.exampleImages.ktm"
              alt="Contoh Foto KTM"
              class="w-full h-full object-contain"
            />
          </div>
          <div
            v-else
            class="aspect-[16/9] rounded-lg bg-[#0c1d44] text-white p-3 flex flex-col justify-between"
          >
            <div class="flex justify-between text-[10px] text-[#fce7ba] font-semibold">
              <span>KARTU TANDA MAHASISWA</span>
              <span>UPNVJT</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-12 h-14 rounded bg-white/15 border border-white/30 flex items-center justify-center text-[10px]">
                FOTO
              </div>
              <div class="text-xs space-y-0.5">
                <div class="font-bold">NAMA LENGKAP</div>
                <div class="font-mono text-[11px] text-[#fce7ba]">25082010XXX</div>
                <div class="text-[10px] text-white/75">FASILKOM</div>
              </div>
            </div>
            <div class="text-[10px] text-emerald-300">
              ✓ Pastikan Nama &amp; NPM terbaca jelas, tidak blur/silau
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-[#ab8240]/50 bg-white/70 p-3 space-y-2">
          <p class="text-xs font-bold text-[#0c1d44] uppercase">
            2. Contoh Foto Selfie Memegang KTM
          </p>
          <div
            v-if="props.exampleImages?.selfie"
            class="aspect-[16/9] rounded-lg overflow-hidden border bg-white"
          >
            <img
              :src="props.exampleImages.selfie"
              alt="Contoh Selfie Memegang KTM"
              class="w-full h-full object-contain"
            />
          </div>
          <div
            v-else
            class="aspect-[16/9] rounded-lg bg-[#0c1d44] text-white p-3 flex items-center justify-center gap-4"
          >
            <div class="flex flex-col items-center gap-1">
              <div class="size-11 rounded-full bg-white/15 border border-white/40 flex items-center justify-center text-[10px]">
                WAJAH
              </div>
              <div class="w-16 h-7 rounded-t-xl bg-white/15 border border-white/30" />
            </div>
            <div class="w-24 h-14 rounded bg-[#fce7ba] text-[#0c1d44] p-1.5 flex flex-col justify-between text-[9px] font-bold shadow">
              <span>KTM ASLI</span>
              <span class="font-mono text-[8px]">25082010XXX</span>
            </div>
          </div>
          <ul class="text-xs text-[#3d4a66] space-y-1 list-disc list-inside">
            <li>Wajah terlihat jelas (tanpa masker/kacamata hitam).</li>
            <li>Pegang KTM asli di samping wajah, bukan foto dari layar HP lain.</li>
          </ul>
        </div>
      </CardContent>
      <CardFooter class="px-5 pb-5">
        <Button
          type="button"
          class="w-full vote-btn-navy rounded-xl py-5 text-sm uppercase"
          @click="showExampleModal = false"
        >
          Saya Paham, Mulai Foto
        </Button>
      </CardFooter>
    </Card>
  </div>

  <!-- Popup Preview Foto yang Diupload -->
  <div
    v-if="previewImage"
    class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
    @click.self="previewImage = null"
  >
    <Card class="vote-parchment-card border-0 max-w-lg w-full overflow-hidden">
      <CardHeader class="flex-row items-center justify-between py-3 px-4 border-b border-[#9c8256]/35">
        <CardTitle class="font-cinzel text-[#0c1d44] text-sm sm:text-base font-bold uppercase">
          {{ previewImage.title }}
        </CardTitle>
        <Button type="button" variant="ghost" size="icon" @click="previewImage = null">
          <X class="size-4 text-[#0c1d44]" />
        </Button>
      </CardHeader>
      <CardContent class="p-4 flex items-center justify-center bg-black/5">
        <img
          :src="previewImage.url"
          :alt="previewImage.title"
          class="max-h-[65dvh] w-auto max-w-full rounded-lg border border-[#ab8240] object-contain"
        />
      </CardContent>
      <CardFooter class="px-4 pb-4 pt-2 justify-end">
        <Button type="button" class="vote-btn-navy rounded-lg px-6 text-xs uppercase" @click="previewImage = null">
          Tutup
        </Button>
      </CardFooter>
    </Card>
  </div>

  <!-- Popup Kamera Langsung -->
  <div
    v-if="cameraTarget"
    class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3 sm:p-4"
  >
    <Card class="max-w-md w-full max-h-[90dvh] overflow-y-auto">
      <CardHeader class="flex-row items-center justify-between py-3 px-4 border-b">
        <CardTitle class="text-base">
          {{ cameraTarget === "ktm" ? "Kamera: Foto KTM" : "Kamera: Selfie Memegang KTM" }}
        </CardTitle>
        <div class="flex items-center gap-1">
          <Button
            type="button"
            variant="ghost"
            size="icon"
            title="Ganti Kamera Depan/Belakang"
            @click="switchCamera"
          >
            <RefreshCw class="size-4" />
          </Button>
          <Button type="button" variant="ghost" size="icon" @click="stopCamera">
            <X class="size-4" />
          </Button>
        </div>
      </CardHeader>
      <CardContent class="p-4 space-y-3">
        <div
          v-if="cameraError"
          class="rounded-lg bg-red-50 border border-red-300 p-3 text-xs text-red-800"
        >
          {{ cameraError }}
        </div>
        <div v-else class="rounded-lg overflow-hidden bg-black aspect-[4/3]">
          <video
            ref="videoRef"
            autoplay
            playsinline
            muted
            class="w-full h-full object-cover"
          />
        </div>
      </CardContent>
      <CardFooter class="flex justify-between gap-2 px-4 pb-4">
        <Button type="button" variant="outline" @click="stopCamera">
          Batal
        </Button>
        <Button
          v-if="!cameraError"
          type="button"
          class="vote-btn-navy gap-2"
          @click="capturePhoto"
        >
          <Camera class="size-4" />
          Ambil Foto
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>
