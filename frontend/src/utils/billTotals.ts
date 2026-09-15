export interface BillLineAmounts {
  price: number
  quantity: number
  discount: number
}

export interface BillTotals {
  subtotal: number
  linesDiscount: number
  totalDiscount: number
  net: number
}

function round(value: number): number {
  return Math.round(value * 100) / 100
}

/** Same rules as the server: line total = price * quantity - line discount. */
export function lineTotal(line: BillLineAmounts): number {
  return round(line.price * line.quantity - line.discount)
}

export function billTotals(lines: BillLineAmounts[], billDiscount: number): BillTotals {
  const subtotal = round(lines.reduce((sum, line) => sum + line.price * line.quantity, 0))
  const linesDiscount = round(lines.reduce((sum, line) => sum + line.discount, 0))
  const totalDiscount = round(linesDiscount + billDiscount)
  return { subtotal, linesDiscount, totalDiscount, net: round(subtotal - totalDiscount) }
}
