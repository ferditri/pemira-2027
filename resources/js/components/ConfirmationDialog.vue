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
    destructive?: boolean;
    confirmText?: string;
    cancelText?: string;
}>();
</script>

<template>
    <AlertDialog>
        <AlertDialogTrigger as-child>
            <slot></slot>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ props.title }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ props.description }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="$emit('reject')">
                    {{ props.cancelText ?? "Tidak" }}
                </AlertDialogCancel>
                <AlertDialogAction
                    :class="
                        props.destructive
                            ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
                            : ''
                    "
                    @click="$emit('accept')"
                >
                    {{ props.confirmText ?? "Ya" }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
