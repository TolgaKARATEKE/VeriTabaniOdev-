<?php
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $conn->prepare("CALL sp_MusteriEkle(?, ?, ?)");
            $stmt->execute([
                $_POST['adsoyad'],
                $_POST['telefon'],
                $_POST['adres']
            ]);
            $success = "Müşteri başarıyla eklendi.";
        } catch (PDOException $e) {
            $error = "Müşteri eklenirken hata oluştu: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'edit') {
        try {
            $stmt = $conn->prepare("CALL sp_MusteriGuncelle(?, ?, ?, ?)");
            $stmt->execute([
                $_POST['musteri_id'],
                $_POST['adsoyad'],
                $_POST['telefon'],
                $_POST['adres']
            ]);
            $success = "Müşteri başarıyla güncellendi.";
        } catch (PDOException $e) {
            $error = "Müşteri güncellenirken hata oluştu: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $stmt = $conn->prepare("CALL sp_MusteriSil(?)");
            $stmt->execute([$_POST['musteri_id']]);
            $success = "Müşteri başarıyla silindi.";
        } catch (PDOException $e) {
            $error = "Müşteri silinirken hata oluştu: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'odeme') {
        try {
            $stmt = $conn->prepare("CALL sp_MusteriOdemeYap(?, ?)");
            $stmt->execute([
                $_POST['musteri_id'],
                $_POST['odeme_tutari']
            ]);
            $success = "Ödeme başarıyla kaydedildi.";
        } catch (PDOException $e) {
            $error = "Ödeme kaydedilirken hata oluştu: " . $e->getMessage();
        }
    }
}


$stmt = $conn->query("CALL sp_MusteriListele()");
$musteriler = $stmt->fetchAll();
?>

<h2 class="mb-4">Müşteriler</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Müşteri Listesi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#musteriEkleModal">
            Yeni Müşteri Ekle
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ad Soyad</th>
                        <th>Telefon</th>
                        <th>Adres</th>
                        <th>Bakiye</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($musteriler as $musteri): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($musteri['AdSoyad']); ?></td>
                            <td><?php echo htmlspecialchars($musteri['Telefon']); ?></td>
                            <td><?php echo htmlspecialchars($musteri['Adres']); ?></td>
                            <td><?php echo number_format($musteri['Bakiye'], 2); ?> ₺</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary btn-action" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#musteriDuzenleModal"
                                        data-musteri='<?php echo json_encode($musteri); ?>'>
                                    Düzenle
                                </button>
                                <button type="button" class="btn btn-sm btn-success btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#odemeModal"
                                        data-musteri='<?php echo json_encode($musteri); ?>'>
                                    Ödeme Al
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#musteriSilModal"
                                        data-musteri='<?php echo json_encode($musteri); ?>'>
                                    Sil
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="musteriEkleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Müşteri Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" name="adsoyad" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" class="form-control" name="telefon" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea class="form-control" name="adres" rows="3"></textarea>
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


<div class="modal fade" id="musteriDuzenleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="musteri_id" id="edit_musteri_id">
                <div class="modal-header">
                    <h5 class="modal-title">Müşteri Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" name="adsoyad" id="edit_adsoyad" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" class="form-control" name="telefon" id="edit_telefon" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea class="form-control" name="adres" id="edit_adres" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="musteriSilModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="musteri_id" id="delete_musteri_id">
                <div class="modal-header">
                    <h5 class="modal-title">Müşteri Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu müşteriyi silmek istediğinizden emin misiniz?</p>
                    <p class="text-danger">Bu işlem geri alınamaz!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Sil</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="odemeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="odeme">
                <input type="hidden" name="musteri_id" id="odeme_musteri_id">
                <div class="modal-header">
                    <h5 class="modal-title">Ödeme Al</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mevcut Bakiye</label>
                        <input type="text" class="form-control" id="odeme_mevcut_bakiye" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Tutarı</label>
                        <input type="number" class="form-control" name="odeme_tutari" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Ödeme Al</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   
    const duzenleModal = document.getElementById('musteriDuzenleModal');
    duzenleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const musteri = JSON.parse(button.getAttribute('data-musteri'));
        
        document.getElementById('edit_musteri_id').value = musteri.MusteriID;
        document.getElementById('edit_adsoyad').value = musteri.AdSoyad;
        document.getElementById('edit_telefon').value = musteri.Telefon;
        document.getElementById('edit_adres').value = musteri.Adres;
    });

   
    const silModal = document.getElementById('musteriSilModal');
    silModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const musteri = JSON.parse(button.getAttribute('data-musteri'));
        document.getElementById('delete_musteri_id').value = musteri.MusteriID;
    });

   
    const odemeModal = document.getElementById('odemeModal');
    odemeModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const musteri = JSON.parse(button.getAttribute('data-musteri'));
        document.getElementById('odeme_musteri_id').value = musteri.MusteriID;
        document.getElementById('odeme_mevcut_bakiye').value = musteri.Bakiye + ' ₺';
    });
});
</script> 