@props(['plan'])

<div class="plan-card" x-on:click="selectedPlan = '{{ $plan->plan_id }}'; setPlanId(selectedPlan)"
    x-bind:class="{ 'bg-bittersweet-200 border border-bittersweet-300': selectedPlan === '{{ $plan->plan_id }}' }">
    <h3 class="mb-4 text-xl font-bold tracking-widest uppercase text-bittersweet-500">{{ $plan->name }}</h3>
    <p class="mb-4 text-sm">{{ $plan->description }}</p>
    <p class="mb-4 text-3xl font-bold tracking-widest uppercase">
        {{ $plan->price }} USD</p>
</div>
