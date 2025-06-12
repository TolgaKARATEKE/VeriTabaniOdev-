<?php
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $conn->prepare("CALL sp_UrunEkle(?, ?)");
            $stmt->execute([
                $_POST['urun_adi'],
                $_POST['birim']
            ]);
            $success = "Ürün başarıyla eklendi.";
        } catch (PDOException $e) {
            $error = "Ürün eklenirken hata oluştu: " . $e->getMessage();
        }
    }
}


$stmt = $conn->query("
    SELECT u.*, COALESCE(SUM(s.KalanMiktar), 0) as ToplamStok 
    FROM Urunler u 
    LEFT JOIN Stok s ON u.UrunID = s.UrunID 
    GROUP BY u.UrunID 
    ORDER BY u.UrunAdi
");
$urunler = $stmt->fetchAll();
?>

<h2 class="mb-4">Ürünler</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Ürün Listesi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#urunEkleModal">
            Yeni Ürün Ekle
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ürün Adı</th>
                        <th>Birim</th>
                        <th>Toplam Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($urunler as $urun): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($urun['UrunAdi']); ?></td>
                            <td><?php echo htmlspecialchars($urun['Birim']); ?></td>
                            <td><?php echo number_format($urun['ToplamStok'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="urunEkleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Ürün Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ürün Adı</label>
                        <input type="text" class="form-control" name="urun_adi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Birim</label>
                        <select class="form-select" name="birim" required>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="adet">Adet</option>
                            <option value="kutu">Kutu</option>
                            <option value="paket">Paket</option>
                            <option value="litre">Litre (lt)</option>
                        </select>
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