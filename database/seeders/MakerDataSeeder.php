<?php

namespace Database\Seeders;

use App\Models\Tracking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\DB; // Asegúrate de importar DB
use App\Models\Question;


/*class MakerDataSeeder extends Seeder
{

    public function run(): void
    {
        $floorplan_ids = [147, 148];
        $control_point = 16;
        $new_floorplan_id = 530;

        /*echo "🔍 Buscando dispositivos con floorplan_ids: " . implode(', ', $floorplan_ids) . " y control_point: $control_point\n";

        $devices = Device::whereIn('floorplan_id', $floorplan_ids)
            ->where('type_control_point_id', $control_point)
            ->get();

        echo "📊 Se encontraron " . $devices->count() . " dispositivos\n";

        if ($devices->count() === 0) {
            echo "❌ No se encontraron dispositivos para copiar\n";
            return;
        }

        echo "🔄 Iniciando proceso de copia al nuevo floorplan_id: $new_floorplan_id\n";
        echo str_repeat("-", 50) . "\n";

        $copiedCount = 0;
        foreach ($devices as $index => $device) {
            echo "📝 Procesando dispositivo " . ($index + 1) . "/" . $devices->count() . "\n";
            echo "   ID Original: " . $device->id . "\n";
            echo "   Nombre: " . $device->name . "\n";
            echo "   Floorplan Actual: " . $device->floorplan_id . "\n";
            echo "   Control Point: " . $device->control_point . "\n";

            try {
                $newDevice = $device->replicate();
                $newDevice->floorplan_id = $new_floorplan_id;
                $newDevice->save();

                $copiedCount++;
                echo "✅ ✅ COPIADO EXITOSAMENTE\n";
                echo "   Nuevo ID: " . $newDevice->id . "\n";
                echo "   Nuevo Floorplan: " . $newDevice->floorplan_id . "\n";

            } catch (\Exception $e) {
                echo "❌ ❌ ERROR al copiar dispositivo ID: " . $device->id . "\n";
                echo "   Error: " . $e->getMessage() . "\n";
            }

            echo str_repeat("-", 30) . "\n";
        }

        echo "\n🎉 PROCESO COMPLETADO\n";
        echo "========================================\n";
        echo "Dispositivos encontrados: " . $devices->count() . "\n";
        echo "Dispositivos copiados exitosamente: " . $copiedCount . "\n";
        echo "Errores: " . ($devices->count() - $copiedCount) . "\n";
        echo "Nuevo floorplan_id asignado: $new_floorplan_id\n";
        echo "========================================\n";


        $devices = Device::whereIn('floorplan_id', $new_floorplan_id)
            ->get();

        $index = 0;
        $count = 1;

        foreach ($devices as $device) {
            $device->itemnumber = 0;
            $device->nplan = $count;

            $index++;
            $count++;
        }
    }
}
*/

/*class MakerDataSeeder extends Seeder
{
    public function run(): void
    {
        $trackings = Tracking::all();
        foreach ($trackings as $tracking) {
            $tracking->trackable_id = $tracking->customer_id;
            $tracking->trackable_type = Customer::class;
            $tracking->save();
        }
        $this->command->info("¡Copia actualizados! Se actualizaron {$trackings->count()} usuarios.");
    }
}*/


// Section -> Control Points 
class MakerDataSeeder extends Seeder
{
    private $file_answers_path = 'datas/json/answers.json';

    private function getOptions($id, $answers)
    {
        foreach ($answers as $answer) {
            if ($answer['id'] == $id) {
                return $answer['options'];
            }
        }
        return [];
    }

    public function run(): void
    {
        $questions = Question::all();
        $answers = json_decode(file_get_contents(public_path($this->file_answers_path)), true);

        foreach ($questions as $index => $q) {
            $answer = $this->getOptions($q->question_option_id, $answers);
            echo $answer[0] ?? 'No answer';
            $q->update([
                'answer_default' => $answer[0] ?? null,
            ]);
        }
    }
}