// main.js

// Función para validar el formato del correo institucional
function validateInstitutionalEmail(email) {
   const emailRegex = /^[a-zA-Z0-9._%+-]+@ipn\.mx$/;
   return emailRegex.test(email);
}

// Validación en tiempo real para el formulario de registro
document.addEventListener('DOMContentLoaded', function () {
   const emailInput = document.getElementById('correo_institucional');
   if (emailInput) {
       emailInput.addEventListener('blur', function () {
           const email = this.value;
           const feedback = document.getElementById('email-feedback');
           if (!validateInstitutionalEmail(email)) {
               this.classList.add('is-invalid');
               if (feedback) feedback.textContent = 'El correo debe ser institucional (@ipn.mx)';
           } else {
               this.classList.remove('is-invalid');
               this.classList.add('is-valid');
               if (feedback) feedback.textContent = '';
           }
       });
   }

   // Confirmación de contraseña
   const passInput = document.getElementById('contrasena');
   const confirmPassInput = document.getElementById('confirmar_contrasena');
   if (passInput && confirmPassInput) {
       confirmPassInput.addEventListener('input', function () {
           if (this.value !== passInput.value) {
               this.setCustomValidity('Las contraseñas no coinciden');
               this.classList.add('is-invalid');
           } else {
               this.setCustomValidity('');
               this.classList.remove('is-invalid');
               this.classList.add('is-valid');
                       }
       });
   }
});

// Función para mostrar mensajes de alerta personalizados
function showAlert(message, type = 'info') {
   const alertDiv = document.createElement('div');
   alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
   alertDiv.role = 'alert';
   alertDiv.innerHTML = `
       ${message}
       <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   `;
   const container = document.querySelector('.container');
   if (container) {
       container.insertBefore(alertDiv, container.firstChild);
   }
}

// Función para manejar la descarga de QR (simulada)
function downloadQR(qrCode) {
   if (!qrCode) return;
   const link = document.createElement('a');
   link.href = `https://api.qrserver.com/v1/create-qr-code/?size=300x300\&data=${encodeURIComponent(qrCode)}`;
   link.download = `qr\_acceso\_${new Date().getTime()}.png`;
   document.body.appendChild(link);
   link.click();
   document.body.removeChild(link);
}

// Función para confirmar eliminaciones
function confirmDelete(message = '¿Estás seguro de que deseas eliminar este registro?') {
   return confirm(message);
}

// Añadir eventos de confirmación a botones de eliminación
document.querySelectorAll('.btn-delete').forEach(button => {
   button.addEventListener('click', function (e) {
       if (!confirmDelete()) {
           e.preventDefault();
       }
   });
});