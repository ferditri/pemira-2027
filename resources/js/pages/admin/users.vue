<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import ConfirmationDialog from "@/components/ConfirmationDialog.vue";
import Select from "@/components/Select.vue";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbList,
    BreadcrumbPage,
} from "@/components/ui/breadcrumb";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { SelectItem } from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { PageProps, User } from "@/types";
import { Link, router, usePage } from "@inertiajs/vue3";
import { Edit, Trash2 } from "lucide-vue-next";
import { ref } from "vue";

const props = withDefaults(
    defineProps<{
        users: Pagination<User>;
        type?: string;
        superAdminEmails?: string[];
    }>(),
    {
        type: "admin",
        superAdminEmails: () => [],
    }
);

const page = usePage<PageProps>();
const superAdminSet = new Set(
    props.superAdminEmails.map((email) => email.toLowerCase())
);
const isSuperAdmin = (target: User) =>
    superAdminSet.has((target.email ?? "").toLowerCase());
const isViewerSuperAdmin = superAdminSet.has(
    (page.props.auth.user?.email ?? "").toLowerCase()
);
const canEdit = (target: User) => !isSuperAdmin(target) || isViewerSuperAdmin;

const type = ref(props.type);

const edit = (user: User) => {
    router.visit(
        route("admin.users.edit", { user, _query: { type: type.value } })
    );
};

const destroy = (user: User) => {
    router.delete(route("admin.users.destroy", { user: user.id }));
};

const prev = () => {
    if (props.users.prev_page_url) {
        router.visit(props.users.prev_page_url, {
            data: { type: type.value },
        });
    }
};

const next = () => {
    if (props.users.next_page_url) {
        router.visit(props.users.next_page_url, {
            data: { type: type.value },
        });
    }
};
</script>

<template>
    <Teleport defer to="#header">
        <Breadcrumb>
            <BreadcrumbList>
                <BreadcrumbItem>
                    <BreadcrumbPage>Pengguna</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>
    </Teleport>
    <div class="flex-1 min-w-0">
        <Card>
            <CardHeader class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3 sm:gap-4">
                    <Label>Peran:</Label>
                    <div class="w-36">
                        <Select
                            v-model="type"
                            @change="
                                router.visit(route('admin.users.index'), {
                                    data: { type },
                                })
                            "
                        >
                            <SelectItem value="admin">Admin</SelectItem>
                            <SelectItem value="committee">
                                Panitia
                            </SelectItem>
                            <SelectItem value="voter">Pemilih</SelectItem>
                        </Select>
                    </div>
                </div>
                <Link :href="route('admin.users.create', { _query: { type } })" class="w-full sm:w-auto">
                    <Button class="w-full sm:w-auto">Tambah Pengguna</Button>
                </Link>
            </CardHeader>
            <CardContent class="space-y-4 overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>NPM</TableHead>
                            <TableHead>Nama</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="!users.data.length">
                            <TableCell
                                class="text-center text-muted-foreground"
                                colspan="3"
                            >
                                Belum ada data
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="user in users.data">
                            <TableCell>{{ user.npm }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Avatar class="size-6">
                                        <AvatarImage
                                            v-if="user.avatar"
                                            :src="user.avatar"
                                        />
                                        <AvatarFallback>
                                            {{ user.name[0] }}
                                        </AvatarFallback>
                                    </Avatar>
                                    {{ user.name }}
                                    <span
                                        v-if="isSuperAdmin(user)"
                                        class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800"
                                    >
                                        Super Admin
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="flex justify-end gap-2">
                                <Button
                                    v-if="canEdit(user)"
                                    size="icon"
                                    variant="outline"
                                    @click="edit(user)"
                                >
                                    <Edit class="size-4" />
                                </Button>
                                <ConfirmationDialog
                                    v-if="!isSuperAdmin(user)"
                                    title="Hapus Pengguna?"
                                    :description="`Akun ${user.name} (${user.npm}) akan dihapus dari sistem.`"
                                    destructive
                                    confirm-text="Ya, Hapus"
                                    @accept="destroy(user)"
                                >
                                    <Button
                                        size="icon"
                                        variant="destructive"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </ConfirmationDialog>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div class="flex justify-end gap-2">
                    <Button
                        :disabled="!users.prev_page_url"
                        variant="outline"
                        @click="prev"
                    >
                        Sebelumnya
                    </Button>
                    <Button
                        :disabled="!users.next_page_url"
                        variant="outline"
                        @click="next"
                    >
                        Berikutnya
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
