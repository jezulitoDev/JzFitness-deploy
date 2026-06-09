<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->exercises() as $groupName => $items) {
            $group = MuscleGroup::query()->firstOrCreate(['name' => $groupName]);

            foreach ($items as [$name, $equipment, $description]) {
                Exercise::query()
                    ->whereNull('user_id')
                    ->updateOrCreate(
                        ['name' => $name],
                        [
                            'muscle_group_id' => $group->id,
                            'equipment' => $equipment,
                            'description' => $description,
                        ],
                    );
            }
        }
    }

    /**
     * Global exercise catalog grouped by muscle group name.
     *
     * @return array<string, list<array{0: string, 1: string, 2: string}>>
     */
    private function exercises(): array
    {
        return [
            'Pecho' => [
                ['Press banca', 'Barbell', 'Tumbado en banco plano, baja la barra al pecho con control y empuja hasta extender los brazos.'],
                ['Press banca inclinado', 'Barbell', 'En banco inclinado a 30-45º, baja la barra a la parte alta del pecho y empuja hacia arriba.'],
                ['Press banca declinado', 'Barbell', 'En banco declinado, baja la barra a la parte baja del pecho y empuja con fuerza.'],
                ['Press plano con mancuernas', 'Dumbbell', 'Tumbado en banco plano, empuja las mancuernas desde el pecho hasta juntar arriba.'],
                ['Press inclinado con mancuernas', 'Dumbbell', 'En banco inclinado, empuja las mancuernas hacia arriba manteniendo los codos a 45º.'],
                ['Press declinado con mancuernas', 'Dumbbell', 'En banco declinado, empuja las mancuernas trabajando la zona inferior del pectoral.'],
                ['Aperturas con mancuernas', 'Dumbbell', 'Tumbado, abre los brazos con codos semiflexionados y junta las mancuernas sobre el pecho.'],
                ['Aperturas en polea (crossover)', 'Cable', 'De pie entre poleas altas, cruza los cables por delante del pecho con codos semiflexionados.'],
                ['Aperturas en contractor (pec deck)', 'Machine', 'Sentado en la máquina, junta los brazos por delante apretando el pectoral.'],
                ['Press de pecho en máquina', 'Machine', 'Sentado, empuja los agarres hacia delante hasta extender los brazos sin bloquear los codos.'],
                ['Fondos en paralelas', 'Bodyweight', 'En paralelas, inclina el torso hacia delante y baja flexionando los codos antes de subir.'],
                ['Flexiones', 'Bodyweight', 'Con el cuerpo recto, baja el pecho al suelo flexionando los codos y empuja para subir.'],
                ['Flexiones declinadas', 'Bodyweight', 'Con los pies elevados sobre un banco, realiza flexiones para enfatizar el pectoral superior.'],
                ['Pullover con mancuerna', 'Dumbbell', 'Tumbado transversal al banco, lleva la mancuerna por detrás de la cabeza y vuelve sobre el pecho.'],
                ['Aperturas con banda elástica', 'Band', 'Con la banda anclada a la espalda, junta las manos por delante del pecho con tensión constante.'],
            ],
            'Espalda' => [
                ['Dominadas', 'Bodyweight', 'Cuélgate de la barra con agarre prono y sube hasta pasar la barbilla por encima.'],
                ['Dominadas supinas (chin-up)', 'Bodyweight', 'Con agarre supino a la anchura de hombros, sube hasta superar la barra con la barbilla.'],
                ['Dominadas asistidas en máquina', 'Machine', 'Apoya las rodillas en la plataforma asistida y realiza el gesto completo de dominada.'],
                ['Jalón al pecho', 'Cable', 'Sentado en la polea alta, tira de la barra hacia la parte alta del pecho juntando las escápulas.'],
                ['Jalón con agarre estrecho', 'Cable', 'Con maneral en V, tira hacia el pecho manteniendo el torso ligeramente inclinado atrás.'],
                ['Remo con barra', 'Barbell', 'Con el torso inclinado, tira de la barra hacia el abdomen apretando la espalda.'],
                ['Remo Pendlay', 'Barbell', 'Desde el suelo en cada repetición, tira explosivamente de la barra al pecho con el torso paralelo.'],
                ['Remo con mancuerna a una mano', 'Dumbbell', 'Apoyado en un banco, tira de la mancuerna hacia la cadera con la espalda recta.'],
                ['Remo en polea baja (Gironda)', 'Cable', 'Sentado, tira del maneral hacia el abdomen llevando los codos atrás y el pecho alto.'],
                ['Remo en máquina', 'Machine', 'Con el pecho apoyado, tira de los agarres hacia atrás juntando las escápulas.'],
                ['Remo en punta (T-bar)', 'Barbell', 'A horcajadas sobre la barra anclada, tira del agarre hacia el pecho con el torso inclinado.'],
                ['Peso muerto', 'Barbell', 'Con la espalda neutra, levanta la barra del suelo extendiendo caderas y rodillas a la vez.'],
                ['Rack pull', 'Barbell', 'Peso muerto parcial desde soportes a la altura de las rodillas, enfocado en la parte alta.'],
                ['Pull-over en polea alta', 'Cable', 'De pie con brazos casi rectos, lleva la barra de la polea alta hasta las caderas.'],
                ['Remo invertido', 'Bodyweight', 'Colgado bajo una barra baja con el cuerpo recto, tira del pecho hacia la barra.'],
                ['Remo con banda elástica', 'Band', 'Con la banda anclada al frente, tira de los extremos hacia el abdomen juntando las escápulas.'],
                ['Remo renegado', 'Dumbbell', 'En posición de plancha sobre mancuernas, rema alternando brazos sin rotar la cadera.'],
            ],
            'Hombros' => [
                ['Press militar', 'Barbell', 'De pie, empuja la barra desde los hombros hasta extender los brazos sobre la cabeza.'],
                ['Press militar sentado con mancuernas', 'Dumbbell', 'Sentado con respaldo, empuja las mancuernas desde los hombros hasta arriba.'],
                ['Press Arnold', 'Dumbbell', 'Comienza con palmas hacia ti y rota las mancuernas mientras empujas sobre la cabeza.'],
                ['Press de hombros en máquina', 'Machine', 'Sentado, empuja los agarres hacia arriba siguiendo el recorrido guiado de la máquina.'],
                ['Elevaciones laterales', 'Dumbbell', 'De pie, eleva las mancuernas lateralmente hasta la altura de los hombros con codos suaves.'],
                ['Elevaciones laterales en polea', 'Cable', 'Con la polea baja al costado, eleva el brazo lateralmente manteniendo tensión constante.'],
                ['Elevaciones frontales', 'Dumbbell', 'Eleva las mancuernas al frente hasta la altura de los hombros, alternando o a la vez.'],
                ['Pájaros (elevaciones posteriores)', 'Dumbbell', 'Inclinado hacia delante, abre los brazos hacia los lados trabajando el deltoides posterior.'],
                ['Face pull', 'Cable', 'Tira de la cuerda de la polea alta hacia la cara separando las manos al final del recorrido.'],
                ['Press tras nuca', 'Barbell', 'Sentado, baja la barra con cuidado por detrás de la cabeza y empuja hacia arriba.'],
                ['Remo al mentón', 'Barbell', 'De pie, tira de la barra verticalmente hasta la altura del pecho con los codos altos.'],
                ['Elevaciones laterales con banda', 'Band', 'Pisa la banda y eleva los brazos lateralmente hasta la altura de los hombros.'],
                ['Press de hombro con kettlebell', 'Kettlebell', 'Con la kettlebell en posición de rack, empuja sobre la cabeza estabilizando el core.'],
            ],
            'Biceps' => [
                ['Curl con barra', 'Barbell', 'De pie, flexiona los codos para subir la barra sin balancear el torso.'],
                ['Curl con barra Z', 'Barbell', 'Con barra Z y agarre semisupino, sube el peso reduciendo la tensión en las muñecas.'],
                ['Curl alterno con mancuernas', 'Dumbbell', 'Alterna la flexión de cada brazo supinando la muñeca al subir.'],
                ['Curl martillo', 'Dumbbell', 'Con agarre neutro, flexiona los codos manteniendo las palmas enfrentadas.'],
                ['Curl concentrado', 'Dumbbell', 'Sentado con el codo apoyado en el muslo, flexiona el brazo de forma controlada.'],
                ['Curl en banco Scott', 'Barbell', 'Con los brazos apoyados en el banco predicador, flexiona los codos con recorrido completo.'],
                ['Curl en polea baja', 'Cable', 'De pie frente a la polea baja, flexiona los codos manteniendo tensión continua.'],
                ['Curl con banda elástica', 'Band', 'Pisa la banda y flexiona los codos contra la resistencia creciente.'],
                ['Curl inclinado con mancuernas', 'Dumbbell', 'Sentado en banco inclinado con los brazos colgando, flexiona maximizando el estiramiento.'],
                ['Curl araña', 'Dumbbell', 'Tumbado boca abajo en banco inclinado, flexiona los codos con los brazos verticales.'],
                ['Curl 21s', 'Barbell', 'Realiza 7 medias repeticiones abajo, 7 arriba y 7 completas sin descanso.'],
            ],
            'Triceps' => [
                ['Extensiones en polea', 'Cable', 'Con los codos pegados al torso, extiende los brazos empujando la barra hacia abajo.'],
                ['Extensiones en polea con cuerda', 'Cable', 'Empuja la cuerda hacia abajo separando los extremos al final del recorrido.'],
                ['Press francés', 'Barbell', 'Tumbado, baja la barra hacia la frente flexionando solo los codos y extiende.'],
                ['Extensión de tríceps sobre la cabeza', 'Dumbbell', 'Con una mancuerna a dos manos sobre la cabeza, baja por detrás y extiende los codos.'],
                ['Patada de tríceps', 'Dumbbell', 'Inclinado con el codo elevado, extiende el antebrazo hacia atrás hasta bloquear.'],
                ['Fondos entre bancos', 'Bodyweight', 'Con las manos en un banco y los pies apoyados, baja y sube flexionando los codos.'],
                ['Press banca con agarre cerrado', 'Barbell', 'Press de banca con las manos a la anchura de los hombros para cargar el tríceps.'],
                ['Flexiones diamante', 'Bodyweight', 'Flexiones con las manos juntas formando un diamante bajo el pecho.'],
                ['Extensión de tríceps en máquina', 'Machine', 'Sentado, extiende los codos contra la resistencia guiada de la máquina.'],
                ['Extensiones de tríceps con banda', 'Band', 'Con la banda anclada arriba, extiende los codos hacia abajo con control.'],
                ['Press francés con mancuernas', 'Dumbbell', 'Tumbado con agarre neutro, baja las mancuernas junto a las orejas y extiende.'],
            ],
            'Antebrazos' => [
                ['Curl de muñeca con barra', 'Barbell', 'Con los antebrazos apoyados y palmas arriba, flexiona las muñecas para subir la barra.'],
                ['Curl de muñeca invertido', 'Barbell', 'Con palmas hacia abajo, extiende las muñecas trabajando los extensores del antebrazo.'],
                ['Curl invertido con barra', 'Barbell', 'Curl de bíceps con agarre prono que enfatiza el braquiorradial y el antebrazo.'],
                ['Paseo del granjero', 'Kettlebell', 'Camina erguido cargando un peso pesado en cada mano durante una distancia o tiempo.'],
                ['Suspensión en barra (dead hang)', 'Bodyweight', 'Cuélgate de la barra con los brazos extendidos aguantando el máximo tiempo posible.'],
                ['Pinza con discos', 'Bodyweight', 'Sujeta dos discos lisos juntos con los dedos el máximo tiempo posible.'],
            ],
            'Cuádriceps' => [
                ['Sentadilla', 'Barbell', 'Con la barra sobre la espalda, baja la cadera por debajo de las rodillas y sube con fuerza.'],
                ['Sentadilla frontal', 'Barbell', 'Con la barra apoyada en los hombros por delante, sentadilla manteniendo el torso vertical.'],
                ['Sentadilla goblet', 'Kettlebell', 'Sujeta la kettlebell al pecho y realiza una sentadilla profunda con el torso erguido.'],
                ['Sentadilla búlgara', 'Dumbbell', 'Con el pie trasero elevado en un banco, baja la rodilla trasera hacia el suelo.'],
                ['Prensa de piernas', 'Machine', 'Sentado en la máquina, empuja la plataforma extendiendo las piernas sin bloquear rodillas.'],
                ['Extensiones de cuádriceps', 'Machine', 'Sentado, extiende las rodillas contra el rodillo hasta estirar las piernas.'],
                ['Zancadas con mancuernas', 'Dumbbell', 'Da un paso largo al frente y baja la rodilla trasera casi hasta el suelo.'],
                ['Zancadas caminando', 'Dumbbell', 'Encadena zancadas avanzando, alternando piernas con el torso erguido.'],
                ['Sentadilla hack', 'Machine', 'En la máquina hack, baja en profundidad con la espalda apoyada y empuja.'],
                ['Sentadilla con banda', 'Band', 'Con la banda bajo los pies y sobre los hombros, realiza sentadillas contra la resistencia.'],
                ['Step-up al cajón', 'Dumbbell', 'Sube a un cajón empujando con la pierna adelantada y baja con control.'],
                ['Sentadilla sissy', 'Bodyweight', 'Inclínate hacia atrás flexionando las rodillas con la cadera extendida, aislando el cuádriceps.'],
                ['Sentadilla con salto', 'Bodyweight', 'Realiza una sentadilla y salta explosivamente al subir, amortiguando la caída.'],
                ['Sentadilla pistol', 'Bodyweight', 'Sentadilla completa a una pierna manteniendo la otra extendida al frente.'],
            ],
            'Isquiotibiales' => [
                ['Peso muerto rumano', 'Barbell', 'Con piernas casi rectas, baja la barra pegada a las piernas empujando la cadera atrás.'],
                ['Peso muerto rumano con mancuernas', 'Dumbbell', 'Igual que el rumano con barra, deslizando las mancuernas por delante de las piernas.'],
                ['Curl femoral tumbado', 'Machine', 'Boca abajo en la máquina, flexiona las rodillas llevando los talones a los glúteos.'],
                ['Curl femoral sentado', 'Machine', 'Sentado, flexiona las rodillas contra el rodillo con la espalda apoyada.'],
                ['Curl nórdico', 'Bodyweight', 'Con los tobillos sujetos, deja caer el cuerpo hacia delante frenando con los isquios.'],
                ['Buenos días', 'Barbell', 'Con la barra en la espalda, inclina el torso hacia delante con la espalda neutra y vuelve.'],
                ['Peso muerto a una pierna', 'Kettlebell', 'Bisagra de cadera sobre una pierna llevando la kettlebell hacia el suelo con la espalda recta.'],
                ['Curl femoral con fitball', 'Bodyweight', 'Tumbado con los talones en el balón, eleva la cadera y flexiona las rodillas rodando el balón.'],
                ['Pull-through en polea', 'Cable', 'De espaldas a la polea baja, lleva la cuerda entre las piernas y extiende la cadera.'],
            ],
            'Glúteos' => [
                ['Hip thrust', 'Barbell', 'Con la espalda apoyada en un banco y la barra en la cadera, extiende la cadera apretando el glúteo.'],
                ['Hip thrust en máquina', 'Machine', 'Realiza la extensión de cadera en la máquina específica con recorrido guiado.'],
                ['Puente de glúteos', 'Bodyweight', 'Tumbado boca arriba con las rodillas flexionadas, eleva la cadera apretando los glúteos.'],
                ['Patada de glúteo en polea', 'Cable', 'Con la tobillera en la polea baja, extiende la pierna hacia atrás sin arquear la lumbar.'],
                ['Abducción de cadera en máquina', 'Machine', 'Sentado, separa las piernas contra la resistencia apretando el glúteo medio.'],
                ['Abducción con banda', 'Band', 'Con la banda sobre las rodillas, sepáralas contra la resistencia en posición de sentadilla parcial.'],
                ['Peso muerto sumo', 'Barbell', 'Con stance ancho y puntas hacia fuera, levanta la barra extendiendo cadera y rodillas.'],
                ['Sentadilla sumo con mancuerna', 'Dumbbell', 'Con los pies muy separados, baja sujetando la mancuerna entre las piernas.'],
                ['Frog pumps', 'Bodyweight', 'Tumbado con las plantas de los pies juntas, eleva la cadera con repeticiones cortas.'],
                ['Patada de glúteo en cuadrupedia', 'Bodyweight', 'A cuatro patas, eleva una pierna flexionada hacia el techo apretando el glúteo.'],
            ],
            'Gemelos' => [
                ['Elevación de talones de pie', 'Machine', 'De pie en la máquina, sube sobre las puntas de los pies con un recorrido completo.'],
                ['Elevación de talones sentado', 'Machine', 'Sentado con el peso sobre las rodillas, eleva los talones trabajando el sóleo.'],
                ['Elevación de talones con barra', 'Barbell', 'Con la barra en la espalda, sube sobre las puntas de los pies con control.'],
                ['Elevación de talones a una pierna', 'Bodyweight', 'Sobre un escalón y a una pierna, baja el talón en estiramiento y sube a la punta.'],
                ['Elevación de talones en prensa', 'Machine', 'En la prensa, empuja la plataforma solo con las puntas de los pies.'],
                ['Saltos de gemelo (pogo)', 'Bodyweight', 'Saltos cortos y rápidos con las rodillas casi rectas, impulsando con los tobillos.'],
            ],
            'Core' => [
                ['Plancha', 'Bodyweight', 'Apoyado en antebrazos y puntas de los pies, mantén el cuerpo recto y el abdomen activo.'],
                ['Plancha lateral', 'Bodyweight', 'Apoyado de lado sobre un antebrazo, mantén la cadera elevada y alineada.'],
                ['Plancha con toque de hombro', 'Bodyweight', 'En plancha alta, toca alternativamente cada hombro sin rotar la cadera.'],
                ['Crunch', 'Bodyweight', 'Tumbado con rodillas flexionadas, eleva los hombros del suelo contrayendo el abdomen.'],
                ['Crunch en polea', 'Cable', 'De rodillas frente a la polea alta, flexiona el tronco llevando los codos a los muslos.'],
                ['Crunch en máquina', 'Machine', 'Sentado en la máquina de abdominales, flexiona el tronco contra la resistencia.'],
                ['Elevaciones de piernas colgado', 'Bodyweight', 'Colgado de la barra, eleva las piernas rectas hasta la horizontal o más.'],
                ['Elevaciones de rodillas en paralelas', 'Bodyweight', 'Apoyado en paralelas, lleva las rodillas al pecho con control.'],
                ['Rueda abdominal', 'Bodyweight', 'De rodillas, rueda hacia delante con la rueda manteniendo el core firme y vuelve.'],
                ['Russian twist', 'Dumbbell', 'Sentado con el torso inclinado atrás, rota el peso de lado a lado.'],
                ['Mountain climbers', 'Bodyweight', 'En plancha alta, lleva las rodillas al pecho alternando a ritmo rápido.'],
                ['Dead bug', 'Bodyweight', 'Tumbado boca arriba, extiende brazo y pierna contrarios sin despegar la lumbar.'],
                ['Bird dog', 'Bodyweight', 'A cuatro patas, extiende brazo y pierna contrarios manteniendo la pelvis estable.'],
                ['Pallof press', 'Cable', 'De pie y perpendicular a la polea, extiende los brazos resistiendo la rotación.'],
                ['Hollow hold', 'Bodyweight', 'Tumbado, eleva hombros y piernas manteniendo la lumbar pegada al suelo.'],
                ['Leñador en polea', 'Cable', 'Rota el tronco llevando el agarre de la polea alta en diagonal hacia la cadera contraria.'],
            ],
            'Trapecio' => [
                ['Encogimientos con barra', 'Barbell', 'De pie con la barra en las manos, eleva los hombros hacia las orejas y baja despacio.'],
                ['Encogimientos con mancuernas', 'Dumbbell', 'Con una mancuerna en cada mano, encoge los hombros con pausa arriba.'],
                ['Encogimientos en multipower', 'Machine', 'En la máquina Smith, realiza encogimientos con la carga guiada.'],
                ['Encogimientos con kettlebells', 'Kettlebell', 'Con una kettlebell en cada mano, eleva los hombros manteniendo los brazos relajados.'],
                ['Remo al mentón con mancuernas', 'Dumbbell', 'Tira de las mancuernas verticalmente hasta el pecho con los codos por encima de las manos.'],
            ],
            'Lumbar' => [
                ['Extensiones lumbares en banco', 'Bodyweight', 'En el banco de hiperextensiones, baja el torso y sube hasta la línea del cuerpo.'],
                ['Extensión lumbar en máquina', 'Machine', 'Sentado, extiende el tronco hacia atrás contra la resistencia con control.'],
                ['Hiperextensiones inversas', 'Bodyweight', 'Con el torso apoyado en un banco alto, eleva las piernas rectas hacia atrás.'],
                ['Superman', 'Bodyweight', 'Tumbado boca abajo, eleva brazos y piernas a la vez manteniendo unos segundos.'],
                ['Peso muerto con kettlebell', 'Kettlebell', 'Levanta la kettlebell desde el suelo con bisagra de cadera y espalda neutra.'],
            ],
            'Cuerpo completo' => [
                ['Burpees', 'Bodyweight', 'Desde de pie, baja a plancha, haz una flexión, vuelve y salta extendiendo los brazos.'],
                ['Thruster', 'Barbell', 'Encadena una sentadilla frontal con un press de hombros en un solo movimiento explosivo.'],
                ['Cargada y press (clean and press)', 'Barbell', 'Sube la barra del suelo a los hombros y empuja sobre la cabeza en dos tiempos.'],
                ['Snatch con mancuerna', 'Dumbbell', 'Lleva la mancuerna del suelo a por encima de la cabeza en un solo movimiento explosivo.'],
                ['Swing con kettlebell', 'Kettlebell', 'Balancea la kettlebell desde entre las piernas hasta el pecho con extensión potente de cadera.'],
                ['Turkish get-up', 'Kettlebell', 'Levántate del suelo hasta quedar de pie manteniendo la kettlebell sobre la cabeza.'],
                ['Cargada con kettlebell', 'Kettlebell', 'Lleva la kettlebell del suelo a la posición de rack con un movimiento fluido.'],
                ['Battle ropes', 'Cardio', 'Agita las cuerdas con ondas alternas o simultáneas manteniendo una sentadilla parcial.'],
                ['Empuje de trineo', 'Machine', 'Empuja el trineo cargado hacia delante con pasos potentes y el torso inclinado.'],
                ['Lanzamiento de balón a pared (wall ball)', 'Medicine Ball', 'Encadena una sentadilla con un lanzamiento del balón a un objetivo en la pared.'],
            ],
            'Cardio' => [
                ['Cinta de correr', 'Cardio', 'Carrera o caminata continua en cinta ajustando velocidad e inclinación.'],
                ['Sprints en cinta (HIIT)', 'Cardio', 'Alterna intervalos cortos de sprint con periodos de recuperación activa.'],
                ['Bicicleta estática', 'Cardio', 'Pedaleo continuo a ritmo moderado controlando la resistencia.'],
                ['Remo en ergómetro', 'Cardio', 'Remada completa encadenando empuje de piernas, tirón de brazos y vuelta controlada.'],
                ['Elíptica', 'Cardio', 'Movimiento de zancada continua sin impacto coordinando brazos y piernas.'],
                ['Escaladora de escaleras', 'Cardio', 'Sube escalones de forma continua manteniendo el torso erguido.'],
                ['Bicicleta de aire (assault bike)', 'Cardio', 'Pedalea y empuja los brazos a la vez; ideal para intervalos intensos.'],
                ['Saltar a la comba', 'Cardio', 'Saltos continuos a la comba con rebote suave sobre las puntas de los pies.'],
                ['Carrera al aire libre', 'Cardio', 'Carrera continua en exterior a ritmo cómodo o por intervalos.'],
                ['Caminata rápida', 'Cardio', 'Camina a paso ligero manteniendo pulsaciones moderadas.'],
                ['Natación', 'Cardio', 'Nado continuo alternando estilos para un trabajo cardiovascular completo.'],
            ],
        ];
    }
}
