import { createMongoAbility } from '@casl/ability'
import { abilitiesPlugin } from '@casl/vue'

export default function (app) {
  try {
    // Read from cookie first, then fallback to localStorage
    let rawRules = useCookie('userAbilityRules').value
    if (!rawRules && typeof localStorage !== 'undefined') {
      const stored = localStorage.getItem('userAbilityRules')
      if (stored) {
        try {
          rawRules = JSON.parse(stored)
        } catch (e) {
          console.error('[casl] Error parsing localStorage rules:', e)
        }
      }
    }
    const rules = Array.isArray(rawRules) ? rawRules : (rawRules ? rawRules : [])

    const initialAbility = createMongoAbility(rules)

    app.use(abilitiesPlugin, initialAbility, {
      useGlobalProperties: true,
    })
  } catch (err) {
    // Fail gracefully - register plugin without initial ability if something goes wrong
    console.error('[casl] Error initializing abilities, falling back to empty rules', err)

    const initialAbility = createMongoAbility([])

    app.use(abilitiesPlugin, initialAbility, { useGlobalProperties: true })
  }
}
