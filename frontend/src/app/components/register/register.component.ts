import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css'],
})
export class RegisterComponent {
  nombre = '';
  correo = '';
  password = '';
  password_confirmation = '';

  errorMessage = '';
  loading = false;
  mostrarPassword = false;
  mostrarConfirm = false;

  constructor(
    private authService: AuthService,
    private router: Router,
  ) {}

  get passwordsCoinciden(): boolean {
    return (
      this.password.length > 0 && this.password === this.password_confirmation
    );
  }

  get passwordsNoCoinciden(): boolean {
    return (
      this.password_confirmation.length > 0 &&
      this.password !== this.password_confirmation
    );
  }

  onRegister() {
    this.errorMessage = '';

    if (
      !this.nombre ||
      !this.correo ||
      !this.password ||
      !this.password_confirmation
    ) {
      this.errorMessage = 'Por favor completa todos los campos.';
      return;
    }

    // 👇 NUEVO: validar formato de correo
    if (!this.EMAIL_REGEX.test(this.correo.trim())) {
      this.errorMessage = 'El correo electrónico no tiene un formato válido.';
      return;
    }

    if (this.password !== this.password_confirmation) {
      this.errorMessage = 'Las contraseñas no coinciden.';
      return;
    }

    // 👇 NUEVO: validar contraseña fuerte
    if (!this.passwordCumpleRequisitos) {
      this.errorMessage =
        'La contraseña debe tener mayúscula, minúscula y número.';
      return;
    }

    this.loading = true;

    this.authService
      .register({
        nombre: this.nombre.trim(),
        correo: this.correo.trim(),
        password: this.password,
        password_confirmation: this.password_confirmation,
      })
      .subscribe({
        next: () => {
          this.router.navigate(['/login']);
        },
        error: (err) => {
          this.loading = false;
          this.errorMessage =
            err.error?.error?.message || 'Error en el registro.';
        },
      });
  }

  togglePassword() {
    this.mostrarPassword = !this.mostrarPassword;
  }
  toggleConfirm() {
    this.mostrarConfirm = !this.mostrarConfirm;
  }

  limpiarError() {
    if (this.errorMessage) this.errorMessage = '';
  }

  // ====== Regex de correo ======
  private readonly EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

  // ====== Validaciones de correo ======
  get correoInvalido(): boolean {
    return this.correo.length > 0 && !this.EMAIL_REGEX.test(this.correo.trim());
  }

  get correoValido(): boolean {
    return this.correo.length > 0 && this.EMAIL_REGEX.test(this.correo.trim());
  }

  // ====== Validaciones de contraseña fuerte ======
  get passwordCumpleRequisitos(): boolean {
    return (
      /[A-Z]/.test(this.password) && // al menos una mayúscula
      /[a-z]/.test(this.password) && // al menos una minúscula
      /\d/.test(this.password) && // al menos un número
      this.password.length >= 6
    );
  }

  // Mostrar feedback solo cuando el usuario ya escribió algo
  get mostrarFeedbackPassword(): boolean {
    return this.password.length > 0;
  }

  get passwordInvalida(): boolean {
    return this.password.length > 0 && !this.passwordCumpleRequisitos;
  }
}
