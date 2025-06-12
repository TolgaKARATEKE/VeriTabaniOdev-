<?php
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['yontem_adi'])) {
    $yontemAdi = trim($_POST['yontem_adi']);
    if ($yontemAdi !== '') {
        try {
            $stmt = $conn->prepare('CALL sp_OdemeYontemiEkle(?)');
            $stmt->execute([$yontemAdi]);
            $success = 'Ödeme yöntemi başarıyla eklendi.';
        } catch (PDOException $e) {
            $error = 'Ekleme sırasında hata: ' . $e->getMessage();
        }
    } else {
        $error = 'Yöntem adı boş olamaz!';
    }
}


$stmt = $conn->query('SELECT * FROM OdemeYontemleri ORDER BY YontemAdi');
$odemeYontemleri = $stmt->fetchAll();
?>

<h2 class="mb-4">Ödeme Yöntemleri</h2>
<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Yeni Ödeme Yöntemi Ekle</div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Yöntem Adı</label>
                <input type="text" class="form-control" name="yontem_adi" required>
            </div>
            <button type="submit" class="btn btn-primary">Ekle</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Kayıtlı Ödeme Yöntemleri</div>
    <div class="card-body">
        <ul class="list-group">
            <?php foreach ($odemeYontemleri as $yontem): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($yontem['YontemAdi']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div> 