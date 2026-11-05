@if($activeOrders->isEmpty())
    <div class="py-16 text-center bg-stone-900 border border-stone-800 rounded-2xl p-6">
        <div class="w-12 h-12 rounded-full bg-stone-800 text-stone-500 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-stone-300 font-bold text-xs">Semua Antrean Bersih</p>
        <p class="text-stone-500 text-[11px] mt-0.5">Tidak ada antrean pesanan aktif saat ini. Siap melayani pelanggan baru.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
        @foreach($activeOrders as $order)
            <div class="bg-stone-900 border border-stone-800 hover:border-amber-500/60 rounded-2xl p-3.5 sm:p-4 shadow-lg flex flex-col justify-between space-y-3.5 transition animate-fadeIn">
                <div>
                    <!-- Card Header: Table Number & Order Number -->
                    <div class="flex items-start justify-between border-b border-stone-800 pb-2.5 gap-2">
                        <div class="flex items-center space-x-2 min-w-0">
                            @if($order->isTakeAway())
                                <span class="text-[10px] sm:text-[11px] font-black px-2 sm:px-2.5 py-1 rounded-md bg-amber-500 text-stone-950 font-mono tracking-wider shrink-0">
                                    TAKE AWAY
                                </span>
                                <div class="min-w-0">
                                    <p class="font-extrabold text-xs text-white truncate max-w-[130px] sm:max-w-[150px]">{{ $order->customer_name ?: 'Take Away' }}</p>
                                    <p class="font-mono text-[10px] text-stone-400">#{{ $order->order_number }} &bull; {{ $order->created_at->format('H:i') }}</p>
                                </div>
                            @else
                                <span class="text-xs sm:text-sm font-black px-2 sm:px-2.5 py-1 rounded-md bg-red-600 text-white shrink-0">
                                    Meja {{ $order->table->table_number ?? '00' }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-mono text-xs font-bold text-stone-200">#{{ $order->order_number }}</p>
                                    <span class="text-[10px] text-stone-500">{{ $order->created_at->format('H:i') }} ({{ $order->created_at->diffForHumans() }})</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status Badge: Murni Menunggu -->
                        <div class="shrink-0">
                            <span class="inline-block bg-amber-950/80 text-amber-300 border border-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                                Menunggu
                            </span>
                        </div>
                    </div>

                    <!-- Item List -->
                    <div class="py-2.5 space-y-2 text-xs divide-y divide-stone-800/60">
                        @foreach($order->items as $item)
                            <div class="pt-1.5 first:pt-0">
                                <div class="flex justify-between items-baseline gap-2">
                                    <span class="font-bold text-stone-200 truncate">{{ $item->quantity }}x {{ $item->menu_name }}</span>
                                    <span class="text-stone-400 font-mono text-[11px] shrink-0">Rp{{ number_format($item->subtotal_price, 0, ',', '.') }}</span>
                                </div>
                                @if(!empty($item->spice_level))
                                    <p class="text-[10px] text-red-400 font-bold">• {{ $item->spice_level }}</p>
                                @endif
                                @if(!empty($item->noodle_type))
                                    <p class="text-[10px] text-stone-400">• {{ $item->noodle_type }}</p>
                                @endif
                                @if(!empty($item->special_notes))
                                    <p class="text-[10px] text-amber-300 italic bg-amber-950/50 border border-amber-900/50 px-1.5 py-0.5 rounded mt-1">
                                        Catatan: {{ $item->special_notes }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($order->kitchen_notes))
                        <div class="bg-stone-950 p-2 rounded-lg border border-stone-800 text-[10px] text-amber-200 mt-2">
                            <strong>Catatan Khusus:</strong> {{ $order->kitchen_notes }}
                        </div>
                    @endif
                </div>

                <!-- Card Footer & Fast Actions (Cukup 1 Tombol Selesai) -->
                <div class="pt-3 border-t border-stone-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-1.5 min-w-0">
                            <span class="text-stone-400 font-medium">Total:</span>
                            @if($order->packaging_fee > 0)
                                <span class="text-[10px] text-amber-400 font-semibold bg-amber-950/60 border border-amber-800/60 px-1.5 py-0.5 rounded truncate">+Kemasan</span>
                            @endif
                        </div>
                        <span class="text-sm font-black text-white shrink-0">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
                    </div>

                    <!-- Tindakan Kasir: Cetak Struk & Tandai Selesai (Touch-Friendly) -->
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('cashier.print.receipt', $order->id) }}" target="_blank" class="bg-stone-800 hover:bg-stone-700 active:bg-stone-600 text-stone-300 text-[11px] font-bold py-2.5 rounded-xl text-center transition flex items-center justify-center">
                            Cetak Struk
                        </a>
                        <form action="{{ route('cashier.order.status', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="selesai">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black py-2.5 rounded-xl transition shadow active:scale-95 flex items-center justify-center">
                                Selesai
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

