import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';

export type EstadoAnimo = 'eufórico' | 'feliz' | 'neutral' | 'preocupado' | 'devastado';

@Component({
  selector: 'app-personaje-divisas',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './personaje-divisas.component.html',
  styleUrls: ['./personaje-divisas.component.css']
})
export class PersonajeDivisasComponent {
  /** Nivel 0 a 4 (0 = devastado, 4 = eufórico) */
  @Input() set nivel(v: number) {
    const n = Math.max(0, Math.min(4, Math.round(v ?? 2)));
    this._nivel = n;
    this.estado = this.mapEstado(n);
  }
  get nivel(): number { return this._nivel; }

  private _nivel = 2;
  estado: EstadoAnimo = 'neutral';

  /** Texto i18n pasado desde el padre */
  @Input() textoEs = '';
  @Input() textoDe = '';

  /** Animación CSS según estado */
  get claseAnimacion(): string {
    return `personaje-${this.estado}`;
  }

  get emojiAmbiental(): string {
    switch (this.estado) {
      case 'eufórico':   return '💰✨';
      case 'feliz':      return '💵';
      case 'neutral':    return '💳';
      case 'preocupado': return '💸';
      case 'devastado':  return '🕳️';
    }
  }

  private mapEstado(n: number): EstadoAnimo {
    if (n >= 4) return 'eufórico';
    if (n === 3) return 'feliz';
    if (n === 2) return 'neutral';
    if (n === 1) return 'preocupado';
    return 'devastado';
  }
}