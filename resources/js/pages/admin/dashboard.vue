<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
import { Check, LayoutDashboard, Users2 } from "lucide-vue-next";

export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbList,
    BreadcrumbPage,
} from "@/components/ui/breadcrumb";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Link } from "@inertiajs/vue3";

defineProps<{
    whitelists: number;
    voters: number;
    committees: number;
    admins: number;
    organizations: Organization[];
}>();
</script>

<template>
    <Teleport defer to="#header">
        <Breadcrumb>
            <BreadcrumbList>
                <BreadcrumbItem>
                    <BreadcrumbPage>Dashboard</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>
    </Teleport>
    <div class="flex-1">
        <div class="grid gap-4">
            <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Whitelists
                        </CardTitle>
                        <Check class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-extrabold">
                            {{ whitelists }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Registered Voters
                        </CardTitle>
                        <Users2 class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-extrabold">{{ voters }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Committees
                        </CardTitle>
                        <Users2 class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-extrabold">
                            {{ committees }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Admins
                        </CardTitle>
                        <Users2 class="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-extrabold">{{ admins }}</div>
                    </CardContent>
                </Card>
            </div>
            <Card>
                <CardHeader class="flex-row justify-between gap-4">
                    <div class="space-y-1.5">
                        <CardTitle>Ringkasan Pemilihan</CardTitle>
                        <CardDescription>
                            Klik untuk menuju Kontrol Pemilihan (Buka/Tutup Vote &amp; Rekap)
                        </CardDescription>
                    </div>
                    <LayoutDashboard class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div
                        class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <Link
                            v-for="organization in organizations"
                            :key="organization.id"
                            :href="route('admin.elections.index')"
                            class="block"
                        >
                            <Card class="cursor-pointer hover:outline">
                                <CardHeader
                                    class="flex-row items-center justify-between gap-4"
                                >
                                    <div>
                                        <CardTitle>
                                            {{ organization.name }}
                                        </CardTitle>
                                        <CardDescription>
                                            {{ organization.major ?? "Semua Prodi" }}
                                        </CardDescription>
                                    </div>
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full border shrink-0"
                                        :class="
                                            organization.is_open
                                                ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                                                : 'bg-slate-100 text-slate-700 border-slate-300'
                                        "
                                    >
                                        {{ organization.is_open ? "Dibuka" : "Ditutup" }}
                                    </span>
                                </CardHeader>
                            </Card>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
