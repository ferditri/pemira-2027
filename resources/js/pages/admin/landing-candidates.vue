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
import {
  ArrowDown,
  ArrowUp,
  Edit,
  GripVertical,
  Plus,
  Trash2,
  Upload,
  X,
} from "lucide-vue-next";
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
const modalOpen = ref(false);
const editingId = ref<number | null>(null);
const selectedSourceId = ref<string>("");
const pictureInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const draggingId = ref<number | null>(null);
const dragOverId = ref<number | null>(null);

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

const defaultRoleLabel = (section: "bem" | "blm", count: number) => {
  if (section === "bem") {
    return count === 0 ? "CALON KETUA BEM" : "CALON WAKIL KETUA BEM";
  }
  return `NO. URUT ${count + 1}`;
};

const openCreateModal = () => {
  editingId.value = null;
  selectedSourceId.value = "";
  previewUrl.value = null;
  form.reset();
  form._method = "post";
  form.section = activeSection.value;
  form.sort_order = sectionItems.value.length + 1;
  form.role_label = defaultRoleLabel(activeSection.value, sectionItems.value.length);
  form.clearErrors();
  modalOpen.value = true;
};

const openEditModal = (item: LandingCandidateItem) => {
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
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
  editingId.value = null;
};

const applySourceCandidate = () => {
  const id = Number(selectedSourceId.value);
  const found = props.sourceCandidates.find((c) => c.id === id);
  if (!found) {
    form.candidate_id = null;
    return;
  }

  form.candidate_id = found.id;
  form.name = found.name_2 ? `${found.name_1} & ${found.name_2}` : found.name_1;
  form.vision = found.vision;
  form.mission = found.mission;
  form.photo_url = found.picture ?? "";
  form.picture = undefined;
  previewUrl.value = found.picture ?? null;

  if (!form.role_label.trim()) {
    form.role_label = defaultRoleLabel(form.section, sectionItems.value.length);
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

const submit = () => {
  if (editingId.value) {
    form._method = "put";
    form.post(route("admin.landing-candidates.update", editingId.value), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form._method = "post";
    form.post(route("admin.landing-candidates.store"), {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const destroyItem = (id: number) => {
  router.delete(route("admin.landing-candidates.destroy", id), {
    preserveScroll: true,
  });
};

const saveOrder = (orderedIds: number[]) => {
  router.post(
    route("admin.landing-candidates.reorder"),
    { ids: orderedIds },
    { preserveScroll: true }
  );
};

const moveItem = (index: number, delta: -1 | 1) => {
  const list = [...sectionItems.value];
  const targetIndex = index + delta;
  if (targetIndex < 0 || targetIndex >= list.length) return;
  const [moved] = list.splice(index, 1);
  list.splice(targetIndex, 0, moved);
  saveOrder(list.map((i) => i.id));
};

const onDragStart = (id: number) => {
  draggingId.value = id;
};

const onDragOver = (id: number) => {
  if (draggingId.value !== null && draggingId.value !== id) {
    dragOverId.value = id;
  }
};

const onDrop = (targetId: number) => {
  const sourceId = draggingId.value;
  draggingId.value = null;
  dragOverId.value = null;
  if (sourceId === null || sourceId === targetId) return;

  const list = [...sectionItems.value];
  const fromIdx = list.findIndex((i) => i.id === sourceId);
  const toIdx = list.findIndex((i) => i.id === targetId);
  if (fromIdx === -1 || toIdx === -1) return;

  const [moved] = list.splice(fromIdx, 1);
  list.splice(toIdx, 0, moved);
  saveOrder(list.map((i) => i.id));
};

const onDragEnd = () => {
  draggingId.value = null;
  dragOverId.value = null;
};
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
          Atur daftar Calon Ketua/Wakil BEM dan Calon BLM dari menu "Calon &amp; Tipe". Geser kartu untuk mengubah urutan tampil.
        </p>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button
          :variant="activeSection === 'bem' ? 'default' : 'outline'"
          @click="activeSection = 'bem'"
        >
          Ketua &amp; Wakil BEM
        </Button>
        <Button
          :variant="activeSection === 'blm' ? 'default' : 'outline'"
          @click="activeSection = 'blm'"
        >
          Calon BLM
        </Button>
        <Button class="gap-1.5" @click="openCreateModal">
          <Plus class="size-4" />
          Tambah dari Calon &amp; Tipe
        </Button>
      </div>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>
          {{
            activeSection === "bem"
              ? "Daftar Calon Ketua & Wakil BEM"
              : "Daftar Calon BLM"
          }}
        </CardTitle>
        <CardDescription>
          Seret ikon pegangan (::) ke atas/bawah atau gunakan tombol panah untuk mengatur urutan di landing page.
        </CardDescription>
      </CardHeader>
      <CardContent class="space-y-3">
        <div
          v-if="!sectionItems.length"
          class="rounded-lg border border-dashed p-10 text-center text-sm text-muted-foreground"
        >
          Belum ada kandidat untuk bagian ini. Klik <strong>Tambah dari Calon &amp; Tipe</strong> di kanan atas.
        </div>

        <div
          v-for="(item, index) in sectionItems"
          :key="item.id"
          draggable="true"
          class="flex flex-col gap-4 rounded-xl border bg-card p-4 transition sm:flex-row sm:items-center sm:justify-between"
          :class="{
            'opacity-50': draggingId === item.id,
            'ring-2 ring-primary': dragOverId === item.id,
          }"
          @dragstart="onDragStart(item.id)"
          @dragover.prevent="onDragOver(item.id)"
          @drop.prevent="onDrop(item.id)"
          @dragend="onDragEnd"
        >
          <div class="flex items-center gap-3.5">
            <div
              class="cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing"
              title="Seret untuk mengubah urutan"
            >
              <GripVertical class="size-5" />
            </div>
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
                  Urutan #{{ index + 1 }}
                </span>
              </div>
              <p class="font-semibold">{{ item.name }}</p>
              <p class="line-clamp-2 text-xs text-muted-foreground">
                {{ item.vision }}
              </p>
            </div>
          </div>

          <div class="flex shrink-0 items-center justify-end gap-1.5">
            <Button
              size="icon"
              variant="ghost"
              :disabled="index === 0"
              title="Geser ke atas"
              @click="moveItem(index, -1)"
            >
              <ArrowUp class="size-4" />
            </Button>
            <Button
              size="icon"
              variant="ghost"
              :disabled="index === sectionItems.length - 1"
              title="Geser ke bawah"
              @click="moveItem(index, 1)"
            >
              <ArrowDown class="size-4" />
            </Button>
            <Button
              size="icon"
              variant="outline"
              title="Ubah"
              @click="openEditModal(item)"
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
              <Button size="icon" variant="destructive" title="Hapus">
                <Trash2 class="size-4" />
              </Button>
            </ConfirmationDialog>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Modal Tambah / Edit dari Calon & Tipe -->
  <Teleport to="body">
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="closeModal"
    >
      <Card class="max-h-[90vh] w-full max-w-lg overflow-y-auto shadow-xl">
        <CardHeader class="flex flex-row items-start justify-between gap-4">
          <div>
            <CardTitle>
              {{ editingId ? "Ubah Kandidat Landing" : "Tambah dari Calon & Tipe" }}
            </CardTitle>
            <CardDescription>
              Pilih kandidat dari menu "Calon &amp; Tipe" untuk ditampilkan di landing page.
            </CardDescription>
          </div>
          <Button size="icon" variant="ghost" @click="closeModal">
            <X class="size-4" />
          </Button>
        </CardHeader>
        <CardContent>
          <form class="space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
              <Label>Pilih Calon dari Calon &amp; Tipe</Label>
              <select
                v-model="selectedSourceId"
                required
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                @change="applySourceCandidate"
              >
                <option value="">-- Pilih calon dari Calon &amp; Tipe --</option>
                <option
                  v-for="src in sourceCandidates"
                  :key="src.id"
                  :value="String(src.id)"
                >
                  {{ src.organization?.name }} · {{ src.group?.name }} — {{ src.name_1 }}
                </option>
              </select>
              <FormMessage v-if="form.errors.candidate_id" variant="error">
                {{ form.errors.candidate_id }}
              </FormMessage>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
              <div class="space-y-2">
                <Label>Label / Jabatan / No. Urut</Label>
                <Input
                  v-model="form.role_label"
                  placeholder="Misal: CALON KETUA BEM / NO. URUT 1"
                />
                <FormMessage v-if="form.errors.role_label" variant="error">
                  {{ form.errors.role_label }}
                </FormMessage>
              </div>
            </div>

            <div class="space-y-2">
              <Label>Foto Kandidat</Label>
              <input
                ref="pictureInput"
                type="file"
                class="hidden"
                accept="image/jpeg, image/png, image/webp"
                @change="onFileChange"
              />
              <div class="flex items-center gap-4">
                <Avatar class="size-16 border">
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
                  Ganti Foto
                </Button>
              </div>
              <FormMessage v-if="form.errors.picture" variant="error">
                {{ form.errors.picture }}
              </FormMessage>
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
              <Textarea v-model="form.vision" rows="3" />
              <FormMessage v-if="form.errors.vision" variant="error">
                {{ form.errors.vision }}
              </FormMessage>
            </div>

            <div class="space-y-2">
              <Label>Misi (1 poin per baris)</Label>
              <Textarea v-model="form.mission" rows="4" />
              <FormMessage v-if="form.errors.mission" variant="error">
                {{ form.errors.mission }}
              </FormMessage>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <Button type="button" variant="outline" @click="closeModal">
                Batal
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ editingId ? "Simpan Perubahan" : "Tambahkan" }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </Teleport>
</template>
