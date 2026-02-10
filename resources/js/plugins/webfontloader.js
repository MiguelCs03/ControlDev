/**
 * plugins/webfontloader.js
 *
 * webfontloader documentation: https://github.com/typekit/webfontloader
 * 
 * DESACTIVADO: Causaba errores CORS. Las fuentes se cargan directamente desde el HTML.
 */

export async function loadFonts() {
  // Desactivado para evitar errores CORS
  console.log('Webfontloader desactivado - usando fuentes del sistema')
}

export default function () {
  // loadFonts() - Desactivado
}
