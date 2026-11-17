<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";

const props = defineProps<{
    title: string;
    description: string;
}>();
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Libre+Baskerville&display=swap");
</style>

<template>
    <AlertDialog>
        <AlertDialogTrigger as-child>
            <slot></slot>
        </AlertDialogTrigger>
        <AlertDialogContent
            class="rounded-[2rem] border-0 bg-[#F8E4BB] p-8 text-center shadow-[0_20px_50px_rgba(15,27,77,0.35)]"
        >
            <AlertDialogHeader class="space-y-2 sm:text-center">
                <AlertDialogTitle
                    class="font-['Cinzel',serif] text-xl font-bold text-[#0F1B4D]"
                >
                    {{ props.title }}
                </AlertDialogTitle>
                <AlertDialogDescription
                    class="font-['Libre_Baskerville',serif] text-sm text-[#151333]"
                >
                    {{ props.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter
                class="mt-4 flex-row justify-center gap-3 sm:justify-center"
            >
                <AlertDialogCancel
                    class="font-['Cinzel',serif] mt-0 rounded-xl border border-[#0F1B4D]/40 bg-transparent px-6 text-sm font-bold text-[#0F1B4D] hover:bg-[#0F1B4D]/10"
                    @click="$emit('reject')"
                >
                    TIDAK
                </AlertDialogCancel>
                <AlertDialogAction
                    class="font-['Cinzel',serif] rounded-xl border-2 border-[#A86B1F] bg-[#0F1B4D] px-8 text-sm font-bold text-[#F8E4BB] hover:bg-[#0F1B4D] hover:brightness-125"
                    @click="$emit('accept')"
                >
                    YA
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>