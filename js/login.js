// En tu código de login cuando el status sea success:
localStorage.setItem('user_session', JSON.stringify({
    nombre: response.data.nombre,
    rol: response.data.rol,
    id: response.data.id
}));
window.location.href = "index.html"; // Al ir al index, el script que te puse arriba detectará el nombre automáticamente.