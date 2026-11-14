<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
} from "@/components/ui/breadcrumb";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { formatMajor } from "@/lib/majors";
import { User } from "@/types";
import { router, useForm } from "@inertiajs/vue3";
import {
  Archive,
  ArrowLeft,
  ArrowRight,
  CheckCircle2,
  Clock,
  History,
  RotateCcw,
  Search,
  X,
  XCircle,
  ZoomIn,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

interface VerificationItem extends UserVerification {
  user: User & {
    verification_histories?: UserVerification[];
  };
}

const props = defineProps<{
  verifications: VerificationItem[];
  stats: {
    total: number;
    pending: number;
    approved: number;
    retake: number;
    rejected: number;
  };
  exampleImages?: {
    ktm: string | null;
    selfie: string | null;
  };
  filters: {
    status: string;
    search: string;
  };
}>();

const currentIndex = ref(0);
const searchText = ref(props.filters.search ?? "");
const selectedStatus = ref(props.filters.status ?? "all");
const zoomImage = ref<string | null>(null);
const showHistory = ref(false);
const showExampleEditor = ref(false);

const exampleForm = useForm({
  ktm: undefined as File | undefined,
  selfie: undefined as File | undefined,
  reset_ktm: false,
  reset_selfie: false,
});

const saveExamples = () => {
  exampleForm.post(route("admin.verifications.examples"), {
    preserveScroll: true,
    onSuccess: () => {
      exampleForm.reset();
      showExampleEditor.value = false;
    },
  });
};

const currentItem = computed<VerificationItem | null>(() => {
  if (!props.verifications.length) return null;
  const safeIndex = Math.min(currentIndex.value, props.verifications.length - 1);
  return props.verifications[safeIndex] ?? null;
});

const reviewForm = useForm({
  status: "approved" as "approved" | "rejected" | "retake",
  admin_note: "",
});

watch(
  currentItem,
  (item) => {
    reviewForm.admin_note = item?.admin_note ?? "";
  },
  { immediate: true },
);

watch(
  () => props.verifications.length,
  (len) => {
    if (currentIndex.value >= len) {
      currentIndex.value = Math.max(0, len - 1);
    }
  },
);

const applyFilter = (statusVal = selectedStatus.value) => {
  selectedStatus.value = statusVal;
  currentIndex.value = 0;
  router.get(
    route("admin.verifications.index"),
    {
      status: statusVal,
      search: searchText.value.trim(),
    },
    { preserveState: true, preserveScroll: true },
  );
};

const goPrev = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--;
    showHistory.value = false;
  }
};

const goNext = () => {
  if (currentIndex.value < props.verifications.length - 1) {
    currentIndex.value++;
    showHistory.value = false;
  }
};

const selectItem = (idx: number) => {
  currentIndex.value = idx;
  showHistory.value = false;
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const submitReview = (status: "approved" | "rejected" | "retake") => {
  if (!currentItem.value) return;
  reviewForm.status = status;
  reviewForm.post(
    route("admin.verifications.review", {
      verification: currentItem.value.id,
    }),
    {
      preserveScroll: true,
      onSuccess: () => {
        if (currentIndex.value < props.verifications.length - 1) {
          currentIndex.value++;
        }
      },
    },
  );
};

const statusLabel = (status?: string) => {
  switch (status) {
    case "approved":
      return "Sah";
    case "retake":
      return "Perlu Re-take";
    case "rejected":
      return "Tidak Sah";
    default:
      return "Menunggu";
  }
};

const statusBadgeClass = (status?: string) => {
  switch (status) {
    case "approved":
      return "bg-emerald-100 text-emerald-800 border-emerald-300";
    case "retake":
      return "bg-amber-100 text-amber-800 border-amber-300";
    case "rejected":
      return "bg-red-100 text-red-800 border-red-300";
    default:
      return "bg-slate-100 text-slate-800 border-slate-300";
  }
};

const majorName = (code?: string) => formatMajor(code);

const archiveItem = (id: number) => {
  useForm({}).post(
    route("admin.verifications.archive", { verification: id }),
    { preserveScroll: true },
  );
};

const handleKeydown = (e: KeyboardEvent) => {
  const tag = (e.target as HTMLElement)?.tagName;
  if (tag === "INPUT" || tag === "TEXTAREA") return;
  if (e.key === "ArrowLeft") goPrev();
  if (e.key === "ArrowRight") goNext();
  if (e.key === "Escape") {
    if (zoomImage.value) {
      zoomImage.value = null;
    } else {
      showHistory.value = false;
    }
  }
};

onMounted(() => window.addEventListener("keydown", handleKeydown));
onUnmounted(() => window.removeEventListener("keydown", handleKeydown));
</script>

<template>
  <Teleport defer to="#header">
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <BreadcrumbPage>Verifikasi KTM &amp; Selfie</BreadcrumbPage>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>
  </Teleport>

  <div class="flex-1 space-y-4">
    <!-- Ringkasan Status -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
      <Card
        class="cursor-pointer transition hover:border-primary"
        :class="selectedStatus === 'all' ? 'ring-2 ring-primary' : ''"
        @click="applyFilter('all')"
      >
        <CardHeader class="p-4 pb-2">
          <CardDescription>Total Upload</CardDescription>
          <CardTitle class="text-2xl">{{ stats.total }}</CardTitle>
        </CardHeader>
      </Card>
      <Card
        class="cursor-pointer transition hover:border-primary"
        :class="selectedStatus === 'pending' ? 'ring-2 ring-primary' : ''"
        @click="applyFilter('pending')"
      >
        <CardHeader class="p-4 pb-2">
          <CardDescription class="flex items-center gap-1">
            <Clock class="size-3.5" /> Menunggu
          </CardDescription>
          <CardTitle class="text-2xl">{{ stats.pending }}</CardTitle>
        </CardHeader>
      </Card>
      <Card
        class="cursor-pointer transition hover:border-emerald-500"
        :class="selectedStatus === 'approved' ? 'ring-2 ring-emerald-500' : ''"
        @click="applyFilter('approved')"
      >
        <CardHeader class="p-4 pb-2">
          <CardDescription class="flex items-center gap-1 text-emerald-700">
            <CheckCircle2 class="size-3.5" /> Sah
          </CardDescription>
          <CardTitle class="text-2xl text-emerald-700">{{ stats.approved }}</CardTitle>
        </CardHeader>
      </Card>
      <Card
        class="cursor-pointer transition hover:border-amber-500"
        :class="selectedStatus === 'retake' ? 'ring-2 ring-amber-500' : ''"
        @click="applyFilter('retake')"
      >
        <CardHeader class="p-4 pb-2">
          <CardDescription class="flex items-center gap-1 text-amber-700">
            <RotateCcw class="size-3.5" /> Minta Re-take
          </CardDescription>
          <CardTitle class="text-2xl text-amber-700">{{ stats.retake }}</CardTitle>
        </CardHeader>
      </Card>
      <Card
        class="cursor-pointer transition hover:border-red-500"
        :class="selectedStatus === 'rejected' ? 'ring-2 ring-red-500' : ''"
        @click="applyFilter('rejected')"
      >
        <CardHeader class="p-4 pb-2">
          <CardDescription class="flex items-center gap-1 text-red-700">
            <XCircle class="size-3.5" /> Tidak Sah
          </CardDescription>
          <CardTitle class="text-2xl text-red-700">{{ stats.rejected }}</CardTitle>
        </CardHeader>
      </Card>
    </div>

    <!-- Search Bar -->
    <form class="flex gap-2" @submit.prevent="applyFilter(selectedStatus)">
      <div class="relative flex-1 max-w-md">
        <Search class="size-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2" />
        <Input
          v-model="searchText"
          placeholder="Cari nama atau NPM pemilih..."
          class="pl-9"
        />
      </div>
      <Button type="submit" variant="outline">Cari</Button>
      <Button
        v-if="filters.search || filters.status !== 'all'"
        type="button"
        variant="ghost"
        @click="
          searchText = '';
          applyFilter('all');
        "
      >
        Reset Filter
      </Button>
    </form>

    <!-- Panel Utama 2 Kolom (Sesuai Lampiran 1: Kiri Contoh Freeze, Kanan Validasi + Panah) -->
    <div class="grid lg:grid-cols-12 gap-4 items-start">
      <!-- KIRI: Contoh (Freeze / Sticky) -->
      <Card class="lg:col-span-4 lg:sticky lg:top-4 bg-muted/30">
        <CardHeader class="pb-3 flex-row items-start justify-between gap-2">
          <div>
            <CardTitle class="text-base">Contoh</CardTitle>
            <CardDescription>
              Patokan untuk membandingkan foto KTM &amp; selfie pemilih.
            </CardDescription>
          </div>
          <Button
            variant="outline"
            size="sm"
            class="shrink-0 text-xs"
            @click="showExampleEditor = true"
          >
            Custom
          </Button>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="rounded-lg border bg-background p-3 space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              1. Contoh Foto KTM
            </p>
            <div
              v-if="exampleImages?.ktm"
              class="aspect-[16/10] rounded-md border overflow-hidden bg-muted cursor-pointer"
              @click="zoomImage = exampleImages.ktm"
            >
              <img
                :src="exampleImages.ktm"
                alt="Contoh Foto KTM"
                class="w-full h-full object-contain"
              />
            </div>
            <div
              v-else
              class="aspect-[16/10] rounded-md border bg-slate-900 text-slate-100 p-3 flex flex-col justify-between"
            >
              <div class="flex items-center justify-between text-[10px] text-amber-300 font-semibold">
                <span>KARTU TANDA MAHASISWA</span>
                <span>UPN VETERAN JATIM</span>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-12 h-14 rounded bg-slate-700 border border-slate-500 flex items-center justify-center text-[10px] text-slate-300">
                  FOTO
                </div>
                <div class="space-y-1 text-xs">
                  <div class="font-bold">NAMA MAHASISWA</div>
                  <div class="font-mono text-[11px] text-slate-300">25082010XXX</div>
                  <div class="text-[10px] text-slate-400">FASILKOM - UPNVJT</div>
                </div>
              </div>
              <div class="text-[10px] text-emerald-400">
                ✓ NPM &amp; Nama harus terbaca jelas, tidak buram/terpotong
              </div>
            </div>
          </div>

          <div class="rounded-lg border bg-background p-3 space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              2. Contoh Foto Megang KTM (Selfie)
            </p>
            <div
              v-if="exampleImages?.selfie"
              class="aspect-[16/10] rounded-md border overflow-hidden bg-muted cursor-pointer"
              @click="zoomImage = exampleImages.selfie"
            >
              <img
                :src="exampleImages.selfie"
                alt="Contoh Selfie Megang KTM"
                class="w-full h-full object-contain"
              />
            </div>
            <div
              v-else
              class="aspect-[16/10] rounded-md border bg-slate-900 text-slate-100 p-3 flex items-center justify-center gap-4"
            >
              <div class="flex flex-col items-center gap-1">
                <div class="size-10 rounded-full bg-slate-700 border border-slate-400 flex items-center justify-center text-[10px]">
                  WAJAH
                </div>
                <div class="w-14 h-7 rounded-t-xl bg-slate-700 border border-slate-500" />
              </div>
              <div class="w-20 h-12 rounded bg-amber-100 text-slate-900 p-1.5 flex flex-col justify-between text-[9px] font-bold shadow">
                <span>KTM ASLI</span>
                <span class="font-mono text-[8px]">25082010XXX</span>
              </div>
            </div>
            <p class="text-[11px] text-muted-foreground">
              ✓ Wajah terlihat jelas dan memegang KTM asli (bukan foto layar/editan).
            </p>
          </div>
        </CardContent>
      </Card>

      <!-- KANAN: Area Validasi -->
      <Card class="lg:col-span-8">
        <template v-if="currentItem">
          <CardHeader class="border-b">
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="flex items-center gap-2 flex-wrap">
                  <CardTitle class="text-xl">
                    {{ currentItem.user.name }} ({{ currentItem.user.npm }})
                  </CardTitle>
                  <span
                    class="text-xs font-semibold px-2.5 py-0.5 rounded-full border"
                    :class="statusBadgeClass(currentItem.status)"
                  >
                    {{ statusLabel(currentItem.status) }}
                  </span>
                </div>
                <CardDescription class="mt-1">
                  {{ majorName(currentItem.user.major) }} · Angkatan 20{{ currentItem.user.year }} ·
                  Data {{ currentIndex + 1 }} dari {{ verifications.length }}
                </CardDescription>
              </div>
              <Button
                v-if="(currentItem.user.verification_histories?.length ?? 0) > 0"
                variant="outline"
                size="sm"
                @click="showHistory = !showHistory"
              >
                <History class="size-4 mr-1.5" />
                Riwayat ({{ currentItem.user.verification_histories?.length ?? 0 }})
              </Button>
            </div>
          </CardHeader>

          <CardContent class="p-6 space-y-5">
            <!-- Foto KTM & Foto Megang KTM -->
            <div class="grid md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label class="text-xs uppercase tracking-wide text-muted-foreground">
                  Foto KTM
                </Label>
                <div
                  class="relative group rounded-lg border overflow-hidden bg-muted aspect-[4/3] cursor-pointer"
                  @click="zoomImage = '/storage/' + currentItem.ktm"
                >
                  <img
                    v-if="currentItem.ktm"
                    :src="'/storage/' + currentItem.ktm"
                    alt="Foto KTM"
                    class="w-full h-full object-contain"
                  />
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs gap-1">
                    <ZoomIn class="size-4" /> Klik untuk perbesar
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-xs uppercase tracking-wide text-muted-foreground">
                  Foto Megang KTM (Selfie)
                </Label>
                <div
                  class="relative group rounded-lg border overflow-hidden bg-muted aspect-[4/3] cursor-pointer"
                  @click="zoomImage = '/storage/' + currentItem.verification"
                >
                  <img
                    v-if="currentItem.verification"
                    :src="'/storage/' + currentItem.verification"
                    alt="Foto Megang KTM"
                    class="w-full h-full object-contain"
                  />
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs gap-1">
                    <ZoomIn class="size-4" /> Klik untuk perbesar
                  </div>
                </div>
              </div>
            </div>

            <!-- Catatan Admin -->
            <div class="space-y-2">
              <Label>Catatan Panitia (opsional, wajib jelas jika minta Re-take / Tidak Sah)</Label>
              <Textarea
                v-model="reviewForm.admin_note"
                rows="2"
                placeholder="Contoh: Foto KTM buram / wajah tertutup masker, silakan foto ulang."
              />
            </div>

            <!-- Tombol Sah / Minta Re-take / Tidak Sah -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t">
              <div class="flex flex-wrap gap-2">
                <Button
                  class="bg-emerald-600 hover:bg-emerald-700 text-white"
                  :disabled="reviewForm.processing"
                  @click="submitReview('approved')"
                >
                  <CheckCircle2 class="size-4 mr-1.5" />
                  Sah
                </Button>
                <Button
                  variant="outline"
                  class="border-amber-500 text-amber-700 hover:bg-amber-50"
                  :disabled="reviewForm.processing"
                  @click="submitReview('retake')"
                >
                  <RotateCcw class="size-4 mr-1.5" />
                  Minta Re-take
                </Button>
                <Button
                  variant="destructive"
                  :disabled="reviewForm.processing"
                  @click="submitReview('rejected')"
                >
                  <XCircle class="size-4 mr-1.5" />
                  Tidak Sah
                </Button>
              </div>

              <!-- Panah Kiri / Kanan -->
              <div class="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="icon"
                  :disabled="currentIndex === 0"
                  title="Sebelumnya (Panah Kiri)"
                  @click="goPrev"
                >
                  <ArrowLeft class="size-4" />
                </Button>
                <span class="text-xs text-muted-foreground min-w-[52px] text-center">
                  {{ currentIndex + 1 }} / {{ verifications.length }}
                </span>
                <Button
                  variant="outline"
                  size="icon"
                  :disabled="currentIndex >= verifications.length - 1"
                  title="Berikutnya (Panah Kanan)"
                  @click="goNext"
                >
                  <ArrowRight class="size-4" />
                </Button>
              </div>
            </div>
          </CardContent>
        </template>

        <CardContent v-else class="p-10 text-center text-muted-foreground">
          Belum ada data upload verifikasi untuk filter ini.
        </CardContent>
      </Card>
    </div>

    <!-- Daftar Tabel Semua Pemilih yang Sudah Upload (Klik untuk lompat) -->
    <Card>
      <CardHeader>
        <CardTitle class="text-base">Daftar Pemilih yang Sudah Upload</CardTitle>
        <CardDescription>
          Klik salah satu baris untuk langsung memeriksa fotonya di panel atas.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>NPM</TableHead>
              <TableHead>Nama</TableHead>
              <TableHead>Prodi</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Catatan</TableHead>
              <TableHead class="w-[60px] text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="!verifications.length">
              <TableCell colspan="6" class="text-center text-muted-foreground">
                Tidak ada data.
              </TableCell>
            </TableRow>
            <TableRow
              v-for="(item, idx) in verifications"
              :key="item.id"
              class="cursor-pointer hover:bg-muted/50"
              :class="idx === currentIndex ? 'bg-muted/70 font-medium' : ''"
              @click="selectItem(idx)"
            >
              <TableCell class="font-mono">{{ item.user.npm }}</TableCell>
              <TableCell>{{ item.user.name }}</TableCell>
              <TableCell>{{ majorName(item.user.major) }}</TableCell>
              <TableCell>
                <span
                  class="text-xs font-semibold px-2 py-0.5 rounded-full border"
                  :class="statusBadgeClass(item.status)"
                >
                  {{ statusLabel(item.status) }}
                </span>
              </TableCell>
              <TableCell class="text-xs text-muted-foreground max-w-xs truncate">
                {{ item.admin_note || "-" }}
              </TableCell>
              <TableCell class="text-right" @click.stop>
                <ConfirmationDialog
                  title="Arsipkan data ini?"
                  description="Data akan disembunyikan dari daftar aktif tanpa dihapus dari database/riwayat."
                  @accept="archiveItem(item.id)"
                >
                  <Button
                    variant="ghost"
                    size="icon"
                    class="size-8 text-muted-foreground hover:text-destructive"
                    title="Arsipkan (nonaktifkan tanpa hapus)"
                  >
                    <Archive class="size-4" />
                  </Button>
                </ConfirmationDialog>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>

  <!-- Popup Modal Riwayat Upload -->
  <div
    v-if="showHistory && currentItem"
    class="fixed inset-0 z-40 bg-black/60 flex items-center justify-center p-4"
    @click.self="showHistory = false"
  >
    <Card class="max-w-2xl w-full max-h-[85vh] flex flex-col">
      <CardHeader class="flex-row items-center justify-between border-b py-4">
        <div>
          <CardTitle class="text-base flex items-center gap-2">
            <History class="size-4" />
            Riwayat Upload — {{ currentItem.user.name }}
          </CardTitle>
          <CardDescription>
            Semua foto yang pernah dikirim tetap tersimpan di database (tidak dihapus).
          </CardDescription>
        </div>
        <Button variant="ghost" size="icon" @click="showHistory = false">
          <X class="size-4" />
        </Button>
      </CardHeader>
      <CardContent class="p-4 overflow-y-auto space-y-3">
        <div
          v-if="!currentItem.user.verification_histories?.length"
          class="text-center text-sm text-muted-foreground py-6"
        >
          Belum ada riwayat upload.
        </div>
        <div
          v-for="(hist, i) in currentItem.user.verification_histories"
          :key="hist.id"
          class="rounded-lg border p-3 space-y-2.5 bg-muted/20"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
              <span class="font-semibold">#{{ (currentItem.user.verification_histories?.length ?? 0) - i }}</span>
              <span
                class="px-2 py-0.5 rounded-full border font-medium"
                :class="statusBadgeClass(hist.status)"
              >
                {{ statusLabel(hist.status) }}
              </span>
              <span v-if="hist.is_active" class="text-primary font-semibold">(Aktif)</span>
              <span v-else class="text-muted-foreground">(Arsip Lama)</span>
            </div>
            <span v-if="hist.created_at" class="text-muted-foreground">
              {{ new Date(hist.created_at).toLocaleString('id-ID') }}
            </span>
          </div>
          <p v-if="hist.admin_note" class="text-xs text-muted-foreground">
            Catatan: "{{ hist.admin_note }}"
          </p>
          <div class="grid grid-cols-2 gap-3">
            <div
              class="rounded border bg-background overflow-hidden cursor-pointer aspect-[4/3] relative group"
              @click="zoomImage = '/storage/' + hist.ktm"
            >
              <img :src="'/storage/' + hist.ktm" alt="KTM" class="w-full h-full object-contain" />
              <div class="absolute bottom-1 left-1 bg-black/65 text-white text-[10px] px-1.5 py-0.5 rounded">
                KTM
              </div>
            </div>
            <div
              class="rounded border bg-background overflow-hidden cursor-pointer aspect-[4/3] relative group"
              @click="zoomImage = '/storage/' + hist.verification"
            >
              <img :src="'/storage/' + hist.verification" alt="Selfie" class="w-full h-full object-contain" />
              <div class="absolute bottom-1 left-1 bg-black/65 text-white text-[10px] px-1.5 py-0.5 rounded">
                Selfie
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Popup Custom Foto Contoh -->
  <div
    v-if="showExampleEditor"
    class="fixed inset-0 z-40 bg-black/60 flex items-center justify-center p-4"
    @click.self="showExampleEditor = false"
  >
    <Card class="max-w-md w-full">
      <CardHeader class="flex-row items-center justify-between border-b py-4">
        <div>
          <CardTitle class="text-base">Custom Foto Contoh Verifikasi</CardTitle>
          <CardDescription>
            Foto ini akan tampil di panel admin &amp; popup panduan pemilih.
          </CardDescription>
        </div>
        <Button variant="ghost" size="icon" @click="showExampleEditor = false">
          <X class="size-4" />
        </Button>
      </CardHeader>
      <form @submit.prevent="saveExamples">
        <CardContent class="p-4 space-y-4">
          <div class="space-y-1.5">
            <Label>Contoh Foto KTM (Opsional)</Label>
            <Input
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="(e: Event) => (exampleForm.ktm = (e.target as HTMLInputElement).files?.[0])"
            />
            <label v-if="exampleImages?.ktm" class="flex items-center gap-2 text-xs text-muted-foreground pt-1">
              <input v-model="exampleForm.reset_ktm" type="checkbox" />
              Kembalikan ke ilustrasi default
            </label>
          </div>
          <div class="space-y-1.5">
            <Label>Contoh Foto Selfie Memegang KTM (Opsional)</Label>
            <Input
              type="file"
              accept="image/jpeg,image/png,image/webp"
              @change="(e: Event) => (exampleForm.selfie = (e.target as HTMLInputElement).files?.[0])"
            />
            <label v-if="exampleImages?.selfie" class="flex items-center gap-2 text-xs text-muted-foreground pt-1">
              <input v-model="exampleForm.reset_selfie" type="checkbox" />
              Kembalikan ke ilustrasi default
            </label>
          </div>
        </CardContent>
        <div class="px-4 pb-4 flex justify-end gap-2">
          <Button type="button" variant="outline" @click="showExampleEditor = false">
            Batal
          </Button>
          <Button type="submit" :disabled="exampleForm.processing">
            Simpan Contoh
          </Button>
        </div>
      </form>
    </Card>
  </div>

  <!-- Modal Zoom Gambar -->
  <div
    v-if="zoomImage"
    class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
    @click="zoomImage = null"
  >
    <img
      :src="zoomImage"
      alt="Preview"
      class="max-w-full max-h-[90vh] rounded-lg shadow-2xl"
    />
  </div>
</template>
