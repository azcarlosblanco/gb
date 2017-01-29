<?php

use Illuminate\Database\Seeder;

class QuizTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
      Eloquent::unguard();
      DB::statement('SET FOREIGN_KEY_CHECKS=0;');

      DB::table('quiz')->truncate();
      DB::table('quiz_item')->truncate();

      DB::table('quiz')->insert([
        array('code' => 'other_coverages', 'description' => 'información de cobertura médica anterior', 'insurance_company_id' => 1),
        array('code' => 'medical_history', 'description' => 'historia médica familiar', 'insurance_company_id' => 1),
        array('code' => 'bad_habits', 'description' => 'Habitos', 'insurance_company_id' => 1),
        array('code' => 'head_doctors', 'description' => 'médicos personales y exámenes de rutina', 'insurance_company_id' => 1),
        array('code' => 'section_a', 'description' => 'seccion A', 'insurance_company_id' => 1),
        array('code' => 'section_b', 'description' => 'seccion B', 'insurance_company_id' => 1)
      ]);

      DB::table('quiz_item')->insert([
        array('description' => '¿Usted o alguno de sus dependientes cuentan con otra cobertura médica internacional? Si respondio \'Sí\', por favor adjunte una copia del certificado de cobertura del plan y el comprobante del último pago.',
              'resp_type' => 1,
              'quiz_id' => 1),
        array('description' => '¿Piensa continuar con esta cobertura?',
              'resp_type' => 1,
              'quiz_id' => 1),
        array('description' => '¿Ha sido alguna solicitud de cobertura médica o de vida rechazada, aceptada sujeta a restricciones, o a una cuota mayor que las tarifas estándar de la compañía para cualquiera de los solicitantes? Si respondio \'Sí\', por favor adjunte detalles.',
              'resp_type' => 1,
              'quiz_id' => 1),
        array('description' => '¿Ha tenido cobertura médica con Best Doctors S.A. Empresa de Medicina Prepagada o cualquiera de sus afiliados?',
              'resp_type' => 1,
              'quiz_id' => 1),
        array('description' => '¿Usted o alguno de sus dependientes tiene historia familiar de diabetes, hipertensión, desórdenes cardíacos, cáncer o enfermedad congénita o hereditaria? Si respondió \'Sí\', por favor explicar.',
              'resp_type' => 1,
              'quiz_id' => 2),
        array('description' => '¿Usted o alguno de sus dependientes alguna vez ha fumado cigarrillos, consumido productos de nicotina, alcohol o drogas ilegales? Si respondió \'Sí\', por favor explicar.',
              'resp_type' => 1,
              'quiz_id' => 3),
        array('description' => '¿Usted o alguno de sus dependientes tiene un médico primario o ha consultado a un especialista?',
              'resp_type' => 1,
              'quiz_id' => 4),
        array('description' => '¿Alguno de los solicitantes ha tenido un examen pediátrico, ginecológico o rutinario en los últimos 5 años?',
              'resp_type' => 1,
              'quiz_id' => 4),
        array('description' => 'a) Cáncer, tumores malignos o tumores benignos.',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'b) ¿Alguna condición médica que haya requerido cirugía y/o algun procedimiento quirúrgico?',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'c) Cálculos en los riñones, alteración en los riñones o la vejiga, frecuencia urinaria o ardor',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'd) Bocio, alteración en la tiroides, diabetes',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'e) Epilepsia, parálisis, enfermedades mentales o nerviosas, alcoholismo, migrañas',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'f) Adicción a drogas por las cuales dicha persona ha sido tratada u hospitalizada',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'g) Alteración de la vesícula, hernia, alteración del estómago o los intestinos, úlceras, hemorroides, alteración de hígado',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'h) Cataratas u otra alteración en los ojos, problema de los oídos',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'i) Tuberculosis, enfermedades pulmonares, asma o bronquitis, sinusitis, tos crónica y padecimiento de la garganta',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'j) Artritis, reumatismo, trastornos de las articulaciones, padecimiento en la columna vertebral, gota',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'k) Patología cardíaca, alteraciones de la presión arterial, anemia, fiebre reumática, trastornos de coagulación, hemofilia, flebitis, trombosis, dolor de pecho, angina, aneurisma',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'l) Femenino: alteraciones menstruales o hemorragias menstruales, desórdenes en los órganos reproductivos, enfermedades transmitidas sexualmente, enfermedad de las mamas',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'm) Femenino: Actualmente embarazada (Fecha de parto) (MM/DD/AAAA)',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'n) Número de: Embarazos Partos Normales Partos Cesárea Abortos Motivo de Cesárea',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'o) Femenino: Complicación del embarazo o del parto, embarazo múltiple, o un hijo(a) con algún defecto de nacimiento, enfermedad congénita o hereditaria',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'p) Masculino: Alteraciones de la próstata, enfermedades transmitidas sexualmente',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'q) SIDA (Síndrome Inmunológico de Deficiencia Adquirida) Complejo Relacionado al SIDA (CRS)',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'r) Dermatitis o enfermedades de la piel u otro problema de la piel',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 's) Desviación del tabique nasal, sinusitis, pólipos u otro trastorno de la nariz',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 't) Defectos de nacimiento o anormalidades congénitas, retraso del desarrollo, síndrome de Down, malformaciones del corazón/pulmón/riñón',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'u) ¿Algún solicitante es candidato o receptor para un transplante de órgano, médula ósea o células madres?',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'v) ¿Algún solicitante es candidato o receptor para prótesis, clavija, tornillos, clavos, alambre, placa ósea/articular?',
              'resp_type' => 1,
              'quiz_id' => 5),
        array('description' => 'a) ¿Haya consultado a un médico u otro proveedor para tratamiento médico o quirúrgico, o para consejo de alguna otra enfermedad que no esté mencionada en la sección A?',
              'resp_type' => 1,
              'quiz_id' => 6),
        array('description' => 'b) ¿Haya tenido alguna alteración en su salud o síntoma que no haya sido mencionado en la Sección A o en la pregunta (a) de esta Secciónpor lo cual haya o no consultado a un médico?',
              'resp_type' => 1,
              'quiz_id' => 6),
        array('description' => 'c) ¿Haya tomado o toma regularmente alguna medicina? En caso afirmativo, indique.',
              'resp_type' => 1,
              'quiz_id' => 6),
        array('description' => 'd) ¿Alguna persona nombrada en esta solicitud ha disminuido o aumentado de peso en los últimos 12 meses? En caso afirmativo indique.',
              'resp_type' => 1,
              'quiz_id' => 6)
      ]);

      DB::statement('SET FOREIGN_KEY_CHECKS=1;');
      Eloquent::reguard();
    }
}
