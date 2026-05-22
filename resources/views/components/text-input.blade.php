@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-slate-200 bg-white text-slate-900 placeholder:text-slate-400 focus:border-blue-400 focus:ring focus:ring-blue-100 rounded-2xl shadow-sm']) }}>
