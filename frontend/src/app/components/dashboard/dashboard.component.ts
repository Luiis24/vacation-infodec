import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Subject, takeUntil } from 'rxjs';

import { ClimaAnimacionComponent } from '../../components/clima-animacion/clima-animacion.component';
import {
  HistorialItem,
  HistorialModalComponent
} from '../../components/historial-modal/historial-modal.component';
import { PersonajeDivisasComponent } from '../../components/personaje-divisas/personaje-divisas.component';
import { AuthService } from '../../services/auth.service';

interface Pais {
  id: number;
  nombre: string;
  codigo: string;
  moneda: {
    codigo: string;
    nombre: string;
    simbolo: string;
  };
}

interface Ciudad {
  id: number;
  pais_id: number;
  nombre: string;
  latitud: string;
  longitud: string;
}

interface ResultadoConsulta {
  pais: string;
  ciudad: string;
  presupuesto_cop: number;
  clima_celsius: string;   // "14.73 °C"
  moneda: string;
  simbolo: string;
  valor_convertido: number;
  tasa_aplicada: number;
  fecha: string;
}

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ClimaAnimacionComponent,
    PersonajeDivisasComponent,
    HistorialModalComponent
  ],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit, OnDestroy {
  // --- Wizard ---
  paso = 1;

  // --- i18n ---
  idioma: 'es' | 'de' = 'es';

  // --- Datos ---
  paises: Pais[] = [];
  ciudades: Ciudad[] = [];
  paisSeleccionado: Pais | null = null;
  ciudadSeleccionada: Ciudad | null = null;
  presupuesto: number | null = null;

  // --- Resultado ---
  resultado: ResultadoConsulta | null = null;
  enviando = false;
  errorConsulta = '';

  // --- Historial ---
  historialList: HistorialItem[] = [];
  verHistorialModal = false;
  cargandoHistorial = false;

  // --- Loading inicial ---
  cargandoPaises = false;
  cargandoCiudades = false;

  // --- Cleanup ---
  private destroy$ = new Subject<void>();

  private apiUrl = 'http://localhost:8000/api';

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.authService.idioma$
      .pipe(takeUntil(this.destroy$))
      .subscribe(lang => {
        this.idioma = (lang === 'de' ? 'de' : 'es');
      });

    this.cargarPaises();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // ==================== i18n ====================
  t(es: string, de: string): string {
    return this.idioma === 'es' ? es : de;
  }

  cambiarIdioma(): void {
    this.authService.setIdioma(this.idioma);
  }

  // ==================== CARGA DE DATOS ====================
  cargarPaises(): void {
    this.cargandoPaises = true;
    this.http
      .get<{ success: boolean; data: Pais[] }>(`${this.apiUrl}/paises`)
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.paises = res.data || [];
          this.cargandoPaises = false;
        },
        error: () => {
          this.cargandoPaises = false;
        }
      });
  }

  onPaisChange(): void {
    this.ciudadSeleccionada = null;

    if (!this.paisSeleccionado) {
      this.ciudades = [];
      return;
    }

    this.cargandoCiudades = true;

    this.http
      .get<{ success: boolean; data: Ciudad[] }>(
        `${this.apiUrl}/paises/${this.paisSeleccionado.id}/ciudades`
      )
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.ciudades = res.data || [];
          this.cargandoCiudades = false;
        },
        error: () => {
          this.ciudades = [];
          this.cargandoCiudades = false;
        }
      });
  }

  // ==================== WIZARD ====================
  irAPaso(p: number): void {
    // Validación mínima de paso
    if (p === 2 && !this.ciudadSeleccionada) return;
    if (p === 3 && (!this.ciudadSeleccionada || !this.presupuesto)) return;

    this.paso = p;

    if (p === 1) {
      // Reset
      this.resultado = null;
      this.errorConsulta = '';
    }
  }

  consultar(): void {
    if (!this.ciudadSeleccionada || !this.presupuesto || this.presupuesto <= 0) {
      return;
    }

    this.enviando = true;
    this.errorConsulta = '';

    this.http
      .post<{ success: boolean; data: ResultadoConsulta }>(
        `${this.apiUrl}/consultas`,
        {
          ciudad_id: this.ciudadSeleccionada.id,
          presupuesto: this.presupuesto
        }
      )
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.resultado = res.data;
          this.paso = 3;
          this.enviando = false;
        },
        error: (err) => {
          this.enviando = false;
          this.errorConsulta =
            err.error?.error?.message ||
            this.t('No se pudo consultar. Intenta de nuevo.', 'Abfrage fehlgeschlagen.');
        }
      });
  }

  // ==================== HELPERS DE RESULTADO ====================
  /** Extrae el número de "14.73 °C" → 14.73 */
  get climaNumerico(): number | null {
    if (!this.resultado?.clima_celsius) return null;
    const n = parseFloat(String(this.resultado.clima_celsius).replace(/[^\d.-]/g, ''));
    return isNaN(n) ? null : n;
  }

  /** Longitud de la ciudad seleccionada (para hora local aprox) */
  get longitudCiudad(): number | null {
    if (!this.ciudadSeleccionada?.longitud) return null;
    const n = parseFloat(this.ciudadSeleccionada.longitud);
    return isNaN(n) ? null : n;
  }

  /** Nivel 0-4 para el personaje según qué tanto rinde el dinero */
  get nivelDivisa(): number {
    if (!this.resultado) return 2;
    const ratio = this.resultado.valor_convertido / this.resultado.presupuesto_cop;

    if (ratio >= 0.005)  return 4; // eufórico
    if (ratio >= 0.002)  return 3; // feliz
    if (ratio >= 0.0008) return 2; // neutral
    if (ratio >= 0.0003) return 1; // preocupado
    return 0;                      // devastado
  }

  /** Texto del personaje según nivel */
  get textoPersonaje(): { es: string; de: string } {
    switch (this.nivelDivisa) {
      case 4:
        return {
          es: '¡Tu dinero rendirá muchísimo aquí!',
          de: 'Ihr Budget wird hier sehr viel wert sein!'
        };
      case 3:
        return {
          es: 'Buena conversión, viaje cómodo.',
          de: 'Gute Umrechnung, komfortable Reise.'
        };
      case 2:
        return {
          es: 'Presupuesto equilibrado para este destino.',
          de: 'Ausgeglichenes Budget für dieses Reiseziel.'
        };
      case 1:
        return {
          es: 'Moneda fuerte, gasta con cuidado.',
          de: 'Starke Währung, geben Sie vorsichtig aus.'
        };
      default:
        return {
          es: 'Tu dinero se esfumará rápido aquí 😅',
          de: 'Ihr Geld wird hier schnell verschwinden 😅'
        };
    }
  }

  formatearCOP(v: number): string {
    return new Intl.NumberFormat('es-CO').format(v ?? 0);
  }

  formatearConvertido(v: number): string {
    return new Intl.NumberFormat('es-CO', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(v ?? 0);
  }

  // ==================== HISTORIAL ====================
  abrirHistorial(): void {
    this.verHistorialModal = true;
    this.cargarHistorial();
  }

  cargarHistorial(): void {
    this.cargandoHistorial = true;
    this.http
      .get<{ success: boolean; data: HistorialItem[] }>(
        `${this.apiUrl}/consultas/historial`
      )
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.historialList = res.data || [];
          this.cargandoHistorial = false;
        },
        error: () => {
          this.cargandoHistorial = false;
        }
      });
  }

  // ==================== LOGOUT ====================
  logout(): void {
    this.authService.logout();
  }
}