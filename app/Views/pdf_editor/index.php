<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    #pdfEditorToolbar {
        background: #fff;
        border: 1px solid #e3e6ec;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    #pdfEditorToolbar .divider {
        width: 1px;
        align-self: stretch;
        background: #e3e6ec;
        margin: 0 4px;
    }

    #pdfEditorToolbar .btn {
        white-space: nowrap;
    }

    #pdfEditorToolbar .btn.tool-active {
        box-shadow: inset 0 0 0 2px rgba(0, 0, 0, .25);
    }

    #pdfEditorStage {
        background: #6c757d20;
        border: 1px solid #e3e6ec;
        border-radius: 10px;
        min-height: 60vh;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        overflow: auto;
        padding: 24px;
    }

    #pdfEditorEmpty {
        margin: auto;
        text-align: center;
        color: #8a94a6;
    }

    #pdfEditorEmpty i {
        font-size: 46px;
        display: block;
        margin-bottom: 10px;
    }

    #pageWrapper {
        position: relative;
        box-shadow: 0 2px 14px rgba(0, 0, 0, .18);
        background: #fff;
    }

    #pdfBaseCanvas {
        display: block;
    }

    #pdfBaseCanvas,
    #fabricCanvasEl {
        position: absolute;
        top: 0;
        left: 0;
    }

    .color-swatch-input {
        width: 40px;
        height: 38px;
        padding: 2px;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    #panelCapas {
        width: 260px;
        flex-shrink: 0;
        margin-left: 14px;
        border: 1px solid #e3e6ec;
        border-radius: 10px;
        background: #fff;
        display: flex;
        flex-direction: column;
        max-height: 60vh;
    }

    .panel-capas-header {
        padding: 10px 12px;
        font-size: .82rem;
        font-weight: 600;
        color: #495057;
        border-bottom: 1px solid #e3e6ec;
        flex-shrink: 0;
    }

    .panel-capas-lista {
        overflow-y: auto;
        padding: 4px;
    }

    .capa-row {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 8px;
        border-radius: 6px;
        cursor: pointer;
        font-size: .8rem;
        color: #495057;
    }

    .capa-row:hover {
        background: #f0f4f8;
    }

    .capa-row.capa-activa {
        background: #e7f1ff;
        color: #0056b3;
    }

    .capa-icono {
        width: 18px;
        text-align: center;
        color: #8a94a6;
        flex-shrink: 0;
    }

    .capa-label {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .capa-botones {
        display: flex;
        gap: 2px;
        flex-shrink: 0;
    }

    .capa-btn {
        border: none;
        background: transparent;
        color: #8a94a6;
        padding: 3px 5px;
        border-radius: 4px;
        font-size: .72rem;
        line-height: 1;
    }

    .capa-btn:hover {
        background: #dde4ec;
        color: #1d2744;
    }
</style>

<div class="card">
    <div class="card-header">
        <h4><i class="fa-solid fa-file-pdf mr-2 text-danger"></i>Editor de PDF</h4>
        <small class="text-muted">
            Abre un PDF, agrega o borra texto e imágenes, y descarga el resultado. Todo se procesa en tu navegador —
            el archivo no se sube al servidor.
        </small>
    </div>

    <div class="card-body">

        <div class="alert alert-info small mb-3">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Haz clic sobre <strong>cualquier texto existente</strong> del PDF para editarlo, moverlo o redimensionarlo
            en el sitio (como en Acrobat). Para <strong>figuras o imágenes existentes</strong>, usa "Recortar/Mover":
            arrastra un rectángulo sobre la figura y quedará convertida en un objeto que puedes mover, agrandar o
            eliminar. Nota: un PDF no reacomoda el resto del texto al editar (no hay reflujo), y la detección de
            colores de fondo/tinta es automática pero aproximada — puedes ajustarla manualmente si hace falta.
        </div>

        <div id="pdfEditorToolbar">

            <input type="file" id="inputAbrirPdf" accept="application/pdf" class="d-none">
            <button type="button" class="btn btn-primary" id="btnAbrirPdf">
                <i class="fa-solid fa-folder-open mr-1"></i> Abrir PDF
            </button>

            <div class="divider"></div>

            <button type="button" class="btn btn-outline-secondary" id="btnTool-select" title="Seleccionar / mover / redimensionar">
                <i class="fa-solid fa-arrow-pointer"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnTool-text" title="Agregar texto">
                <i class="fa-solid fa-font"></i> Texto
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnNegrita" title="Negrita (texto seleccionado)">
                <i class="fa-solid fa-bold"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnCursiva" title="Cursiva (texto seleccionado)">
                <i class="fa-solid fa-italic"></i>
            </button>
            <input type="color" id="textColor" class="color-swatch-input" value="#000000" title="Color del texto seleccionado" disabled>
            <select id="textFontSize" class="form-control form-control-sm" style="width:auto;" title="Tamaño del texto seleccionado" disabled>
                <option value="">Tamaño</option>
                <option value="8">8</option>
                <option value="10">10</option>
                <option value="12">12</option>
                <option value="14">14</option>
                <option value="16">16</option>
                <option value="18">18</option>
                <option value="20">20</option>
                <option value="24">24</option>
                <option value="28">28</option>
                <option value="32">32</option>
                <option value="40">40</option>
                <option value="48">48</option>
            </select>
            <button type="button" class="btn btn-outline-secondary" id="btnTool-rect" title="Tapar / borrar texto (rectángulo)">
                <i class="fa-solid fa-eraser"></i> Borrar / Tapar
            </button>
            <input type="color" id="rectColor" class="color-swatch-input" value="#ffffff" title="Color del rectángulo">

            <button type="button" class="btn btn-outline-secondary" id="btnTool-extract" title="Recortar una figura/imagen existente para poder moverla o redimensionarla">
                <i class="fa-solid fa-crop-simple"></i> Recortar / Mover
            </button>

            <input type="file" id="inputAgregarImagen" accept="image/*" class="d-none">
            <button type="button" class="btn btn-outline-secondary" id="btnTool-image" title="Agregar imagen nueva">
                <i class="fa-solid fa-image"></i> Imagen
            </button>

            <div class="divider"></div>

            <button type="button" class="btn btn-outline-danger" id="btnEliminarSeleccion" title="Eliminar seleccionado (Supr)">
                <i class="fa-solid fa-trash"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnDeshacer" title="Deshacer">
                <i class="fa-solid fa-rotate-left"></i>
            </button>

            <div class="divider"></div>

            <button type="button" class="btn btn-outline-secondary" id="btnPagAnterior" title="Página anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span id="pagInfo" class="small text-muted">— / —</span>
            <button type="button" class="btn btn-outline-secondary" id="btnPagSiguiente" title="Página siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <select id="zoomSelect" class="form-control form-control-sm" style="width:auto;">
                <option value="1">100%</option>
                <option value="1.25" selected>125%</option>
                <option value="1.5">150%</option>
                <option value="2">200%</option>
                <option value="0.75">75%</option>
            </select>

            <div class="divider"></div>

            <button type="button" class="btn btn-outline-secondary" id="btnOcr" title="Reconocer texto en esta página (para PDFs escaneados / imágenes sin texto)" disabled>
                <i class="fa-solid fa-magnifying-glass mr-1"></i> OCR
            </button>
            <span id="ocrStatus" class="small text-muted"></span>

            <button type="button" class="btn btn-success ml-auto" id="btnDescargar" disabled>
                <i class="fa-solid fa-download mr-1"></i> Descargar PDF
            </button>
        </div>

        <div class="row no-gutters">
            <div class="col">
                <div id="pdfEditorStage">
                    <div id="pdfEditorEmpty">
                        <i class="fa-solid fa-file-arrow-up"></i>
                        Abre un PDF para comenzar a editar
                    </div>
                    <div id="pageWrapper" class="d-none">
                        <canvas id="pdfBaseCanvas"></canvas>
                        <canvas id="fabricCanvasEl"></canvas>
                    </div>
                </div>
            </div>

            <div id="panelCapas">
                <div class="panel-capas-header">
                    <i class="fa-solid fa-layer-group mr-1"></i> Capas de esta página
                </div>
                <div id="capasLista" class="panel-capas-lista">
                    <div class="text-muted small text-center py-3">Sin capas todavía</div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- PDF.js -->
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<!-- Fabric.js -->
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<!-- pdf-lib -->
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<!-- Tesseract.js (OCR) -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js"></script>

<script>
(function () {
    'use strict';

    pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

    const CUSTOM_PROPS = ['sourcePngDataUrl', 'isExistingPdfText', 'isOcrText', 'covered'];

    let pdfDoc          = null;   // documento pdf.js (para render en pantalla)
    let originalBytes    = null;  // Uint8Array original (para pdf-lib al exportar)
    let currentPage      = 1;
    let numPages         = 0;
    let renderScale      = 1.25;
    let fabricCanvas     = null;
    let currentTool      = 'select';
    const pagesState     = {};    // { numPagina: JSON de fabric }
    const undoStacks     = {};    // { numPagina: [ JSON, JSON, ... ] }
    let restoringHistory = false;
    let rectDrawing       = null;  // rectángulo/recorte en progreso (arrastrando)

    const $ = (id) => document.getElementById(id);

    /* ============================================================
       INICIALIZAR FABRIC CANVAS (una sola vez)
    ============================================================ */
    function initFabric() {
        fabricCanvas = new fabric.Canvas('fabricCanvasEl', {
            selection: true,
        });

        fabricCanvas.on('mouse:down', onCanvasMouseDown);
        fabricCanvas.on('mouse:move', onCanvasMouseMove);
        fabricCanvas.on('mouse:up', onCanvasMouseUp);

        fabricCanvas.on('object:added', pushHistory);
        fabricCanvas.on('object:modified', pushHistory);
        fabricCanvas.on('object:removed', pushHistory);

        fabricCanvas.on('selection:created', onSeleccionCambia);
        fabricCanvas.on('selection:updated', onSeleccionCambia);
        fabricCanvas.on('selection:cleared', sincronizarControlesTexto);

        document.addEventListener('keydown', function (e) {
            const activeTag = document.activeElement ? document.activeElement.tagName : '';
            if (activeTag === 'INPUT' || activeTag === 'TEXTAREA') return;
            if ((e.key === 'Delete' || e.key === 'Backspace') && fabricCanvas.getActiveObject()) {
                if (fabricCanvas.getActiveObject().isEditing) return; // editando texto
                e.preventDefault();
                eliminarSeleccion();
            }
        });
    }

    function pushHistory() {
        if (restoringHistory) return;
        if (!undoStacks[currentPage]) undoStacks[currentPage] = [];
        undoStacks[currentPage].push(JSON.stringify(fabricCanvas.toJSON(CUSTOM_PROPS)));
        if (undoStacks[currentPage].length > 30) undoStacks[currentPage].shift();
        actualizarPanelCapas();
    }

    function deshacer() {
        const stack = undoStacks[currentPage];
        if (!stack || stack.length < 2) return;
        stack.pop(); // estado actual
        const previo = stack[stack.length - 1];
        restoringHistory = true;
        fabricCanvas.loadFromJSON(previo, function () {
            fabricCanvas.renderAll();
            restoringHistory = false;
            actualizarPanelCapas();
        });
    }

    /* ============================================================
       PANEL DE CAPAS — orden real de dibujo (respetado al exportar)
    ============================================================ */
    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    function iconoCapa(obj) {
        if (obj.type === 'rect') return 'fa-square';
        if (obj.type === 'image') return 'fa-image';
        return 'fa-font';
    }

    function etiquetaCapa(obj) {
        if (obj.type === 'rect') return 'Rectángulo / tapado';
        if (obj.type === 'image') return 'Imagen';
        const t = String(obj.text || '').trim();
        return t ? (t.length > 26 ? t.slice(0, 26) + '…' : t) : 'Texto vacío';
    }

    /* Solo se listan objetos que el usuario agregó o editó — el texto
       original del PDF que nunca se tocó no es una "capa" manipulable. */
    function actualizarPanelCapas() {
        const cont = $('capasLista');
        if (!cont || !fabricCanvas) return;

        const objetos = fabricCanvas.getObjects()
            .filter((o) => !(o.isExistingPdfText && !o.covered));

        if (!objetos.length) {
            cont.innerHTML = '<div class="text-muted small text-center py-3">Sin capas todavía</div>';
            return;
        }

        const activo = fabricCanvas.getActiveObject();
        cont.innerHTML = '';

        // De adelante hacia atrás: la capa de más arriba (frente) primero.
        for (let i = objetos.length - 1; i >= 0; i--) {
            const obj = objetos[i];
            const row = document.createElement('div');
            row.className = 'capa-row' + (obj === activo ? ' capa-activa' : '');
            row.innerHTML =
                '<span class="capa-icono"><i class="fa-solid ' + iconoCapa(obj) + '"></i></span>' +
                '<span class="capa-label">' + escHtml(etiquetaCapa(obj)) + '</span>' +
                '<span class="capa-botones">' +
                    '<button type="button" class="capa-btn" data-accion="subir" title="Traer al frente"><i class="fa-solid fa-arrow-up"></i></button>' +
                    '<button type="button" class="capa-btn" data-accion="bajar" title="Enviar atrás"><i class="fa-solid fa-arrow-down"></i></button>' +
                    '<button type="button" class="capa-btn" data-accion="borrar" title="Eliminar"><i class="fa-solid fa-trash"></i></button>' +
                '</span>';

            row.addEventListener('click', (e) => {
                if (e.target.closest('.capa-btn')) return;
                fabricCanvas.setActiveObject(obj);
                fabricCanvas.requestRenderAll();
                sincronizarControlesTexto();
                actualizarPanelCapas();
            });
            row.querySelector('[data-accion="subir"]').addEventListener('click', (e) => {
                e.stopPropagation();
                moverCapa(obj, true);
            });
            row.querySelector('[data-accion="bajar"]').addEventListener('click', (e) => {
                e.stopPropagation();
                moverCapa(obj, false);
            });
            row.querySelector('[data-accion="borrar"]').addEventListener('click', (e) => {
                e.stopPropagation();
                fabricCanvas.remove(obj);
                fabricCanvas.discardActiveObject();
                fabricCanvas.requestRenderAll();
                actualizarPanelCapas();
            });

            cont.appendChild(row);
        }
    }

    /* Sube/baja un objeto respecto de las demás CAPAS VISIBLES (no del
       arreglo interno completo de Fabric, que incluye cientos de
       "hit-targets" invisibles del texto original del PDF). Con
       bringForward/sendBackwards de Fabric, un solo paso podía quedar
       "atrapado" detrás de esos objetos invisibles y no verse ningún
       cambio — por eso se salta directo al vecino visible más cercano. */
    function moverCapa(obj, subir) {
        const todos = fabricCanvas.getObjects();
        const visibles = todos.filter((o) => !(o.isExistingPdfText && !o.covered));
        const posVisible = visibles.indexOf(obj);
        if (posVisible === -1) return;

        if (subir) {
            if (posVisible >= visibles.length - 1) return; // ya es la capa más alta
            const vecino = visibles[posVisible + 1];
            fabricCanvas.moveTo(obj, todos.indexOf(vecino));
        } else {
            if (posVisible <= 0) return; // ya es la capa más baja
            const vecino = visibles[posVisible - 1];
            fabricCanvas.moveTo(obj, todos.indexOf(vecino));
        }

        // Fabric dibuja el objeto seleccionado siempre al frente mientras
        // esté activo (para que sus controles queden accesibles), aunque
        // su orden real ya haya cambiado. Se deselecciona para que el
        // cambio de orden se vea de inmediato.
        fabricCanvas.discardActiveObject();
        fabricCanvas.requestRenderAll();
        pushHistory();
    }

    function eliminarSeleccion() {
        const activos = fabricCanvas.getActiveObjects();
        if (!activos.length) return;
        activos.forEach((obj) => fabricCanvas.remove(obj));
        fabricCanvas.discardActiveObject();
        fabricCanvas.requestRenderAll();
    }

    /* ============================================================
       TEXTO ORIGINAL DEL PDF — click para editar como en Acrobat
    ============================================================ */

    /* Crea, por cada fragmento de texto real detectado por PDF.js, un
       "hit-target" invisible del mismo tamaño/posición. Al seleccionarlo
       se cubre con el color de fondo detectado y se revela como texto
       editable, movible y redimensionable. */
    async function agregarCapaTextoOriginal(page, pageHeightPt) {
        const textContent = await page.getTextContent();

        textContent.items.forEach((item) => {
            const str = item.str;
            if (!str || !str.trim()) return;

            const tx = item.transform;
            // Se omiten fragmentos rotados/inclinados (no soportado en este MVP)
            if (Math.abs(tx[1]) > 0.05 || Math.abs(tx[2]) > 0.05) return;

            const fontSize = Math.hypot(tx[0], tx[1]) || item.height || 10;
            const left   = tx[4];
            const top    = pageHeightPt - tx[5] - item.height;

            // El nombre CSS genérico de textContent.styles (p.ej. "sans-serif")
            // no distingue negrita/cursiva. page.commonObjs sí expone el
            // nombre real de la fuente (p.ej. "Helvetica-Bold") y, en la
            // mayoría de casos, los flags .bold/.italic ya calculados por
            // PDF.js a partir del descriptor de la fuente.
            let esNegrita = false;
            let esCursiva = false;
            try {
                const fontObj = page.commonObjs.get(item.fontName);
                const nombreFuente = (fontObj && fontObj.name) || '';
                esNegrita = !!(fontObj && fontObj.bold) || /bold/i.test(nombreFuente);
                esCursiva = !!(fontObj && fontObj.italic) || /italic|oblique/i.test(nombreFuente);
            } catch (e) {
                // Fuente aún no resuelta u objeto no disponible: se deja sin negrita/cursiva.
            }

            // IText (no Textbox): un fragmento de texto real del PDF es una
            // sola línea; IText no reacomoda/reajusta ancho, así que no se
            // deforma ni se "infla" al mostrarse.
            const hit = new fabric.IText(str, {
                left, top,
                fontSize: Math.max(fontSize, 6),
                fontFamily: 'Helvetica, Arial, sans-serif',
                fontWeight: esNegrita ? 'bold' : 'normal',
                fontStyle: esCursiva ? 'italic' : 'normal',
                fill: 'rgba(0,0,0,0)',
                hasControls: false,
                hasRotatingPoint: false,
                lockScalingFlip: true,
                isExistingPdfText: true,
                covered: false,
            });
            hit.setControlsVisibility({ ml: false, mr: false, mt: false, mb: false, mtr: false });
            fabricCanvas.add(hit);
        });
    }

    function onSeleccionCambia(e) {
        const obj = e.selected && e.selected[0];
        if (obj && obj.isExistingPdfText && !obj.covered) {
            cubrirTextoOriginal(obj);
        }
        sincronizarControlesTexto();
    }

    /* ============================================================
       COLOR / TAMAÑO DEL TEXTO SELECCIONADO
    ============================================================ */
    function esObjetoDeTexto(obj) {
        return !!obj && (obj.type === 'i-text' || obj.type === 'textbox' || obj.type === 'text');
    }

    function sincronizarControlesTexto() {
        const obj = fabricCanvas.getActiveObject();
        const esTexto = esObjetoDeTexto(obj);

        $('textColor').disabled = !esTexto;
        $('textFontSize').disabled = !esTexto;

        if (esTexto) {
            const fill = String(obj.fill || '#000000');
            $('textColor').value = /^#[0-9a-f]{6}$/i.test(fill) ? fill : '#000000';
            $('textFontSize').value = String(Math.round(obj.fontSize || 14));
        }
    }

    function aplicarColorTexto(hex) {
        const obj = fabricCanvas.getActiveObject();
        if (!esObjetoDeTexto(obj)) return;
        obj.set('fill', hex);
        fabricCanvas.requestRenderAll();
    }

    function aplicarTamanoTexto(valor) {
        const obj = fabricCanvas.getActiveObject();
        if (!esObjetoDeTexto(obj) || !valor) return;
        obj.set('fontSize', parseInt(valor, 10));
        fabricCanvas.requestRenderAll();
        pushHistory();
    }

    /* Tapa el texto original detectado con un rectángulo del color de
       fondo muestreado, y revela la caja de texto para poder editarla,
       moverla o redimensionarla. */
    function cubrirTextoOriginal(obj) {
        const px = Math.round(obj.left * renderScale);
        const py = Math.round(obj.top * renderScale);
        const pw = Math.max(1, Math.round((obj.width || 10) * renderScale));
        const ph = Math.max(1, Math.round((obj.height || obj.fontSize || 10) * renderScale));

        // Fondo blanco por defecto: es el caso más común (documentos/facturas)
        // y evita parches grises cuando el muestreo automático cae sobre
        // tinta cercana. El usuario puede recolorear el rectángulo generado
        // seleccionándolo y usando el selector de color de la barra.
        const tinta = muestrearColorTinta(px, py, pw, ph);

        const cover = new fabric.Rect({
            left: obj.left, top: obj.top, width: obj.width || 10, height: obj.height || obj.fontSize || 10,
            fill: '#ffffff',
            hasRotatingPoint: false,
        });
        cover.setControlsVisibility({ mtr: false });

        restoringHistory = true;
        fabricCanvas.add(cover);
        const idxTexto = fabricCanvas.getObjects().indexOf(obj);
        fabricCanvas.moveTo(cover, Math.max(0, idxTexto));
        restoringHistory = false;

        obj.set({ fill: tinta, hasControls: true, covered: true });
        fabricCanvas.setActiveObject(obj);
        fabricCanvas.requestRenderAll();
        pushHistory();
    }

    function muestrearColorFondo(px, py, pw, ph) {
        const canvas = $('pdfBaseCanvas');
        const ctx = canvas.getContext('2d');
        const puntos = [
            [px + 2, Math.max(0, py - 3)],
            [px + pw - 2, Math.max(0, py - 3)],
            [px + Math.round(pw / 2), Math.min(canvas.height - 1, py + ph + 2)],
        ];
        let r = 0, g = 0, b = 0, n = 0;
        puntos.forEach(([x, y]) => {
            if (x < 0 || y < 0 || x >= canvas.width || y >= canvas.height) return;
            const d = ctx.getImageData(x, y, 1, 1).data;
            r += d[0]; g += d[1]; b += d[2]; n++;
        });
        if (!n) return '#ffffff';
        return rgbToHex(Math.round(r / n), Math.round(g / n), Math.round(b / n));
    }

    function muestrearColorTinta(px, py, pw, ph) {
        const canvas = $('pdfBaseCanvas');
        const ctx = canvas.getContext('2d');
        let mejor = { lum: 999, color: [0, 0, 0] };
        const pasos = 9;
        for (let i = 0; i < pasos; i++) {
            for (let j = 0; j < pasos; j++) {
                const x = px + Math.round((pw * (i + 0.5)) / pasos);
                const y = py + Math.round((ph * (j + 0.5)) / pasos);
                if (x < 0 || y < 0 || x >= canvas.width || y >= canvas.height) continue;
                const d = ctx.getImageData(x, y, 1, 1).data;
                const lum = 0.299 * d[0] + 0.587 * d[1] + 0.114 * d[2];
                if (lum < mejor.lum) mejor = { lum, color: [d[0], d[1], d[2]] };
            }
        }
        // El pixel de tinta más oscuro suele quedar algo "lavado" por el
        // anti-aliasing (gris en vez de negro puro). Si es oscuro y sin
        // tono de color marcado (gris neutro), se redondea a negro puro;
        // si tiene un tono real (rojo, azul, etc.) se respeta tal cual.
        const [r, g, b] = mejor.color;
        const esGrisNeutro = Math.max(r, g, b) - Math.min(r, g, b) < 18;
        if (mejor.lum < 110 && esGrisNeutro) return '#000000';
        return rgbToHex(r, g, b);
    }

    function rgbToHex(r, g, b) {
        return '#' + [r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('');
    }

    /* ============================================================
       RECORTAR / MOVER FIGURAS O IMÁGENES EXISTENTES
    ============================================================ */

    /* Convierte la región arrastrada en una imagen (recorte de los
       píxeles ya renderizados) que queda como objeto movible y
       redimensionable, y tapa el hueco original con el color de fondo. */
    function recortarFigura(left, top, width, height) {
        if (width < 3 || height < 3) return;

        const canvas = $('pdfBaseCanvas');
        const sx = Math.round(left * renderScale);
        const sy = Math.round(top * renderScale);
        const sw = Math.round(width * renderScale);
        const sh = Math.round(height * renderScale);

        const temp = document.createElement('canvas');
        temp.width = sw;
        temp.height = sh;
        temp.getContext('2d').drawImage(canvas, sx, sy, sw, sh, 0, 0, sw, sh);
        const dataUrl = temp.toDataURL('image/png');

        const bg = muestrearColorFondo(sx, sy, sw, sh);
        const cover = new fabric.Rect({
            left, top, width, height, fill: bg, hasRotatingPoint: false,
        });
        fabricCanvas.add(cover);

        fabric.Image.fromURL(dataUrl, function (img) {
            img.set({
                left, top,
                scaleX: width / img.width,
                scaleY: height / img.height,
                hasRotatingPoint: false,
                sourcePngDataUrl: dataUrl,
            });
            fabricCanvas.add(img);
            fabricCanvas.setActiveObject(img);
            setTool('select');
            fabricCanvas.requestRenderAll();
            pushHistory();
        });
    }

    /* ============================================================
       HERRAMIENTAS
    ============================================================ */
    function setTool(tool) {
        currentTool = tool;
        ['select', 'text', 'rect', 'extract', 'image'].forEach((t) => {
            const btn = $('btnTool-' + t);
            if (btn) btn.classList.toggle('tool-active', t === tool);
        });
        fabricCanvas.isDrawingMode = false;
        fabricCanvas.selection = (tool === 'select');
        fabricCanvas.forEachObject((o) => (o.selectable = (tool === 'select')));
        fabricCanvas.defaultCursor = (tool === 'select') ? 'default' : 'crosshair';

        // Al entrar a una herramienta de creación se limpia la selección
        // previa; si no, un objeto anterior seguía "activo" y el selector
        // de color de #rectColor terminaba recoloreándolo a él en vez de
        // aplicar el color solo a la figura nueva que se va a dibujar.
        if (tool !== 'select') {
            fabricCanvas.discardActiveObject();
            fabricCanvas.requestRenderAll();
        }
    }

    function onCanvasMouseDown(opt) {
        if (!fabricCanvas) return;
        const pointer = fabricCanvas.getPointer(opt.e);

        if (currentTool === 'text') {
            const texto = new fabric.Textbox('Escribe aquí', {
                left: pointer.x,
                top: pointer.y,
                fontSize: 14,
                fill: '#000000',
                fontFamily: 'Helvetica, Arial, sans-serif',
                width: 200,
                hasRotatingPoint: false,
            });
            fabricCanvas.add(texto);
            fabricCanvas.setActiveObject(texto);
            setTool('select');
            fabricCanvas.requestRenderAll();
            setTimeout(() => {
                texto.enterEditing();
                texto.selectAll();
            }, 0);
            return;
        }

        if (currentTool === 'rect' || currentTool === 'extract') {
            rectDrawing = new fabric.Rect({
                left: pointer.x,
                top: pointer.y,
                width: 1,
                height: 1,
                fill: currentTool === 'rect' ? $('rectColor').value : 'rgba(0, 123, 255, 0.25)',
                stroke: currentTool === 'extract' ? '#007bff' : undefined,
                strokeDashArray: currentTool === 'extract' ? [5, 4] : undefined,
                selectable: false,
                hasRotatingPoint: false,
            });
            fabricCanvas.add(rectDrawing);
            rectDrawing._startX = pointer.x;
            rectDrawing._startY = pointer.y;
        }
    }

    function onCanvasMouseMove(opt) {
        if ((currentTool !== 'rect' && currentTool !== 'extract') || !rectDrawing) return;
        const pointer = fabricCanvas.getPointer(opt.e);
        const left = Math.min(pointer.x, rectDrawing._startX);
        const top  = Math.min(pointer.y, rectDrawing._startY);
        const width  = Math.abs(pointer.x - rectDrawing._startX);
        const height = Math.abs(pointer.y - rectDrawing._startY);
        rectDrawing.set({ left, top, width, height });
        fabricCanvas.requestRenderAll();
    }

    function onCanvasMouseUp() {
        if ((currentTool !== 'rect' && currentTool !== 'extract') || !rectDrawing) return;

        const { left, top, width, height } = rectDrawing;
        const esRecorte = currentTool === 'extract';
        fabricCanvas.remove(rectDrawing);
        rectDrawing = null;

        if (esRecorte) {
            recortarFigura(left, top, width, height);
            return;
        }

        const finalRect = new fabric.Rect({
            left, top, width, height,
            fill: $('rectColor').value,
            hasRotatingPoint: false,
        });
        fabricCanvas.add(finalRect);
        setTool('select');
        fabricCanvas.setActiveObject(finalRect);
        pushHistory();
    }

    /* Normaliza cualquier imagen (jpg/png/webp/gif) a PNG dataURL */
    function normalizarImagenAPng(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    const c = document.createElement('canvas');
                    c.width = img.naturalWidth;
                    c.height = img.naturalHeight;
                    c.getContext('2d').drawImage(img, 0, 0);
                    resolve(c.toDataURL('image/png'));
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    async function agregarImagen(file) {
        const pngDataUrl = await normalizarImagenAPng(file);
        fabric.Image.fromURL(pngDataUrl, function (img) {
            const maxWidthPt = 220;
            const scale = img.width > maxWidthPt ? (maxWidthPt / img.width) : 1;
            img.set({
                left: 60,
                top: 60,
                scaleX: scale,
                scaleY: scale,
                hasRotatingPoint: false,
                sourcePngDataUrl: pngDataUrl,
            });
            fabricCanvas.add(img);
            fabricCanvas.setActiveObject(img);
            setTool('select');
            fabricCanvas.requestRenderAll();
        });
    }

    /* ============================================================
       NEGRITA / CURSIVA (sobre el objeto de texto seleccionado)
    ============================================================ */
    function alternarNegrita() {
        const obj = fabricCanvas.getActiveObject();
        if (!obj || (obj.type !== 'i-text' && obj.type !== 'textbox' && obj.type !== 'text')) return;
        const activo = obj.fontWeight === 'bold';
        obj.set('fontWeight', activo ? 'normal' : 'bold');
        fabricCanvas.requestRenderAll();
        pushHistory();
    }

    function alternarCursiva() {
        const obj = fabricCanvas.getActiveObject();
        if (!obj || (obj.type !== 'i-text' && obj.type !== 'textbox' && obj.type !== 'text')) return;
        const activo = obj.fontStyle === 'italic';
        obj.set('fontStyle', activo ? 'normal' : 'italic');
        fabricCanvas.requestRenderAll();
        pushHistory();
    }

    /* ============================================================
       OCR (PDFs escaneados / páginas sin capa de texto real)
    ============================================================ */
    let ocrWorkerPromise = null;

    function getOcrWorker() {
        if (!ocrWorkerPromise) {
            ocrWorkerPromise = Tesseract.createWorker('spa', 1, {
                logger: (m) => {
                    if (m.status && typeof m.progress === 'number') {
                        $('ocrStatus').textContent = `OCR: ${m.status} ${Math.round(m.progress * 100)}%`;
                    }
                },
            });
        }
        return ocrWorkerPromise;
    }

    async function ejecutarOcr() {
        if (!pdfDoc) return;

        const btn = $('btnOcr');
        const statusEl = $('ocrStatus');
        btn.disabled = true;
        statusEl.textContent = 'Cargando motor de OCR...';

        try {
            const worker = await getOcrWorker();
            statusEl.textContent = 'Reconociendo texto de la página...';

            const { data } = await worker.recognize($('pdfBaseCanvas'));
            let agregadas = 0;

            (data.words || []).forEach((w) => {
                const texto = (w.text || '').trim();
                if (!texto || (typeof w.confidence === 'number' && w.confidence < 40)) return;

                const { x0, y0, y1 } = w.bbox;
                const left   = x0 / renderScale;
                const top    = y0 / renderScale;
                const height = (y1 - y0) / renderScale;
                const fontSize = Math.max(height * 0.85, 6);

                const hit = new fabric.IText(texto, {
                    left, top,
                    fontSize,
                    fontFamily: 'Helvetica, Arial, sans-serif',
                    fill: 'rgba(0,0,0,0)',
                    hasControls: false,
                    hasRotatingPoint: false,
                    lockScalingFlip: true,
                    isExistingPdfText: true,
                    isOcrText: true,
                    covered: false,
                });
                hit.setControlsVisibility({ ml: false, mr: false, mt: false, mb: false, mtr: false });
                fabricCanvas.add(hit);
                agregadas++;
            });

            pushHistory();
            statusEl.textContent = `OCR completado: ${agregadas} palabra(s) detectada(s) y ahora editables.`;
        } catch (err) {
            console.error('Error de OCR:', err);
            statusEl.textContent = 'Error al ejecutar OCR. Ver consola.';
        } finally {
            btn.disabled = false;
            setTimeout(() => { statusEl.textContent = ''; }, 6000);
        }
    }

    /* ============================================================
       ABRIR PDF
    ============================================================ */
    async function abrirPdf(file) {
        const buffer = await file.arrayBuffer();
        originalBytes = new Uint8Array(buffer);

        pdfDoc = await pdfjsLib.getDocument({ data: originalBytes.slice() }).promise;
        numPages = pdfDoc.numPages;
        currentPage = 1;

        Object.keys(pagesState).forEach((k) => delete pagesState[k]);
        Object.keys(undoStacks).forEach((k) => delete undoStacks[k]);

        $('pdfEditorEmpty').classList.add('d-none');
        $('pageWrapper').classList.remove('d-none');
        $('btnDescargar').disabled = false;
        $('btnOcr').disabled = false;

        await renderPage(currentPage);
    }

    async function renderPage(num) {
        if (!pdfDoc) return;

        // Guardar estado de la página que se está dejando
        if (fabricCanvas && pagesState.__currentRendered) {
            pagesState[pagesState.__currentRendered] = fabricCanvas.toJSON(CUSTOM_PROPS);
        }

        const page = await pdfDoc.getPage(num);
        const viewport = page.getViewport({ scale: renderScale });

        const baseCanvas = $('pdfBaseCanvas');
        baseCanvas.width = viewport.width;
        baseCanvas.height = viewport.height;

        await page.render({ canvasContext: baseCanvas.getContext('2d'), viewport }).promise;

        $('pageWrapper').style.width = viewport.width + 'px';
        $('pageWrapper').style.height = viewport.height + 'px';

        // El canvas de Fabric ocupa el tamaño físico en píxeles de la página
        // renderizada, pero mantenemos las coordenadas de los objetos en
        // "puntos PDF" (espacio lógico) aplicando el zoom vía setZoom().
        fabricCanvas.setDimensions({ width: viewport.width, height: viewport.height });
        fabricCanvas.setZoom(renderScale);

        restoringHistory = true;
        if (pagesState[num]) {
            fabricCanvas.loadFromJSON(pagesState[num], function () {
                fabricCanvas.renderAll();
                restoringHistory = false;
                if (!undoStacks[num]) pushHistory();
                actualizarPanelCapas();
            });
        } else {
            fabricCanvas.clear();
            const pageHeightPt = page.getViewport({ scale: 1 }).height;
            await agregarCapaTextoOriginal(page, pageHeightPt);
            restoringHistory = false;
            undoStacks[num] = [];
            pushHistory();
        }

        currentPage = num;
        pagesState.__currentRendered = num;
        $('pagInfo').textContent = num + ' / ' + numPages;
        setTool('select');
    }

    /* ============================================================
       EXPORTAR / DESCARGAR PDF
    ============================================================ */
    function fabricPointToPdf(obj, pageHeightPt) {
        const w = obj.width * (obj.scaleX || 1);
        const h = obj.height * (obj.scaleY || 1);
        return {
            x: obj.left,
            yTop: obj.top,
            width: w,
            height: h,
            yPdf: pageHeightPt - obj.top - h,
        };
    }

    async function descargarPdf() {
        if (!originalBytes) return;

        // Guardar la página visible antes de exportar
        if (pagesState.__currentRendered) {
            pagesState[pagesState.__currentRendered] = fabricCanvas.toJSON(CUSTOM_PROPS);
        }

        const { PDFDocument, StandardFonts, rgb } = PDFLib;
        const pdfLibDoc = await PDFDocument.load(originalBytes.slice());
        const fuentes = {
            normal: await pdfLibDoc.embedFont(StandardFonts.Helvetica),
            bold: await pdfLibDoc.embedFont(StandardFonts.HelveticaBold),
            italic: await pdfLibDoc.embedFont(StandardFonts.HelveticaOblique),
            boldItalic: await pdfLibDoc.embedFont(StandardFonts.HelveticaBoldOblique),
        };
        const imageCache = new Map();

        const pages = pdfLibDoc.getPages();

        for (let i = 1; i <= numPages; i++) {
            const stateJson = pagesState[i];
            if (!stateJson) continue;

            const state = typeof stateJson === 'string' ? JSON.parse(stateJson) : stateJson;
            if (!state.objects || !state.objects.length) continue;

            const page = pages[i - 1];
            const { height: pageHeightPt } = page.getSize();

            for (const obj of state.objects) {
                // Texto original del PDF que el usuario nunca tocó: ya está
                // en el documento base, no se vuelve a dibujar.
                if (obj.isExistingPdfText && !obj.covered) continue;

                const geo = fabricPointToPdf(obj, pageHeightPt);

                if (obj.type === 'rect') {
                    const color = hexToRgb(obj.fill || '#ffffff');
                    page.drawRectangle({
                        x: geo.x,
                        y: geo.yPdf,
                        width: geo.width,
                        height: geo.height,
                        color: rgb(color.r, color.g, color.b),
                    });
                } else if (obj.type === 'textbox' || obj.type === 'text' || obj.type === 'i-text') {
                    const fontSize = (obj.fontSize || 14) * (obj.scaleY || 1);
                    const color = hexToRgb(obj.fill || '#000000');
                    const negrita = obj.fontWeight === 'bold' || obj.fontWeight >= 600;
                    const cursiva = obj.fontStyle === 'italic' || obj.fontStyle === 'oblique';
                    const fuente = negrita && cursiva ? fuentes.boldItalic
                        : negrita ? fuentes.bold
                        : cursiva ? fuentes.italic
                        : fuentes.normal;
                    // El re-wrap solo aplica a los cuadros de texto multilínea
                    // (Textbox) que el usuario agrega. El texto real del PDF
                    // (i-text) es siempre una sola línea: recalcular su ancho
                    // con las métricas de fuente del navegador (distintas a
                    // las del PDF original) podía partirlo en líneas de más.
                    const lineas = obj.type === 'textbox'
                        ? obtenerLineasEnvueltas(obj)
                        : String(obj.text || '').split('\n');
                    let cursorY = pageHeightPt - obj.top - fontSize;
                    for (const linea of lineas) {
                        page.drawText(linea, {
                            x: obj.left,
                            y: cursorY,
                            size: fontSize,
                            font: fuente,
                            color: rgb(color.r, color.g, color.b),
                        });
                        cursorY -= fontSize * 1.15;
                    }
                } else if (obj.type === 'image' && obj.sourcePngDataUrl) {
                    let embedded = imageCache.get(obj.sourcePngDataUrl);
                    if (!embedded) {
                        const pngBytes = dataUrlToBytes(obj.sourcePngDataUrl);
                        embedded = await pdfLibDoc.embedPng(pngBytes);
                        imageCache.set(obj.sourcePngDataUrl, embedded);
                    }
                    page.drawImage(embedded, {
                        x: geo.x,
                        y: geo.yPdf,
                        width: geo.width,
                        height: geo.height,
                    });
                }
            }
        }

        const outBytes = await pdfLibDoc.save();
        const blob = new Blob([outBytes], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'documento_editado.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 5000);
    }

    /* Reconstruye el textbox (sin canvas) para obtener el mismo salto de
       línea automático que se ve en pantalla, en lugar del texto crudo. */
    function obtenerLineasEnvueltas(obj) {
        try {
            const temp = new fabric.Textbox(obj.text || '', obj);
            const raw = temp.textLines || temp._textLines;
            if (raw && raw.length) {
                return raw.map((l) => (Array.isArray(l) ? l.join('') : l));
            }
        } catch (e) {
            // ignorar y usar respaldo
        }
        return String(obj.text || '').split('\n');
    }

    function hexToRgb(hex) {
        hex = (hex || '#000000').replace('#', '');
        if (hex.length === 3) hex = hex.split('').map((c) => c + c).join('');
        const num = parseInt(hex, 16) || 0;
        return {
            r: ((num >> 16) & 255) / 255,
            g: ((num >> 8) & 255) / 255,
            b: (num & 255) / 255,
        };
    }

    function dataUrlToBytes(dataUrl) {
        const base64 = dataUrl.split(',')[1];
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
        return bytes;
    }

    /* ============================================================
       EVENTOS DE LA BARRA DE HERRAMIENTAS
    ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        initFabric();

        $('btnAbrirPdf').addEventListener('click', () => $('inputAbrirPdf').click());
        $('inputAbrirPdf').addEventListener('change', function (e) {
            if (e.target.files[0]) abrirPdf(e.target.files[0]);
            e.target.value = '';
        });

        $('btnTool-select').addEventListener('click', () => setTool('select'));
        $('btnTool-text').addEventListener('click', () => setTool('text'));
        $('btnTool-rect').addEventListener('click', () => setTool('rect'));
        $('btnTool-extract').addEventListener('click', () => setTool('extract'));
        $('btnTool-image').addEventListener('click', () => $('inputAgregarImagen').click());
        $('inputAgregarImagen').addEventListener('change', function (e) {
            if (e.target.files[0]) agregarImagen(e.target.files[0]);
            e.target.value = '';
        });

        $('btnEliminarSeleccion').addEventListener('click', eliminarSeleccion);
        $('btnDeshacer').addEventListener('click', deshacer);
        $('btnNegrita').addEventListener('click', alternarNegrita);
        $('btnCursiva').addEventListener('click', alternarCursiva);
        $('btnOcr').addEventListener('click', ejecutarOcr);

        $('textColor').addEventListener('input', function () { aplicarColorTexto(this.value); });
        $('textColor').addEventListener('change', function () {
            if (esObjetoDeTexto(fabricCanvas.getActiveObject())) pushHistory();
        });
        $('textFontSize').addEventListener('change', function () { aplicarTamanoTexto(this.value); });

        $('btnPagAnterior').addEventListener('click', () => {
            if (currentPage > 1) renderPage(currentPage - 1);
        });
        $('btnPagSiguiente').addEventListener('click', () => {
            if (currentPage < numPages) renderPage(currentPage + 1);
        });

        $('zoomSelect').addEventListener('change', function () {
            renderScale = parseFloat(this.value);
            if (pdfDoc) renderPage(currentPage);
        });

        $('btnDescargar').addEventListener('click', descargarPdf);

        // Si hay un rectángulo (tapado/redacción) seleccionado, el selector
        // de color lo recolorea en vivo — útil para corregir un color de
        // fondo detectado incorrectamente.
        $('rectColor').addEventListener('input', function () {
            const obj = fabricCanvas.getActiveObject();
            if (obj && obj.type === 'rect') {
                obj.set('fill', this.value);
                fabricCanvas.requestRenderAll();
            }
        });
        $('rectColor').addEventListener('change', function () {
            const obj = fabricCanvas.getActiveObject();
            if (obj && obj.type === 'rect') pushHistory();
        });
    });
})();
</script>

<?= $this->endSection() ?>
