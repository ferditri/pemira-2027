<script lang="ts" setup>
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { useEnterMotion } from "@/composables/useMotion";
import { Link, router, useForm } from "@inertiajs/vue3";
import { Check, X } from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps<{
  organization: Organization;
  group: Group;
  candidates: Candidate[];
  ballotDetails: BallotDetail[] | null;
}>();

const form = useForm({
  candidate_ids: null,
});

const isSubmitting = ref(false);
const activeCandidate = ref<(Candidate & { index: number }) | null>(null);
const rootRef = ref<HTMLElement | null>(null);
useEnterMotion(rootRef, "[data-enter]", { y: 14, duration: 0.48, staggerDelay: 0.06 });

const selected = ref<number[]>(
  props.ballotDetails?.map((detail) => detail.candidate_id) ?? [],
);

const select = (id: number) => {
  if (selected.value.includes(id)) {
    selected.value = selected.value.filter((value) => value !== id);
  } else if (props.group.min_candidates === 1) {
    selected.value = [id];
  } else if (selected.value.length < props.group.min_candidates) {
    selected.value.push(id);
  }
};

const openVisionModal = (candidate: Candidate, index: number) => {
  activeCandidate.value = { ...candidate, index };
};

const closeVisionModal = () => {
  activeCandidate.value = null;
};

const submit = () => {
  if (isSubmitting.value) {
    return;
  }
  if (!selected.value || selected.value.length === 0) {
    return;
  }
  if (
    selected.value.length < props.group.min_candidates &&
    props.candidates.length >= props.group.min_candidates
  ) {
    form.setError(
      "candidate_ids",
      `Pilihlah minimal ${props.group.min_candidates} kandidat!`,
    );
    return;
  }

  isSubmitting.value = true;
  form.clearErrors();

  router.post(
    route("vote.group.store", {
      organization: props.organization.id,
      group: props.group.id,
    }),
    {
      candidate_ids: selected.value,
    },
    {
      preserveState: false,
      onFinish: () => {
        isSubmitting.value = false;
      },
    },
  );
};
</script>

<template>
  <div class="vote-royal-bg flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div ref="rootRef" class="max-w-5xl w-full relative z-10">
      <Card data-enter class="vote-parchment-card border-0">
        <CardHeader class="text-center pt-8 pb-5 px-6">
          <CardTitle
            class="font-cinzel text-2xl sm:text-4xl font-extrabold text-[#0c1d44] uppercase tracking-wider"
          >
            {{ group.name }}
          </CardTitle>
          <CardDescription
            class="font-cinzel text-[#1e2f54] text-sm sm:text-base font-bold mt-1.5 tracking-wide uppercase"
          >
            <template v-if="group.min_candidates === 1">
              Pilih Satu Kandidat
            </template>
            <template v-else>
              Pilihlah {{ group.min_candidates }} Kandidat di Bawah Ini
            </template>
          </CardDescription>

          <div class="flex flex-wrap justify-center gap-3.5 mt-5">
            <Link
              :href="
                route('vote.group.previous', {
                  organization: organization.id,
                  group: group.id,
                })
              "
            >
              <Button
                class="vote-btn-navy rounded-lg px-8 py-2.5 text-xs sm:text-sm uppercase"
              >
                Kembali
              </Button>
            </Link>

            <Button
              :disabled="isSubmitting || !selected || selected.length === 0"
              @click="submit"
              class="vote-btn-outline rounded-lg px-8 py-2.5 text-xs sm:text-sm uppercase disabled:opacity-45 disabled:cursor-not-allowed"
            >
              <span v-if="isSubmitting">Menyimpan...</span>
              <span v-else>Selanjutnya</span>
            </Button>
          </div>

          <div
            v-if="form.errors.candidate_ids"
            class="text-red-700 bg-red-100/80 border border-red-300 rounded-lg px-4 py-2 text-xs sm:text-sm font-bold mt-4 max-w-md mx-auto"
          >
            {{ form.errors.candidate_ids }}
          </div>
        </CardHeader>

        <CardContent class="pt-3 pb-10 px-6 sm:px-10">
          <div class="flex flex-wrap justify-center gap-7 sm:gap-9">
            <div
              v-for="(candidate, number) in candidates"
              :key="candidate.id"
              @click="select(candidate.id)"
              :class="[
                'vote-candidate-arch w-full max-w-[260px] px-5 pt-7 pb-6 text-center cursor-pointer relative flex flex-col items-center justify-between',
                selected.includes(candidate.id) ? 'is-selected' : '',
              ]"
            >
              <!-- Selected Checkmark Badge -->
              <div
                v-if="selected.includes(candidate.id)"
                class="absolute top-3 right-3 size-7 rounded-full bg-[#d98218] text-white flex items-center justify-center shadow-md z-20"
                title="Terpilih"
              >
                <Check class="size-4 stroke-[3]" />
              </div>

              <!-- Circular Portrait with Top Diamond Ornament -->
              <div class="relative mb-4">
                <div
                  class="absolute -top-1.5 left-1/2 -translate-x-1/2 size-3 rotate-45 bg-[#d99b38] border border-[#5c3d12] z-10"
                ></div>
                <div
                  class="size-32 sm:size-36 rounded-full p-1 bg-gradient-to-b from-[#e4b35a] to-[#a9782a] shadow-md"
                >
                  <img
                    :src="candidate.picture"
                    :alt="candidate.name_1"
                    class="w-full h-full rounded-full object-cover bg-[#0c1d44]"
                  />
                </div>
              </div>

              <!-- Candidate Label & Name -->
              <div class="space-y-1 mb-5">
                <p
                  class="font-cinzel text-base sm:text-lg font-extrabold text-[#5c1624] tracking-wide uppercase"
                >
                  Calon {{ number + 1 }}
                </p>
                <h3
                  class="font-cinzel text-xs sm:text-sm font-bold text-[#0c1d44] leading-snug uppercase"
                >
                  {{ candidate.name_1 }}
                  <template v-if="candidate.name_2">
                    <br />
                    &amp;
                    <br />
                    {{ candidate.name_2 }}
                  </template>
                </h3>
              </div>

              <!-- Visi & Misi Modal Button -->
              <button
                type="button"
                @click.stop="openVisionModal(candidate, number)"
                class="vote-btn-maroon rounded-md px-6 py-2 text-[11px] uppercase tracking-wider"
              >
                Visi &amp; Misi
              </button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Visi & Misi Royal Modal -->
    <div
      v-if="activeCandidate"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#050c1a]/80 backdrop-blur-sm"
      @click.self="closeVisionModal"
    >
      <div
        class="relative w-full max-w-xl max-h-[85vh] flex flex-col overflow-hidden rounded-2xl bg-[#fbf5e8] border-2 border-[#bba070] shadow-2xl"
      >
        <button
          type="button"
          @click="closeVisionModal"
          class="absolute top-4 right-4 z-30 size-9 rounded-full bg-[#6e1324] text-[#faebd7] border border-[#d4a446] shadow-md flex items-center justify-center hover:bg-[#8a1a2f] transition"
          aria-label="Tutup modal"
        >
          <X class="size-4" />
        </button>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-6 sm:p-8">
        <div class="text-center mb-5">
          <img
            :src="activeCandidate.picture"
            :alt="activeCandidate.name_1"
            class="size-24 rounded-full object-cover mx-auto mb-3 border-2 border-[#ab8240] shadow-md"
          />
          <span
            class="inline-block px-3.5 py-1 rounded-full text-xs font-cinzel font-bold bg-[#6e1324] text-[#fce7ba] mb-1.5"
          >
            CALON {{ activeCandidate.index + 1 }}
          </span>
          <h3
            class="font-cinzel text-lg sm:text-xl font-extrabold text-[#0c1d44] uppercase"
          >
            {{ activeCandidate.name_1 }}
            <template v-if="activeCandidate.name_2">
              &amp; {{ activeCandidate.name_2 }}
            </template>
          </h3>
        </div>

        <div class="space-y-4 text-left">
          <div class="bg-white/70 border border-[#d4ba8a] rounded-xl p-4">
            <h4
              class="font-cinzel text-xs font-extrabold text-[#6e1324] uppercase tracking-wider mb-1.5"
            >
              Visi
            </h4>
            <pre
              class="font-sans text-xs sm:text-sm text-[#3c2d17] whitespace-pre-wrap leading-relaxed"
              >{{ activeCandidate.vision }}</pre
            >
          </div>

          <div class="bg-white/70 border border-[#d4ba8a] rounded-xl p-4">
            <h4
              class="font-cinzel text-xs font-extrabold text-[#6e1324] uppercase tracking-wider mb-1.5"
            >
              Misi
            </h4>
            <pre
              class="font-sans text-xs sm:text-sm text-[#3c2d17] whitespace-pre-wrap leading-relaxed"
              >{{ activeCandidate.mission }}</pre
            >
          </div>
        </div>

        <div class="mt-6 flex justify-center gap-3">
          <Button
            type="button"
            @click="
              select(activeCandidate.id);
              closeVisionModal();
            "
            class="vote-btn-navy rounded-lg px-7 py-2.5 text-xs uppercase"
          >
            {{
              selected.includes(activeCandidate.id)
                ? "Batalkan Pilihan"
                : "Pilih Kandidat Ini"
            }}
          </Button>
          <Button
            type="button"
            @click="closeVisionModal"
            class="vote-btn-outline rounded-lg px-6 py-2.5 text-xs uppercase"
          >
            Tutup
          </Button>
        </div>
        </div>
      </div>
    </div>
  </div>
</template>
