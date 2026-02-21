import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const usePresupuestoStore = defineStore('presupuesto', () => {

  // =========================
  // STATE
  // =========================

  const categoria = ref(null)
  const modulos = ref([])
  const regiones = ref([])
  const versiones = ref([])

  const regionSeleccionada = ref(null)
  const versionSeleccionada = ref(null)

  // =========================
  // ACTION: Cargar datos
  // =========================

  function cargarEstructura(data) {
    categoria.value = data.categoria
    modulos.value = data.modulos
    regiones.value = data.regiones
    versiones.value = data.versiones

    regionSeleccionada.value = regiones.value[0]?.id ?? null
    versionSeleccionada.value = versiones.value[0]?.id ?? null
  }

  // =========================
  // CORE: Resolver precio
  // =========================

  function resolverPrecio(recurso) {
    const versionId = versionSeleccionada.value
    const regionId = regionSeleccionada.value

    const vrKey = `${versionId}_${regionId}`

    if (recurso.precios_version_region?.[vrKey])
      return recurso.precios_version_region[vrKey]

    if (recurso.precios_version?.[versionId])
      return recurso.precios_version[versionId]

    if (recurso.precios_region?.[regionId])
      return recurso.precios_region[regionId]

    return recurso.precio_referencia
  }

  // =========================
  // Cálculo Item
  // =========================

  /*function calcularCostoUnitarioItem(item) {
    return item.recursos.reduce((total, recurso) => {
      const precio = resolverPrecio(recurso)
      return total + (recurso.rendimiento_recurso * precio)
    }, 0)
  }*/
 function calcularCostoUnitarioItem(item) {
    return calcularPrecioUnitarioFinal(item)
    }


  function calcularSubtotalItem(item) {
    const costoUnitario = calcularCostoUnitarioItem(item)
    return costoUnitario * item.rendimiento_modulo
  }

    // =========================
  // Cálculo de totales por tipo
  // =========================

  function calcularTotalesPorTipo(item) {
  let materiales = 0
  let manoObra = 0
  let equipo = 0

  item.recursos.forEach(recurso => {
    const precio = resolverPrecio(recurso)
    
    // 1. Redondeamos el subtotal de este recurso individual
    // Multiplicamos rendimiento por precio y fijamos 2 decimales
    const subtotal = +(recurso.rendimiento_recurso * precio).toFixed(2)

    switch (recurso.tipo) {
      case 'Material':
        materiales += subtotal
        break
      case 'ManoObra':
        manoObra += subtotal
        break
      case 'Equipo':
        equipo += subtotal
        break
    }
  })

  // 2. Redondeamos los totales finales para evitar decimales residuales 
  // (ej. 10.000000000004) propios de las sumas en JS
  return { 
    materiales: +materiales.toFixed(2), 
    manoObra: +manoObra.toFixed(2), 
    equipo: +equipo.toFixed(2) 
  }
}

// =========================
  // Cálculo con la norma sabs
  // =========================

function calcularPrecioUnitarioFinal(item) {
  const { materiales, manoObra, equipo } = calcularTotalesPorTipo(item)

  const A = materiales
  const B = manoObra
  const C = equipo

  const D = A
  const E = B
  const F = E * 0.55
  const O = (E + F) * 0.1494
  const G = E + F + O
  const H = G * 0.05
  const I = C + H
  const J = D + G + I
  const K = J * 0.00
  const L = J * 0.10
  const M = (J + L) * 0.10
  const N = J + L + M
  const P = N * 0.0309
  const Q = N + P

  return Number(Q.toFixed(2))
}



  // =========================
  // Totales
  // =========================

  const totalCategoria = computed(() => {
    return modulos.value.reduce((totalModulos, modulo) => {

      const totalModulo = modulo.items.reduce((totalItems, item) => {
        return totalItems + calcularSubtotalItem(item)
      }, 0)

      return totalModulos + totalModulo
    }, 0)
  })

  // =========================
  // Return
  // =========================

  return {
    categoria,
    modulos,
    regiones,
    versiones,
    regionSeleccionada,
    versionSeleccionada,

    cargarEstructura,
    resolverPrecio,
    calcularCostoUnitarioItem,
    calcularSubtotalItem,
    totalCategoria
  }
})
