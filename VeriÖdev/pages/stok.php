<?php
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $conn->prepare("CALL sp_StokGirisYap(?, ?, ?, ?)");
            $stmt->execute([
                $_POST['urun_id'],
                $_POST['mustahsil_id'],
                $_POST['miktar'],
                $_POST['alis_fiyati']
            ]);
            $success = "Stok girişi başarıyla kaydedildi.";
        } catch (PDOException $e) {
            $error = "Stok girişi yapılırken hata oluştu: " . $e->getMessage();
        }
    }
}


$stmt = $conn->query("
    SELECT s.*, u.UrunAdi, m.AdSoyad as MustahsilAdi 
    FROM Stok s 
    JOIN Urunler u ON s.UrunID = u.UrunID 
    JOIN Mustahsiller m ON s.MustahsilID = m.MustahsilID 
    ORDER BY s.GirisTarihi DESC
");
$stokGirisleri = $stmt->fetchAll();


$stmt = $conn->query("SELECT * FROM Urunler ORDER BY UrunAdi");
$urunler = $stmt->fetchAll();


$stmt = $conn->query("SELECT * FROM Mustahsiller ORDER BY AdSoyad");
$mustahsiller = $stmt->fetchAll();
?>

<h2 class="mb-4">Stok Girişi</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Stok Giriş Listesi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#stokGirisModal">
            Yeni Stok Girişi
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Ürün</th>
                        <th>Mustahsil</th>
                        <th>Miktar</th>
                        <th>Alış Fiyatı</th>
                        <th>Kalan Miktar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stokGirisleri as $giris): ?>
                        <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($giris['GirisTarihi'])); ?></td>
                            <td><?php echo htmlspecialchars($giris['UrunAdi']); ?></td>
                            <td><?php echo htmlspecialchars($giris['MustahsilAdi']); ?></td>
                            <td><?php echo number_format($giris['Miktar'], 2); ?></td>
                            <td><?php echo number_format($giris['AlisFiyati'], 2); ?> ₺</td>
                            <td><?php echo number_format($giris['KalanMiktar'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="stokGirisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Stok Girişi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ürün</label>
                        <select class="form-select" name="urun_id" required>
                            <option value="">Ürün Seçin</option>
                            <?php foreach ($urunler as $urun): ?>
                                <option value="<?php echo $urun['UrunID']; ?>">
                                    <?php echo htmlspecialchars($urun['UrunAdi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mustahsil</label>
                        <select class="form-select" name="mustahsil_id" required>
                            <option value="">Mustahsil Seçin</option>
                            <?php foreach ($mustahsiller as $mustahsil): ?>
                                <option value="<?php echo $mustahsil['MustahsilID']; ?>">
                                    <?php echo htmlspecialchars($mustahsil['AdSoyad']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Miktar</label>
                        <input type="number" class="form-control" name="miktar" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alış Fiyatı</label>
                        <input type="number" class="form-control" name="alis_fiyati" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div> 