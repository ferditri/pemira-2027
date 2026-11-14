<script lang="ts" setup>
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { MAJOR_OPTIONS } from "@/lib/majors";
import { Link, useForm } from "@inertiajs/vue3";
import { Plus, Settings } from "lucide-vue-next";
import { ref } from "vue";
import ConfirmationDialog from "./ConfirmationDialog.vue";
import FormMessage from "./FormMessage.vue";
import { Avatar, AvatarFallback } from "./ui/avatar";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Label } from "./ui/label";

const props = defineProps<{
  organization: Organization;
}>();

const form = useForm({
  _method: "put",
  logo: <File | undefined>undefined,
  major: props.organization.major ?? "",
  name: props.organization.name,
});

const logoInput = ref<HTMLInputElement | null>(null);
const logoURL = ref<string | undefined>(props.organization.logo);

const setLogo = (event: Event) => {
  const target = event.target as HTMLInputElement;
  form.logo = target.files?.[0];

  if (form.logo) {
    const reader = new FileReader();
    reader.onload = (e) => {
      logoURL.value = e.target?.result as string;
    };
    reader.readAsDataURL(form.logo);
  }
};

const remove = () => {
  form.delete(
    route("admin.organizations.destroy", {
      organization: props.organization.id,
    }),
  );
};
</script>

<template>
  <Card>
    <CardHeader class="flex-row justify-between gap-4">
      <div class="space-y-1.5">
        <CardTitle>Organization</CardTitle>
        <CardDescription>Settings</CardDescription>
      </div>
      <Settings class="size-4 text-muted-foreground" />
    </CardHeader>
    <CardContent>
      <form
        class="space-y-4"
        @submit.prevent="
          form.post(
            route('admin.organizations.update', {
              organization: props.organization.id,
            }),
          )
        "
      >
        <div class="space-y-2 text-center">
          <Label class="block">Logo</Label>
          <input
            ref="logoInput"
            type="file"
            class="absolute -top-24 -left-24"
            accept="image/jpeg, image/png, image/webp"
            @change="setLogo"
          />
          <img
            v-if="logoURL"
            :src="logoURL"
            alt="Logo"
            class="mx-auto size-24 hover:opacity-75 cursor-pointer"
            @click="logoInput?.click()"
            @error="logoURL = undefined"
          />
          <Avatar
            v-else
            class="size-24 border-2 cursor-pointer hover:outline"
            @click="logoInput?.click()"
          >
            <AvatarFallback>
              <Plus class="size-4 text-muted-foreground" />
            </AvatarFallback>
          </Avatar>
          <FormMessage v-if="form.errors.logo" variant="error">
            {{ form.errors.logo }}
          </FormMessage>
        </div>
        <div class="space-y-2">
          <Label>Name</Label>
          <Input v-model="form.name" autofocus />
          <FormMessage v-if="form.errors.name" variant="error">
            {{ form.errors.name }}
          </FormMessage>
        </div>
        <div class="space-y-2">
          <Label>
            Prodi / Major
            <span class="text-muted-foreground"> - Opsional </span>
          </Label>
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
            Pilih "Semua Prodi" jika organisasi berlaku untuk seluruh fakultas (contoh: BLM &amp; BEM).
          </FormMessage>
          <FormMessage v-if="form.errors.major" variant="error">
            {{ form.errors.major }}
          </FormMessage>
        </div>
        <div class="flex flex-row-reverse justify-between">
          <Button type="submit">Save</Button>
          <Link :href="route('admin.candidates.index')">
            <Button type="button" variant="outline">Back</Button>
          </Link>
        </div>
      </form>
    </CardContent>
  </Card>
  <ConfirmationDialog
    title="Hapus Organisasi?"
    :description="`Organisasi ${organization.name} akan dihapus permanen. Pastikan semua tipe/grup di dalamnya sudah kosong.`"
    destructive
    confirm-text="Ya, Hapus"
    @accept="remove"
  >
    <Button
      type="button"
      variant="destructive"
      class="mt-4 w-full"
    >
      Remove
    </Button>
  </ConfirmationDialog>
</template>
