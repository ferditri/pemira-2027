<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import Flash from "@/components/Flash.vue";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Separator } from "@/components/ui/separator";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarRail,
  SidebarTrigger,
} from "@/components/ui/sidebar";
import { prefersReducedMotion } from "@/composables/useMotion";
import { PageProps } from "@/types";
import { Link, router, usePage } from "@inertiajs/vue3";
import { animate, stagger } from "motion";
import {
  Check,
  ChevronsUpDown,
  Flame,
  IdCard,
  Layers,
  LayoutDashboard,
  LogOut,
  Sparkles,
  Users,
  Vote,
} from "lucide-vue-next";
import { nextTick, onMounted, onUnmounted, ref } from "vue";

const page = usePage<PageProps>();
const user = page.props.auth.user;
const mainContentRef = ref<HTMLElement | null>(null);

const runAdminEnter = async () => {
  await nextTick();
  const root = mainContentRef.value;
  if (!root || prefersReducedMotion()) return;
  const targets = Array.from(root.children) as HTMLElement[];
  if (!targets.length) return;
  animate(
    targets,
    { opacity: [0, 1], y: [10, 0] },
    { duration: 0.36, delay: stagger(0.04), ease: [0.16, 1, 0.3, 1] }
  );
};

let removeFinishListener: (() => void) | null = null;
onMounted(() => {
  runAdminEnter();
  removeFinishListener = router.on("finish", (event) => {
    if (event.detail.visit.completed) {
      runAdminEnter();
    }
  });
});

onUnmounted(() => {
  removeFinishListener?.();
});

const menu = {
  items: [
    {
      name: "Dashboard",
      url: route("admin.dashboard"),
      icon: LayoutDashboard,
    },
    {
      name: "Verifikasi KTM",
      url: route("admin.verifications.index"),
      icon: IdCard,
    },
    {
      name: "Kontrol Pemilihan",
      url: route("admin.elections.index"),
      icon: Vote,
    },
    {
      name: "Calon & Tipe",
      url: route("admin.candidates.index"),
      icon: Layers,
    },
    {
      name: "Daftar Pemilih",
      url: route("admin.whitelists.index"),
      icon: Check,
    },
    {
      name: "Pengguna",
      url: route("admin.users.index"),
      icon: Users,
    },
    {
      name: "Kandidat Landing",
      url: route("admin.landing-candidates.index"),
      icon: Sparkles,
    },
  ],
};
</script>

<template>
  <SidebarProvider>
    <Sidebar collapsible="icon">
      <SidebarHeader>
        <Flash />
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              size="lg"
              class="data-[state=open]:bg-accent data-[state=open]:text-accent-foreground border"
            >
              <div
                class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
              >
                <Flame class="size-4" />
              </div>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-extrabold"> PEMIRA </span>
                <span class="truncate text-xs"> Fakultas Ilmu Komputer </span>
              </div>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Menu</SidebarGroupLabel>
          <SidebarMenu>
            <SidebarMenuItem v-for="item in menu.items" :key="item.name">
              <SidebarMenuButton as-child>
                <Link :href="item.url">
                  <component :is="item.icon" />
                  <span>{{ item.name }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </SidebarMenu>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <SidebarMenuButton
                  size="lg"
                  class="data-[state=open]:bg-accent data-[state=open]:text-accent-foreground"
                >
                  <Avatar>
                    <AvatarImage
                      v-if="user.avatar"
                      :src="user.avatar"
                      :alt="user.name"
                    />
                  </Avatar>
                  <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">
                      {{ user.name }}
                    </span>
                    <span class="truncate text-xs">
                      {{ user.email }}
                    </span>
                  </div>
                  <ChevronsUpDown class="ml-auto size-4" />
                </SidebarMenuButton>
              </DropdownMenuTrigger>
              <DropdownMenuContent
                class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                side="bottom"
                align="end"
                :side-offset="4"
              >
                <DropdownMenuLabel class="p-0 font-normal">
                  <div
                    class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
                  >
                    <Avatar>
                      <AvatarImage
                        v-if="user.avatar"
                        :src="user.avatar"
                        :alt="user.name"
                      />
                    </Avatar>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                      <span class="truncate font-semibold">
                        {{ user.name }}
                      </span>
                      <span class="truncate text-xs">
                        {{ user.email }}
                      </span>
                    </div>
                  </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  class="cursor-pointer"
                  @click="router.visit(route('index'))"
                >
                  <Vote />
                  Kembali ke Halaman Voting
                </DropdownMenuItem>
                <DropdownMenuItem
                  class="cursor-pointer"
                  @click="router.post(route('logout'))"
                >
                  <LogOut />
                  Keluar
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
      <SidebarRail />
    </Sidebar>
    <SidebarInset class="bg-stone-50/40">
      <header
        class="flex h-14 shrink-0 items-center gap-2 border-b border-stone-200/70 bg-background/95 backdrop-blur-sm transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12"
      >
        <div id="header" class="flex items-center gap-2 px-4 md:px-6">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />
        </div>
      </header>
      <div ref="mainContentRef" class="flex flex-1 p-4 md:p-6">
        <slot />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>
