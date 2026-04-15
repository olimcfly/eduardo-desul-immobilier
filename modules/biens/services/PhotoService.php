<?php

class PhotoService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    public function getPhotos(int $bienId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM bien_photos WHERE bien_id = :bien_id ORDER BY position ASC, id ASC');
        $stmt->execute([':bien_id' => $bienId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function uploadPhotos(int $bienId, array $files): array
    {
        if (empty($files['name']) || !is_array($files['name'])) {
            return [];
        }

        $targetDir = $this->getTargetDirectory($bienId);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException('Impossible de créer le dossier de stockage des photos.');
        }

        $position = $this->getNextPosition($bienId);
        $uploaded = [];

        foreach ($files['name'] as $index => $originalName) {
            if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpName = $files['tmp_name'][$index] ?? null;
            if (!$tmpName || !is_uploaded_file($tmpName)) {
                continue;
            }

            $extension = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $fileName = uniqid('photo_', true) . '.' . $extension;
            $fullPath = $targetDir . '/' . $fileName;
            $publicPath = $this->buildPublicPath($bienId, $fileName);

            if (!move_uploaded_file($tmpName, $fullPath)) {
                continue;
            }

            $stmt = $this->pdo->prepare('INSERT INTO bien_photos (bien_id, chemin, alt, position) VALUES (:bien_id, :chemin, :alt, :position)');
            $stmt->execute([
                ':bien_id' => $bienId,
                ':chemin' => $publicPath,
                ':alt' => pathinfo((string) $originalName, PATHINFO_FILENAME),
                ':position' => $position,
            ]);

            $uploaded[] = [
                'id' => (int) $this->pdo->lastInsertId(),
                'bien_id' => $bienId,
                'chemin' => $publicPath,
                'alt' => pathinfo((string) $originalName, PATHINFO_FILENAME),
                'position' => $position,
            ];

            $position++;
        }

        return $uploaded;
    }

    public function reorderPhotos(int $bienId, array $photoIds): void
    {
        if ($photoIds === []) {
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE bien_photos SET position = :position WHERE id = :id AND bien_id = :bien_id');
        foreach (array_values($photoIds) as $position => $photoId) {
            $stmt->execute([
                ':position' => $position,
                ':id' => (int) $photoId,
                ':bien_id' => $bienId,
            ]);
        }
    }

    public function setPrimaryPhoto(int $photoId): void
    {
        $stmt = $this->pdo->prepare('SELECT id, bien_id, chemin FROM bien_photos WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            throw new RuntimeException('Photo introuvable.');
        }

        $stmt = $this->pdo->prepare('UPDATE biens SET photo_principale = :photo_principale WHERE id = :id');
        $stmt->execute([
            ':photo_principale' => $photo['chemin'],
            ':id' => (int) $photo['bien_id'],
        ]);
    }

    public function deletePhoto(int $photoId): void
    {
        $stmt = $this->pdo->prepare('SELECT id, bien_id, chemin FROM bien_photos WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$photo) {
            return;
        }

        $localPath = ROOT_PATH . '/' . ltrim((string) $photo['chemin'], '/');
        if (is_file($localPath)) {
            @unlink($localPath);
        }

        $deleteStmt = $this->pdo->prepare('DELETE FROM bien_photos WHERE id = :id');
        $deleteStmt->execute([':id' => $photoId]);

        $this->compactPositions((int) $photo['bien_id']);
    }

    private function compactPositions(int $bienId): void
    {
        $photos = $this->getPhotos($bienId);
        $stmt = $this->pdo->prepare('UPDATE bien_photos SET position = :position WHERE id = :id');

        foreach ($photos as $position => $photo) {
            $stmt->execute([
                ':position' => $position,
                ':id' => (int) $photo['id'],
            ]);
        }
    }

    private function getNextPosition(int $bienId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM bien_photos WHERE bien_id = :bien_id');
        $stmt->execute([':bien_id' => $bienId]);

        return (int) $stmt->fetchColumn();
    }

    private function getTargetDirectory(int $bienId): string
    {
        return rtrim(UPLOAD_PATH, '/') . '/biens/' . $bienId;
    }

    private function buildPublicPath(int $bienId, string $fileName): string
    {
        return '/storage/uploads/biens/' . $bienId . '/' . $fileName;
    }
}
