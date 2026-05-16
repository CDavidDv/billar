<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TableSession;
use Carbon\Carbon;

class SessionBillingService
{
    public function calculateTimeCost(TableSession $session, ?Carbon $asOf = null): array
    {
        $closedAt = $asOf ?? now();
        $rawMinutes = (int) $session->opened_at->diffInMinutes($closedAt);

        // Subtract already-accumulated paused minutes + current pause (if still paused)
        $pausedMinutes = (int) ($session->paused_minutes ?? 0);
        if ($session->status === 'paused' && $session->paused_at) {
            $pausedMinutes += (int) $session->paused_at->diffInMinutes($closedAt);
        }

        $minutes = max(0, $rawMinutes - $pausedMinutes);

        if ($session->billing_type === 'precio_fijo') {
            return [
                'minutes' => $minutes,
                'time_cost' => (float) $session->hourly_rate,
                'is_paused' => $session->status === 'paused',
            ];
        }

        $hours = $minutes / 60;
        $cost = round($hours * (float) $session->hourly_rate, 2);

        return [
            'minutes' => $minutes,
            'time_cost' => $cost,
            'is_paused' => $session->status === 'paused',
        ];
    }

    public function pauseSession(TableSession $session): void
    {
        if ($session->status !== 'active') {
            return;
        }
        $session->update(['status' => 'paused', 'paused_at' => now()]);
    }

    public function resumeSession(TableSession $session): void
    {
        if ($session->status !== 'paused' || ! $session->paused_at) {
            return;
        }
        $extraMinutes = (int) $session->paused_at->diffInMinutes(now());
        $session->update([
            'status' => 'active',
            'paused_minutes' => ($session->paused_minutes ?? 0) + $extraMinutes,
            'paused_at' => null,
        ]);
    }

    public function closeSession(TableSession $session, string $paymentMethod): Order
    {
        $now = now();
        $billing = $this->calculateTimeCost($session, $now);

        $session->update([
            'closed_at' => $now,
            'closed_by' => auth()->id(),
            'time_minutes' => $billing['minutes'],
            'time_cost' => $billing['time_cost'],
            'status' => 'closed',
        ]);

        $order = $session->order;
        $order->time_cost = $billing['time_cost'];
        $order->total = (float) $order->subtotal + $billing['time_cost'] - (float) $order->discount;
        $order->status = 'closed';
        $order->closed_by = auth()->id();
        $order->closed_at = $now;
        $order->payment_method = $paymentMethod;
        $order->save();

        return $order;
    }
}
