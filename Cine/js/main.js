/**
 * Archivo JavaScript principal para CINE-ONLINE
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes de la interfaz
    inicializarCarousel();
    inicializarMenuMovil();
    inicializarValidacionFormularios();
    inicializarModales();
    
    // Agregar eventos adicionales según sea necesario
    agregarEventosGlobales();
});

/**
 * Inicializa el carousel de películas destacadas
 */
function inicializarCarousel() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.slide');
    const prevBtn = carousel.querySelector('.prev-btn');
    const nextBtn = carousel.querySelector('.next-btn');
    const dots = carousel.querySelectorAll('.dot');
    
    let currentSlide = 0;
    const slideCount = slides.length;
    
    // Función para mostrar un slide específico
    function showSlide(index) {
        if (index < 0) index = slideCount - 1;
        if (index >= slideCount) index = 0;
        
        // Ocultar todos los slides y mostrar solo el actual
        slides.forEach(slide => slide.style.display = 'none');
        slides[index].style.display = 'block';
        
        // Actualizar indicadores
        dots.forEach(dot => dot.classList.remove('active'));
        dots[index].classList.add('active');
        
        // Actualizar índice del slide actual
        currentSlide = index;
    }
    
    // Configurar botones de navegación
    if (prevBtn && nextBtn) {
        prevBtn.addEventListener('click', () => {
            showSlide(currentSlide - 1);
        });
        
        nextBtn.addEventListener('click', () => {
            showSlide(currentSlide + 1);
        });
    }
    
    // Configurar indicadores (dots)
    if (dots.length > 0) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
            });
        });
    }
    
    // Auto rotación del carousel cada 5 segundos
    setInterval(() => {
        showSlide(currentSlide + 1);
    }, 5000);
    
    // Mostrar el primer slide
    showSlide(0);
}

/**
 * Inicializa el menú móvil para dispositivos con pantallas pequeñas
 */
function inicializarMenuMovil() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (!menuToggle || !navMenu) return;
    
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });
    
    // Cerrar menú al hacer clic en un enlace
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });
}

/**
 * Inicializa la validación de formularios
 */
function inicializarValidacionFormularios() {
    // Formulario de registro
    const formRegistro = document.getElementById('form-registro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');
            let isValid = true;
            
            // Validar nombre
            if (nombre.value.trim() === '') {
                mostrarError(nombre, 'El nombre es obligatorio');
                isValid = false;
            } else {
                limpiarError(nombre);
            }
            
            // Validar email
            if (email.value.trim() === '') {
                mostrarError(email, 'El email es obligatorio');
                isValid = false;
            } else if (!validarEmail(email.value)) {
                mostrarError(email, 'Ingresa un email válido');
                isValid = false;
            } else {
                limpiarError(email);
            }
            
            // Validar contraseña
            if (password.value === '') {
                mostrarError(password, 'La contraseña es obligatoria');
                isValid = false;
            } else if (password.value.length < 6) {
                mostrarError(password, 'La contraseña debe tener al menos 6 caracteres');
                isValid = false;
            } else {
                limpiarError(password);
            }
            
            // Confirmar contraseña
            if (confirmPassword.value === '') {
                mostrarError(confirmPassword, 'Confirma tu contraseña');
                isValid = false;
            } else if (confirmPassword.value !== password.value) {
                mostrarError(confirmPassword, 'Las contraseñas no coinciden');
                isValid = false;
            } else {
                limpiarError(confirmPassword);
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Formulario de inicio de sesión
    const formLogin = document.getElementById('form-login');
    if (formLogin) {
        formLogin.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid = true;
            
            // Validar email
            if (email.value.trim() === '') {
                mostrarError(email, 'El email es obligatorio');
                isValid = false;
            } else if (!validarEmail(email.value)) {
                mostrarError(email, 'Ingresa un email válido');
                isValid = false;
            } else {
                limpiarError(email);
            }
            
            // Validar contraseña
            if (password.value === '') {
                mostrarError(password, 'La contraseña es obligatoria');
                isValid = false;
            } else {
                limpiarError(password);
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Inicializa las ventanas modales
 */
function inicializarModales() {
    const modales = document.querySelectorAll('.modal');
    const abrirModal = document.querySelectorAll('[data-modal]');
    const cerrarModal = document.querySelectorAll('.cerrar-modal');
    
    // Abrir modal
    abrirModal.forEach(boton => {
        boton.addEventListener('click', () => {
            const modalId = boton.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('mostrar');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Cerrar modal con botón X
    cerrarModal.forEach(boton => {
        boton.addEventListener('click', () => {
            const modal = boton.closest('.modal');
            if (modal) {
                modal.classList.remove('mostrar');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Cerrar modal haciendo clic fuera de él
    modales.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('mostrar');
                document.body.style.overflow = 'auto';
            }
        });
    });
}

/**
 * Agrega eventos globales adicionales
 */
function agregarEventosGlobales() {
    // Filtro de películas por género
    const filtroGenero = document.getElementById('filtro-genero');
    if (filtroGenero) {
        filtroGenero.addEventListener('change', () => {
            const genero = filtroGenero.value;
            const url = new URL(window.location.href);
            
            if (genero) {
                url.searchParams.set('genero', genero);
            } else {
                url.searchParams.delete('genero');
            }
            
            window.location.href = url.toString();
        });
    }
    
    // Búsqueda de películas
    const formBusqueda = document.getElementById('form-busqueda');
    if (formBusqueda) {
        formBusqueda.addEventListener('submit', (e) => {
            const busqueda = document.getElementById('busqueda').value.trim();
            if (busqueda === '') {
                e.preventDefault();
            }
        });
    }
}

/**
 * Muestra un mensaje de error para un campo del formulario
 */
function mostrarError(input, mensaje) {
    const formControl = input.parentElement;
    const errorElement = formControl.querySelector('.mensaje-error') || document.createElement('small');
    
    if (!formControl.querySelector('.mensaje-error')) {
        errorElement.className = 'mensaje-error';
        formControl.appendChild(errorElement);
    }
    
    input.classList.add('error');
    errorElement.textContent = mensaje;
}

/**
 * Limpia el mensaje de error de un campo del formulario
 */
function limpiarError(input) {
    const formControl = input.parentElement;
    const errorElement = formControl.querySelector('.mensaje-error');
    
    input.classList.remove('error');
    if (errorElement) {
        errorElement.textContent = '';
    }
}

/**
 * Valida un email
 */
function validarEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}