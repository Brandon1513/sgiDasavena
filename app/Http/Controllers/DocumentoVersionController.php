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
    public function show(Documento $documento, DocumentoVersion $version)
    {
        // Evita que abran una versión que no pertenece a este documento
        if ($version->documento_id !== $documento->id) {
            abort(404);
        }

        $version->load([
            'documento',
            // si tienes revisiones
            // 'revisiones' => fn($q) => $q->orderByDesc('id'),
        ]);

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

    return back()->with('success', 'Version marcada como obsoleta');
     // 📧 enviar correo a admins SGI
    $admins = User::role('administrador_sgi')->get();

    foreach ($admins as $admin) {
        Mail::to($admin->email)->send(new DocumentoBajaMailable($documento));
    }

    return back()->with('success', 'Versión marcada como obsoleta y notificada');
}
}
