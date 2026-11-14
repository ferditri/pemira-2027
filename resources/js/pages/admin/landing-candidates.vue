<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
export default {
  layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import FormMessage from "@/components/FormMessage.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
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
import { Textarea } from "@/components/ui/textarea";
import { router, useForm } from "@inertiajs/vue3";
import { Edit, Plus, Trash2, Upload } from "lucide-vue-next";
import { computed, ref } from "vue";

type LandingCandidateItem = {
  id: number;
  section: "bem" | "blm";
  candidate_id: number | null;
  role_label: string;
  name: string;
  photo: string | null;
  vision: string;
  mission: string[];
  sort_order: number;
};

type SourceCandidateItem = {
  id: number;
  name_1: string;
  name_2?: string | null;
  vision: string;
  mission: string;
  picture?: string | null;
  organization?: { id: number; name: string } | null;
  group?: { id: number; name: string } | null;
};

const props = defineProps<{
  landingCandidates: LandingCandidateItem[];
  sourceCandidates: SourceCandidateItem[];
}>();

const activeSection = ref<"bem" | "blm">("bem");
const editingId = ref<number | null>(null);
const selectedSourceId = ref<string>("");
const pictureInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const form = useForm({
  _method: "post" as "post" | "put",
  section: "bem" as "bem" | "blm",
  candidate_id: null as number | null,
  role_label: "",
  name: "",
  vision: "",
  mission: "",
  sort_order: 1,
  photo_url: "" as string | null,
  picture: undefined as File | undefined,
});

const sectionItems = computed(() =>
  props.landingCandidates.filter((item) => item.section === activeSection.value)
);

const switchSection = (section: "bem" | "blm") => {
  activeSection.value = section;
  if (!editingId.value) {
    form.section = section;
    form.sort_order = sectionItems.value.length + 1;
    form.role_label =
      section === "bem"
        ? sectionItems.value.length === 0
          ? "CALON KETUA BEM"
          : "CALON WAKIL KETUA BEM"
        : `NO. URUT ${sectionItems.value.length + 1}`;
  }
};

const resetForm = () => {
  editingId.value = null;
  selectedSourceId.value = "";
  previewUrl.value = null;
  form.reset();
  form._method = "post";
  form.section = activeSection.value;
  form.sort_order = sectionItems.value.length + 1;
  form.role_label =
    activeSection.value === "bem"
      ? sectionItems.value.length === 0
        ? "CALON KETUA BEM"
        : "CALON WAKIL KETUA BEM"
      : `NO. URUT ${sectionItems.value.length + 1}`;
  form.clearErrors();
};

const applySourceCandidate = () => {
  const id = Number(selectedSourceId.value);
  const found = props.sourceCandidates.find((c) => c.id === id);
  if (!found) return;

  form.candidate_id = found.id;
  form.name = found.name_2 ? `${found.name_1} & ${found.name_2}` : found.name_1;
  form.vision = found.vision;
  form.mission = found.mission;
  form.photo_url = found.picture ?? "";
  form.picture = undefined;
  previewUrl.value = found.picture ?? null;

  if (!form.role_label.trim()) {
    form.role_label =
      form.section === "bem"
        ? "CALON KETUA BEM"
        : `NO. URUT ${sectionItems.value.length + 1}`;
  }
};

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file) return;

  form.picture = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    previewUrl.value = (e.target?.result as string) || null;
  };
  reader.readAsDataURL(file);
};

const startEdit = (item: LandingCandidateItem) => {
  editingId.value = item.id;
  activeSection.value = item.section;
  selectedSourceId.value = item.candidate_id ? String(item.candidate_id) : "";
  form._method = "put";
  form.section = item.section;
  form.candidate_id = item.candidate_id;
  form.role_label = item.role_label;
  form.name = item.name;
  form.vision = item.vision;
  form.mission = (item.mission ?? []).join("\n");
  form.sort_order = item.sort_order;
  form.photo_url = item.photo ?? "";
  form.picture = undefined;
  previewUrl.value = item.photo;
  form.clearErrors();
};

const submit = () => {
  if (editingId.value) {
    form._method = "put";
    form.post(route("admin.landing-candidates.update", editingId.value), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => resetForm(),
    });
  } else {
    form._method = "post";
    form.post(route("admin.landing-candidates.store"), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => resetForm(),
    });
  }
};

const destroyItem = (id: number) => {
  router.delete(route("admin.landing-candidates.destroy", id), {
    preserveScroll: true,
    onSuccess: () => {
      if (editingId.value === id) resetForm();
    },
  });
};

// Initialize default role_label and sort_order on load
resetForm();
</script>

<template>
  <Teleport defer to="#header">
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <BreadcrumbPage>Kandidat Landing</BreadcrumbPage>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>
  </Teleport>

  <div class="flex-1 space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Kandidat Landing Page</h1>
        <p class="text-sm text-muted-foreground">
          Atur daftar Calon Ketua/Wakil BEM dan Calon BLM yang tampil di halaman utama.
        </p>
      </div>
      <div class="flex gap-2">
        <Button
          :variant="activeSection === 'bem' ? 'default' : 'outline'"
          @click="switchSection('bem')"
        >
          Ketua &amp; Wakil BEM
        </Button>
        <Button
          :variant="activeSection === 'blm' ? 'default' : 'outline'"
          @click="switchSection('blm')"
        >
          Calon BLM
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
      <!-- Form Tambah / Edit -->
      <Card class="lg:col-span-5 h-fit">
        <CardHeader>
          <CardTitle>
            {{ editingId ? "Ubah Kandidat Landing" : "Tambah Kandidat Landing" }}
          </CardTitle>
          <CardDescription>
            Pilih langsung dari data "Calon &amp; Tipe" atau isi manual di bawah ini.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form class="space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
              <Label>Ambil dari Calon &amp; Tipe (Opsional)</Label>
              <select
                v-model="selectedSourceId"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                @change="applySourceCandidate"
              >
                <option value="">-- Pilih calon yang sudah dibuat --</option>
                <option
                  v-for="src in sourceCandidates"
                  :key="src.id"
                  :value="String(src.id)"
                >
                  {{ src.organization?.name }} · {{ src.group?.name }} — {{ src.name_1 }}
                </option>
              </select>
            </div>

            <div class="space-y-2">
              <Label>Bagian Landing Page</Label>
              <select
                v-model="form.section"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
              >
                <option value="bem">Ketua &amp; Wakil BEM</option>
                <option value="blm">Calon BLM</option>
              </select>
            </div>

            <div class="space-y-2 text-center">
              <Label class="block text-left">Foto Kandidat</Label>
              <input
                ref="pictureInput"
                type="file"
                class="hidden"
                accept="image/jpeg, image/png, image/webp"
                @change="onFileChange"
              />
              <div class="flex items-center gap-4">
                <Avatar class="size-20 border">
                  <AvatarImage v-if="previewUrl" :src="previewUrl" />
                  <AvatarFallback>
                    <Plus class="size-5 text-muted-foreground" />
                  </AvatarFallback>
                </Avatar>
                <Button
                  type="button"
                  variant="outline"
                  class="gap-2"
                  @click="pictureInput?.click()"
                >
                  <Upload class="size-4" />
                  Pilih Foto
                </Button>
              </div>
              <FormMessage v-if="form.errors.picture" variant="error">
                {{ form.errors.picture }}
              </FormMessage>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="space-y-2 sm:col-span-2">
                <Label>Label / Jabatan / No. Urut</Label>
                <Input
                  v-model="form.role_label"
                  placeholder="Misal: CALON KETUA BEM / NO. URUT 1"
                />
                <FormMessage v-if="form.errors.role_label" variant="error">
                  {{ form.errors.role_label }}
                </FormMessage>
              </div>
              <div class="space-y-2">
                <Label>Urutan</Label>
                <Input v-model.number="form.sort_order" type="number" min="0" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Nama Kandidat</Label>
              <Input v-model="form.name" placeholder="Nama lengkap kandidat" />
              <FormMessage v-if="form.errors.name" variant="error">
                {{ form.errors.name }}
              </FormMessage>
            </div>

            <div class="space-y-2">
              <Label>Visi</Label>
              <Textarea v-model="form.vision" rows="3" placeholder="Tuliskan visi kandidat" />
              <FormMessage v-if="form.errors.vision" variant="error">
                {{ form.errors.vision }}
              </FormMessage>
            </div>

            <div class="space-y-2">
              <Label>Misi (1 poin per baris)</Label>
              <Textarea
                v-model="form.mission"
                rows="4"
                placeholder="Poin misi 1&#10;Poin misi 2&#10;Poin misi 3"
              />
              <FormMessage v-if="form.errors.mission" variant="error">
                {{ form.errors.mission }}
              </FormMessage>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <Button
                v-if="editingId"
                type="button"
                variant="outline"
                @click="resetForm"
              >
                Batal
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ editingId ? "Simpan Perubahan" : "Tambah ke Landing" }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Daftar Kartu yang Tampil -->
      <Card class="lg:col-span-7">
        <CardHeader>
          <CardTitle>
            {{
              activeSection === "bem"
                ? "Daftar Calon Ketua & Wakil BEM"
                : "Daftar Calon BLM"
            }}
          </CardTitle>
          <CardDescription>
            Tampilan kartu berikut langsung terhubung ke halaman landing page.
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div
            v-if="!sectionItems.length"
            class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
          >
            Belum ada kandidat untuk bagian ini. Silakan tambah dari form di samping.
          </div>

          <div
            v-for="item in sectionItems"
            :key="item.id"
            class="flex flex-col gap-4 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between"
          >
            <div class="flex items-center gap-4">
              <Avatar class="size-16 border shrink-0">
                <AvatarImage v-if="item.photo" :src="item.photo" />
                <AvatarFallback>{{ item.name[0] ?? "?" }}</AvatarFallback>
              </Avatar>
              <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-900">
                    {{ item.role_label }}
                  </span>
                  <span class="text-xs text-muted-foreground">
                    Urutan #{{ item.sort_order }}
                  </span>
                </div>
                <p class="font-semibold">{{ item.name }}</p>
                <p class="line-clamp-2 text-xs text-muted-foreground">
                  {{ item.vision }}
                </p>
              </div>
            </div>

            <div class="flex shrink-0 justify-end gap-2">
              <Button
                size="icon"
                variant="outline"
                @click="startEdit(item)"
              >
                <Edit class="size-4" />
              </Button>
              <ConfirmationDialog
                title="Hapus Kandidat Landing?"
                :description="`${item.name} (${item.role_label}) akan dihapus dari landing page.`"
                destructive
                confirm-text="Ya, Hapus"
                @accept="destroyItem(item.id)"
              >
                <Button size="icon" variant="destructive">
                  <Trash2 class="size-4" />
                </Button>
              </ConfirmationDialog>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
