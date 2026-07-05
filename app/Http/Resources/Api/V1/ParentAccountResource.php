<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'invited_at' => $this->invited_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'app' => $this->app?->value,
            'is_vvip' => $this->is_vvip,
            'account_type' => $this->account_type?->value,
            'account_balance' => $this->pointsBalance(),
            'discount_percent' => $this->discountPercent(),
            'discount_cap_percent' => config('loyalty.app_two.discount_cap_bp') / 100,
        ];
    }
}
