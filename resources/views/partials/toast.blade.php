<div
    x-data="{ show: false, message: '', heading: '' }"
    x-on:toast-success.window="heading = $event.detail[0].heading; message = $event.detail[0].message; show = true; setTimeout(() => show = false, 4000)"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-6 right-6 z-[9999] bg-white border border-green-200 rounded-xl shadow-lg px-5 py-4 flex items-start gap-3 min-w-[280px]"
    x-cloak
>
    <span class="text-green-500 text-xl">🎁</span>
    <div>
        <p class="font-semibold text-sm" style="color: #1f2937;" x-text="heading"></p>
        <p class="text-xs mt-0.5" style="color: #6b7280;" x-text="message"></p>
    </div>
</div>
