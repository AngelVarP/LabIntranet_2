// super simple: usa alert como fallback
export function showToast(message, type = 'info') {
  if (typeof window !== 'undefined') {
    // puedes reemplazar por tu lib favorita luego
    alert((type === 'error' ? 'Error: ' : '') + message)
  }
}
