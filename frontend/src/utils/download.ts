export function saveBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}

/** Open a printable page (receipt, barcode label) in a small window, like the old panel did. */
export function openPrintWindow(path: string): void {
  window.open(path, 'PRINT', 'height=650,width=420')?.focus()
}
