<?php

class KeywordOpportunityScoringService {

    public function scoreOpportunity(array $candidate): array {
        $volume = max(0, min(100, (int)($candidate['volume_score'] ?? 55)));
        $competition = max(0, min(100, (int)($candidate['competition_score'] ?? 45)));
        $intent = max(0, min(100, (int)($candidate['intent_score'] ?? 70)));
        $trend = max(0, min(100, (int)($candidate['trend_score'] ?? 60)));

        $finalScore = (int) round(
            ($volume * 0.35)
            + ((100 - $competition) * 0.30)
            + ($intent * 0.20)
            + ($trend * 0.15)
        );

        return [
            'score' => max(0, min(100, $finalScore)),
            'components' => [
                'volume' => $volume,
                'competition' => $competition,
                'intent' => $intent,
                'trend' => $trend,
            ],
        ];
    }
}
