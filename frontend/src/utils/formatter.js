export function formatCollected(val) {
  const num = Number(val || 0)
  if (num <= 9999) return new Intl.NumberFormat('id-ID').format(num)

  const abs = Math.abs(num)
  const suffixes = [
    { value: 1_000_000_000, suffix: ' Miliar' },
    { value: 1_000_000, suffix: ' Juta' },
    { value: 1_000, suffix: ' Ribu' },
  ]

  for (const { value, suffix } of suffixes) {
    if (abs >= value) {
      const divided = num / value
      const rounded = Number.isInteger(divided) ? divided : Math.round(divided * 10) / 10
      return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(rounded) + suffix
    }
  }

  return new Intl.NumberFormat('id-ID').format(num)
}
