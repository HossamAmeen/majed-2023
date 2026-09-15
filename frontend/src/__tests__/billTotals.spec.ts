import { describe, expect, it } from 'vitest'

import { billTotals, lineTotal } from '@/utils/billTotals'

describe('billTotals', () => {
  it('matches the server rules: line discounts are per line, bill discount is added on top', () => {
    const lines = [
      { price: 100, quantity: 2, discount: 5 },
      { price: 250, quantity: 1, discount: 0 },
    ]

    expect(billTotals(lines, 10)).toEqual({
      subtotal: 450,
      linesDiscount: 5,
      totalDiscount: 15,
      net: 435,
    })
  })

  it('rounds to two decimals', () => {
    expect(lineTotal({ price: 19.99, quantity: 3, discount: 0.1 })).toBe(59.87)
  })

  it('handles an empty bill', () => {
    expect(billTotals([], 0)).toEqual({ subtotal: 0, linesDiscount: 0, totalDiscount: 0, net: 0 })
  })
})
