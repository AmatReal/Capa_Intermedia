document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCrearProducto');  // Asegúrate de que tu formulario tenga este ID
    const categoria = document.getElementById('id_categoria');  // ID del campo de categoría
    const nombre = document.getElementById('nombre_producto');  // ID del campo de nombre del producto
    const descripcion = document.getElementById('descripcion_producto');  // ID del campo de descripción
    const video = document.getElementById('video_producto');  // ID del campo de video
    const imagenes = document.getElementById('imagenes_producto');  // ID del campo de imágenes
    const submitButton = document.getElementById('submit'); // ID del botón de enviar

    // Función para validar la categoría
    function validateCategoria() {
        if (categoria.value === "") {
            alert("La categoría es obligatoria.");
            return false;
        }
        return true;
    }

    // Función para validar el nombre del producto
    function validateNombre() {
        if (nombre.value.trim() === "") {
            alert("El nombre del producto es obligatorio.");
            return false;
        }
        return true;
    }

    // Función para validar la descripción
    function validateDescripcion() {
        if (descripcion.value.trim() === "") {
            alert("La descripción es obligatoria.");
            return false;
        }
        return true;
    }

    // Función para validar las imágenes
    function validateImagenes() {
        const validImageExtensions = ['jpg', 'jpeg', 'png'];
        const files = imagenes.files;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!validImageExtensions.includes(fileExtension)) {
                alert("Las imágenes deben ser de tipo JPG, JPEG o PNG.");
                return false;
            }
        }

        return true;
    }

    // Función para validar el video
    function validateVideo() {
        const validVideoExtensions = ['mp4'];
        const file = video.files[0];

        if (file) {
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!validVideoExtensions.includes(fileExtension)) {
                alert("El video debe ser de tipo MP4.");
                return false;
            }
        }
        return true;
    }

    // Validar todo antes de enviar el formulario
    form.addEventListener('submit', function (event) {
        event.preventDefault();  // Prevenir el envío del formulario por defecto

        // Realizar las validaciones
        if (validateCategoria() && validateNombre() && validateDescripcion() && validateImagenes() && validateVideo()) {
            // Si todas las validaciones son correctas, enviar el formulario
            form.submit();  // Este es el envío real del formulario
        }
    });
});
    