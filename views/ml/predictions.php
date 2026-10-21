<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Go up two directories (../../) to reach config/db.php
require_once __DIR__ . '/../../config/db.php';

// --- HANDLE INSERTION ---
if (isset($_POST['add_prediction'])) {
    $model_id = $_POST['model_id'];
    $input_data = $_POST['input_data'];
    $prediction_result = $_POST['prediction_result'];
    $confidence_score = $_POST['confidence_score'];

    $stmt = $pdo->prepare("INSERT INTO predictions (model_id, input_data, prediction_result, confidence_score) VALUES (?, ?, ?, ?)");
    $stmt->execute([$model_id, $input_data, $prediction_result, $confidence_score]);
    header("Location: predictions.php");
    exit();
}

// --- HANDLE DELETION ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM predictions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: predictions.php");
    exit();
}

// --- HANDLE UPDATE PREPARATION & EXECUTION ---
$edit_pred = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM predictions WHERE id = ?");
    $stmt->execute([$id]);
    $edit_pred = $stmt->fetch();
}

if (isset($_POST['update_prediction'])) {
    $id = $_POST['id'];
    $model_id = $_POST['model_id'];
    $input_data = $_POST['input_data'];
    $prediction_result = $_POST['prediction_result'];
    $confidence_score = $_POST['confidence_score'];

    $stmt = $pdo->prepare("UPDATE predictions SET model_id = ?, input_data = ?, prediction_result = ?, confidence_score = ? WHERE id = ?");
    $stmt->execute([$model_id, $input_data, $prediction_result, $confidence_score, $id]);
    header("Location: predictions.php");
    exit();
}

// --- FETCH PARENT MODELS FOR DROPDOWN ---
$stmt = $pdo->query("SELECT id, model_name FROM ml_models ORDER BY model_name ASC");
$models = $stmt->fetchAll();

// --- FETCH ALL PREDICTIONS WITH MODEL NAME ---
$stmt = $pdo->query("SELECT predictions.*, ml_models.model_name FROM predictions JOIN ml_models ON predictions.model_id = ml_models.id ORDER BY predictions.id DESC");
$predictions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Predictions (Child Entity)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { background: #f9f9f9; padding: 15px; border: 1px solid #ccc; width: 380px; }
        input[type=text], input[type=number], select { width: 100%; padding: 8px; margin: 5px 0 15px 0; }
        button { padding: 8px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <a href="../../index.php">← Back to Dashboard</a>
    <h2>Child Entity: Predictions</h2>

    <form method="POST" action="predictions.php">
        <h3><?= $edit_pred ? 'Edit Prediction' : 'Add New Prediction' ?></h3>
        <?php if ($edit_pred): ?>
            <input type="hidden" name="id" value="<?= $edit_pred['id'] ?>">
        <?php endif; ?>

        <label>Select ML Model (Parent):</label>
        <select name="model_id" required>
            <option value="">-- Choose Model --</option>
            <?php foreach ($models as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($edit_pred && $edit_pred['model_id'] == $m['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['model_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Input Data:</label>
        <input type="text" name="input_data" value="<?= $edit_pred['input_data'] ?? '' ?>" required>

        <label>Prediction Result:</label>
        <input type="text" name="prediction_result" value="<?= $edit_pred['prediction_result'] ?? '' ?>" required>

        <label>Confidence Score (%):</label>
        <input type="number" step="0.01" name="confidence_score" value="<?= $edit_pred['confidence_score'] ?? '' ?>" required>

        <?php if ($edit_pred): ?>
            <button type="submit" name="update_prediction">Update Prediction</button>
            <a href="predictions.php">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_prediction">Add Prediction</button>
        <?php endif; ?>
    </form>

    <h3>Recorded Predictions</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Model Name</th>
            <th>Input Data</th>
            <th>Prediction Result</th>
            <th>Confidence</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($predictions as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['model_name']) ?></td>
                <td><?= htmlspecialchars($p['input_data']) ?></td>
                <td><?= htmlspecialchars($p['prediction_result']) ?></td>
                <td><?= $p['confidence_score'] ?>%</td>
                <td><?= $p['created_at'] ?></td>
                <td>
                    <a href="predictions.php?edit=<?= $p['id'] ?>">Edit</a> | 
                    <a href="predictions.php?delete=<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this prediction?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
