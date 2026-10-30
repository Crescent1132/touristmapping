
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

require_role('admin');

// Only allow deletion through a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: destinations.php');
    exit;
}

verify_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: destinations.php?error=invalid_id');
    exit;
}

try {
    // Get the destination before deleting it.
    $stmt = $pdo->prepare(
        "SELECT id, image FROM destinations WHERE id = ?"
    );
    $stmt->execute([$id]);
    $destination = $stmt->fetch();

    if (!$destination) {
        header('Location: destinations.php?error=not_found');
        exit;
    }

    // Delete the destination from the database.
    $stmt = $pdo->prepare(
        "DELETE FROM destinations WHERE id = ?"
    );
    $stmt->execute([$id]);

    // Delete its uploaded image only after database deletion succeeds.
    if (!empty($destination['image'])) {
        $uploadDirectory = realpath(
            __DIR__ . '/../uploads/destinations'
        );

        $imagePath = realpath(
            __DIR__ . '/../' . $destination['image']
        );

        // Only delete files inside uploads/destinations.
        if (
            $uploadDirectory &&
            $imagePath &&
            strpos(
                $imagePath,
                $uploadDirectory . DIRECTORY_SEPARATOR
            ) === 0 &&
            is_file($imagePath)
        ) {
            unlink($imagePath);
        }
    }

    header('Location: destinations.php?deleted=1');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());

    header('Location: destinations.php?error=delete_failed');
    exit;
}
