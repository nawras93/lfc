<?php

namespace App\Services;

class CandidateDataNormalizer
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalize(array $data): array
    {
        $consentGiven = (bool) ($data['consent_given'] ?? false);

        if ($consentGiven) {
            $data['consent_at'] ??= now();
        } else {
            $data['consent_at'] = null;
        }

        return $data;
    }
}
