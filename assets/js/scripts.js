document.addEventListener('DOMContentLoaded', () => {
    const adminLink = document.getElementById('admin-link');

    // Simulación de sesión para mostrar u ocultar enlaces.
    const userRole = sessionStorage.getItem('role'); // Cambiar esto a una validación real en tu app

    if (userRole === 'admin') {
        adminLink.style.display = 'inline';
    } else {
        adminLink.style.display = 'none';
    }
});
