/**
 * Archivo JavaScript para la funcionalidad de reservas de asientos
 */

// Variables globales
let asientosSeleccionados = [];
let precioUnitario = 0;

/**
 * Inicializa la selección de asientos
 * @param {number} capacidadSala - Capacidad total de la sala
 * @param {Array} asientosReservados - Array con los asientos ya reservados
 * @param {number} precio - Precio por asiento
 */
function inicializarSeleccionAsientos(capacidadSala, asientosReservados, precio) {
    // Establecer el precio unitario
    precioUnitario = precio;
    
    // Obtener el contenedor de asientos
    const contenedorAsientos = document.getElementById('sala-asientos');
    if (!contenedorAsientos) return;
    
    // Vaciar el contenedor de asientos
    contenedorAsientos.innerHTML = '';
    
    // Calcular el número de filas y columnas
    const columnasPorFila = Math.ceil(Math.sqrt(capacidadSala));
    const numFilas = Math.ceil(capacidadSala / columnasPorFila);
    
    // Letras para las filas
    const letrasFilas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    // Generar los asientos
    let asientoId = 1;
    for (let i = 0; i < numFilas && asientoId <= capacidadSala; i++) {
        // Crear fila
        const fila = document.createElement('div');
        fila.className = 'fila';
        
        // Añadir etiqueta de fila
        const etiquetaFila = document.createElement('div');
        etiquetaFila.className = 'etiqueta-fila';
        etiquetaFila.textContent = letrasFilas[i];
        fila.appendChild(etiquetaFila);
        
        // Crear asientos de la fila
        for (let j = 0; j < columnasPorFila && asientoId <= capacidadSala; j++) {
            const asientoCode = `${letrasFilas[i]}${j+1}`;
            
            // Crear asiento
            const asiento = document.createElement('div');
            asiento.className = 'asiento';
            asiento.dataset.id = asientoCode;
            asiento.textContent = j + 1;
            
            // Verificar si el asiento está reservado
            if (asientosReservados.includes(asientoCode)) {
                asiento.classList.add('reservado');
            } else {
                // Añadir evento de clic para asientos disponibles
                asiento.addEventListener('click', toggleSeleccionAsiento);
            }
            
            // Añadir asiento a la fila
            fila.appendChild(asiento);
            asientoId++;
        }
        
        // Añadir fila al contenedor
        contenedorAsientos.appendChild(fila);
    }
    
    // Inicializar campos del formulario
    actualizarResumen();
}

/**
 * Maneja la selección/deselección de un asiento
 */
function toggleSeleccionAsiento(e) {
    const asiento = e.target;
    const asientoId = asiento.dataset.id;
    
    // Verificar si el asiento está reservado
    if (asiento.classList.contains('reservado')) {
        return;
    }
    
    // Alternar selección
    if (asiento.classList.contains('seleccionado')) {
        // Quitar asiento de la selección
        asiento.classList.remove('seleccionado');
        asientosSeleccionados = asientosSeleccionados.filter(id => id !== asientoId);
    } else {
        // Añadir asiento a la selección
        asiento.classList.add('seleccionado');
        asientosSeleccionados.push(asientoId);
    }
    
    // Actualizar resumen
    actualizarResumen();
}

/**
 * Actualiza el resumen de la reserva
 */
function actualizarResumen() {
    const asientosTexto = document.getElementById('asientos-seleccionados');
    const totalTexto = document.getElementById('total-pagar');
    const inputAsientos = document.getElementById('input-asientos');
    const inputTotal = document.getElementById('input-total');
    const btnConfirmar = document.getElementById('btn-confirmar');
    
    // Ordenar asientos seleccionados para mejor visualización
    const asientosOrdenados = [...asientosSeleccionados].sort();
    
    // Actualizar texto de asientos seleccionados
    asientosTexto.textContent = asientosOrdenados.length > 0 ? asientosOrdenados.join(', ') : 'Ninguno';
    
    // Calcular y actualizar total
    const total = asientosOrdenados.length * precioUnitario;
    totalTexto.textContent = total.toFixed(2);
    
    // Actualizar campos ocultos del formulario
    inputAsientos.value = asientosOrdenados.join(',');
    inputTotal.value = total.toFixed(2);
    
    // Habilitar/deshabilitar botón de confirmación
    btnConfirmar.disabled = asientosOrdenados.length === 0;
}

/**
 * Inicializa la visualización de una reserva existente
 * @param {Array} asientosReservados - Array con los asientos reservados
 */
function inicializarVisualizacionReserva(asientosReservados) {
    // Obtener el contenedor de asientos
    const contenedorAsientos = document.getElementById('sala-asientos-reserva');
    if (!contenedorAsientos) return;
    
    // Convertir string a array si es necesario
    if (typeof asientosReservados === 'string') {
        asientosReservados = asientosReservados.split(',');
    }
    
    // Resaltar los asientos reservados
    const asientos = contenedorAsientos.querySelectorAll('.asiento');
    asientos.forEach(asiento => {
        const asientoId = asiento.dataset.id;
        if (asientosReservados.includes(asientoId)) {
            asiento.classList.add('reservado');
        }
    });
}

/**
 * Validación del formulario de reserva
 */
function validarFormularioReserva() {
    const form = document.getElementById('form-reserva');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        // Verificar que se hayan seleccionado asientos
        if (asientosSeleccionados.length === 0) {
            alert('Por favor, selecciona al menos un asiento.');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
}