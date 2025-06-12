<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $conn->prepare("CALL sp_SatisYap(?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['musteri_id'],
                $_POST['urun_id'],
                $_POST['miktar'],
                $_POST['satis_fiyati'],
                $_POST['odeme_yontemi_id']
            ]);
            $success = "Satış başarıyla kaydedildi.";
        } catch (PDOException $e) {
            $error = "Satış yapılırken hata oluştu: " . $e->getMessage();
        }
    }
}


$stmt = $conn->query("
    SELECT s.*, m.AdSoyad as MusteriAdi, o.YontemAdi as OdemeYontemi,
           sd.UrunID, u.UrunAdi, sd.Miktar, sd.BirimFiyat
    FROM Satislar s 
    JOIN Musteriler m ON s.MusteriID = m.MusteriID 
    LEFT JOIN OdemeYontemleri o ON s.OdemeYontemID = o.OdemeYontemID
    JOIN SatisDetaylari sd ON s.SatisID = sd.SatisID
    JOIN Urunler u ON sd.UrunID = u.UrunID
    ORDER BY s.SatisTarihi DESC
");
$satislar = $stmt->fetchAll();


$stmt = $conn->query("SELECT * FROM Musteriler ORDER BY AdSoyad");
$musteriler = $stmt->fetchAll();


$stmt = $conn->query("
    SELECT u.*, COALESCE(SUM(s.KalanMiktar), 0) as ToplamStok 
    FROM Urunler u 
    LEFT JOIN Stok s ON u.UrunID = s.UrunID 
    GROUP BY u.UrunID 
    HAVING ToplamStok > 0
    ORDER BY u.UrunAdi
");
$urunler = $stmt->fetchAll();


$stmt = $conn->query("SELECT * FROM OdemeYontemleri ORDER BY YontemAdi");
$odemeYontemleri = $stmt->fetchAll();
?>

<h2 class="mb-4">Satışlar</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Satış Listesi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#satisModal">
            Yeni Satış
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Müşteri</th>
                        <th>Ürün</th>
                        <th>Miktar</th>
                        <th>Birim Fiyat</th>
                        <th>Toplam Tutar</th>
                        <th>Ödeme Yöntemi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($satislar as $satis): ?>
                        <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($satis['SatisTarihi'])); ?></td>
                            <td><?php echo htmlspecialchars($satis['MusteriAdi']); ?></td>
                            <td><?php echo htmlspecialchars($satis['UrunAdi']); ?></td>
                            <td><?php echo number_format($satis['Miktar'], 2); ?></td>
                            <td><?php echo number_format($satis['BirimFiyat'], 2); ?> ₺</td>
                            <td><?php echo number_format($satis['ToplamTutar'], 2); ?> ₺</td>
                            <td><?php echo htmlspecialchars($satis['OdemeYontemi'] ?? 'Belirtilmemiş'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="satisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Satış</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Müşteri</label>
                        <select class="form-select" name="musteri_id" required>
                            <option value="">Müşteri Seçin</option>
                            <?php foreach ($musteriler as $musteri): ?>
                                <option value="<?php echo $musteri['MusteriID']; ?>">
                                    <?php echo htmlspecialchars($musteri['AdSoyad']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ürün</label>
                        <select class="form-select" name="urun_id" required>
                            <option value="">Ürün Seçin</option>
                            <?php foreach ($urunler as $urun): ?>
                                <option value="<?php echo $urun['UrunID']; ?>">
                                    <?php echo htmlspecialchars($urun['UrunAdi']); ?> 
                                    (Stok: <?php echo number_format($urun['ToplamStok'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Miktar</label>
                        <input type="number" class="form-control" name="miktar" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satış Fiyatı</label>
                        <input type="number" class="form-control" name="satis_fiyati" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Yöntemi</label>
                        <select class="form-select" name="odeme_yontemi_id">
                            <option value="">Ödeme Yöntemi Seçin</option>
                            <?php foreach ($odemeYontemleri as $yontem): ?>
                                <option value="<?php echo $yontem['OdemeYontemID']; ?>">
                                    <?php echo htmlspecialchars($yontem['YontemAdi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Satış Yap</button>
                </div>
            </form>
        </div>
    </div>
</div> 