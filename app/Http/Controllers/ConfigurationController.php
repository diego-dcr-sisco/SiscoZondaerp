<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppearanceSetting;

class ConfigurationController extends Controller
{
    public function index() {
        return view('configuration.index');
    }

    public function appearance() {
        // Obtener la configuración actual o crear una por defecto
        $appearance = AppearanceSetting::first();
        
        if (!$appearance) {
            $appearance = new AppearanceSetting();
        }
        
        return view('configuration.system.appearance', compact('appearance'));
    }

    public function updateAppearance(Request $request) {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'watermark' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
        ]);

        // Obtener o crear la configuración
        $appearance = AppearanceSetting::first();
        
        if (!$appearance) {
            $appearance = new AppearanceSetting();
        }

        // Manejar la carga del logo si se proporciona
        if ($request->hasFile('logo')) {
            $logoPath = public_path('images/logo_reporte.png');

            // Si existe el logo actual, eliminarlo
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }

            // Guardar el nuevo logo
            $request->file('logo')->move(public_path('images'), 'logo_reporte.png');
            
            // Guardar la ruta en la base de datos
            $appearance->logo_path = 'images/logo_reporte.png';
        }

        // Manejar la carga de la marca de agua si se proporciona
        if ($request->hasFile('watermark')) {
            $watermarkPath = public_path('images/watermark.png');

            // Si existe la marca de agua actual, eliminarla
            if (file_exists($watermarkPath)) {
                unlink($watermarkPath);
            }

            // Guardar la nueva marca de agua
            $request->file('watermark')->move(public_path('images'), 'watermark.png');

            // Guardar la ruta en la base de datos
            $appearance->watermark_path = 'images/watermark.png';
        }

        // Actualizar los colores
        $appearance->primary_color = $request->primary_color;
        $appearance->secondary_color = $request->secondary_color;
        $watermarkOpacity = $request->input('watermark_opacity', 10); 
        $appearance->watermark_opacity = $watermarkOpacity / 100; //Convertir valor a decimal
       
        
        $appearance->save();


        return redirect()->route('config.appearance')->with('success', 'Apariencia actualizada correctamente.');
    }
}