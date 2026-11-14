<script lang="ts">
import AdminLayout from "@/layouts/AdminLayout.vue";
import { useForm } from "@inertiajs/vue3";

export default {
    layout: AdminLayout,
};
</script>

<script lang="ts" setup>
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
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";

const props = defineProps<{
    whitelist?: Whitelist;
}>();

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
    <div class="flex-1">
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
    </div>
</template>
