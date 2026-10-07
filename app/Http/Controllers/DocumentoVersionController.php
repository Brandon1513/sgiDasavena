<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentoBajaMailable;
use App\Models\User;

class DocumentoVersionController extends Controller
{
    public function show(DocumentoVersion $version)
    {
        $version->load([
            'documento',
            // si tienes revisiones
            // 'revisiones' => fn($q) => $q->orderByDesc('id'),
        ]);

        $documento = $version->documento;

        return view('documentos.versiones.show', compact('documento', 'version'));
    }

public function marcarObsoleto(DocumentoVersion $version)
{
    $user = auth()->user();

    if (!$user->hasRole('administrador_sgi') && !$user->hasRole('administrador')) {
        abort(403);
    }

    $version->update([
        'estatus' => 'obsoleto'
    ]);

    // enviar correo a admins SGI
    $documento = $version->documento;
    $admins = User::role('administrador_sgi')->get();

    foreach ($admins as $admin) {
        if ($admin->email) {
            try {
                Mail::to($admin->email)->send(new DocumentoBajaMailable($documento));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('No se pudo enviar la notificación de versión obsoleta.', [
                    'documento_id' => $documento->id,
                    'email' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    return back()->with('success', 'Versión marcada como obsoleta y notificada');
}
}
