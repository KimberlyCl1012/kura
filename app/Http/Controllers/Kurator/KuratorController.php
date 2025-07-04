<?php

namespace App\Http\Controllers\Kurator;

use App\Http\Controllers\Controller;
use App\Models\Kurator;
use App\Models\Site;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Str;

class KuratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kurators = DB::table('kurators')
            ->join('user_details', 'user_details.id', '=', 'kurators.user_detail_id')
            ->join('users', 'user_details.user_id', '=', 'users.id')
            ->join('list_sites', 'user_details.site_id', '=', 'list_sites.id')
            ->select(
                'kurators.id as id',
                'kurators.user_uuid',
                'kurators.specialty',
                'kurators.type_kurator',
                'kurators.type_identification',
                'kurators.identification',
                'kurators.state',
                'user_details.name',
                'user_details.fatherLastName',
                'user_details.motherLastName',
                'user_details.sex',
                'user_details.site_id',
                'user_details.mobile',
                'user_details.contactEmail',
                'list_sites.siteName',
                'users.email'
            )
            ->get();

        $sites = DB::table('list_sites')->select('id', 'siteName')->get();

        return Inertia::render('Kurators/Index', [
            'kurators' => $kurators,
            'sites' => $sites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Hombre,Mujer',
            'mobile' => 'nullable|string|max:20',
            'site_id' => 'required|exists:list_sites,id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'specialty' => 'required|string|max:255',
            'type_kurator' => 'required|string|max:50',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $userDetail = UserDetail::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'fatherLastName' => $request->fatherLastName,
                'motherLastName' => $request->motherLastName,
                'sex' => $request->sex,
                'mobile' => $request->mobile,
                'contactEmail' => $request->email,
                'site_id' => $request->site_id,
            ]);

            $anio = Carbon::now()->format('Y');
            $site = str_pad($userDetail->site_id, 2, '0', STR_PAD_LEFT);
            $random = strtoupper(Str::random(3));

            Kurator::create([
                'user_uuid' => 'KU' . $anio . '-' . $site . $random,
                'user_detail_id' => $userDetail->id,
                'specialty' => $request->specialty,
                'type_kurator' => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Kurador creado correctamente.');
        } catch (\Throwable $e) {
            dd($e);

            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al crear el kurador.'])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $kurator = Kurator::findOrFail($id);
        $userDetail = $kurator->userDetail;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fatherLastName' => 'required|string|max:255',
            'motherLastName' => 'nullable|string|max:255',
            'sex' => 'required|string|in:Hombre,Mujer',
            'mobile' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userDetail->user_id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('user_details', 'contactEmail')->ignore($userDetail->id),
            ],
            'site_id' => 'required|exists:list_sites,id',
            'specialty' => 'required|string|max:255',
            'type_kurator' => 'required|string|max:50',
            'type_identification' => 'required|string|max:50',
            'identification' => 'required|string|max:50',
        ]);

        DB::beginTransaction();

        try {

            DB::table('users')
                ->where('id', $userDetail->user_id)
                ->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            DB::table('user_details')
                ->where('id', $userDetail->id)
                ->update([
                    'name' => $request->name,
                    'fatherLastName' => $request->fatherLastName,
                    'motherLastName' => $request->motherLastName,
                    'sex' => $request->sex,
                    'mobile' => $request->mobile,
                    'contactEmail' => $request->email,
                    'site_id' => $request->site_id,
                    'updated_at' => now(),
                ]);

            $kurator->update([
                'specialty' => $request->specialty,
                'type_kurator' => $request->type_kurator,
                'type_identification' => $request->type_identification,
                'identification' => $request->identification,
            ]);

            DB::commit();

            return redirect()->route('kurators.index')->with('success', 'Kurador actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al actualizar el kurador.'])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $kurator = Kurator::findOrFail($id);
            $userDetail = $kurator->userDetail;
            $userId = $userDetail->user_id;

            DB::beginTransaction();

            $kurator->delete();
            $userDetail->delete();
            DB::table('users')->where('id', $userId)->delete();

            DB::commit();

            return back()->with('success', 'Kurador eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return back()->withErrors(['error' => 'Error al eliminar el kurador.']);
        }
    }
}
