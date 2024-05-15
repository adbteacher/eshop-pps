// Guardar la posición de desplazamiento antes de enviar el formulario
function saveScrollPosition() {
    localStorage.setItem('scrollPosition', window.scrollY);
}

// Restaurar la posición de desplazamiento inmediatamente después de que la página
window.onload = function() {
    if (localStorage.getItem('scrollPosition')) {
        window.scrollTo(0, parseInt(localStorage.getItem('scrollPosition'), 10));
        localStorage.removeItem('scrollPosition');
    }
};