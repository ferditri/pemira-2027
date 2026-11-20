<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
import { router, useForm } from "@inertiajs/vue3";

export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import FormMessage from "@/components/FormMessage.vue";
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
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Textarea } from "@/components/ui/textarea";
import { User } from "@/types";
import { Trash2 } from "lucide-vue-next";
import { ref } from "vue";

type WhitelistRow = Whitelist & {
    created_at?: string;
    user?: Pick<User, "id" | "npm" | "name" | "type"> | null;
};

const props = defineProps<{
    whitelist?: Whitelist;
    whitelists: Pagination<WhitelistRow> & { total?: number };
    search?: string;
}>();

const searchQuery = ref(props.search ?? "");

const applySearch = () => {
    router.get(
        route("admin.whitelists.index"),
        searchQuery.value.trim() ? { search: searchQuery.value.trim() } : {},
        { preserveState: true, preserveScroll: true }
    );
};

const destroy = (npm: string) => {
    router.delete(route("admin.whitelists.destroy", npm), {
        preserveScroll: true,
    });
};

const prev = () => {
    if (props.whitelists.prev_page_url) {
        router.visit(props.whitelists.prev_page_url, { preserveScroll: true });
    }
};

const next = () => {
    if (props.whitelists.next_page_url) {
        router.visit(props.whitelists.next_page_url, { preserveScroll: true });
    }
};

const formatTanggal = (dateStr?: string) => {
    if (!dateStr) return "-";
    const date = new Date(dateStr);
    if (Number.isNaN(date.getTime())) return dateStr;
    return `${new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
        timeZone: "Asia/Jakarta",
    }).format(date)} WIB`;
};

const validateForm = useForm({
    npm: props.whitelist ? props.whitelist.npm : "",
});
const storeForm = useForm({
    npm: "",
});
const massForm = useForm({
    whitelists: "",
});
</script>

<template>
    <Teleport defer to="#header">
        <Breadcrumb>
            <BreadcrumbList>
                <BreadcrumbItem>
                    <BreadcrumbPage>Daftar Pemilih</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>
    </Teleport>
    <div class="flex-1 min-w-0">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Cek Daftar Pemilih</CardTitle>
                        <CardDescription>
                            Cari NPM untuk memastikan terdaftar
                        </CardDescription>
                        <form
                            class="space-y-6 mt-2"
                            @submit.prevent="
                                validateForm.post(
                                    route('admin.whitelists.validate'),
                                    {
                                        data: {
                                            npm: validateForm.npm,
                                        },
                                    }
                                )
                            "
                        >
                            <div class="space-y-2">
                                <Input
                                    v-model="validateForm.npm"
                                    placeholder="Masukkan NPM"
                                />
                                <FormMessage
                                    v-if="validateForm.wasSuccessful"
                                    variant="success"
                                >
                                    NPM terdaftar dalam daftar pemilih
                                </FormMessage>
                                <FormMessage
                                    v-if="validateForm.errors.npm"
                                    variant="error"
                                >
                                    NPM tidak terdaftar dalam daftar pemilih
                                </FormMessage>
                            </div>
                            <Button type="submit">Cek NPM</Button>
                        </form>
                    </CardHeader>
                </Card>
            </div>
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Tambah Satu NPM</CardTitle>
                        <CardDescription>
                            Tambahkan satu NPM ke daftar pemilih
                        </CardDescription>
                        <form
                            class="space-y-6 mt-2"
                            @submit.prevent="
                                storeForm.post(
                                    route('admin.whitelists.store.single')
                                )
                            "
                        >
                            <div class="space-y-2">
                                <Input
                                    v-model="storeForm.npm"
                                    placeholder="Masukkan NPM"
                                />
                                <FormMessage
                                    v-if="storeForm.wasSuccessful"
                                    variant="success"
                                >
                                    NPM berhasil ditambahkan ke daftar pemilih
                                </FormMessage>
                                <FormMessage
                                    v-if="storeForm.errors.npm"
                                    variant="error"
                                >
                                    {{ storeForm.errors.npm }}
                                </FormMessage>
                            </div>
                            <Button type="submit">Tambah</Button>
                        </form>
                    </CardHeader>
                </Card>
            </div>
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Pemilih Massal</CardTitle>
                        <CardDescription>
                            Perbarui seluruh NPM dalam daftar pemilih
                        </CardDescription>
                        <form
                            class="space-y-6 mt-2"
                            @submit.prevent="
                                massForm.post(route('admin.whitelists.store'))
                            "
                        >
                            <div class="space-y-2">
                                <Textarea
                                    v-model="massForm.whitelists"
                                    placeholder="Tempel daftar NPM di sini (pisahkan dengan baris baru)"
                                />
                                <FormMessage
                                    v-if="massForm.errors.whitelists"
                                    variant="error"
                                >
                                    {{ massForm.errors.whitelists }}
                                </FormMessage>
                            </div>
                            <Button type="submit">Simpan</Button>
                        </form>
                    </CardHeader>
                </Card>
            </div>
        </div>

        <Card class="mt-6">
            <CardHeader class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <CardTitle class="flex items-center gap-2">
                        Daftar NPM Terdaftar
                        <span
                            v-if="whitelists.total !== undefined"
                            class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground"
                        >
                            {{ whitelists.total }} NPM
                        </span>
                    </CardTitle>
                    <CardDescription>
                        Seluruh NPM yang diizinkan login dan mengikuti pemilihan
                    </CardDescription>
                </div>
                <form class="flex w-full gap-2 sm:w-auto" @submit.prevent="applySearch">
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari NPM..."
                        class="sm:w-56"
                    />
                    <Button type="submit" variant="outline">Cari</Button>
                </form>
            </CardHeader>
            <CardContent class="p-2 sm:p-6 overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>NPM</TableHead>
                            <TableHead>Status Akun</TableHead>
                            <TableHead class="hidden md:table-cell">Ditambahkan</TableHead>
                            <TableHead class="text-right w-[64px]">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="!whitelists.data.length">
                            <TableCell colspan="4" class="text-center text-muted-foreground">
                                Belum ada NPM dalam daftar pemilih
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="item in whitelists.data" :key="item.npm">
                            <TableCell class="font-medium align-top sm:align-middle">
                                <div class="font-mono text-xs sm:text-sm">{{ item.npm }}</div>
                                <div class="md:hidden text-[11px] text-muted-foreground mt-0.5">
                                    {{ formatTanggal(item.created_at) }}
                                </div>
                            </TableCell>
                            <TableCell class="min-w-0">
                                <div v-if="item.user" class="flex flex-col items-start gap-0.5 min-w-0">
                                    <span
                                        class="inline-flex items-center whitespace-nowrap rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-800"
                                    >
                                        Sudah Login
                                    </span>
                                    <span class="text-xs text-muted-foreground truncate max-w-[130px] sm:max-w-[240px]">
                                        {{ item.user.name }}
                                    </span>
                                </div>
                                <span
                                    v-else
                                    class="inline-flex items-center whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700"
                                >
                                    Belum Login
                                </span>
                            </TableCell>
                            <TableCell class="hidden md:table-cell text-muted-foreground whitespace-nowrap">
                                {{ formatTanggal(item.created_at) }}
                            </TableCell>
                            <TableCell class="text-right whitespace-nowrap">
                                <ConfirmationDialog
                                    title="Hapus NPM?"
                                    :description="`NPM ${item.npm} akan dihapus dari daftar pemilih.`"
                                    destructive
                                    confirm-text="Ya, Hapus"
                                    @accept="destroy(item.npm)"
                                >
                                    <Button size="icon" variant="destructive" class="size-8">
                                        <Trash2 class="size-3.5" />
                                    </Button>
                                </ConfirmationDialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
            <CardFooter class="flex justify-end gap-2">
                <Button
                    :disabled="!whitelists.prev_page_url"
                    variant="outline"
                    @click="prev"
                >
                    Sebelumnya
                </Button>
                <Button
                    :disabled="!whitelists.next_page_url"
                    variant="outline"
                    @click="next"
                >
                    Berikutnya
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
