@props(['date'])

<span class="font-bold">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</span>
