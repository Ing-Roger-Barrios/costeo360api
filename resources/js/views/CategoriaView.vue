<template>
  <div class="min-h-screen bg-gray-50 p-6">

    <!-- ================= HEADER ================= -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-6">

      <div class="flex justify-between items-center mb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">
            {{ store.categoria?.nombre }}
          </h1>
          <p class="text-gray-500 text-sm">
            Código: {{ store.categoria?.codigo }}
          </p>
        </div>

        <div class="text-right">
          <p class="text-sm text-gray-500">Total General</p>
          <p class="text-3xl font-bold text-blue-600">
            {{ formatoMoneda(store.totalCategoria) }}
          </p>
        </div>
      </div>

      <div class="flex gap-4">

        <!-- Select Versión -->
        <select
          v-model="store.versionSeleccionada"
          class="border rounded-lg px-3 py-2 bg-white shadow-sm"
        >
          <option
            v-for="v in store.versiones"
            :key="v.id"
            :value="v.id"
          >
            {{ v.version }}
          </option>
        </select>

        <!-- Select Región -->
        <select
          v-model="store.regionSeleccionada"
          class="border rounded-lg px-3 py-2 bg-white shadow-sm"
        >
          <option
            v-for="r in store.regiones"
            :key="r.id"
            :value="r.id"
          >
            {{ r.nombre }}
          </option>
        </select>

      </div>
    </div>

    <!-- ================= MODULOS ================= -->

    <div class="bg-white shadow-md rounded-xl">

      <!-- Tabs -->
      <div class="flex border-b overflow-x-auto">
        <button
          v-for="modulo in store.modulos"
          :key="modulo.id"
          @click="moduloActivo = modulo"
          :class="[
            'px-4 py-3 whitespace-nowrap',
            moduloActivo?.id === modulo.id
              ? 'border-b-2 border-blue-600 font-semibold text-blue-600'
              : 'text-gray-600'
          ]"
        >
          {{ modulo.nombre }}
        </button>
      </div>

      <!-- Tabla Items -->
      <div class="p-4">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="text-left p-2">Código</th>
              <th class="text-left p-2">Descripción</th>
              <th class="text-right p-2">Unidad</th>
              <th class="text-right p-2">Rendimiento</th>
              <th class="text-right p-2">C.U.</th>
              <th class="text-right p-2">Subtotal</th>
            </tr>
          </thead>

          <tbody>
            <template
              v-for="item in moduloActivo?.items"
              :key="item.id"
            >
              <!-- Fila Item -->
              <tr
                class="border-b hover:bg-gray-50 cursor-pointer"
                @click="toggleExpand(item.id)"
              >
                <td class="p-2">{{ item.codigo }}</td>
                <td class="p-2 font-medium">
                  {{ item.descripcion }}
                </td>
                <td class="p-2 text-right">
                  {{ item.unidad }}
                </td>
                <td class="p-2 text-right">
                  {{ item.rendimiento_modulo }}
                </td>
                <td class="p-2 text-right">
                  {{ formatoMoneda(store.calcularCostoUnitarioItem(item)) }}
                </td>
                <td class="p-2 text-right font-semibold">
                  {{ formatoMoneda(store.calcularSubtotalItem(item)) }}
                </td>
              </tr>

              <!-- Recursos Expandibles -->
              <tr v-if="expandedItems.includes(item.id)">
                <td colspan="6" class="bg-gray-50 p-4">

                  <div
                    v-for="grupo in agruparPorTipo(item.recursos)"
                    :key="grupo.tipo"
                    class="mb-4"
                  >
                    <h3 class="font-semibold text-gray-700 mb-2">
                      {{ grupo.tipo }}
                    </h3>

                    <div
                      v-for="recurso in grupo.lista"
                      :key="recurso.id"
                      class="flex justify-between text-sm mb-1"
                    >
                      <span>
                        {{ recurso.nombre }}
                      </span>
                      <span>
                        {{ recurso.unidad }}
                      </span>
                      <span>
                        {{ recurso.rendimiento_recurso }} ×
                        {{ formatoMoneda(store.resolverPrecio(recurso)) }}
                      </span>
                    </div>

                  </div>

                </td>
              </tr>

            </template>
          </tbody>
        </table>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { usePresupuestoStore } from '@/stores/presupuestoStore'
import { watch } from 'vue'


const store = usePresupuestoStore()
const route = useRoute()   // 👈 ESTA LÍNEA FALTABA

const moduloActivo = ref(null)
const expandedItems = ref([])

function toggleExpand(id) {
  if (expandedItems.value.includes(id)) {
    expandedItems.value =
      expandedItems.value.filter(x => x !== id)
  } else {
    expandedItems.value.push(id)
  }
}

watch(
  () => [store.versionSeleccionada, store.regionSeleccionada],
  async ([version, region]) => {
    if (!version || !region) return

    const categoryId = route.params.id

    const res = await fetch(
      `/api/v1/categories/${categoryId}/presupuesto-estructura?version_id=${version}&region_id=${region}`,
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        }
      }
    )

    const data = await res.json()
    store.cargarEstructura(data)
  }
)

function agruparPorTipo(recursos) {
  const grupos = {}

  recursos.forEach(r => {
    if (!grupos[r.tipo]) {
      grupos[r.tipo] = []
    }
    grupos[r.tipo].push(r)
  })

  return Object.keys(grupos).map(tipo => ({
    tipo,
    lista: grupos[tipo]
  }))
}

function formatoMoneda(valor) {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(valor || 0)
}

onMounted(async () => {
  try {

    const categoryId = route.params.id

    if (!categoryId) {
      console.error('No se recibió ID de categoría')
      return
    }

    /*const res = await fetch(
        `/api/v1/categories/${categoryId}/presupuesto-estructura`,
        {
            headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
            }
        }
    )*/
   const res = await fetch(
        `/api/v1/categories/${categoryId}/presupuesto-estructura?version_id=${store.versionSeleccionada}&region_id=${store.regionSeleccionada}`,
        {
            headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
            }
        }
    )

    if (!res.ok) throw new Error('Error al cargar presupuesto')

    const data = await res.json()

    store.cargarEstructura(data)

  } catch (error) {
    console.error('Error en CategoriaView:', error)
  }
})

</script>
