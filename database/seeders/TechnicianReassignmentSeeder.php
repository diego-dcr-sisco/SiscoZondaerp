<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Contract;
use App\Models\OrderTechnician;
use App\Models\ContractTechnician;
use App\Models\Technician;

class TechnicianReassignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder reemplaza un técnico por otro en TODAS las órdenes y contratos del sistema.
     * Solo actualiza los registros que tengan asignado el técnico antiguo.
     * 
     * ⚠️ ADVERTENCIA: Este seeder actualiza registros en TODO el sistema sin importar el cliente.
     * 
     * Uso:
     * php artisan db:seed --class=TechnicianReassignmentSeeder
     * 
     * Debes modificar las variables $oldTechnicianId y $newTechnicianId antes de ejecutar.
     */
    public function run(): void
    {
        // ====================================================
        // CONFIGURACIÓN: Modifica estos valores antes de ejecutar
        // ====================================================
        $oldTechnicianId = 70;       // ID del técnico actual (a reemplazar)
        $newTechnicianId = 56;       // ID del nuevo técnico (reemplazo)
        // ====================================================

        // Validar que el técnico antiguo existe
        $oldTechnician = Technician::find($oldTechnicianId);
        if (!$oldTechnician) {
            $this->command->error("❌ Técnico antiguo con ID {$oldTechnicianId} no encontrado.");
            return;
        }

        // Validar que el técnico nuevo existe
        $newTechnician = Technician::find($newTechnicianId);
        if (!$newTechnician) {
            $this->command->error("❌ Técnico nuevo con ID {$newTechnicianId} no encontrado.");
            return;
        }

        // Validar que no sean el mismo técnico
        if ($oldTechnicianId === $newTechnicianId) {
            $this->command->error("❌ El técnico antiguo y el nuevo son el mismo. No hay nada que actualizar.");
            return;
        }

        $oldTechnicianName = $oldTechnician->user->name ?? 'N/A';
        $newTechnicianName = $newTechnician->user->name ?? 'N/A';
        
        $this->command->info("🔄 Iniciando reasignación GLOBAL de técnico...");
        $this->command->warn("⚠️  Se actualizarán TODAS las órdenes y contratos del sistema");
        $this->command->info("🔧 Técnico antiguo: {$oldTechnicianName} (ID: {$oldTechnicianId})");
        $this->command->info("✨ Técnico nuevo: {$newTechnicianName} (ID: {$newTechnicianId})");
        $this->command->newLine();

        // Actualizar TODAS las órdenes con el técnico antiguo
        $orderTechniciansUpdated = OrderTechnician::where('technician_id', $oldTechnicianId)
            ->update(['technician_id' => $newTechnicianId]);
        
        if ($orderTechniciansUpdated > 0) {
            $this->command->info("✅ {$orderTechniciansUpdated} registros actualizados en order_technician");
            
            // Contar órdenes únicas afectadas
            $affectedOrders = OrderTechnician::where('technician_id', $newTechnicianId)
                ->distinct('order_id')
                ->count('order_id');
            $this->command->info("   Órdenes afectadas: {$affectedOrders}");
        } else {
            $this->command->warn("⚠️  No se encontraron registros con el técnico antiguo en order_technician");
        }

        $this->command->newLine();

        // Actualizar TODOS los contratos con el técnico antiguo
        $contractTechniciansUpdated = ContractTechnician::where('technician_id', $oldTechnicianId)
            ->update(['technician_id' => $newTechnicianId]);
        
        if ($contractTechniciansUpdated > 0) {
            $this->command->info("✅ {$contractTechniciansUpdated} registros actualizados en contract_technician");
            
            // Contar contratos únicos afectados
            $affectedContracts = ContractTechnician::where('technician_id', $newTechnicianId)
                ->distinct('contract_id')
                ->count('contract_id');
            $this->command->info("   Contratos afectados: {$affectedContracts}");
        } else {
            $this->command->warn("⚠️  No se encontraron registros con el técnico antiguo en contract_technician");
        }

        $this->command->newLine();
        $this->command->info("🎉 Reasignación global completada exitosamente");
    }
}
