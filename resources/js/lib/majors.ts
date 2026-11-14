export const MAJOR_OPTIONS = [
  { value: "", label: "Semua Prodi (Fakultas)" },
  { value: "10", label: "10 - Informatika" },
  { value: "20", label: "20 - Sistem Informasi" },
  { value: "30", label: "30 - Sains Data" },
  { value: "40", label: "40 - Bisnis Digital" },
] as const;

export function formatMajor(code?: string | null): string {
  switch (code) {
    case "10":
      return "Informatika (10)";
    case "20":
      return "Sistem Informasi (20)";
    case "30":
      return "Sains Data (30)";
    case "40":
      return "Bisnis Digital (40)";
    default:
      return code ? `Prodi ${code}` : "Semua Prodi";
  }
}
