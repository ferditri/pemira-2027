<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
import { PageProps, User } from "@/types";
import { Link, useForm, usePage } from "@inertiajs/vue3";

export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
import FormMessage from "@/components/FormMessage.vue";
import Select from "@/components/Select.vue";
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
import { SelectItem } from "@/components/ui/select";

const props = defineProps<{
    user: User;
}>();

const page = usePage<PageProps>();
const isSelf = page.props.auth.user.id === props.user.id;

const form = useForm({
    type: props.user.type,
    npm: props.user.npm,
    email: props.user.email,
    name: props.user.name,
});
</script>

<template>
    <Teleport defer to="#header">
        <Breadcrumb>
            <BreadcrumbList>
                <BreadcrumbItem class="hidden md:block">
                    <BreadcrumbLink
                        :href="
                            route('admin.users.index', {
                                _query: { type: user.type },
                            })
                        "
                    >
                        Pengguna
                    </BreadcrumbLink>
                </BreadcrumbItem>
                <BreadcrumbSeparator class="hidden md:block" />
                <BreadcrumbItem>
                    <BreadcrumbPage>Ubah</BreadcrumbPage>
                </BreadcrumbItem>
            </BreadcrumbList>
        </Breadcrumb>
    </Teleport>
    <div class="flex-1">
        <Card class="max-w-sm">
            <CardHeader>
                <CardTitle>Ubah Pengguna</CardTitle>
                <CardDescription>Silakan lengkapi formulir di bawah ini</CardDescription>
            </CardHeader>
            <CardContent>
                <form
                    class="space-y-6"
                    @submit.prevent="
                        form.put(route('admin.users.update', { user: user.id }))
                    "
                >
                    <div class="space-y-2">
                        <Label>Peran</Label>
                        <Select v-model="form.type" :disabled="isSelf">
                            <SelectItem value="admin">Admin</SelectItem>
                            <SelectItem value="committee">Panitia</SelectItem>
                            <SelectItem value="voter">Pemilih</SelectItem>
                        </Select>
                        <p v-if="isSelf" class="text-xs text-muted-foreground">
                            Peran akun Anda sendiri tidak dapat diturunkan selama sedang login.
                        </p>
                        <FormMessage v-if="form.errors.type" variant="error">
                            {{ form.errors.type }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>NPM</Label>
                        <Input v-model="form.npm" autofocus />
                        <FormMessage v-if="form.errors.npm" variant="error">
                            {{ form.errors.npm }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Email</Label>
                        <Input v-model="form.email" type="email" />
                        <FormMessage v-if="form.errors.email" variant="error">
                            {{ form.errors.email }}
                        </FormMessage>
                    </div>
                    <div class="space-y-2">
                        <Label>Nama</Label>
                        <Input v-model="form.name" />
                        <FormMessage v-if="form.errors.name" variant="error">
                            {{ form.errors.name }}
                        </FormMessage>
                    </div>
                    <div class="flex flex-row-reverse justify-between">
                        <Button type="submit" :disabled="form.processing">Simpan</Button>
                        <Link
                            :href="
                                route('admin.users.index', {
                                    _query: { type: user.type },
                                })
                            "
                        >
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
