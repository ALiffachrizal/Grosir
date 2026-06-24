<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan seluruh akun pengguna.
     */
    public function index()
    {
        $users = User::orderBy('role')
            ->orderBy('username')
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],

            'role' => [
                'required',
                'in:admin,cashier,warehouse',
            ],
        ], [
            'username.required' =>
                'Username wajib diisi.',

            'username.string' =>
                'Username harus berupa teks.',

            'username.max' =>
                'Username maksimal 255 karakter.',

            'username.unique' =>
                'Username sudah digunakan.',

            'password.required' =>
                'Password wajib diisi.',

            'password.string' =>
                'Password harus berupa teks.',

            'password.min' =>
                'Password minimal 6 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak cocok.',

            'role.required' =>
                'Role wajib dipilih.',

            'role.in' =>
                'Role tidak valid.',
        ]);

        $username = trim($validated['username']);

        User::create([
            'username' => $username,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User ' . $username . ' berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],

            'role' => [
                'required',
                'in:admin,cashier,warehouse',
            ],
        ], [
            'username.required' =>
                'Username wajib diisi.',

            'username.string' =>
                'Username harus berupa teks.',

            'username.max' =>
                'Username maksimal 255 karakter.',

            'username.unique' =>
                'Username sudah digunakan.',

            'password.string' =>
                'Password harus berupa teks.',

            'password.min' =>
                'Password minimal 6 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak cocok.',

            'role.required' =>
                'Role wajib dipilih.',

            'role.in' =>
                'Role tidak valid.',
        ]);

        $username = trim($validated['username']);

        $data = [
            'username' => $username,
            'role' => $validated['role'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Password bersifat opsional saat edit
        |--------------------------------------------------------------------------
        |
        | Jika password tidak diisi, password lama tetap digunakan.
        |
        */
        if ($request->filled('password')) {
            $data['password'] = Hash::make(
                $validated['password']
            );
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User ' . $username . ' berhasil diperbarui.'
            );
    }

    /**
     * Menghapus akun pengguna.
     */
    public function destroy(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | Cegah pengguna menghapus akun sendiri
        |--------------------------------------------------------------------------
        */
        if ((int) $user->id === (int) auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'Tidak bisa menghapus akun yang sedang digunakan.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Hitung seluruh riwayat pengguna
        |--------------------------------------------------------------------------
        |
        | Foreign key pada tabel transaksi menggunakan restrictOnDelete().
        | Karena itu akun yang memiliki riwayat tidak boleh dihapus.
        |
        */
        $purchaseOrderCount = $user->purchaseOrders()->count();
        $saleCount = $user->sales()->count();
        $refundCount = $user->refunds()->count();
        $stockLogCount = $user->stockLogs()->count();

        $usedIn = [];

        if ($purchaseOrderCount > 0) {
            $usedIn[] = $purchaseOrderCount . ' purchase order';
        }

        if ($saleCount > 0) {
            $usedIn[] = $saleCount . ' transaksi penjualan';
        }

        if ($refundCount > 0) {
            $usedIn[] = $refundCount . ' proses refund';
        }

        if ($stockLogCount > 0) {
            $usedIn[] = $stockLogCount . ' riwayat stok';
        }

        /*
        |--------------------------------------------------------------------------
        | Tolak penghapusan jika memiliki riwayat
        |--------------------------------------------------------------------------
        */
        if (!empty($usedIn)) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'User "' . $user->username .
                    '" tidak dapat dihapus karena memiliki riwayat: ' .
                    implode(', ', $usedIn) .
                    '. Data riwayat harus tetap tersimpan.'
                );
        }

        $username = $user->username;

        /*
        |--------------------------------------------------------------------------
        | Hapus akun
        |--------------------------------------------------------------------------
        |
        | QueryException tetap ditangani untuk mencegah halaman error jika
        | ternyata masih terdapat relasi database lain.
        |
        */
        try {
            $user->delete();
        } catch (QueryException $exception) {
            report($exception);

            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'User "' . $username .
                    '" tidak dapat dihapus karena masih digunakan oleh data lain.'
                );
        }

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User ' . $username . ' berhasil dihapus.'
            );
    }
}