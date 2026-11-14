<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
import { PageProps } from "@/types";
import { Link, useForm, usePage } from "@inertiajs/vue3";

export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import FormMessage from "@/components/FormMessage.vue";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
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
import { MAJOR_OPTIONS } from "@/lib/majors";

const props = defineProps<{
    new_ordering: number;
}>();
const page = usePage<PageProps>();
const organization = page.props.organization;

const form = useForm({
    name: "",
    year: "",
    major: "",
    min_candidates: 1,
    ordering: props.new_ordering,
});
</script>

<template>
    <Teleport defer to="#header">
        <Breadcrumb>
            <BreadcrumbList>
                <BreadcrumbItem>
                    <BreadcrumbLink :href="route('admin.candidates.index')">
                        Calon &amp; Tipe
                    </BreadcrumbLink>
                </BreadcrumbItem>
                <BreadcrumbSeparator class="hidden md:block" />
                <BreadcrumbItem>
                    <BreadcrumbPage>{{ organization?.name }} · Tambah Tipe</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>
    </Teleport>
    <div class="flex-1">
        <Card class="max-w-sm">
            <CardHeader>
                <CardTitle>Tambah Tipe Pemilihan</CardTitle>
                <CardDescription>Silakan lengkapi formulir di bawah ini</CardDescription>
            </CardHeader>
            <CardContent>
                <form
                    class="space-y-4"
                    @submit.prevent="
                        form.post(
                            route('admin.organizations.groups.store', {
                                organization: organization?.id,
                            })
                        )
                    "
                >
                    <div class="space-y-2">
                        <Label>Nama Tipe / Grup</Label>
                        <Input v-model="form.name" autofocus />
                        <FormMessage v-if="form.errors.name" variant="error">
                            {{ form.errors.name }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Angkatan (Opsional)</Label>
                        <Input v-model="form.year" placeholder="Contoh: 24 (kosong = semua angkatan)" />
                        <FormMessage v-if="form.errors.year" variant="error">
                            {{ form.errors.year }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Program Studi (Opsional)</Label>
                        <select
                            v-model="form.major"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option
                                v-for="opt in MAJOR_OPTIONS"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </option>
                        </select>
                        <FormMessage>
                            Pilih "Semua Prodi" untuk umum (seperti BEM), atau pilih prodi tertentu (seperti BLM Informatika).
                        </FormMessage>
                        <FormMessage v-if="form.errors.major" variant="error">
                            {{ form.errors.major }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Minimal Pilihan Calon</Label>
                        <Input v-model="form.min_candidates" type="number" />
                        <FormMessage>
                            Jumlah minimal calon yang harus dipilih saat pemungutan suara.
                        </FormMessage>
                        <FormMessage
                            v-if="form.errors.min_candidates"
                            variant="error"
                        >
                            {{ form.errors.min_candidates }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Urutan Tampil</Label>
                        <Input v-model="form.ordering" />
                        <FormMessage
                            v-if="form.errors.ordering"
                            variant="error"
                        >
                            {{ form.errors.ordering }}
                        </FormMessage>
                    </div>
                    <div class="flex flex-row-reverse justify-between">
                        <Button type="submit">Tambah</Button>
                        <Link :href="route('admin.candidates.index')">
                            <Button type="button" variant="outline">
                                Kembali
                            </Button>
                        </Link>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
