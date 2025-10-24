<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import AppShell from '@/Layouts/AppShell.vue'

/* --------- util axios/CSRF --------- */
async function ensureCsrf(){ 
  try { await axios.get('/sanctum/csrf-cookie', { withCredentials:true }) } catch {}
}

/* --------- estado general --------- */
const tab = ref('Cursos') // 'Cursos' | 'Secciones' | 'Grupos'
const loading = ref(false)
const error = ref(null)

/* ===================== CURSOS ===================== */
const cursos = ref([])
const cursosMeta = ref(null)
const cursoForm = reactive({ id:null, codigo:'', nombre:'', periodo:'' })
const cursoQ = ref('')
const cursoPage = ref(1)

async function loadCursos(){
  loading.value = true; error.value=null
  try{
    const { data } = await axios.get('/api/cursos', {
      params: { q: cursoQ.value, page: cursoPage.value, per_page: 10 }
    })
    cursos.value = data.data ?? data
    cursosMeta.value = data.meta ?? null
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}
function editCurso(r){ Object.assign(cursoForm, { id:r.id, codigo:r.codigo, nombre:r.nombre, periodo:r.periodo }) }
function newCurso(){ Object.assign(cursoForm, { id:null, codigo:'', nombre:'', periodo:'' }) }

async function saveCurso(){
  await ensureCsrf()
  try{
    if(cursoForm.id){
      await axios.put(`/api/cursos/${cursoForm.id}`, cursoForm, { withCredentials:true })
    }else{
      await axios.post('/api/cursos', cursoForm, { withCredentials:true })
    }
    newCurso()
    await loadCursos()
    alert('Curso guardado')
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}
async function delCurso(id){
  if(!confirm('¿Eliminar curso?')) return
  await ensureCsrf()
  try{
    await axios.delete(`/api/cursos/${id}`, { withCredentials:true })
    await loadCursos()
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}

/* ===================== SECCIONES ===================== */
const secciones = ref([])
const seccionesMeta = ref(null)
const seccionForm = reactive({ id:null, curso_id:null, nombre:'' })
const cursoLookup = ref([]) // para combo
const seccionCursoFiltro = ref(null)
const seccionPage = ref(1)

async function loadCursosLookup(){
  try{
    const { data } = await axios.get('/api/lookups/cursos')
    // normalizar a [{id, nombre, codigo, periodo}]
    cursoLookup.value = (data?.data ?? data ?? []).map(c => ({
      id: c.id, nombre: c.nombre ?? c.text ?? c.label ?? '', codigo: c.codigo, periodo: c.periodo
    }))
  }catch{}
}
async function loadSecciones(){
  loading.value = true; error.value=null
  try{
    const { data } = await axios.get('/api/secciones', {
      params: { curso_id: seccionCursoFiltro.value || undefined, page: seccionPage.value, per_page: 10 }
    })
    secciones.value = data.data ?? data
    seccionesMeta.value = data.meta ?? null
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}
function editSeccion(r){ Object.assign(seccionForm, { id:r.id, curso_id:r.curso_id, nombre:r.nombre }) }
function newSeccion(){ Object.assign(seccionForm, { id:null, curso_id:null, nombre:'' }) }

async function saveSeccion(){
  await ensureCsrf()
  try{
    if(seccionForm.id){
      await axios.put(`/api/secciones/${seccionForm.id}`, seccionForm, { withCredentials:true })
    }else{
      await axios.post('/api/secciones', seccionForm, { withCredentials:true })
    }
    newSeccion()
    await loadSecciones()
    alert('Sección guardada')
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}
async function delSeccion(id){
  if(!confirm('¿Eliminar sección?')) return
  await ensureCsrf()
  try{
    await axios.delete(`/api/secciones/${id}`, { withCredentials:true })
    await loadSecciones()
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}

/* ===================== GRUPOS ===================== */
const grupos = ref([])
const gruposMeta = ref(null)
const grupoForm = reactive({ id:null, seccion_id:null, nombre:'' })
const seccionLookup = ref([])
const gruposCursoFiltro = ref(null)
const gruposSeccionFiltro = ref(null)
const grupoPage = ref(1)

async function loadSeccionesLookup(){
  try{
    const { data } = await axios.get('/api/lookups/secciones', {
      params: { curso_id: gruposCursoFiltro.value || undefined }
    })
    // normalizar a [{id, nombre, curso?}]
    seccionLookup.value = (data?.data ?? data ?? []).map(s => ({
      id: s.id, nombre: s.nombre ?? s.text ?? s.label ?? ''
    }))
  }catch{}
}
async function loadGrupos(){
  loading.value = true; error.value=null
  try{
    const { data } = await axios.get('/api/grupos', {
      params: { seccion_id: gruposSeccionFiltro.value || undefined, page: grupoPage.value, per_page: 10 }
    })
    grupos.value = data.data ?? data
    gruposMeta.value = data.meta ?? null
  }catch(e){ error.value = e?.response?.data?.message || e.message }
  finally{ loading.value=false }
}
function editGrupo(r){ Object.assign(grupoForm, { id:r.id, seccion_id:r.seccion_id, nombre:r.nombre }) }
function newGrupo(){ Object.assign(grupoForm, { id:null, seccion_id:null, nombre:'' }) }

async function saveGrupo(){
  await ensureCsrf()
  try{
    if(grupoForm.id){
      await axios.put(`/api/grupos/${grupoForm.id}`, grupoForm, { withCredentials:true })
    }else{
      await axios.post('/api/grupos', grupoForm, { withCredentials:true })
    }
    newGrupo()
    await loadGrupos()
    alert('Grupo guardado')
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}
async function delGrupo(id){
  if(!confirm('¿Eliminar grupo?')) return
  await ensureCsrf()
  try{
    await axios.delete(`/api/grupos/${id}`, { withCredentials:true })
    await loadGrupos()
  }catch(e){ alert(e?.response?.data?.message || 'Error'); console.error(e) }
}

/* --------- inicial --------- */
onMounted(async ()=>{
  await ensureCsrf()
  await Promise.all([loadCursos(), loadCursosLookup(), loadSecciones(), loadSeccionesLookup(), loadGrupos()])
})
</script>

<template>
  <AppShell>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-800">Académico</h1>

        <div class="inline-flex rounded-lg border bg-white p-1">
          <button
            class="rounded-md px-3 py-1 text-sm"
            :class="tab==='Cursos' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            @click="tab='Cursos'">Cursos</button>
          <button
            class="rounded-md px-3 py-1 text-sm"
            :class="tab==='Secciones' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            @click="tab='Secciones'">Secciones</button>
          <button
            class="rounded-md px-3 py-1 text-sm"
            :class="tab==='Grupos' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'"
            @click="tab='Grupos'">Grupos</button>
        </div>
      </div>

      <div v-if="error" class="rounded border border-rose-200 bg-rose-50 p-3 text-rose-700">{{ error }}</div>

      <!-- ================== CURSOS ================== -->
      <section v-show="tab==='Cursos'" class="grid gap-4 md:grid-cols-[360px_1fr]">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Nuevo / Editar Curso</h2>
          <div class="space-y-2">
            <input v-model.trim="cursoForm.codigo" class="w-full rounded border p-2" placeholder="Código (p.ej. QUI101)" />
            <input v-model.trim="cursoForm.nombre" class="w-full rounded border p-2" placeholder="Nombre" />
            <input v-model.trim="cursoForm.periodo" class="w-full rounded border p-2" placeholder="Periodo (2025-I)" />
            <div class="flex gap-2">
              <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="saveCurso">Guardar</button>
              <button class="rounded bg-slate-200 px-3 py-2" @click="newCurso">Limpiar</button>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-3 flex items-center gap-2">
            <input v-model="cursoQ" @input="cursoPage=1; loadCursos()" class="rounded border p-2" placeholder="Buscar…" />
          </div>
          <div v-if="loading">Cargando…</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b bg-slate-50">
              <th class="p-2 text-left">Código</th><th class="p-2 text-left">Nombre</th><th class="p-2 text-left">Periodo</th><th class="p-2"></th>
            </tr></thead>
            <tbody>
              <tr v-for="c in cursos" :key="c.id" class="border-b">
                <td class="p-2">{{ c.codigo }}</td>
                <td class="p-2">{{ c.nombre }}</td>
                <td class="p-2">{{ c.periodo }}</td>
                <td class="p-2 text-right">
                  <button class="mr-2 text-indigo-600" @click="editCurso(c)">Editar</button>
                  <button class="text-rose-600" @click="delCurso(c.id)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="cursosMeta" class="mt-3 flex items-center gap-2">
            <button class="rounded border px-3 py-1" :disabled="(cursosMeta.current_page||1)<=1" @click="cursoPage--; loadCursos()">Anterior</button>
            <span>Página {{ cursosMeta.current_page }} de {{ cursosMeta.last_page }}</span>
            <button class="rounded border px-3 py-1" :disabled="(cursosMeta.current_page||1)>=cursosMeta.last_page" @click="cursoPage++; loadCursos()">Siguiente</button>
          </div>
        </div>
      </section>

      <!-- ================== SECCIONES ================== -->
      <section v-show="tab==='Secciones'" class="grid gap-4 md:grid-cols-[360px_1fr]">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Nueva / Editar Sección</h2>
          <div class="space-y-2">
            <select v-model="seccionForm.curso_id" class="w-full rounded border p-2">
              <option :value="null">Curso…</option>
              <option v-for="c in cursoLookup" :key="c.id" :value="c.id">
                {{ c.codigo ? c.codigo+' · ' : '' }}{{ c.nombre }} ({{ c.periodo }})
              </option>
            </select>
            <input v-model.trim="seccionForm.nombre" class="w-full rounded border p-2" placeholder="Nombre (p.ej. A)" />
            <div class="flex gap-2">
              <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="saveSeccion">Guardar</button>
              <button class="rounded bg-slate-200 px-3 py-2" @click="newSeccion">Limpiar</button>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-3 flex items-end gap-2">
            <div>
              <label class="text-xs text-slate-500">Curso</label>
              <select v-model="seccionCursoFiltro" @change="seccionPage=1; loadSecciones()" class="mt-1 rounded border p-2">
                <option :value="null">Todos</option>
                <option v-for="c in cursoLookup" :key="c.id" :value="c.id">
                  {{ c.codigo ? c.codigo+' · ' : '' }}{{ c.nombre }} ({{ c.periodo }})
                </option>
              </select>
            </div>
          </div>
          <div v-if="loading">Cargando…</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b bg-slate-50">
              <th class="p-2 text-left">Curso</th><th class="p-2 text-left">Sección</th><th class="p-2"></th>
            </tr></thead>
            <tbody>
              <tr v-for="s in secciones" :key="s.id" class="border-b">
                <td class="p-2">{{ s.codigo }} · {{ s.curso }} ({{ s.periodo }})</td>
                <td class="p-2">{{ s.nombre }}</td>
                <td class="p-2 text-right">
                  <button class="mr-2 text-indigo-600" @click="editSeccion(s)">Editar</button>
                  <button class="text-rose-600" @click="delSeccion(s.id)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="seccionesMeta" class="mt-3 flex items-center gap-2">
            <button class="rounded border px-3 py-1" :disabled="(seccionesMeta.current_page||1)<=1" @click="seccionPage--; loadSecciones()">Anterior</button>
            <span>Página {{ seccionesMeta.current_page }} de {{ seccionesMeta.last_page }}</span>
            <button class="rounded border px-3 py-1" :disabled="(seccionesMeta.current_page||1)>=seccionesMeta.last_page" @click="seccionPage++; loadSecciones()">Siguiente</button>
          </div>
        </div>
      </section>

      <!-- ================== GRUPOS ================== -->
      <section v-show="tab==='Grupos'" class="grid gap-4 md:grid-cols-[360px_1fr]">
        <div class="rounded-xl bg-white p-4 shadow-sm">
          <h2 class="mb-3 font-semibold">Nuevo / Editar Grupo</h2>
          <div class="space-y-2">
            <div class="text-xs text-slate-500">Filtra secciones por Curso (opcional)</div>
            <select v-model="gruposCursoFiltro" @change="loadSeccionesLookup()" class="w-full rounded border p-2">
              <option :value="null">Todos los cursos</option>
              <option v-for="c in cursoLookup" :key="c.id" :value="c.id">
                {{ c.codigo ? c.codigo+' · ' : '' }}{{ c.nombre }} ({{ c.periodo }})
              </option>
            </select>

            <select v-model="grupoForm.seccion_id" class="w-full rounded border p-2">
              <option :value="null">Sección…</option>
              <option v-for="s in seccionLookup" :key="s.id" :value="s.id">{{ s.nombre }}</option>
            </select>

            <input v-model.trim="grupoForm.nombre" class="w-full rounded border p-2" placeholder="Nombre (p.ej. Grupo 1)" />
            <div class="flex gap-2">
              <button class="rounded bg-emerald-600 px-3 py-2 text-white" @click="saveGrupo">Guardar</button>
              <button class="rounded bg-slate-200 px-3 py-2" @click="newGrupo">Limpiar</button>
            </div>
          </div>
        </div>

        <div class="rounded-xl bg-white p-4 shadow-sm">
          <div class="mb-3 flex items-end gap-2">
            <div>
              <label class="text-xs text-slate-500">Sección</label>
              <select v-model="gruposSeccionFiltro" @change="grupoPage=1; loadGrupos()" class="mt-1 rounded border p-2">
                <option :value="null">Todas</option>
                <option v-for="s in seccionLookup" :key="s.id" :value="s.id">{{ s.nombre }}</option>
              </select>
            </div>
          </div>
          <div v-if="loading">Cargando…</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b bg-slate-50">
              <th class="p-2 text-left">Curso/Sección</th><th class="p-2 text-left">Grupo</th><th class="p-2 text-left">Delegado</th><th class="p-2"></th>
            </tr></thead>
            <tbody>
              <tr v-for="g in grupos" :key="g.id" class="border-b">
                <td class="p-2">{{ g.curso }} / {{ g.seccion }}</td>
                <td class="p-2">{{ g.nombre }}</td>
                <td class="p-2">{{ g.delegado ?? '-' }}</td>
                <td class="p-2 text-right">
                  <button class="mr-2 text-indigo-600" @click="editGrupo(g)">Editar</button>
                  <button class="text-rose-600" @click="delGrupo(g.id)">Eliminar</button>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="gruposMeta" class="mt-3 flex items-center gap-2">
            <button class="rounded border px-3 py-1" :disabled="(gruposMeta.current_page||1)<=1" @click="grupoPage--; loadGrupos()">Anterior</button>
            <span>Página {{ gruposMeta.current_page }} de {{ gruposMeta.last_page }}</span>
            <button class="rounded border px-3 py-1" :disabled="(gruposMeta.current_page||1)>=gruposMeta.last_page" @click="grupoPage++; loadGrupos()">Siguiente</button>
          </div>
        </div>
      </section>

    </div>
  </AppShell>
</template>
