<?php
$db = new Database();
$conn = $db->getConnection();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $conn->prepare("INSERT INTO Mustahsiller (AdSoyad, Telefon, Adres) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['adsoyad'],
                $_POST['telefon'],
                $_POST['adres']
            ]);
            $success = "Mustahsil başarıyla eklendi.";
        } catch (PDOException $e) {
            $error = "Mustahsil eklenirken hata oluştu: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'edit') {
        try {
            $stmt = $conn->prepare("UPDATE Mustahsiller SET AdSoyad = ?, Telefon = ?, Adres = ? WHERE MustahsilID = ?");
            $stmt->execute([
                $_POST['adsoyad'],
                $_POST['telefon'],
                $_POST['adres'],
                $_POST['mustahsil_id']
            ]);
            $success = "Mustahsil başarıyla güncellendi.";
        } catch (PDOException $e) {
            $error = "Mustahsil güncellenirken hata oluştu: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $stmt = $conn->prepare("DELETE FROM Mustahsiller WHERE MustahsilID = ?");
            $stmt->execute([$_POST['mustahsil_id']]);
            $success = "Mustahsil başarıyla silindi.";
        } catch (PDOException $e) {
            $error = "Mustahsil silinirken hata oluştu: " . $e->getMessage();
        }
    }
}


$stmt = $conn->query("SELECT * FROM Mustahsiller ORDER BY AdSoyad");
$mustahsiller = $stmt->fetchAll();
?>

<h2 class="mb-4">Mustahsiller</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Mustahsil Listesi</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mustahsilEkleModal">
            Yeni Mustahsil Ekle
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
                        <th>Borç</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mustahsiller as $mustahsil): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mustahsil['AdSoyad']); ?></td>
                            <td><?php echo htmlspecialchars($mustahsil['Telefon']); ?></td>
                            <td><?php echo htmlspecialchars($mustahsil['Adres']); ?></td>
                            <td><?php echo number_format($mustahsil['Borc'], 2); ?> ₺</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary btn-action" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#mustahsilDuzenleModal"
                                        data-mustahsil='<?php echo json_encode($mustahsil); ?>'>
                                    Düzenle
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#mustahsilSilModal"
                                        data-mustahsil='<?php echo json_encode($mustahsil); ?>'>
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


<div class="modal fade" id="mustahsilEkleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Mustahsil Ekle</h5>
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


<div class="modal fade" id="mustahsilDuzenleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="mustahsil_id" id="edit_mustahsil_id">
                <div class="modal-header">
                    <h5 class="modal-title">Mustahsil Düzenle</h5>
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


<div class="modal fade" id="mustahsilSilModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="mustahsil_id" id="delete_mustahsil_id">
                <div class="modal-header">
                    <h5 class="modal-title">Mustahsil Sil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bu mustahsil'i silmek istediğinizden emin misiniz?</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const duzenleModal = document.getElementById('mustahsilDuzenleModal');
    duzenleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const mustahsil = JSON.parse(button.getAttribute('data-mustahsil'));
        
        document.getElementById('edit_mustahsil_id').value = mustahsil.MustahsilID;
        document.getElementById('edit_adsoyad').value = mustahsil.AdSoyad;
        document.getElementById('edit_telefon').value = mustahsil.Telefon;
        document.getElementById('edit_adres').value = mustahsil.Adres;
    });

    
    const silModal = document.getElementById('mustahsilSilModal');
    silModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const mustahsil = JSON.parse(button.getAttribute('data-mustahsil'));
        document.getElementById('delete_mustahsil_id').value = mustahsil.MustahsilID;
    });
});
</script> 