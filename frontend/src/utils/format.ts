const numberFormat = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 })

export function formatNumber(value: number | null | undefined): string {
  return numberFormat.format(Number(value ?? 0))
}

export function formatMoney(value: number | null | undefined): string {
  return `${formatNumber(value)} جنيه`
}

function pad(value: number): string {
  return String(value).padStart(2, '0')
}

/** YYYY-MM-DD in the browser's local time. */
export function toISODate(date: Date): string {
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

export function formatDateTime(value: string | null | undefined): string {
  if (!value) return '--'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return `${toISODate(date)} ${pad(date.getHours())}:${pad(date.getMinutes())}`
}

export function formatDate(value: string | null | undefined): string {
  if (!value) return '--'
  return value.length <= 10 ? value : formatDateTime(value).slice(0, 10)
}
