<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index');
    }

    public function store(Request $request)
    {
    // Modificar el Request
    $request->request->add(['username' => Str::slug($request->username)]);
        // Si son mas de 3 aatributos es mejor usar un array
    $request->validate([
        'username' => ['required', 'unique:users,username,'.auth()->user()->id, 'min:3', 'max:20', 'not_in:twitter,editar-perfil'],
    ]);

    if($request->imagen) {
        $imagen = $request->file('imagen');
 
        $nombreImagen = Str::uuid() . "." . $imagen->extension();
 
        $manager = new ImageManager(new Driver());
        $imagenServidor = $manager::gd()->read($imagen);
        $imagenServidor->resizeDown(1000, 1000);
 
        $imagenPath = public_path('perfiles') . '/' . $nombreImagen;
        
        $imagenServidor->toPng()->save($imagenPath);
    } 
        // Guardar cambios
        $usuario = User::find(auth()->user()->id);
        $usuario->username = $request->username;
        $usuario->imagen = $nombreImagen ?? auth()->user()->imagen ?? null;
        $usuario->save();

        // Redireccionar al usuario
        return redirect()->route('posts.index', $usuario->username);

    }
}
