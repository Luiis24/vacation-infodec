import { CommonModule } from '@angular/common';
import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';

type Condicion = 'nieve' | 'lluvia' | 'nublado' | 'soleado' | 'calor';
type Momento = 'dia' | 'noche';

interface Particula {
  id: number;
  left: number;   // % horizontal
  delay: number;  // s
  duration: number; // s
  size: number;   // px
  opacity: number;
}

@Component({
  selector: 'app-clima-animacion',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './clima-animacion.component.html',
  styleUrls: ['./clima-animacion.component.css']
})
export class ClimaAnimacionComponent implements OnChanges {
  /** Temperatura en °C */
  @Input() clima: number | null = null;

  /** Longitud geográfica (-180 a 180). Sirve para aproximar la hora local */
  @Input() longitud: number | null = null;

  /** Si es true, ocupa toda la pantalla (fixed). Si no, absolute dentro del contenedor padre */
  @Input() fullscreen = false;

  condicion: Condicion = 'soleado';
  momento: Momento = 'dia';

  particulas: Particula[] = [];
  estrellas: Particula[] = [];
  nubes: Particula[] = [];

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['clima'] || changes['longitud']) {
      this.calcularCondicion();
      this.calcularMomento();
      this.generarParticulas();
    }
  }

  private calcularCondicion(): void {
    const t = this.clima ?? 20;

    if (t <= 0) this.condicion = 'nieve';
    else if (t <= 10) this.condicion = 'lluvia';
    else if (t <= 18) this.condicion = 'nublado';
    else if (t <= 28) this.condicion = 'soleado';
    else this.condicion = 'calor';
  }

  private calcularMomento(): void {
    if (this.longitud === null || this.longitud === undefined) {
      this.momento = 'dia';
      return;
    }

    // UTC + offset aproximado por longitud
    const ahoraUTC = new Date();
    const horaUTC = ahoraUTC.getUTCHours() + ahoraUTC.getUTCMinutes() / 60;
    const offsetHoras = this.longitud / 15;
    let horaLocal = (horaUTC + offsetHoras + 24) % 24;

    this.momento = horaLocal >= 6 && horaLocal < 19 ? 'dia' : 'noche';
  }

  private generarParticulas(): void {
    // Nieve o lluvia
    if (this.condicion === 'nieve' || this.condicion === 'lluvia') {
      const total = this.condicion === 'nieve' ? 40 : 70;
      this.particulas = Array.from({ length: total }, (_, i) => ({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 5,
        duration: this.condicion === 'nieve' ? 6 + Math.random() * 6 : 0.6 + Math.random() * 0.6,
        size: this.condicion === 'nieve' ? 4 + Math.random() * 6 : 1.5 + Math.random() * 1.5,
        opacity: 0.4 + Math.random() * 0.6
      }));
    } else {
      this.particulas = [];
    }

    // Estrellas solo de noche y sin lluvia/nieve fuerte
    if (this.momento === 'noche' && this.condicion !== 'lluvia' && this.condicion !== 'nieve') {
      this.estrellas = Array.from({ length: 60 }, (_, i) => ({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 4,
        duration: 2 + Math.random() * 3,
        size: 1 + Math.random() * 2.5,
        opacity: 0.3 + Math.random() * 0.7
      }));
    } else {
      this.estrellas = [];
    }

    // Nubes para nublado / lluvia
    if (this.condicion === 'nublado' || this.condicion === 'lluvia') {
      this.nubes = Array.from({ length: 4 }, (_, i) => ({
        id: i,
        left: -20 + Math.random() * 100,
        delay: Math.random() * 10,
        duration: 30 + Math.random() * 30,
        size: 100 + Math.random() * 120,
        opacity: 0.5 + Math.random() * 0.4
      }));
    } else {
      this.nubes = [];
    }
  }

  get claseFondo(): string {
    return `fondo ${this.condicion} ${this.momento}`;
  }
}