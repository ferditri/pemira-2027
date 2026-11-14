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
import { router, useForm } from "@inertiajs/vue3";
import {
  ArrowDown,
  ArrowUp,
  Edit,
  GripVertical,
  Plus,
  Trash2,
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
const draggingId = ref<number | null>(null);
const dragOverId = ref<number | null>(null);

const form = useForm({
  section: "bem" as "bem" | "blm",
  candidate_id: "" as string,
  role_label: "",
  sort_order: 1,
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
  form.reset();
  form.section = activeSection.value;
  form.candidate_id = "";
  form.sort_order = sectionItems.value.length + 1;
  form.role_label = defaultRoleLabel(activeSection.value, sectionItems.value.length);
  form.clearErrors();
  modalOpen.value = true;
};

const openEditModal = (item: LandingCandidateItem) => {
  editingId.value = item.id;
  activeSection.value = item.section;
  form.section = item.section;
  form.candidate_id = item.candidate_id ? String(item.candidate_id) : "";
  form.role_label = item.role_label;
  form.sort_order = item.sort_order;
  form.clearErrors();
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
  editingId.value = null;
};

const onSectionChange = () => {
  const count = props.landingCandidates.filter(
    (item) => item.section === form.section && item.id !== editingId.value
  ).length;
  form.role_label = defaultRoleLabel(form.section, count);
  form.sort_order = count + 1;
};

const submit = () => {
  if (editingId.value) {
    form.put(route("admin.landing-candidates.update", editingId.value), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route("admin.landing-candidates.store"), {
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
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Kandidat Landing Page</h1>
      <p class="text-sm text-muted-foreground">
        Atur daftar Calon Ketua/Wakil BEM dan Calon BLM dari menu "Calon &amp; Tipe". Geser kartu untuk mengubah urutan tampil.
      </p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="inline-flex w-fit rounded-lg border bg-muted/40 p-1">
        <Button
          size="sm"
          :variant="activeSection === 'bem' ? 'default' : 'ghost'"
          @click="activeSection = 'bem'"
        >
          Ketua &amp; Wakil BEM
        </Button>
        <Button
          size="sm"
          :variant="activeSection === 'blm' ? 'default' : 'ghost'"
          @click="activeSection = 'blm'"
        >
          Calon BLM
        </Button>
      </div>
      <Button class="w-fit shrink-0 gap-1.5" @click="openCreateModal">
        <Plus class="size-4" />
        Tambah dari Calon &amp; Tipe
      </Button>
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

  <!-- Modal Simpel: Pilih Calon dari Calon & Tipe -->
  <Teleport to="body">
    <div
      v-if="modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="closeModal"
    >
      <Card class="w-full max-w-md shadow-xl">
        <CardHeader class="flex flex-row items-start justify-between gap-4">
          <div>
            <CardTitle>
              {{ editingId ? "Ubah Kandidat Landing" : "Tambah dari Calon & Tipe" }}
            </CardTitle>
            <CardDescription>
              Pilih kandidat dari menu "Calon &amp; Tipe". Foto, nama, visi, dan misi otomatis diambil dari sana.
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
                v-model="form.candidate_id"
                required
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
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
                  @change="onSectionChange"
                >
                  <option value="bem">Ketua &amp; Wakil BEM</option>
                  <option value="blm">Calon BLM</option>
                </select>
              </div>
              <div class="space-y-2">
                <Label>Label / Jabatan / No. Urut</Label>
                <Input
                  v-model="form.role_label"
                  placeholder="CALON KETUA BEM / NO. URUT 1"
                />
                <FormMessage v-if="form.errors.role_label" variant="error">
                  {{ form.errors.role_label }}
                </FormMessage>
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <Button type="button" variant="outline" @click="closeModal">
                Batal
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ editingId ? "Simpan" : "Tambahkan" }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </Teleport>
</template>
