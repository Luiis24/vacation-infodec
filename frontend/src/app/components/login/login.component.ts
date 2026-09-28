import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  correo = '';
  password = '';
  errorMessage = '';
  loading = false;
  mostrarPassword = false;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  onLogin() {
    if (!this.correo || !this.password) {
      this.errorMessage = 'Por favor completa todos los campos.';
      return;
    }

    this.errorMessage = '';
    this.loading = true;

    this.authService
      .login({ correo: this.correo, password: this.password })
      .subscribe({
        next: (res) => {
          this.authService.saveTokens(
            res.data.access_token,
            res.data.refresh_token
          );
          this.router.navigate(['/dashboard']);
        },
        error: (err) => {
          this.loading = false;
          this.errorMessage =
            err.error?.error?.message || 'Error al iniciar sesión.';
        }
      });
  }

  togglePassword() {
    this.mostrarPassword = !this.mostrarPassword;
  }

  limpiarError() {
    if (this.errorMessage) this.errorMessage = '';
  }
}