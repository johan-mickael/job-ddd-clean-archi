const formatter = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })

export function formatDate(iso: string): string {
  return formatter.format(new Date(iso))
}
