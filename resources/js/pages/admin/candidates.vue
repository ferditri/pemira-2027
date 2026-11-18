<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>

<script lang="ts" setup>
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
import { formatMajor } from "@/lib/majors";
import { Link, router } from "@inertiajs/vue3";
import { ArrowRight, Edit, Layers, Plus, Settings, UserPlus } from "lucide-vue-next";
import { onMounted, onUnmounted, ref } from "vue";

interface CandidateOrg extends Organization {
  groups: (Group & { candidates: Candidate[] })[];
}

const props = defineProps<{
  organizations: CandidateOrg[];
}>();

const selectedOrgId = ref<number | "all">("all");

const majorLabel = (code: string | null) => formatMajor(code);

const visibleOrganizations = () => {
  if (selectedOrgId.value === "all") return props.organizations;
  return props.organizations.filter((o) => o.id === selectedOrgId.value);
};

const refreshData = () => {
  router.reload({
    only: ["organizations"],
  });
};

const onVisibilityChange = () => {
  if (document.visibilityState === "visible") {
    refreshData();
  }
};

onMounted(() => {
  window.addEventListener("focus", refreshData);
  document.addEventListener("visibilitychange", onVisibilityChange);
});

onUnmounted(() => {
  window.removeEventListener("focus", refreshData);
  document.removeEventListener("visibilitychange", onVisibilityChange);
});
</script>

<template>
  <Teleport defer to="#header">
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <BreadcrumbPage>Kelola Calon &amp; Tipe</BreadcrumbPage>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>
  </Teleport>

  <div class="flex-1 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight">Pengaturan Calon &amp; Tipe Pemilihan</h2>
        <p class="text-sm text-muted-foreground">
          Atur tipe pemilihan (misal BEM, BLM SIFO, BLM INFOR) beserta nama, foto, visi, dan misi calon dari satu tempat.
        </p>
      </div>
      <Link :href="route('admin.organizations.create')">
        <Button variant="outline">
          <Plus class="size-4 mr-1.5" />
          Tambah Organisasi
        </Button>
      </Link>
    </div>

    <!-- Filter Organisasi -->
    <div v-if="organizations.length" class="flex flex-wrap gap-2">
      <Button
        size="sm"
        :variant="selectedOrgId === 'all' ? 'default' : 'outline'"
        @click="selectedOrgId = 'all'"
      >
        Semua Organisasi
      </Button>
      <Button
        v-for="org in organizations"
        :key="org.id"
        size="sm"
        :variant="selectedOrgId === org.id ? 'default' : 'outline'"
        @click="selectedOrgId = org.id"
      >
        {{ org.name }}
      </Button>
    </div>

    <div v-if="!organizations.length" class="rounded-lg border p-10 text-center text-muted-foreground">
      Belum ada organisasi. Silakan buat organisasi terlebih dahulu.
    </div>

    <!-- Mode Ringkas / Minimize saat tab "Semua Organisasi" aktif -->
    <div
      v-if="selectedOrgId === 'all' && organizations.length"
      class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4"
    >
      <Card
        v-for="org in organizations"
        :key="org.id"
        class="cursor-pointer transition hover:border-primary flex flex-col justify-between"
        @click="selectedOrgId = org.id"
      >
        <CardHeader class="pb-3">
          <div class="flex items-center gap-3">
            <Avatar class="size-11 border">
              <AvatarImage v-if="org.logo" :src="org.logo" :alt="org.name" />
              <AvatarFallback>{{ org.name[0] }}</AvatarFallback>
            </Avatar>
            <div class="min-w-0">
              <CardTitle class="text-base truncate">{{ org.name }}</CardTitle>
              <CardDescription>{{ majorLabel(org.major) }}</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent class="pt-0 flex items-center justify-between text-xs text-muted-foreground">
          <span>
            {{ org.groups.length }} Tipe ·
            {{ org.groups.reduce((acc, g) => acc + g.candidates.length, 0) }} Calon
          </span>
          <Button size="sm" variant="outline" class="h-8 gap-1">
            Lihat Detail
            <ArrowRight class="size-3.5" />
          </Button>
        </CardContent>
      </Card>
    </div>

    <!-- Detail Lengkap Organisasi & Grup/Tipe & Calon saat tab organisasi tertentu dipilih -->
    <template v-else>
      <div v-for="org in visibleOrganizations()" :key="org.id" class="space-y-4">
      <Card>
        <CardHeader class="border-b bg-muted/20">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <Avatar class="size-10 border">
                <AvatarImage v-if="org.logo" :src="org.logo" :alt="org.name" />
                <AvatarFallback>{{ org.name[0] }}</AvatarFallback>
              </Avatar>
              <div>
                <CardTitle class="text-lg">{{ org.name }}</CardTitle>
                <CardDescription>
                  {{ org.groups.length }} Tipe Pemilihan · {{ majorLabel(org.major) }}
                </CardDescription>
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <Link :href="route('admin.organizations.show', { organization: org.id })">
                <Button variant="outline" size="sm">
                  <Settings class="size-4 mr-1.5" />
                  Edit Organisasi
                </Button>
              </Link>
              <Link :href="route('admin.organizations.groups.create', { organization: org.id })">
                <Button size="sm">
                  <Plus class="size-4 mr-1.5" />
                  Tambah Tipe (BEM / BLM / Prodi)
                </Button>
              </Link>
            </div>
          </div>
        </CardHeader>

        <CardContent class="p-6 space-y-6">
          <div v-if="!org.groups.length" class="text-center py-6 text-sm text-muted-foreground">
            Belum ada tipe pemilihan di {{ org.name }}. Klik "Tambah Tipe" untuk menambahkan (contoh: BEM, BLM SIFO, BLM INFOR).
          </div>

          <div
            v-for="group in org.groups"
            :key="group.id"
            class="rounded-lg border p-4 space-y-4"
          >
            <div class="flex flex-wrap items-center justify-between gap-3 border-b pb-3">
              <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <Layers class="size-4 text-primary" />
                  <h3 class="font-bold text-base">{{ group.name }}</h3>
                  <span class="text-xs px-2 py-0.5 rounded-full bg-muted border">
                    {{ majorLabel(group.major) }}
                  </span>
                  <span class="text-xs px-2 py-0.5 rounded-full bg-muted border">
                    {{ group.year ? `Angkatan 20${group.year}` : "Semua Angkatan" }}
                  </span>
                  <span class="text-xs text-muted-foreground">
                    Min. pilih: {{ group.min_candidates }} · Urutan: {{ group.ordering }}
                  </span>
                </div>
              </div>

              <div class="flex flex-wrap gap-2">
                <Link
                  :href="
                    route('admin.organizations.groups.show', {
                      organization: org.id,
                      group: group.id,
                    })
                  "
                >
                  <Button variant="outline" size="sm">
                    <Edit class="size-3.5 mr-1.5" />
                    Edit Tipe
                  </Button>
                </Link>
                <Link
                  :href="
                    route('admin.organizations.groups.candidates.create', {
                      organization: org.id,
                      group: group.id,
                    })
                  "
                >
                  <Button size="sm">
                    <UserPlus class="size-3.5 mr-1.5" />
                    Tambah Calon
                  </Button>
                </Link>
              </div>
            </div>

            <div v-if="!group.candidates.length" class="text-sm text-muted-foreground py-3">
              Belum ada calon di tipe {{ group.name }}.
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
              <Card
                v-for="candidate in group.candidates"
                :key="candidate.id"
                class="overflow-hidden flex flex-col justify-between"
              >
                <CardHeader class="p-4 flex-row items-center gap-3 space-y-0">
                  <Avatar class="size-14 border shrink-0">
                    <AvatarImage v-if="candidate.picture" :src="candidate.picture" />
                    <AvatarFallback>{{ candidate.name_1[0] }}</AvatarFallback>
                  </Avatar>
                  <div class="min-w-0">
                    <CardTitle class="text-base truncate">{{ candidate.name_1 }}</CardTitle>
                    <CardDescription v-if="candidate.name_2" class="truncate">
                      &amp; {{ candidate.name_2 }}
                    </CardDescription>
                  </div>
                </CardHeader>

                <CardContent class="px-4 pb-3 text-xs text-muted-foreground space-y-1.5">
                  <p class="line-clamp-2">
                    <span class="font-semibold text-foreground">Visi:</span>
                    {{ candidate.vision }}
                  </p>
                  <p class="line-clamp-2">
                    <span class="font-semibold text-foreground">Misi:</span>
                    {{ candidate.mission }}
                  </p>
                </CardContent>

                <div class="px-4 pb-4 pt-2 border-t flex justify-end">
                  <Link
                    :href="
                      route('admin.organizations.groups.candidates.edit', {
                        organization: org.id,
                        group: group.id,
                        candidate: candidate.id,
                      })
                    "
                  >
                    <Button variant="outline" size="sm">
                      <Edit class="size-3.5 mr-1.5" />
                      Edit Calon
                    </Button>
                  </Link>
                </div>
              </Card>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    </template>
  </div>
</template>
