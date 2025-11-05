<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Tambah Akun Baru</h2>
    </x-slot>

    <div class="bg-white shadow rounded-lg p-6 max-w-lg">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Nama</label>
                <input type="text" name="name" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" name="password" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Peran</label>
                <select name="peran" class="w-full border rounded p-2" required>
                    <option value="pembuat_gatepass">Pembuat Gatepass</option>
                    <option value="kontraktor">Kontraktor</option>
                    <option value="security">Security</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Simpan
            </button>
        </form>
    </div>
</x-app-layout>
