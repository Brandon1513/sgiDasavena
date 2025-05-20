<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->with('roles')->paginate(10)->appends(['search' => $request->search]);

        return view('usuarios.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $usuarios = User::role('jefe')->get(); // Solo mostrar usuarios con rol jefe
        return view('usuarios.create', compact('roles', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'area' => 'nullable|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'area' => $request->area,
            'puesto' => $request->puesto,
            'jefe_id' => $request->jefe_id,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $usuarios = User::role('jefe')->get(); // Solo mostrar usuarios con rol jefe
        return view('usuarios.edit', compact('user', 'roles', 'usuarios'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'area' => 'nullable|string|max:255',
            'puesto' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        // Datos básicos
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'area' => $request->area,
            'puesto' => $request->puesto,
            'jefe_id' => $request->jefe_id,
        ];

        // Si se envía una nueva contraseña
        if (!empty($request->password)) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        $user->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(User $user)
    {
        $user->activo = !$user->activo;
        $user->save();

        return redirect()->route('usuarios.index')->with('success', 'Estado actualizado correctamente.');
    }
}
