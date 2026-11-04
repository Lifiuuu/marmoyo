@extends('layouts.admin')

@section('title', 'Kelola Staf & Pengguna - Kedai Mie Marmoyo')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-stone-900">Kelola Staf &amp; Akun Pengguna</h2>
            <p class="text-xs text-stone-500 mt-1">Manajemen kredensial dan hak akses internal sistem POS tanpa registrasi publik.</p>
        </div>
        <button onclick="openCreateUserModal()" class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow active:scale-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>+ Tambah Staf Baru</span>
        </button>
    </div>

    <!-- Alert / Flash Message -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium px-4 py-3 rounded-2xl shadow-xs">
            <p class="font-bold">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metric Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Total Staf Terdaftar</p>
            <p class="text-2xl font-black text-stone-900 mt-1">{{ $users->count() }}</p>
            <span class="text-[10px] text-stone-500 mt-0.5 inline-block">Seluruh akun internal sistem</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Staf Kasir POS</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $cashierCount }}</p>
            <span class="text-[10px] text-stone-500 mt-0.5 inline-block">Akses terminal transaksi &amp; live orders</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Administrator / Owner</p>
            <p class="text-2xl font-black text-red-600 mt-1">{{ $adminCount }}</p>
            <span class="text-[10px] text-stone-500 mt-0.5 inline-block">Akses penuh dashboard &amp; keuangan</span>
        </div>
    </div>

    <!-- Security Info Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900 flex items-start space-x-3">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="space-y-1">
            <p class="font-bold">Keamanan Akun Internal:</p>
            <p class="text-[11px] text-amber-800 leading-relaxed">
                Sistem Marmoyo menggunakan otentikasi tertutup (*Closed-loop*). Pelanggan tidak dapat mendaftar sendiri dari luar. Semua akun kasir baru wajib dibuat oleh Owner dari halaman ini. Pastikan memberikan password sementara yang aman kepada kasir.
            </p>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <h3 class="font-extrabold text-stone-900 text-sm">Daftar Akun Pengguna</h3>
            <span class="text-xs text-stone-400">{{ $users->count() }} Pengguna Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-stone-100 text-stone-400 uppercase font-semibold">
                        <th class="pb-2.5">Nama &amp; Kredensial</th>
                        <th class="pb-2.5">Hak Akses (Role)</th>
                        <th class="pb-2.5">Terdaftar Sejak</th>
                        <th class="pb-2.5">Status</th>
                        <th class="pb-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3.5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-xs shadow-xs {{ $user->role === 'admin' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-stone-900 text-xs">{{ $user->name }}</p>
                                        <p class="font-mono text-[11px] text-stone-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        Owner / Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Staf Kasir
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 text-stone-500 font-mono text-[11px]">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3.5">
                                <span class="inline-flex items-center space-x-1.5 text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md text-[10px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Aktif</span>
                                </span>
                            </td>
                            <td class="py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <!-- Tombol Edit & Ganti Password -->
                                <button type="button" 
                                    onclick='openEditUserModal(@json($user))'
                                    class="inline-flex items-center space-x-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs px-2.5 py-1.5 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit / Password</span>
                                </button>

                                <!-- Tombol Hapus (Dilindungi: Akun Sendiri Tidak Bisa Dihapus) -->
                                @if(auth()->id() === $user->id)
                                    <span class="text-[10px] text-stone-400 italic font-semibold px-2 py-1 bg-stone-50 rounded-lg border border-stone-200">
                                        (Akun Anda)
                                    </span>
                                @else
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun staf \'{{ $user->name }}\'? Akun ini tidak akan dapat login lagi.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-stone-400 hover:text-rose-600 font-bold text-xs px-2 py-1.5 rounded-lg hover:bg-rose-50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-stone-400">Belum ada akun staf terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: TAMBAH STAF BARU                  -->
<!-- ========================================== -->
<div id="createUserModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div>
                <h3 class="font-black text-stone-900 text-sm">Tambah Akun Staf Baru</h3>
                <p class="text-[11px] text-stone-500">Kredensial untuk kasir atau pengelola kedai</p>
            </div>
            <button onclick="closeCreateUserModal()" class="text-stone-400 hover:text-stone-600 p-1 text-base">&times;</button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-stone-800 mb-1">Nama Lengkap Staf *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            <div>
                <label class="block font-bold text-stone-800 mb-1">Email / Username Login *</label>
                <input type="email" name="email" required placeholder="Email" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            <div>
                <label class="block font-bold text-stone-800 mb-1">Hak Akses (Role) *</label>
                <select name="role" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 font-semibold focus:outline-none focus:border-red-500">
                    <option value="kasir" selected>Staf Kasir (Hanya Akses POS &amp; Live Orders)</option>
                    <option value="admin">Owner / Administrator (Akses Penuh)</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-stone-800 mb-1">Password Sementara *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter..." class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                <p class="text-[10px] text-stone-400 mt-1">Berikan password ini kepada kasir untuk login pertama kali.</p>
            </div>

            <div class="pt-3 border-t border-stone-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 rounded-xl text-stone-600 hover:bg-stone-100 font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2 rounded-xl shadow active:scale-95 transition">
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: EDIT STAF & RESET PASSWORD       -->
<!-- ========================================== -->
<div id="editUserModal" class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div>
                <h3 class="font-black text-stone-900 text-sm">Edit Data Staf &amp; Reset Password</h3>
                <p class="text-[11px] text-stone-500">Perbarui profil atau ganti password akun staf</p>
            </div>
            <button onclick="closeEditUserModal()" class="text-stone-400 hover:text-stone-600 p-1 text-base">&times;</button>
        </div>

        <form id="editUserForm" action="" method="POST" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-stone-800 mb-1">Nama Lengkap Staf *</label>
                <input type="text" name="name" id="edit-name" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            <div>
                <label class="block font-bold text-stone-800 mb-1">Email / Username Login *</label>
                <input type="email" name="email" id="edit-email" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
            </div>

            <div>
                <label class="block font-bold text-stone-800 mb-1">Hak Akses (Role) *</label>
                <select name="role" id="edit-role" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 font-semibold focus:outline-none focus:border-red-500">
                    <option value="kasir">Staf Kasir (Hanya Akses POS &amp; Live Orders)</option>
                    <option value="admin">Owner / Administrator (Akses Penuh)</option>
                </select>
            </div>

            <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl space-y-1">
                <label class="block font-bold text-amber-900">Ganti Password (Opsional)</label>
                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak ingin ganti..." class="w-full bg-white border border-stone-200 rounded-lg px-2.5 py-2 text-stone-800 focus:outline-none focus:border-red-500">
                <p class="text-[10px] text-amber-800">Isi hanya jika staf lupa password atau ingin mengubah password.</p>
            </div>

            <div class="pt-3 border-t border-stone-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl text-stone-600 hover:bg-stone-100 font-semibold transition">
                    Batal
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-5 py-2 rounded-xl shadow active:scale-95 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }

    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-role').value = user.role;
        document.getElementById('editUserModal').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }
</script>
@endsection

