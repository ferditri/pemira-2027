<script lang="ts" setup>
import { Link, useForm } from "@inertiajs/vue3";
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

const selected = ref(
  props.ballotDetails?.map((detail) => detail.candidate_id) ?? [],
);

const viewing = ref<Candidate | null>(null);
const open = ref(false);

const openDetail = (candidate: Candidate) => {
  viewing.value = candidate;
  open.value = true;
};

const select = (id: number) => {
  if (selected.value.includes(id)) {
    selected.value = selected.value.filter((value) => value !== id);
  } else if (props.group.min_candidates === 1) {
    selected.value = [id];
  } else if (selected.value.length < props.group.min_candidates) {
    selected.value.push(id);
  }
};

const submit = () => {
  if (!selected.value) {
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
  useForm({
    candidate_ids: selected.value,
  }).post(
    route("vote.group.store", {
      organization: props.organization.id,
      group: props.group.id,
    }),
    {
      preserveState: false,
    },
  );
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Cinzel+Decorative:wght@700;900&family=Libre+Baskerville&display=swap");

.font-title {
  font-family: "Cinzel Decorative", serif;
}
.font-label {
  font-family: "Cinzel", serif;
}
.font-body {
  font-family: "Libre Baskerville", serif;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .modal-box,
.modal-leave-active .modal-box {
  transition: transform 0.3s ease;
}
.modal-enter-from .modal-box,
.modal-leave-to .modal-box {
  transform: scale(0.92) translateY(1rem);
}
</style>

<template>
  <div
    class="min-h-dvh flex justify-center p-4 sm:p-6 lg:p-8 bg-[#F8E4BB] bg-[url('/background.webp')] bg-cover bg-center bg-fixed relative"
  >
    <div class="w-full max-w-5xl relative z-10">
      <div
        class="rounded-[2.5rem] bg-[#F8E4BB] px-6 py-10 text-center shadow-[0_20px_50px_rgba(15,27,77,0.25)]"
      >
        <h1 class="font-title text-3xl sm:text-5xl font-black text-[#0F1B4D]">
          {{ group.name }}
        </h1>
        <p class="font-label mt-1 text-base font-bold text-[#151333]">
          PILIH {{ group.min_candidates }} KANDIDAT
        </p>

        <div class="mt-5 flex justify-center gap-3">
          <Link
            :href="
              route('vote.group.previous', {
                organization: organization.id,
                group: group.id,
              })
            "
            class="font-label rounded-md bg-[#0F1B4D] px-8 py-2 text-sm font-bold text-[#F8E4BB] hover:brightness-125"
          >
            KEMBALI
          </Link>
          <button
            type="button"
            @click="submit"
            class="font-label rounded-md border border-[#A86B1F] bg-[#FBF3DE] px-8 py-2 text-sm font-bold text-[#0F1B4D] hover:brightness-95"
          >
            SELANJUTNYA
          </button>
        </div>

        <p
          v-if="form.errors.candidate_ids"
          class="font-body mt-3 text-sm font-bold text-red-700"
        >
          {{ form.errors.candidate_ids }}
        </p>

        <div class="mt-10 flex flex-wrap justify-center gap-10">
          <div
            v-for="(candidate, number) in candidates"
            :key="candidate.id"
            @click="select(candidate.id)"
            :class="[
              selected.includes(candidate.id)
                ? 'ring-4 ring-[#FF7F0F] scale-[1.03]'
                : 'hover:scale-[1.02]',
              'w-80 cursor-pointer rounded-t-[10rem] rounded-b-3xl border-4 border-[#A86B1F] bg-[#FBF3DE] px-6 pb-6 pt-12 transition-all duration-300 shadow-xl',
            ]"
          >
            <img
              :src="candidate.picture"
              :alt="candidate.name_1"
              class="mx-auto size-56 rounded-full border-4 border-[#A86B1F] object-cover object-top"
            />
            <p class="font-title mt-5 text-2xl font-black text-[#6B1A24]">
              CALON {{ number + 1 }}
            </p>
            <p
              class="font-label mt-2 text-lg font-bold leading-snug text-[#0F1B4D]"
            >
              {{ candidate.name_1 }}
              <template v-if="candidate.name_2">
                <br />&amp;<br />
                {{ candidate.name_2 }}
              </template>
            </p>
            <button
              type="button"
              @click.stop="openDetail(candidate)"
              class="font-label mt-5 rounded-md bg-[#6B1A24] px-6 py-2 text-xs font-bold text-[#F8E4BB] hover:brightness-125"
            >
              VISI &amp; MISI
            </button>
          </div>
        </div>
      </div>
    </div>

    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="open = false"
      >
        <div
          class="modal-box max-h-[85dvh] w-full max-w-lg overflow-y-auto rounded-3xl bg-[#F8E4BB] p-6 text-left shadow-2xl"
        >
          <h2 class="font-title text-xl font-black text-[#0F1B4D]">
            {{ viewing?.name_1 }}
            <template v-if="viewing?.name_2">
              &amp; {{ viewing.name_2 }}</template
            >
          </h2>
          <p class="font-label mt-4 text-xs font-bold text-[#6B1A24]">VISI</p>
          <pre class="font-body whitespace-pre-wrap text-xs text-[#151333]">{{
            viewing?.vision
          }}</pre>
          <p class="font-label mt-4 text-xs font-bold text-[#6B1A24]">MISI</p>
          <pre class="font-body whitespace-pre-wrap text-xs text-[#151333]">{{
            viewing?.mission
          }}</pre>
          <button
            type="button"
            @click="open = false"
            class="font-label mt-5 rounded-md bg-[#0F1B4D] px-6 py-1.5 text-xs font-bold text-[#F8E4BB] hover:brightness-125"
          >
            TUTUP
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>