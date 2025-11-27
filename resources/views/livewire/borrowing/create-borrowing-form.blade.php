@script
<script>
    // [FIX] Kita bungkus dalam event listener 'alpine:init'
    // Ini memastikan Alpine sudah siap sebelum kita mendaftarkan data komponen.
    // Mencegah error "ReferenceError: Alpine is not defined".
    document.addEventListener('alpine:init', () => {
        Alpine.data('borrowingFormPersistence', () => ({
            showRestoreBanner: false,
            lastSaved: null,

            initPersistence() {
                this.checkDraft();

                // Auto-save setiap 3 detik jika ada perubahan
                // Menggunakan setInterval agar tidak terlalu membebani browser
                setInterval(() => {
                    this.saveDraft();
                }, 3000);

                // Listener event dari Livewire Component (PHP)
                // Saat peminjaman berhasil disimpan, draft lokal dihapus
                $wire.on('borrowing-saved', () => {
                    this.discardDraft();
                });
            },

            async saveDraft() {
                try {
                    // Mengambil data reaktif dari Livewire menggunakan $wire.get()
                    // Ini lebih stabil daripada @this.get() di dalam modul script
                    let data = {
                        userSearch: await $wire.get('userSearch'),
                        user_id: await $wire.get('user_id'),
                        borrow_date: await $wire.get('borrow_date'),
                        expected_return_date: await $wire.get('expected_return_date'),
                        purpose: await $wire.get('purpose'),
                        notes: await $wire.get('notes'),
                        rows: await $wire.get('rows'),
                        timestamp: new Date().toLocaleString()
                    };

                    // Hanya simpan jika user sudah mulai mengisi data
                    // Mencegah penyimpanan draft kosong yang tidak berguna
                    if (data.user_id || data.purpose || (data.rows && data.rows.length > 0)) {
                        localStorage.setItem('borrowing_draft_v1', JSON.stringify(data));

                        // Update tampilan waktu simpan terakhir
                        this.lastSaved = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    }
                } catch (error) {
                    console.warn('Gagal menyimpan draft:', error);
                }
            },

            checkDraft() {
                try {
                    let draft = localStorage.getItem('borrowing_draft_v1');
                    if (draft) {
                        let data = JSON.parse(draft);
                        // Jika draft ditemukan, tampilkan banner notifikasi
                        this.showRestoreBanner = true;
                        this.lastSaved = data.timestamp;
                    }
                } catch (e) {
                    console.error('Error checking draft', e);
                }
            },

            restoreDraft() {
                let draft = localStorage.getItem('borrowing_draft_v1');
                if (draft) {
                    let data = JSON.parse(draft);

                    // Mengembalikan data ke Livewire component
                    // $wire.set() akan memicu update di sisi server juga
                    $wire.set('userSearch', data.userSearch);
                    $wire.set('user_id', data.user_id);
                    $wire.set('borrow_date', data.borrow_date);
                    $wire.set('expected_return_date', data.expected_return_date);
                    $wire.set('purpose', data.purpose);
                    $wire.set('notes', data.notes);
                    $wire.set('rows', data.rows);

                    this.showRestoreBanner = false;
                }
            },

            discardDraft() {
                localStorage.removeItem('borrowing_draft_v1');
                this.showRestoreBanner = false;
            },

            clearDraft() {
                this.discardDraft();
            }
        }));
    });
</script>
@endscript

{{-- Container Utama --}}
<div class="min-h-screen bg-gray-50/50 dark:bg-gray-900 pb-20 font-sans"
     x-data="borrowingFormPersistence()"
     x-init="initPersistence()">

    {{-- Header Utama (Sticky) --}}
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-30 shadow-sm backdrop-blur-md bg-white/90 dark:bg-gray-800/90">
        <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl">
                    {{-- Icon Dokumen --}}
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                        Buat Peminjaman
                    </h1>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Formulir peminjaman aset baru</p>

                        {{-- Indikator Teks "Tersimpan" (Muncul via Alpine x-show) --}}
                        <span x-show="lastSaved"
                              x-transition.opacity.duration.500ms
                              class="text-[10px] text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full flex items-center gap-1"
                              style="display: none;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Tersimpan <span x-text="lastSaved"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Loading Indicator (Muncul saat Livewire memproses request) --}}
            <div wire:loading class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-full text-xs font-bold animate-pulse border border-indigo-100 dark:border-indigo-800">
                <svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                MEMPROSES...
            </div>
        </div>
    </div>

    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Banner Restore Draft (Muncul jika ada data tersimpan di localStorage) --}}
        <div x-show="showRestoreBanner"
             x-transition
             class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm flex justify-between items-center"
             style="display: none;">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <div>
                    <p class="text-sm text-blue-700 font-medium">Kami menemukan data peminjaman yang belum disimpan.</p>
                    <p class="text-xs text-blue-600">Terakhir diubah pada <span x-text="lastSaved"></span></p>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="discardDraft()" type="button" class="text-xs font-medium text-gray-500 hover:text-gray-700 px-3 py-1.5 hover:bg-gray-100 rounded-md transition-colors">Abaikan</button>
                <button @click="restoreDraft()" type="button" class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md shadow-sm transition-colors">Pulihkan Data</button>
            </div>
        </div>

        {{-- Layout Grid: Kiri (5) vs Kanan (7) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- PANEL KIRI: PENCARIAN BARANG (5 Kolom) --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- Search Input Container --}}
                <div class="relative group">
                    {{-- Icon Kaca Pembesar --}}
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    {{-- Input Field --}}
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        class="block w-full pl-14 pr-12 py-4 bg-white dark:bg-gray-800 border-0 ring-1 ring-gray-200 dark:ring-gray-700 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 shadow-lg shadow-gray-200/50 dark:shadow-none text-lg transition-all placeholder:text-base"
                        placeholder="Cari nama barang atau kode aset..."
                        autofocus
                    >

                    {{-- Tombol Clear (X) --}}
                    @if($searchQuery)
                        <button wire:click="$set('searchQuery', '')" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-red-500 cursor-pointer transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    @endif
                </div>

                {{-- Area Hasil Pencarian --}}
                <div class="min-h-[300px] relative">

                    {{-- Loading State Overlay --}}
                    <div wire:loading wire:target="searchQuery" class="absolute inset-0 z-10 bg-gray-50/50 dark:bg-gray-900/50 backdrop-blur-[1px] rounded-xl flex flex-col items-center justify-center text-gray-500 transition-all duration-300">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-full shadow-lg mb-3">
                            <svg class="w-8 h-8 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <span class="text-sm font-medium bg-white dark:bg-gray-800 px-3 py-1 rounded-full shadow-sm">Mencari data...</span>
                    </div>

                    {{-- Kondisi 1: Ada Hasil Pencarian --}}
                    @if(count($searchResults) > 0)
                        <div class="grid grid-cols-1 gap-4" wire:loading.remove wire:target="searchQuery">
                            @foreach($searchResults as $result)
                                <div
                                    wire:click="selectItem({{ $result['id'] }})"
                                    class="group cursor-pointer bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-lg hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-200 relative overflow-hidden"
                                >
                                    {{-- Dekorasi Background Hover --}}
                                    <div class="absolute top-0 right-0 w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-bl-full -mr-4 -mt-4 transition-colors group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20"></div>

                                    <div class="relative flex justify-between items-start gap-4">
                                        <div class="flex-1 min-w-0"> {{-- min-w-0 agar truncate berfungsi --}}
                                            {{-- Badge Tipe --}}
                                            <div class="mb-2">
                                                @if($result['type'] === \App\Enums\ItemType::Fixed)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                                        📦 Aset Tetap
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-green-50 text-green-700 border border-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
                                                        💧 Habis Pakai
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Nama Barang --}}
                                            <h3 class="font-bold text-gray-900 dark:text-white text-base group-hover:text-indigo-600 transition-colors truncate">
                                                {{ $result['name'] }}
                                            </h3>

                                            {{-- Kode Barang --}}
                                            <p class="text-xs font-mono text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-2 py-0.5 rounded w-fit mt-1.5">
                                                {{ $result['code'] }}
                                            </p>
                                        </div>

                                        {{-- Info Stok --}}
                                        <div class="text-right flex-shrink-0 self-center bg-gray-50 dark:bg-gray-700/30 p-2 rounded-lg group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20 transition-colors">
                                            <span class="block text-lg font-bold text-gray-800 dark:text-gray-200 leading-none">{{ $result['stock_info'] }}</span>
                                            <span class="text-[10px] uppercase text-gray-400 font-semibold tracking-wider mt-1 block">Tersedia</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- Kondisi 2: Tidak Ditemukan --}}
                    @elseif(strlen($searchQuery) >= 2)
                        <div class="flex flex-col items-center justify-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 text-center" wire:loading.remove wire:target="searchQuery">
                            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-full mb-4 animate-bounce">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-gray-900 dark:text-white font-medium text-sm">Tidak ditemukan</h3>
                            <p class="text-xs text-gray-500 mt-1 max-w-[200px]">Kata kunci "{{ $searchQuery }}" tidak cocok dengan data apapun.</p>
                        </div>

                    {{-- Kondisi 3: State Awal --}}
                    @else
                        <div class="flex flex-col items-center justify-center py-24 text-center opacity-60" wire:loading.remove wire:target="searchQuery">
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-full mb-4 shadow-sm">
                                <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 font-medium text-base">Siap mencari barang</p>
                            <p class="text-xs text-gray-400 mt-1">Ketik minimal 2 karakter untuk memulai</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- PANEL KANAN: FORM & CART (7 Kolom - Lebih Lebar & Sticky) --}}
            <div class="lg:col-span-7 lg:sticky lg:top-24 space-y-6">

                <form wire:submit.prevent="submit" id="borrowing-form">
                    {{-- Card Utama: Tanpa max-h agar tinggi menyesuaikan konten --}}
                    <div class="bg-white dark:bg-gray-900 shadow-xl shadow-gray-200/50 dark:shadow-none rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">

                        {{-- 1. HEADER FORM (Data Peminjam & Tanggal) --}}
                        <div class="bg-gray-50/50 dark:bg-gray-800/50 p-6 border-b border-gray-200 dark:border-gray-700 space-y-6">

                            {{-- Input Pencarian Peminjam (AlpineJS Dropdown) --}}
                            <div class="relative" x-data="{ open: false }">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Nama Peminjam <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="userSearch"
                                        @focus="open = true"
                                        @blur="setTimeout(() => open = false, 200)"
                                        placeholder="Cari nama karyawan..."
                                        class="block w-full pl-11 pr-4 rounded-xl border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:bg-gray-800 dark:border-gray-700 dark:text-white py-3.5 text-sm transition-all placeholder-gray-400"
                                        autocomplete="off"
                                    >
                                    {{-- Loading Spinner Kecil --}}
                                    <div wire:loading wire:target="userSearch" class="absolute right-3 top-3.5">
                                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>
                                </div>

                                {{-- Dropdown Hasil --}}
                                @if(!empty($userSearchResults) && count($userSearchResults) > 0)
                                    <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-60 overflow-auto py-2">
                                        @foreach($userSearchResults as $user)
                                            <div wire:click="selectUser({{ $user['id'] }})" class="px-5 py-3 hover:bg-indigo-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors group">
                                                <div class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-indigo-700 dark:group-hover:text-indigo-300">{{ $user['name'] }}</div>
                                                <div class="text-xs text-gray-500 group-hover:text-indigo-500">{{ $user['email'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @error('user_id') <p class="text-red-500 text-xs mt-2 font-medium pl-1 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tgl. Pinjam</label>
                                    <input type="datetime-local" wire:model="borrow_date" class="block w-full rounded-xl border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white py-3.5 px-4 transition-all">
                                    @error('borrow_date') <p class="text-red-500 text-xs mt-2 pl-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tgl. Kembali</label>
                                    <input type="datetime-local" wire:model="expected_return_date" class="block w-full rounded-xl border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white py-3.5 px-4 transition-all">
                                    @error('expected_return_date') <p class="text-red-500 text-xs mt-2 pl-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 2. LIST BARANG (Tabel Responsif) --}}
                        <div class="flex-1 p-0 bg-white dark:bg-gray-900 flex flex-col min-h-[300px]">
                            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-white dark:bg-gray-900 sticky top-0 z-20">
                                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-sm uppercase tracking-wide">
                                    <span class="bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 text-xs font-extrabold px-2.5 py-0.5 rounded-md">{{ count($rows) }}</span>
                                    Item Dipilih
                                </h3>
                                <span class="text-xs text-gray-400 hidden sm:inline">Pastikan stok & unit sesuai</span>
                            </div>

                            @error('rows')
                                <div class="mx-6 mt-4 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3 animate-pulse">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
                                </div>
                            @enderror

                            @if(count($rows) === 0)
                                <div class="flex flex-col items-center justify-center py-16 text-center opacity-60">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4 shadow-inner">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <p class="text-gray-500 font-medium">Keranjang masih kosong</p>
                                    <p class="text-xs text-gray-400 mt-1">Klik barang di panel kiri untuk menambahkan</p>
                                </div>
                            @else
                                {{-- Container Tabel dengan Scroll Horizontal --}}
                                <div class="overflow-x-auto w-full">
                                    <table class="w-full text-left border-collapse min-w-[600px]"> {{-- min-w agar tidak penyet di mobile --}}
                                        <thead>
                                            <tr class="border-b border-gray-100 dark:border-gray-800 text-[11px] uppercase tracking-wider text-gray-500 bg-gray-50/50 dark:bg-gray-800/50">
                                                <th class="px-6 py-3 font-semibold w-12 text-center">#</th>
                                                <th class="px-6 py-3 font-semibold w-1/4">Barang</th>
                                                <th class="px-6 py-3 font-semibold w-1/2">Detail Unit / Lokasi</th>
                                                <th class="px-6 py-3 font-semibold text-center w-24">Qty</th>
                                                <th class="px-6 py-3 font-semibold w-12"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                            @foreach($rows as $index => $row)
                                                <tr class="group hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors" wire:key="row-{{ $index }}">

                                                    {{-- No --}}
                                                    <td class="px-6 py-4 text-xs text-gray-400 font-mono align-top pt-6 text-center">
                                                        {{ $index + 1 }}
                                                    </td>

                                                    {{-- Info Barang --}}
                                                    <td class="px-6 py-4 align-top">
                                                        <div class="font-bold text-sm text-gray-900 dark:text-white line-clamp-2">{{ $row['item_name'] }}</div>
                                                        <div class="text-xs text-gray-500 font-mono mt-1">{{ $row['item_code'] }}</div>
                                                        <div class="mt-2">
                                                            @if($row['type'] === 'fixed')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">Aset Tetap</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-green-50 text-green-700 border border-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">Habis Pakai</span>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    {{-- Dropdown Detail --}}
                                                    <td class="px-6 py-4 align-top">
                                                        <div class="relative mt-1">
                                                            <select
                                                                wire:model.live="rows.{{ $index }}.selected_detail_id"
                                                                wire:change="updateRowDetail({{ $index }}, $event.target.value)"
                                                                class="block w-full rounded-lg border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5 pl-3 pr-8 text-xs transition-all appearance-none cursor-pointer shadow-sm"
                                                            >
                                                                <option value="">-- {{ $row['type'] === 'fixed' ? 'Pilih Unit Aset' : 'Pilih Lokasi Stok' }} --</option>
                                                                @foreach($row['available_options'] as $opt)
                                                                    <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            {{-- Icon Chevron --}}
                                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                            </div>
                                                        </div>
                                                        @error("rows.{$index}.selected_detail_id")
                                                            <span class="text-red-500 text-[10px] font-bold flex items-center gap-1 mt-2 animate-pulse">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                Wajib dipilih
                                                            </span>
                                                        @enderror
                                                    </td>

                                                    {{-- Input Qty --}}
                                                    <td class="px-6 py-4 align-top">
                                                        <div class="flex flex-col items-center">
                                                            <input type="number"
                                                                wire:model="rows.{{ $index }}.quantity"
                                                                min="1"
                                                                max="{{ $row['max_quantity'] }}"
                                                                {{ $row['type'] === 'fixed' ? 'disabled' : '' }}
                                                                class="block w-20 text-center rounded-lg border-gray-200 bg-white text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 font-bold disabled:text-gray-400 disabled:bg-gray-50 disabled:cursor-not-allowed transition-all shadow-sm"
                                                            >
                                                            @if($row['type'] === 'consumable')
                                                                <div class="text-[10px] text-gray-400 mt-1 font-medium whitespace-nowrap bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">Max: {{ $row['max_quantity'] }}</div>
                                                            @endif
                                                        </div>
                                                    </td>

                                                    {{-- Tombol Hapus --}}
                                                    <td class="px-6 py-4 align-middle text-right">
                                                        <button type="button" wire:click="removeRow({{ $index }})" class="text-gray-300 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all transform hover:scale-110" title="Hapus Item">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- 3. FOOTER ACTIONS --}}
                        <div class="p-6 bg-gray-50/80 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 space-y-6 backdrop-blur-sm">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Keperluan Peminjaman <span class="text-red-500">*</span></label>
                                <textarea wire:model="purpose" rows="3" class="block w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white resize-none py-3 px-4 transition-all" placeholder="Jelaskan keperluan secara singkat..."></textarea>
                                @error('purpose') <p class="text-red-500 text-xs mt-2 pl-1 font-medium flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Catatan Tambahan</label>
                                <input type="text" wire:model="notes" class="block w-full rounded-xl border-gray-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white py-3 px-4 transition-all" placeholder="Opsional">
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    @click="clearDraft()"
                                    wire:loading.attr="disabled"
                                    class="w-full flex justify-center items-center gap-2 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-indigo-500/30 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 disabled:opacity-70 disabled:cursor-not-allowed transform transition-all active:scale-[0.99] hover:shadow-xl">
                                    <span wire:loading.remove class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                        SIMPAN PEMINJAMAN
                                    </span>
                                    <span wire:loading class="flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        MENYIMPAN DATA...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- Script AlpineJS sudah ada di Bagian 1 --}}
</div>
