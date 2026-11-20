<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";

export default {
  layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
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
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { formatMajor } from "@/lib/majors";
import { Link, useForm } from "@inertiajs/vue3";
import {
  BarChart3,
  CheckCircle2,
  Lock,
  Play,
  Plus,
  Settings,
  Square,
  Vote,
} from "lucide-vue-next";

interface ElectionOrg extends Organization {
  groups_count: number;
  candidates_count: number;
  ballots_count: number;
  verified_ballots_count: number;
}

defineProps<{
  organizations: ElectionOrg[];
}>();

const openVote = (orgId: number) => {
  useForm({}).post(route("organizations.open", { organization: orgId }), {
    preserveScroll: true,
  });
};

const closeVote = (orgId: number) => {
  useForm({}).post(route("organizations.close", { organization: orgId }), {
    preserveScroll: true,
  });
};

const majorLabel = (code: string | null) => formatMajor(code);
</script>

<template>
  <Teleport defer to="#header">
    <Breadcrumb>
      <BreadcrumbList>
        <BreadcrumbItem>
          <BreadcrumbPage>Kontrol Pemilihan</BreadcrumbPage>
        </BreadcrumbItem>
      </BreadcrumbList>
    </Breadcrumb>
  </Teleport>

  <div class="flex-1 min-w-0 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold tracking-tight">Kontrol Pemilihan per Organisasi</h2>
        <p class="text-sm text-muted-foreground">
          Buka atau hentikan sesi voting masing-masing organisasi, atau buka halaman rekap &amp; pengaturannya.
        </p>
      </div>
      <Link :href="route('admin.organizations.create')">
        <Button>
          <Plus class="size-4 mr-1.5" />
          Tambah Organisasi
        </Button>
      </Link>
    </div>

    <div v-if="!organizations.length" class="rounded-lg border p-10 text-center text-muted-foreground">
      Belum ada organisasi. Klik "Tambah Organisasi" untuk membuat.
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <Card v-for="org in organizations" :key="org.id" class="flex flex-col justify-between">
        <CardHeader>
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <Avatar class="size-12 border">
                <AvatarImage v-if="org.logo" :src="org.logo" :alt="org.name" />
                <AvatarFallback>{{ org.name[0] }}</AvatarFallback>
              </Avatar>
              <div>
                <CardTitle class="text-lg">{{ org.name }}</CardTitle>
                <CardDescription>{{ majorLabel(org.major) }}</CardDescription>
              </div>
            </div>

            <span
              class="text-xs font-semibold px-2.5 py-1 rounded-full border shrink-0"
              :class="
                org.is_open
                  ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                  : 'bg-slate-100 text-slate-700 border-slate-300'
              "
            >
              {{ org.is_open ? "Sedang Dibuka" : "Ditutup" }}
            </span>
          </div>
        </CardHeader>

        <CardContent>
          <div class="grid grid-cols-3 gap-2 rounded-lg border bg-muted/30 p-3 text-center">
            <div>
              <p class="text-xs text-muted-foreground">Tipe / Grup</p>
              <p class="text-lg font-bold">{{ org.groups_count }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Total Suara</p>
              <p class="text-lg font-bold">{{ org.ballots_count }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Suara Sah</p>
              <p class="text-lg font-bold text-emerald-700">{{ org.verified_ballots_count }}</p>
            </div>
          </div>
        </CardContent>

        <CardFooter class="flex flex-col items-stretch sm:flex-row sm:flex-wrap sm:items-center justify-between gap-2 border-t pt-4">
          <div class="flex flex-wrap gap-2">
            <ConfirmationDialog
              v-if="!org.is_open"
              title="Buka Pemilihan?"
              :description="`Pemilih akan langsung bisa melakukan voting untuk ${org.name}.`"
              @accept="openVote(org.id)"
            >
              <Button class="bg-emerald-600 hover:bg-emerald-700 text-white">
                <Play class="size-4 mr-1.5" />
                Mulai / Buka Vote
              </Button>
            </ConfirmationDialog>

            <ConfirmationDialog
              v-else
              title="Hentikan Pemilihan?"
              :description="`Pemilih tidak akan bisa mengirim suara lagi untuk ${org.name} setelah ditutup.`"
              @accept="closeVote(org.id)"
            >
              <Button variant="destructive">
                <Square class="size-4 mr-1.5" />
                Hentikan Vote
              </Button>
            </ConfirmationDialog>

            <Link :href="route('organizations.recap.result', { organization: org.id })">
              <Button variant="outline">
                <BarChart3 class="size-4 mr-1.5" />
                Hasil
              </Button>
            </Link>
          </div>

          <div class="flex gap-2">
            <Link :href="route('organizations.dashboard', { organization: org.id })">
              <Button variant="ghost" size="sm">
                <Vote class="size-4 mr-1" />
                Detail Suara
              </Button>
            </Link>
            <Link :href="route('admin.organizations.show', { organization: org.id })">
              <Button variant="ghost" size="sm">
                <Settings class="size-4 mr-1" />
                Setting
              </Button>
            </Link>
          </div>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>
