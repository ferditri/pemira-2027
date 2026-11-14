<script lang="ts" setup>
import { MAJOR_OPTIONS } from "@/lib/majors";
import { PageProps } from "@/types";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { Settings } from "lucide-vue-next";
import ConfirmationDialog from "./ConfirmationDialog.vue";
import FormMessage from "./FormMessage.vue";
import { Button } from "./ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "./ui/card";
import { Input } from "./ui/input";
import { Label } from "./ui/label";

const props = defineProps<{
    group: Group;
}>();
const page = usePage<PageProps>();
const organization = page.props.organization;
const form = useForm({
    name: props.group.name,
    year: props.group.year ?? "",
    major: props.group.major ?? "",
    min_candidates: props.group.min_candidates,
    ordering: props.group.ordering,
});

const remove = () => {
    form.delete(
        route("admin.organizations.groups.destroy", {
            organization: organization?.id,
            group: props.group.id,
        })
    );
};
</script>

<template>
    <Card>
        <CardHeader class="flex-row justify-between gap-4">
            <div class="space-y-1.5">
                <CardTitle>Group</CardTitle>
                <CardDescription>Settings</CardDescription>
            </div>
            <Settings class="size-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
            <form
                class="space-y-4"
                @submit.prevent="
                    form.put(
                        route('admin.organizations.groups.update', {
                            organization: organization?.id,
                            group: group.id,
                        })
                    )
                "
            >
                <div class="space-y-2">
                    <Label>Name</Label>
                    <Input v-model="form.name" autofocus />
                    <FormMessage v-if="form.errors.name" variant="error">
                        {{ form.errors.name }}
                    </FormMessage>
                </div>
                <div class="space-y-2">
                    <Label>Year (Angkatan - Opsional)</Label>
                    <Input v-model="form.year" placeholder="Contoh: 24 (kosong = semua angkatan)" />
                    <FormMessage v-if="form.errors.year" variant="error">
                        {{ form.errors.year }}
                    </FormMessage>
                </div>
                <div class="space-y-2">
                    <Label>Prodi / Major (Opsional)</Label>
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
                        <option
                            v-if="
                                form.major &&
                                !MAJOR_OPTIONS.some((o) => o.value === form.major)
                            "
                            :value="form.major"
                        >
                            Prodi {{ form.major }}
                        </option>
                    </select>
                    <FormMessage>
                        Membatasi pemilih berdasarkan 2 digit kode prodi NPM. Pilih "Semua Prodi" untuk umum (seperti BEM).
                    </FormMessage>
                    <FormMessage v-if="form.errors.major" variant="error">
                        {{ form.errors.major }}
                    </FormMessage>
                </div>
                <div class="space-y-2">
                    <Label>Min. Candidates</Label>
                    <Input v-model="form.min_candidates" type="number" />
                    <FormMessage>
                        The minimum amount of candidates need to be selected
                        when voting.
                    </FormMessage>
                    <FormMessage
                        v-if="form.errors.min_candidates"
                        variant="error"
                    >
                        {{ form.errors.min_candidates }}
                    </FormMessage>
                </div>
                <div class="space-y-2">
                    <Label>Ordering</Label>
                    <Input v-model="form.ordering" />
                    <FormMessage v-if="form.errors.ordering" variant="error">
                        {{ form.errors.ordering }}
                    </FormMessage>
                </div>
                <div class="flex flex-row-reverse justify-between">
                    <Button type="submit">Save</Button>
                    <Link :href="route('admin.candidates.index')">
                        <Button variant="outline" type="button">Back</Button>
                    </Link>
                </div>
            </form>
        </CardContent>
    </Card>
    <ConfirmationDialog
        title="Hapus Tipe Pemilihan?"
        :description="`Tipe ${group.name} akan dihapus permanen. Pastikan semua calon di dalamnya sudah kosong.`"
        destructive
        confirm-text="Ya, Hapus"
        @accept="remove"
    >
        <Button variant="destructive" class="w-full mt-4">
            Remove
        </Button>
    </ConfirmationDialog>
</template>
