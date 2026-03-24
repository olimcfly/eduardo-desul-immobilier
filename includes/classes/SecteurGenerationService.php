<?php

class SecteurGenerationService
{
    public function __construct(private PDO $db)
    {
    }

    public function isAvailable(): bool
    {
        return false;
    }
}
