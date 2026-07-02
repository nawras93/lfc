<?php

namespace App\Services;

use Carbon\Carbon;

class CandidateDataNormalizer
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        if (! empty($data['date_of_birth'])) {
            $data['year_of_birth'] = Carbon::parse($data['date_of_birth'])->year;
        }

        $consentGiven = (bool) ($data['consent_given'] ?? false);

        if ($consentGiven) {
            $data['consent_at'] ??= now();
        } else {
            $data['consent_at'] = null;
        }

        return $data;
    }
}
