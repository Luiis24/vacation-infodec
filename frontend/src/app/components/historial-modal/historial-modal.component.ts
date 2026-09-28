import { CommonModule } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';

export interface HistorialItem {
  id: number;
  presupuesto_cop: number;
  clima: number;
  tasa: number;
  valor_convertido: number;
  fecha: string;
  ciudad?: {
    id: number;
    nombre: string;
    pais?: {
      nombre: string;
      moneda?: {
        simbolo: string;
        codigo: string;
        nombre: string;
      };
    };
  };
}

@Component({
  selector: 'app-historial-modal',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './historial-modal.component.html',
  styleUrls: ['./historial-modal.component.css']
})
export class HistorialModalComponent {
  @Input() visible = false;
  @Input() idioma: 'es' | 'de' = 'es';
  @Input() historial: HistorialItem[] = [];
  @Input() cargando = false;

  @Output() cerrar = new EventEmitter<void>();

  /** Detecta si el clima es frío (para color del badge) */
  esFrio(clima: number): boolean {
    return clima <= 10;
  }

  /** Detecta si el clima es caluroso */
  esCaluroso(clima: number): boolean {
    return clima >= 25;
  }

  /** Icono según clima */
  iconoClima(clima: number): string {
    if (clima <= 0) return '❄️';
    if (clima <= 10) return '🌧️';
    if (clima <= 18) return '☁️';
    if (clima <= 28) return '☀️';
    return '🔥';
  }

  /** Formatea el número con separador de miles */
  formatearCOP(v: number): string {
    return new Intl.NumberFormat('es-CO').format(v ?? 0);
  }

  /** Formatea valor convertido con 2 decimales */
  formatearConvertido(v: number): string {
    return new Intl.NumberFormat('es-CO', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(v ?? 0);
  }

  /** Formatea fecha relativa simple */
  formatearFecha(fecha: string): string {
    if (!fecha) return '';
    const d = new Date(fecha);
    const dia = d.getDate().toString().padStart(2, '0');
    const mes = (d.getMonth() + 1).toString().padStart(2, '0');
    const anio = d.getFullYear();
    const hora = d.getHours().toString().padStart(2, '0');
    const min = d.getMinutes().toString().padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${min}`;
  }

  /** Texto según idioma */
  t(es: string, de: string): string {
    return this.idioma === 'es' ? es : de;
  }

  /** Cerrar con click en backdrop */
  onBackdropClick(event: MouseEvent): void {
    if ((event.target as HTMLElement).classList.contains('modal-backdrop')) {
      this.cerrar.emit();
    }
  }
}